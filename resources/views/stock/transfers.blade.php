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
                <span class="material-symbols-outlined text-cyan-400 text-3xl">sync_alt</span>
                Transfer Stok Antar Cabang (Branch Transfers)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Pengiriman dan penerimaan pasokan barang antar gudang/cabang toko.</p>
        </div>

        <button type="button" onclick="openTransferModal()" class="px-4 py-2.5 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
            <span class="material-symbols-outlined text-lg">local_shipping</span>
            <span>+ Buat Transfer Stok Baru</span>
        </button>
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
        <a href="{{ route('stock.transfers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-cyan-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">sync_alt</span>
            <span>Transfer Cabang</span>
        </a>
        <a href="{{ route('stock.history') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">history</span>
            <span>Riwayat Stok</span>
        </a>
    </div>

    <!-- Main Workspace: Table History Transfers -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-cyan-400">history</span>
            Daftar Transfer Stok Antar Cabang
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Transfer</th>
                        <th class="p-3.5">Dari Cabang</th>
                        <th class="p-3.5">Ke Cabang</th>
                        <th class="p-3.5 text-center">Detail Produk</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5">Pengirim / Penerima</th>
                        <th class="p-3.5 text-center rounded-r-xl">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($transfers as $trf)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-cyan-300">
                                <div>{{ $trf->transfer_number }}</div>
                                <div class="text-[10px] text-slate-500 font-normal">{{ $trf->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="p-3.5 font-bold text-white">{{ $trf->fromBranch->name ?? 'Pusat' }}</td>
                            <td class="p-3.5 font-bold text-emerald-400">{{ $trf->toBranch->name ?? 'Tujuan' }}</td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-900 text-slate-300 font-bold border border-slate-800">
                                    {{ $trf->details->count() }} Jenis Item
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($trf->status === 'received')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">DITERIMA</span>
                                @elseif($trf->status === 'shipped')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 animate-pulse">DIKIRIM</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30">{{ strtoupper($trf->status) }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-300 text-xs">
                                <div>Kirim: <strong class="text-white">{{ $trf->sender->name ?? 'Admin' }}</strong></div>
                                @if($trf->receiver)
                                    <div class="text-[11px] text-slate-400">Terima: <strong class="text-emerald-400">{{ $trf->receiver->name }}</strong></div>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                @if($trf->status === 'shipped')
                                    <form action="{{ route('stock.transfers.receive', $trf->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa barang transfer ini sudah diterima di lokasi tujuan?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow transition-all flex items-center justify-center gap-1 mx-auto">
                                            <span class="material-symbols-outlined text-base">check</span>
                                            <span>Konfirmasi Diterima</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-500 text-xs font-semibold">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">Belum ada riwayat transfer stok antar cabang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $transfers->links() }}
        </div>
    </div>
</div>

<!-- Modal Buat Transfer Stok -->
<div id="transferModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-xl w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400">local_shipping</span>
                Buat Pengiriman Transfer Stok Baru
            </h3>
            <button type="button" onclick="closeTransferModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('stock.transfers.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Dari Cabang Asal <span class="text-rose-400">*</span></label>
                    <select name="from_branch_id" required class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-cyan-500">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $b->is_main ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Ke Cabang Tujuan <span class="text-rose-400">*</span></label>
                    <select name="to_branch_id" required class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-cyan-500">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ !$b->is_main ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Item Selection -->
            <div class="space-y-2">
                <label class="block text-xs font-extrabold text-slate-300">Pilih Barang yang Dikirim <span class="text-rose-400">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <select id="transfer_prod_select" data-placeholder="-- Cari & Pilih Produk --" class="select-searchable w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                            <option value=""></option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-stock="{{ $p->stock }}" data-unit="{{ $p->unit }}">
                                    {{ $p->name }} (Sisa Stok: {{ $p->stock }} {{ $p->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="number" id="transfer_qty_input" min="1" value="1" placeholder="Qty" class="w-full bg-slate-950 text-xs text-white font-bold rounded-xl px-3 py-2 border border-slate-800">
                    </div>
                </div>
                <button type="button" onclick="addTransferItem()" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 text-xs font-extrabold border border-slate-700">
                    + Tambah ke Daftar Kirim
                </button>
            </div>

            <!-- Selected Items Table -->
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-2xl space-y-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Daftar Barang Transfer:</span>
                <div id="transferItemsList" class="space-y-1 max-h-36 overflow-y-auto text-xs text-white">
                    <p class="text-slate-500 text-center py-2 text-xs">Belum ada barang dipilih.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Pengiriman</label>
                <textarea name="notes" rows="2" placeholder="Catatan pengiriman barang..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeTransferModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs shadow-glow">KIRIM TRANSFER STOK</button>
            </div>
        </form>
    </div>
</div>

<script>
    let transferItems = [];

    function openTransferModal() {
        document.getElementById('transferModal').classList.remove('hidden');
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.add('hidden');
    }

    function addTransferItem() {
        const select = document.getElementById('transfer_prod_select');
        const opt = select.options[select.selectedIndex];
        const qty = parseInt(document.getElementById('transfer_qty_input').value) || 1;

        if (!opt) return;

        const id = opt.value;
        const name = opt.dataset.name;
        const stock = parseInt(opt.dataset.stock);
        const unit = opt.dataset.unit;

        if (qty > stock) {
            alert(`Stok produk '${name}' tidak mencukupi (Sisa: ${stock})!`);
            return;
        }

        const existing = transferItems.find(i => i.id === id);
        if (existing) {
            existing.qty += qty;
        } else {
            transferItems.push({ id, name, qty, unit });
        }

        renderTransferItems();
    }

    function removeTransferItem(index) {
        transferItems.splice(index, 1);
        renderTransferItems();
    }

    function renderTransferItems() {
        const container = document.getElementById('transferItemsList');
        if (transferItems.length === 0) {
            container.innerHTML = `<p class="text-slate-500 text-center py-2 text-xs">Belum ada barang dipilih.</p>`;
            return;
        }

        container.innerHTML = transferItems.map((item, idx) => `
            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900 border border-slate-800 text-xs">
                <span class="font-bold text-white truncate max-w-[200px]">${item.name}</span>
                <div class="flex items-center gap-3">
                    <span class="font-mono font-bold text-cyan-300">${item.qty} ${item.unit}</span>
                    <input type="hidden" name="items[${idx}][product_id]" value="${item.id}">
                    <input type="hidden" name="items[${idx}][qty]" value="${item.qty}">
                    <button type="button" onclick="removeTransferItem(${idx})" class="text-rose-400 hover:text-rose-300 font-bold">✕</button>
                </div>
            </div>
        `).join('');
    }
</script>
@endsection
