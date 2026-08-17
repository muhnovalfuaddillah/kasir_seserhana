<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Kinetic POS - System Kasir Toko Modern, Penjualan POS Real-time, Manajemen Stok, Keuangan & Laporan Toko Pro." />
    <meta name="robots" content="index, follow" />
    <meta property="og:title" content="Kinetic POS - Dashboard Kasir Modern" />
    <meta property="og:description" content="Sistem Kasir Pro dengan performa tinggi, manajemen stok, transaksi POS, dan laporan analitik keuangan toko." />
    <meta property="og:type" content="website" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <title>@yield('title', 'Kinetic POS - Dashboard Kasir Modern')</title>
    
    <!-- Google Fonts (Full Material Symbols & Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- Local Vendor Styles & App CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}" />
    @vite(['resources/css/app.css'])
</head>

@php
    $isPosPage = request()->routeIs('pos.*');
@endphp
<body class="bg-[#0b0f19] text-gray-100 font-sans antialiased selection:bg-brand-500 selection:text-white min-h-screen">

    <!-- Mobile / Desktop Overlay -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" role="button" tabindex="0" aria-label="Tutup Overlay Sidebar" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden transition-opacity"></div>

    <!-- Sidebar Navbar (Disembunyikan Otomatis di Halaman POS Agar Layar Kasir Maksimal Raksasa) -->
    <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-[#0f172a] border-r border-slate-800/80 z-50 flex flex-col transition-transform duration-300 -translate-x-full {{ $isPosPage ? '' : 'lg:translate-x-0' }}">
        
        <!-- Brand Header -->
        <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800/80">
            <a href="{{ route('dashboard') }}" aria-label="Halaman Dashboard Kinetic POS" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-cyan-400 flex items-center justify-center text-white shadow-glow group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl font-bold">point_of_sale</span>
                </div>
                <div>
                    <span class="font-bold text-lg leading-none text-white tracking-wide flex items-center gap-1">
                        Kinetic<span class="text-brand-400 font-extrabold">POS</span>
                    </span>
                    <span class="text-[11px] font-medium text-slate-400 tracking-wider uppercase block">System Kasir Pro</span>
                </div>
            </a>
            <button onclick="toggleSidebar()" aria-label="Tutup Sidebar Menu" class="lg:hidden text-slate-400 hover:text-white p-1">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-5 space-y-4 overflow-y-auto">
            
            <!-- SECTION 1: OPERASIONAL TOKO -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Operasional Toko</div>
                
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-brand-600 to-indigo-600 text-white shadow-glow' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    <span class="material-symbols-outlined text-xl">grid_view</span>
                    <span>Dashboard</span>
                    @if(request()->routeIs('dashboard'))
                        <span class="ml-auto w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    @endif
                </a>

                @if(auth()->user()->isKasir())
                    <!-- Terminal POS Kasir (Kasir Only) -->
                    <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('pos.index') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-glow-emerald' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                        <span class="material-symbols-outlined text-xl {{ request()->routeIs('pos.index') ? '' : 'group-hover:text-emerald-400' }} transition-colors">shopping_cart</span>
                        <span>Terminal POS Kasir</span>
                        <span class="ml-auto px-2 py-0.5 text-[10px] font-bold bg-emerald-500/20 text-emerald-400 rounded-md border border-emerald-500/30 uppercase tracking-wider">Live</span>
                    </a>
                @endif

                <!-- Shift Buka/Tutup Kas (All Roles) -->
                <a href="{{ route('shifts.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('shifts.*') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                    <span class="material-symbols-outlined text-xl {{ request()->routeIs('shifts.*') ? 'text-emerald-400' : 'group-hover:text-emerald-400' }} transition-colors">point_of_sale</span>
                    <span>Shift Buka/Tutup Kas</span>
                </a>

                <!-- Riwayat / Kelola Transaksi Toko -->
                <details class="group border border-slate-800/80 rounded-xl bg-slate-900/60 overflow-hidden transition-all" {{ request()->routeIs('transactions.index') ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3.5 py-2.5 font-semibold text-xs text-slate-300 hover:text-white cursor-pointer select-none transition-all hover:bg-slate-800/60">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-xl text-amber-400">receipt_long</span>
                            <span>{{ auth()->user()->isAdmin() ? 'Kelola Transaksi Toko' : 'Riwayat Transaksi' }}</span>
                        </div>
                        <span class="material-symbols-outlined text-sm text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                    </summary>

                    <div class="px-2 pb-2 pt-1 space-y-1 bg-slate-950/60 border-t border-slate-800/60">
                        <a href="{{ route('transactions.index', ['type' => 'all']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->query('type') === 'all' || (request()->routeIs('transactions.index') && !request()->has('type')) ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30 shadow-glow' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="material-symbols-outlined text-base text-amber-400">receipt_long</span>
                            <span>Semua Transaksi</span>
                        </a>
                        <a href="{{ route('transactions.index', ['type' => 'penjualan']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->query('type') === 'penjualan' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="material-symbols-outlined text-base text-emerald-400">shopping_cart</span>
                            <span>Penjualan POS</span>
                        </a>
                        <a href="{{ route('transactions.index', ['type' => 'pengeluaran']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->query('type') === 'pengeluaran' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="material-symbols-outlined text-base text-rose-400">payments</span>
                            <span>Pengeluaran Operasional</span>
                        </a>
                        <a href="{{ route('transactions.index', ['type' => 'pembelian']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->query('type') === 'pembelian' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="material-symbols-outlined text-base text-cyan-400">local_shipping</span>
                            <span>Pembelian Stok</span>
                        </a>
                        <a href="{{ route('transactions.index', ['type' => 'bayar_hutang']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->query('type') === 'bayar_hutang' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="material-symbols-outlined text-base text-purple-400">account_balance_wallet</span>
                            <span>Bayar Hutang / Kasbon</span>
                        </a>
                    </div>
                </details>
            </div>

            <!-- SECTION 2: MASTER DATA & PELANGGAN -->
            <div class="space-y-1 pt-2 border-t border-slate-800/60">
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Master Data</div>
                
                <!-- Master Data Toko (Dropdown Option Accordion) -->
                <details class="group border border-slate-800/80 rounded-xl bg-slate-900/60 overflow-hidden transition-all" {{ request()->routeIs('products.*') || request()->routeIs('categories.*') || request()->routeIs('customers.*') ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3.5 py-2.5 font-semibold text-xs text-slate-300 hover:text-white cursor-pointer select-none transition-all hover:bg-slate-800/60">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-xl text-indigo-400">database</span>
                            <span>Master Data Toko</span>
                        </div>
                        <span class="material-symbols-outlined text-sm text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                    </summary>

                    <!-- Sub-Menu Options -->
                    <div class="px-2 pb-2 pt-1 space-y-1 bg-slate-950/60 border-t border-slate-800/60">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('products.index') ? 'bg-brand-500/20 text-brand-300 border border-brand-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-brand-400">inventory_2</span>
                                <span>Master Produk & Grosir</span>
                            </a>

                            <a href="{{ route('categories.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('categories.index') ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-cyan-400">category</span>
                                <span>Master Kategori Produk</span>
                            </a>
                        @endif

                        <a href="{{ route('customers.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('customers.*') ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <span class="material-symbols-outlined text-base text-indigo-400">groups</span>
                            <span>Pelanggan & Limit Kasbon</span>
                        </a>
                    </div>
                </details>
            </div>

            <!-- SECTION 3: MANAJEMEN STOK & GUDANG (Admin Only) -->
            @if(auth()->user()->isAdmin())
                <div class="space-y-1 pt-2 border-t border-slate-800/60">
                    <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Persediaan Stok</div>
                    
                    <details class="group border border-slate-800/80 rounded-xl bg-slate-900/60 overflow-hidden transition-all" {{ request()->routeIs('stock.*') || request()->routeIs('branches.*') ? 'open' : '' }}>
                        <summary class="flex items-center justify-between px-3.5 py-2.5 font-semibold text-xs text-slate-300 hover:text-white cursor-pointer select-none transition-all hover:bg-slate-800/60">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-xl text-emerald-400">inventory</span>
                                <span>Manajemen Stok & Gudang</span>
                            </div>
                            <span class="material-symbols-outlined text-sm text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                        </summary>

                        <div class="px-2 pb-2 pt-1 space-y-1 bg-slate-950/60 border-t border-slate-800/60">
                            <a href="{{ route('stock.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('stock.index') ? 'bg-brand-500/20 text-brand-300 border border-brand-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-brand-400">dashboard</span>
                                <span>Ringkasan Stok</span>
                            </a>
                            <a href="{{ route('stock.in') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('stock.in') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-emerald-400">add_box</span>
                                <span>Stok Masuk (Restock)</span>
                            </a>
                            <a href="{{ route('stock.out') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('stock.out') ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-rose-400">indeterminate_check_box</span>
                                <span>Stok Keluar (Waste)</span>
                            </a>
                            <a href="{{ route('stock.opname') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('stock.opname') ? 'bg-brand-500/20 text-brand-300 border border-brand-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-brand-400">fact_check</span>
                                <span>Stock Opname</span>
                            </a>
                            <a href="{{ route('stock.transfers') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('stock.transfers') ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-cyan-400">sync_alt</span>
                                <span>Transfer Cabang</span>
                            </a>
                            <a href="{{ route('stock.alerts') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('stock.alerts') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-amber-400">warning</span>
                                <span>Stok Menipis & Expired</span>
                            </a>
                            <a href="{{ route('branches.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('branches.index') ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-cyan-400">store</span>
                                <span>Pengaturan Cabang</span>
                            </a>
                        </div>
                    </details>
                </div>

                <!-- SECTION 4: KEUANGAN & LAPORAN (Admin Only) -->
                <div class="space-y-1 pt-2 border-t border-slate-800/60">
                    <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Keuangan & Analitik</div>

                    <!-- Keuangan & Gaji -->
                    <details class="group border border-slate-800/80 rounded-xl bg-slate-900/60 overflow-hidden transition-all mb-1" {{ request()->routeIs('financial.*') ? 'open' : '' }}>
                        <summary class="flex items-center justify-between px-3.5 py-2.5 font-semibold text-xs text-slate-300 hover:text-white cursor-pointer select-none transition-all hover:bg-slate-800/60">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-xl text-emerald-400">account_balance_wallet</span>
                                <span>Keuangan & Gaji</span>
                            </div>
                            <span class="material-symbols-outlined text-sm text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                        </summary>

                        <div class="px-2 pb-2 pt-1 space-y-1 bg-slate-950/60 border-t border-slate-800/60">
                            <a href="{{ route('financial.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('financial.index') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-emerald-400">dashboard</span>
                                <span>Ringkasan Keuangan</span>
                            </a>
                            <a href="{{ route('financial.cash_in') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('financial.cash_in') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-emerald-400">south_east</span>
                                <span>Kas Masuk</span>
                            </a>
                            <a href="{{ route('financial.cash_out') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('financial.cash_out') ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-rose-400">north_east</span>
                                <span>Kas Keluar</span>
                            </a>
                            <a href="{{ route('financial.categories') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('financial.categories') ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-cyan-400">category</span>
                                <span>Kategori Pengeluaran</span>
                            </a>
                            <a href="{{ route('financial.payrolls') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('financial.payrolls') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-purple-400">badge</span>
                                <span>Gaji Karyawan</span>
                            </a>
                        </div>
                    </details>

                    <!-- Laporan (Dropdown Accordion) -->
                    <details class="group border border-slate-800/80 rounded-xl bg-slate-900/60 overflow-hidden transition-all" {{ request()->routeIs('reports.*') ? 'open' : '' }}>
                        <summary class="flex items-center justify-between px-3.5 py-2.5 font-semibold text-xs text-slate-300 hover:text-white cursor-pointer select-none transition-all hover:bg-slate-800/60">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-xl text-brand-400">analytics</span>
                                <span>Laporan</span>
                            </div>
                            <span class="material-symbols-outlined text-sm text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                        </summary>

                        <div class="px-2 pb-2 pt-1 space-y-1 bg-slate-950/60 border-t border-slate-800/60">
                            <a href="{{ route('reports.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.index') ? 'bg-brand-500/20 text-brand-300 border border-brand-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-brand-400">dashboard</span>
                                <span>Semua Laporan</span>
                            </a>
                            <a href="{{ route('reports.daily_sales') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.daily_sales') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-emerald-400">today</span>
                                <span>Penjualan Harian</span>
                            </a>
                            <a href="{{ route('reports.monthly_sales') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.monthly_sales') ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-cyan-400">calendar_month</span>
                                <span>Penjualan Bulanan</span>
                            </a>
                            <a href="{{ route('reports.best_sellers') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.best_sellers') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-amber-400">local_fire_department</span>
                                <span>Produk Terlaris</span>
                            </a>
                            <a href="{{ route('reports.slow_moving') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.slow_moving') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-purple-400">inventory_2</span>
                                <span>Produk Tidak Laku</span>
                            </a>
                            <a href="{{ route('reports.stock') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.stock') ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-blue-400">warehouse</span>
                                <span>Laporan Stok Barang</span>
                            </a>
                            <a href="{{ route('reports.purchases') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.purchases') ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-teal-400">local_shipping</span>
                                <span>Laporan Pembelian Stok</span>
                            </a>
                            <a href="{{ route('reports.expenses') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.expenses') ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-rose-400">payments</span>
                                <span>Laporan Pengeluaran</span>
                            </a>
                            <a href="{{ route('financial.cashflow') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('financial.cashflow') ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-cyan-400">water_drop</span>
                                <span>Laporan Arus Kas</span>
                            </a>
                            <a href="{{ route('financial.profit_loss') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('financial.profit_loss') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-purple-400">analytics</span>
                                <span>Laporan Laba Rugi</span>
                            </a>
                            <a href="{{ route('reports.net_profit') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('reports.net_profit') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                                <span class="material-symbols-outlined text-base text-emerald-400">trending_up</span>
                                <span>Laporan Laba Bersih</span>
                            </a>
                        </div>
                    </details>
                </div>

                <!-- SECTION 5: PENGATURAN SYSTEM & USER (Admin Only) -->
                <div class="space-y-1 pt-2 border-t border-slate-800/60">
                    <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Pengaturan System</div>

                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('users.index') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                        <span class="material-symbols-outlined text-xl {{ request()->routeIs('users.index') ? 'text-brand-400' : 'group-hover:text-brand-400' }} transition-colors">badge</span>
                        <span>Data Karyawan & User Login</span>
                    </a>

                    <a href="{{ route('users.activity_logs') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('users.activity_logs') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                        <span class="material-symbols-outlined text-xl {{ request()->routeIs('users.activity_logs') ? 'text-purple-400' : 'group-hover:text-purple-400' }} transition-colors">shield</span>
                        <span>Log Aktivitas System</span>
                    </a>
                </div>
            @endif

            <!-- SECTION 6: PROFIL -->
            <div class="space-y-1 pt-2 border-t border-slate-800/60">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('profile.edit') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                    <span class="material-symbols-outlined text-xl {{ request()->routeIs('profile.edit') ? 'text-brand-400' : 'group-hover:text-brand-400' }} transition-colors">account_circle</span>
                    <span>Profil Saya</span>
                </a>
            </div>
        </nav>

        <!-- User Profile Card Sidebar Footer -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/50">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('profile.edit') }}" aria-label="Profil User {{ auth()->user()->name }}" class="flex items-center gap-3 min-w-0 flex-1 group hover:opacity-80 transition-opacity">
                    <div class="relative shrink-0">
                        <img class="w-10 h-10 rounded-full object-cover ring-2 ring-brand-500/50 group-hover:ring-brand-400" 
                            width="40" height="40" loading="lazy" decoding="async"
                            src="{{ auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : asset('storage/' . auth()->user()->avatar)) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop' }}" 
                            alt="Foto Profil {{ auth()->user()->name }}">
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-slate-900"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate group-hover:text-brand-300 transition-colors">{{ auth()->user()->name }}</p>
                        <span class="inline-block text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md {{ auth()->user()->isAdmin() ? 'bg-brand-500/20 text-brand-300 border border-brand-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                            Role: {{ auth()->user()->role }}
                        </span>
                    </div>
                </a>

                <!-- Logout Form Trigger -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" aria-label="Logout dari sistem" class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" title="Logout dari sistem">
                        <span class="material-symbols-outlined text-xl">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Top Navigation Header -->
    <header class="fixed top-0 right-0 left-0 {{ $isPosPage ? '' : 'lg:left-64' }} h-20 bg-[#0f172a]/90 backdrop-blur-md border-b border-slate-800/80 z-30 flex items-center justify-between px-4 sm:px-8">
        
        <!-- Left Section: Menu Toggle & Store Title -->
        <div class="flex items-center gap-4 flex-1 max-w-xl">
            <button onclick="toggleSidebar()" aria-label="Buka Menu Sistem" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-black transition-all shadow-md">
                <span class="material-symbols-outlined text-lg text-brand-400">menu</span>
                <span>Menu System</span>
            </button>
            <div class="hidden sm:flex items-center gap-2 text-sm text-slate-400 font-medium">
                <span class="material-symbols-outlined text-brand-400">storefront</span>
                <span>Toko Utama • Cabang Jakarta Pusat</span>
            </div>
        </div>

        <!-- Right Section: Quick Stats, Date Time & POS Shortcut -->
        <div class="flex items-center gap-3 sm:gap-4">
            
            <!-- Quick Date/Time Pill -->
            <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900/80 border border-slate-800 text-xs font-medium text-slate-300">
                <span class="material-symbols-outlined text-brand-400 text-base">calendar_today</span>
                <span id="currentDate">Senin, 10 Ags 2026</span>
                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                <span id="currentTime" class="font-bold text-white">09:00 WIB</span>
            </div>

            <!-- User Badge Link -->
            <a href="{{ route('profile.edit') }}" aria-label="Profil Akun {{ auth()->user()->name }}" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-brand-500/50 text-xs font-semibold text-slate-200 hover:text-white transition-all">
                <span class="material-symbols-outlined text-emerald-400 text-base">account_circle</span>
                <span>{{ auth()->user()->name }}</span>
            </a>

            @if(auth()->user()->isKasir())
                <!-- Quick Action POS Button for Kasir -->
                <a href="{{ route('pos.index') }}" aria-label="Buka Terminal Kasir POS" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs shadow-glow-emerald transition-all transform active:scale-95">
                    <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                    <span>+ Buka Kasir</span>
                </a>
            @else
                <!-- Quick Action Master Produk for Admin -->
                <a href="{{ route('products.index') }}" aria-label="Buka Master Produk Toko" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold text-xs shadow-glow transition-all transform active:scale-95">
                    <span class="material-symbols-outlined text-lg">inventory_2</span>
                    <span>+ Master Produk</span>
                </a>
            @endif
        </div>
    </header>

    <!-- Main Content Container (Margin 0 di POS Kasir Agar Layar Sangat Luas) -->
    <main class="{{ $isPosPage ? '' : 'lg:ml-64' }} pt-20 min-h-screen">
        @if(session('success'))
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" aria-label="Tutup notifikasi sukses" class="text-emerald-400 hover:text-emerald-200">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" aria-label="Tutup notifikasi error" class="text-rose-400 hover:text-rose-200">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
            
            const timeEl = document.getElementById('currentTime');
            const dateEl = document.getElementById('currentDate');
            if (timeEl) timeEl.textContent = timeStr;
            if (dateEl) dateEl.textContent = dateStr;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // AUTO LAZY LOAD JQUERY & SELECT2 ONLY WHEN NEEDED BY VIEW (.select-searchable or window.needsJQuery)
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.select-searchable') || window.needsJQuery) {
                const jq = document.createElement('script');
                jq.src = "{{ asset('vendor/jquery/jquery.min.js') }}";
                jq.onload = function() {
                    if (document.querySelector('.select-searchable')) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = "{{ asset('vendor/select2/select2.min.css') }}";
                        document.head.appendChild(link);

                        const s2 = document.createElement('script');
                        s2.src = "{{ asset('vendor/select2/select2.min.js') }}";
                        s2.onload = function() {
                            if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                                $('.select-searchable').each(function() {
                                    const ph = $(this).data('placeholder') || '-- Cari & Pilih --';
                                    $(this).select2({
                                        width: '100%',
                                        placeholder: ph,
                                        allowClear: true
                                    });
                                });
                            }
                        };
                        document.body.appendChild(s2);
                    }
                };
                document.body.appendChild(jq);
            }
        });

        // AUTO FORMAT RUPIAH CURRENCY INPUT (.input-currency) LIVE THOUSAND SEPARATORS
        function parseRupiahNumber(val) {
            if (val === null || val === undefined || val === '') return 0;
            if (typeof val === 'number') return Math.round(val);

            let str = val.toString().trim();
            if (!str) return 0;

            // Handle DB decimal strings ending with .00 or .0 (e.g. "1000.00" -> 1000)
            if (/\.\d{1,2}$/.test(str) && !str.includes(',')) {
                let parsed = parseFloat(str);
                if (!isNaN(parsed)) return Math.round(parsed);
            }

            // Otherwise strip thousand separator dots
            let clean = str.replace(/\./g, '').replace(/,/g, '.');
            let parsed = parseFloat(clean);
            return isNaN(parsed) ? 0 : Math.round(parsed);
        }

        function formatRupiah(value) {
            if (value === null || value === undefined || value === '') return '';
            let num = parseRupiahNumber(value);
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function unformatRupiah(value) {
            if (value === null || value === undefined || value === '') return '';
            return parseRupiahNumber(value);
        }

        document.addEventListener('input', function(e) {
            if (e.target && (e.target.classList.contains('input-currency') || e.target.dataset.type === 'currency')) {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;
                e.target.value = formatRupiah(e.target.value);
                let newLength = e.target.value.length;
                let newPosition = cursorPosition + (newLength - originalLength);
                if (newPosition >= 0 && newPosition <= newLength) {
                    e.target.setSelectionRange(newPosition, newPosition);
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.input-currency, [data-type="currency"]').forEach(function(el) {
                if (el.value) {
                    el.value = formatRupiah(el.value);
                }
            });
        });

        // Strip thousand separator dots before form submission so backend receives raw numeric values
        document.addEventListener('submit', function(e) {
            e.target.querySelectorAll('.input-currency, [data-type="currency"]').forEach(function(el) {
                el.value = unformatRupiah(el.value);
            });
        });

        // GLOBAL EXPORT TABLE TO EXCEL (.csv / .xls with UTF-8 BOM)
        function exportReportToExcel(tableId, filename = 'Laporan_Toko.csv') {
            const table = document.getElementById(tableId);
            if (!table) {
                alert('Tabel data laporan tidak ditemukan!');
                return;
            }
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                // Skip rows with class no-export
                if (rows[i].classList.contains('no-export')) continue;

                for (let j = 0; j < cols.length; j++) {
                    // Skip action column or no-export cells
                    if (cols[j].classList.contains('no-export')) continue;
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                if (row.length > 0) {
                    csv.push(row.join(';'));
                }
            }
            
            const csvFile = new Blob(["\ufeff" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const downloadLink = document.createElement('a');
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>

    <!-- App JS (Only 0.45 kB) -->
    @vite(['resources/js/app.js'])
</body>
</html>
