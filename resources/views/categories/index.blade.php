@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-[1600px] mx-auto">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400">category</span>
                Master Kategori Produk
            </h1>
            <p class="text-xs text-slate-400">Kelompokkan produk toko ke dalam kategori untuk kemudahan transaksi kasir</p>
        </div>

        <!-- Add Category Button Trigger Modal -->
        <button onclick="openModal('addCategoryModal')" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow flex items-center gap-2 transition-all self-start sm:self-auto">
            <span class="material-symbols-outlined text-base">add_circle</span>
            <span>+ Tambah Kategori</span>
        </button>
    </div>

    <!-- Category Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($categories as $category)
            <div class="glass-card rounded-2xl p-5 space-y-4 hover:border-brand-500/40 transition-all group relative">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30 group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-2xl">{{ $category->icon }}</span>
                    </div>
                    
                    <div class="flex items-center gap-1">
                        <button onclick="editCategory({{ json_encode($category) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" title="Edit">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </button>
                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" title="Hapus">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div>
                    <h3 class="text-base font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $category->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1">Slug: <code class="text-slate-300">{{ $category->slug }}</code></p>
                </div>

                <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs font-medium">
                    <span class="text-slate-400">Total Produk</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-slate-800 text-cyan-300 border border-slate-700 font-bold">
                        {{ $category->products_count }} item
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card rounded-2xl p-12 text-center text-slate-400 space-y-3">
                <span class="material-symbols-outlined text-5xl text-slate-600">category</span>
                <p class="text-sm font-semibold">Belum ada kategori terdaftar.</p>
            </div>
        @endforelse
    </div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-md w-full p-6 space-y-5 border border-slate-700">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white">Tambah Kategori Baru</h3>
            <button onclick="closeModal('addCategoryModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Kategori</label>
                <input type="text" name="name" required placeholder="Contoh: Kopi & Coffee" class="w-full bg-slate-900 text-sm text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Icon Google Symbols (Opsional)</label>
                <input type="text" name="icon" placeholder="Contoh: local_cafe, cookie, flatware" class="w-full bg-slate-900 text-sm text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                <span class="text-[11px] text-slate-500">Gunakan nama ikon dari Material Symbols</span>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addCategoryModal')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-md w-full p-6 space-y-5 border border-slate-700">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white">Edit Kategori</h3>
            <button onclick="closeModal('editCategoryModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="editCategoryForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Kategori</label>
                <input type="text" id="edit_name" name="name" required class="w-full bg-slate-900 text-sm text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Icon Google Symbols</label>
                <input type="text" id="edit_icon" name="icon" class="w-full bg-slate-900 text-sm text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editCategoryModal')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function editCategory(cat) {
        document.getElementById('editCategoryForm').action = `/categories/${cat.id}`;
        document.getElementById('edit_name').value = cat.name;
        document.getElementById('edit_icon').value = cat.icon;
        openModal('editCategoryModal');
    }
</script>
@endsection
