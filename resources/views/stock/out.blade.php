@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-bold text-xs sm:text-sm flex items-center gap-2 shadow-lg">
            <span class="material-symbols-outlined text-xl">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-300 font-bold text-xs sm:text-sm flex items-center gap-2 shadow-lg">
            <span class="material-symbols-outlined text-xl">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Page & Tabs -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-400 text-3xl">indeterminate_check_box</span>
                Stok Keluar (Stock Out / Waste & Damage)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Catat pengurangan stok non-penjualan seperti barang rusak, hilang, kadaluarsa, atau pemakaian operasional internal.</p>
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
        <a href="{{ route('stock.out') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-rose-600 text-white shadow-glow transition-all flex items-center gap-2">
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
    </div>

    <!-- Main Workspace: Form Left (1 Col), Table Right (2 Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Form Input Stok Keluar -->
        <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
            <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="material-symbols-outlined text-rose-400">remove_circle</span>
                Form Catat Barang Keluar / Rusak
            </h3>

            <form action="{{ route('stock.out.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Pilih Produk <span class="text-rose-400">*</span></label>
                    <select name="product_id" required data-placeholder="-- Cari & Pilih Produk --" class="select-searchable w-full bg-slate-950 text-xs sm:text-sm font-semibold text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-rose-500">
                        <option value=""></option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} (Sisa Stok: {{ $p->stock }} {{ $p->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-300 mb-1">Jumlah Keluar (Qty) <span class="text-rose-400">*</span></label>
                        <input type="number" name="qty" required min="1" value="{{ old('qty', 1) }}" class="w-full bg-slate-950 text-xs sm:text-sm font-black text-rose-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-300 mb-1">Alasan Pengurangan <span class="text-rose-400">*</span></label>
                        <select name="reason" required class="w-full bg-slate-950 text-xs sm:text-sm font-bold text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-rose-500">
                            <option value="Barang Rusak / Pecah">Barang Rusak / Pecah</option>
                            <option value="Barang Kadaluarsa (Expired)">Barang Kadaluarsa (Expired)</option>
                            <option value="Barang Hilang / Selisih">Barang Hilang / Selisih</option>
                            <option value="Pemakaian Operasional Internal">Pemakaian Operasional Internal</option>
                            <option value="Sample / Promo Toko">Sample / Promo Toko</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Cabang asal</label>
                    <select name="branch_id" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-rose-500">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $b->is_main ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">No. Referensi / Berita Acara</label>
                    <input type="text" name="reference_number" placeholder="Contoh: BA-WASTE-2026-05" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" placeholder="Jelaskan alasan detail pengurangan barang ini..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-rose-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-black text-sm shadow-lg transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-xl">save</span>
                    <span>SIMPAN STOK KELUAR</span>
                </button>
            </form>
        </div>

        <!-- Right: Table History Stok Keluar -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
            <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="material-symbols-outlined text-rose-400">history</span>
                Riwayat Barang Keluar & Waste
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                        <tr>
                            <th class="p-3 rounded-l-xl">Waktu</th>
                            <th class="p-3">Produk</th>
                            <th class="p-3 text-right">Qty Keluar</th>
                            <th class="p-3">Alasan</th>
                            <th class="p-3 text-right">Stok Akhir</th>
                            <th class="p-3 rounded-r-xl">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 font-medium">
                        @forelse($movements as $m)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 font-mono text-slate-400 text-[11px]">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3 font-bold text-white max-w-[180px] truncate" title="{{ $m->product->name ?? '-' }}">{{ $m->product->name ?? '-' }}</td>
                                <td class="p-3 text-right font-black font-mono text-rose-400">{{ $m->qty }}</td>
                                <td class="p-3 text-xs text-amber-300 font-semibold max-w-[150px] truncate" title="{{ $m->reason }}">{{ $m->reason }}</td>
                                <td class="p-3 text-right font-bold font-mono text-white">{{ number_format($m->stock_after) }}</td>
                                <td class="p-3 text-slate-400 text-xs truncate max-w-[100px]">{{ $m->user->name ?? 'Admin' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">Belum ada catatan barang keluar.</td>
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
</div>
@endsection
