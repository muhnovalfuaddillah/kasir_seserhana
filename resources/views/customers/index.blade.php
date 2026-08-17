@extends('layouts.app')

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 glass-card p-6 rounded-2xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs font-bold text-indigo-400 uppercase tracking-widest">
                <span class="material-symbols-outlined text-sm">groups</span>
                <span>Manajemen Pelanggan POS</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Master Data Pelanggan & Limit Kasbon</h1>
            <p class="text-xs sm:text-sm text-slate-400">Kelola database pelanggan tetap, tentukan batas limit kasbon, dan pantau total tunggakan piutang toko.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openCreateModal()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs shadow-glow transition-all active:scale-95">
                <span class="material-symbols-outlined text-lg">person_add</span>
                <span>+ Tambah Pelanggan</span>
            </button>
        </div>
    </div>

    <!-- KPI Metrics Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Pelanggan -->
        <div class="glass-card p-5 rounded-2xl flex items-center justify-between border-l-4 border-indigo-500">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pelanggan Terdaftar</p>
                <h3 class="text-2xl font-black text-white mt-1">{{ number_format($totalCustomers) }} <span class="text-xs font-medium text-slate-400">Orang</span></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">group</span>
            </div>
        </div>

        <!-- Total Piutang Belum Lunas -->
        <div class="glass-card p-5 rounded-2xl flex items-center justify-between border-l-4 border-amber-500">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Tunggakan / Piutang Toko</p>
                <h3 class="text-2xl font-black text-amber-400 mt-1">Rp {{ number_format($totalDebt, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
            </div>
        </div>

        <!-- Pelanggan Over Limit -->
        <div class="glass-card p-5 rounded-2xl flex items-center justify-between border-l-4 border-rose-500">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pelanggan Over-Limit</p>
                <h3 class="text-2xl font-black text-rose-400 mt-1">{{ number_format($overLimitCount) }} <span class="text-xs font-medium text-slate-400">Orang</span></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">warning</span>
            </div>
        </div>
    </div>

    <!-- Table Section & Search Filter -->
    <div class="glass-card rounded-2xl overflow-hidden">
        
        <!-- Filter Header -->
        <div class="p-5 border-b border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form action="{{ route('customers.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pelanggan, kode, atau telepon..." class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-xs font-medium text-white placeholder-slate-500 focus:outline-none focus:border-brand-500">
                </div>
                <select name="status" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 focus:outline-none focus:border-brand-500">
                    <option value="">-- Semua Status --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-200 hover:text-white text-xs font-bold border border-slate-700 transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table List -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Kode & Nama Pelanggan</th>
                        <th class="px-6 py-4">No. HP / Telepon</th>
                        <th class="px-6 py-4 text-right">Limit Kasbon</th>
                        <th class="px-6 py-4 text-right">Hutang Berjalan</th>
                        <th class="px-6 py-4 text-right">Sisa Limit</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($customers as $c)
                        @php
                            $isOver = $c->credit_limit > 0 && $c->current_debt > $c->credit_limit;
                            $sisaLimit = $c->credit_limit > 0 ? max(0, $c->credit_limit - $c->current_debt) : null;
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-300 font-bold flex items-center justify-center text-sm uppercase">
                                        {{ substr($c->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('customers.show', $c) }}" class="font-extrabold text-white hover:text-brand-300 transition-colors block text-sm">
                                            {{ $c->name }}
                                        </a>
                                        <span class="text-[10px] text-slate-400 font-mono tracking-wider">{{ $c->code }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-300">
                                {{ $c->phone ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-white">
                                @if($c->credit_limit > 0)
                                    Rp {{ number_format($c->credit_limit, 0, ',', '.') }}
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-400">Unlimited</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-extrabold text-amber-400">
                                @if($c->current_debt > 0)
                                    Rp {{ number_format($c->current_debt, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-500 font-normal">Rp 0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold">
                                @if($c->credit_limit > 0)
                                    @if($isOver)
                                        <span class="text-rose-400 flex items-center justify-end gap-1 font-bold">
                                            <span class="material-symbols-outlined text-sm">warning</span> Over-Limit
                                        </span>
                                    @else
                                        <span class="text-emerald-400">Rp {{ number_format($sisaLimit, 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-500 font-normal">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $c->status === 'active' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                    {{ $c->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('customers.show', $c) }}" class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 transition-colors" title="Lihat Detail & Histori Piutang">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                    </a>
                                    <button onclick="openEditModal({{ json_encode($c) }})" class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 transition-colors" title="Edit Data Pelanggan">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <form action="{{ route('customers.destroy', $c) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-colors" title="Hapus Pelanggan">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2 text-slate-600">person_off</span>
                                <p class="font-medium text-sm">Belum ada data pelanggan yang terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Pelanggan -->
<div id="createModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl border border-slate-800">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-900/60">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-400">person_add</span>
                <h3 class="font-extrabold text-white text-base">Tambah Pelanggan Baru</h3>
            </div>
            <button onclick="closeCreateModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('customers.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap Pelanggan *</label>
                <input type="text" name="name" required placeholder="Contoh: Pak Budi Santoso" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-medium text-white placeholder-slate-600 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">No. HP / WhatsApp</label>
                <input type="text" name="phone" placeholder="Contoh: 08123456789" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-medium text-white placeholder-slate-600 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Tipe Pelanggan</label>
                <select name="type" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-white focus:outline-none focus:border-brand-500">
                    <option value="retail">Eceran (Umum)</option>
                    <option value="wholesale">Grosir / Reseller Toko</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Batas Limit Kasbon / Piutang (Rp) *</label>
                <input type="text" name="credit_limit" required value="0" placeholder="0" class="input-currency w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-bold text-amber-400 placeholder-slate-600 focus:outline-none focus:border-amber-500">
                <p class="text-[10px] text-slate-400 mt-1">* Isi 0 jika tidak ada batas limit kasbon (Unlimited).</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Alamat / Catatan Lengkap</label>
                <textarea name="address" rows="2" placeholder="Alamat rumah atau catatan toko pelanggan..." class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-medium text-white placeholder-slate-600 focus:outline-none focus:border-brand-500"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs shadow-glow transition-all">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pelanggan -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl border border-slate-800">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-900/60">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-400">edit</span>
                <h3 class="font-extrabold text-white text-base">Edit Data Pelanggan</h3>
            </div>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap Pelanggan *</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">No. HP / WhatsApp</label>
                <input type="text" id="edit_phone" name="phone" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Tipe Pelanggan</label>
                <select id="edit_type" name="type" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-white focus:outline-none focus:border-brand-500">
                    <option value="retail">Eceran (Umum)</option>
                    <option value="wholesale">Grosir / Reseller Toko</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Batas Limit Kasbon / Piutang (Rp) *</label>
                <input type="text" id="edit_credit_limit" name="credit_limit" required class="input-currency w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-bold text-amber-400 focus:outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Status Pelanggan</label>
                <select id="edit_status" name="status" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-white focus:outline-none focus:border-brand-500">
                    <option value="active">Aktif</option>
                    <option value="inactive">Non-Aktif</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Alamat / Catatan Lengkap</label>
                <textarea id="edit_address" name="address" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-brand-500"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs shadow-glow transition-all">Perbarui Pelanggan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }
    function openEditModal(customer) {
        document.getElementById('editForm').action = "/customers/" + customer.id;
        document.getElementById('edit_name').value = customer.name;
        document.getElementById('edit_phone').value = customer.phone || '';
        document.getElementById('edit_type').value = customer.type || 'retail';
        document.getElementById('edit_credit_limit').value = formatRupiah(customer.credit_limit);
        document.getElementById('edit_status').value = customer.status;
        document.getElementById('edit_address').value = customer.address || '';
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endsection
