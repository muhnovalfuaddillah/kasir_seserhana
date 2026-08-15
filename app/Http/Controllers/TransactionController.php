<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'all');
        $query = Transaction::with('details');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('cashier_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = (clone $query)->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Calculate KPI Metrics for current month or selected filter
        $summaryQuery = Transaction::where('status', 'completed');
        if ($request->filled('date')) {
            $summaryQuery->whereDate('created_at', $request->date);
        }

        $totalPenjualan = (clone $summaryQuery)->where('type', 'penjualan')->sum('total_amount');
        $totalPengeluaran = (clone $summaryQuery)->where('type', 'pengeluaran')->sum('total_amount');
        $totalPembelian = (clone $summaryQuery)->where('type', 'pembelian')->sum('total_amount');
        $totalSisaHutang = (clone $summaryQuery)->sum('debt_amount');

        $products = Product::orderBy('name', 'asc')->get();
        $customerDebts = Transaction::where('payment_method', 'hutang')->where('debt_amount', '>', 0)->orderBy('created_at', 'desc')->get();

        return view('transactions.index', compact(
            'transactions',
            'type',
            'totalPenjualan',
            'totalPengeluaran',
            'totalPembelian',
            'totalSisaHutang',
            'products',
            'customerDebts'
        ));
    }

    public function storeCustomerDebtPayment(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'pay_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,qris,edc',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($request->transaction_id);

            $payAmount = (float)$request->pay_amount;
            $currentDebt = (float)$transaction->debt_amount;

            $newDebt = max(0, $currentDebt - $payAmount);
            $transaction->update([
                'debt_amount' => $newDebt,
                'pay_amount' => $transaction->pay_amount + $payAmount,
            ]);

            $invoiceNumber = 'PAY-CUST-' . date('YmdHis') . rand(10, 99);

            Transaction::create([
                'invoice_number' => $invoiceNumber,
                'type' => 'bayar_hutang',
                'description' => "Pelunasan Hutang Pelanggan {$transaction->customer_name} (Nota #{$transaction->invoice_number})",
                'cashier_name' => auth()->user()->name ?? 'Kasir',
                'customer_name' => $transaction->customer_name,
                'total_amount' => $payAmount,
                'debt_amount' => 0,
                'discount_amount' => 0,
                'pay_amount' => $payAmount,
                'change_amount' => 0,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()->back()->with('success', "Pembayaran hutang pelanggan {$transaction->customer_name} sebesar Rp " . number_format($payAmount, 0, ',', '.') . " berhasil dicatat! (Sisa Hutang: Rp " . number_format($newDebt, 0, ',', '.') . ")");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat pelunasan hutang pelanggan: ' . $e->getMessage());
        }
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,qris,edc',
        ]);

        $invoiceNumber = 'EXP-' . date('YmdHis') . rand(10, 99);

        Transaction::create([
            'invoice_number' => $invoiceNumber,
            'type' => 'pengeluaran',
            'description' => $request->description,
            'cashier_name' => auth()->user()->name ?? 'Admin',
            'customer_name' => 'Pengeluaran Toko',
            'total_amount' => $request->total_amount,
            'discount_amount' => 0,
            'pay_amount' => $request->total_amount,
            'change_amount' => 0,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
        ]);

        return redirect()->back()->with('success', "Pengeluaran operasional '{$request->description}' sebesar Rp " . number_format($request->total_amount, 0, ',', '.') . " berhasil dicatat!");
    }

    public function storePurchase(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'supplier_name' => 'required|string|max:255',
            'payment_method' => 'required|in:cash,qris,edc',
            'is_debt' => 'nullable|boolean',
            'debt_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $qty = (int)$request->quantity;
            $purchasePrice = (float)$request->purchase_price;
            $totalAmount = $qty * $purchasePrice;

            $isDebt = $request->boolean('is_debt');
            $debtAmount = $isDebt ? (float)($request->debt_amount ?? $totalAmount) : 0;
            $payAmount = $isDebt ? max(0, $totalAmount - $debtAmount) : $totalAmount;

            $invoiceNumber = 'PUR-' . date('YmdHis') . rand(10, 99);

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'type' => 'pembelian',
                'description' => "Pembelian Stok {$product->name} ({$qty} {$product->unit}) dari {$request->supplier_name}",
                'cashier_name' => auth()->user()->name ?? 'Admin',
                'customer_name' => 'Toko (Restock)',
                'supplier_name' => $request->supplier_name,
                'total_amount' => $totalAmount,
                'debt_amount' => $debtAmount,
                'due_date' => $isDebt ? $request->due_date : null,
                'discount_amount' => 0,
                'pay_amount' => $payAmount,
                'change_amount' => 0,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'purchase_price' => $purchasePrice,
                'selling_price' => $product->selling_price,
                'quantity' => $qty,
                'subtotal' => $totalAmount,
            ]);

            // Automatically increment product stock & update purchase price
            $product->increment('stock', $qty);
            if ($purchasePrice > 0) {
                $product->update(['purchase_price' => $purchasePrice]);
            }

            DB::commit();

            return redirect()->back()->with('success', "Pembelian stok '{$product->name}' (+{$qty} pcs) berhasil dicatat dan stok bertambah!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat pembelian stok: ' . $e->getMessage());
        }
    }

    public function storeDebtPayment(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'pay_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,qris,edc',
            'description' => 'nullable|string|max:255',
        ]);

        $invoiceNumber = 'PAY-' . date('YmdHis') . rand(10, 99);

        Transaction::create([
            'invoice_number' => $invoiceNumber,
            'type' => 'bayar_hutang',
            'description' => $request->description ?? "Pembayaran Hutang kepada {$request->supplier_name}",
            'cashier_name' => auth()->user()->name ?? 'Admin',
            'customer_name' => 'Pelunasan Hutang',
            'supplier_name' => $request->supplier_name,
            'total_amount' => $request->pay_amount,
            'debt_amount' => 0,
            'discount_amount' => 0,
            'pay_amount' => $request->pay_amount,
            'change_amount' => 0,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
        ]);

        return redirect()->back()->with('success', "Pembayaran hutang kepada '{$request->supplier_name}' sebesar Rp " . number_format($request->pay_amount, 0, ',', '.') . " berhasil dicatat!");
    }

    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load('details'));
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status === 'canceled') {
            return redirect()->back()->with('error', 'Transaksi sudah dibatalkan sebelumnya!');
        }

        DB::beginTransaction();
        try {
            if ($transaction->type === 'penjualan') {
                foreach ($transaction->details as $detail) {
                    if ($detail->product_id) {
                        Product::where('id', $detail->product_id)->increment('stock', $detail->quantity);
                    }
                }
            } elseif ($transaction->type === 'pembelian') {
                foreach ($transaction->details as $detail) {
                    if ($detail->product_id) {
                        Product::where('id', $detail->product_id)->decrement('stock', $detail->quantity);
                    }
                }
            }

            $transaction->update(['status' => 'canceled']);

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi berhasil dibatalkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}
