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

        // Batal / Retur Transaksi
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

        // Transaksi Tambahan Admin (Pengeluaran, Pembelian Stok, Bayar Hutang)
        Route::post('/transactions/expense', [TransactionController::class, 'storeExpense'])->name('transactions.store_expense');
        Route::post('/transactions/purchase', [TransactionController::class, 'storePurchase'])->name('transactions.store_purchase');
        Route::post('/transactions/debt-payment', [TransactionController::class, 'storeDebtPayment'])->name('transactions.store_debt');
        Route::post('/transactions/customer-debt-payment', [TransactionController::class, 'storeCustomerDebtPayment'])->name('transactions.store_customer_debt');

        // Laporan Keuangan & HPP Laba Rugi
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
    });
});
