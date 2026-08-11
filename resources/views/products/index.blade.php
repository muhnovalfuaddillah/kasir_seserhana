@extends('layouts.app')

@section('content')
<main class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-[1600px] mx-auto">
    
    <!-- Page Header & Filters -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400">inventory_2</span>
                Master Produk & QR/Barcode Label
            </h1>
            <p class="text-xs text-slate-400">Kelola barang, cetak stiker QR Code / Barcode produk untuk ditempel pada item</p>
        </div>

        <button onclick="openModal('addProductModal')" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow flex items-center gap-2 transition-all self-start md:self-auto">
            <span class="material-symbols-outlined text-base">add_box</span>
            <span>+ Tambah Produk Baru</span>
        </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="glass-card rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('products.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk atau kode QR/barcode..." class="w-full bg-slate-900 text-xs text-slate-200 rounded-xl pl-10 pr-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <select name="category_id" onchange="this.form.submit()" class="w-full sm:w-48 bg-slate-900 text-xs text-slate-200 rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold w-full sm:w-auto">
                Filter
            </button>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($products as $product)
            <div class="glass-card rounded-2xl p-5 space-y-4 hover:border-brand-500/40 transition-all flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-[10px] font-bold">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </span>
                        
                        <div class="flex items-center gap-1">
                            <button onclick="showQrModal({{ json_encode($product) }})" class="p-1 rounded text-cyan-400 hover:text-cyan-200 hover:bg-slate-800" title="Cetak QR Code Label">
                                <span class="material-symbols-outlined text-lg">qr_code_2</span>
                            </button>
                            <button onclick="editProduct({{ json_encode($product) }})" class="p-1 rounded text-slate-400 hover:text-white" title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </button>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded text-slate-400 hover:text-rose-400" title="Hapus">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- QR Code Preview & Name -->
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 bg-white p-1 rounded-xl shrink-0 flex items-center justify-center border border-slate-700">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($product->barcode) }}" alt="QR Code" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-white group-hover:text-brand-300 transition-colors line-clamp-2">{{ $product->name }}</h3>
                            <p class="text-[11px] text-slate-400 font-mono mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">qr_code_scanner</span>
                                {{ $product->barcode }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800/80 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Harga Beli (Modal)</span>
                        <span class="text-slate-300 font-semibold">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-300">Harga Jual</span>
                        <span class="text-base font-extrabold text-white">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs text-slate-400">Stok Tersedia</span>
                        @if($product->stock <= 5)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                {{ $product->stock }} {{ $product->unit }} (Hampir Habis)
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                {{ $product->stock }} {{ $product->unit }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card rounded-2xl p-12 text-center text-slate-400 space-y-3">
                <span class="material-symbols-outlined text-5xl text-slate-600">inventory_2</span>
                <p class="text-sm font-semibold">Belum ada produk yang ditemukan.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-4">
        {{ $products->links() }}
    </div>
</main>

<!-- QR Code Printable Label Modal -->
<div id="qrModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white text-slate-900 rounded-2xl max-w-xs w-full p-6 space-y-4 text-center shadow-2xl">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Stiker Label QR Code Produk</h3>
        
        <div id="qrLabelArea" class="border border-dashed border-slate-300 p-4 rounded-xl space-y-2 bg-slate-50">
            <h4 id="qrProdName" class="font-extrabold text-sm text-slate-900 leading-tight"></h4>
            <div class="w-32 h-32 mx-auto bg-white p-2 rounded-lg shadow-sm border border-slate-200 flex items-center justify-center">
                <img id="qrImg" src="" alt="QR Code" class="w-full h-full object-contain">
            </div>
            <p id="qrCodeText" class="font-mono text-xs text-slate-700 font-bold tracking-wider"></p>
            <p id="qrProdPrice" class="text-base font-black text-emerald-600"></p>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Stiker</span>
            </button>
            <button onclick="closeModal('qrModal')" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-800 font-bold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-lg w-full p-6 space-y-5 border border-slate-700">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white">Tambah Produk Baru</h3>
            <button onclick="closeModal('addProductModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('products.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Produk</label>
                    <input type="text" name="name" required placeholder="Contoh: Kopi Arabika 250g" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori</label>
                    <select name="category_id" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kode QR / Barcode</label>
                    <input type="text" name="barcode" placeholder="Kosongkan untuk auto-generate" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Beli (Modal)</label>
                    <input type="number" name="purchase_price" required min="0" placeholder="25000" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual</label>
                    <input type="number" name="selling_price" required min="0" placeholder="45000" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stok Awal</label>
                    <input type="number" name="stock" required min="0" placeholder="50" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Satuan</label>
                    <input type="text" name="unit" required placeholder="pcs, botol, pack" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addProductModal')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-glow">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-lg w-full p-6 space-y-5 border border-slate-700">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white">Edit Produk</h3>
            <button onclick="closeModal('editProductModal')" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="editProductForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Produk</label>
                    <input type="text" id="edit_prod_name" name="name" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori</label>
                    <select id="edit_category_id" name="category_id" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kode QR / Barcode</label>
                    <input type="text" id="edit_barcode" name="barcode" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Beli</label>
                    <input type="number" id="edit_purchase_price" name="purchase_price" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual</label>
                    <input type="number" id="edit_selling_price" name="selling_price" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stok</label>
                    <input type="number" id="edit_stock" name="stock" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Satuan</label>
                    <input type="text" id="edit_unit" name="unit" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editProductModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-xs font-semibold shadow-glow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    function showQrModal(prod) {
        document.getElementById('qrProdName').textContent = prod.name;
        document.getElementById('qrCodeText').textContent = prod.barcode;
        document.getElementById('qrProdPrice').textContent = `Rp ${new Intl.NumberFormat('id-ID').format(prod.selling_price)}`;
        document.getElementById('qrImg').src = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(prod.barcode)}`;
        openModal('qrModal');
    }

    function editProduct(prod) {
        document.getElementById('editProductForm').action = `/products/${prod.id}`;
        document.getElementById('edit_prod_name').value = prod.name;
        document.getElementById('edit_category_id').value = prod.category_id;
        document.getElementById('edit_barcode').value = prod.barcode;
        document.getElementById('edit_purchase_price').value = prod.purchase_price;
        document.getElementById('edit_selling_price').value = prod.selling_price;
        document.getElementById('edit_stock').value = prod.stock;
        document.getElementById('edit_unit').value = prod.unit;
        openModal('editProductModal');
    }
</script>
@endsection
