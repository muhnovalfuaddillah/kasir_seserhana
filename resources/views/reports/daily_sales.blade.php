@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-400 text-3xl">today</span>
                Laporan Penjualan Harian
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Rincian omset penjualan per tanggal, metode pembayaran, rata-rata transaksi, & transaksi nota.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="exportReportToExcel('dailySalesTable', 'Laporan_Penjualan_Harian_{{ $date }}.csv')" class="px-4 py-2.5 rounded-2xl bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
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
        <a href="{{ route('reports.daily_sales') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-emerald-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">today</span>
            <span>Penjualan Harian</span>
        </a>
        <a href="{{ route('reports.monthly_sales') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
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

    <!-- Filter Date Picker -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800 shadow-xl">
        <form action="{{ route('reports.daily_sales') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Pilih Tanggal Laporan Harian</label>
                <input type="date" name="date" value="{{ $date }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800">
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-glow flex items-center gap-1">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Tampilkan Laporan</span>
            </button>
        </form>
    </div>

    <!-- 4 Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Omset Harian</span>
            <div class="text-2xl font-black text-emerald-400 font-mono">Rp {{ number_format($totalOmset, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Tanggal: {{ date('d/m/Y', strtotime($date)) }}</p>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Transaksi Nota</span>
            <div class="text-2xl font-black text-white font-mono">{{ $totalTrx }} Nota</div>
            <p class="text-[11px] text-slate-400">Transaksi Selesai</p>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rata-Rata Nota (AOV)</span>
            <div class="text-2xl font-black text-cyan-300 font-mono">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Rata-rata per pelanggan</p>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Metode Bayar (Tunai vs Non-Tunai)</span>
            <div class="text-xs font-mono space-y-0.5 text-slate-300 pt-1">
                <div class="flex justify-between"><span>Tunai:</span> <strong class="text-emerald-400">Rp {{ number_format($cashSales, 0, ',', '.') }}</strong></div>
                <div class="flex justify-between"><span>QRIS:</span> <strong class="text-cyan-400">Rp {{ number_format($qrisSales, 0, ',', '.') }}</strong></div>
                <div class="flex justify-between"><span>EDC:</span> <strong class="text-purple-400">Rp {{ number_format($edcSales, 0, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Table Transactions -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-emerald-400">receipt_long</span>
            Rincian Nota Transaksi Tanggal {{ date('d/m/Y', strtotime($date)) }}
        </h3>

        <div class="overflow-x-auto">
            <table id="dailySalesTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Invoice</th>
                        <th class="p-3.5">Jam</th>
                        <th class="p-3.5">Kasir / Operator</th>
                        <th class="p-3.5 text-center">Metode Bayar</th>
                        <th class="p-3.5 text-right">Total Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-cyan-300">{{ $trx->invoice_number }}</td>
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $trx->created_at->format('H:i:s') }}</td>
                            <td class="p-3.5 font-bold text-white">{{ $trx->user->name ?? 'Kasir' }}</td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ strtoupper($trx->payment_method) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-right font-black font-mono text-emerald-400 text-sm">
                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">Tidak ada transaksi penjualan pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
