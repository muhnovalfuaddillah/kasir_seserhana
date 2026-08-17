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
                <span class="material-symbols-outlined text-emerald-400 text-3xl">south_east</span>
                Pencatatan Kas Masuk (Non-POS Income)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Catat penerimaan uang di luar kasir POS (setoran modal owner, pinjaman bank, pendanaan, atau pemasukan lain).</p>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('financial.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Ringkasan Keuangan</span>
        </a>
        <a href="{{ route('financial.cash_in') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-emerald-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">south_east</span>
            <span>Kas Masuk</span>
        </a>
        <a href="{{ route('financial.cash_out') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">north_east</span>
            <span>Kas Keluar</span>
        </a>
        <a href="{{ route('financial.categories') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">category</span>
            <span>Kategori Pengeluaran</span>
        </a>
        <a href="{{ route('financial.payrolls') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">badge</span>
            <span>Gaji Karyawan</span>
        </a>
        <a href="{{ route('financial.cashflow') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">water_drop</span>
            <span>Laporan Arus Kas</span>
        </a>
    </div>

    <!-- Main Workspace: Form Left (1 Col), Table Right (2 Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Form Input Kas Masuk -->
        <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
            <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="material-symbols-outlined text-emerald-400">add_circle</span>
                Form Tambah Kas Masuk
            </h3>

            <form action="{{ route('financial.cash_in.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Nominal Penerimaan (Rp) <span class="text-rose-400">*</span></label>
                    <input type="text" name="amount" required data-type="currency" placeholder="Contoh: 5.000.000" class="input-currency w-full bg-slate-950 text-sm font-black text-emerald-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Kategori Pemasukan</label>
                    <select name="cash_category_id" data-placeholder="-- Pilih Kategori Pemasukan --" class="select-searchable w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                        <option value=""></option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Metode Penerimaan</label>
                        <select name="payment_method" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                            <option value="cash">Tunai (Cash)</option>
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Akun Tujuan</label>
                        <input type="text" name="account_name" value="Kas Utama" placeholder="Kas Utama / Bank BCA" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">No. Bukti / Referensi</label>
                    <input type="text" name="reference_number" placeholder="Contoh: REF-MODAL-001" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Keterangan</label>
                    <textarea name="notes" rows="3" placeholder="Keterangan sumber penerimaan kas..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-sm shadow-lg transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-xl">save</span>
                    <span>SIMPAN KAS MASUK</span>
                </button>
            </form>
        </div>

        <!-- Table History Kas Masuk -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
            <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                <span class="material-symbols-outlined text-emerald-400">history</span>
                Riwayat Transaksi Kas Masuk
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                        <tr>
                            <th class="p-3.5 rounded-l-xl">No. Transaksi</th>
                            <th class="p-3.5">Tanggal</th>
                            <th class="p-3.5">Kategori</th>
                            <th class="p-3.5 text-right">Nominal</th>
                            <th class="p-3.5">No. Ref</th>
                            <th class="p-3.5">Keterangan</th>
                            <th class="p-3.5 rounded-r-xl">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 font-medium">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3.5 font-mono font-bold text-cyan-300">{{ $tx->transaction_number }}</td>
                                <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3.5 text-white font-bold">{{ $tx->category->name ?? 'Umum' }}</td>
                                <td class="p-3.5 text-right font-black font-mono text-emerald-400 text-sm">
                                    +Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="p-3.5 font-mono text-slate-400 text-xs">{{ $tx->reference_number ?: '-' }}</td>
                                <td class="p-3.5 text-slate-400 max-w-[180px] truncate" title="{{ $tx->notes }}">{{ $tx->notes ?: '-' }}</td>
                                <td class="p-3.5 text-slate-400 text-xs truncate max-w-[100px]">{{ $tx->user->name ?? 'Admin' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">Belum ada transaksi kas masuk yang dicatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
