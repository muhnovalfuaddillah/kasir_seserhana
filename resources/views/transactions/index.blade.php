@extends('layouts.app')

@section('content')
<style>
@media print {
    @page {
        size: 58mm auto;
        margin: 0;
    }
    html, body {
        width: 58mm !important;
        background: #fff !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    body * {
        visibility: hidden !important;
    }
    #viewReceiptModal, #viewReceiptModal * {
        visibility: visible !important;
        color: #000000 !important;
        font-weight: 700 !important;
        opacity: 1 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border-color: #000000 !important;
    }
    #viewReceiptModal {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 58mm !important;
        max-width: 58mm !important;
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
        box-shadow: none !important;
    }
    #viewReceiptModal > div {
        width: 58mm !important;
        max-width: 58mm !important;
        box-shadow: none !important;
        border: none !important;
        padding: 4px 6px !important;
        margin: 0 !important;
        color: #000000 !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 10.5px !important;
        line-height: 1.25 !important;
    }
    #viewReceiptModal button, .no-print {
        display: none !important;
    }
}
</style>

<div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-[1600px] mx-auto">
    
    <!-- Page Header & Action Buttons -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-400">receipt_long</span>
                Kelola Transaksi & Keuangan Toko
            </h1>
            <p class="text-xs text-slate-400">Pusat pencatatan transaksi penjualan, pengeluaran operasional, pembelian stok, dan pelunasan hutang</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="openModal('customerDebtModal')" class="px-3.5 py-2 rounded-xl bg-emerald-600/90 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg flex items-center gap-1.5 transition-all active:scale-95">
                <span class="material-symbols-outlined text-base">account_balance_wallet</span>
                <span>+ Pelunasan Kasbon Pelanggan</span>
            </button>
            @if(auth()->user()->isAdmin())
                <button type="button" onclick="openModal('expenseModal')" class="px-3.5 py-2 rounded-xl bg-rose-600/90 hover:bg-rose-500 text-white text-xs font-bold shadow-lg flex items-center gap-1.5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-base">payments</span>
                    <span>+ Catat Pengeluaran</span>
                </button>
                <button type="button" onclick="openModal('purchaseModal')" class="px-3.5 py-2 rounded-xl bg-cyan-600/90 hover:bg-cyan-500 text-white text-xs font-bold shadow-lg flex items-center gap-1.5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-base">local_shipping</span>
                    <span>+ Pembelian Stok</span>
                </button>
                <button type="button" onclick="openModal('debtModal')" class="px-3.5 py-2 rounded-xl bg-purple-600/90 hover:bg-purple-500 text-white text-xs font-bold shadow-lg flex items-center gap-1.5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-base">account_balance_wallet</span>
                    <span>+ Bayar Hutang Supplier</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-5 space-y-1 border-l-4 border-l-emerald-500">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Penjualan</span>
            <p class="text-xl sm:text-2xl font-black text-emerald-400">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400">Pemasukan Kasir POS</p>
        </div>

        <div class="glass-card rounded-2xl p-5 space-y-1 border-l-4 border-l-rose-500">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengeluaran Operasional</span>
            <p class="text-xl sm:text-2xl font-black text-rose-400">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400">Listrik, Sewa, Gaji, dll</p>
        </div>

        <div class="glass-card rounded-2xl p-5 space-y-1 border-l-4 border-l-cyan-500">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pembelian Stok (Restock)</span>
            <p class="text-xl sm:text-2xl font-black text-cyan-400">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400">Kulakan dari Supplier</p>
        </div>

        <div class="glass-card rounded-2xl p-5 space-y-1 border-l-4 border-l-purple-500">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sisa Hutang Toko</span>
            <p class="text-xl sm:text-2xl font-black text-purple-400">Rp {{ number_format($totalSisaHutang, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400">Tagihan Belum Lunas</p>
        </div>
    </div>

    <!-- Transaction Types Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar border-b border-slate-800">
        <a href="{{ route('transactions.index', array_merge(request()->query(), ['type' => 'all'])) }}" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $type === 'all' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-base">receipt_long</span>
            <span>Semua Transaksi</span>
        </a>
        <a href="{{ route('transactions.index', array_merge(request()->query(), ['type' => 'penjualan'])) }}" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $type === 'penjualan' ? 'bg-emerald-600 text-white shadow-glow-emerald' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-base">shopping_cart</span>
            <span>Penjualan POS</span>
        </a>
        <a href="{{ route('transactions.index', array_merge(request()->query(), ['type' => 'pengeluaran'])) }}" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $type === 'pengeluaran' ? 'bg-rose-600 text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-base">payments</span>
            <span>Pengeluaran Operasional</span>
        </a>
        <a href="{{ route('transactions.index', array_merge(request()->query(), ['type' => 'pembelian'])) }}" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $type === 'pembelian' ? 'bg-cyan-600 text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-base">local_shipping</span>
            <span>Pembelian Stok</span>
        </a>
        <a href="{{ route('transactions.index', array_merge(request()->query(), ['type' => 'bayar_hutang'])) }}" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $type === 'bayar_hutang' ? 'bg-purple-600 text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-base">account_balance_wallet</span>
            <span>Bayar Hutang</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form action="{{ route('transactions.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Cari Invoice / Subjek / Supplier / Admin</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kata kunci..." class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Metode Bayar</label>
                <select name="payment_method" data-placeholder="-- Semua Metode --" class="select-searchable w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value=""></option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="edc" {{ request('payment_method') == 'edc' ? 'selected' : '' }}>EDC / Debit</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="glass-card rounded-2xl p-6 space-y-4 border border-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900/80 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Invoice</th>
                        <th class="p-3.5">Jenis</th>
                        <th class="p-3.5">Deskripsi / Subjek</th>
                        <th class="p-3.5">Petugas / Kasir</th>
                        <th class="p-3.5">Metode Bayar</th>
                        <th class="p-3.5">Total Nominal</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-bold text-white whitespace-nowrap">
                                #{{ $trx->invoice_number }}
                                <div class="text-[10px] text-slate-500 font-normal">{{ $trx->created_at->format('d/m/Y H:i') }} WIB</div>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                @if($trx->type === 'penjualan')
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">Penjualan</span>
                                @elseif($trx->type === 'pengeluaran')
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">Pengeluaran</span>
                                @elseif($trx->type === 'pembelian')
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 uppercase">Pembelian Stok</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-purple-500/20 text-purple-400 border border-purple-500/30 uppercase">Bayar Hutang</span>
                                @endif
                            </td>
                            <td class="p-3.5 max-w-xs">
                                <p class="font-bold text-white truncate">{{ $trx->description ?? $trx->customer_name }}</p>
                                @if($trx->supplier_name)
                                    <p class="text-[10px] text-cyan-400">Supplier: {{ $trx->supplier_name }}</p>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-400 whitespace-nowrap">{{ $trx->cashier_name }}</td>
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="uppercase font-bold text-slate-300">{{ $trx->payment_method }}</span>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="font-extrabold text-white">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                                @if($trx->debt_amount > 0)
                                    <div class="text-[10px] font-bold text-purple-400">Hutang: Rp {{ number_format($trx->debt_amount, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                @if($trx->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">Lunas</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">Retur/Batal</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    @if($trx->type === 'penjualan' || $trx->details->count() > 0)
                                        <button onclick="viewReceipt({{ json_encode($trx->load('details')) }})" class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors" title="Lihat Struk/Detail">
                                            <span class="material-symbols-outlined text-base">receipt</span>
                                        </button>
                                    @endif

                                    @if(auth()->user()->isAdmin() && $trx->status === 'completed')
                                        <form action="{{ route('transactions.cancel', $trx->id) }}" method="POST" onsubmit="return confirm('Membatalkan transaksi ini akan mengembalikan stok produk (jika ada). Lanjutkan?')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-colors" title="Batalkan/Retur">
                                                <span class="material-symbols-outlined text-base">cancel</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500 font-semibold">
                                Tidak ada data transaksi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            {{ $transactions->links() }}
        </div>
    </div>

<!-- Modal 1: Catat Pengeluaran Operasional -->
<div id="expenseModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-400">payments</span>
                Catat Pengeluaran Operasional
            </h3>
            <button type="button" onclick="closeModal('expenseModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('transactions.store_expense') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan / Keperluan <span class="text-rose-400">*</span></label>
                <input type="text" name="description" required placeholder="Contoh: Bayar Listrik Toko Bulan Ini" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nominal Pengeluaran (Rp) <span class="text-rose-400">*</span></label>
                <input type="number" name="total_amount" required min="1" placeholder="150000" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Metode Pembayaran <span class="text-rose-400">*</span></label>
                <select name="payment_method" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="cash">Tunai (Cash Kasir)</option>
                    <option value="qris">QRIS / Transfer Bank</option>
                    <option value="edc">EDC / Kartu Debit</option>
                </select>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('expenseModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-lg">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Catat Pembelian Stok (Restock) -->
<div id="purchaseModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-lg w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400">local_shipping</span>
                Catat Pembelian Stok (Restock)
            </h3>
            <button type="button" onclick="closeModal('purchaseModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('transactions.store_purchase') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Produk <span class="text-rose-400">*</span></label>
                <select name="product_id" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="">-- Pilih Produk yang Di-Restock --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} (Stok Saat Ini: {{ $prod->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Jumlah Qty <span class="text-rose-400">*</span></label>
                    <input type="number" name="quantity" required min="1" value="10" placeholder="10" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Beli Unit / HPP (Rp) <span class="text-rose-400">*</span></label>
                    <input type="number" name="purchase_price" required min="0" placeholder="2500" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Supplier / Vendor <span class="text-rose-400">*</span></label>
                    <input type="text" name="supplier_name" required placeholder="PT. Distributor Jaya" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Metode Bayar <span class="text-rose-400">*</span></label>
                    <select name="payment_method" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="qris">Transfer / QRIS</option>
                        <option value="edc">EDC / Kartu Debit</option>
                    </select>
                </div>
            </div>

            <!-- Checkbox Option for Credit / Debt Pembelian -->
            <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 space-y-2">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-300 cursor-pointer">
                    <input type="checkbox" name="is_debt" value="1" onchange="toggleDebtFields(this.checked)" class="rounded bg-slate-900 border-slate-700 text-brand-600 focus:ring-0">
                    <span>Pembelian Kredit (Hutang ke Supplier)</span>
                </label>

                <div id="debtFieldsContainer" class="hidden grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Nominal Hutang (Rp)</label>
                        <input type="number" name="debt_amount" placeholder="Kosongkan jika full hutang" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    </div>
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('purchaseModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-lg">Simpan Pembelian & Tambah Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Catat Bayar Hutang -->
<div id="debtModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400">account_balance_wallet</span>
                Catat Pembayaran Hutang
            </h3>
            <button type="button" onclick="closeModal('debtModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('transactions.store_debt') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Supplier / Pihak Penerima <span class="text-rose-400">*</span></label>
                <input type="text" name="supplier_name" required placeholder="Contoh: PT. Distributor Jaya" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nominal Dibayarkan (Rp) <span class="text-rose-400">*</span></label>
                <input type="number" name="pay_amount" required min="1" placeholder="500000" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Metode Pembayaran <span class="text-rose-400">*</span></label>
                <select name="payment_method" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="cash">Tunai (Cash Kasir)</option>
                    <option value="qris">Transfer / QRIS</option>
                    <option value="edc">EDC / Debit</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan</label>
                <input type="text" name="description" placeholder="Contoh: Cicilan Hutang Ke-2 Nota PUR-1002" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('debtModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-lg">Simpan Pembayaran Hutang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Pelunasan Hutang / Kasbon Pelanggan -->
<div id="customerDebtModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-400">payments</span>
                Pelunasan Kasbon / Hutang Pelanggan
            </h3>
            <button type="button" onclick="closeModal('customerDebtModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('transactions.store_customer_debt') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Pelanggan & Nota Kasbon <span class="text-rose-400">*</span></label>
                <select name="transaction_id" id="page_customer_debt_select" required onchange="onPageCustomerDebtSelect(this)" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="">-- Pilih Nota Hutang Pelanggan --</option>
                    @foreach($customerDebts as $cd)
                        <option value="{{ $cd->id }}" data-debt="{{ (int)$cd->debt_amount }}">#{{ $cd->invoice_number }} - {{ $cd->customer_name }} (Sisa Hutang: Rp {{ number_format($cd->debt_amount, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nominal Dibayarkan (Rp) <span class="text-rose-400">*</span></label>
                <input type="number" name="pay_amount" id="page_customer_debt_pay_amount" required min="1" placeholder="Nominal lunas terisi otomatis" class="w-full bg-slate-900 text-xs font-bold text-emerald-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Metode Pembayaran <span class="text-rose-400">*</span></label>
                <select name="payment_method" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="cash">Tunai (Cash)</option>
                    <option value="qris">QRIS / Transfer Bank</option>
                    <option value="edc">EDC / Kartu Debit</option>
                </select>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('customerDebtModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg">Simpan Pelunasan Kasbon</button>
            </div>
        </form>
    </div>
</div>

<!-- View Receipt Modal -->
<div id="viewReceiptModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white text-slate-900 rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-2xl font-mono text-xs">
        <div class="text-center space-y-1 pb-3 border-b border-dashed border-slate-300">
            <h3 class="text-base font-bold uppercase tracking-wider text-slate-900">KINETIC POS STORE</h3>
            <p class="text-[11px] text-slate-600">Jl. Sudirman No. 88, Jakarta Pusat</p>
        </div>

        <div class="space-y-1 text-[11px] text-slate-600 pb-2 border-b border-dashed border-slate-300">
            <div class="flex justify-between"><span id="viewRcptInvoice"></span><span id="viewRcptDate"></span></div>
            <div class="flex justify-between"><span id="viewRcptCashier"></span><span id="viewRcptCustomer"></span></div>
        </div>

        <div id="viewRcptItems" class="space-y-2 py-2 border-b border-dashed border-slate-300"></div>

        <div class="space-y-1 pt-1 text-[11px]">
            <div class="flex justify-between"><span>Diskon:</span><span id="viewRcptDiscount"></span></div>
            <div class="flex justify-between font-bold text-sm text-slate-900 border-t border-slate-200 pt-1"><span>TOTAL:</span><span id="viewRcptTotal"></span></div>
            <div class="flex justify-between text-slate-600"><span>Bayar (<span id="viewRcptMethod"></span>):</span><span id="viewRcptPay"></span></div>
            <div class="flex justify-between text-slate-600"><span>Kembali:</span><span id="viewRcptChange"></span></div>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Nota</span>
            </button>
            <button onclick="closeModal('viewReceiptModal')" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-800 font-bold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    function onPageCustomerDebtSelect(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const debtAmount = selectedOption.getAttribute('data-debt');
        const payInput = document.getElementById('page_customer_debt_pay_amount');
        if (debtAmount && payInput) {
            payInput.value = parseInt(debtAmount, 10);
        } else if (payInput) {
            payInput.value = '';
        }
    }

    function toggleDebtFields(isChecked) {
        const container = document.getElementById('debtFieldsContainer');
        if (isChecked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function viewReceipt(trx) {
        document.getElementById('viewRcptInvoice').textContent = `No: ${trx.invoice_number}`;
        
        let formattedDate = '-';
        if (trx.created_at) {
            const dt = new Date(trx.created_at);
            const dateStr = dt.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const timeStr = dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
            formattedDate = `${dateStr} ${timeStr} WIB`;
        }
        document.getElementById('viewRcptDate').textContent = formattedDate;
        document.getElementById('viewRcptCashier').textContent = `Kasir: ${trx.cashier_name}`;
        document.getElementById('viewRcptCustomer').textContent = `Pelanggan: ${trx.customer_name}`;

        const itemsEl = document.getElementById('viewRcptItems');
        if (trx.details && trx.details.length > 0) {
            itemsEl.innerHTML = trx.details.map(d => {
                const isWholesale = d.is_wholesale || (d.normal_price && parseFloat(d.selling_price) < parseFloat(d.normal_price));
                return `
                    <div class="space-y-0.5 border-b border-dashed border-slate-200/80 pb-1.5 pt-1">
                        <div class="font-bold text-slate-900 flex items-center justify-between">
                            <span>${d.product_name}</span>
                            ${isWholesale ? `<span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 border border-amber-300">GROSIR</span>` : ''}
                        </div>
                        <div class="flex justify-between text-slate-600 text-xs">
                            <span>${d.quantity} x Rp ${formatRupiah(d.selling_price)}</span>
                            <span class="font-bold text-slate-900">Rp ${formatRupiah(d.subtotal)}</span>
                        </div>
                        ${isWholesale ? `<div class="text-[9px] font-bold text-emerald-700 italic">🏷️ ${d.wholesale_label || 'Harga Grosir Tier'}</div>` : ''}
                    </div>
                `;
            }).join('');
        } else {
            itemsEl.innerHTML = `<div class="italic text-slate-500">${trx.description || 'Detail transaksi'}</div>`;
        }

        document.getElementById('viewRcptDiscount').textContent = `Rp ${formatRupiah(trx.discount_amount || 0)}`;
        document.getElementById('viewRcptTotal').textContent = `Rp ${formatRupiah(trx.total_amount)}`;
        document.getElementById('viewRcptMethod').textContent = (trx.payment_method || 'CASH').toUpperCase();
        document.getElementById('viewRcptPay').textContent = `Rp ${formatRupiah(trx.pay_amount || trx.total_amount)}`;
        document.getElementById('viewRcptChange').textContent = `Rp ${formatRupiah(trx.change_amount || 0)}`;

        openModal('viewReceiptModal');
    }

    function formatRupiah(num) {
        if (num === null || num === undefined || isNaN(num)) return '0';
        return new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(num)));
    }
</script>
@endsection
