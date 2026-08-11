<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Kasir - Kinetic POS</title>
    
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
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 35px -5px rgba(99, 102, 241, 0.3)',
                    }
                }
            }
        }
    </script>
    <style>
        .glass-login {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>

<body class="bg-[#090d16] text-gray-100 font-sans antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Background Glow Orbs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-brand-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-cyan-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md space-y-6 relative z-10">
        
        <!-- Brand Logo Header -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-cyan-400 flex items-center justify-center text-white shadow-glow mx-auto transform hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-3xl font-bold">point_of_sale</span>
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
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
                        <input type="email" id="emailInput" name="email" value="{{ old('email') }}" required placeholder="email@kasir.com" class="w-full bg-slate-900/90 text-xs text-white rounded-xl pl-10 pr-4 py-3 border border-slate-800 focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                        <input type="password" id="passwordInput" name="password" required placeholder="••••••••" class="w-full bg-slate-900/90 text-xs text-white rounded-xl pl-10 pr-10 py-3 border border-slate-800 focus:outline-none focus:border-brand-500 transition-all">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                            <span class="material-symbols-outlined text-lg" id="pwdEyeIcon">visibility</span>
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
                    <button type="button" onclick="quickFill('admin@kasir.com', 'password123')" class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-left transition-colors group">
                        <div class="flex items-center justify-between text-[11px] font-bold text-white group-hover:text-brand-300">
                            <span>👑 Role Admin</span>
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono">admin@kasir.com</span>
                    </button>

                    <button type="button" onclick="quickFill('kasir@kasir.com', 'password123')" class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-left transition-colors group">
                        <div class="flex items-center justify-between text-[11px] font-bold text-white group-hover:text-emerald-400">
                            <span>🛒 Role Kasir</span>
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono">kasir@kasir.com</span>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-600">
            &copy; 2026 Kinetic POS System. All Rights Reserved.
        </p>
    </div>

    <script>
        function quickFill(email, password) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = password;
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('pwdEyeIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.textContent = 'visibility_off';
            } else {
                pwdInput.type = 'password';
                eyeIcon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
