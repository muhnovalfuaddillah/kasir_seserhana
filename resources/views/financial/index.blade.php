@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-400 text-3xl">account_balance_wallet</span>
                Manajemen Keuangan Toko & Gaji
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Pantau seluruh arus kas (Kas Masuk, Kas Keluar, Operasional), gaji karyawan, laporan arus kas, dan analisis Laba Rugi secara presisi.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('financial.cash_in') }}" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">arrow_downward</span>
                <span>+ Kas Masuk</span>
            </a>
            <a href="{{ route('financial.cash_out') }}" class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">arrow_upward</span>
                <span>- Kas Keluar</span>
            </a>
            <a href="{{ route('financial.payrolls') }}" class="px-4 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">badge</span>
                <span>Gaji Karyawan</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('financial.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-emerald-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Ringkasan Keuangan</span>
        </a>
        <a href="{{ route('financial.cash_in') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
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
        <a href="{{ route('financial.profit_loss') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">analytics</span>
            <span>Laporan Laba Rugi</span>
        </a>
    </div>

    <!-- 4 KPI Main Financial Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Saldo Kas Utama -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl flex flex-col justify-between h-full">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Saldo Kas Toko</span>
                <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30">
                    <span class="material-symbols-outlined text-xl">account_balance</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-cyan-300 font-mono">Rp {{ number_format($saldoKasUtama, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Pemasukan POS + Kas Masuk - Kas Keluar</p>
        </div>

        <!-- Card 2: Omset Penjualan POS (Bulan Ini) -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl flex flex-col justify-between h-full">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Omset POS (Bulan Ini)</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                    <span class="material-symbols-outlined text-xl">point_of_sale</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono">Rp {{ number_format($omsetPos, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Laba Kotor: Rp {{ number_format($labaKotor, 0, ',', '.') }}</p>
        </div>

        <!-- Card 3: Total Kas Keluar Operasional -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl flex flex-col justify-between h-full">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kas Keluar (Bulan Ini)</span>
                <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                    <span class="material-symbols-outlined text-xl">payments</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-rose-400 font-mono">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400">Termasuk Gaji: Rp {{ number_format($totalGajiPaid, 0, ',', '.') }}</p>
        </div>

        <!-- Card 4: Estimasi Laba Bersih -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2 shadow-xl flex flex-col justify-between h-full">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estimasi Laba Bersih</span>
                <div class="w-10 h-10 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center border border-purple-500/30">
                    <span class="material-symbols-outlined text-xl">trending_up</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black {{ $labaBersih >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-mono">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </div>
            <a href="{{ route('financial.profit_loss') }}" class="text-[11px] font-bold text-purple-400 hover:underline flex items-center gap-1">
                <span>Rincian Laporan Laba Rugi</span>
                <span class="material-symbols-outlined text-xs">arrow_forward</span>
            </a>
        </div>
    </div>

    <!-- Recent Cash Transactions Table -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-400">history</span>
                Transaksi Kas Masuk & Keluar Terkini
            </h3>
            <div class="flex items-center gap-3">
                <a href="{{ route('financial.cash_in') }}" class="text-xs font-bold text-emerald-400 hover:underline">+ Kas Masuk</a>
                <span class="text-slate-700">•</span>
                <a href="{{ route('financial.cash_out') }}" class="text-xs font-bold text-rose-400 hover:underline">- Kas Keluar</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Transaksi</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5 text-center">Jenis</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5 text-right">Nominal</th>
                        <th class="p-3.5">Catatan / Keterangan</th>
                        <th class="p-3.5 rounded-r-xl">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-cyan-300">{{ $tx->transaction_number }}</td>
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3.5 text-center">
                                @if($tx->type === 'in')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">KAS MASUK</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">KAS KELUAR</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-white font-bold">{{ $tx->category->name ?? 'Umum' }}</td>
                            <td class="p-3.5 text-right font-black font-mono text-sm {{ $tx->type === 'in' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $tx->type === 'in' ? '+Rp ' : '-Rp ' }}{{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-slate-400 max-w-[200px] truncate" title="{{ $tx->notes }}">{{ $tx->notes ?: '-' }}</td>
                            <td class="p-3.5 text-slate-400 text-xs truncate max-w-[100px]">{{ $tx->user->name ?? 'Admin' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">Belum ada transaksi kas yang dicatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
