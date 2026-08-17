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
                <span class="material-symbols-outlined text-brand-400 text-3xl">fact_check</span>
                Penyesuaian Stok (Stock Opname)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Rekonsiliasi jumlah fisik barang di toko/gudang dengan stok sistem dan hitung selisih otomatis.</p>
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
        <a href="{{ route('stock.opname') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all flex items-center gap-2">
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

    <!-- Main Workspace: Stock Opname Form -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-6 shadow-xl">
        
        <form action="{{ route('stock.opname.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Top Controls: Date & Notes -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-4 border-b border-slate-800">
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Tanggal Opname <span class="text-rose-400">*</span></label>
                    <input type="date" name="adjustment_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 text-xs sm:text-sm font-bold text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Opname</label>
                    <input type="text" name="notes" placeholder="Contoh: Stock Opname Bulanan Agustus 2026 Toko Utama" class="w-full bg-slate-950 text-xs sm:text-sm text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Table Physical Count vs System Stock -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-400">inventory_2</span>
                        Hitung Stok Fisik Barang (Physical Count)
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold">Ketik jumlah fisik yang dihitung secara aktual di kolom 'Stok Fisik'</span>
                </div>

                <div class="overflow-x-auto max-h-[480px] overflow-y-auto pr-1 border border-slate-800 rounded-2xl">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-xs font-extrabold text-slate-400 uppercase bg-slate-900 sticky top-0 border-b border-slate-800 z-10">
                            <tr>
                                <th class="p-3.5">Nama Produk</th>
                                <th class="p-3.5">Barcode</th>
                                <th class="p-3.5 text-right">Stok Sistem</th>
                                <th class="p-3.5 text-center w-36">Stok Fisik (Aktual)</th>
                                <th class="p-3.5 text-right">Selisih</th>
                                <th class="p-3.5">Alasan / Catatan Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-medium">
                            @foreach($products as $idx => $p)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="p-3.5 font-bold text-white">
                                        <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $p->id }}">
                                        <span>{{ $p->name }}</span>
                                    </td>
                                    <td class="p-3.5 font-mono text-slate-400 text-xs">{{ $p->barcode }}</td>
                                    <td class="p-3.5 text-right font-bold text-slate-300 font-mono text-sm">
                                        <span id="sys-stock-{{ $p->id }}">{{ $p->stock }}</span> {{ $p->unit }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <input type="number" name="items[{{ $idx }}][physical_stock]" id="phys-input-{{ $p->id }}" value="{{ $p->stock }}" min="0" oninput="calculateDiff({{ $p->id }}, {{ $p->stock }}, this.value)" class="w-28 text-center bg-slate-950 text-sm font-black text-white rounded-xl py-1.5 px-2 border-2 border-brand-500/60 focus:border-brand-400 focus:ring-2 focus:ring-brand-400/50">
                                    </td>
                                    <td class="p-3.5 text-right font-black font-mono text-sm">
                                        <span id="diff-tag-{{ $p->id }}" class="text-slate-400 font-bold">0</span>
                                    </td>
                                    <td class="p-3.5">
                                        <input type="text" name="items[{{ $idx }}][reason]" placeholder="Alasan jika ada selisih..." class="w-full bg-slate-950 text-xs text-white rounded-lg px-2.5 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-3 flex items-center justify-end">
                <button type="submit" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-brand-600 to-teal-600 hover:from-brand-500 hover:to-teal-500 text-white font-black text-sm shadow-glow transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                    <span>SIMPAN & REKONSILIASI STOCK OPNAME</span>
                </button>
            </div>
        </form>
    </div>

    <!-- History Stock Opname List -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-brand-400">history</span>
            Riwayat Dokumen Stock Opname
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3 rounded-l-xl">No. Dokumen</th>
                        <th class="p-3">Tanggal Opname</th>
                        <th class="p-3 text-center">Jumlah Item Diselisihi</th>
                        <th class="p-3">Catatan</th>
                        <th class="p-3 rounded-r-xl">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($adjustments as $adj)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3 font-mono font-bold text-brand-300">{{ $adj->adjustment_number }}</td>
                            <td class="p-3 font-mono text-slate-300">{{ $adj->adjustment_date ? $adj->adjustment_date->format('d/m/Y') : '-' }}</td>
                            <td class="p-3 text-center font-bold font-mono text-white">{{ $adj->details->count() }} Item</td>
                            <td class="p-3 text-slate-400 max-w-[250px] truncate">{{ $adj->notes ?: '-' }}</td>
                            <td class="p-3 text-slate-400">{{ $adj->user->name ?? 'Admin' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">Belum ada riwayat Stock Opname.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $adjustments->links() }}
        </div>
    </div>
</div>

<script>
    function calculateDiff(productId, sysStock, physValue) {
        const phys = parseInt(physValue) || 0;
        const diff = phys - sysStock;
        const tag = document.getElementById(`diff-tag-${productId}`);

        if (!tag) return;

        if (diff > 0) {
            tag.textContent = `+${diff}`;
            tag.className = "text-emerald-400 font-extrabold";
        } else if (diff < 0) {
            tag.textContent = `${diff}`;
            tag.className = "text-rose-400 font-extrabold";
        } else {
            tag.textContent = `0`;
            tag.className = "text-slate-400 font-bold";
        }
    }
</script>
@endsection
