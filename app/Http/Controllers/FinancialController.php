<?php

namespace App\Http\Controllers;

use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialController extends Controller
{
    // 1. Dashboard Ringkasan Keuangan
    public function index()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // 1. Total Penjualan POS (Omset) Bulan Ini
        $omsetPos = Transaction::where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        // 2. Total HPP Penjualan POS (Beban Pokok) Bulan Ini
        $hppPos = TransactionDetail::whereHas('transaction', function($q) use ($startOfMonth, $endOfMonth) {
            $q->where('type', 'penjualan')
              ->where('status', 'completed')
              ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        })->selectRaw('SUM(quantity * purchase_price) as hpp')->value('hpp') ?? 0;

        // 3. Kas Masuk (Non-POS) Bulan Ini
        $totalKasMasuk = CashTransaction::where('type', 'in')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // 4. Kas Keluar (Operasional) Bulan Ini
        $totalKasKeluar = CashTransaction::where('type', 'out')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // 5. Total Gaji Karyawan Dibayar Bulan Ini
        $totalGajiPaid = Payroll::whereBetween('payment_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('net_salary');

        // Laba Kotor = Omset POS - HPP
        $labaKotor = $omsetPos - $hppPos;

        // Laba Bersih = Laba Kotor + Kas Masuk - Kas Keluar
        $labaBersih = $labaKotor + $totalKasMasuk - $totalKasKeluar;

        // Total Saldo Kas Toko Kumulatif (Seluruh Waktu)
        $cumulativeOmset = Transaction::where('type', 'penjualan')->where('status', 'completed')->sum('total_amount');
        $cumulativeKasMasuk = CashTransaction::where('type', 'in')->sum('amount');
        $cumulativeKasKeluar = CashTransaction::where('type', 'out')->sum('amount');
        $saldoKasUtama = $cumulativeOmset + $cumulativeKasMasuk - $cumulativeKasKeluar;

        $recentTransactions = CashTransaction::with(['category', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('financial.index', compact(
            'omsetPos',
            'hppPos',
            'labaKotor',
            'totalKasMasuk',
            'totalKasKeluar',
            'totalGajiPaid',
            'labaBersih',
            'saldoKasUtama',
            'recentTransactions'
        ));
    }

    // 2. Kas Masuk (Cash In)
    public function cashIn()
    {
        $categories = CashCategory::where('type', 'in')->orderBy('name', 'asc')->get();
        $transactions = CashTransaction::with(['category', 'user'])
            ->where('type', 'in')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('financial.cash_in', compact('categories', 'transactions'));
    }

    public function storeCashIn(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'cash_category_id' => 'nullable|exists:cash_categories,id',
            'payment_method' => 'required|string',
            'account_name' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $trxNumber = 'KIN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            CashTransaction::create([
                'transaction_number' => $trxNumber,
                'type' => 'in',
                'cash_category_id' => $request->cash_category_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'account_name' => $request->account_name,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('financial.cash_in')->with('success', "Kas Masuk sebesar Rp " . number_format($request->amount, 0, ',', '.') . " berhasil dicatat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat Kas Masuk: ' . $e->getMessage())->withInput();
        }
    }

    // 3. Kas Keluar (Cash Out / Expense)
    public function cashOut()
    {
        $categories = CashCategory::where('type', 'out')->orderBy('name', 'asc')->get();
        $transactions = CashTransaction::with(['category', 'user'])
            ->where('type', 'out')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('financial.cash_out', compact('categories', 'transactions'));
    }

    public function storeCashOut(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'cash_category_id' => 'nullable|exists:cash_categories,id',
            'payment_method' => 'required|string',
            'account_name' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $trxNumber = 'KOUT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            CashTransaction::create([
                'transaction_number' => $trxNumber,
                'type' => 'out',
                'cash_category_id' => $request->cash_category_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'account_name' => $request->account_name,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('financial.cash_out')->with('success', "Kas Keluar (Beban) sebesar Rp " . number_format($request->amount, 0, ',', '.') . " berhasil dicatat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat Kas Keluar: ' . $e->getMessage())->withInput();
        }
    }

    // 4. Kategori Pengeluaran & Pemasukan
    public function categories()
    {
        $categories = CashCategory::withCount('transactions')->orderBy('type', 'asc')->orderBy('name', 'asc')->get();
        return view('financial.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:in,out',
            'description' => 'nullable|string',
        ]);

        CashCategory::create($request->all());

        return redirect()->route('financial.categories')->with('success', "Kategori Keuangan '{$request->name}' berhasil ditambahkan!");
    }

    public function destroyCategory(CashCategory $category)
    {
        $category->delete();
        return redirect()->route('financial.categories')->with('success', 'Kategori Keuangan berhasil dihapus.');
    }

    // 5. Gaji Karyawan & Payroll
    public function payrolls()
    {
        $employees = Employee::where('status', 'active')->orderBy('name', 'asc')->get();
        $payrolls = Payroll::with(['employee', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('financial.payrolls', compact('employees', 'payrolls'));
    }

    public function storePayroll(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_month' => 'required|string',
            'base_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($request->employee_id);
            $allowance = (float)($request->allowance ?? 0);
            $deduction = (float)($request->deduction ?? 0);
            $netSalary = (float)$request->base_salary + $allowance - $deduction;

            $payrollNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $payroll = Payroll::create([
                'payroll_number' => $payrollNumber,
                'employee_id' => $employee->id,
                'period_month' => $request->period_month,
                'base_salary' => $request->base_salary,
                'allowance' => $allowance,
                'deduction' => $deduction,
                'net_salary' => $netSalary,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            // Ensure 'Gaji Karyawan' cash category exists
            $categoryGaji = CashCategory::firstOrCreate(
                ['name' => 'Gaji Karyawan', 'type' => 'out'],
                ['description' => 'Pengeluaran Beban Gaji & Tunjangan Karyawan']
            );

            // Automatically record Kas Keluar
            CashTransaction::create([
                'transaction_number' => 'KOUT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                'type' => 'out',
                'cash_category_id' => $categoryGaji->id,
                'amount' => $netSalary,
                'payment_method' => $request->payment_method,
                'account_name' => 'Kas Utama',
                'reference_number' => $payrollNumber,
                'notes' => "Pembayaran Gaji Karyawan '{$employee->name}' Periode {$request->period_month}",
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('financial.payrolls')->with('success', "Gaji Karyawan untuk '{$employee->name}' (Rp " . number_format($netSalary, 0, ',', '.') . ") berhasil diproses!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses Gaji: ' . $e->getMessage())->withInput();
        }
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'base_salary' => 'required|numeric|min:0',
        ]);

        Employee::create($request->all());

        return redirect()->route('financial.payrolls')->with('success', "Data Karyawan '{$request->name}' berhasil ditambahkan!");
    }

    // 6. Laporan Arus Kas (Cash Flow Statement)
    public function cashflow(Request $request)
    {
        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: now()->endOfMonth()->toDateString();

        $omsetPos = Transaction::where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('total_amount');

        $kasMasuk = CashTransaction::where('type', 'in')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        $kasKeluarOps = CashTransaction::where('type', 'out')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        $totalInflow = $omsetPos + $kasMasuk;
        $totalOutflow = $kasKeluarOps;
        $netCashflow = $totalInflow - $totalOutflow;

        $cashDetails = CashTransaction::with(['category', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('financial.cashflow', compact(
            'startDate',
            'endDate',
            'omsetPos',
            'kasMasuk',
            'totalInflow',
            'kasKeluarOps',
            'totalOutflow',
            'netCashflow',
            'cashDetails'
        ));
    }

    // 7. Laporan Laba Rugi Komprehensif (Income Statement / Profit & Loss)
    public function profitLoss(Request $request)
    {
        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: now()->endOfMonth()->toDateString();

        // Omset Penjualan POS
        $omsetPos = Transaction::where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('total_amount');

        // HPP Penjualan POS
        $hppPos = TransactionDetail::whereHas('transaction', function($q) use ($startDate, $endDate) {
            $q->where('type', 'penjualan')
              ->where('status', 'completed')
              ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        })->selectRaw('SUM(quantity * purchase_price) as hpp')->value('hpp') ?? 0;

        $labaKotor = $omsetPos - $hppPos;

        // Pemasukan Non-POS
        $kasMasukLain = CashTransaction::where('type', 'in')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        // Breakdown Beban Operasional Per Kategori
        $expenseCategories = CashCategory::where('type', 'out')
            ->withSum(['transactions' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }], 'amount')
            ->get();

        $totalOperasional = CashTransaction::where('type', 'out')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        $labaBersih = $labaKotor + $kasMasukLain - $totalOperasional;

        return view('financial.profit_loss', compact(
            'startDate',
            'endDate',
            'omsetPos',
            'hppPos',
            'labaKotor',
            'kasMasukLain',
            'expenseCategories',
            'totalOperasional',
            'labaBersih'
        ));
    }
}
