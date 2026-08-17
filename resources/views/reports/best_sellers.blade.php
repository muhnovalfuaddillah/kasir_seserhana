@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-400 text-3xl">local_fire_department</span>
                Laporan Produk Terlaris (Best Seller)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Peringkat barang paling laku berdasarkan volume Qty Terjual & Total Omset yang dihasilkan.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="exportReportToExcel('bestSellersTable', 'Laporan_Produk_Terlaris_{{ $startDate }}_s_d_{{ $endDate }}.csv')" class="px-4 py-2.5 rounded-2xl bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white border border-amber-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Download Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
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
        <a href="{{ route('reports.monthly_sales') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">calendar_month</span>
            <span>Penjualan Bulanan</span>
        </a>
        <a href="{{ route('reports.best_sellers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-amber-600 text-white shadow-glow transition-all flex items-center gap-2">
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

    <!-- Filter Date Range -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800 shadow-xl">
        <form action="{{ route('reports.best_sellers') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Periode Tanggal Laporan</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                    <span class="text-slate-500 font-bold">s/d</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                </div>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-glow flex items-center gap-1">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Tampilkan Peringkat</span>
            </button>
        </form>
    </div>

    <!-- Table Best Sellers Ranking -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <div class="overflow-x-auto">
            <table id="bestSellersTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 text-center rounded-l-xl">Peringkat</th>
                        <th class="p-3.5">Nama Produk</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5 text-center">Total Qty Terjual</th>
                        <th class="p-3.5 text-right">Harga Jual Satuan</th>
                        <th class="p-3.5 text-right rounded-r-xl">Total Omset Dihasilkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($products as $idx => $p)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 text-center">
                                @if($idx == 0)
                                    <span class="w-7 h-7 rounded-full bg-amber-400 text-slate-950 font-black inline-flex items-center justify-center text-xs shadow-lg">1</span>
                                @elseif($idx == 1)
                                    <span class="w-7 h-7 rounded-full bg-slate-300 text-slate-950 font-black inline-flex items-center justify-center text-xs shadow">2</span>
                                @elseif($idx == 2)
                                    <span class="w-7 h-7 rounded-full bg-amber-700 text-white font-black inline-flex items-center justify-center text-xs shadow">3</span>
                                @else
                                    <span class="font-mono font-bold text-slate-400">#{{ $idx + 1 }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-bold text-white">
                                <div>{{ $p->product->name ?? 'Produk' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $p->product->barcode ?? '' }}</div>
                            </td>
                            <td class="p-3.5 text-slate-300 font-medium">{{ $p->product->category->name ?? 'Umum' }}</td>
                            <td class="p-3.5 text-center font-mono font-black text-amber-400 text-sm">
                                {{ $p->total_sold }} {{ $p->product->unit ?? 'pcs' }}
                            </td>
                            <td class="p-3.5 text-right font-mono text-slate-300">
                                Rp {{ number_format($p->product->selling_price ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-black font-mono text-emerald-400 text-sm">
                                Rp {{ number_format($p->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data penjualan produk pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
