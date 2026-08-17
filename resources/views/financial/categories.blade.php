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
                <span class="material-symbols-outlined text-cyan-400 text-3xl">category</span>
                Master Kategori Pengeluaran & Pemasukan
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Pengelompokan jenis beban operasional dan akun pemasukan untuk analisis keuangan toko yang tertata rapi.</p>
        </div>

        <button type="button" onclick="openCategoryModal()" class="px-4 py-2.5 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>+ Tambah Kategori Baru</span>
        </button>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('financial.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
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
        <a href="{{ route('financial.categories') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-cyan-600 text-white shadow-glow transition-all flex items-center gap-2">
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

    <!-- Category Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($categories as $cat)
            <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-4 shadow-xl flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $cat->type === 'in' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border-rose-500/30' }}">
                            {{ $cat->type === 'in' ? 'Pemasukan Kas' : 'Pengeluaran / Beban' }}
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-400">{{ $cat->transactions_count }} Transaksi</span>
                    </div>

                    <div>
                        <h3 class="text-base font-extrabold text-white">{{ $cat->name }}</h3>
                        <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $cat->description ?: 'Tidak ada deskripsi.' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-end">
                    <form action="{{ route('financial.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-bold text-rose-400 hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">delete</span>
                            <span>Hapus Kategori</span>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Form Tambah Kategori -->
<div id="categoryModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400">category</span>
                Tambah Kategori Keuangan
            </h3>
            <button type="button" onclick="closeCategoryModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('financial.categories.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Nama Kategori <span class="text-rose-400">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Beban Pemeliharaan AC" class="w-full bg-slate-950 text-xs sm:text-sm text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-cyan-500">
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Tipe Kategori <span class="text-rose-400">*</span></label>
                <select name="type" required class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-cyan-500">
                    <option value="out">Pengeluaran / Beban (Expense)</option>
                    <option value="in">Pemasukan Kas (Income)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Deskripsi Penjelasan</label>
                <textarea name="description" rows="3" placeholder="Penjelasan kegunaan kategori ini..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs shadow-glow">SIMPAN KATEGORI</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCategoryModal() {
        document.getElementById('categoryModal').classList.remove('hidden');
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }
</script>
@endsection
