@extends('layouts.app')

@section('content')
<div class="max-w-[1600px] mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

    <!-- Page Header & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white flex items-center gap-3">
                <span class="material-symbols-outlined text-brand-400 text-3xl">manage_accounts</span>
                Pengaturan Profil Saya
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Kelola informasi akun pribadi, foto profil, serta keamanan kata sandi Anda.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'bg-brand-500/20 text-brand-300 border border-brand-500/40' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' }}">
                Role: {{ $user->role }}
            </span>
        </div>
    </div>

    <!-- Hero Profile Overview Banner Card -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 relative overflow-hidden bg-gradient-to-br from-[#0f172a] via-[#111827] to-[#0b0f19]">
        <!-- Ambient Glowing Background Accents -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 lg:gap-8">
            <!-- Profile Avatar Preview Box -->
            <div class="relative group shrink-0">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden ring-4 ring-slate-800 shadow-2xl bg-slate-900 flex items-center justify-center">
                    <img id="avatarHeroPreview" 
                        src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar)) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop' }}" 
                        alt="{{ $user->name }}" 
                        class="w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-2 -right-2 px-2.5 py-1 rounded-lg bg-emerald-500 text-slate-950 font-black text-[10px] uppercase tracking-wider flex items-center gap-1 shadow-lg border border-emerald-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-950 animate-ping"></span>
                    Aktif
                </div>
            </div>

            <!-- User Bio & Info Header -->
            <div class="flex-1 text-center md:text-left space-y-3">
                <div class="flex flex-col md:flex-row md:items-center gap-2 sm:gap-3">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $user->name }}</h2>
                    <span class="inline-self-center md:inline-self-auto px-3 py-0.5 rounded-full text-xs font-bold border {{ $user->isAdmin() ? 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30' : 'bg-teal-500/20 text-teal-300 border-teal-500/30' }}">
                        {{ $user->isAdmin() ? 'Administrator Sistem' : 'Staf Kasir Operational' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs text-slate-400 font-medium">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-slate-500 text-base">mail</span>
                        {{ $user->email }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-slate-500 text-base">calendar_today</span>
                        Terdaftar sejak {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '10 Ags 2026' }}
                    </span>
                </div>

                <!-- Quick Stats Pills -->
                <div class="pt-3 grid grid-cols-2 sm:grid-cols-3 gap-3 max-w-xl">
                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 text-left">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Transaksi Diproses</span>
                        <span class="text-base sm:text-lg font-black text-emerald-400">{{ number_format($processedTransactionsCount) }} Trx</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 text-left">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Omset Penjualan</span>
                        <span class="text-base sm:text-lg font-black text-cyan-400">Rp {{ number_format($totalSalesAmount, 0, ',', '.') }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 text-left col-span-2 sm:col-span-1">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Keamanan</span>
                        <span class="text-base sm:text-lg font-black text-amber-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            Aman
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Forms Grid: Edit Info & Edit Password -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Form Ubah Informasi Profil (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6 border border-slate-800">
                <div class="pb-4 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-400">person</span>
                            Informasi Akun Utama
                        </h3>
                        <p class="text-xs text-slate-400">Perbarui identitas pribadi dan foto profil Anda</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <!-- Avatar Upload Picker -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-300">Foto Profil (Avatar)</label>
                        <div class="flex items-center gap-4 p-3 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-900 shrink-0 border border-slate-700">
                                <img id="avatarFormPreview" 
                                    src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar)) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop' }}" 
                                    alt="Preview" 
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 space-y-1">
                                <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="previewAvatar(event)" class="block w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white hover:file:bg-brand-500 cursor-pointer">
                                <p class="text-[10px] text-slate-500">Format: JPG, PNG, WEBP. Maksimal ukuran berkas 2MB.</p>
                            </div>
                        </div>
                        @error('avatar')
                            <p class="text-xs font-semibold text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Full Name Field -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-semibold text-slate-300">Nama Lengkap</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-lg">badge</span>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-950 text-sm text-white rounded-xl pl-10 pr-4 py-3 border border-slate-800 focus:outline-none focus:border-brand-500 transition-colors">
                        </div>
                        @error('name')
                            <p class="text-xs font-semibold text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-semibold text-slate-300">Alamat Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-lg">mail</span>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full bg-slate-950 text-sm text-white rounded-xl pl-10 pr-4 py-3 border border-slate-800 focus:outline-none focus:border-brand-500 transition-colors">
                        </div>
                        @error('email')
                            <p class="text-xs font-semibold text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Readonly Role Display -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Role Hak Akses (Read-only)</label>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs font-semibold text-slate-400">
                            <span class="material-symbols-outlined text-brand-400">admin_panel_settings</span>
                            <span>{{ strtoupper($user->role) }} - {{ $user->isAdmin() ? 'Akses Penuh Manajemen Toko' : 'Akses Operasional Terminal Kasir' }}</span>
                        </div>
                    </div>

                    <!-- Submit Profile Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-glow transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">save</span>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Form Ubah Password & Role Privileges (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Ubah Password Form -->
            <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6 border border-slate-800">
                <div class="pb-4 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-400">lock_reset</span>
                        Keamanan & Kata Sandi
                    </h3>
                    <p class="text-xs text-slate-400">Perbarui kata sandi untuk melindungi akun Anda</p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div class="space-y-1.5">
                        <label for="current_password" class="block text-xs font-semibold text-slate-300">Kata Sandi Saat Ini</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-lg">key</span>
                            <input type="password" name="current_password" id="current_password" required placeholder="••••••••" class="w-full bg-slate-950 text-sm text-white rounded-xl pl-10 pr-4 py-2.5 border border-slate-800 focus:outline-none focus:border-amber-500 transition-colors">
                        </div>
                        @error('current_password')
                            <p class="text-xs font-semibold text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-300">Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-lg">lock</span>
                            <input type="password" name="password" id="password" required placeholder="Minimal 6 karakter" class="w-full bg-slate-950 text-sm text-white rounded-xl pl-10 pr-4 py-2.5 border border-slate-800 focus:outline-none focus:border-amber-500 transition-colors">
                        </div>
                        @error('password')
                            <p class="text-xs font-semibold text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300">Konfirmasi Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-lg">check_circle</span>
                            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Ulangi kata sandi baru" class="w-full bg-slate-950 text-sm text-white rounded-xl pl-10 pr-4 py-2.5 border border-slate-800 focus:outline-none focus:border-amber-500 transition-colors">
                        </div>
                    </div>

                    <!-- Password Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-extrabold text-xs shadow-lg transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">shield_lock</span>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Role Privileges Card -->
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-3">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-400 text-base">verified</span>
                    Hak Akses Role ({{ strtoupper($user->role) }})
                </h4>

                <ul class="space-y-2 text-xs text-slate-400 font-medium">
                    @if($user->isAdmin())
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Manajemen Master Produk & Kategori
                        </li>
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Laporan Keuangan & Analisis HPP/Laba Rugi
                        </li>
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Batal / Retur Transaksi Kasir
                        </li>
                        <li class="flex items-center gap-2 text-slate-500">
                            <span class="material-symbols-outlined text-sm">block</span>
                            Terminal POS Kasir (Khusus Role Kasir)
                        </li>
                    @else
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Akses Operasional Terminal Kasir POS
                        </li>
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Scan QR / Barcode Produk Real-Time
                        </li>
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Proses Checkout & Cetak Struk Penjualan
                        </li>
                        <li class="flex items-center gap-2 text-slate-500">
                            <span class="material-symbols-outlined text-sm">block</span>
                            Manajemen Master Produk (Memerlukan Admin)
                        </li>
                    @endif
                </ul>
            </div>

        </div>
    </div>
</div>

<script>
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarFormPreview').src = e.target.result;
                document.getElementById('avatarHeroPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
