@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page & Tabs -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400 text-3xl">history</span>
                Riwayat Pergerakan Stok (Stock Movement Audit Trail)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Jejak audit lengkap pergerakan kartu stok untuk setiap barang (penjualan, stok masuk, stok keluar, opname, dan transfer).</p>
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
        <a href="{{ route('stock.history') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">history</span>
            <span>Riwayat / Kartu Stok</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800 shadow-xl">
        <form action="{{ route('stock.history') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">Filter Produk</label>
                <select name="product_id" data-placeholder="-- Semua Produk --" class="select-searchable w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value=""></option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">Filter Tipe Pergerakan</label>
                <select name="type" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="all">-- Semua Tipe --</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Stok Masuk (Supplier)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Stok Keluar (Waste)</option>
                    <option value="opname" {{ request('type') === 'opname' ? 'selected' : '' }}>Stock Opname</option>
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfer Cabang</option>
                    <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Penjualan Kasir POS</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">Tanggal Mulai & Sampai</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-2.5 py-2 border border-slate-800">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-slate-950 text-xs text-white rounded-xl px-2.5 py-2 border border-slate-800">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-glow flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-base">filter_alt</span>
                    <span>Filter Log</span>
                </button>
                <a href="{{ route('stock.history') }}" class="px-3 py-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white text-xs font-bold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-brand-400">history_edu</span>
            Jejak Audit Kartu Stok (Stock Card Movement)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">Waktu Log</th>
                        <th class="p-3.5">Nama Produk</th>
                        <th class="p-3.5 text-center">Tipe Transaksi</th>
                        <th class="p-3.5 text-right">Stok Awal</th>
                        <th class="p-3.5 text-right">Perubahan (Qty)</th>
                        <th class="p-3.5 text-right">Stok Akhir</th>
                        <th class="p-3.5">No. Referensi / Alasan</th>
                        <th class="p-3.5 rounded-r-xl">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $m->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="p-3.5 font-bold text-white max-w-[200px] truncate" title="{{ $m->product->name ?? '-' }}">
                                <div>{{ $m->product->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $m->product->barcode ?? '' }}</div>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($m->type === 'in')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">STOK MASUK</span>
                                @elseif($m->type === 'out')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">STOK KELUAR</span>
                                @elseif($m->type === 'opname')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-brand-500/20 text-brand-300 border border-brand-500/30">STOCK OPNAME</span>
                                @elseif($m->type === 'transfer')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">TRANSFER</span>
                                @elseif($m->type === 'sale')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30">PENJUALAN POS</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-800 text-slate-300">{{ strtoupper($m->type) }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right font-bold font-mono text-slate-400">{{ number_format($m->stock_before) }}</td>
                            <td class="p-3.5 text-right font-black font-mono text-sm {{ $m->qty > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $m->qty > 0 ? "+{$m->qty}" : $m->qty }}
                            </td>
                            <td class="p-3.5 text-right font-black font-mono text-white text-sm">{{ number_format($m->stock_after) }}</td>
                            <td class="p-3.5 max-w-[200px]">
                                <div class="font-mono text-xs text-brand-300 font-bold">{{ $m->reference_number ?: '-' }}</div>
                                <div class="text-[11px] text-slate-400 truncate" title="{{ $m->reason }}">{{ $m->reason }}</div>
                            </td>
                            <td class="p-3.5 text-slate-300 text-xs font-semibold">{{ $m->user->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">Belum ada data pergerakan stok yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection
