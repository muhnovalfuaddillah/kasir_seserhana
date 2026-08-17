@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400 text-3xl">inventory_2</span>
                Manajemen Stok & Gudang
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Pantau dan kelola pergerakan stok barang, stok masuk, stok keluar, stock opname, transfer antar cabang, dan kadaluarsa.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('stock.in') }}" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">add_box</span>
                <span>+ Stok Masuk</span>
            </a>
            <a href="{{ route('stock.out') }}" class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">indeterminate_check_box</span>
                <span>- Stok Keluar</span>
            </a>
            <a href="{{ route('stock.opname') }}" class="px-4 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-glow transition-all">
                <span class="material-symbols-outlined text-lg">fact_check</span>
                <span>Stock Opname</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('stock.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Ringkasan Stok</span>
        </a>
        <a href="{{ route('stock.in') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">call_received</span>
            <span>Stok Masuk</span>
        </a>
        <a href="{{ route('stock.out') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">call_made</span>
            <span>Stok Keluar</span>
        </a>
        <a href="{{ route('stock.opname') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">fact_check</span>
            <span>Stock Opname</span>
        </a>
        <a href="{{ route('stock.transfers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">sync_alt</span>
            <span>Transfer Cabang</span>
        </a>
        <a href="{{ route('stock.history') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">history</span>
            <span>Riwayat / Kartu Stok</span>
        </a>
        <a href="{{ route('stock.alerts') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all flex items-center gap-2 relative">
            <span class="material-symbols-outlined text-lg text-amber-400">warning</span>
            <span>Stok Menipis & Expired</span>
            @if($lowStockProducts->count() > 0 || $expiredBatches->count() > 0)
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
            @endif
        </a>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total SKU / Produk -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Jenis Barang</span>
                <div class="w-10 h-10 rounded-2xl bg-brand-500/20 text-brand-400 flex items-center justify-center border border-brand-500/30">
                    <span class="material-symbols-outlined text-xl">view_in_ar</span>
                </div>
            </div>
            <div class="text-3xl font-black text-white font-mono">{{ number_format($totalProducts) }} <span class="text-xs font-semibold text-slate-400">SKU</span></div>
            <p class="text-[11px] text-slate-400">Terdaftar di master katalog</p>
        </div>

        <!-- Card 2: Total Fisik Unit Stok -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Unit Barang</span>
                <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30">
                    <span class="material-symbols-outlined text-xl">inventory</span>
                </div>
            </div>
            <div class="text-3xl font-black text-cyan-300 font-mono">{{ number_format($totalStockQty) }} <span class="text-xs font-semibold text-slate-400">Unit</span></div>
            <p class="text-[11px] text-slate-400">Total kuantitas barang fisik</p>
        </div>

        <!-- Card 3: Total Nilai Aset Stok -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Nilai Aset Stok</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                    <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Dihitung dari (Stok x Harga Beli)</p>
        </div>

        <!-- Card 4: Peringatan Stok Menipis & Expired -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Peringatan Stok & Expired</span>
                <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xl font-black text-amber-400 font-mono">{{ number_format($lowStockProducts->count()) }} <span class="text-[10px] font-bold text-slate-400">Menipis</span></div>
                <span class="text-slate-600">•</span>
                <div class="text-xl font-black text-rose-400 font-mono">{{ number_format($expiredBatches->count()) }} <span class="text-[10px] font-bold text-slate-400">Expired</span></div>
            </div>
            <a href="{{ route('stock.alerts') }}" class="text-[11px] font-bold text-brand-400 hover:underline flex items-center gap-1">
                <span>Lihat Detail Peringatan</span>
                <span class="material-symbols-outlined text-xs">arrow_forward</span>
            </a>
        </div>
    </div>

    <!-- Main Grid: Recent Movements & Low Stock Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Log Pergerakan Stok Terkini (2 Cols) -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-5 border border-slate-800 space-y-4 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-400">history</span>
                    Riwayat Pergerakan Stok Terkini
                </h3>
                <a href="{{ route('stock.history') }}" class="text-xs font-bold text-brand-400 hover:underline">Selengkapnya &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                        <tr>
                            <th class="p-3 rounded-l-xl">Waktu</th>
                            <th class="p-3">Produk</th>
                            <th class="p-3 text-center">Tipe</th>
                            <th class="p-3 text-right">Perubahan (Qty)</th>
                            <th class="p-3 text-right">Stok Akhir</th>
                            <th class="p-3 rounded-r-xl">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 font-medium">
                        @forelse($recentMovements as $m)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 font-mono text-slate-400 text-[11px]">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3 font-bold text-white max-w-[180px] truncate" title="{{ $m->product->name ?? '-' }}">{{ $m->product->name ?? '-' }}</td>
                                <td class="p-3 text-center">
                                    @if($m->type === 'in')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">MASUK</span>
                                    @elseif($m->type === 'out')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">KELUAR</span>
                                    @elseif($m->type === 'opname')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-brand-500/20 text-brand-300 border border-brand-500/30">OPNAME</span>
                                    @elseif($m->type === 'transfer')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">TRANSFER</span>
                                    @elseif($m->type === 'sale')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30">PENJUALAN</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-800 text-slate-300">{{ strtoupper($m->type) }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right font-black font-mono {{ $m->qty > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $m->qty > 0 ? "+{$m->qty}" : $m->qty }}
                                </td>
                                <td class="p-3 text-right font-bold font-mono text-white">{{ number_format($m->stock_after) }}</td>
                                <td class="p-3 text-slate-400 text-xs truncate max-w-[100px]">{{ $m->user->name ?? 'Sistem' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">Belum ada riwayat pergerakan stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Low Stock Alert Card List (1 Col) -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-4 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-400">warning</span>
                    Perlu Restok Segera
                </h3>
                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-black">{{ $lowStockProducts->count() }} Item</span>
            </div>

            <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                @forelse($lowStockProducts as $lp)
                    <div class="p-3 rounded-2xl bg-slate-900/90 border border-slate-800 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-bold text-white truncate" title="{{ $lp->name }}">{{ $lp->name }}</h4>
                            <p class="text-[10px] text-slate-400 font-mono">Min: {{ $lp->min_stock }} {{ $lp->unit }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $lp->stock <= 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40' }}">
                                {{ $lp->stock <= 0 ? 'HABIS' : 'Sisa ' . $lp->stock }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-xs">
                        🎉 Semua stok produk aman (di atas batas minimum).
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
