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

    <!-- Header Page & Tabs -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400 text-3xl">store</span>
                Pengaturan Master Cabang & Gudang Toko
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Kelola daftar lokasi cabang toko, gudang penyimpanan, dan tetapkan Cabang Utama (Pusat).</p>
        </div>

        <button type="button" onclick="openAddBranchModal()" class="px-4 py-2.5 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
            <span class="material-symbols-outlined text-lg">add_location_alt</span>
            <span>+ Tambah Cabang / Gudang Baru</span>
        </button>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('stock.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Ringkasan Stok</span>
        </a>
        <a href="{{ route('stock.transfers') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">sync_alt</span>
            <span>Transfer Cabang</span>
        </a>
        <a href="{{ route('branches.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-cyan-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">store</span>
            <span>Pengaturan Cabang</span>
        </a>
    </div>

    <!-- Branch Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($branches as $b)
            <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-4 shadow-xl relative overflow-hidden">
                @if($b->is_main)
                    <div class="absolute top-0 right-0 px-3 py-1 bg-gradient-to-l from-emerald-500 to-teal-600 text-white text-[10px] font-black uppercase tracking-wider rounded-bl-2xl shadow">
                        Cabang Utama (Pusat)
                    </div>
                @endif

                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-2xl {{ $b->is_main ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' }} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">storefront</span>
                    </div>
                    <div class="min-w-0 flex-1 space-y-0.5">
                        <span class="px-2 py-0.5 rounded bg-slate-800 font-mono text-[10px] text-cyan-300 font-bold border border-slate-700">{{ $b->code }}</span>
                        <h3 class="text-base font-black text-white truncate" title="{{ $b->name }}">{{ $b->name }}</h3>
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">call</span>
                            <span>{{ $b->phone ?: 'Tidak ada no. telp' }}</span>
                        </p>
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-slate-300 space-y-1">
                    <div class="flex items-start gap-1">
                        <span class="material-symbols-outlined text-sm text-slate-400 shrink-0 mt-0.5">location_on</span>
                        <span class="text-slate-300">{{ $b->address ?: 'Alamat belum diatur' }}</span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-800/80">
                    <button type="button" onclick='openEditBranchModal(@json($b))' class="text-xs font-bold text-cyan-400 hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        <span>Edit Cabang</span>
                    </button>

                    @if(!$b->is_main)
                        <form action="{{ route('branches.destroy', $b->id) }}" method="POST" onsubmit="return confirm('Hapus cabang ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-rose-400 hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">delete</span>
                                <span>Hapus</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Form Tambah / Edit Cabang -->
<div id="branchModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 id="modalTitle" class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400">store</span>
                <span>Tambah Cabang / Gudang</span>
            </h3>
            <button type="button" onclick="closeBranchModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="branchForm" action="{{ route('branches.store') }}" method="POST" class="space-y-4">
            @csrf
            <div id="methodField"></div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Nama Cabang / Gudang <span class="text-rose-400">*</span></label>
                <input type="text" name="name" id="branch_name" required placeholder="Contoh: Cabang 3 (Surabaya)" class="w-full bg-slate-950 text-xs sm:text-sm text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-cyan-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Kode Cabang <span class="text-rose-400">*</span></label>
                    <input type="text" name="code" id="branch_code" required placeholder="Contoh: CBG-03" class="w-full bg-slate-950 text-xs sm:text-sm text-white font-mono uppercase rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">No. Telepon / WA</label>
                    <input type="text" name="phone" id="branch_phone" placeholder="0812-3456-7890" class="w-full bg-slate-950 text-xs sm:text-sm text-white rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Alamat Lengkap Cabang</label>
                <textarea name="address" id="branch_address" rows="3" placeholder="Alamat cabang atau lokasi gudang..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800 focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-2xl bg-slate-950 border border-slate-800">
                <input type="checkbox" name="is_main" id="branch_is_main" value="1" class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500">
                <label for="branch_is_main" class="text-xs font-bold text-white select-none">Jadikan sebagai Cabang Utama (Pusat)</label>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeBranchModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs shadow-glow">SIMPAN CABANG</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddBranchModal() {
        document.getElementById('modalTitle').innerHTML = `<span class="material-symbols-outlined text-cyan-400">store</span><span>Tambah Cabang / Gudang Baru</span>`;
        document.getElementById('branchForm').action = "{{ route('branches.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('branch_name').value = '';
        document.getElementById('branch_code').value = '';
        document.getElementById('branch_phone').value = '';
        document.getElementById('branch_address').value = '';
        document.getElementById('branch_is_main').checked = false;
        document.getElementById('branchModal').classList.remove('hidden');
    }

    function openEditBranchModal(branch) {
        document.getElementById('modalTitle').innerHTML = `<span class="material-symbols-outlined text-cyan-400">edit</span><span>Edit Data Cabang #${branch.code}</span>`;
        document.getElementById('branchForm').action = `/branches/${branch.id}`;
        document.getElementById('methodField').innerHTML = `<input type="hidden" name="_method" value="PUT">`;
        document.getElementById('branch_name').value = branch.name || '';
        document.getElementById('branch_code').value = branch.code || '';
        document.getElementById('branch_phone').value = branch.phone || '';
        document.getElementById('branch_address').value = branch.address || '';
        document.getElementById('branch_is_main').checked = branch.is_main == 1;
        document.getElementById('branchModal').classList.remove('hidden');
    }

    function closeBranchModal() {
        document.getElementById('branchModal').classList.add('hidden');
    }
</script>
@endsection
