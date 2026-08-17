@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-[1600px] mx-auto">
    
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

    <!-- Search & Filter Bar (Pencarian Produk Raksasa Dominan & Kategori diperkecil) -->
    <div class="glass-card rounded-2xl p-4 sm:p-5 space-y-3 border-2 border-brand-500/30 shadow-2xl">
        <form action="{{ route('products.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
            
            <!-- INPUT PENCARIAN PRODUK DIPERBESAR & DOMINAN -->
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-brand-400 text-2xl">search</span>
                <input type="text" id="productSearchInput" oninput="filterProductsLive()" name="search" value="{{ request('search') }}" placeholder="🔍 KETIK NAMA PRODUK ATAU BARCODE DI SINI (LIVE SEARCH)..." class="w-full bg-slate-950 text-sm sm:text-base text-white font-extrabold rounded-2xl pl-12 pr-4 py-3.5 border-2 border-brand-500/60 focus:outline-none focus:border-brand-400 focus:ring-4 focus:ring-brand-500/20 shadow-inner">
            </div>

            <!-- FILTER KATEGORI DIPERKECIL & COMPACT -->
            <div class="w-full sm:w-36 lg:w-44 shrink-0">
                <select name="category_id" onchange="this.form.submit()" data-placeholder="-- Kategori --" class="select-searchable w-full bg-slate-900 text-xs font-semibold text-slate-300 rounded-xl px-2.5 py-3 border border-slate-800 focus:outline-none focus:border-brand-500">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-extrabold w-full sm:w-auto border border-slate-700 transition-all flex items-center justify-center gap-1 shrink-0">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Filter</span>
            </button>
        </form>

        <div class="flex items-center gap-2 text-xs font-semibold text-amber-400/90 pt-1 border-t border-slate-800/80">
            <span class="material-symbols-outlined text-base">warning</span>
            <span>Urutan Otomatis: Produk dengan stok menipis (&le;5) dan stok habis (0) otomatis ditampilkan di posisi paling atas.</span>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5" id="productsGrid">
        @forelse($products as $product)
            <div class="product-card-item glass-card rounded-2xl p-5 space-y-4 hover:border-brand-500/40 transition-all flex flex-col justify-between group" data-name="{{ strtolower($product->name) }}" data-barcode="{{ strtolower($product->barcode) }}">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-[10px] font-extrabold">
                            {{ $product->category->name ?? 'Umum' }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-500 font-bold">ID: #{{ $product->id }}</span>
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
                        <span class="text-xs font-bold text-slate-300">Harga Jual Ecer</span>
                        <span class="text-base font-extrabold text-white">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                    </div>

                    @if($product->wholesalePrices && $product->wholesalePrices->count() > 0)
                        <div class="p-2 rounded-xl bg-purple-950/40 border border-purple-500/30 text-[11px] space-y-1">
                            <span class="font-bold text-purple-300 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">sell</span> Tier Harga Grosir:
                            </span>
                            @foreach($product->wholesalePrices as $tier)
                                <div class="flex justify-between text-slate-300">
                                    <span>&ge; {{ $tier->min_qty }} {{ $product->unit }} ({{ $tier->unit_label }})</span>
                                    <span class="font-bold text-amber-400">Rp {{ number_format($tier->price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

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

                    <!-- TOMBOL EDIT PRODUK MENCILAK & JELAS DI BAAGIAN BAWAH KARTU -->
                    <div class="pt-2 flex items-center gap-2">
                        <button type="button" onclick="editProductFromBtn(this)" data-product="{{ base64_encode(json_encode($product)) }}" class="flex-1 py-2.5 rounded-xl bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/40 text-xs font-black transition-all flex items-center justify-center gap-1.5 shadow-md group-hover:bg-blue-600 group-hover:text-white">
                            <span class="material-symbols-outlined text-base">edit</span>
                            <span>Edit Data Produk</span>
                        </button>
                        <button type="button" onclick="showQrModalFromBtn(this)" data-product="{{ base64_encode(json_encode($product)) }}" class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-cyan-400 border border-slate-800 text-xs font-bold transition-all" title="Cetak Label QR Code">
                            <span class="material-symbols-outlined text-base">qr_code_2</span>
                        </button>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Hapus produk {{ $product->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2.5 rounded-xl bg-slate-900 hover:bg-rose-600 text-slate-400 hover:text-white border border-slate-800 text-xs font-bold transition-all" title="Hapus Produk">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </form>
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

<!-- QR Code Printable Label Modal -->
<div id="qrModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
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
<div id="addProductModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
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
                    <select name="category_id" required data-placeholder="-- Pilih Kategori --" class="select-searchable w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                        <option value=""></option>
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
                    <input type="text" name="purchase_price" required data-type="currency" placeholder="25.000" class="input-currency w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual</label>
                    <input type="text" name="selling_price" required data-type="currency" placeholder="45.000" class="input-currency w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stok Awal</label>
                    <input type="number" name="stock" required min="0" placeholder="50" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Satuan Eceran</label>
                    <input type="text" name="unit" required placeholder="pcs, botol, pack" class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <!-- Tier Harga Grosir Inputs (Optional) -->
                <div class="col-span-2 p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">sell</span>
                            Tier Harga Grosir Berjenjang (Opsional)
                        </span>
                        <button type="button" onclick="addWholesaleRow('add_wholesale_container')" class="text-[11px] font-bold text-brand-400 hover:underline">+ Tambah Tier</button>
                    </div>
                    <div id="add_wholesale_container" class="space-y-2">
                        <!-- Dynamic Rows -->
                        <div class="grid grid-cols-3 gap-2">
                            <input type="number" name="wholesale_min_qty[]" min="2" placeholder="Min Qty (misal 6)" class="bg-slate-950 text-xs text-white rounded-lg p-2 border border-slate-800">
                            <input type="text" name="wholesale_unit_label[]" placeholder="Nama Satuan (misal Pak)" class="bg-slate-950 text-xs text-white rounded-lg p-2 border border-slate-800">
                            <input type="text" name="wholesale_price[]" data-type="currency" placeholder="Harga Grosir (Rp)" class="input-currency bg-slate-950 text-xs text-amber-400 font-bold rounded-lg p-2 border border-slate-800">
                        </div>
                    </div>
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
<div id="editProductModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-lg w-full p-6 space-y-5 border border-slate-700 max-h-[90vh] overflow-y-auto">
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
                    <input type="text" id="edit_purchase_price" name="purchase_price" required data-type="currency" class="input-currency w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual Ecer</label>
                    <input type="text" id="edit_selling_price" name="selling_price" required data-type="currency" class="input-currency w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stok</label>
                    <input type="number" id="edit_stock" name="stock" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Satuan</label>
                    <input type="text" id="edit_unit" name="unit" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-4 py-2.5 border border-slate-800">
                </div>

                <!-- Tier Harga Grosir Inputs Edit -->
                <div class="col-span-2 p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">sell</span>
                            Tier Harga Grosir Berjenjang
                        </span>
                        <button type="button" onclick="addWholesaleRow('edit_wholesale_container')" class="text-[11px] font-bold text-brand-400 hover:underline">+ Tambah Tier</button>
                    </div>
                    <div id="edit_wholesale_container" class="space-y-2">
                        <!-- Dynamic Rows loaded via JS -->
                    </div>
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
    function filterProductsLive() {
        const query = (document.getElementById('productSearchInput')?.value || '').trim().toLowerCase();
        const items = document.querySelectorAll('.product-card-item');
        
        items.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const barcode = card.getAttribute('data-barcode') || '';
            if (name.includes(query) || barcode.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function openModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }

    function showQrModalFromBtn(btn) {
        try {
            const raw = btn.getAttribute('data-product');
            const prod = JSON.parse(atob(raw));
            showQrModal(prod);
        } catch(e) {
            console.error("Error opening QR modal", e);
        }
    }

    function editProductFromBtn(btn) {
        try {
            const raw = btn.getAttribute('data-product');
            const prod = JSON.parse(atob(raw));
            editProduct(prod);
        } catch(e) {
            console.error("Error opening edit product modal", e);
        }
    }

    function showQrModal(prod) {
        document.getElementById('qrProdName').textContent = prod.name;
        document.getElementById('qrCodeText').textContent = prod.barcode;
        document.getElementById('qrProdPrice').textContent = `Rp ${new Intl.NumberFormat('id-ID').format(prod.selling_price)}`;
        document.getElementById('qrImg').src = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(prod.barcode)}`;
        openModal('qrModal');
    }

    function addWholesaleRow(containerId, minQty = '', unitLabel = '', price = '') {
        const container = document.getElementById(containerId);
        if (!container) return;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-3 gap-2 items-center';
        row.innerHTML = `
            <input type="number" name="wholesale_min_qty[]" value="${minQty}" min="2" placeholder="Min Qty (misal 6)" class="bg-slate-950 text-xs text-white rounded-lg p-2 border border-slate-800">
            <input type="text" name="wholesale_unit_label[]" value="${unitLabel}" placeholder="Nama Satuan (misal Pak)" class="bg-slate-950 text-xs text-white rounded-lg p-2 border border-slate-800">
            <div class="flex items-center gap-1">
                <input type="text" name="wholesale_price[]" value="${price ? formatRupiah(price) : ''}" data-type="currency" placeholder="Harga (Rp)" class="input-currency bg-slate-950 text-xs text-amber-400 font-bold rounded-lg p-2 border border-slate-800 flex-1">
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-rose-400 hover:text-rose-200 text-base font-bold p-1">×</button>
            </div>
        `;
        container.appendChild(row);
    }

    function editProduct(prod) {
        document.getElementById('editProductForm').action = `/products/${prod.id}`;
        document.getElementById('edit_prod_name').value = prod.name;
        document.getElementById('edit_category_id').value = prod.category_id;
        document.getElementById('edit_barcode').value = prod.barcode;
        document.getElementById('edit_purchase_price').value = formatRupiah(prod.purchase_price);
        document.getElementById('edit_selling_price').value = formatRupiah(prod.selling_price);
        document.getElementById('edit_stock').value = prod.stock;
        document.getElementById('edit_unit').value = prod.unit;

        const container = document.getElementById('edit_wholesale_container');
        if (container) {
            container.innerHTML = '';
            if (prod.wholesale_prices && prod.wholesale_prices.length > 0) {
                prod.wholesale_prices.forEach(w => {
                    addWholesaleRow('edit_wholesale_container', w.min_qty, w.unit_label || '', w.price);
                });
            } else {
                addWholesaleRow('edit_wholesale_container');
            }
        }

        openModal('editProductModal');
    }
</script>
@endsection
