<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount(['transactions' => function ($q) {
            $q->where('type', 'penjualan');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        $totalCustomers = Customer::count();
        $totalDebt = Customer::sum('current_debt');
        $overLimitCount = Customer::where('credit_limit', '>', 0)
            ->whereRaw('current_debt > credit_limit')
            ->count();

        return view('customers.index', compact('customers', 'totalCustomers', 'totalDebt', 'overLimitCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'type' => 'required|in:retail,wholesale',
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'credit_limit.required' => 'Limit kasbon/piutang wajib diisi.',
        ]);

        $code = 'CUST-' . strtoupper(Str::random(6));

        Customer::create([
            'code' => $code,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit,
            'current_debt' => 0,
            'type' => $request->type,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', "Pelanggan '{$request->name}' ({$request->type}) berhasil ditambahkan!");
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'type' => 'required|in:retail,wholesale',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit,
            'type' => $request->type,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', "Data pelanggan '{$customer->name}' berhasil diperbarui!");
    }

    public function show(Customer $customer)
    {
        $transactions = Transaction::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere('customer_name', $customer->name);
        })->orderBy('created_at', 'desc')->paginate(15);

        return view('customers.show', compact('customer', 'transactions'));
    }

    public function destroy(Customer $customer)
    {
        if ($customer->current_debt > 0) {
            return redirect()->back()->with('error', "Pelanggan '{$customer->name}' masih memiliki sisa tunggakan/piutang Rp " . number_format($customer->current_debt, 0, ',', '.') . "! Lunasi dulu sebelum menghapus.");
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', "Pelanggan berhasil dihapus!");
    }
}
