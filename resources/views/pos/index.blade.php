@extends('layouts.app')

@section('content')
<!-- HTML5 QR/Barcode Scanner Script -->
<script src="https://unpkg.com/html5-qrcode"></script>

<!-- Mobile Top Navigation Tab Bar (Only visible on mobile < lg) -->
<div class="lg:hidden flex items-center bg-[#0f172a] border-b border-slate-800 p-2 gap-2 sticky top-0 z-30 shadow-md">
    <button type="button" onclick="switchPosMobileTab('catalog')" id="mobileTabBtnCatalog" class="flex-1 py-2.5 rounded-xl text-xs font-bold bg-brand-600 text-white flex items-center justify-center gap-2 transition-all">
        <span class="material-symbols-outlined text-base">grid_view</span>
        <span>Katalog & Kamera</span>
    </button>
    <button type="button" onclick="switchPosMobileTab('cart')" id="mobileTabBtnCart" class="flex-1 py-2.5 rounded-xl text-xs font-bold bg-slate-800 text-slate-400 flex items-center justify-center gap-2 transition-all relative">
        <span class="material-symbols-outlined text-base">shopping_cart</span>
        <span>Keranjang</span>
        <span id="mobileTabCartBadge" class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-500 text-slate-950 font-black">0</span>
    </button>
</div>

