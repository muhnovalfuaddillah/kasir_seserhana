<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\CashTransaction;
use App\Models\CashCategory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // 1. Reports Hub Overview
    public function index()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $omsetBulanIni = Transaction::where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $totalTrxBulanIni = Transaction::where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $topSellingProduct = TransactionDetail::whereHas('transaction', function($q) use ($startOfMonth, $endOfMonth) {
            $q->where('type', 'penjualan')
              ->where('status', 'completed')
              ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        })
        ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
        ->groupBy('product_id')
        ->orderByDesc('total_qty')
        ->with('product')
        ->first();

        $totalValuasiStok = Product::selectRaw('SUM(stock * purchase_price) as total_val')->value('total_val') ?? 0;

        return view('reports.index', compact(
            'omsetBulanIni',
            'totalTrxBulanIni',
            'topSellingProduct',
            'totalValuasiStok'
        ));
    }

    // 2. Laporan Penjualan Harian
    public function dailySales(Request $request)
    {
        $date = $request->date ?: date('Y-m-d');

        $transactions = Transaction::with(['user', 'details.product'])
            ->where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalOmset = $transactions->sum('total_amount');
        $totalTrx = $transactions->count();
        $avgOrderValue = $totalTrx > 0 ? $totalOmset / $totalTrx : 0;

        $cashSales = $transactions->where('payment_method', 'cash')->sum('total_amount');
        $qrisSales = $transactions->where('payment_method', 'qris')->sum('total_amount');
        $edcSales = $transactions->where('payment_method', 'edc')->sum('total_amount');

        return view('reports.daily_sales', compact(
            'date',
            'transactions',
            'totalOmset',
            'totalTrx',
            'avgOrderValue',
            'cashSales',
            'qrisSales',
            'edcSales'
        ));
    }

    // 3. Laporan Penjualan Bulanan
    public function monthlySales(Request $request)
    {
        $monthYear = $request->month_year ?: date('Y-m');
        $startOfMonth = Carbon::parse($monthYear . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($monthYear . '-01')->endOfMonth();

        $dailyStats = Transaction::where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as trx_count, SUM(total_amount) as total_omset')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        $totalOmset = $dailyStats->sum('total_omset');
        $totalTrx = $dailyStats->sum('trx_count');

        return view('reports.monthly_sales', compact(
            'monthYear',
            'dailyStats',
            'totalOmset',
            'totalTrx'
        ));
    }

    // 4. Laporan Produk Terlaris (Best Seller)
    public function bestSellers(Request $request)
    {
        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: now()->endOfMonth()->toDateString();

        $products = TransactionDetail::whereHas('transaction', function($q) use ($startDate, $endDate) {
            $q->where('type', 'penjualan')
              ->where('status', 'completed')
              ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        })
        ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
        ->groupBy('product_id')
        ->orderByDesc('total_sold')
        ->with('product.category')
        ->get();

        return view('reports.best_sellers', compact(
            'startDate',
            'endDate',
            'products'
        ));
    }

    // 5. Laporan Produk Tidak Laku (Slow-Moving)
    public function slowMoving(Request $request)
    {
        $startDate = $request->start_date ?: now()->subMonths(1)->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: now()->toDateString();

        // Get product IDs sold in period
        $soldProductIds = TransactionDetail::whereHas('transaction', function($q) use ($startDate, $endDate) {
            $q->where('type', 'penjualan')
              ->where('status', 'completed')
              ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        })->pluck('product_id')->unique();

        // Get products with 0 sales or very low sales
        $slowProducts = Product::with('category')
            ->whereNotIn('id', $soldProductIds)
            ->orderBy('stock', 'desc')
            ->get();

        return view('reports.slow_moving', compact(
            'startDate',
            'endDate',
            'slowProducts'
        ));
    }

    // 6. Laporan Valuasi & Status Stok
    public function stockReport(Request $request)
    {
        $products = Product::with('category')->orderBy('stock', 'asc')->get();

        $totalItems = $products->count();
        $totalStockQty = $products->sum('stock');
        $totalValuationCost = $products->sum(function($p) { return $p->stock * $p->purchase_price; });
        $totalValuationSelling = $products->sum(function($p) { return $p->stock * $p->selling_price; });
        $potentialProfit = $totalValuationSelling - $totalValuationCost;

        $lowStockCount = $products->filter(function($p) { return $p->stock <= 5 && $p->stock > 0; })->count();
        $outOfStockCount = $products->filter(function($p) { return $p->stock <= 0; })->count();

        return view('reports.stock', compact(
            'products',
            'totalItems',
            'totalStockQty',
            'totalValuationCost',
            'totalValuationSelling',
            'potentialProfit',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    // 7. Laporan Pembelian & Restock
    public function purchasesReport(Request $request)
    {
        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: now()->endOfMonth()->toDateString();

        $movements = StockMovement::with(['product', 'branch', 'user'])
            ->where('type', 'in')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPurchaseQty = $movements->sum('qty');
        $totalPurchaseSpend = $movements->sum(function($m) {
            return $m->qty * ($m->product->purchase_price ?? 0);
        });

        return view('reports.purchases', compact(
            'startDate',
            'endDate',
            'movements',
            'totalPurchaseQty',
            'totalPurchaseSpend'
        ));
    }

    // 8. Laporan Pengeluaran & Beban
    public function expensesReport(Request $request)
    {
        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: now()->endOfMonth()->toDateString();

        $expenses = CashTransaction::with(['category', 'user'])
            ->where('type', 'out')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $categoryBreakdown = CashCategory::where('type', 'out')
            ->withSum(['transactions' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }], 'amount')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        return view('reports.expenses', compact(
            'startDate',
            'endDate',
            'expenses',
            'categoryBreakdown',
            'totalExpenses'
        ));
    }

    // 9. Laporan Laba Bersih & Margin %
    public function netProfitReport(Request $request)
    {
        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?: now()->endOfMonth()->toDateString();

        $omsetPos = Transaction::where('type', 'penjualan')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('total_amount');

        $hppPos = TransactionDetail::whereHas('transaction', function($q) use ($startDate, $endDate) {
            $q->where('type', 'penjualan')
              ->where('status', 'completed')
              ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        })->selectRaw('SUM(quantity * purchase_price) as hpp')->value('hpp') ?? 0;

        $labaKotor = $omsetPos - $hppPos;

        $kasMasukLain = CashTransaction::where('type', 'in')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        $totalBebanOps = CashTransaction::where('type', 'out')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        $labaBersih = $labaKotor + $kasMasukLain - $totalBebanOps;
        $profitMargin = $omsetPos > 0 ? ($labaBersih / $omsetPos) * 100 : 0;

        return view('reports.net_profit', compact(
            'startDate',
            'endDate',
            'omsetPos',
            'hppPos',
            'labaKotor',
            'kasMasukLain',
            'totalBebanOps',
            'labaBersih',
            'profitMargin'
        ));
    }
}
