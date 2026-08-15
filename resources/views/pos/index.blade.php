@extends('layouts.app')

@section('content')
<!-- HTML5 QR/Barcode Scanner Script with Fallback -->
<script src="https://unpkg.com/html5-qrcode" crossorigin="anonymous"></script>
<script>
    if (typeof Html5Qrcode === 'undefined') {
        document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"><\/script>');
    }
</script>

<style>
@media print {
    body * { visibility: hidden !important; }
    #receiptModal, #receiptModal * { visibility: visible !important; }
    #receiptModal {
        position: fixed !important; left: 0 !important; top: 0 !important;
        width: 100vw !important; height: 100vh !important; background: white !important;
        padding: 0 !important; margin: 0 !important; display: flex !important;
        align-items: flex-start !important; justify-content: center !important;
    }
    #receiptModal > div {
        max-width: 100% !important; box-shadow: none !important; border: none !important;
        padding: 10px !important; margin: 0 !important; color: black !important;
    }
    #receiptModal button { display: none !important; }
}
</style>

<div class="min-h-[calc(100vh-5rem)] bg-[#0b0f19] p-3 sm:p-6 max-w-[1800px] mx-auto relative space-y-5">
    
    <!-- Floating Toast Notification for Instant Scan -->
    <div id="scanToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 transition-all duration-300 opacity-0 pointer-events-none transform -translate-y-4">
        <div class="px-6 py-3.5 rounded-2xl bg-emerald-500 text-slate-950 font-black text-sm sm:text-base shadow-2xl border-2 border-emerald-300 flex items-center gap-3">
            <span class="material-symbols-outlined text-2xl animate-bounce">check_circle</span>
            <span id="scanToastMsg">Item berhasil di-scan!</span>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN POS WORKSPACE (SCALED UP & TOUCH-FRIENDLY) -->
    <div class="flex flex-col lg:flex-row gap-6 items-start">
        
        <!-- LEFT COLUMN: Product Catalog Grid & Camera Viewfinder (60% Width) -->
        <div class="flex-1 w-full space-y-4 min-w-0">
            
            <!-- TOP BAR: Live Search + Scan Multiplier + Camera Controls -->
            <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-4 shadow-xl">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                    
                    <!-- Search Bar Input with Live Dropdown -->
                    <div class="relative flex-1">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-brand-400 text-xl pointer-events-none">search</span>
                            <input type="text" id="searchInput" oninput="handleSearchInputLive()" onkeydown="handleSearchInputKeydown(event)" placeholder="Ketik nama produk / scan barcode disini [/]..." class="w-full bg-slate-950 text-sm sm:text-base text-white font-bold rounded-2xl pl-12 pr-10 py-3.5 border-2 border-brand-500/60 focus:border-brand-400 focus:ring-2 focus:ring-brand-500/30 transition-all shadow-inner">
                            <button type="button" id="clearSearchBtn" onclick="clearSearchInput()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white" title="Hapus pencarian">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        <!-- LIVE SEARCH RESULTS DROPDOWN (Pencarian langsung keluar) -->
                        <div id="liveSearchResults" class="hidden absolute top-full left-0 right-0 mt-2 bg-slate-900/95 backdrop-blur-xl border-2 border-brand-500/50 rounded-2xl shadow-2xl z-50 max-h-80 overflow-y-auto divide-y divide-slate-800/80">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Preset Qty Multiplier & Action Buttons -->
                    <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center bg-slate-950 border border-slate-800 rounded-2xl p-1 text-xs font-bold gap-1" title="Preset Qty Scan Otomatis">
                            <span class="text-xs text-slate-400 pl-2 hidden xl:inline font-bold">Qty Scan:</span>
                            <button type="button" onclick="setScanMultiplier(1)" id="mult-1" class="scan-mult-btn px-3 py-1.5 rounded-xl bg-brand-600 text-white font-extrabold text-xs transition-all">1x</button>
                            <button type="button" onclick="setScanMultiplier(2)" id="mult-2" class="scan-mult-btn px-3 py-1.5 rounded-xl text-slate-400 hover:text-white font-extrabold text-xs transition-all">2x</button>
                            <button type="button" onclick="setScanMultiplier(5)" id="mult-5" class="scan-mult-btn px-3 py-1.5 rounded-xl text-slate-400 hover:text-white font-extrabold text-xs transition-all">5x</button>
                            <button type="button" onclick="setScanMultiplier(10)" id="mult-10" class="scan-mult-btn px-3 py-1.5 rounded-xl text-slate-400 hover:text-white font-extrabold text-xs transition-all">10x</button>
                        </div>

                        <!-- Camera Toggle Button -->
                        <button type="button" onclick="toggleAlwaysOnCamera()" id="cameraToggleBtn" class="px-4 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-cyan-300 border border-cyan-500/40 font-bold text-xs sm:text-sm flex items-center gap-1.5 transition-all shrink-0">
                            <span class="material-symbols-outlined text-lg">videocam</span>
                            <span id="cameraToggleText" class="hidden sm:inline">Kamera Scan</span>
                        </button>

                        <!-- Quick Add Product Button -->
                        <button type="button" onclick="openQuickAddModal('')" class="px-4 py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-1.5 transition-all shrink-0 shadow-glow">
                            <span class="material-symbols-outlined text-lg">add_box</span>
                            <span class="hidden sm:inline">+ Produk</span>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Embedded Camera Viewfinder -->
                <div id="alwaysOnCameraWidget" class="hidden glass-card rounded-2xl p-4 border border-cyan-500/30 bg-slate-900/90 transition-all flex flex-col md:flex-row items-center gap-4">
                    <div class="w-full md:w-64 h-36 relative overflow-hidden rounded-xl border-2 border-cyan-500/50 bg-black shadow-inner shrink-0">
                        <div id="always-on-qr-reader" class="w-full h-full object-cover"></div>
                        <div class="absolute top-2 left-2 px-2.5 py-0.5 rounded bg-black/70 text-emerald-400 text-xs font-bold flex items-center gap-1.5 pointer-events-none">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            LIVE SCANNER
                        </div>
                    </div>
                    <div class="flex-1 space-y-2 text-xs sm:text-sm w-full">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-white flex items-center gap-1.5 text-sm">
                                <span class="material-symbols-outlined text-cyan-400 text-base">qr_code_scanner</span>
                                Pemindai Kamera Otomatis
                            </h3>
                            <span id="scannerStatusBadge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                Aktif Me-Scan
                            </span>
                        </div>
                        <p class="text-slate-400 text-xs">Dekatkan barcode produk ke kotak pemindai kamera untuk otomatis masuk ke keranjang.</p>
                        <div id="lastScannedItemAlert" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-slate-500 text-base">history</span>
                            <span class="truncate">Item terakhir: <strong id="lastScannedText" class="text-cyan-300">Belum ada barang</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Category Pills Bar & Keyboard Shortcuts Legend Strip -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pt-2 border-t border-slate-800/80">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar w-full sm:w-auto">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1">Kategori:</span>
                        <button onclick="setCategory('all')" id="cat-all" class="cat-pill px-4 py-2 rounded-2xl text-xs sm:text-sm font-extrabold whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all">
                            Semua Kategori
                        </button>
                        @php
                            $lowCount = $products->filter(fn($p) => $p->stock <= 5)->count();
                        @endphp
                        @if($lowCount > 0)
                            <button onclick="setCategory('lowstock')" id="cat-lowstock" class="cat-pill px-4 py-2 rounded-2xl text-xs sm:text-sm font-extrabold whitespace-nowrap bg-rose-950/90 text-rose-300 border border-rose-500/50 hover:bg-rose-900 shadow-md transition-all">
                                ⚠️ Stok Menipis ({{ $lowCount }})
                            </button>
                        @endif
                        @foreach($categories as $cat)
                            <button onclick="setCategory({{ $cat->id }})" id="cat-{{ $cat->id }}" class="cat-pill px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Keyboard Shortcut Quick Legend Bar -->
                    <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-[11px] font-bold text-slate-300 shrink-0">
                        <span class="material-symbols-outlined text-brand-400 text-sm">keyboard</span>
                        <span>Shortcut:</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-900 text-brand-300 font-mono border border-slate-800">[/] Cari</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-900 text-cyan-300 font-mono border border-slate-800">[F4] Uang Diterima</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-900 text-emerald-400 font-mono border border-slate-800">[F12] Bayar</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-900 text-rose-300 font-mono border border-slate-800">[F9] Reset</span>
                    </div>
                </div>
            </div>

            <!-- PRODUCT CARDS CATALOG GRID (DIPERBESAR & SANGAT JELAS) -->
            <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 gap-4 max-h-[calc(100vh-17rem)] overflow-y-auto pr-1">
                @foreach($products as $prod)
                    <div id="product-card-{{ $prod->id }}" onclick="addToCartById({{ $prod->id }})" class="product-card glass-card rounded-3xl p-4 space-y-3 cursor-pointer hover:border-brand-500/60 hover:bg-slate-800/90 transition-all flex flex-col justify-between group active:scale-95 select-none {{ $prod->stock <= 0 ? 'border-rose-500/30 bg-rose-950/10' : '' }}" data-name="{{ strtolower($prod->name) }}" data-barcode="{{ strtolower($prod->barcode ?? '') }}" data-cat="{{ $prod->category_id }}">
                        
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-1">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 truncate max-w-[110px]">
                                    {{ $prod->category->name ?? 'Produk' }}
                                </span>
                                <span class="text-xs font-black shrink-0 {{ $prod->stock <= 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40 px-2 py-0.5 rounded-lg animate-pulse' : ($prod->stock <= 5 ? 'text-amber-400' : 'text-emerald-400') }}">
                                    {{ $prod->stock <= 0 ? '⚠️ STOK HABIS' : 'Stok: ' . $prod->stock }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white p-1 rounded-xl shrink-0 flex items-center justify-center border border-slate-700 shadow-md">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($prod->barcode ?? 'NOBARCODE') }}" alt="QR" class="w-full h-full object-contain">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm sm:text-base font-black text-white group-hover:text-brand-300 transition-colors line-clamp-2 leading-snug">
                                        {{ $prod->name }}
                                    </h3>
                                    <p class="text-xs text-slate-400 font-mono truncate">Code: {{ $prod->barcode }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2.5 border-t border-slate-800/80 flex items-center justify-between">
                            <span class="text-sm sm:text-lg font-black text-emerald-400">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                            <div class="w-8 h-8 rounded-xl {{ $prod->stock <= 0 ? 'bg-rose-500/20 text-rose-400' : 'bg-brand-600/20 text-brand-400 group-hover:bg-brand-600 group-hover:text-white' }} flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-lg">{{ $prod->stock <= 0 ? 'block' : 'add' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT COLUMN: DEDICATED SPACIOUS CART & GRAND TOTAL PANEL (40% Width - DIPERBESAR) -->
        <div id="posCartSection" class="w-full lg:w-[540px] shrink-0 glass-card rounded-3xl border-2 border-slate-800 overflow-hidden shadow-2xl flex flex-col justify-between space-y-0">
            
            <!-- 1. HERO DUAL DISPLAY BANNER (GRAND TOTAL & UANG KEMBALIAN RAKSASA DI ATAS KERANJANG) -->
            <div class="p-5 sm:p-6 bg-gradient-to-r from-[#064e3b] via-[#0f172a] to-[#082f49] border-b-2 border-emerald-500/60 shadow-xl relative overflow-hidden space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-400 text-xl">shopping_bag</span>
                        <h2 class="text-xs sm:text-sm font-black uppercase tracking-wider text-slate-200">Keranjang Kasir</h2>
                        <span id="cartCountBadge" class="px-3 py-0.5 rounded-full text-xs font-black bg-brand-500/20 text-brand-300 border border-brand-500/30">0 Item</span>
                    </div>
                    <button onclick="clearCart()" class="text-xs font-bold text-rose-400 hover:text-rose-300 transition-colors flex items-center gap-1 hover:bg-rose-500/10 px-2.5 py-1 rounded-lg">
                        <span class="material-symbols-outlined text-base">delete_sweep</span>
                        <span>Reset</span>
                    </button>
                </div>

                <!-- GRAND TOTAL RAKSASA -->
                <div class="space-y-0.5">
                    <span class="text-xs font-bold text-emerald-300/80 uppercase tracking-wider block">GRAND TOTAL TAGIHAN</span>
                    <div id="finalTotalDisplay" class="text-4xl sm:text-5xl lg:text-6xl font-black font-mono text-emerald-400 tracking-tight truncate drop-shadow-md">
                        Rp 0
                    </div>
                </div>

                <!-- UANG KEMBALIAN RAKSASA & METODE PEMBAYARAN -->
                <div class="flex items-center justify-between pt-2.5 border-t border-emerald-500/30 text-xs sm:text-sm">
                    <span class="text-slate-300 font-bold">
                        Metode: <strong id="topPayMethodDisplay" class="text-white uppercase font-black bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">TUNAI</strong>
                    </span>
                    <span class="flex items-center gap-1 text-slate-300 font-bold">
                        Kembalian: <strong id="topChangeDisplay" class="text-cyan-300 font-black font-mono text-lg sm:text-2xl">Rp 0</strong>
                    </span>
                </div>
            </div>

            <!-- 2. SPACIOUS CART ITEMS TABLE (TABEL KERANJANG KASIR DIPERBESAR) -->
            <div class="p-4 sm:p-5 overflow-x-auto min-h-[240px] max-h-[380px] overflow-y-auto flex-1">
                <table class="w-full text-left text-xs sm:text-sm text-slate-200">
                    <thead class="text-xs font-extrabold text-slate-400 uppercase bg-slate-900/90 border-b border-slate-800">
                        <tr>
                            <th class="p-3 rounded-l-xl">Produk</th>
                            <th class="p-3 text-right">Harga</th>
                            <th class="p-3 text-center">Qty</th>
                            <th class="p-3 text-right">Subtotal</th>
                            <th class="p-3 text-center rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody" class="divide-y divide-slate-800/80 font-medium">
                        <!-- Dynamic Cart Rows via JS -->
                    </tbody>
                </table>

                <!-- Empty Cart Placeholder -->
                <div id="emptyCartState" class="py-14 text-center text-slate-500 space-y-2">
                    <span class="material-symbols-outlined text-6xl text-slate-700">remove_shopping_cart</span>
                    <p class="text-sm font-bold text-slate-400">Keranjang masih kosong.</p>
                    <p class="text-xs text-slate-500">Klik produk dari katalog atau scan barcode untuk menambah barang.</p>
                </div>
            </div>

            <!-- 3. BOTTOM CHECKOUT & PAYMENT CONTROLS GRID -->
            <div class="p-5 bg-slate-900/95 border-t border-slate-800 space-y-4">
                
                <!-- Customer Name & Discount Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nama Pelanggan</label>
                        <input type="text" id="customerNameInput" placeholder="Pelanggan Umum" class="w-full bg-slate-950 text-xs sm:text-sm font-semibold text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Diskon (Rp)</label>
                        <input type="text" inputmode="numeric" id="discountInput" oninput="formatCurrencyInput(this); renderCartSummary()" placeholder="0" value="0" class="w-full bg-slate-950 text-xs sm:text-sm font-black text-amber-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <!-- Payment Method Selector Tabs -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Metode Pembayaran</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" onclick="setPaymentMethod('cash')" id="pay-cash" class="pay-method-btn p-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-brand-600 text-white border border-brand-500 shadow-glow flex flex-col items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-lg">payments</span>
                            <span>Tunai</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('qris')" id="pay-qris" class="pay-method-btn p-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-slate-950 text-slate-400 border border-slate-800 hover:text-white flex flex-col items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-lg">qr_code_scanner</span>
                            <span>QRIS</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('edc')" id="pay-edc" class="pay-method-btn p-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-slate-950 text-slate-400 border border-slate-800 hover:text-white flex flex-col items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-lg">credit_card</span>
                            <span>EDC</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('hutang')" id="pay-hutang" class="pay-method-btn p-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-slate-950 text-slate-400 border border-slate-800 hover:text-white flex flex-col items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                            <span>Hutang</span>
                        </button>
                    </div>

                    <!-- Hutang Options -->
                    <div id="hutangOptionsContainer" class="hidden p-3 rounded-xl bg-purple-950/40 border border-purple-500/30 space-y-2 text-xs mt-2">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">DP (Rp)</label>
                                <input type="text" inputmode="numeric" id="dpAmountInput" oninput="formatCurrencyInput(this); calculateChange()" placeholder="0" value="0" class="w-full bg-slate-950 text-xs text-white rounded-lg px-2.5 py-1.5 border border-purple-500/30">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Jatuh Tempo</label>
                                <input type="date" id="dueDateInput" class="w-full bg-slate-950 text-xs text-white rounded-lg px-2.5 py-1.5 border border-purple-500/30">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pay Amount Input, Quick Presets & BIG KEMBALIAN BOX -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-slate-300 font-extrabold flex items-center gap-1.5">
                            <span>Uang Diterima (Rp)</span>
                            <span class="px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-300 font-mono text-[10px] font-extrabold border border-brand-500/30">F4</span>
                        </span>
                        <input type="text" inputmode="numeric" id="payAmountInput" oninput="formatCurrencyInput(this); calculateChange()" onkeydown="handlePayAmountKeydown(event)" placeholder="0" class="w-48 bg-slate-950 text-right text-sm sm:text-base font-black text-white rounded-xl px-3.5 py-2 border-2 border-brand-500/60 focus:border-brand-400 focus:ring-2 focus:ring-brand-400/50">
                    </div>

                    <!-- Quick Cash Presets -->
                    <div id="quickCashRow" class="flex items-center gap-2 overflow-x-auto pb-0.5">
                        <button type="button" onclick="setQuickCash('exact')" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">Pas</button>
                        <button type="button" onclick="setQuickCash(50000)" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">50rb</button>
                        <button type="button" onclick="setQuickCash(100000)" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">100rb</button>
                        <button type="button" onclick="setQuickCash(200000)" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">200rb</button>
                    </div>

                    <!-- HIGHLIGHTED KEMBALIAN BOX -->
                    <div class="p-3 rounded-2xl bg-[#032838] border-2 border-cyan-500/50 flex items-center justify-between">
                        <span class="text-xs sm:text-sm font-bold text-cyan-300 uppercase tracking-wider">Kembalian:</span>
                        <span id="changeDisplay" class="font-black text-cyan-300 font-mono text-lg sm:text-xl">Rp 0</span>
                    </div>
                </div>

                <!-- Big Checkout Action Button (With F12 / Ctrl+Enter Shortcut Badges) -->
                <button type="button" onclick="processCheckout()" id="checkoutBtn" class="w-full py-4 sm:py-5 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:from-emerald-500 hover:to-teal-400 text-white font-black text-base sm:text-lg shadow-[0_0_25px_rgba(16,185,129,0.4)] transition-all transform active:scale-98 disabled:opacity-50 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                    <span>BAYAR & CETAK STRUK</span>
                    <span class="ml-1 px-2 py-0.5 rounded-lg bg-black/40 text-emerald-200 font-mono text-xs font-bold border border-emerald-400/40">F12 / Ctrl+Enter</span>
                </button>
            </div>
        </div>
    </div>
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
            <button type="button" onclick="window.print()" id="printReceiptBtn" class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Nota</span>
                <span class="ml-1 px-2 py-0.5 rounded-lg bg-black/30 text-white font-mono text-xs font-bold border border-white/30">Enter / P</span>
            </button>
            <button type="button" onclick="closeReceiptModal()" class="px-4 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs">
                Tutup [Esc]
            </button>
        </div>
    </div>
</div>

<!-- Quick Add Product Modal for Cashier -->
<div id="quickAddProductModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400">add_shopping_cart</span>
                Tambah Produk Cepat (POS)
            </h3>
            <button type="button" onclick="closeQuickAddModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="quickAddProductForm" onsubmit="submitQuickProduct(event)" class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Produk <span class="text-rose-400">*</span></label>
                <input type="text" id="quick_name" required placeholder="Contoh: Aqua 600ml" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual (Rp) <span class="text-rose-400">*</span></label>
                    <input type="number" id="quick_selling_price" required min="0" placeholder="3000" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Stok Awal <span class="text-rose-400">*</span></label>
                    <input type="number" id="quick_stock" required min="1" value="10" placeholder="10" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori <span class="text-rose-400">*</span></label>
                    <select id="quick_category_id" required class="w-full bg-slate-900 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kode QR / Barcode</label>
                    <input type="text" id="quick_barcode" placeholder="Otomatis jika kosong" class="w-full bg-slate-900 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeQuickAddModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" id="quickSubmitBtn" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-glow">Simpan & Tambah ke Keranjang</button>
            </div>
        </form>
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
    let lastScannedCode = '';
    let barcodeBuffer = '';
    let barcodeTimeout = null;

    document.addEventListener('DOMContentLoaded', function() {
        startAlwaysOnCamera();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.focus();
        renderCart();

        // GLOBAL KEYDOWN LISTENER UNTUK HARDWARE USB BARCODE SCANNER & SHORTCUTS KASIR
        document.addEventListener('keydown', function(e) {
            const activeEl = document.activeElement;
            const receiptModal = document.getElementById('receiptModal');
            const isReceiptOpen = receiptModal && !receiptModal.classList.contains('hidden');

            // 0. Shortcut saat Modal Nota Struk terbuka: Enter / P -> Cetak Nota, Esc -> Tutup
            if (isReceiptOpen) {
                if (e.key === 'Enter' || e.key.toLowerCase() === 'p') {
                    e.preventDefault();
                    window.print();
                    return;
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeReceiptModal();
                    return;
                }
            }

            // 1. Shortcut F12 atau Ctrl+Enter -> BAYAR & CETAK STRUK
            if (e.key === 'F12' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                processCheckout();
                return;
            }

            // 2. Shortcut F4 atau Alt+P -> Fokus ke input Uang Diterima (Rp)
            if (e.key === 'F4' || (e.altKey && e.key.toLowerCase() === 'p')) {
                e.preventDefault();
                const payInput = document.getElementById('payAmountInput');
                if (payInput) {
                    payInput.focus();
                    payInput.select();
                }
                return;
            }

            // 3. Shortcut F9 -> Kosongkan / Reset Keranjang
            if (e.key === 'F9') {
                e.preventDefault();
                if (cart.length > 0 && confirm('Kosongkan keranjang belanja?')) {
                    clearCart();
                }
                return;
            }

            // 4. Shortcut F2 -> Toggle Kamera Scan
            if (e.key === 'F2') {
                e.preventDefault();
                toggleAlwaysOnCamera();
                return;
            }

            // 5. Shortcut Escape -> Tutup Modal
            if (e.key === 'Escape') {
                closeReceiptModal();
                closeQuickAddModal();
                return;
            }

            const isOtherInputFocused = activeEl && (
                activeEl.id === 'customerNameInput' ||
                activeEl.id === 'discountInput' ||
                activeEl.id === 'payAmountInput' ||
                activeEl.id.startsWith('quick_')
            );

            if (isOtherInputFocused) return;

            // Shortcut '/' untuk fokus pencarian
            if (e.key === '/' && activeEl !== searchInput) {
                e.preventDefault();
                if (searchInput) searchInput.focus();
                return;
            }

            // Hardware USB Barcode Scanner Buffer saat searchInput tidak fokus
            if (activeEl !== searchInput) {
                if (e.key === 'Enter') {
                    if (barcodeBuffer.trim().length > 0) {
                        e.preventDefault();
                        const scannedCode = barcodeBuffer.trim();
                        barcodeBuffer = '';
                        scanBarcode(scannedCode);
                    }
                } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                    barcodeBuffer += e.key;
                    clearTimeout(barcodeTimeout);
                    barcodeTimeout = setTimeout(() => {
                        barcodeBuffer = '';
                    }, 250);
                }
            }
        });
    });

    function handlePayAmountKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processCheckout();
        }
    }

    // INSTANT LIVE SEARCH DROPDOWN (Pencarian langsung keluar saat diketik)
    function handleSearchInputLive() {
        const query = document.getElementById('searchInput').value.trim().toLowerCase();
        const dropdown = document.getElementById('liveSearchResults');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (clearBtn) {
            if (query.length > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        if (query.length === 0) {
            dropdown.classList.add('hidden');
            filterGridProducts();
            return;
        }

        filterGridProducts();

        const matches = productsData.filter(p => {
            const nameMatch = p.name && p.name.toLowerCase().includes(query);
            const barcodeMatch = p.barcode && p.barcode.toString().toLowerCase().includes(query);
            const catMatch = (currentCategory === 'all' || p.category_id == currentCategory);
            return (nameMatch || barcodeMatch) && catMatch;
        }).slice(0, 8); // Top 8 matches

        if (matches.length === 0) {
            dropdown.innerHTML = `
                <div class="p-4 text-center text-xs text-slate-400 space-y-1">
                    <p class="font-bold text-slate-300">Tidak ada produk '${query}'</p>
                    <button type="button" onclick="openQuickAddModal('${query}')" class="text-brand-400 hover:underline font-bold text-xs">+ Tambah Produk Baru Ini</button>
                </div>
            `;
            dropdown.classList.remove('hidden');
            return;
        }

        dropdown.innerHTML = matches.map(p => `
            <div onclick="selectSearchResult(${p.id})" class="p-3.5 hover:bg-brand-600/30 transition-colors cursor-pointer flex items-center justify-between gap-3 group ${p.stock <= 0 ? 'bg-rose-950/20' : ''}">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 bg-white p-0.5 rounded-lg shrink-0 flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=${encodeURIComponent(p.barcode || 'NOBARCODE')}" class="w-full h-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs sm:text-sm font-bold text-white group-hover:text-brand-300 truncate">${p.name}</h4>
                        <span class="text-xs font-mono text-slate-400">Barcode: ${p.barcode} • <span class="${p.stock <= 0 ? 'text-rose-400 font-bold' : ''}">${p.stock <= 0 ? 'STOK HABIS' : 'Stok: ' + p.stock}</span></span>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs sm:text-sm font-black text-emerald-400">Rp ${formatRupiah(p.selling_price)}</span>
                    <span class="block text-xs font-bold ${p.stock <= 0 ? 'text-rose-400' : 'text-brand-400 group-hover:underline'}">${p.stock <= 0 ? '⚠️ STOK HABIS' : '+ Tambah'}</span>
                </div>
            </div>
        `).join('');

        dropdown.classList.remove('hidden');
    }

    function selectSearchResult(productId) {
        addToCartById(productId);
        clearSearchInput();
    }

    function clearSearchInput() {
        const input = document.getElementById('searchInput');
        if (input) input.value = '';
        const dropdown = document.getElementById('liveSearchResults');
        if (dropdown) dropdown.classList.add('hidden');
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) clearBtn.classList.add('hidden');
        filterGridProducts();
        input.focus();
    }

    function handleSearchInputKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = document.getElementById('searchInput').value.trim();
            if (!query) return;

            const found = scanBarcode(query);
            if (found) {
                clearSearchInput();
            } else {
                const matches = productsData.filter(p => p.name.toLowerCase().includes(query.toLowerCase()));
                if (matches.length > 0) {
                    addToCart(matches[0]);
                    clearSearchInput();
                } else {
                    playBeep('error');
                    showToast(`❌ Produk '${query}' tidak ditemukan`);
                }
            }
        }
    }

    let activeScanMultiplier = 1;

    function setScanMultiplier(mult) {
        activeScanMultiplier = parseInt(mult) || 1;
        document.querySelectorAll('.scan-mult-btn').forEach(btn => {
            btn.className = "scan-mult-btn px-3 py-1.5 rounded-xl text-slate-400 hover:text-white font-extrabold text-xs transition-all";
        });
        const activeBtn = document.getElementById(`mult-${activeScanMultiplier}`);
        if (activeBtn) {
            activeBtn.className = "scan-mult-btn px-3 py-1.5 rounded-xl bg-brand-600 text-white font-extrabold text-xs transition-all";
        }
        showToast(`⚡ Preset Qty Scan: ${activeScanMultiplier}x`);
    }

    function addToCartById(prodId) {
        const prod = productsData.find(p => p.id === prodId);
        if (prod) addToCart(prod);
    }

    function addToCart(product) {
        if (!product) return;

        // VALIDASI STOK HABIS (STOK <= 0)
        if (product.stock <= 0) {
            playBeep('error');
            showToast(`⚠️ STOK HABIS! '${product.name}' (Stok: 0)`);
            
            const alertText = document.getElementById('lastScannedText');
            if (alertText) {
                alertText.innerHTML = `<span class="text-rose-400 font-extrabold">⚠️ STOK HABIS!</span> ${product.name} (Stok: 0)`;
            }

            alert(`⚠️ STOK HABIS!\n\nProduk '${product.name}' (Barcode: ${product.barcode || '-'}) tidak dapat ditambahkan karena STOK SUDAH HABIS (0).\n\nSilakan lakukan pengisian stok di menu Master Produk.`);
            return;
        }

        const addAmount = activeScanMultiplier;
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            if (existing.qty + addAmount <= product.stock) {
                existing.qty += addAmount;
            } else {
                existing.qty = product.stock;
                playBeep('error');
                showToast(`⚠️ Stok maksimal '${product.name}' adalah ${product.stock}!`);
                alert(`⚠️ BATAS STOK TERCAPAI!\n\nJumlah '${product.name}' di keranjang sudah mencapai batas stok maksimal (${product.stock}).`);
                renderCart();
                return;
            }
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                barcode: product.barcode || 'NOBARCODE',
                selling_price: product.selling_price,
                stock: product.stock,
                qty: Math.min(addAmount, product.stock)
            });
        }
        playBeep('success');
        showToast(`✅ ${product.name} (+${addAmount})`);
        renderCart();

        // OTOMATIS LANGSUNG FOKUS KE INPUT JUMLAH (QTY) BARANG YANG BARU DI-SCAN
        setTimeout(() => {
            const qtyInput = document.getElementById(`qty-input-${product.id}`);
            if (qtyInput) {
                qtyInput.focus();
                qtyInput.select();
            }
        }, 60);
    }

    function setCustomQty(id, val) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        let num = parseInt(val) || 1;
        if (num <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (num > item.stock) {
            item.qty = item.stock;
            alert(`Stok maksimal adalah ${item.stock}!`);
        } else {
            item.qty = num;
        }
        renderCart();
    }

    function handleQtyKeydown(e, id) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) searchInput.focus();
        }
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (item.qty > item.stock) {
            item.qty = item.stock;
            alert(`Stok maksimal adalah ${item.stock}!`);
        }
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const tableBody = document.getElementById('cartTableBody');
        const emptyState = document.getElementById('emptyCartState');
        const totalItems = cart.reduce((sum, i) => sum + i.qty, 0);

        document.getElementById('cartCountBadge').textContent = `${totalItems} Item`;

        if (cart.length === 0) {
            tableBody.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            tableBody.innerHTML = cart.map(item => `
                <tr class="hover:bg-slate-800/40 transition-colors">
                    <td class="p-3 font-bold text-white flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-white p-0.5 rounded-lg shrink-0 flex items-center justify-center border border-slate-700">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=${encodeURIComponent(item.barcode || 'NOBARCODE')}" class="w-full h-full object-contain">
                        </div>
                        <span class="truncate max-w-[140px] sm:max-w-[180px] text-xs sm:text-sm font-black text-white" title="${item.name}">${item.name}</span>
                    </td>
                    <td class="p-3 text-right font-bold text-slate-300 text-xs sm:text-sm">Rp ${formatRupiah(item.selling_price)}</td>
                    <td class="p-3 text-center">
                        <div class="inline-flex items-center gap-1.5 bg-slate-950 p-1 rounded-xl border border-slate-800">
                            <button onclick="updateQty(${item.id}, -1)" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-extrabold flex items-center justify-center shadow text-xs sm:text-sm">-</button>
                            <input type="number" id="qty-input-${item.id}" value="${item.qty}" min="1" max="${item.stock}" onfocus="this.select()" onchange="setCustomQty(${item.id}, this.value)" onkeydown="handleQtyKeydown(event, ${item.id})" class="w-14 sm:w-16 text-center bg-slate-900 border-2 border-brand-500/80 text-white font-black text-xs sm:text-sm rounded-lg py-1 px-1 focus:border-brand-400 focus:ring-2 focus:ring-brand-400">
                            <button onclick="updateQty(${item.id}, 1)" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-extrabold flex items-center justify-center shadow text-xs sm:text-sm">+</button>
                        </div>
                    </td>
                    <td class="p-3 text-right font-black text-emerald-400 text-xs sm:text-base">Rp ${formatRupiah(item.selling_price * item.qty)}</td>
                    <td class="p-3 text-center">
                        <button onclick="updateQty(${item.id}, -${item.qty})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" title="Hapus Barang">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        renderCartSummary();
    }

    function formatCurrencyInput(el) {
        if (!el) return;
        let rawValue = el.value.replace(/\D/g, '');
        if (!rawValue) {
            el.value = '';
            return;
        }
        const number = parseInt(rawValue, 10);
        el.value = new Intl.NumberFormat('id-ID').format(number);
    }

    function getRawNumber(val) {
        if (!val) return 0;
        const str = val.toString().replace(/\D/g, '');
        return parseInt(str, 10) || 0;
    }

    function renderCartSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = getRawNumber(document.getElementById('discountInput').value);
        const finalTotal = Math.max(0, subtotal - discount);

        const totalEl = document.getElementById('finalTotalDisplay');
        if (totalEl) totalEl.textContent = `Rp ${formatRupiah(finalTotal)}`;
        
        calculateChange();
    }

    function setPaymentMethod(method) {
        currentPaymentMethod = method;
        const topPayEl = document.getElementById('topPayMethodDisplay');
        if (topPayEl) topPayEl.textContent = method.toUpperCase();

        document.querySelectorAll('.pay-method-btn').forEach(btn => {
            btn.className = "pay-method-btn p-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-slate-950 text-slate-400 border border-slate-800 hover:text-white flex flex-col items-center gap-1 transition-all";
        });

        const activeBtn = document.getElementById(`pay-${method}`);
        if (activeBtn) {
            if (method === 'hutang') {
                activeBtn.className = "pay-method-btn p-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-purple-600 text-white border border-purple-500 shadow-lg flex flex-col items-center gap-1 transition-all";
            } else {
                activeBtn.className = "pay-method-btn p-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-brand-600 text-white border border-brand-500 shadow-glow flex flex-col items-center gap-1 transition-all";
            }
        }

        const quickRow = document.getElementById('quickCashRow');
        const payInput = document.getElementById('payAmountInput');
        const hutangContainer = document.getElementById('hutangOptionsContainer');

        if (method === 'qris' || method === 'edc') {
            const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
            const discount = getRawNumber(document.getElementById('discountInput').value);
            const finalTotal = Math.max(0, subtotal - discount);
            payInput.value = new Intl.NumberFormat('id-ID').format(finalTotal);
            if (quickRow) quickRow.classList.add('hidden');
            if (hutangContainer) hutangContainer.classList.add('hidden');
        } else if (method === 'hutang') {
            if (quickRow) quickRow.classList.add('hidden');
            if (hutangContainer) hutangContainer.classList.remove('hidden');
        } else {
            if (quickRow) quickRow.classList.remove('hidden');
            if (hutangContainer) hutangContainer.classList.add('hidden');
        }
        calculateChange();
    }

    function setQuickCash(amount) {
        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = getRawNumber(document.getElementById('discountInput').value);
        const finalTotal = Math.max(0, subtotal - discount);

        const payInput = document.getElementById('payAmountInput');
        if (amount === 'exact') {
            payInput.value = new Intl.NumberFormat('id-ID').format(finalTotal);
        } else {
            payInput.value = new Intl.NumberFormat('id-ID').format(amount);
        }
        calculateChange();
    }

    function calculateChange() {
        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = getRawNumber(document.getElementById('discountInput').value);
        const finalTotal = Math.max(0, subtotal - discount);
        
        let payAmount = getRawNumber(document.getElementById('payAmountInput').value);
        if (currentPaymentMethod === 'hutang') {
            payAmount = getRawNumber(document.getElementById('dpAmountInput')?.value);
        }

        const change = payAmount - finalTotal;
        const changeEl = document.getElementById('changeDisplay');
        const topChangeEl = document.getElementById('topChangeDisplay');

        if (currentPaymentMethod === 'hutang') {
            const sisaHutang = Math.max(0, finalTotal - payAmount);
            const str = `Sisa: Rp ${formatRupiah(sisaHutang)}`;
            if (changeEl) { changeEl.textContent = str; changeEl.className = "font-extrabold text-purple-400 text-base sm:text-lg"; }
            if (topChangeEl) { topChangeEl.textContent = `Rp ${formatRupiah(sisaHutang)}`; topChangeEl.className = "text-purple-400 font-black font-mono text-lg sm:text-2xl"; }
        } else if (change >= 0) {
            const str = `Rp ${formatRupiah(change)}`;
            if (changeEl) { changeEl.textContent = str; changeEl.className = "font-extrabold text-cyan-300 text-base sm:text-lg"; }
            if (topChangeEl) { topChangeEl.textContent = str; topChangeEl.className = "text-cyan-300 font-black font-mono text-lg sm:text-2xl"; }
        } else {
            const str = `-Rp ${formatRupiah(Math.abs(change))}`;
            if (changeEl) { changeEl.textContent = str; changeEl.className = "font-extrabold text-rose-400 text-base sm:text-lg"; }
            if (topChangeEl) { topChangeEl.textContent = str; topChangeEl.className = "text-rose-400 font-black font-mono text-lg sm:text-2xl"; }
        }
    }

    function scanBarcode(code) {
        if (!code) return null;
        const cleanCode = code.toString().replace(/[\r\n]/g, '').trim().toLowerCase();
        if (!cleanCode) return null;

        // 1. Match persis dengan barcode produk
        let product = productsData.find(p => p.barcode && p.barcode.toString().trim().toLowerCase() === cleanCode);

        // 2. Match tanpa angka 0 di depan
        if (!product) {
            const noZero = cleanCode.replace(/^0+/, '');
            product = productsData.find(p => p.barcode && p.barcode.toString().trim().toLowerCase().replace(/^0+/, '') === noZero);
        }

        // 3. Match ID produk jika numerik
        if (!product && !isNaN(cleanCode)) {
            product = productsData.find(p => p.id == cleanCode);
        }

        if (product) {
            addToCart(product);
            const alertText = document.getElementById('lastScannedText');
            if (alertText) {
                alertText.innerHTML = `<span class="text-emerald-400 font-extrabold">${product.name}</span> (Rp ${formatRupiah(product.selling_price)})`;
            }
            return product;
        } else {
            playBeep('error');
            showToast(`❌ Barcode '${cleanCode}' tidak terdaftar!`);
            const alertText = document.getElementById('lastScannedText');
            if (alertText) {
                alertText.innerHTML = `<span class="text-rose-400 font-extrabold">Barcode '${cleanCode}' tidak terdaftar</span> <button type="button" onclick="openQuickAddModal('${cleanCode}')" class="underline text-cyan-300 ml-1 font-bold">[ + Tambah ]</button>`;
            }
            return null;
        }
    }

    function playBeep(type = 'success') {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(type === 'success' ? 1050 : 350, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch (e) {}
    }

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

    function filterGridProducts() {
        const query = document.getElementById('searchInput').value.trim().toLowerCase();
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const barcode = card.dataset.barcode || '';
            const cat = card.dataset.cat || '';
            const prodId = card.id.replace('product-card-', '');
            const prod = productsData.find(p => p.id == prodId);

            const matchesQuery = !query || name.includes(query) || barcode.includes(query);
            
            let matchesCat = false;
            if (currentCategory === 'all') {
                matchesCat = true;
            } else if (currentCategory === 'lowstock') {
                matchesCat = (prod && prod.stock <= 5);
            } else {
                matchesCat = (cat == currentCategory);
            }

            if (matchesQuery && matchesCat) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function setCategory(catId) {
        currentCategory = catId;
        document.querySelectorAll('.cat-pill').forEach(btn => {
            if (btn.id === 'cat-lowstock') {
                btn.className = "cat-pill px-4 py-2 rounded-2xl text-xs sm:text-sm font-extrabold whitespace-nowrap bg-rose-950/90 text-rose-300 border border-rose-500/50 hover:bg-rose-900 shadow-md transition-all";
            } else {
                btn.className = "cat-pill px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 transition-all";
            }
        });
        const activeBtn = document.getElementById(`cat-${catId}`);
        if (activeBtn) {
            if (catId === 'lowstock') {
                activeBtn.className = "cat-pill px-4 py-2 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-rose-600 text-white border border-rose-400 shadow-glow transition-all";
            } else {
                activeBtn.className = "cat-pill px-4 py-2 rounded-2xl text-xs sm:text-sm font-extrabold whitespace-nowrap bg-brand-600 text-white shadow-glow transition-all";
            }
        }
        filterGridProducts();
    }

    function toggleAlwaysOnCamera() {
        const widget = document.getElementById('alwaysOnCameraWidget');
        const btnText = document.getElementById('cameraToggleText');

        if (widget.classList.contains('hidden')) {
            widget.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Sembunyikan Kamera';
            if (!isCameraRunning) startAlwaysOnCamera();
        } else {
            widget.classList.add('hidden');
            if (btnText) btnText.textContent = 'Kamera Scan';
        }
    }

    function startAlwaysOnCamera() {
        const container = document.getElementById('always-on-qr-reader');
        if (!container || typeof Html5Qrcode === 'undefined') return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("always-on-qr-reader");
        }

        const config = {
            fps: 30,
            qrbox: function(w, h) { return { width: Math.floor(w * 0.9), height: Math.floor(h * 0.9) }; },
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                const now = Date.now();
                if (decodedText === lastScannedCode && (now - lastScanTime) < 1500) {
                    return; // Hindari re-scan beruntun item yang sama dalam 1.5s
                }
                lastScanTime = now;
                lastScannedCode = decodedText;

                scanBarcode(decodedText);
            },
            () => {}
        ).then(() => { isCameraRunning = true; })
        .catch(() => { isCameraRunning = false; });
    }

    function openQuickAddModal(initialBarcode = '') {
        document.getElementById('quick_name').value = '';
        document.getElementById('quick_selling_price').value = '';
        document.getElementById('quick_stock').value = 10;
        document.getElementById('quick_barcode').value = initialBarcode;
        document.getElementById('quickAddProductModal').classList.remove('hidden');
    }

    function closeQuickAddModal() {
        document.getElementById('quickAddProductModal').classList.add('hidden');
    }

    function submitQuickProduct(e) {
        e.preventDefault();
        const btn = document.getElementById('quickSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        const payload = {
            name: document.getElementById('quick_name').value,
            selling_price: document.getElementById('quick_selling_price').value,
            purchase_price: Math.floor(document.getElementById('quick_selling_price').value * 0.7),
            stock: document.getElementById('quick_stock').value,
            category_id: document.getElementById('quick_category_id').value,
            barcode: document.getElementById('quick_barcode').value || null,
            unit: 'pcs'
        };

        fetch("{{ route('products.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Simpan & Tambah ke Keranjang';

            if (data.success || data.product) {
                const newProd = data.product || data;
                productsData.push(newProd);
                addToCart(newProd);
                closeQuickAddModal();
                showToast(`✅ ${newProd.name} ditambahkan!`);
            } else {
                location.reload();
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'Simpan & Tambah ke Keranjang';
            location.reload();
        });
    }

    function processCheckout() {
        if (cart.length === 0) {
            alert('Keranjang belanja masih kosong!');
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
        const discount = getRawNumber(document.getElementById('discountInput').value);
        const finalTotal = Math.max(0, subtotal - discount);
        const payAmount = getRawNumber(document.getElementById('payAmountInput').value);

        if (currentPaymentMethod !== 'hutang' && payAmount < finalTotal) {
            alert(`Uang pembayaran (Rp ${formatRupiah(payAmount)}) kurang dari total tagihan (Rp ${formatRupiah(finalTotal)})!`);
            return;
        }

        const btn = document.getElementById('checkoutBtn');
        btn.disabled = true;
        btn.innerHTML = `<span>Memproses...</span>`;

        fetch("{{ route('pos.checkout') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                items: cart.map(i => ({ id: i.id, qty: i.qty })),
                customer_name: document.getElementById('customerNameInput').value || 'Pelanggan Umum',
                discount_amount: discount,
                pay_amount: payAmount,
                payment_method: currentPaymentMethod,
                dp_amount: getRawNumber(document.getElementById('dpAmountInput')?.value),
                due_date: document.getElementById('dueDateInput')?.value || null
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-xl">check_circle</span><span>BAYAR & CETAK STRUK</span>`;

            if (data.success) {
                showReceiptModal(data.transaction);
                clearCart();
                document.getElementById('payAmountInput').value = '';
                document.getElementById('discountInput').value = '0';
            } else {
                alert(data.message || 'Terjadi kesalahan saat checkout.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-xl">check_circle</span><span>BAYAR & CETAK STRUK</span>`;
            alert('Gagal menghubungi server.');
        });
    }

    function showReceiptModal(trx) {
        document.getElementById('rcptInvoice').textContent = `No: ${trx.invoice_number}`;
        document.getElementById('rcptDate').textContent = new Date(trx.created_at).toLocaleDateString('id-ID');
        document.getElementById('rcptCustomer').textContent = `Pelanggan: ${trx.customer_name}`;
        if (document.getElementById('rcptCashier')) document.getElementById('rcptCashier').textContent = trx.cashier_name || 'Kasir';

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

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
</script>
@endsection