<div class="min-h-[calc(100vh-5rem)] lg:h-[calc(100vh-5rem)] flex flex-col lg:flex-row overflow-y-auto lg:overflow-hidden bg-[#0b0f19] relative pb-24 lg:pb-0">
    
    <!-- Floating Toast Notification for Instant Scan -->
    <div id="scanToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 transition-all duration-300 opacity-0 pointer-events-none transform -translate-y-4">
        <div class="px-5 py-3 rounded-2xl bg-emerald-500 text-slate-950 font-black text-xs shadow-2xl border-2 border-emerald-300 flex items-center gap-3">
            <span class="material-symbols-outlined text-xl animate-bounce">check_circle</span>
            <span id="scanToastMsg">Item berhasil di-scan!</span>
        </div>
    </div>

    <!-- Left Section: Live Camera Widget + Catalog (65% width on desktop) -->
    <div id="posCatalogSection" class="flex-1 flex flex-col min-w-0 p-3 sm:p-6 space-y-4 overflow-y-auto w-full lg:w-auto">
        
        <!-- Top Bar: Header & Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-400">shopping_cart</span>
                    Terminal Kasir POS
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-extrabold border border-emerald-500/40 animate-pulse flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Kamera Aktif
                    </span>
                </h1>
                <p class="text-xs text-slate-400">Scan barcode atau pilih barang langsung dari katalog</p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Toggle Always-On Camera Button -->
                <button type="button" onclick="toggleAlwaysOnCamera()" id="cameraToggleBtn" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-cyan-500/40 font-bold text-xs flex items-center gap-1.5 transition-all shrink-0">
                    <span class="material-symbols-outlined text-base">videocam</span>
                    <span id="cameraToggleText">Sembunyikan Kamera</span>
                </button>

                <!-- Search Input with Clear Button & Shortcut Hint -->
                <div class="relative w-full sm:w-72">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">search</span>
                    <input type="text" id="searchInput" oninput="filterProducts()" placeholder="Cari nama / barcode... [/]" class="w-full bg-slate-900 text-xs text-white rounded-xl pl-9 pr-8 py-2 border border-slate-800 focus:outline-none focus:border-brand-500 transition-all">
                    <button type="button" id="clearSearchBtn" onclick="clearSearchInput()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors" title="Hapus teks pencarian">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Embedded Always-On Camera Viewfinder Widget -->
        <div id="alwaysOnCameraWidget" class="glass-card rounded-2xl p-3 sm:p-4 border border-cyan-500/30 bg-slate-900/90 transition-all flex flex-col md:flex-row items-center gap-4">
            <!-- Camera Viewport Box -->
            <div class="w-full md:w-64 h-36 relative overflow-hidden rounded-xl border-2 border-cyan-500/50 bg-black shadow-inner shrink-0">
                <div id="always-on-qr-reader" class="w-full h-full object-cover"></div>
                <div class="absolute top-2 left-2 px-2 py-0.5 rounded bg-black/70 text-emerald-400 text-[10px] font-bold flex items-center gap-1 pointer-events-none">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    LIVE SCANNER
                </div>
            </div>

            <!-- Scanner Status & Guide Info -->
            <div class="flex-1 space-y-2 text-xs w-full">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-white flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-cyan-400 text-base">qr_code_scanner</span>
                        Status Pemindai Otomatis
                    </h3>
                    <span id="scannerStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        Aktif Me-Scan
                    </span>
                </div>
                <p class="text-slate-400 text-[11px]">
                    Dekatkan barcode ke kamera untuk menambah barang ke keranjang secara otomatis.
                </p>
                <div id="lastScannedItemAlert" class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-[11px] text-slate-300 font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500 text-sm">history</span>
                    <span class="truncate">Item terakhir: <strong id="lastScannedText" class="text-cyan-300">Belum ada barang</strong></span>
                </div>
            </div>
        </div>

        <!-- Category Pills Bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            <button onclick="setCategory('all')" id="cat-all" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all">
                Semua Kategori
            </button>
            @foreach($categories as $cat)
                <button onclick="setCategory({{ $cat->id }})" id="cat-{{ $cat->id }}" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Products Grid Cards -->
        <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4 flex-1">
            @foreach($products as $prod)
                <div id="product-card-{{ $prod->id }}" onclick="addToCart({{ json_encode($prod) }})" class="product-card glass-card rounded-2xl p-3 sm:p-4 space-y-2.5 cursor-pointer hover:border-brand-500/50 hover:bg-slate-800/80 transition-all flex flex-col justify-between group active:scale-95 select-none" data-name="{{ strtolower($prod->name) }}" data-barcode="{{ strtolower($prod->barcode ?? '') }}" data-cat="{{ $prod->category_id }}">
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 truncate max-w-[90px]">
                                {{ $prod->category->name ?? 'Produk' }}
                            </span>
                            <span id="stock-badge-{{ $prod->id }}" class="text-[10px] font-bold shrink-0 {{ $prod->stock <= 5 ? 'text-rose-400' : 'text-emerald-400' }}">Stok: {{ $prod->stock }}</span>
                        </div>

                        <!-- Product Thumbnail & Details -->
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white p-0.5 rounded-lg shrink-0 flex items-center justify-center border border-slate-700">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($prod->barcode) }}" alt="QR" class="w-full h-full object-contain">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-xs font-bold text-white group-hover:text-brand-300 transition-colors line-clamp-2 leading-tight">
                                    {{ $prod->name }}
                                </h3>
                                <p class="text-[10px] text-slate-500 font-mono truncate">Code: {{ $prod->barcode }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-xs font-extrabold text-emerald-400">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                        <div class="w-7 h-7 rounded-lg bg-brand-600/20 text-brand-400 flex items-center justify-center group-hover:bg-brand-600 group-hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-base">add</span>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Empty Search Results Alert -->
            <div id="noProductsSearchResult" class="hidden col-span-full glass-card rounded-2xl p-8 text-center text-slate-400 space-y-3">
                <span class="material-symbols-outlined text-4xl text-slate-600">search_off</span>
                <p class="text-xs font-semibold text-slate-300">Tidak ada produk yang cocok dengan pencarian.</p>
                <button type="button" onclick="clearSearchInput()" class="px-3.5 py-1.5 rounded-xl bg-brand-600/20 text-brand-300 hover:bg-brand-600 hover:text-white text-xs font-bold transition-all border border-brand-500/30">
                    Reset Pencarian
                </button>
            </div>
        </div>
    </div>

    <!-- Right Section: Cart Panel & Checkout (35% width on desktop) -->
    <div id="posCartSection" class="w-full lg:w-[420px] bg-[#0f172a] border-t lg:border-t-0 lg:border-l border-slate-800/80 flex flex-col justify-between hidden lg:flex shrink-0 min-h-[500px] lg:min-h-0 lg:h-full">
        
        <!-- Cart Header -->
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between bg-slate-900/60">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400">shopping_bag</span>
                <h2 class="text-sm font-bold text-white">Keranjang Belanja</h2>
                <span id="cartCount" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-500/20 text-brand-300 border border-brand-500/30">0 item</span>
            </div>
            <button onclick="clearCart()" class="text-xs font-semibold text-rose-400 hover:text-rose-300 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">delete_sweep</span>
                <span>Reset</span>
            </button>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div id="cartItemsList" class="flex-1 p-4 overflow-y-auto space-y-3 min-h-[200px]">
            <div id="emptyCartState" class="h-full flex flex-col items-center justify-center text-center text-slate-500 space-y-2 py-12">
                <span class="material-symbols-outlined text-5xl text-slate-700">remove_shopping_cart</span>
                <p class="text-xs font-medium">Keranjang masih kosong.<br>Pilih produk atau scan barcode.</p>
            </div>
        </div>

        <!-- Cart Summary & Payment Calculation -->
        <div class="p-4 bg-slate-900/90 border-t border-slate-800 space-y-3">
            
            <!-- Customer Name & Discount Row -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">Nama Pelanggan</label>
                    <input type="text" id="customerNameInput" placeholder="Pelanggan Umum" class="w-full bg-slate-950 text-xs text-white rounded-lg px-3 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">Potongan Diskon (Rp)</label>
                    <input type="number" id="discountInput" oninput="renderCartSummary()" placeholder="0" min="0" value="0" class="w-full bg-slate-950 text-xs text-white rounded-lg px-3 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Total Amount Display Header -->
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                <span class="text-xs text-slate-400 font-semibold">Total Tagihan</span>
                <span id="finalTotalDisplay" class="text-xl font-black text-emerald-400">Rp 0</span>
            </div>

            <!-- Payment Method Tabs -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="setPaymentMethod('cash')" id="pay-cash" class="pay-method-btn p-2 rounded-xl text-xs font-bold bg-brand-600 text-white border border-brand-500 shadow-glow flex flex-col items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-base">payments</span>
                        <span>Tunai</span>
                    </button>
                    <button type="button" onclick="setPaymentMethod('qris')" id="pay-qris" class="pay-method-btn p-2 rounded-xl text-xs font-bold bg-slate-950 text-slate-400 border border-slate-800 hover:text-white flex flex-col items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                        <span>QRIS</span>
                    </button>
                    <button type="button" onclick="setPaymentMethod('edc')" id="pay-edc" class="pay-method-btn p-2 rounded-xl text-xs font-bold bg-slate-950 text-slate-400 border border-slate-800 hover:text-white flex flex-col items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-base">credit_card</span>
                        <span>EDC / Debit</span>
                    </button>
                </div>
            </div>

            <!-- Pay Amount & Change Calculation -->
            <div class="space-y-2 pt-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-semibold">Uang Diterima (Rp)</span>
                    <input type="number" id="payAmountInput" oninput="calculateChange()" placeholder="0" class="w-32 bg-slate-950 text-right text-xs font-bold text-white rounded-lg px-3 py-1.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>

                <!-- Quick Cash Presets -->
                <div id="quickCashRow" class="flex items-center gap-1.5 overflow-x-auto pb-1">
                    <button type="button" onclick="setQuickCash('exact')" class="px-2.5 py-1 rounded bg-slate-800 text-[10px] font-bold text-slate-300 hover:bg-slate-700">Uang Pas</button>
                    <button type="button" onclick="setQuickCash(50000)" class="px-2 py-1 rounded bg-slate-800 text-[10px] font-bold text-slate-300 hover:bg-slate-700">50rb</button>
                    <button type="button" onclick="setQuickCash(100000)" class="px-2 py-1 rounded bg-slate-800 text-[10px] font-bold text-slate-300 hover:bg-slate-700">100rb</button>
                    <button type="button" onclick="setQuickCash(200000)" class="px-2 py-1 rounded bg-slate-800 text-[10px] font-bold text-slate-300 hover:bg-slate-700">200rb</button>
                </div>

                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-800">
                    <span class="text-slate-400 font-semibold">Kembalian</span>
                    <span id="changeDisplay" class="font-extrabold text-cyan-400">Rp 0</span>
                </div>
            </div>

            <!-- Checkout Submit Button -->
            <button type="button" onclick="processCheckout()" id="checkoutBtn" class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm shadow-glow-emerald transition-all transform active:scale-98 disabled:opacity-50 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                <span>Bayar & Cetak Struk</span>
            </button>
        </div>
    </div>
</div>

<!-- Floating Mobile Quick Checkout Bar -->
<div id="mobileFloatingCartBar" class="lg:hidden fixed bottom-0 left-0 right-0 p-3 bg-[#0f172a]/95 backdrop-blur-xl border-t border-brand-500/40 z-40 flex items-center justify-between shadow-2xl transition-all duration-300 transform translate-y-full">
    <div class="space-y-0.5">
        <span id="mobileFloatingItemCount" class="text-[11px] font-bold text-brand-300">0 Item di keranjang</span>
        <div id="mobileFloatingTotal" class="text-base font-black text-emerald-400">Rp 0</div>
    </div>
    <button type="button" onclick="switchPosMobileTab('cart')" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-extrabold text-xs shadow-glow-emerald flex items-center gap-2 active:scale-95 transition-transform">
        <span>Lihat Keranjang & Bayar</span>
        <span class="material-symbols-outlined text-base">arrow_forward</span>
    </button>
</div>

<!-- Thermal Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white text-slate-900 rounded-2xl max-w-sm w-full p-5 sm:p-6 space-y-4 shadow-2xl font-mono text-xs max-h-[90vh] overflow-y-auto">
        <div class="text-center space-y-1 pb-3 border-b border-dashed border-slate-300">
            <h3 class="text-base font-bold uppercase tracking-wider text-slate-900">KINETIC POS STORE</h3>
            <p class="text-[11px] text-slate-600">Jl. Sudirman No. 88, Jakarta Pusat</p>
            <p class="text-[11px] text-slate-600">Telp: (021) 555-0199</p>
        </div>

        <div class="space-y-1 text-[11px] text-slate-600 pb-2 border-b border-dashed border-slate-300">
            <div class="flex justify-between"><span id="rcptInvoice">No: TRX-000</span><span id="rcptDate">10/08/2026</span></div>
            <div class="flex justify-between"><span>Kasir: <strong id="rcptCashier" class="font-bold text-slate-900">Kasir</strong></span><span id="rcptCustomer">Pelanggan: Umum</span></div>
        </div>

        <div id="rcptItems" class="space-y-2 py-2 border-b border-dashed border-slate-300"></div>

        <div class="space-y-1 pt-1 text-[11px]">
            <div class="flex justify-between"><span>Subtotal:</span><span id="rcptSubtotal">Rp 0</span></div>
            <div class="flex justify-between"><span>Diskon:</span><span id="rcptDiscount">Rp 0</span></div>
            <div class="flex justify-between font-bold text-sm text-slate-900 border-t border-slate-200 pt-1"><span>TOTAL:</span><span id="rcptTotal">Rp 0</span></div>
            <div class="flex justify-between text-slate-600"><span>Bayar (<span id="rcptMethod">TUNAI</span>):</span><span id="rcptPay">Rp 0</span></div>
            <div class="flex justify-between text-slate-600"><span>Kembali:</span><span id="rcptChange">Rp 0</span></div>
        </div>

        <div class="text-center pt-2 flex flex-col items-center justify-center">
            <img id="rcptQrImg" src="" alt="QR Invoice" class="w-24 h-24 object-contain border p-1">
            <p class="text-[9px] text-slate-500 mt-1">Scan untuk verifikasi nota digital</p>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Nota</span>
            </button>
            <button onclick="closeReceiptModal()" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-800 font-bold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    const productsData = @json($products);
    let cart = [];
    let currentCategory = 'all';
    let currentPaymentMethod = 'cash';
    let html5QrCode = null;
    let isCameraRunning = false;
    let lastScanTime = 0;

    // Initialize Always-On Camera Scanner on Page Load
    document.addEventListener('DOMContentLoaded', function() {
        startAlwaysOnCamera();
        window.addEventListener('resize', handleResponsiveResize);
        handleResponsiveResize();
    });

    function switchPosMobileTab(tab) {
        const catalogSec = document.getElementById('posCatalogSection');
        const cartSec = document.getElementById('posCartSection');
        const btnCatalog = document.getElementById('mobileTabBtnCatalog');
        const btnCart = document.getElementById('mobileTabBtnCart');

        if (tab === 'catalog') {
            catalogSec.classList.remove('hidden');
            cartSec.classList.add('hidden');
            cartSec.classList.remove('flex');

            btnCatalog.className = "flex-1 py-2.5 rounded-xl text-xs font-bold bg-brand-600 text-white flex items-center justify-center gap-2 transition-all";
            btnCart.className = "flex-1 py-2.5 rounded-xl text-xs font-bold bg-slate-800 text-slate-400 flex items-center justify-center gap-2 transition-all relative";
        } else {
            catalogSec.classList.add('hidden');
            cartSec.classList.remove('hidden');
            cartSec.classList.add('flex');

            btnCart.className = "flex-1 py-2.5 rounded-xl text-xs font-bold bg-brand-600 text-white flex items-center justify-center gap-2 transition-all relative";
            btnCatalog.className = "flex-1 py-2.5 rounded-xl text-xs font-bold bg-slate-800 text-slate-400 flex items-center justify-center gap-2 transition-all";
        }
        updateMobileFloatingBar();
    }

    function handleResponsiveResize() {
        const catalogSec = document.getElementById('posCatalogSection');
        const cartSec = document.getElementById('posCartSection');

        if (window.innerWidth >= 1024) {
            catalogSec.classList.remove('hidden');
            cartSec.classList.remove('hidden');
            cartSec.classList.add('flex');
        }
        updateMobileFloatingBar();
    }

    function updateMobileFloatingBar() {
        const floatingBar = document.getElementById('mobileFloatingCartBar');
        const totalItems = cart.reduce((sum, i) => sum + i.qty, 0);
        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const finalTotal = Math.max(0, subtotal - discount);

        const badge = document.getElementById('mobileTabCartBadge');
        if (badge) badge.textContent = totalItems;

        if (!floatingBar) return;

        const catalogSec = document.getElementById('posCatalogSection');
        const isCatalogVisible = !catalogSec.classList.contains('hidden');

        if (totalItems > 0 && isCatalogVisible && window.innerWidth < 1024) {
            floatingBar.classList.remove('translate-y-full');
            document.getElementById('mobileFloatingItemCount').textContent = `${totalItems} Item dipilih`;
            document.getElementById('mobileFloatingTotal').textContent = `Rp ${formatRupiah(finalTotal)}`;
        } else {
            floatingBar.classList.add('translate-y-full');
        }
    }

    function startAlwaysOnCamera() {
        const container = document.getElementById('always-on-qr-reader');
        if (!container) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("always-on-qr-reader");
        }

        const config = {
            fps: 30, // 30 FPS high speed real-time
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                return {
                    width: Math.floor(viewfinderWidth * 0.85),
                    height: Math.floor(viewfinderHeight * 0.7)
                };
            },
            aspectRatio: 1.777778, // Widescreen viewport
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                const found = scanBarcode(decodedText);
                if (found) {
                    document.getElementById('lastScannedText').innerHTML = `<span class="text-emerald-400 font-extrabold">${found.name}</span> (Rp ${formatRupiah(found.selling_price)})`;
                } else {
                    document.getElementById('lastScannedText').innerHTML = `<span class="text-rose-400 font-extrabold">QR '${decodedText}' tidak terdaftar</span>`;
                }
            },
            (errorMessage) => {}
        ).then(() => {
            isCameraRunning = true;
            document.getElementById('cameraToggleText').textContent = 'Sembunyikan Kamera';
            document.getElementById('scannerStatusBadge').textContent = 'Aktif Me-Scan';
            document.getElementById('scannerStatusBadge').className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
        }).catch(err => {
            isCameraRunning = false;
            document.getElementById('scannerStatusBadge').textContent = 'Kamera Matikan/Izin Ditolak';
            document.getElementById('scannerStatusBadge').className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30';
        });
    }

    function toggleAlwaysOnCamera() {
        const widget = document.getElementById('alwaysOnCameraWidget');
        const btnText = document.getElementById('cameraToggleText');

        if (widget.classList.contains('hidden')) {
            widget.classList.remove('hidden');
            btnText.textContent = 'Sembunyikan Kamera';
            if (!isCameraRunning) startAlwaysOnCamera();
        } else {
            widget.classList.add('hidden');
            btnText.textContent = 'Tampilkan Kamera';
        }
    }

    // Audio Beep Feedback
    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1050, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch (e) {}
    }

    // Toast alert feedback
    function showToast(msg) {
        const toast = document.getElementById('scanToast');
        document.getElementById('scanToastMsg').textContent = msg;
        toast.classList.remove('opacity-0', '-translate-y-4', 'pointer-events-none');
        toast.classList.add('opacity-100', 'translate-y-0');
        
        setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', '-translate-y-4', 'pointer-events-none');
        }, 1500);
    }

    function addToCart(product) {
        const liveProduct = productsData.find(p => p.id === product.id) || product;
        const existing = cart.find(item => item.id === liveProduct.id);
        if (existing) {
            if (existing.qty < liveProduct.stock) {
                existing.qty++;
            } else {
                alert(`Stok maksimal untuk '${liveProduct.name}' hanya ${liveProduct.stock}!`);
                return;
            }
        } else {
            if (liveProduct.stock <= 0) {
                alert(`Stok '${liveProduct.name}' sudah habis!`);
                return;
            }
            cart.push({
                id: liveProduct.id,
                name: liveProduct.name,
                barcode: liveProduct.barcode,
                selling_price: liveProduct.selling_price,
                stock: liveProduct.stock,
                qty: 1
            });
        }
        playBeep();
        showToast(`✅ ${liveProduct.name} (+1)`);
        renderCart();
    }

    function scanBarcode(code) {
        const now = Date.now();
        if (now - lastScanTime < 500) return null; // Throttle 500ms
        lastScanTime = now;

        const product = productsData.find(p => p.barcode && p.barcode.toLowerCase() === code.trim().toLowerCase());
        if (product) {
            addToCart(product);
            return product;
        }
        return null;
    }

    // USB Barcode & Keyboard Shortcut Listener
    let barcodeBuffer = '';
    let barcodeTimer = null;
    document.addEventListener('keydown', function(e) {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        
        // Keyboard Shortcut: press '/' to quickly jump & focus search input
        if (e.key === '/' && activeTag !== 'input' && activeTag !== 'textarea') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
            return;
        }

        if (activeTag === 'input' && document.activeElement.id !== 'searchInput') return;

        if (e.key === 'Enter') {
            if (barcodeBuffer.length >= 3) {
                scanBarcode(barcodeBuffer);
                barcodeBuffer = '';
            }
        } else if (e.key.length === 1) {
            barcodeBuffer += e.key;
            clearTimeout(barcodeTimer);
            barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 200);
        }
    });

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        const liveProduct = productsData.find(p => p.id === id);
        const maxStock = liveProduct ? liveProduct.stock : item.stock;

        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (item.qty > maxStock) {
            item.qty = maxStock;
            alert(`Stok maksimal adalah ${maxStock}!`);
        }
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const cartList = document.getElementById('cartItemsList');

        if (cart.length === 0) {
            cartList.innerHTML = `
                <div class="h-full flex flex-col items-center justify-center text-center text-slate-500 space-y-2 py-12">
                    <span class="material-symbols-outlined text-5xl text-slate-700">remove_shopping_cart</span>
                    <p class="text-xs font-medium">Keranjang masih kosong.<br>Pilih produk atau scan barcode.</p>
                </div>`;
        } else {
            cartList.innerHTML = cart.map(item => `
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between gap-3 animate-fade-in">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="w-8 h-8 bg-white p-0.5 rounded shrink-0 flex items-center justify-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=${encodeURIComponent(item.barcode)}" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-white truncate">${item.name}</h4>
                            <p class="text-[11px] text-emerald-400 font-semibold">Rp ${formatRupiah(item.selling_price)}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button onclick="updateQty(${item.id}, -1)" class="w-6 h-6 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold flex items-center justify-center">-</button>
                        <span class="text-xs font-bold text-white w-5 text-center">${item.qty}</span>
                        <button onclick="updateQty(${item.id}, 1)" class="w-6 h-6 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold flex items-center justify-center">+</button>
                    </div>

                    <div class="text-right min-w-[70px]">
                        <span class="text-xs font-extrabold text-white">Rp ${formatRupiah(item.selling_price * item.qty)}</span>
                    </div>
                </div>
            `).join('');
        }

        document.getElementById('cartCount').textContent = `${cart.reduce((sum, i) => sum + i.qty, 0)} item`;
        renderCartSummary();
    }

    function renderCartSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const finalTotal = Math.max(0, subtotal - discount);

        document.getElementById('finalTotalDisplay').textContent = `Rp ${formatRupiah(finalTotal)}`;
        calculateChange();
        updateMobileFloatingBar();
    }

    function setPaymentMethod(method) {
        currentPaymentMethod = method;
        document.querySelectorAll('.pay-method-btn').forEach(btn => {
            btn.className = "pay-method-btn p-2 rounded-xl text-xs font-bold bg-slate-950 text-slate-400 border border-slate-800 hover:text-white flex flex-col items-center gap-1 transition-all";
        });

        const activeBtn = document.getElementById(`pay-${method}`);
        if (activeBtn) {
            activeBtn.className = "pay-method-btn p-2 rounded-xl text-xs font-bold bg-brand-600 text-white border border-brand-500 shadow-glow flex flex-col items-center gap-1 transition-all";
        }

        const quickRow = document.getElementById('quickCashRow');
        const payInput = document.getElementById('payAmountInput');

        if (method === 'qris' || method === 'edc') {
            const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
            const discount = parseFloat(document.getElementById('discountInput').value) || 0;
            const finalTotal = Math.max(0, subtotal - discount);
            payInput.value = finalTotal;
            quickRow.classList.add('hidden');
        } else {
            quickRow.classList.remove('hidden');
        }
        calculateChange();
    }

    function setQuickCash(amount) {
        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const finalTotal = Math.max(0, subtotal - discount);

        if (amount === 'exact') {
            document.getElementById('payAmountInput').value = finalTotal;
        } else {
            document.getElementById('payAmountInput').value = amount;
        }
        calculateChange();
    }

    function calculateChange() {
        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const finalTotal = Math.max(0, subtotal - discount);
        const payAmount = parseFloat(document.getElementById('payAmountInput').value) || 0;

        const change = payAmount - finalTotal;
        const changeEl = document.getElementById('changeDisplay');

        if (change >= 0) {
            changeEl.textContent = `Rp ${formatRupiah(change)}`;
            changeEl.className = "font-extrabold text-cyan-400";
        } else {
            changeEl.textContent = `-Rp ${formatRupiah(Math.abs(change))}`;
            changeEl.className = "font-extrabold text-rose-400";
        }
    }

    function setCategory(catId) {
        currentCategory = catId;
        document.querySelectorAll('.cat-pill').forEach(btn => {
            btn.className = "cat-pill px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all";
        });
        const activeBtn = document.getElementById(`cat-${catId}`);
        if (activeBtn) {
            activeBtn.className = "cat-pill px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all";
        }
        filterProducts();
    }

    function clearSearchInput() {
        const input = document.getElementById('searchInput');
        if (input) {
            input.value = '';
            filterProducts();
            input.focus();
        }
    }

    function filterProducts() {
        const input = document.getElementById('searchInput');
        const query = input.value.toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) {
            if (query.length > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;

        cards.forEach(card => {
            if (card.dataset.outOfStock === "true") {
                card.classList.add('hidden');
                return;
            }
            const name = card.dataset.name;
            const barcode = card.dataset.barcode;
            const cat = card.dataset.cat;

            const matchQuery = name.includes(query) || barcode.includes(query);
            const matchCat = (currentCategory === 'all' || cat == currentCategory);

            if (matchQuery && matchCat) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        const emptyState = document.getElementById('noProductsSearchResult');
        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }
    }

    function processCheckout() {
        if (cart.length === 0) {
            alert('Keranjang belanja masih kosong!');
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const finalTotal = Math.max(0, subtotal - discount);
        const payAmount = parseFloat(document.getElementById('payAmountInput').value) || 0;

        if (payAmount < finalTotal) {
            alert('Uang pembayaran kurang dari total tagihan!');
            return;
        }

        const btn = document.getElementById('checkoutBtn');
        btn.disabled = true;
        btn.innerHTML = `<span>Memproses...</span>`;

        fetch("{{ route('pos.checkout') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                items: cart.map(i => ({ id: i.id, qty: i.qty })),
                customer_name: document.getElementById('customerNameInput').value || 'Pelanggan Umum',
                discount_amount: discount,
                pay_amount: payAmount,
                payment_method: currentPaymentMethod
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-lg">check_circle</span><span>Bayar & Cetak Struk</span>`;

            if (data.success) {
                showReceiptModal(data.transaction);
                clearCart();
                updateUIStock(data.transaction.details);
                document.getElementById('payAmountInput').value = '';
                document.getElementById('discountInput').value = '0';
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses checkout.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-lg">check_circle</span><span>Bayar & Cetak Struk</span>`;
            alert('Gagal menghubungi server.');
        });
    }

    function updateUIStock(details) {
        if (!details || !Array.isArray(details)) return;
        details.forEach(detail => {
            const prod = productsData.find(p => p.id === detail.product_id);
            if (prod) {
                prod.stock = Math.max(0, prod.stock - detail.quantity);
                const stockBadge = document.getElementById(`stock-badge-${prod.id}`);
                const productCard = document.getElementById(`product-card-${prod.id}`);
                if (stockBadge) {
                    stockBadge.textContent = `Stok: ${prod.stock}`;
                    if (prod.stock <= 5) {
                        stockBadge.className = "text-[10px] font-bold text-rose-400";
                    } else {
                        stockBadge.className = "text-[10px] font-bold text-emerald-400";
                    }
                }
                if (prod.stock === 0 && productCard) {
                    productCard.dataset.outOfStock = "true";
                    productCard.classList.add('hidden');
                }
            }
        });
    }

    function showReceiptModal(trx) {
        document.getElementById('rcptInvoice').textContent = `No: ${trx.invoice_number}`;
        document.getElementById('rcptDate').textContent = new Date(trx.created_at).toLocaleDateString('id-ID');
        document.getElementById('rcptCashier').textContent = trx.cashier_name || 'Kasir';
        document.getElementById('rcptCustomer').textContent = `Pelanggan: ${trx.customer_name}`;

        const itemsEl = document.getElementById('rcptItems');
        itemsEl.innerHTML = trx.details.map(d => `
            <div>
                <div class="font-bold">${d.product_name}</div>
                <div class="flex justify-between text-slate-600">
                    <span>${d.quantity} x Rp ${formatRupiah(d.selling_price)}</span>
                    <span class="font-semibold text-slate-900">Rp ${formatRupiah(d.subtotal)}</span>
                </div>
            </div>
        `).join('');

        const subtotal = trx.details.reduce((s, d) => s + parseFloat(d.subtotal), 0);
        document.getElementById('rcptSubtotal').textContent = `Rp ${formatRupiah(subtotal)}`;
        document.getElementById('rcptDiscount').textContent = `Rp ${formatRupiah(trx.discount_amount)}`;
        document.getElementById('rcptTotal').textContent = `Rp ${formatRupiah(trx.total_amount)}`;
        document.getElementById('rcptMethod').textContent = trx.payment_method.toUpperCase();
        document.getElementById('rcptPay').textContent = `Rp ${formatRupiah(trx.pay_amount)}`;
        document.getElementById('rcptChange').textContent = `Rp ${formatRupiah(trx.change_amount)}`;
        document.getElementById('rcptQrImg').src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(trx.invoice_number)}`;

        document.getElementById('receiptModal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').classList.add('hidden');
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }
</script>
@endsection
