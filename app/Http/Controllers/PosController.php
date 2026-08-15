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
        // Urutkan produk dengan stok menipis (<= 5) & stok habis (0) paling atas
        $products = Product::with('category')
            ->orderByRaw("CASE WHEN stock <= 5 THEN 0 ELSE 1 END")
            ->orderBy('stock', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $customerDebts = Transaction::where('payment_method', 'hutang')->where('debt_amount', '>', 0)->orderBy('created_at', 'desc')->get();
        return view('pos.index', compact('categories', 'products', 'customerDebts'));
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
            'payment_method' => 'required|in:cash,qris,edc,hutang',
            'due_date' => 'nullable|date',
        ]);

        if ($request->payment_method === 'hutang' && (!$request->filled('customer_name') || trim($request->customer_name) === '')) {
            return response()->json([
                'success' => false,
                'message' => 'Nama Pelanggan / Pemohon Kasbon wajib diisi untuk transaksi Hutang!',
            ], 422);
        }

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
            $payAmount = (float)$request->pay_amount;

            $isHutang = $request->payment_method === 'hutang';
            $debtAmount = 0;
            $changeAmount = 0;

            if ($isHutang) {
                $debtAmount = max(0, $finalTotal - $payAmount);
                $changeAmount = 0;
            } else {
                if ($payAmount < $finalTotal) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Jumlah pembayaran kurang dari total belanja!',
                    ], 422);
                }
                $changeAmount = $payAmount - $finalTotal;
            }

            $invoiceNumber = 'TRX-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            $customerName = $request->customer_name ?: ($isHutang ? 'Pelanggan Kasbon' : 'Pelanggan Umum');

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'type' => 'penjualan',
                'description' => $isHutang ? "Kasbon/Hutang Pelanggan {$customerName} (Sisa: Rp " . number_format($debtAmount, 0, ',', '.') . ")" : "Penjualan POS Kasir",
                'cashier_name' => auth()->user()->name ?? 'Kasir',
                'customer_name' => $customerName,
                'total_amount' => $finalTotal,
                'debt_amount' => $debtAmount,
                'due_date' => $isHutang ? $request->due_date : null,
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
                'message' => $isHutang ? 'Transaksi Hutang/Kasbon berhasil dicatat!' : 'Transaksi berhasil!',
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

    public function quickStoreProduct(Request $request)
    {
        // Auto-generate barcode if left empty or whitespace
        if (!$request->filled('barcode') || trim($request->barcode) === '') {
            $request->merge([
                'barcode' => 'BRC-' . strtoupper(Str::random(8))
            ]);
        } else {
            $request->merge([
                'barcode' => trim($request->barcode)
            ]);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'barcode' => 'required|string|unique:products,barcode',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'stock.required' => 'Stok awal minimal 1.',
            'barcode.unique' => 'Kode Barcode ini sudah terdaftar untuk produk lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'barcode' => $request->barcode,
                'purchase_price' => $request->filled('purchase_price') ? $request->purchase_price : $request->selling_price,
                'selling_price' => $request->selling_price,
                'stock' => $request->stock,
                'unit' => $request->unit ?: 'pcs',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Produk '{$product->name}' berhasil ditambahkan!",
                'product' => $product->load('category'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah produk: ' . $e->getMessage(),
            ], 500);
        }
    }
}
