@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-400 text-3xl">payments</span>
                Laporan Pengeluaran & Beban Operasional Toko
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Rincian beban biaya operasional yang dikelompokkan berdasarkan kategori pengeluaran.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="exportReportToExcel('expensesReportTable', 'Laporan_Pengeluaran_{{ $startDate }}_s_d_{{ $endDate }}.csv')" class="px-4 py-2.5 rounded-2xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Download Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
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
        <a href="{{ route('reports.purchases') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">local_shipping</span>
            <span>Laporan Pembelian</span>
        </a>
        <a href="{{ route('reports.expenses') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-rose-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">payments</span>
            <span>Laporan Pengeluaran</span>
        </a>
        <a href="{{ route('reports.net_profit') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">trending_up</span>
            <span>Laporan Laba Bersih</span>
        </a>
    </div>

    <!-- Filter Date Range -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800 shadow-xl">
        <form action="{{ route('reports.expenses') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Periode Tanggal Laporan</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                    <span class="text-slate-500 font-bold">s/d</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-glow flex items-center gap-1">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Tampilkan Pengeluaran</span>
            </button>
        </form>
    </div>

    <!-- Summary Box -->
    <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Seluruh Pengeluaran Operasional</span>
        <div class="text-3xl font-black text-rose-400 font-mono">-Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
        <p class="text-[11px] text-slate-400">Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</p>
    </div>

    <!-- Category Breakdown Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($categoryBreakdown as $cb)
            @if(($cb->transactions_sum_amount ?? 0) > 0)
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-extrabold text-white">{{ $cb->name }}</h4>
                        <p class="text-[11px] text-slate-400 line-clamp-1">{{ $cb->description ?: 'Beban Operasional' }}</p>
                    </div>
                    <div class="text-sm font-black font-mono text-rose-400 shrink-0">
                        Rp {{ number_format($cb->transactions_sum_amount, 0, ',', '.') }}
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Table Expenses -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <div class="overflow-x-auto">
            <table id="expensesReportTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Transaksi</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Kategori Beban</th>
                        <th class="p-3.5 text-right">Nominal</th>
                        <th class="p-3.5">No. Nota / Ref</th>
                        <th class="p-3.5">Keterangan</th>
                        <th class="p-3.5 rounded-r-xl">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($expenses as $ex)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-cyan-300">{{ $ex->transaction_number }}</td>
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $ex->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3.5 font-bold text-white">{{ $ex->category->name ?? 'Operasional' }}</td>
                            <td class="p-3.5 text-right font-black font-mono text-rose-400 text-sm">
                                -Rp {{ number_format($ex->amount, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-400 text-xs">{{ $ex->reference_number ?: '-' }}</td>
                            <td class="p-3.5 text-slate-400 max-w-[200px] truncate" title="{{ $ex->notes }}">{{ $ex->notes ?: '-' }}</td>
                            <td class="p-3.5 text-slate-400 text-xs truncate max-w-[100px]">{{ $ex->user->name ?? 'Admin' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">Tidak ada pengeluaran pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
