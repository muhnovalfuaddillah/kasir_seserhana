@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-400 text-3xl">trending_up</span>
                Laporan Laba Bersih & Margin Keuntungan (Net Profit & Margin %)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Analisis Laba Bersih bersih dan kalkulasi rasio Profit Margin % dari total pendapatan toko.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="exportReportToExcel('netProfitReportTable', 'Laporan_Laba_Bersih_{{ $startDate }}_s_d_{{ $endDate }}.csv')" class="px-4 py-2.5 rounded-2xl bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Download Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
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
        <a href="{{ route('reports.expenses') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">payments</span>
            <span>Laporan Pengeluaran</span>
        </a>
        <a href="{{ route('reports.net_profit') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-emerald-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">trending_up</span>
            <span>Laporan Laba Bersih</span>
        </a>
    </div>

    <!-- Filter Date Range -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800 shadow-xl">
        <form action="{{ route('reports.net_profit') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Periode Laporan Laba Bersih</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                    <span class="text-slate-500 font-bold">s/d</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-glow flex items-center gap-1">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Hitung Margin Laba</span>
            </button>
        </form>
    </div>

    <!-- 3 Net Profit KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-2 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Omset Penjualan (Revenue)</span>
            <div class="text-3xl font-black text-white font-mono">Rp {{ number_format($omsetPos, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Penjualan Bersih Kasir POS</p>
        </div>

        <div class="glass-card p-6 rounded-3xl border border-emerald-500/40 bg-emerald-950/30 space-y-2 shadow-2xl">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-400">Hasil Laba Bersih (Net Profit)</span>
            <div class="text-3xl font-black {{ $labaBersih >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-mono">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400">Omset - HPP - Seluruh Pengeluaran</p>
        </div>

        <div class="glass-card p-6 rounded-3xl border border-cyan-500/40 bg-cyan-950/30 space-y-2 shadow-2xl">
            <span class="text-xs font-black uppercase tracking-wider text-cyan-300">Profit Margin %</span>
            <div class="text-3xl font-black text-cyan-300 font-mono">
                {{ number_format($profitMargin, 2, ',', '.') }}%
            </div>
            <p class="text-[11px] text-slate-400">Persentase Keuntungan Bersih dari Omset</p>
        </div>
    </div>

    <!-- Breakdown Table -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-emerald-400">equalizer</span>
            Rincian Kalkulasi Laba Bersih & Profit Margin
        </h3>

        <div class="overflow-x-auto">
            <table id="netProfitReportTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">Komponen Keuangan</th>
                        <th class="p-3.5 text-right rounded-r-xl">Nominal Rupiah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium font-mono text-sm">
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3.5 font-bold font-sans text-white">1. Total Omset Penjualan POS (Revenue)</td>
                        <td class="p-3.5 text-right font-bold text-white">+Rp {{ number_format($omsetPos, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3.5 font-bold font-sans text-rose-300">2. HPP / Modal Barang Terjual (Cost of Goods Sold)</td>
                        <td class="p-3.5 text-right font-bold text-rose-400">-Rp {{ number_format($hppPos, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-cyan-950/30 font-bold text-cyan-300">
                        <td class="p-3.5 font-sans uppercase">Subtotal Laba Kotor (Gross Profit)</td>
                        <td class="p-3.5 text-right">Rp {{ number_format($labaKotor, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3.5 font-bold font-sans text-emerald-300">3. Pemasukan Kas Non-POS (Kas Masuk)</td>
                        <td class="p-3.5 text-right font-bold text-emerald-400">+Rp {{ number_format($kasMasukLain, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="hover:bg-slate-800/40">
                        <td class="p-3.5 font-bold font-sans text-rose-300">4. Total Pengeluaran Operasional & Gaji (Expenses)</td>
                        <td class="p-3.5 text-right font-bold text-rose-400">-Rp {{ number_format($totalBebanOps, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gradient-to-r from-emerald-950 via-slate-900 to-teal-950 font-black text-base">
                        <td class="p-4 font-sans uppercase text-emerald-300">TOTAL HASIL LABA BERSIH (NET PROFIT)</td>
                        <td class="p-4 text-right text-emerald-400 font-mono text-xl">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
