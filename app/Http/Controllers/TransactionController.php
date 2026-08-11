<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('details');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('cashier_name', 'like', "%{$search}%");
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

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('transactions.index', compact('transactions'));
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
            // Restore stock
            foreach ($transaction->details as $detail) {
                if ($detail->product_id) {
                    Product::where('id', $detail->product_id)->increment('stock', $detail->quantity);
                }
            }

            $transaction->update(['status' => 'canceled']);

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi berhasil dibatalkan dan stok dikembalikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}
