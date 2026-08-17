@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-400 text-3xl">warehouse</span>
                Laporan Valuasi Aset & Status Stok Persediaan
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Rincian nilai aset stok persediaan gudang (Harga Modal vs Harga Jual), proyeksi laba kotor, dan alert stok menipis.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="exportReportToExcel('stockReportTable', 'Laporan_Valuasi_Stok_Persediaan.csv')" class="px-4 py-2.5 rounded-2xl bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Download Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
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
        <a href="{{ route('reports.best_sellers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">local_fire_department</span>
            <span>Produk Terlaris</span>
        </a>
        <a href="{{ route('reports.slow_moving') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">inventory_2</span>
            <span>Produk Tidak Laku</span>
        </a>
        <a href="{{ route('reports.stock') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-blue-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">warehouse</span>
            <span>Laporan Stok</span>
        </a>
    </div>

    <!-- 4 KPI Stock Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Valuasi Modal Aset</span>
            <div class="text-2xl font-black text-blue-400 font-mono">Rp {{ number_format($totalValuationCost, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Stok × Harga Beli (Modal)</p>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Valuasi Harga Jual</span>
            <div class="text-2xl font-black text-cyan-300 font-mono">Rp {{ number_format($totalValuationSelling, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Proyeksi Laba: Rp {{ number_format($potentialProfit, 0, ',', '.') }}</p>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Fisik Unit Stok</span>
            <div class="text-2xl font-black text-white font-mono">{{ number_format($totalStockQty, 0, ',', '.') }} Unit</div>
            <p class="text-[11px] text-slate-400">Dari {{ $totalItems }} Jenis Produk</p>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-slate-800 space-y-1 shadow-xl">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Peringatan Stok</span>
            <div class="text-xs font-mono space-y-0.5 text-slate-300 pt-1">
                <div class="flex justify-between"><span>Stok Menipis (<=5):</span> <strong class="text-amber-400">{{ $lowStockCount }} Item</strong></div>
                <div class="flex justify-between"><span>Stok Habis (0):</span> <strong class="text-rose-400">{{ $outOfStockCount }} Item</strong></div>
            </div>
        </div>
    </div>

    <!-- Table Inventory Valuation -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <div class="overflow-x-auto">
            <table id="stockReportTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">Nama Produk</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5 text-center">Sisa Stok</th>
                        <th class="p-3.5 text-right">Harga Modal</th>
                        <th class="p-3.5 text-right">Harga Jual</th>
                        <th class="p-3.5 text-right">Valuasi Modal</th>
                        <th class="p-3.5 text-right rounded-r-xl">Proyeksi Nilai Jual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($products as $p)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-bold text-white">
                                <div>{{ $p->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $p->barcode }}</div>
                            </td>
                            <td class="p-3.5 text-slate-300">{{ $p->category->name ?? 'Umum' }}</td>
                            <td class="p-3.5 text-center font-mono font-black text-sm {{ $p->stock <= 0 ? 'text-rose-400' : ($p->stock <= 5 ? 'text-amber-400' : 'text-emerald-400') }}">
                                {{ $p->stock }} {{ $p->unit }}
                            </td>
                            <td class="p-3.5 text-right font-mono text-slate-400">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono text-white">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono font-bold text-blue-300">
                                Rp {{ number_format($p->stock * $p->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-black font-mono text-cyan-300 text-sm">
                                Rp {{ number_format($p->stock * $p->selling_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">Belum ada produk di database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
