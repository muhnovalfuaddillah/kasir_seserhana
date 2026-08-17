<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\CustomerController;

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout (Authenticated Only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes for Authenticated Users (Admin & Kasir)
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Saya (Admin & Kasir)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ----------------------------------------------------
    // KASIR ONLY ROUTES (Khusus Role Kasir Operasional)
    // ----------------------------------------------------
    Route::middleware('role:kasir')->group(function () {
        // Terminal Kasir POS
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('/pos/quick-product', [PosController::class, 'quickStoreProduct'])->name('pos.quick_product');
    });

    // Riwayat Transaksi (Lihat & Struk)
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // ----------------------------------------------------
    // ADMIN ONLY ROUTES (Khusus Role Admin / Manager)
    // ----------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        // Master Produk Catalog View & Master Produk CRUD
        Route::get('/products-view', [ProductController::class, 'index'])->name('products.view');
        Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
        Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
        Route::resource('customers', CustomerController::class);

        // Modul Manajemen Stok Lengkap
        Route::get('/stock', [StockManagementController::class, 'index'])->name('stock.index');
        Route::get('/stock/in', [StockManagementController::class, 'stockIn'])->name('stock.in');
        Route::post('/stock/in', [StockManagementController::class, 'storeStockIn'])->name('stock.in.store');
        Route::get('/stock/out', [StockManagementController::class, 'stockOut'])->name('stock.out');
        Route::post('/stock/out', [StockManagementController::class, 'storeStockOut'])->name('stock.out.store');
        Route::get('/stock/opname', [StockManagementController::class, 'opname'])->name('stock.opname');
        Route::post('/stock/opname', [StockManagementController::class, 'storeOpname'])->name('stock.opname.store');
        Route::get('/stock/transfers', [StockManagementController::class, 'transfers'])->name('stock.transfers');
        Route::post('/stock/transfers', [StockManagementController::class, 'storeTransfer'])->name('stock.transfers.store');
        Route::post('/stock/transfers/{transfer}/receive', [StockManagementController::class, 'receiveTransfer'])->name('stock.transfers.receive');
        Route::get('/stock/history', [StockManagementController::class, 'history'])->name('stock.history');
        Route::get('/stock/alerts', [StockManagementController::class, 'alerts'])->name('stock.alerts');
        Route::post('/stock/suppliers', [StockManagementController::class, 'storeSupplier'])->name('stock.suppliers.store');
        Route::resource('branches', BranchController::class)->except(['create', 'edit', 'show']);

        // Modul Manajemen Keuangan & Gaji Lengkap
        Route::get('/financial', [FinancialController::class, 'index'])->name('financial.index');
        Route::get('/financial/cash-in', [FinancialController::class, 'cashIn'])->name('financial.cash_in');
        Route::post('/financial/cash-in', [FinancialController::class, 'storeCashIn'])->name('financial.cash_in.store');
        Route::get('/financial/cash-out', [FinancialController::class, 'cashOut'])->name('financial.cash_out');
        Route::post('/financial/cash-out', [FinancialController::class, 'storeCashOut'])->name('financial.cash_out.store');
        Route::get('/financial/categories', [FinancialController::class, 'categories'])->name('financial.categories');
        Route::post('/financial/categories', [FinancialController::class, 'storeCategory'])->name('financial.categories.store');
        Route::delete('/financial/categories/{category}', [FinancialController::class, 'destroyCategory'])->name('financial.categories.destroy');
        Route::get('/financial/payrolls', [FinancialController::class, 'payrolls'])->name('financial.payrolls');
        Route::post('/financial/payrolls', [FinancialController::class, 'storePayroll'])->name('financial.payrolls.store');
        Route::post('/financial/employees', [FinancialController::class, 'storeEmployee'])->name('financial.employees.store');
        Route::get('/financial/cashflow', [FinancialController::class, 'cashflow'])->name('financial.cashflow');
        Route::get('/financial/profit-loss', [FinancialController::class, 'profitLoss'])->name('financial.profit_loss');

        // Batal / Retur Transaksi
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

        // Transaksi Tambahan Admin (Pengeluaran, Pembelian Stok, Bayar Hutang)
        Route::post('/transactions/expense', [TransactionController::class, 'storeExpense'])->name('transactions.store_expense');
        Route::post('/transactions/purchase', [TransactionController::class, 'storePurchase'])->name('transactions.store_purchase');
        Route::post('/transactions/debt-payment', [TransactionController::class, 'storeDebtPayment'])->name('transactions.store_debt');
        Route::post('/transactions/customer-debt-payment', [TransactionController::class, 'storeCustomerDebtPayment'])->name('transactions.store_customer_debt');

        // Pusat Laporan & Analitik Bisnis Toko (8 Sub-Laporan)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily_sales');
        Route::get('/reports/monthly-sales', [ReportController::class, 'monthlySales'])->name('reports.monthly_sales');
        Route::get('/reports/best-sellers', [ReportController::class, 'bestSellers'])->name('reports.best_sellers');
        Route::get('/reports/slow-moving', [ReportController::class, 'slowMoving'])->name('reports.slow_moving');
        Route::get('/reports/stock', [ReportController::class, 'stockReport'])->name('reports.stock');
        Route::get('/reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases');
        Route::get('/reports/expenses', [ReportController::class, 'expensesReport'])->name('reports.expenses');
        Route::get('/reports/net-profit', [ReportController::class, 'netProfitReport'])->name('reports.net_profit');

        // Manajemen User (Super Admin & Admin Only)
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::get('/activity-logs', [UserController::class, 'activityLogs'])->name('users.activity_logs');
    });

    // Manajemen Shift Kasir (Accessible by All Roles)
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts/open', [ShiftController::class, 'openShift'])->name('shifts.open');
    Route::post('/shifts/close', [ShiftController::class, 'closeShift'])->name('shifts.close');
    Route::get('/shifts/{shift}/print', [ShiftController::class, 'printReport'])->name('shifts.print');
    
    // Admin Shift Actions (Edit, Delete, Force Close)
    Route::middleware('role:admin')->group(function () {
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
        Route::post('/shifts/{shift}/force-close', [ShiftController::class, 'forceClose'])->name('shifts.force_close');
    });
});
