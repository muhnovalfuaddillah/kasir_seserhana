<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // 1. Today's Total Revenue & Growth
        $todayRevenue = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $yesterdayRevenue = Transaction::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('total_amount');

        $revenueGrowth = $yesterdayRevenue > 0 
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1) 
            : 0;

        // 2. Today's Transaction Count
        $todayTransactionsCount = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        $avgPerTrx = $todayTransactionsCount > 0 ? round($todayRevenue / $todayTransactionsCount) : 0;

        // 3. Today's Sold Items Count
        $todayItemsSold = TransactionDetail::whereHas('transaction', function ($q) use ($today) {
            $q->whereDate('created_at', $today)->where('status', 'completed');
        })->sum('quantity');

        // 4. Payment Method Breakdown
        $qrisTotal = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->where('payment_method', 'qris')
            ->sum('total_amount');

        $edcTotal = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->where('payment_method', 'edc')
            ->sum('total_amount');

        $cashTotal = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $nonCashTotal = $qrisTotal + $edcTotal;
        $qrisRatio = $todayRevenue > 0 ? round(($nonCashTotal / $todayRevenue) * 100, 1) : 0;
        $qrisPercent = $todayRevenue > 0 ? round(($qrisTotal / $todayRevenue) * 100, 1) : 0;
        $edcPercent = $todayRevenue > 0 ? round(($edcTotal / $todayRevenue) * 100, 1) : 0;
        $cashPercent = $todayRevenue > 0 ? round(($cashTotal / $todayRevenue) * 100, 1) : 0;

        // 5. Recent Transactions
        $recentTransactions = Transaction::with('details')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // 6. Top Selling Products Today (Fallback to all-time if today empty)
        $topProducts = TransactionDetail::whereHas('transaction', function ($q) use ($today) {
                $q->whereDate('created_at', $today)->where('status', 'completed');
            })
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('AVG(selling_price) as avg_price'))
            ->groupBy('product_name')
            ->orderBy('total_qty', 'desc')
            ->take(4)
            ->get();

        if ($topProducts->isEmpty()) {
            $topProducts = TransactionDetail::whereHas('transaction', function ($q) {
                    $q->where('status', 'completed');
                })
                ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('AVG(selling_price) as avg_price'))
                ->groupBy('product_name')
                ->orderBy('total_qty', 'desc')
                ->take(4)
                ->get();
        }

        // 7. Low Stock Alerts
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        // 8. Weekly Sales Data (Mon-Sun)
        $startOfWeek = Carbon::now()->startOfWeek();
        $weeklySales = [];
        $weeklySum = 0;
        $maxWeeklyTotal = 0;

        for ($i = 0; $i < 7; $i++) {
            $date = (clone $startOfWeek)->addDays($i);
            $dailyTotal = Transaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            if ($dailyTotal > $maxWeeklyTotal) {
                $maxWeeklyTotal = $dailyTotal;
            }
            $weeklySum += $dailyTotal;

            $weeklySales[] = [
                'day' => $date->isoFormat('ddd'),
                'full_date' => $date->format('Y-m-d'),
                'is_today' => $date->isToday(),
                'is_future' => $date->isFuture(),
                'total' => $dailyTotal,
            ];
        }

        return view('Dashboard', compact(
            'todayRevenue',
            'revenueGrowth',
            'todayTransactionsCount',
            'avgPerTrx',
            'todayItemsSold',
            'qrisRatio',
            'nonCashTotal',
            'qrisTotal',
            'qrisPercent',
            'edcTotal',
            'edcPercent',
            'cashTotal',
            'cashPercent',
            'recentTransactions',
            'topProducts',
            'lowStockProducts',
            'weeklySales',
            'weeklySum',
            'maxWeeklyTotal'
        ));
    }
}
