@extends('layouts.app')

@section('content')
<main class="p-4 sm:p-6 lg:p-8 space-y-8 max-w-[1600px] mx-auto">
    
    <!-- Hero / Welcome Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-900 via-indigo-950 to-slate-900 p-6 sm:p-8 border border-slate-800/80 shadow-2xl">
        <!-- Decorative Glow Background Elements -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 left-1/3 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 border border-brand-400/30 text-brand-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Shift Operasional • Role: {{ strtoupper(auth()->user()->role) }} • POS Main Store
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Selamat Datang, <span class="bg-gradient-to-r from-brand-300 via-indigo-200 to-cyan-300 bg-clip-text text-transparent">{{ auth()->user()->name }}</span> 👋
                </h1>
                <p class="text-slate-400 text-sm max-w-xl">
                    Berikut adalah ringkasan performa penjualan toko dan aktivitas transaksi kasir secara real-time.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800/90 hover:bg-slate-700/90 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2 transition-all">
                    <span class="material-symbols-outlined text-base">analytics</span>
                    <span>Laporan Rekap</span>
                </a>
                @if(auth()->user()->isKasir())
                    <a href="{{ route('pos.index') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow flex items-center gap-2 transition-all">
                        <span class="material-symbols-outlined text-base">point_of_sale</span>
                        <span>Buka Terminal Kasir</span>
                    </a>
                @else
                    <a href="{{ route('products.index') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow flex items-center gap-2 transition-all">
                        <span class="material-symbols-outlined text-base">inventory_2</span>
                        <span>Kelola Master Produk</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: Total Omset Hari Ini -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:border-brand-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Omset Hari Ini</span>
                <div class="w-10 h-10 rounded-xl bg-brand-500/20 text-brand-400 flex items-center justify-center border border-brand-500/30 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">payments</span>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                <div class="flex items-center gap-2 pt-1">
                    @if($revenueGrowth >= 0)
                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-emerald-500/30">
                            <span class="material-symbols-outlined text-xs">trending_up</span>
                            +{{ $revenueGrowth }}%
                        </span>
                    @else
                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-400 text-xs font-bold border border-rose-500/30">
                            <span class="material-symbols-outlined text-xs">trending_down</span>
                            {{ $revenueGrowth }}%
                        </span>
                    @endif
                    <span class="text-xs text-slate-400">vs kemarin</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Transaksi -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:border-indigo-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center border border-indigo-500/30 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ number_format($todayTransactionsCount) }} <span class="text-sm font-normal text-slate-400">trx</span></p>
                <div class="flex items-center gap-2 pt-1">
                    <span class="text-xs text-slate-400">Rata-rata Rp {{ number_format($avgPerTrx, 0, ',', '.') }}/trx</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Produk Terjual -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:border-cyan-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Item Terjual</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">shopping_bag</span>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ number_format($todayItemsSold) }} <span class="text-sm font-normal text-slate-400">pcs</span></p>
                <div class="flex items-center gap-2 pt-1">
                    <span class="text-xs text-cyan-300 font-semibold truncate">Terlaris: {{ $topProducts->first()->product_name ?? 'Belum ada' }}</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Non-Tunai / QRIS Ratio -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden group hover:border-amber-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Non-Tunai (QRIS/EDC)</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center border border-amber-500/30 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">qr_code_scanner</span>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $qrisRatio }}%</p>
                <div class="flex items-center gap-2 pt-1">
                    <span class="text-xs text-amber-300 font-semibold">Rp {{ number_format($nonCashTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <!-- Admin Quick Access: Kelola Transaksi Toko -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-400">account_balance_wallet</span>
                    Pintasan Pengelolaan Transaksi Toko (Admin)
                </h3>
                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 flex items-center gap-1">
                    <span>Buka Pusat Transaksi</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ route('transactions.index', ['type' => 'penjualan']) }}" class="p-3 rounded-xl bg-slate-900/80 hover:bg-slate-800/80 border border-slate-800 transition-all flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30 group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-base">shopping_cart</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white group-hover:text-emerald-400 transition-colors">Penjualan POS</div>
                        <div class="text-[10px] text-slate-400">Nota kasir</div>
                    </div>
                </a>

                <a href="{{ route('transactions.index', ['type' => 'pengeluaran']) }}" class="p-3 rounded-xl bg-slate-900/80 hover:bg-slate-800/80 border border-slate-800 transition-all flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30 group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-base">payments</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white group-hover:text-rose-400 transition-colors">Pengeluaran</div>
                        <div class="text-[10px] text-slate-400">Catat operasional</div>
                    </div>
                </a>

                <a href="{{ route('transactions.index', ['type' => 'pembelian']) }}" class="p-3 rounded-xl bg-slate-900/80 hover:bg-slate-800/80 border border-slate-800 transition-all flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30 group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-base">local_shipping</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white group-hover:text-cyan-400 transition-colors">Pembelian Stok</div>
                        <div class="text-[10px] text-slate-400">Restock supplier</div>
                    </div>
                </a>

                <a href="{{ route('transactions.index', ['type' => 'bayar_hutang']) }}" class="p-3 rounded-xl bg-slate-900/80 hover:bg-slate-800/80 border border-slate-800 transition-all flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center border border-purple-500/30 group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-base">account_balance_wallet</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white group-hover:text-purple-400 transition-colors">Bayar Hutang</div>
                        <div class="text-[10px] text-slate-400">Pelunasan supplier</div>
                    </div>
                </a>
            </div>
        </div>
    @endif

    <!-- Middle Section: Chart & Payment Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Weekly Sales Chart Container -->
        <div class="lg:col-span-2 glass-card rounded-2xl p-6 space-y-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-400">bar_chart</span>
                        Penjualan Minggu Ini
                    </h2>
                    <p class="text-xs text-slate-400">Grafik omset harian dari Senin s.d. Minggu secara real-time</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">Target: Rp 15M/hari</span>
                </div>
            </div>

            <!-- CSS Bar Chart Visual -->
            <div class="h-64 pt-8 pb-4 flex items-end justify-between gap-2 sm:gap-6 border-b border-slate-800 relative px-2 sm:px-4">
                
                <!-- Y-Axis Background Lines -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-8">
                    <div class="border-b border-slate-800/60 w-full flex justify-between text-[10px] text-slate-500"><span>Max</span></div>
                    <div class="border-b border-slate-800/60 w-full flex justify-between text-[10px] text-slate-500"><span>75%</span></div>
                    <div class="border-b border-slate-800/60 w-full flex justify-between text-[10px] text-slate-500"><span>50%</span></div>
                    <div class="border-b border-slate-800/60 w-full flex justify-between text-[10px] text-slate-500"><span>25%</span></div>
                    <div class="w-full flex justify-between text-[10px] text-slate-500"><span>0</span></div>
                </div>

                @foreach($weeklySales as $sale)
                    @php
                        $heightPercent = $maxWeeklyTotal > 0 ? max(8, min(100, round(($sale['total'] / $maxWeeklyTotal) * 100))) : 8;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-2 group z-10">
                        @if($sale['is_today'])
                            <div class="w-full max-w-[42px] bg-gradient-to-t from-brand-600 to-indigo-400 rounded-t-lg shadow-glow transition-all relative" style="height: {{ $heightPercent }}%;" title="Rp {{ number_format($sale['total'], 0, ',', '.') }}">
                                <div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-brand-600 border border-brand-400 text-white text-[10px] px-2 py-0.5 rounded-md font-bold whitespace-nowrap shadow-lg">
                                    Rp {{ number_format($sale['total'] / 1000000, 1) }}M
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-brand-400">{{ $sale['day'] }} (Hari ini)</span>
                        @elseif($sale['is_future'])
                            <div class="w-full max-w-[42px] bg-slate-800/60 border border-dashed border-slate-700 rounded-t-lg transition-all relative" style="height: 8%;" title="Rp 0"></div>
                            <span class="text-xs font-semibold text-slate-500">{{ $sale['day'] }}</span>
                        @else
                            <div class="w-full max-w-[42px] bg-slate-800 hover:bg-brand-500/80 rounded-t-lg transition-all relative" style="height: {{ $heightPercent }}%;" title="Rp {{ number_format($sale['total'], 0, ',', '.') }}">
                                <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 border border-slate-700 text-slate-200 text-[10px] px-2 py-0.5 rounded-md font-bold whitespace-nowrap transition-opacity shadow-lg">
                                    Rp {{ number_format($sale['total'] / 1000000, 1) }}M
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-slate-400">{{ $sale['day'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Footer Stats -->
            <div class="grid grid-cols-3 gap-4 pt-2 text-center border-t border-slate-800/80">
                <div>
                    <span class="text-[11px] text-slate-400 uppercase font-semibold">Total Mingguan</span>
                    <p class="text-base font-bold text-white">Rp {{ number_format($weeklySum, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 uppercase font-semibold">Rata-rata / Hari</span>
                    <p class="text-base font-bold text-white">Rp {{ number_format(round($weeklySum / 7), 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 uppercase font-semibold">Target Harian</span>
                    <p class="text-base font-bold text-emerald-400">Rp 15.000.000</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Top Payment Methods & Quick Cashier Summary -->
        <div class="space-y-6">
            
            <!-- Payment Method Breakdown -->
            <div class="glass-card rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-slate-200 flex items-center justify-between">
                    <span>Metode Pembayaran Hari Ini</span>
                    <span class="text-xs text-slate-400 font-normal">Realtime</span>
                </h3>

                <div class="space-y-3 pt-2">
                    <!-- QRIS -->
                    <div>
                        <div class="flex justify-between text-xs font-medium mb-1">
                            <span class="text-slate-300 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                QRIS (Digital)
                            </span>
                            <span class="text-white font-bold">{{ $qrisPercent }}% (Rp {{ number_format($qrisTotal, 0, ',', '.') }})</span>
                        </div>
                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full transition-all" style="width: {{ max(0, min(100, $qrisPercent)) }}%"></div>
                        </div>
                    </div>

                    <!-- Tunai -->
                    <div>
                        <div class="flex justify-between text-xs font-medium mb-1">
                            <span class="text-slate-300 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-400"></span>
                                Uang Tunai (Cash)
                            </span>
                            <span class="text-white font-bold">{{ $cashPercent }}% (Rp {{ number_format($cashTotal, 0, ',', '.') }})</span>
                        </div>
                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-400 rounded-full transition-all" style="width: {{ max(0, min(100, $cashPercent)) }}%"></div>
                        </div>
                    </div>

                    <!-- EDC / Debit -->
                    <div>
                        <div class="flex justify-between text-xs font-medium mb-1">
                            <span class="text-slate-300 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                Card Debit / EDC
                            </span>
                            <span class="text-white font-bold">{{ $edcPercent }}% (Rp {{ number_format($edcTotal, 0, ',', '.') }})</span>
                        </div>
                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full transition-all" style="width: {{ max(0, min(100, $edcPercent)) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert Warning Box -->
            <div class="rounded-2xl p-5 bg-gradient-to-r from-amber-950/60 to-rose-950/60 border border-amber-500/30 space-y-3">
                <div class="flex items-center gap-3 text-amber-400">
                    <span class="material-symbols-outlined text-xl">warning</span>
                    <h4 class="text-sm font-bold text-white">Peringatan Stok Penjualan</h4>
                </div>
                <div class="space-y-2 text-xs">
                    @forelse($lowStockProducts as $prod)
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900/60 border border-amber-500/20">
                            <span class="font-medium text-slate-200 truncate max-w-[180px]">{{ $prod->name }}</span>
                            <span class="px-2 py-0.5 rounded {{ $prod->stock <= 2 ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400' }} font-bold">
                                Sisa {{ $prod->stock }} {{ $prod->unit }}
                            </span>
                        </div>
                    @empty
                        <div class="p-2 rounded-xl bg-slate-900/40 text-emerald-400 text-xs font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Semua stok produk saat ini aman (> 5 pcs)
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Transactions Table & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Transactions Table (2 Cols) -->
        <div class="lg:col-span-2 glass-card rounded-2xl p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-400">history</span>
                        Transaksi Terkini Real-Time
                    </h3>
                    <p class="text-xs text-slate-400">Daftar penjualan terbaru dari terminal kasir</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors flex items-center gap-1">
                    Lihat Semua Transaksi
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900/80 border-b border-slate-800">
                        <tr>
                            <th class="p-3.5 rounded-l-xl">No. Nota</th>
                            <th class="p-3.5">Waktu</th>
                            <th class="p-3.5">Kasir</th>
                            <th class="p-3.5">Pembayaran</th>
                            <th class="p-3.5">Total</th>
                            <th class="p-3.5 text-center rounded-r-xl">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @forelse($recentTransactions as $trx)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3.5 font-bold text-white">#{{ $trx->invoice_number }}</td>
                                <td class="p-3.5 text-slate-400">{{ $trx->created_at->format('H:i') }} WIB</td>
                                <td class="p-3.5">{{ $trx->cashier_name }}</td>
                                <td class="p-3.5">
                                    @if($trx->payment_method === 'qris')
                                        <span class="inline-flex items-center gap-1 text-emerald-400">
                                            <span class="material-symbols-outlined text-sm">qr_code</span>
                                            QRIS
                                        </span>
                                    @elseif($trx->payment_method === 'edc')
                                        <span class="inline-flex items-center gap-1 text-amber-400">
                                            <span class="material-symbols-outlined text-sm">credit_card</span>
                                            EDC / Debit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-indigo-400">
                                            <span class="material-symbols-outlined text-sm">payments</span>
                                            Tunai
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 font-bold {{ $trx->status === 'canceled' ? 'text-rose-400 line-through' : 'text-white' }}">
                                    Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="p-3.5 text-center">
                                    @if($trx->status === 'completed')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">Lunas</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">Batal</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-500">Belum ada transaksi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Selling Products List -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <h3 class="text-base font-bold text-white flex items-center justify-between pb-2 border-b border-slate-800">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-400">local_fire_department</span>
                    Produk Terlaris
                </span>
                <span class="text-xs text-slate-400 font-normal">Realtime</span>
            </h3>

            <div class="space-y-3">
                @forelse($topProducts as $index => $top)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800 hover:border-brand-500/30 transition-all">
                        <span class="w-7 h-7 rounded-lg {{ $index === 0 ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-slate-800 text-slate-300 border-slate-700' }} text-xs font-black flex items-center justify-center border">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-white truncate">{{ $top->product_name }}</h4>
                            <p class="text-[11px] text-slate-400">Rp {{ number_format($top->avg_price, 0, ',', '.') }} / item</p>
                        </div>
                        <span class="text-xs font-extrabold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-500/20 shrink-0">
                            {{ $top->total_qty }} terjual
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500 text-xs">Belum ada produk terjual.</div>
                @endforelse
            </div>
        </div>
    </div>
</main>
@endsection