@extends('layouts.app')

@section('content')
<main class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-[1600px] mx-auto">
    
    <!-- Page Header & Date Range Form -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400">analytics</span>
                Laporan Penjualan & Keuntungan
            </h1>
            <p class="text-xs text-slate-400">Analisis statistik pendapatan omset, modal (HPP), dan perhitungan laba bersih toko</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Date Range Filter Form -->
            <form action="{{ route('reports.index') }}" method="GET" class="glass-card p-2 rounded-2xl flex flex-wrap items-center gap-2 border border-slate-800">
                <div class="flex items-center gap-2 px-2">
                    <span class="text-xs text-slate-400 font-semibold">Periode:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-slate-900 text-xs text-white rounded-lg px-2.5 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <span class="text-xs text-slate-500">s/d</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-slate-900 text-xs text-white rounded-lg px-2.5 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
                <button type="submit" class="px-4 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow">
                    Filter
                </button>
            </form>

            <!-- Export Excel Button -->
            <a href="{{ route('reports.export_excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-4 py-2.5 rounded-2xl bg-emerald-600/90 hover:bg-emerald-500 text-white text-xs font-bold shadow-glow-emerald flex items-center gap-2 transition-all transform active:scale-95">
                <span class="material-symbols-outlined text-base">table_chart</span>
                <span>Export Excel</span>
            </a>

            <!-- Export / Cetak PDF Button -->
            <a href="{{ route('reports.export_pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg flex items-center gap-2 transition-all transform active:scale-95">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak / Export PDF</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Revenue -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Omset Penjualan</span>
            <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 font-medium">{{ $transactionCount }} Transaksi Sukses</p>
        </div>

        <!-- Total COGS (HPP / Modal) -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Modal Barang (HPP)</span>
            <p class="text-2xl sm:text-3xl font-extrabold text-amber-400 tracking-tight">Rp {{ number_format($totalCogs, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 font-medium">Harga Beli Produk Terjual</p>
        </div>

        <!-- Net Profit -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden space-y-2 border-emerald-500/30">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Keuntungan Bersih (Profit)</span>
            <p class="text-2xl sm:text-3xl font-extrabold text-emerald-400 tracking-tight">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
            <p class="text-xs text-emerald-400 font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">trending_up</span>
                Omset dikurangi Modal HPP
            </p>
        </div>

        <!-- Total Diskon Ditanggung -->
        <div class="glass-card rounded-2xl p-6 relative overflow-hidden space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Potongan Diskon</span>
            <p class="text-2xl sm:text-3xl font-extrabold text-cyan-400 tracking-tight">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 font-medium">Diskon Diberikan ke Pelanggan</p>
        </div>
    </div>

    <!-- Payment Breakdown & Daily Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Payment Channel Distribution -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <h3 class="text-base font-bold text-white pb-2 border-b border-slate-800">
                Pemasukan Berdasarkan Payment Method
            </h3>

            <div class="space-y-4 pt-2">
                <!-- Cash -->
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white">Tunai (Cash)</h4>
                            <p class="text-[11px] text-slate-400">Pemasukan fisik kasir</p>
                        </div>
                    </div>
                    <span class="text-sm font-extrabold text-white">Rp {{ number_format($cashTotal, 0, ',', '.') }}</span>
                </div>

                <!-- QRIS -->
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <span class="material-symbols-outlined">qr_code_scanner</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white">QRIS (Digital)</h4>
                            <p class="text-[11px] text-slate-400">Transfer instant QRIS</p>
                        </div>
                    </div>
                    <span class="text-sm font-extrabold text-white">Rp {{ number_format($qrisTotal, 0, ',', '.') }}</span>
                </div>

                <!-- EDC -->
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                            <span class="material-symbols-outlined">credit_card</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white">EDC / Kartu Debit</h4>
                            <p class="text-[11px] text-slate-400">Mesin gesek EDC</p>
                        </div>
                    </div>
                    <span class="text-sm font-extrabold text-white">Rp {{ number_format($edcTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Daily Breakdown Table (2 Cols) -->
        <div class="lg:col-span-2 glass-card rounded-2xl p-6 space-y-4">
            <h3 class="text-base font-bold text-white pb-2 border-b border-slate-800">
                Rincian Pemasukan Harian
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900/80 border-b border-slate-800">
                        <tr>
                            <th class="p-3.5 rounded-l-xl">Tanggal</th>
                            <th class="p-3.5">Jumlah Transaksi</th>
                            <th class="p-3.5">Rata-rata / Trx</th>
                            <th class="p-3.5 rounded-r-xl text-right">Total Omset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @forelse($dailyReports as $day)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3.5 font-bold text-white">{{ \Carbon\Carbon::parse($day->date)->isoFormat('D MMMM Y') }}</td>
                                <td class="p-3.5">{{ $day->count }} transaksi</td>
                                <td class="p-3.5 text-slate-400">Rp {{ number_format($day->count > 0 ? $day->revenue / $day->count : 0, 0, ',', '.') }}</td>
                                <td class="p-3.5 font-extrabold text-emerald-400 text-right">Rp {{ number_format($day->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-500 font-semibold">
                                    Tidak ada data transaksi pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
