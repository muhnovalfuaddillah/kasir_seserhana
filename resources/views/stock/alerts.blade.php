@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page & Tabs -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-400 text-3xl">warning</span>
                Notifikasi Stok Minimum & Produk Kadaluarsa (Expired)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Peringatan otomatis untuk produk yang mencapai stok batas minimum dan pelacakan batch produk kadaluarsa.</p>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('stock.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Ringkasan Stok</span>
        </a>
        <a href="{{ route('stock.in') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">call_received</span>
            <span>Stok Masuk</span>
        </a>
        <a href="{{ route('stock.out') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">call_made</span>
            <span>Stok Keluar</span>
        </a>
        <a href="{{ route('stock.opname') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">fact_check</span>
            <span>Stock Opname</span>
        </a>
        <a href="{{ route('stock.transfers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">sync_alt</span>
            <span>Transfer Cabang</span>
        </a>
        <a href="{{ route('stock.history') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">history</span>
            <span>Riwayat Stok</span>
        </a>
        <a href="{{ route('stock.alerts') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-amber-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">warning</span>
            <span>Stok Menipis & Expired</span>
        </a>
    </div>

    <!-- Grid 2 Sections: Left (Low Stock), Right (Expired & Near Expiry) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        
        <!-- Section 1: Low Stock Minimum Alert Table -->
        <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-400">warning</span>
                    Peringatan Stok di Bawah Minimum (Low Stock)
                </h3>
                <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-black border border-amber-500/30">
                    {{ $lowStockProducts->count() }} Produk
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                        <tr>
                            <th class="p-3 rounded-l-xl">Produk</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3 text-right">Stok Batas Minimum</th>
                            <th class="p-3 text-right">Sisa Stok Sekarang</th>
                            <th class="p-3 text-center rounded-r-xl">Aksi Restok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 font-medium">
                        @forelse($lowStockProducts as $lp)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 font-bold text-white max-w-[180px] truncate" title="{{ $lp->name }}">{{ $lp->name }}</td>
                                <td class="p-3 text-slate-400 text-xs">{{ $lp->category->name ?? 'Produk' }}</td>
                                <td class="p-3 text-right font-mono font-bold text-slate-400">{{ $lp->min_stock }} {{ $lp->unit }}</td>
                                <td class="p-3 text-right">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-black font-mono {{ $lp->stock <= 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40 animate-pulse' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40' }}">
                                        {{ $lp->stock <= 0 ? 'HABIS (0)' : $lp->stock . ' ' . $lp->unit }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('stock.in') }}?product_id={{ $lp->id }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">add_box</span>
                                        <span>Restok</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">Aman! Tidak ada produk yang berada di bawah batas minimum stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Expired & Near Expiry Batches -->
        <div class="space-y-6">
            
            <!-- Expired Batches (Sudah Kadaluarsa) -->
            <div class="glass-card rounded-3xl p-5 sm:p-6 border border-rose-500/40 bg-rose-950/20 space-y-4 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-rose-500/30">
                    <h3 class="text-sm sm:text-base font-extrabold text-rose-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-rose-400">event_busy</span>
                        Produk Sudah Kadaluarsa (Expired)
                    </h3>
                    <span class="px-3 py-1 rounded-full bg-rose-500/30 text-rose-300 text-xs font-black border border-rose-500/40">
                        {{ $expiredBatches->count() }} Batch
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-[11px] font-bold text-rose-300 uppercase bg-rose-900/40 border-b border-rose-500/30">
                            <tr>
                                <th class="p-3 rounded-l-xl">Produk</th>
                                <th class="p-3">No. Batch</th>
                                <th class="p-3 text-right">Stok Batch</th>
                                <th class="p-3 text-right">Tgl Expired</th>
                                <th class="p-3 text-center rounded-r-xl">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-500/20 font-medium">
                            @forelse($expiredBatches as $eb)
                                <tr class="hover:bg-rose-900/20 transition-colors">
                                    <td class="p-3 font-bold text-white max-w-[150px] truncate" title="{{ $eb->product->name ?? '-' }}">{{ $eb->product->name ?? '-' }}</td>
                                    <td class="p-3 font-mono text-xs text-rose-200">{{ $eb->batch_number }}</td>
                                    <td class="p-3 text-right font-black font-mono text-rose-400">{{ $eb->stock }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-rose-300">{{ $eb->expired_date ? $eb->expired_date->format('d/m/Y') : '-' }}</td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('stock.out') }}?product_id={{ $eb->product_id }}&reason=Barang+Kadaluarsa+(Expired)" class="px-2.5 py-1 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-[11px] inline-flex items-center gap-1">
                                            <span>Retur / Waste</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400 text-xs">Bersih! Tidak ada barang batch yang kadaluarsa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Near Expiry Batches (Mendekati Kadaluarsa <= 60 Hari) -->
            <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-400">hourglass_top</span>
                        Produk Mendekati Kadaluarsa (&le; 60 Hari)
                    </h3>
                    <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-black border border-amber-500/30">
                        {{ $nearExpiryBatches->count() }} Batch
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                            <tr>
                                <th class="p-3 rounded-l-xl">Produk</th>
                                <th class="p-3">No. Batch</th>
                                <th class="p-3 text-right">Stok Batch</th>
                                <th class="p-3 text-right rounded-r-xl">Tgl Expired</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-medium">
                            @forelse($nearExpiryBatches as $nb)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="p-3 font-bold text-white max-w-[150px] truncate" title="{{ $nb->product->name ?? '-' }}">{{ $nb->product->name ?? '-' }}</td>
                                    <td class="p-3 font-mono text-xs text-slate-400">{{ $nb->batch_number }}</td>
                                    <td class="p-3 text-right font-black font-mono text-amber-400">{{ $nb->stock }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-amber-300">{{ $nb->expired_date ? $nb->expired_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-500 text-xs">Aman! Tidak ada batch produk yang mendekati kadaluarsa dalam 60 hari ke depan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
