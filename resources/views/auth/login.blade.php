<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Halaman Login Kinetic POS System. Akses kasir dan admin untuk transaksi penjualan toko modern." />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="{{ url()->current() }}" />
    
    <title>Login Kasir - Kinetic POS</title>
    
    <!-- Fast Preconnect & Non-Blocking Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" media="print" onload="this.media='all'">

    <!-- Compiled CSS via Vite -->
    @vite(['resources/css/app.css'])

    <style>
        .glass-login {
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transform: translateZ(0);
        }
    </style>
</head>

<body class="bg-[#090d16] text-gray-100 font-sans antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Background Glow Orbs (GPU accelerated) -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-brand-600/20 rounded-full blur-3xl pointer-events-none will-change-transform"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-cyan-600/20 rounded-full blur-3xl pointer-events-none will-change-transform"></div>

    <main class="w-full max-w-md space-y-6 relative z-10">
        
        <!-- Brand Logo Header -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-cyan-400 flex items-center justify-center text-white shadow-glow mx-auto transform hover:scale-105 transition-transform">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-wide">
                Kinetic<span class="text-brand-400">POS</span> System
            </h1>
            <p class="text-xs text-slate-400">Silakan login menggunakan akun Admin atau Kasir Anda</p>
        </div>

        <!-- Glass Card Form -->
        <div class="glass-login rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="emailInput" class="block text-xs font-bold text-slate-300 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <input type="email" id="emailInput" name="email" value="{{ old('email') }}" required placeholder="email@kasir.com" class="w-full bg-slate-900/90 text-xs text-white rounded-xl pl-10 pr-4 py-3 border border-slate-800 focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                </div>

                <div>
                    <label for="passwordInput" class="block text-xs font-bold text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <input type="password" id="passwordInput" name="password" required placeholder="••••••••" class="w-full bg-slate-900/90 text-xs text-white rounded-xl pl-10 pr-10 py-3 border border-slate-800 focus:outline-none focus:border-brand-500 transition-all">
                        <button type="button" onclick="togglePasswordVisibility()" aria-label="Tampilkan atau sembunyikan password" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                            <svg id="pwdEyeIcon" class="w-5 h-5 text-slate-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-800 text-brand-600 focus:ring-brand-500">
                        <span>Ingat Sesi Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-glow transition-all active:scale-98">
                    Masuk ke Sistem Kasir
                </button>
            </form>

            <!-- Quick Fill Demo Login Preset -->
            <div class="pt-4 border-t border-slate-800/80 space-y-2">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">⚡ Akses Login Cepat Demo</span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="quickFill('admin@kasir.com', 'password123')" aria-label="Isi otomatis akun Demo Admin" class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-left transition-colors group">
                        <div class="flex items-center justify-between text-[11px] font-bold text-white group-hover:text-brand-300">
                            <span>👑 Role Admin</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono">admin@kasir.com</span>
                    </button>

                    <button type="button" onclick="quickFill('kasir@kasir.com', 'password123')" aria-label="Isi otomatis akun Demo Kasir" class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-left transition-colors group">
                        <div class="flex items-center justify-between text-[11px] font-bold text-white group-hover:text-emerald-400">
                            <span>🛒 Role Kasir</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono">kasir@kasir.com</span>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-500">
            &copy; 2026 Kinetic POS System. All Rights Reserved.
        </p>
    </main>

    <script>
        function quickFill(email, password) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = password;
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('passwordInput');
            const eyeSvg = document.getElementById('pwdEyeIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.016 10.016 0 013.982-.863c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-4.692-4.692a3 3 0 00-4.243-4.243"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>';
            } else {
                pwdInput.type = 'password';
                eyeSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html>
