<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $products = Product::with('category')->where('stock', '>', 0)->orderBy('name', 'asc')->get();
        return view('pos.index', compact('categories', 'products'));
    }

    public function search(Request $request)
    {
        $query = Product::with('category')->where('stock', '>', 0);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id') && $request->category_id != 'all') {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('name', 'asc')->get();

        return response()->json($products);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'discount_amount' => 'nullable|numeric|min:0',
            'pay_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,edc',
        ]);

        DB::beginTransaction();
        try {
            $items = $request->items;
            $totalAmount = 0;
            $transactionDetails = [];

            foreach ($items as $itemData) {
                $product = Product::find($itemData['id']);

                if ($product->stock < $itemData['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok produk '{$product->name}' tidak mencukupi! (Sisa: {$product->stock})",
                    ], 422);
                }

                $subtotal = $product->selling_price * $itemData['qty'];
                $totalAmount += $subtotal;

                // Reduce stock
                $product->decrement('stock', $itemData['qty']);

                $transactionDetails[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'purchase_price' => $product->purchase_price,
                    'selling_price' => $product->selling_price,
                    'quantity' => $itemData['qty'],
                    'subtotal' => $subtotal,
                ];
            }

            $discount = $request->discount_amount ?? 0;
            $finalTotal = max(0, $totalAmount - $discount);
            $payAmount = $request->pay_amount;

            if ($payAmount < $finalTotal) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran kurang dari total belanja!',
                ], 422);
            }

            $changeAmount = $payAmount - $finalTotal;
            $invoiceNumber = 'TRX-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'cashier_name' => auth()->user()->name ?? 'Kasir',
                'customer_name' => $request->customer_name ?? 'Pelanggan Umum',
                'total_amount' => $finalTotal,
                'discount_amount' => $discount,
                'pay_amount' => $payAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);

            foreach ($transactionDetails as $detail) {
                $detail['transaction_id'] = $transaction->id;
                TransactionDetail::create($detail);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'transaction' => $transaction->load('details'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
