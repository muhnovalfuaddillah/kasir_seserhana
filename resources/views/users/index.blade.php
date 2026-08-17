@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-bold text-xs sm:text-sm flex items-center gap-2 shadow-lg">
            <span class="material-symbols-outlined text-xl">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-300 font-bold text-xs sm:text-sm flex items-center gap-2 shadow-lg">
            <span class="material-symbols-outlined text-xl">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400 text-3xl">manage_accounts</span>
                Manajemen Pengguna & Hak Akses Role
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Kelola akun Super Admin, Manager, dan Kasir beserta kewenangan hak akses masing-masing role.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('users.activity_logs') }}" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-2 border border-slate-700 shadow transition-all">
                <span class="material-symbols-outlined text-lg">shield</span>
                <span>Log Aktivitas User</span>
            </a>
            <button type="button" onclick="openAddUserModal()" class="px-4 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">person_add</span>
                <span>+ Tambah Pengguna Baru</span>
            </button>
        </div>
    </div>

    <!-- Table Users List -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">Nama Pengguna</th>
                        <th class="p-3.5">Email / Username</th>
                        <th class="p-3.5 text-center">Hak Akses Role</th>
                        <th class="p-3.5">Terdaftar Sejak</th>
                        <th class="p-3.5 text-center rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-bold text-white flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-400 font-black flex items-center justify-center border border-brand-500/30 text-xs uppercase">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <span>{{ $u->name }}</span>
                            </td>
                            <td class="p-3.5 font-mono text-slate-300">{{ $u->email }}</td>
                            <td class="p-3.5 text-center">
                                @if($u->role === 'super_admin' || $u->role === 'admin')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-purple-500/20 text-purple-300 border border-purple-500/30">SUPER ADMIN</span>
                                @elseif($u->role === 'manager')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">MANAGER</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">KASIR</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-400 font-mono text-[11px]">{{ $u->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="editUser({{ json_encode($u) }})" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-400" title="Edit User">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus pengguna {{ $u->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-rose-400" title="Hapus User">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">Belum ada pengguna terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal Form Tambah User -->
<div id="addUserModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400">person_add</span>
                Tambah Pengguna Baru
            </h3>
            <button type="button" onclick="closeAddUserModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Nama Lengkap <span class="text-rose-400">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Rian Kasir Utama" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Email / Username Login <span class="text-rose-400">*</span></label>
                <input type="email" name="email" required placeholder="rian@kasir.com" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Hak Akses Role <span class="text-rose-400">*</span></label>
                <select name="role" required class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800">
                    <option value="kasir">Kasir (POS & Shift Buka/Tutup Kas saja)</option>
                    <option value="manager">Manager (Produk, Stok, Laporan & Keuangan)</option>
                    <option value="super_admin">Super Admin (Akses Penuh Seluruh Fitur)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Password Login <span class="text-rose-400">*</span></label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs shadow-glow">SIMPAN PENGGUNA</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form Edit User -->
<div id="editUserModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-400">manage_accounts</span>
                Edit Pengguna
            </h3>
            <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="eu_name" required class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Email / Username Login</label>
                <input type="email" name="email" id="eu_email" required class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Hak Akses Role</label>
                <select name="role" id="eu_role" required class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2.5 border border-slate-800">
                    <option value="kasir">Kasir (POS & Shift Buka/Tutup Kas saja)</option>
                    <option value="manager">Manager (Produk, Stok, Laporan & Keuangan)</option>
                    <option value="super_admin">Super Admin (Akses Penuh Seluruh Fitur)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs shadow-glow">UPDATE PENGGUNA</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
    }
    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.add('hidden');
    }
    function openEditUserModal() {
        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    function editUser(user) {
        document.getElementById('editUserForm').action = "/users/" + user.id;
        document.getElementById('eu_name').value = user.name;
        document.getElementById('eu_email').value = user.email;
        document.getElementById('eu_role').value = user.role;
        openEditUserModal();
    }
</script>
@endsection
