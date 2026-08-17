@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400 text-3xl">calendar_month</span>
                Laporan Penjualan Bulanan
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Tren akumulasi omset harian dalam 1 bulan, performa jumlah nota, dan total omset bulanan.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="exportReportToExcel('monthlySalesTable', 'Laporan_Penjualan_Bulanan_{{ $monthYear }}.csv')" class="px-4 py-2.5 rounded-2xl bg-cyan-600/20 hover:bg-cyan-600 text-cyan-300 hover:text-white border border-cyan-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Download Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">print</span>
                <span>Cetak PDF</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Semua Laporan</span>
        </a>
        <a href="{{ route('reports.daily_sales') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">today</span>
            <span>Penjualan Harian</span>
        </a>
        <a href="{{ route('reports.monthly_sales') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-cyan-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">calendar_month</span>
            <span>Penjualan Bulanan</span>
        </a>
        <a href="{{ route('reports.best_sellers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">local_fire_department</span>
            <span>Produk Terlaris</span>
        </a>
        <a href="{{ route('reports.slow_moving') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">inventory_2</span>
            <span>Produk Tidak Laku</span>
        </a>
        <a href="{{ route('reports.stock') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">warehouse</span>
            <span>Laporan Stok</span>
        </a>
    </div>

    <!-- Filter Month Picker -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800 shadow-xl">
        <form action="{{ route('reports.monthly_sales') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Pilih Bulan & Tahun Laporan</label>
                <input type="month" name="month_year" value="{{ $monthYear }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800">
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-glow flex items-center gap-1">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Tampilkan Bulanan</span>
            </button>
        </form>
    </div>

    <!-- 2 Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Omset Bulan {{ date('F Y', strtotime($monthYear . '-01')) }}</span>
            <div class="text-3xl font-black text-cyan-300 font-mono">Rp {{ number_format($totalOmset, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Akumulasi Penjualan Bersih</p>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Transaksi Nota Bulanan</span>
            <div class="text-3xl font-black text-white font-mono">{{ $totalTrx }} Transaksi</div>
            <p class="text-[11px] text-slate-400">Total Nota Terproses</p>
        </div>
    </div>

    <!-- Table Daily Breakdown -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-cyan-400">bar_chart</span>
            Rincian Omset Penjualan Per Hari ({{ date('F Y', strtotime($monthYear . '-01')) }})
        </h3>

        <div class="overflow-x-auto">
            <table id="monthlySalesTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">Tanggal</th>
                        <th class="p-3.5 text-center">Jumlah Nota</th>
                        <th class="p-3.5 text-right">Rata-Rata per Nota</th>
                        <th class="p-3.5 text-right rounded-r-xl">Total Omset Harian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($dailyStats as $ds)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-white">{{ date('d/m/Y', strtotime($ds->date)) }}</td>
                            <td class="p-3.5 text-center font-bold text-slate-300">{{ $ds->trx_count }} Nota</td>
                            <td class="p-3.5 text-right font-mono text-slate-400">
                                Rp {{ number_format($ds->trx_count > 0 ? $ds->total_omset / $ds->trx_count : 0, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-black font-mono text-cyan-300 text-sm">
                                Rp {{ number_format($ds->total_omset, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500">Tidak ada transaksi penjualan pada bulan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
