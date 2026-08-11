@extends('layouts.app')

@section('content')
<main class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-[1600px] mx-auto">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-400">receipt_long</span>
                Riwayat Penjualan & Transaksi
            </h1>
            <p class="text-xs text-slate-400">Daftar seluruh nota penjualan, detail belanja, cetak ulang struk, dan pembatalan nota</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card rounded-2xl p-4">
        <form action="{{ route('transactions.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Cari Nota / Pelanggan / Kasir</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="TRX-XXXXXXXX..." class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Metode Bayar</label>
                <select name="payment_method" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="">Semua</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="edc" {{ request('payment_method') == 'edc' ? 'selected' : '' }}>EDC / Debit</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="glass-card rounded-2xl p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900/80 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Nota</th>
                        <th class="p-3.5">Waktu</th>
                        <th class="p-3.5">Pelanggan</th>
                        <th class="p-3.5">Kasir</th>
                        <th class="p-3.5">Pembayaran</th>
                        <th class="p-3.5">Total Tagihan</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-bold text-white">{{ $trx->invoice_number }}</td>
                            <td class="p-3.5 text-slate-400">{{ $trx->created_at->format('d/m/Y H:i') }} WIB</td>
                            <td class="p-3.5">{{ $trx->customer_name }}</td>
                            <td class="p-3.5 text-slate-400">{{ $trx->cashier_name }}</td>
                            <td class="p-3.5">
                                <span class="uppercase font-bold text-slate-300">{{ $trx->payment_method }}</span>
                            </td>
                            <td class="p-3.5 font-extrabold text-white">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-center">
                                @if($trx->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">Lunas</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">Retur/Batal</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="viewReceipt({{ json_encode($trx->load('details')) }})" class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors" title="Lihat Struk">
                                        <span class="material-symbols-outlined text-base">receipt</span>
                                    </button>
                                    @if($trx->status === 'completed')
                                        <form action="{{ route('transactions.cancel', $trx->id) }}" method="POST" onsubmit="return confirm('Membatalkan transaksi ini akan mengembalikan stok produk. Lanjutkan?')">
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
                                Tidak ada transaksi yang ditemukan.
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
</main>

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

    function viewReceipt(trx) {
        document.getElementById('viewRcptInvoice').textContent = `No: ${trx.invoice_number}`;
        document.getElementById('viewRcptDate').textContent = new Date(trx.created_at).toLocaleDateString('id-ID');
        document.getElementById('viewRcptCashier').textContent = `Kasir: ${trx.cashier_name}`;
        document.getElementById('viewRcptCustomer').textContent = `Pelanggan: ${trx.customer_name}`;

        const itemsEl = document.getElementById('viewRcptItems');
        itemsEl.innerHTML = trx.details.map(d => `
            <div>
                <div class="font-bold">${d.product_name}</div>
                <div class="flex justify-between text-slate-600">
                    <span>${d.quantity} x Rp ${formatRupiah(d.selling_price)}</span>
                    <span class="font-semibold text-slate-900">Rp ${formatRupiah(d.subtotal)}</span>
                </div>
            </div>
        `).join('');

        document.getElementById('viewRcptDiscount').textContent = `Rp ${formatRupiah(trx.discount_amount)}`;
        document.getElementById('viewRcptTotal').textContent = `Rp ${formatRupiah(trx.total_amount)}`;
        document.getElementById('viewRcptMethod').textContent = trx.payment_method.toUpperCase();
        document.getElementById('viewRcptPay').textContent = `Rp ${formatRupiah(trx.pay_amount)}`;
        document.getElementById('viewRcptChange').textContent = `Rp ${formatRupiah(trx.change_amount)}`;

        openModal('viewReceiptModal');
    }

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
</script>
@endsection
