<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kinetic POS - Dashboard Kasir Modern</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 25px -5px rgba(99, 102, 241, 0.25)',
                        'glow-emerald': '0 0 25px -5px rgba(16, 185, 129, 0.25)',
                    }
                }
            }
        }
    </script>
    
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0b0f19; }
        ::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }
        
        .glass-card {
            background: rgba(17, 24, 39, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }
    </style>
</head>

<body class="bg-[#0b0f19] text-gray-100 font-sans antialiased selection:bg-brand-500 selection:text-white min-h-screen">

    <!-- Mobile Overlay -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Sidebar Navbar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-[#0f172a] border-r border-slate-800/80 z-50 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
        
        <!-- Brand Header -->
        <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-cyan-400 flex items-center justify-center text-white shadow-glow group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl font-bold">point_of_sale</span>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-none text-white tracking-wide flex items-center gap-1">
                        Kinetic<span class="text-brand-400 font-extrabold">POS</span>
                    </h1>
                    <span class="text-[11px] font-medium text-slate-400 tracking-wider uppercase">System Kasir Pro</span>
                </div>
            </a>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <div class="px-3 pb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Menu Utama</div>
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-brand-600 to-indigo-600 text-white shadow-glow' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <span class="material-symbols-outlined text-xl">grid_view</span>
                <span>Dashboard</span>
                @if(request()->routeIs('dashboard'))
                    <span class="ml-auto w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                @endif
            </a>

            <!-- Terminal POS Kasir -->
            <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('pos.index') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-glow-emerald' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                <span class="material-symbols-outlined text-xl {{ request()->routeIs('pos.index') ? '' : 'group-hover:text-emerald-400' }} transition-colors">shopping_cart</span>
                <span>Terminal POS Kasir</span>
                <span class="ml-auto px-2 py-0.5 text-[10px] font-bold bg-emerald-500/20 text-emerald-400 rounded-md border border-emerald-500/30 uppercase tracking-wider">Live</span>
            </a>

            @if(auth()->user()->isAdmin())
                <!-- Master Produk (Admin Only) -->
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('products.index') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                    <span class="material-symbols-outlined text-xl {{ request()->routeIs('products.index') ? 'text-brand-400' : 'group-hover:text-brand-400' }} transition-colors">inventory_2</span>
                    <span>Master Produk</span>
                </a>

                <!-- Master Kategori (Admin Only) -->
                <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('categories.index') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                    <span class="material-symbols-outlined text-xl {{ request()->routeIs('categories.index') ? 'text-cyan-400' : 'group-hover:text-cyan-400' }} transition-colors">category</span>
                    <span>Master Kategori</span>
                </a>
            @endif

            <!-- Riwayat Penjualan -->
            <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('transactions.index') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                <span class="material-symbols-outlined text-xl {{ request()->routeIs('transactions.index') ? 'text-amber-400' : 'group-hover:text-amber-400' }} transition-colors">receipt_long</span>
                <span>Riwayat Penjualan</span>
            </a>

            <!-- Profil Saya -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('profile.edit') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                <span class="material-symbols-outlined text-xl {{ request()->routeIs('profile.edit') ? 'text-brand-400' : 'group-hover:text-brand-400' }} transition-colors">account_circle</span>
                <span>Profil Saya</span>
            </a>

            @if(auth()->user()->isAdmin())
                <div class="pt-4 px-3 pb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Laporan Keuangan</div>

                <!-- Laporan & Keuntungan (Admin Only) -->
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('reports.index') ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 group' }}">
                    <span class="material-symbols-outlined text-xl {{ request()->routeIs('reports.index') ? 'text-purple-400' : 'group-hover:text-purple-400' }} transition-colors">analytics</span>
                    <span>Laporan & Keuangan</span>
                </a>
            @endif
        </nav>

        <!-- User Profile Card Sidebar Footer -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/50">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 min-w-0 flex-1 group hover:opacity-80 transition-opacity">
                    <div class="relative shrink-0">
                        <img class="w-10 h-10 rounded-full object-cover ring-2 ring-brand-500/50 group-hover:ring-brand-400" 
                            src="{{ auth()->user()->avatar ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : asset('storage/' . auth()->user()->avatar)) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop' }}" 
                            alt="Foto Profile">
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-slate-900"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate group-hover:text-brand-300 transition-colors">{{ auth()->user()->name }}</p>
                        <span class="inline-block text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md {{ auth()->user()->isAdmin() ? 'bg-brand-500/20 text-brand-300 border border-brand-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                            Role: {{ auth()->user()->role }}
                        </span>
                    </div>
                </a>

                <!-- Logout Form Trigger -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" title="Logout dari sistem">
                        <span class="material-symbols-outlined text-xl">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Top Navigation Header -->
    <header class="fixed top-0 right-0 left-0 lg:left-64 h-20 bg-[#0f172a]/90 backdrop-blur-md border-b border-slate-800/80 z-30 flex items-center justify-between px-4 sm:px-8">
        
        <!-- Left Section: Mobile Toggle & Store Title -->
        <div class="flex items-center gap-4 flex-1 max-w-xl">
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-300 hover:text-white p-2 rounded-xl bg-slate-800 border border-slate-700">
                <span class="material-symbols-outlined text-xl">menu</span>
            </button>
            <div class="hidden sm:flex items-center gap-2 text-sm text-slate-400 font-medium">
                <span class="material-symbols-outlined text-brand-400">storefront</span>
                <span>Toko Utama • Cabang Jakarta Pusat</span>
            </div>
        </div>

        <!-- Right Section: Quick Stats, Date Time & POS Shortcut -->
        <div class="flex items-center gap-3 sm:gap-4">
            
            <!-- Quick Date/Time Pill -->
            <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900/80 border border-slate-800 text-xs font-medium text-slate-300">
                <span class="material-symbols-outlined text-brand-400 text-base">calendar_today</span>
                <span id="currentDate">Senin, 10 Ags 2026</span>
                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                <span id="currentTime" class="font-bold text-white">09:00 WIB</span>
            </div>

            <!-- User Badge Link -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-brand-500/50 text-xs font-semibold text-slate-200 hover:text-white transition-all">
                <span class="material-symbols-outlined text-emerald-400 text-base">account_circle</span>
                <span>{{ auth()->user()->name }}</span>
            </a>

            <!-- Quick Action POS Button -->
            <a href="{{ route('pos.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs shadow-glow-emerald transition-all transform active:scale-95">
                <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                <span>+ Buka Kasir</span>
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <div class="lg:ml-64 pt-20 min-h-screen">
        @if(session('success'))
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
            
            const timeEl = document.getElementById('currentTime');
            const dateEl = document.getElementById('currentDate');
            if (timeEl) timeEl.textContent = timeStr;
            if (dateEl) dateEl.textContent = dateStr;
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>
