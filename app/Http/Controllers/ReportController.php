<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->getReportData($startDate, $endDate);

        return view('reports.index', array_merge(['startDate' => $startDate, 'endDate' => $endDate], $data));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->getReportData($startDate, $endDate);
        
        $transactions = Transaction::with('details')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reports.pdf', array_merge([
            'startDate' => $startDate, 
            'endDate' => $endDate,
            'transactions' => $transactions
        ], $data));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->getReportData($startDate, $endDate);
        
        $transactions = Transaction::with('details')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $fileName = "Laporan_Penjualan_{$startDate}_sd_{$endDate}.csv";

        return response()->streamDownload(function () use ($startDate, $endDate, $data, $transactions) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Store Header
            fputcsv($handle, ['LAPORAN PENJUALAN & KEUNTUNGAN TOKO - KINETIC POS']);
            fputcsv($handle, ["Periode Laporan: {$startDate} s/d {$endDate}"]);
            fputcsv($handle, ["Tanggal Unduh: " . Carbon::now()->isoFormat('D MMMM Y, HH:mm') . ' WIB']);
            fputcsv($handle, []);

            // Summary Table
            fputcsv($handle, ['--- RINGKASAN FINANSIAL TOKO ---']);
            fputcsv($handle, ['Metrik', 'Nilai (Rp)']);
            fputcsv($handle, ['Total Omset Penjualan', $data['totalRevenue']]);
            fputcsv($handle, ['Total Modal HPP', $data['totalCogs']]);
            fputcsv($handle, ['Keuntungan Bersih (Profit)', $data['netProfit']]);
            fputcsv($handle, ['Total Potongan Diskon', $data['totalDiscount']]);
            fputcsv($handle, ['Total Transaksi Lunas', $data['transactionCount']]);
            fputcsv($handle, ['Pemasukan Tunai (Cash)', $data['cashTotal']]);
            fputcsv($handle, ['Pemasukan QRIS (Digital)', $data['qrisTotal']]);
            fputcsv($handle, ['Pemasukan EDC / Debit', $data['edcTotal']]);
            fputcsv($handle, []);

            // Daily Summary Table
            fputcsv($handle, ['--- RINCIAN PEMASUKAN HARIAN ---']);
            fputcsv($handle, ['Tanggal', 'Jumlah Transaksi', 'Rata-rata/Trx (Rp)', 'Total Omset (Rp)']);
            foreach ($data['dailyReports'] as $day) {
                $avg = $day->count > 0 ? round($day->revenue / $day->count) : 0;
                fputcsv($handle, [
                    $day->date,
                    $day->count,
                    $avg,
                    $day->revenue
                ]);
            }
            fputcsv($handle, []);

            // Transactions Detail Table
            fputcsv($handle, ['--- RINCIAN SELURUH TRANSAKSI ---']);
            fputcsv($handle, ['No Invoice', 'Waktu Transaksi', 'Nama Kasir', 'Nama Pelanggan', 'Metode Pembayaran', 'Status', 'Total Tagihan (Rp)']);

            foreach ($transactions as $trx) {
                fputcsv($handle, [
                    $trx->invoice_number,
                    $trx->created_at->format('Y-m-d H:i:s'),
                    $trx->cashier_name,
                    $trx->customer_name,
                    strtoupper($trx->payment_method),
                    strtoupper($trx->status),
                    $trx->total_amount
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    private function getReportData($startDate, $endDate)
    {
        $transactionsQuery = Transaction::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'completed');

        $totalRevenue = (clone $transactionsQuery)->sum('total_amount');
        $transactionCount = (clone $transactionsQuery)->count();
        $totalDiscount = (clone $transactionsQuery)->sum('discount_amount');

        $detailsQuery = TransactionDetail::whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate)
              ->where('status', 'completed');
        });

        $totalCogs = (clone $detailsQuery)->selectRaw('SUM(purchase_price * quantity) as total_cogs')->value('total_cogs') ?? 0;
        $netProfit = $totalRevenue - $totalCogs;

        $cashTotal = (clone $transactionsQuery)->where('payment_method', 'cash')->sum('total_amount');
        $qrisTotal = (clone $transactionsQuery)->where('payment_method', 'qris')->sum('total_amount');
        $edcTotal = (clone $transactionsQuery)->where('payment_method', 'edc')->sum('total_amount');

        $dailyReports = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as revenue')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return [
            'totalRevenue' => $totalRevenue,
            'transactionCount' => $transactionCount,
            'totalDiscount' => $totalDiscount,
            'totalCogs' => $totalCogs,
            'netProfit' => $netProfit,
            'cashTotal' => $cashTotal,
            'qrisTotal' => $qrisTotal,
            'edcTotal' => $edcTotal,
            'dailyReports' => $dailyReports,
        ];
    }
}
