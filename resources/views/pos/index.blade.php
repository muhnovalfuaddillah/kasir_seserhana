@extends('layouts.app')

@section('title', 'Terminal POS Kasir - Kinetic POS')

@section('content')
<!-- HTML5 QR/Barcode Scanner Script (Local Fast Vendor) -->
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}" defer></script>

<style>
@media print {
    @page {
        size: 58mm auto;
        margin: 0;
    }
    html, body {
        width: 58mm !important;
        background: #fff !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    body * {
        visibility: hidden !important;
    }
    #receiptModal, #receiptModal * {
        visibility: visible !important;
        color: #000000 !important;
        font-weight: 700 !important;
        opacity: 1 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border-color: #000000 !important;
    }
    #receiptModal {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 58mm !important;
        max-width: 58mm !important;
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
        box-shadow: none !important;
    }
    #receiptModal > div {
        width: 58mm !important;
        max-width: 58mm !important;
        box-shadow: none !important;
        border: none !important;
        padding: 4px 6px !important;
        margin: 0 !important;
        color: #000000 !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 10.5px !important;
        line-height: 1.25 !important;
    }
    #receiptModal button, .no-print {
        display: none !important;
    }
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

    <!-- MAIN TWO-COLUMN POS WORKSPACE (CART-FIRST FOCUSED LAYOUT) -->
    <div class="space-y-5">
        
        <!-- TOP CONTROL BAR: Live Search Bar + Stock Lookup + Camera Controls -->
        <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-4 shadow-xl">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                
                <!-- Search Bar Input with Live Dropdown -->
                <div class="relative flex-1">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-brand-400 text-xl pointer-events-none">search</span>
                        <input type="text" id="searchInput" aria-label="Cari nama produk atau scan barcode" oninput="handleSearchInputLive()" onkeydown="handleSearchInputKeydown(event)" placeholder="Cari nama produk atau scan barcode di sini [/]..." class="w-full bg-slate-950 text-sm sm:text-base text-white font-bold rounded-2xl pl-12 pr-10 py-3.5 border-2 border-brand-500/60 focus:border-brand-400 focus:ring-2 focus:ring-brand-500/30 transition-all shadow-inner">
                        <button type="button" id="clearSearchBtn" onclick="clearSearchInput()" aria-label="Hapus teks pencarian" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white" title="Hapus pencarian">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>

                    <!-- LIVE SEARCH RESULTS DROPDOWN (Pencarian langsung keluar) -->
                    <div id="liveSearchResults" class="hidden absolute top-full left-0 right-0 mt-2 bg-slate-900/95 backdrop-blur-xl border-2 border-brand-500/50 rounded-2xl shadow-2xl z-50 max-h-80 overflow-y-auto divide-y divide-slate-800/80">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Action Controls: Multiplier, Stock Lookup Modal, Camera, Add Product -->
                <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                    <div class="flex items-center bg-slate-950 border border-slate-800 rounded-2xl p-1 text-xs font-bold gap-1" title="Preset Qty Scan Otomatis">
                        <span class="text-xs text-slate-400 pl-2 hidden xl:inline font-bold">Qty Scan:</span>
                        <button type="button" onclick="setScanMultiplier(1)" id="mult-1" aria-label="Set kuantitas scan 1 kali" class="scan-mult-btn px-3 py-1.5 rounded-xl bg-brand-600 text-white font-extrabold text-xs transition-all">1x</button>
                        <button type="button" onclick="setScanMultiplier(2)" id="mult-2" aria-label="Set kuantitas scan 2 kali" class="scan-mult-btn px-3 py-1.5 rounded-xl text-slate-400 hover:text-white font-extrabold text-xs transition-all">2x</button>
                        <button type="button" onclick="setScanMultiplier(5)" id="mult-5" aria-label="Set kuantitas scan 5 kali" class="scan-mult-btn px-3 py-1.5 rounded-xl text-slate-400 hover:text-white font-extrabold text-xs transition-all">5x</button>
                        <button type="button" onclick="setScanMultiplier(10)" id="mult-10" aria-label="Set kuantitas scan 10 kali" class="scan-mult-btn px-3 py-1.5 rounded-xl text-slate-400 hover:text-white font-extrabold text-xs transition-all">10x</button>
                    </div>

                    <!-- DEDICATED STOCK LOOKUP BUTTON (TAMPIAN STOK TERSENDIRI) -->
                    <button type="button" onclick="openStockLookupModal()" aria-label="Cek stok barang" class="px-4 py-3.5 rounded-2xl bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/40 font-extrabold text-xs sm:text-sm flex items-center gap-2 transition-all shrink-0 shadow-lg">
                        <span class="material-symbols-outlined text-lg">inventory_2</span>
                        <span>📦 Cek Stok Barang</span>
                    </button>

                    <!-- Camera Toggle Button -->
                    <button type="button" onclick="toggleAlwaysOnCamera()" id="cameraToggleBtn" aria-label="Toggle kamera scan barcode" class="px-4 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-cyan-300 border border-cyan-500/40 font-bold text-xs sm:text-sm flex items-center gap-1.5 transition-all shrink-0">
                        <span class="material-symbols-outlined text-lg">videocam</span>
                        <span id="cameraToggleText" class="hidden sm:inline">Kamera Scan</span>
                    </button>

                    <!-- Quick Add Product Button -->
                    <button type="button" onclick="openQuickAddModal('')" aria-label="Tambah produk baru cepat" class="px-4 py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-1.5 transition-all shrink-0 shadow-glow">
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

            <!-- Keyboard Shortcuts Legend Strip -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pt-2 border-t border-slate-800/80">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                    <span class="material-symbols-outlined text-brand-400 text-base">info</span>
                    <span>Ketik nama produk atau scan barcode pada pencarian di atas untuk memasukkan barang ke keranjang.</span>
                </div>
                <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-[11px] font-bold text-slate-300 shrink-0">
                    <span class="material-symbols-outlined text-brand-400 text-sm">keyboard</span>
                    <span>Shortcut:</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-900 text-brand-300 font-mono border border-slate-800">[/] Cari</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-900 text-blue-300 font-mono border border-slate-800">[F3] Cek Stok</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-900 text-cyan-300 font-mono border border-slate-800">[F4] Uang Diterima</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-900 text-emerald-400 font-mono border border-slate-800">[F12] Bayar</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-900 text-rose-300 font-mono border border-slate-800">[F9] Reset</span>
                </div>
            </div>
        </div>

        <!-- UNIFIED POS CART & PAYMENT CARD (KERANJANG BELANJA + RINGKASAN PEMBAYARAN JADI SATU CONTAINER) -->
        <div id="posCartSection" class="glass-card rounded-3xl border-2 border-slate-800 overflow-hidden shadow-2xl space-y-0">
            
            <!-- 1. UNIFIED CARD HEADER STRIP -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-[#0f172a] via-[#064e3b] to-[#0f172a] border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-400 text-2xl">shopping_cart_checkout</span>
                    <h2 class="text-base sm:text-lg font-black text-white">Keranjang Belanja & Ringkasan Pembayaran</h2>
                    <span id="cartCountBadge" class="px-3 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">0 Item</span>
                </div>
                
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openStockLookupModal()" class="px-3.5 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/30 text-xs font-extrabold flex items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-base">inventory_2</span>
                        <span>📦 + Pilih dari Stok</span>
                    </button>
                    <button type="button" onclick="clearCart()" class="text-xs font-bold text-rose-400 hover:text-rose-300 transition-colors flex items-center gap-1 hover:bg-rose-500/10 px-2.5 py-1.5 rounded-xl border border-rose-500/20">
                        <span class="material-symbols-outlined text-base">delete_sweep</span>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- 2. UNIFIED CARD CONTENT (GRID 2 KOLOM TERPADU DALAM SATU WADAH) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-800/80">
                
                <!-- LEFT SECTION: TABEL KERANJANG BELANJA (LG: 7 COLS) -->
                <div class="lg:col-span-7 flex flex-col justify-between min-h-[420px]">
                    <div class="p-4 sm:p-5 overflow-x-auto flex-1 min-h-[300px] max-h-[500px] overflow-y-auto">
                        <table class="w-full text-left text-xs sm:text-sm text-slate-200">
                            <thead class="text-xs font-extrabold text-slate-400 uppercase bg-slate-900/90 border-b border-slate-800 sticky top-0 z-10">
                                <tr>
                                    <th class="p-3.5 rounded-l-xl">Produk & Detail</th>
                                    <th class="p-3.5 text-right">Harga Satuan</th>
                                    <th class="p-3.5 text-center">Jumlah (Qty)</th>
                                    <th class="p-3.5 text-right">Subtotal</th>
                                    <th class="p-3.5 text-center rounded-r-xl">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody" class="divide-y divide-slate-800/80 font-medium">
                                <!-- Dynamic Cart Rows via JS -->
                            </tbody>
                        </table>

                        <!-- Empty Cart Placeholder State -->
                        <div id="emptyCartState" class="py-16 text-center text-slate-500 space-y-3">
                            <span class="material-symbols-outlined text-7xl text-slate-700">remove_shopping_cart</span>
                            <div class="space-y-1">
                                <p class="text-base font-black text-slate-300">Keranjang Belanja Masih Kosong</p>
                                <p class="text-xs text-slate-400 max-w-md mx-auto">Ketik nama produk pada kolom pencarian di atas, scan barcode, atau klik <button type="button" onclick="openStockLookupModal()" class="text-blue-400 underline font-bold">"📦 Cek Stok Barang"</button> untuk memasukkan barang.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Footer Summary Subtotal Bar -->
                    <div class="p-4 bg-slate-900/80 border-t border-slate-800 flex items-center justify-between text-xs sm:text-sm font-bold text-slate-300">
                        <span id="cartSummaryItemCount">Total: 0 Barang</span>
                        <span class="text-emerald-400 font-extrabold font-mono text-sm">Subtotal: <strong id="cartSubtotalText" class="text-base">Rp 0</strong></span>
                    </div>
                </div>

                <!-- RIGHT SECTION: FORM & RINGKASAN PEMBAYARAN (LG: 5 COLS) -->
                <div class="lg:col-span-5 bg-slate-900/40 p-4 sm:p-5 flex flex-col justify-between space-y-4">
                    
                    <!-- HERO DISPLAY BANNER (GRAND TOTAL & KEMBALIAN) -->
                    <div class="p-4 sm:p-5 bg-gradient-to-r from-[#064e3b] via-[#0f172a] to-[#082f49] rounded-2xl border-2 border-emerald-500/50 shadow-xl space-y-3">
                        <div class="space-y-0.5">
                            <span class="text-[11px] font-extrabold text-emerald-300/80 uppercase tracking-wider block">GRAND TOTAL TAGIHAN</span>
                            <div id="finalTotalDisplay" class="text-3xl sm:text-4xl lg:text-5xl font-black font-mono text-emerald-400 tracking-tight truncate drop-shadow-md">
                                Rp 0
                            </div>
                        </div>

                        <!-- BADGE DETEKSI OTOMATIS HARGA GROSIR (REAL-TIME DETECT) -->
                        <div id="wholesaleNoticeBadge" class="hidden p-2.5 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-extrabold flex items-center justify-between shadow-inner transition-all animate-pulse">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base text-amber-400">sell</span>
                                <span>🏷️ Menerapkan Harga Grosir!</span>
                            </span>
                            <span id="wholesaleSavedAmount" class="font-mono font-black text-amber-200 bg-amber-950/80 px-2 py-0.5 rounded-lg border border-amber-500/30">
                                Hemat Rp 0
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-emerald-500/30 text-xs">
                            <span class="text-slate-300 font-bold">
                                Metode: <strong id="topPayMethodDisplay" class="text-white uppercase font-black bg-slate-950 px-2 py-0.5 rounded-lg border border-slate-800">TUNAI</strong>
                            </span>
                            <span class="flex items-center gap-1 text-slate-300 font-bold">
                                Kembalian: <strong id="topChangeDisplay" class="text-cyan-300 font-black font-mono text-base sm:text-xl">Rp 0</strong>
                            </span>
                        </div>
                    </div>

                    <!-- Customer Select & Discount Row -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-300">Pilih / Nama Pelanggan</label>
                                <a href="{{ route('customers.index') }}" target="_blank" class="text-[10px] text-brand-400 hover:underline font-bold">+ Baru</a>
                            </div>
                            <select id="customerSelect" onchange="handleCustomerSelectChange()" class="w-full bg-slate-950 text-xs font-semibold text-white rounded-xl px-3 py-2.5 border border-slate-800 focus:outline-none focus:border-brand-500">
                                <option value="" data-debt="0" data-limit="0" data-name="Pelanggan Umum">-- Pelanggan Umum --</option>
                                @if(isset($customers) && count($customers) > 0)
                                    <optgroup label="Pelanggan Terdaftar">
                                        @foreach($customers as $c)
                                            @php
                                                $avail = $c->credit_limit > 0 ? max(0, $c->credit_limit - $c->current_debt) : 'unlimited';
                                            @endphp
                                            <option value="{{ $c->id }}" data-debt="{{ $c->current_debt }}" data-limit="{{ $c->credit_limit }}" data-avail="{{ $avail }}" data-name="{{ $c->name }}">
                                                {{ $c->name }} (Hutang: Rp {{ number_format($c->current_debt, 0, ',', '.') }}{{ $c->credit_limit > 0 ? ' | Sisa Limit: Rp ' . number_format($avail, 0, ',', '.') : '' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>

                            <input type="text" id="customerNameInput" placeholder="Nama Pelanggan / Pemohon Kasbon..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800 focus:outline-none focus:border-brand-500 mt-1.5 hidden">

                            <div id="customerDebtBadge" class="hidden mt-1.5 p-2 rounded-xl bg-indigo-950/40 border border-indigo-500/30 text-[11px] space-y-0.5">
                                <div class="flex justify-between font-bold text-slate-300">
                                    <span>Tunggakan Hutang:</span>
                                    <span id="badgeCurrentDebt" class="text-amber-400 font-mono">Rp 0</span>
                                </div>
                                <div class="flex justify-between text-slate-400">
                                    <span>Batas Limit Kasbon:</span>
                                    <span id="badgeCreditLimit" class="text-white font-mono">Unlimited</span>
                                </div>
                            </div>
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

                        <div id="quickCashRow" class="flex items-center gap-2 overflow-x-auto pb-0.5">
                            <button type="button" onclick="setQuickCash('exact')" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">Pas</button>
                            <button type="button" onclick="setQuickCash(50000)" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">50rb</button>
                            <button type="button" onclick="setQuickCash(100000)" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">100rb</button>
                            <button type="button" onclick="setQuickCash(200000)" class="px-3 py-1.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:bg-slate-700">200rb</button>
                        </div>

                        <div class="p-3 rounded-2xl bg-[#032838] border-2 border-cyan-500/50 flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-bold text-cyan-300 uppercase tracking-wider">Kembalian:</span>
                            <span id="changeDisplay" class="font-black text-cyan-300 font-mono text-lg sm:text-xl">Rp 0</span>
                        </div>
                    </div>

                    <!-- Big Checkout Action Button -->
                    <button type="button" onclick="processCheckout()" id="checkoutBtn" class="w-full py-4 sm:py-4.5 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:from-emerald-500 hover:to-teal-400 text-white font-black text-base sm:text-lg shadow-[0_0_25px_rgba(16,185,129,0.4)] transition-all transform active:scale-98 disabled:opacity-50 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-2xl">check_circle</span>
                        <span>BAYAR & CETAK STRUK</span>
                        <span class="ml-1 px-2 py-0.5 rounded-lg bg-black/40 text-emerald-200 font-mono text-xs font-bold border border-emerald-400/40">F12 / Ctrl+Enter</span>
                    </button>
                </div>
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

<!-- DEDICATED STOCK LOOKUP MODAL FOR CASHIER (TAMPILAN STOK TERSENDIRI) -->
<div id="stockLookupModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-5xl w-full p-5 sm:p-6 space-y-4 border border-blue-500/30 shadow-2xl bg-slate-950/95 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800 shrink-0">
            <div class="space-y-0.5">
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-400 text-2xl">inventory_2</span>
                    Cek Stok & Katalog Produk Kasir
                </h3>
                <p class="text-xs text-slate-400">Cari informasi sisa stok, harga modal, harga jual, & pilih barang ke keranjang.</p>
            </div>
            <button type="button" onclick="closeStockLookupModal()" class="w-9 h-9 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition-all">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Filter Controls Inside Modal -->
        <div class="space-y-3 shrink-0">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" id="modalStockSearchInput" oninput="renderStockModalTable()" placeholder="Cari nama produk / scan barcode di sini..." class="w-full bg-slate-900 text-xs sm:text-sm text-white rounded-xl pl-10 pr-4 py-2.5 border border-slate-800 focus:outline-none focus:border-blue-500 font-semibold">
                </div>
                <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 no-scrollbar shrink-0">
                    <button type="button" onclick="setModalStockCategory('all')" id="modal-cat-all" class="modal-stock-cat-pill px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white shadow-glow">
                        Semua Produk
                    </button>
                    <button type="button" onclick="setModalStockCategory('lowstock')" id="modal-cat-lowstock" class="modal-stock-cat-pill px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 text-amber-300 border border-slate-800 hover:bg-slate-800">
                        ⚠️ Stok Menipis (<=5)
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" onclick="setModalStockCategory({{ $cat->id }})" id="modal-cat-{{ $cat->id }}" class="modal-stock-cat-pill px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 text-slate-400 border border-slate-800 hover:bg-slate-800 hover:text-white">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Scrollable Table -->
        <div class="overflow-x-auto flex-1 min-h-[300px] max-h-[500px] border border-slate-800 rounded-2xl">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 sticky top-0 z-10 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">Nama Produk & Barcode</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5 text-right">Harga Modal</th>
                        <th class="p-3.5 text-right">Harga Jual POS</th>
                        <th class="p-3.5 text-center">Sisa Stok</th>
                        <th class="p-3.5 text-center">Aksi Kasir</th>
                    </tr>
                </thead>
                <tbody id="stockModalTableBody" class="divide-y divide-slate-800/80 font-medium">
                    <!-- JS Populated -->
                </tbody>
            </table>
        </div>

        <div class="pt-2 flex items-center justify-between text-xs text-slate-400 shrink-0 border-t border-slate-900">
            <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-900 font-mono border border-slate-800 text-slate-300">Esc</kbd> untuk menutup tampilan stok.</span>
            <button type="button" onclick="closeStockLookupModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700 font-bold">
                Tutup Tampilan Stok
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
    let lastScannedCode = '';
    let barcodeBuffer = '';
    let barcodeTimeout = null;
    let modalStockCategoryFilter = 'all';

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

            // 2. Shortcut F3 -> BUKA TAMPILAN STOK PRODUK (MODAL)
            if (e.key === 'F3') {
                e.preventDefault();
                openStockLookupModal();
                return;
            }

            // 3. Shortcut F4 atau Alt+P -> Fokus ke input Uang Diterima (Rp)
            if (e.key === 'F4' || (e.altKey && e.key.toLowerCase() === 'p')) {
                e.preventDefault();
                const payInput = document.getElementById('payAmountInput');
                if (payInput) {
                    payInput.focus();
                    payInput.select();
                }
                return;
            }

            // 4. Shortcut F9 -> Kosongkan / Reset Keranjang
            if (e.key === 'F9') {
                e.preventDefault();
                if (cart.length > 0 && confirm('Kosongkan keranjang belanja?')) {
                    clearCart();
                }
                return;
            }

            // 5. Shortcut F2 -> Toggle Kamera Scan
            if (e.key === 'F2') {
                e.preventDefault();
                toggleAlwaysOnCamera();
                return;
            }

            // 6. Shortcut Escape -> Tutup Modal
            if (e.key === 'Escape') {
                closeReceiptModal();
                closeQuickAddModal();
                closeStockLookupModal();
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
            const wholesaleList = product.wholesale_prices || product.wholesalePrices || [];
            cart.push({
                id: product.id,
                name: product.name,
                barcode: product.barcode || 'NOBARCODE',
                purchase_price: product.purchase_price || 0,
                selling_price: product.selling_price,
                wholesale_prices: wholesaleList,
                stock: product.stock,
                unit: product.unit || 'pcs',
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

    function getEffectiveItemPrice(item) {
        if (!item) return { price: 0, isWholesale: false, label: '' };
        let basePrice = parseFloat(item.selling_price) || 0;
        
        const wholesaleTiers = item.wholesale_prices || item.wholesalePrices || [];

        if (wholesaleTiers && wholesaleTiers.length > 0) {
            const currentQty = parseInt(item.qty, 10) || 1;
            const eligibleTiers = wholesaleTiers
                .filter(w => parseInt(w.min_qty, 10) <= currentQty)
                .sort((a, b) => parseInt(b.min_qty, 10) - parseInt(a.min_qty, 10));
            
            if (eligibleTiers.length > 0) {
                const tierPrice = parseFloat(eligibleTiers[0].price) || 0;
                if (tierPrice < basePrice) {
                    return {
                        price: tierPrice,
                        isWholesale: true,
                        label: eligibleTiers[0].unit_label || `Min ${eligibleTiers[0].min_qty} ${item.unit || 'pcs'}`
                    };
                }
            }
        }

        return {
            price: basePrice,
            isWholesale: false,
            label: ''
        };
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
            tableBody.innerHTML = cart.map(item => {
                const priceInfo = getEffectiveItemPrice(item);
                const subtotal = priceInfo.price * item.qty;

                return `
                <tr class="hover:bg-slate-800/40 transition-colors">
                    <td class="p-3 font-bold text-white flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-white p-0.5 rounded-lg shrink-0 flex items-center justify-center border border-slate-700">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=${encodeURIComponent(item.barcode || 'NOBARCODE')}" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="truncate max-w-[140px] sm:max-w-[180px] text-xs sm:text-sm font-black text-white block" title="${item.name}">${item.name}</span>
                            ${priceInfo.isWholesale ? `
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/30 inline-flex items-center gap-0.5">
                                    🏷️ Grosir (${priceInfo.label})
                                </span>
                            ` : ''}
                        </div>
                    </td>
                    <td class="p-3 text-right text-xs sm:text-sm">
                        ${priceInfo.isWholesale ? `
                            <div class="space-y-0.5">
                                <span class="font-black text-amber-400 text-xs sm:text-sm block font-mono">Rp ${formatRupiah(priceInfo.price)}</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/40 inline-block">
                                    🏷️ GROSIR (${priceInfo.label})
                                </span>
                                <span class="block text-[10px] text-slate-500 line-through">Ecer: Rp ${formatRupiah(item.selling_price)}</span>
                            </div>
                        ` : `
                            <span class="font-extrabold text-slate-200 font-mono">Rp ${formatRupiah(priceInfo.price)}</span>
                        `}
                    </td>
                    <td class="p-3 text-center">
                        <div class="inline-flex items-center gap-1.5 bg-slate-950 p-1 rounded-xl border border-slate-800">
                            <button onclick="updateQty(${item.id}, -1)" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-extrabold flex items-center justify-center shadow text-xs sm:text-sm">-</button>
                            <input type="number" id="qty-input-${item.id}" value="${item.qty}" min="1" max="${item.stock}" onfocus="this.select()" onchange="setCustomQty(${item.id}, this.value)" onkeydown="handleQtyKeydown(event, ${item.id})" class="w-14 sm:w-16 text-center bg-slate-900 border-2 border-brand-500/80 text-white font-black text-xs sm:text-sm rounded-lg py-1 px-1 focus:border-brand-400 focus:ring-2 focus:ring-brand-400">
                            <button onclick="updateQty(${item.id}, 1)" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-extrabold flex items-center justify-center shadow text-xs sm:text-sm">+</button>
                        </div>
                    </td>
                    <td class="p-3 text-right font-black text-emerald-400 text-xs sm:text-base">Rp ${formatRupiah(subtotal)}</td>
                    <td class="p-3 text-center">
                        <button onclick="updateQty(${item.id}, -${item.qty})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" title="Hapus Barang">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </td>
                </tr>
            `}).join('');
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

    function getCartEffectiveTotals() {
        let normalSubtotal = 0;
        let effectiveSubtotal = 0;

        cart.forEach(item => {
            const priceInfo = getEffectiveItemPrice(item);
            const normalP = parseFloat(item.selling_price) || 0;
            normalSubtotal += (normalP * item.qty);
            effectiveSubtotal += (priceInfo.price * item.qty);
        });

        const wholesaleSaved = Math.max(0, normalSubtotal - effectiveSubtotal);
        const discount = getRawNumber(document.getElementById('discountInput')?.value || 0);
        const finalTotal = Math.max(0, effectiveSubtotal - discount);

        return {
            normalSubtotal,
            effectiveSubtotal,
            wholesaleSaved,
            discount,
            finalTotal
        };
    }

    function renderCartSummary() {
        const totals = getCartEffectiveTotals();

        const totalEl = document.getElementById('finalTotalDisplay');
        if (totalEl) totalEl.textContent = `Rp ${formatRupiah(totals.finalTotal)}`;

        const cartSubtotalEl = document.getElementById('cartSubtotalText');
        if (cartSubtotalEl) cartSubtotalEl.textContent = `Rp ${formatRupiah(totals.effectiveSubtotal)}`;

        const itemCountEl = document.getElementById('cartSummaryItemCount');
        if (itemCountEl) {
            const count = cart.reduce((sum, item) => sum + item.qty, 0);
            itemCountEl.textContent = `Total: ${count} Barang`;
        }

        // Wholesale Savings Badge Indicator inside GRAND TOTAL TAGIHAN Banner
        const noticeEl = document.getElementById('wholesaleNoticeBadge');
        const savedEl = document.getElementById('wholesaleSavedAmount');
        if (noticeEl && savedEl) {
            if (totals.wholesaleSaved > 0) {
                savedEl.textContent = `Hemat Rp ${formatRupiah(totals.wholesaleSaved)}`;
                noticeEl.classList.remove('hidden');
                noticeEl.classList.add('flex');
            } else {
                noticeEl.classList.add('hidden');
                noticeEl.classList.remove('flex');
            }
        }
        
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
            const totals = getCartEffectiveTotals();
            payInput.value = new Intl.NumberFormat('id-ID').format(totals.finalTotal);
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
        const totals = getCartEffectiveTotals();
        const payInput = document.getElementById('payAmountInput');
        if (amount === 'exact') {
            payInput.value = new Intl.NumberFormat('id-ID').format(totals.finalTotal);
        } else {
            payInput.value = new Intl.NumberFormat('id-ID').format(amount);
        }
        calculateChange();
    }

    function calculateChange() {
        const totals = getCartEffectiveTotals();
        const payInputVal = document.getElementById('payAmountInput')?.value || '';
        let payAmount = getRawNumber(payInputVal);

        if (currentPaymentMethod === 'hutang') {
            payAmount = getRawNumber(document.getElementById('dpAmountInput')?.value || 0);
        }

        const change = payAmount - totals.finalTotal;
        const changeEl = document.getElementById('changeDisplay');
        const topChangeEl = document.getElementById('topChangeDisplay');

        if (currentPaymentMethod === 'hutang') {
            const sisaHutang = Math.max(0, totals.finalTotal - payAmount);
            const str = `Sisa: Rp ${formatRupiah(sisaHutang)}`;
            if (changeEl) { changeEl.textContent = str; changeEl.className = "font-extrabold text-purple-400 text-base sm:text-lg"; }
            if (topChangeEl) { topChangeEl.textContent = `Rp ${formatRupiah(sisaHutang)}`; topChangeEl.className = "text-purple-400 font-black font-mono text-base sm:text-xl"; }
        } else if (change >= 0) {
            const str = `Rp ${formatRupiah(change)}`;
            if (changeEl) { changeEl.textContent = str; changeEl.className = "font-extrabold text-cyan-300 text-base sm:text-lg"; }
            if (topChangeEl) { topChangeEl.textContent = str; topChangeEl.className = "text-cyan-300 font-black font-mono text-base sm:text-xl"; }
        } else {
            const str = `-Rp ${formatRupiah(Math.abs(change))}`;
            if (changeEl) { changeEl.textContent = str; changeEl.className = "font-extrabold text-rose-400 text-base sm:text-lg"; }
            if (topChangeEl) { topChangeEl.textContent = str; topChangeEl.className = "text-rose-400 font-black font-mono text-base sm:text-xl"; }
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

    function handleCustomerSelectChange() {
        const select = document.getElementById('customerSelect');
        const manualInput = document.getElementById('customerNameInput');
        const debtBadge = document.getElementById('customerDebtBadge');
        
        if (!select) return;

        const opt = select.options[select.selectedIndex];
        const custId = select.value;
        const custName = opt ? opt.getAttribute('data-name') : '';
        const debt = parseFloat(opt ? opt.getAttribute('data-debt') : 0) || 0;
        const limit = parseFloat(opt ? opt.getAttribute('data-limit') : 0) || 0;

        if (manualInput) {
            manualInput.value = custName || '';
        }

        if (debtBadge) {
            if (custId) {
                debtBadge.classList.remove('hidden');
                document.getElementById('badgeCurrentDebt').textContent = "Rp " + formatRupiah(debt);
                if (limit > 0) {
                    document.getElementById('badgeCreditLimit').textContent = "Rp " + formatRupiah(limit);
                } else {
                    document.getElementById('badgeCreditLimit').textContent = "Unlimited (Tanpa Limit)";
                }
            } else {
                debtBadge.classList.add('hidden');
            }
        }
    }

    function processCheckout() {
        if (cart.length === 0) {
            alert('Keranjang belanja masih kosong!');
            return;
        }

        const cartTotals = getCartEffectiveTotals();
        const discount = cartTotals.discount;
        const finalTotal = cartTotals.finalTotal;
        const payAmount = getRawNumber(document.getElementById('payAmountInput').value);

        if (currentPaymentMethod !== 'hutang' && payAmount < finalTotal) {
            alert(`Uang pembayaran (Rp ${formatRupiah(payAmount)}) kurang dari total tagihan (Rp ${formatRupiah(finalTotal)})!`);
            return;
        }

        const customerSelect = document.getElementById('customerSelect');
        const customerId = customerSelect ? customerSelect.value : null;
        const customerName = document.getElementById('customerNameInput')?.value || (customerSelect ? customerSelect.options[customerSelect.selectedIndex]?.getAttribute('data-name') : 'Pelanggan Umum');

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
                customer_id: customerId || null,
                customer_name: customerName || 'Pelanggan Umum',
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
        
        let formattedDate = '-';
        if (trx.created_at) {
            const dt = new Date(trx.created_at);
            const dateStr = dt.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const timeStr = dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
            formattedDate = `${dateStr} ${timeStr} WIB`;
        }
        document.getElementById('rcptDate').textContent = formattedDate;
        document.getElementById('rcptCustomer').textContent = `Pelanggan: ${trx.customer_name}`;
        if (document.getElementById('rcptCashier')) document.getElementById('rcptCashier').textContent = trx.cashier_name || 'Kasir';

        const itemsEl = document.getElementById('rcptItems');
        itemsEl.innerHTML = trx.details.map(d => {
            const sellingPrice = parseFloat(d.selling_price) || 0;
            const normalPrice = parseFloat(d.normal_price) || sellingPrice;
            const itemSubtotal = parseFloat(d.subtotal) || (sellingPrice * (d.quantity || 1));
            const isWholesale = d.is_wholesale || (normalPrice > 0 && sellingPrice < normalPrice);

            return `
                <div class="space-y-0.5 border-b border-dashed border-slate-200/80 pb-1.5 pt-1">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>${escapeHtml(d.product_name)}</span>
                        ${isWholesale ? `<span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 border border-amber-300">GROSIR</span>` : ''}
                    </div>
                    <div class="flex justify-between text-slate-700 text-xs">
                        <span>${d.quantity} x Rp ${formatRupiah(sellingPrice)}</span>
                        <span class="font-bold text-slate-900 font-mono">Rp ${formatRupiah(itemSubtotal)}</span>
                    </div>
                    ${isWholesale ? `<div class="text-[9px] font-bold text-emerald-700 italic">🏷️ ${escapeHtml(d.wholesale_label || 'Harga Grosir Tier')}</div>` : ''}
                </div>
            `;
        }).join('');

        const subtotal = trx.details.reduce((s, d) => s + (parseFloat(d.subtotal) || 0), 0);
        document.getElementById('rcptSubtotal').textContent = `Rp ${formatRupiah(subtotal)}`;
        document.getElementById('rcptDiscount').textContent = `Rp ${formatRupiah(parseFloat(trx.discount_amount) || 0)}`;
        document.getElementById('rcptTotal').textContent = `Rp ${formatRupiah(parseFloat(trx.total_amount) || 0)}`;
        document.getElementById('rcptMethod').textContent = (trx.payment_method || 'CASH').toUpperCase();
        document.getElementById('rcptPay').textContent = `Rp ${formatRupiah(parseFloat(trx.pay_amount) || 0)}`;
        document.getElementById('rcptChange').textContent = `Rp ${formatRupiah(parseFloat(trx.change_amount) || 0)}`;
        document.getElementById('rcptQrImg').src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(trx.invoice_number)}`;

        document.getElementById('receiptModal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').classList.add('hidden');
    }

    function formatRupiah(num) {
        if (num === null || num === undefined || isNaN(num)) return '0';
        return new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(num)));
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }

    function openStockLookupModal() {
        const modal = document.getElementById('stockLookupModal');
        if (modal) {
            modal.classList.remove('hidden');
            const searchInput = document.getElementById('modalStockSearchInput');
            if (searchInput) searchInput.focus();
            renderStockModalTable();
        }
    }

    function closeStockLookupModal() {
        const modal = document.getElementById('stockLookupModal');
        if (modal) modal.classList.add('hidden');
    }

    function setModalStockCategory(cat) {
        modalStockCategoryFilter = cat;
        document.querySelectorAll('.modal-stock-cat-pill').forEach(btn => {
            btn.className = 'modal-stock-cat-pill px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 text-slate-400 border border-slate-800 hover:bg-slate-800 hover:text-white';
        });
        const activeBtn = document.getElementById(`modal-cat-${cat}`);
        if (activeBtn) {
            activeBtn.className = 'modal-stock-cat-pill px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white shadow-glow';
        }
        renderStockModalTable();
    }

    function renderStockModalTable() {
        const query = (document.getElementById('modalStockSearchInput')?.value || '').trim().toLowerCase();
        const tbody = document.getElementById('stockModalTableBody');
        if (!tbody) return;

        let filtered = productsData.filter(p => {
            const nameMatch = p.name && p.name.toLowerCase().includes(query);
            const barcodeMatch = p.barcode && p.barcode.toString().toLowerCase().includes(query);
            let catMatch = true;
            if (modalStockCategoryFilter === 'lowstock') {
                catMatch = p.stock <= 5;
            } else if (modalStockCategoryFilter !== 'all') {
                catMatch = p.category_id == modalStockCategoryFilter;
            }
            return (nameMatch || barcodeMatch) && catMatch;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-500 font-bold">Tidak ada produk ditemukan untuk kriteria ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(p => {
            const isLow = p.stock <= 5 && p.stock > 0;
            const isZero = p.stock <= 0;
            let stockBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Stok: ${p.stock} ${p.unit||'pcs'}</span>`;
            if (isZero) {
                stockBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">HABIS (0)</span>`;
            } else if (isLow) {
                stockBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">MENIPIS (${p.stock})</span>`;
            }

            const cleanName = escapeHtml(p.name);

            return `
                <tr class="hover:bg-slate-800/40 transition-colors">
                    <td class="p-3.5 font-bold text-white">
                        <div>${cleanName}</div>
                        <div class="text-[10px] font-mono text-slate-400">Barcode: ${escapeHtml(p.barcode || '-')}</div>
                    </td>
                    <td class="p-3.5 text-slate-300 font-medium">${escapeHtml(p.category ? p.category.name : 'Umum')}</td>
                    <td class="p-3.5 text-right font-mono text-slate-400">Rp ${formatRupiah(p.purchase_price || 0)}</td>
                    <td class="p-3.5 text-right font-mono font-black text-emerald-400 text-sm">Rp ${formatRupiah(p.selling_price || 0)}</td>
                    <td class="p-3.5 text-center">${stockBadge}</td>
                    <td class="p-3.5 text-center">
                        <button type="button" onclick="addToCartById(${p.id}); showScanToast('${cleanName} masuk keranjang!')" class="px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs shadow-glow flex items-center gap-1 mx-auto transition-all transform active:scale-95">
                            <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                            <span>+ Keranjang</span>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }
</script>
@endsection
