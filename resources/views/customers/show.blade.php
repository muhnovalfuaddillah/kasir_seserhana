@extends('layouts.app')

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Header Breadcrumb & Back -->
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('customers.index') }}" class="flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Data Pelanggan</span>
        </a>
        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-800 text-indigo-400 border border-slate-700">
            {{ $customer->code }}
        </span>
    </div>

    <!-- Customer Detail Profile Card & Debt Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Profile Info -->
        <div class="glass-card p-6 rounded-2xl space-y-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-500/20 text-indigo-300 font-black text-2xl flex items-center justify-center border border-indigo-500/30 uppercase">
                    {{ substr($customer->name, 0, 2) }}
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-white">{{ $customer->name }}</h2>
                    <p class="text-xs text-slate-400 font-mono">{{ $customer->phone ?: 'Tidak ada no telepon' }}</p>
                </div>
            </div>

            <hr class="border-slate-800">

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-semibold mb-0.5">Alamat Lengkap:</span>
                    <p class="text-slate-200 font-medium leading-relaxed">{{ $customer->address ?: '-' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold mb-0.5">Status Akun:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $customer->status === 'active' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                        {{ $customer->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold mb-0.5">Tanggal Terdaftar:</span>
                    <span class="text-slate-300 font-mono">{{ $customer->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Debt KPI & Credit Limit Summary -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Limit Kasbon -->
            <div class="glass-card p-5 rounded-2xl flex flex-col justify-between border-t-4 border-indigo-500">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Batas Limit Kasbon</span>
                <div class="mt-4">
                    <h3 class="text-2xl font-black text-white">
                        @if($customer->credit_limit > 0)
                            Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}
                        @else
                            <span class="text-indigo-400">Unlimited</span>
                        @endif
                    </h3>
                    <p class="text-[10px] text-slate-400 mt-1">Maksimal tunggakan kasbon yang diizinkan.</p>
                </div>
            </div>

            <!-- Total Tunggakan Berjalan -->
            <div class="glass-card p-5 rounded-2xl flex flex-col justify-between border-t-4 border-amber-500">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hutang Berjalan</span>
                <div class="mt-4">
                    <h3 class="text-2xl font-black text-amber-400">Rp {{ number_format($customer->current_debt, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-slate-400 mt-1">Total piutang toko yang belum dilunasi.</p>
                </div>
            </div>

            <!-- Sisa Kuota Limit -->
            <div class="glass-card p-5 rounded-2xl flex flex-col justify-between border-t-4 {{ ($customer->credit_limit > 0 && $customer->current_debt > $customer->credit_limit) ? 'border-rose-500' : 'border-emerald-500' }}">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sisa Kuota Kasbon</span>
                <div class="mt-4">
                    @if($customer->credit_limit > 0)
                        @if($customer->current_debt > $customer->credit_limit)
                            <h3 class="text-xl font-black text-rose-400 flex items-center gap-1">
                                <span class="material-symbols-outlined text-lg">warning</span> Over-Limit
                            </h3>
                            <p class="text-[10px] text-rose-400 mt-1">Melebihi limit sebesar Rp {{ number_format($customer->current_debt - $customer->credit_limit, 0, ',', '.') }}</p>
                        @else
                            <h3 class="text-2xl font-black text-emerald-400">Rp {{ number_format($customer->available_credit, 0, ',', '.') }}</h3>
                            <p class="text-[10px] text-slate-400 mt-1">Sisa saldo kuota untuk kasbon baru.</p>
                        @endif
                    @else
                        <h3 class="text-2xl font-black text-emerald-400">Tanpa Batas</h3>
                        <p class="text-[10px] text-slate-400 mt-1">Pelanggan tidak memiliki batasan kasbon.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Transaction & Debt History Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-400">history</span>
                <h3 class="font-extrabold text-white text-base">Histori Belanja & Transaksi Piutang</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">No. Invoice</th>
                        <th class="px-6 py-4">Waktu Transaksi</th>
                        <th class="px-6 py-4">Jenis Transaksi</th>
                        <th class="px-6 py-4 text-right">Total Belanja</th>
                        <th class="px-6 py-4 text-right">Tunggakan Hutang</th>
                        <th class="px-6 py-4 text-center">Metode Bayar</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-white">
                                {{ $t->invoice_number }}
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-400">
                                {{ $t->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $t->type === 'penjualan' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30' }}">
                                    {{ $t->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-white">
                                Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-extrabold">
                                @if($t->debt_amount > 0)
                                    <span class="text-amber-400">Rp {{ number_format($t->debt_amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-emerald-400 font-normal">Lunas</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold uppercase text-[10px]">
                                {{ $t->payment_method }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($t->payment_method === 'hutang' && $t->debt_amount > 0)
                                    <button onclick="openPayModal({{ json_encode($t) }})" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 border border-emerald-500/40 text-[11px] font-bold transition-all">
                                        Bayar Hutang
                                    </button>
                                @else
                                    <span class="text-slate-500 text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2 text-slate-600">receipt</span>
                                <p class="font-medium text-sm">Belum ada riwayat transaksi untuk pelanggan ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Bayar Hutang Pelanggan -->
<div id="payModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl border border-slate-800">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-900/60">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-400">payments</span>
                <h3 class="font-extrabold text-white text-base">Pelunasan Hutang Pelanggan</h3>
            </div>
            <button onclick="closePayModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('transactions.store_customer_debt') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="pay_transaction_id" name="transaction_id">
            
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">No Invoice Nota:</span>
                    <span id="pay_invoice" class="font-bold text-white font-mono"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Sisa Tunggakan Nota Ini:</span>
                    <span id="pay_current_debt" class="font-extrabold text-amber-400"></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Jumlah Pembayaran / Setoran (Rp) *</label>
                <input type="text" id="pay_amount" name="pay_amount" required class="input-currency w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-bold text-emerald-400 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Metode Pembayaran</label>
                <select name="payment_method" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-white focus:outline-none focus:border-brand-500">
                    <option value="cash">Tunai / Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="edc">Debit / EDC</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closePayModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-glow-emerald transition-all">Simpan Pelunasan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPayModal(trx) {
        document.getElementById('pay_transaction_id').value = trx.id;
        document.getElementById('pay_invoice').innerText = trx.invoice_number;
        document.getElementById('pay_current_debt').innerText = "Rp " + formatRupiah(trx.debt_amount);
        document.getElementById('pay_amount').value = formatRupiah(trx.debt_amount);
        document.getElementById('payModal').classList.remove('hidden');
    }
    function closePayModal() {
        document.getElementById('payModal').classList.add('hidden');
    }
</script>
@endsection
