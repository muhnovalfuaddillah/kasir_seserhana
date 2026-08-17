@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400 text-3xl">analytics</span>
                Pusat Laporan & Analitik Bisnis Toko
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Akses 8 laporan bisnis lengkap: Penjualan Harian, Bulanan, Produk Terlaris, Produk Tidak Laku, Stok, Pembelian, Pengeluaran, & Laba Bersih.</p>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Semua Laporan</span>
        </a>
        <a href="{{ route('reports.daily_sales') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">today</span>
            <span>Penjualan Harian</span>
        </a>
        <a href="{{ route('reports.monthly_sales') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">calendar_month</span>
            <span>Penjualan Bulanan</span>
        </a>
        <a href="{{ route('reports.best_sellers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">local_fire_department</span>
            <span>Produk Terlaris</span>
        </a>
        <a href="{{ route('reports.slow_moving') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">inventory_2</span>
            <span>Produk Tidak Laku</span>
        </a>
        <a href="{{ route('reports.stock') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">warehouse</span>
            <span>Laporan Stok</span>
        </a>
        <a href="{{ route('reports.purchases') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">local_shipping</span>
            <span>Laporan Pembelian</span>
        </a>
        <a href="{{ route('reports.expenses') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">payments</span>
            <span>Laporan Pengeluaran</span>
        </a>
        <a href="{{ route('reports.net_profit') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">trending_up</span>
            <span>Laporan Laba Bersih</span>
        </a>
    </div>

    <!-- 8 Sub-Reports Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- 1. Penjualan Harian -->
        <a href="{{ route('reports.daily_sales') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-emerald-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                <span class="material-symbols-outlined text-2xl">today</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-emerald-300 transition-colors">1. Penjualan Harian</h3>
                <p class="text-xs text-slate-400 mt-1">Rincian omset penjualan per tanggal/jam, metode bayar, & rata-rata nota.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-emerald-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 2. Penjualan Bulanan -->
        <a href="{{ route('reports.monthly_sales') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-cyan-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30">
                <span class="material-symbols-outlined text-2xl">calendar_month</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-cyan-300 transition-colors">2. Penjualan Bulanan</h3>
                <p class="text-xs text-slate-400 mt-1">Tren pertumbuhan omset bulanan, grafik harian, & perbandingan nota.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-cyan-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 3. Produk Terlaris -->
        <a href="{{ route('reports.best_sellers') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-amber-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center border border-amber-500/30">
                <span class="material-symbols-outlined text-2xl">local_fire_department</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-amber-300 transition-colors">3. Produk Terlaris</h3>
                <p class="text-xs text-slate-400 mt-1">Ranking barang paling laris berdasarkan Qty & Total Omset yang dihasilkan.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-amber-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 4. Produk Tidak Laku -->
        <a href="{{ route('reports.slow_moving') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-purple-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center border border-purple-500/30">
                <span class="material-symbols-outlined text-2xl">inventory_2</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-purple-300 transition-colors">4. Produk Tidak Laku</h3>
                <p class="text-xs text-slate-400 mt-1">Identifikasi produk yang jarang/tidak pernah terjual untuk cegah *Dead Stock*.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-purple-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 5. Laporan Stok & Valuasi -->
        <a href="{{ route('reports.stock') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-blue-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/20 text-blue-400 flex items-center justify-center border border-blue-500/30">
                <span class="material-symbols-outlined text-2xl">warehouse</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-blue-300 transition-colors">5. Valuasi & Status Stok</h3>
                <p class="text-xs text-slate-400 mt-1">Total nilai aset persediaan barang (Harga Modal/Jual), stok menipis, & habis.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-blue-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 6. Laporan Pembelian -->
        <a href="{{ route('reports.purchases') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-teal-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center border border-teal-500/30">
                <span class="material-symbols-outlined text-2xl">local_shipping</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-teal-300 transition-colors">6. Laporan Pembelian</h3>
                <p class="text-xs text-slate-400 mt-1">Total pengeluaran kulakan barang dari supplier & riwayat stok masuk.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-teal-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 7. Laporan Pengeluaran -->
        <a href="{{ route('reports.expenses') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-rose-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-rose-300 transition-colors">7. Laporan Pengeluaran</h3>
                <p class="text-xs text-slate-400 mt-1">Rincian beban operasional per Kategori (Gaji, Listrik, Sewa Tempat, Pemasaran).</p>
            </div>
            <div class="pt-2 text-xs font-bold text-rose-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 8. Laporan Arus Kas -->
        <a href="{{ route('financial.cashflow') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-cyan-400 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30">
                <span class="material-symbols-outlined text-2xl">water_drop</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-cyan-300 transition-colors">8. Laporan Arus Kas</h3>
                <p class="text-xs text-slate-400 mt-1">Aliran uang kas masuk dan keluar riil per periode operasional toko.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-cyan-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 9. Laporan Laba Rugi -->
        <a href="{{ route('financial.profit_loss') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-purple-400 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center border border-purple-500/30">
                <span class="material-symbols-outlined text-2xl">analytics</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-purple-300 transition-colors">9. Laporan Laba Rugi</h3>
                <p class="text-xs text-slate-400 mt-1">Laporan Laba/Rugi komprehensif (Pendapatan vs HPP vs Beban Operasional).</p>
            </div>
            <div class="pt-2 text-xs font-bold text-purple-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 10. Laporan Laba Bersih -->
        <a href="{{ route('reports.net_profit') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-emerald-400 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                <span class="material-symbols-outlined text-2xl">trending_up</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-emerald-300 transition-colors">10. Laba Bersih & Margin %</h3>
                <p class="text-xs text-slate-400 mt-1">Kalkulasi Laba Bersih presisi & persentase margin keuntungan (*Profit Margin %*).</p>
            </div>
            <div class="pt-2 text-xs font-bold text-emerald-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

        <!-- 11. Laporan Shift Kasir -->
        <a href="{{ route('shifts.index') }}" class="glass-card rounded-3xl p-6 border border-slate-800 hover:border-emerald-500/50 transition-all group space-y-3 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                <span class="material-symbols-outlined text-2xl">point_of_sale</span>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white group-hover:text-emerald-300 transition-colors">11. Shift Buka/Tutup Kas</h3>
                <p class="text-xs text-slate-400 mt-1">Riwayat rekonsiliasi kas laci kasir, modal awal, & perhitungan fisik selisih kas.</p>
            </div>
            <div class="pt-2 text-xs font-bold text-emerald-400 flex items-center gap-1">
                <span>Buka Laporan</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </a>

    </div>
</div>
@endsection
