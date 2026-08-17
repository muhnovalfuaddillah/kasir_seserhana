@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page & Tabs -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400 text-3xl">analytics</span>
                Laporan Laba Rugi Komprehensif (Income Statement)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Rincian pendapatan omset penjualan POS, Beban Pokok Penjualan (HPP), Laba Kotor, beban operasional, dan kalkulasi Laba Bersih presisi.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="exportReportToExcel('profitLossReportTable', 'Laporan_Laba_Rugi_{{ $startDate }}_s_d_{{ $endDate }}.csv')" class="px-4 py-2.5 rounded-2xl bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Download Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">print</span>
                <span>Cetak PDF</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('financial.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Ringkasan Keuangan</span>
        </a>
        <a href="{{ route('financial.cash_in') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">south_east</span>
            <span>Kas Masuk</span>
        </a>
        <a href="{{ route('financial.cash_out') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">north_east</span>
            <span>Kas Keluar</span>
        </a>
        <a href="{{ route('financial.categories') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">category</span>
            <span>Kategori Pengeluaran</span>
        </a>
        <a href="{{ route('financial.payrolls') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">badge</span>
            <span>Gaji Karyawan</span>
        </a>
        <a href="{{ route('financial.cashflow') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">water_drop</span>
            <span>Laporan Arus Kas</span>
        </a>
        <a href="{{ route('financial.profit_loss') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-purple-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">analytics</span>
            <span>Laporan Laba Rugi</span>
        </a>
    </div>

    <!-- Filter Date Range -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800 shadow-xl">
        <form action="{{ route('financial.profit_loss') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Periode Laporan Laba Rugi</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                    <span class="text-slate-500 font-bold">s/d</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-glow flex items-center gap-1">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Hitung Laba Rugi</span>
            </button>
        </form>
    </div>

    <!-- Profit & Loss Statement Card -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 space-y-6 shadow-2xl">
        <div class="text-center space-y-1 pb-4 border-b border-slate-800">
            <h2 class="text-xl font-black text-white uppercase tracking-wider">KINETIC POS - LAPORAN LABA RUGI TOKO</h2>
            <p class="text-xs text-slate-400 font-mono">Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</p>
        </div>

        <div class="overflow-x-auto">
            <table id="profitLossReportTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">Deskripsi Komponen Laba Rugi</th>
                        <th class="p-3.5 text-right rounded-r-xl">Nominal Rupiah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    <!-- 1. PENDAPATAN -->
                    <tr class="bg-emerald-950/20 font-bold text-emerald-400">
                        <td colspan="2" class="p-3 font-sans uppercase">1. PENDAPATAN OPERASIONAL (REVENUE)</td>
                    </tr>
                    <tr>
                        <td class="p-3 pl-6 text-slate-300">Penjualan Kotor Kasir POS (Omset)</td>
                        <td class="p-3 text-right font-mono text-white font-bold">Rp {{ number_format($omsetPos, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 pl-6 text-slate-300">Pemasukan Kas Non-POS Lainnya</td>
                        <td class="p-3 text-right font-mono text-emerald-400 font-bold">+Rp {{ number_format($kasMasukLain, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="font-bold text-emerald-300 bg-slate-900/60">
                        <td class="p-3 uppercase">TOTAL PENDAPATAN OPERASIONAL</td>
                        <td class="p-3 text-right font-mono">Rp {{ number_format($omsetPos + $kasMasukLain, 0, ',', '.') }}</td>
                    </tr>

                    <!-- 2. HPP -->
                    <tr class="bg-cyan-950/20 font-bold text-cyan-400">
                        <td colspan="2" class="p-3 font-sans uppercase">2. HARGA POKOK PENJUALAN (HPP / COGS)</td>
                    </tr>
                    <tr>
                        <td class="p-3 pl-6 text-slate-300">Total Harga Beli / Modal Barang Terjual (HPP)</td>
                        <td class="p-3 text-right font-mono text-rose-400 font-bold">-Rp {{ number_format($hppPos, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="font-bold text-cyan-300 bg-cyan-950/40 border border-cyan-500/40">
                        <td class="p-3.5 uppercase">LABA KOTOR (GROSS PROFIT)</td>
                        <td class="p-3.5 text-right font-mono text-lg text-cyan-300">Rp {{ number_format($labaKotor + $kasMasukLain, 0, ',', '.') }}</td>
                    </tr>

                    <!-- 3. BEBAN OPERASIONAL -->
                    <tr class="bg-rose-950/20 font-bold text-rose-400">
                        <td colspan="2" class="p-3 font-sans uppercase">3. BEBAN OPERASIONAL & GAJI KARYAWAN (EXPENSES)</td>
                    </tr>
                    @foreach($expenseCategories as $ec)
                        @if(($ec->transactions_sum_amount ?? 0) > 0)
                            <tr>
                                <td class="p-3 pl-6 text-slate-300">{{ $ec->name }}</td>
                                <td class="p-3 text-right font-mono text-rose-400 font-semibold">-Rp {{ number_format($ec->transactions_sum_amount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="font-bold text-rose-300 bg-slate-900/60">
                        <td class="p-3 uppercase">TOTAL BEBAN OPERASIONAL</td>
                        <td class="p-3 text-right font-mono">-Rp {{ number_format($totalOperasional, 0, ',', '.') }}</td>
                    </tr>

                    <!-- 4. NET PROFIT -->
                    <tr class="{{ $labaBersih >= 0 ? 'bg-gradient-to-r from-emerald-950 via-slate-900 to-teal-950' : 'bg-gradient-to-r from-rose-950 via-slate-900 to-amber-950' }} font-black text-base">
                        <td class="p-4 font-sans uppercase text-white">HASIL LABA BERSIH (NET PROFIT)</td>
                        <td class="p-4 text-right font-mono text-xl {{ $labaBersih >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
