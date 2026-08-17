<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Transaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = CashierShift::with(['user', 'branch'])
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        $activeShift = CashierShift::activeShift();

        return view('shifts.index', compact('shifts', 'activeShift'));
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $activeShift = CashierShift::activeShift();
        if ($activeShift) {
            return redirect()->back()->with('error', 'Anda masih memiliki Shift Kasir yang aktif! Silakan Tutup Kas terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            $shiftNumber = 'SHIFT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $shift = CashierShift::create([
                'shift_number' => $shiftNumber,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? null,
                'start_time' => now(),
                'starting_cash' => $request->starting_cash,
                'expected_cash' => $request->starting_cash,
                'status' => 'open',
                'notes' => $request->notes,
            ]);

            ActivityLog::log('OPEN_SHIFT', "Membuka Shift Kasir '{$shiftNumber}' dengan Modal Kas Awal Rp " . number_format($request->starting_cash, 0, ',', '.'));

            DB::commit();
            return redirect()->back()->with('success', "Shift Kasir '{$shiftNumber}' berhasil dibuka dengan Modal Kas Awal Rp " . number_format($request->starting_cash, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal Membuka Shift Kasir: ' . $e->getMessage());
        }
    }

    public function closeShift(Request $request)
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift = CashierShift::activeShift();
        if (!$shift) {
            return redirect()->back()->with('error', 'Tidak ditemukan Shift Kasir yang aktif untuk ditutup!');
        }

        DB::beginTransaction();
        try {
            // Calculate sales transactions within shift period
            $cashierName = $shift->user ? $shift->user->name : null;
            $salesQuery = Transaction::where('type', 'penjualan')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$shift->start_time, now()]);

            $salesQuery->where(function ($q) use ($shift, $cashierName) {
                $q->where('user_id', $shift->user_id);
                if ($cashierName) {
                    $q->orWhere('cashier_name', $cashierName);
                }
            });

            $sales = $salesQuery->get();

            $cashSales = $sales->where('payment_method', 'cash')->sum('total_amount');
            $qrisSales = $sales->where('payment_method', 'qris')->sum('total_amount');
            $edcSales = $sales->where('payment_method', 'edc')->sum('total_amount');
            $totalTrx = $sales->count();

            $expectedCash = $shift->starting_cash + $cashSales;
            $actualCash = (float) $request->actual_cash;
            $difference = $actualCash - $expectedCash;

            $shift->update([
                'end_time' => now(),
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'cash_difference' => $difference,
                'total_sales_cash' => $cashSales,
                'total_sales_qris' => $qrisSales,
                'total_sales_edc' => $edcSales,
                'total_transactions' => $totalTrx,
                'status' => 'closed',
                'notes' => $request->notes,
            ]);

            ActivityLog::log('CLOSE_SHIFT', "Menutup Shift Kasir '{$shift->shift_number}'. Expected Cash: Rp " . number_format($expectedCash, 0, ',', '.') . ", Actual Cash: Rp " . number_format($actualCash, 0, ',', '.') . ", Selisih: Rp " . number_format($difference, 0, ',', '.'));

            DB::commit();
            return redirect()->route('shifts.print', $shift->id)->with('success', "Shift Kasir '{$shift->shift_number}' berhasil ditutup.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal Menutup Shift Kasir: ' . $e->getMessage());
        }
    }

    public function printReport(CashierShift $shift)
    {
        return view('shifts.print', compact('shift'));
    }

    public function forceClose(Request $request, CashierShift $shift)
    {
        if ($shift->status === 'closed') {
            return redirect()->back()->with('error', 'Shift Kasir ini sudah ditutup sebelumnya!');
        }

        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate sales transactions within shift period
            $cashierName = $shift->user ? $shift->user->name : null;
            $salesQuery = Transaction::where('type', 'penjualan')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$shift->start_time, now()]);

            $salesQuery->where(function ($q) use ($shift, $cashierName) {
                $q->where('user_id', $shift->user_id);
                if ($cashierName) {
                    $q->orWhere('cashier_name', $cashierName);
                }
            });

            $sales = $salesQuery->get();

            $cashSales = $sales->where('payment_method', 'cash')->sum('total_amount');
            $qrisSales = $sales->where('payment_method', 'qris')->sum('total_amount');
            $edcSales = $sales->where('payment_method', 'edc')->sum('total_amount');
            $totalTrx = $sales->count();

            $expectedCash = $shift->starting_cash + $cashSales;
            $actualCash = (float) $request->actual_cash;
            $difference = $actualCash - $expectedCash;

            $shift->update([
                'end_time' => now(),
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'cash_difference' => $difference,
                'total_sales_cash' => $cashSales,
                'total_sales_qris' => $qrisSales,
                'total_sales_edc' => $edcSales,
                'total_transactions' => $totalTrx,
                'status' => 'closed',
                'notes' => trim(($shift->notes ? $shift->notes . " | " : "") . "[Penutupan Paksa oleh Admin] " . ($request->notes ?? '')),
            ]);

            ActivityLog::log('FORCE_CLOSE_SHIFT', "Admin menutup paksa Shift Kasir '{$shift->shift_number}'. Expected: Rp " . number_format($expectedCash, 0, ',', '.') . ", Actual: Rp " . number_format($actualCash, 0, ',', '.'));

            DB::commit();
            return redirect()->back()->with('success', "Shift Kasir '{$shift->shift_number}' berhasil ditutup oleh Admin.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menutup shift: ' . $e->getMessage());
        }
    }

    public function update(Request $request, CashierShift $shift)
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'actual_cash' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $startingCash = (float) $request->starting_cash;
            $expectedCash = $startingCash + $shift->total_sales_cash;
            
            $data = [
                'starting_cash' => $startingCash,
                'expected_cash' => $expectedCash,
                'notes' => $request->notes,
            ];

            if ($shift->status === 'closed' && $request->filled('actual_cash')) {
                $actualCash = (float) $request->actual_cash;
                $data['actual_cash'] = $actualCash;
                $data['cash_difference'] = $actualCash - $expectedCash;
            }

            $shift->update($data);

            ActivityLog::log('UPDATE_SHIFT', "Memperbarui data Shift Kasir '{$shift->shift_number}'");

            return redirect()->back()->with('success', "Data Shift Kasir '{$shift->shift_number}' berhasil diperbarui!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengedit data shift: ' . $e->getMessage());
        }
    }

    public function destroy(CashierShift $shift)
    {
        try {
            $shiftNumber = $shift->shift_number;
            $shift->delete();

            ActivityLog::log('DELETE_SHIFT', "Menghapus data Shift Kasir '{$shiftNumber}'");

            return redirect()->back()->with('success', "Data Shift Kasir '{$shiftNumber}' berhasil dihapus!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus shift: ' . $e->getMessage());
        }
    }
}
