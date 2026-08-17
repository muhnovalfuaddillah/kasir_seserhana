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
                <span class="material-symbols-outlined text-cyan-400 text-3xl">point_of_sale</span>
                Manajemen Shift Kasir & Buka/Tutup Kas
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Atur modal awal kasir (*Cash Drawer*), catat penutupan shift, hitung fisik uang tunai, dan selisih kas.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <button type="button" onclick="exportReportToExcel('shiftReportTable', 'Laporan_Shift_Kasir.csv')" class="px-3.5 py-2.5 rounded-2xl bg-cyan-600/20 hover:bg-cyan-600 text-cyan-300 hover:text-white border border-cyan-500/30 font-extrabold text-xs sm:text-sm flex items-center gap-1.5 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Download Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-3.5 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 font-extrabold text-xs sm:text-sm flex items-center gap-1.5 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">print</span>
                <span>Cetak PDF</span>
            </button>

            @if($activeShift)
                <div class="px-4 py-2 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-black flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>SHIFT AKTIF: {{ $activeShift->shift_number }} (Modal: Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }})</span>
                </div>
                <button type="button" onclick="openCloseShiftModal()" class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                    <span class="material-symbols-outlined text-lg">lock</span>
                    <span>🔴 TUTUP KASIR (CLOSE SHIFT)</span>
                </button>
            @else
                <button type="button" onclick="openOpenShiftModal()" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                    <span class="material-symbols-outlined text-lg">key</span>
                    <span>🟢 BUKA KASIR (OPEN SHIFT)</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Table Shifts History -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-cyan-400">history</span>
            Riwayat Shift Buka / Tutup Kasir
        </h3>

        <div class="overflow-x-auto">
            <table id="shiftReportTable" class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Shift</th>
                        <th class="p-3.5">Kasir / Operator</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5">Waktu Buka / Tutup</th>
                        <th class="p-3.5 text-right">Modal Awal</th>
                        <th class="p-3.5 text-right">Penjualan Cash</th>
                        <th class="p-3.5 text-right">Ekspektasi Kas</th>
                        <th class="p-3.5 text-right">Fisik Kas Hitung</th>
                        <th class="p-3.5 text-right">Selisih</th>
                        <th class="p-3.5 text-center rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($shifts as $s)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-cyan-300">{{ $s->shift_number }}</td>
                            <td class="p-3.5 font-bold text-white">{{ $s->user->name ?? 'Kasir' }}</td>
                            <td class="p-3.5 text-center">
                                @if($s->status === 'open')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">AKTIF BUKA</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-800 text-slate-400 border border-slate-700">DITUTUP</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">
                                <div>Buka: {{ $s->start_time ? $s->start_time->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '-' }}</div>
                                <div class="text-[10px] text-slate-500">Tutup: {{ $s->end_time ? $s->end_time->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '-' }}</div>
                            </td>
                            <td class="p-3.5 text-right font-mono text-white">Rp {{ number_format($s->starting_cash, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono text-emerald-400">+Rp {{ number_format($s->total_sales_cash, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono text-cyan-300">Rp {{ number_format($s->expected_cash, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono font-bold text-white">Rp {{ number_format($s->actual_cash, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono font-black {{ $s->cash_difference == 0 ? 'text-slate-400' : ($s->cash_difference > 0 ? 'text-emerald-400' : 'text-rose-400') }}">
                                {{ $s->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($s->cash_difference, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('shifts.print', $s->id) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 font-bold text-xs inline-flex items-center gap-1 border border-slate-700" title="Cetak Struk Shift">
                                        <span class="material-symbols-outlined text-sm">print</span>
                                        <span>Struk</span>
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                        @if($s->status === 'open')
                                            <button type="button" onclick="openAdminForceCloseModal({{ json_encode($s) }})" class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 border border-rose-500/30 font-bold text-xs inline-flex items-center gap-1" title="Tutup Shift Kasir Ini">
                                                <span class="material-symbols-outlined text-sm">lock</span>
                                                <span>Tutup</span>
                                            </button>
                                        @endif

                                        <button type="button" onclick="openAdminEditShiftModal({{ json_encode($s) }})" class="px-2.5 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 border border-amber-500/30 font-bold text-xs inline-flex items-center gap-1" title="Edit Data Shift Kasir">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                            <span>Edit</span>
                                        </button>

                                        <form action="{{ route('shifts.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data shift kasir ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-950/60 text-rose-400 hover:bg-rose-900 border border-rose-800 font-bold text-xs inline-flex items-center gap-1" title="Hapus Data Shift">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-8 text-center text-slate-500">Belum ada riwayat shift kasir.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $shifts->links() }}
        </div>
    </div>
</div>

<!-- Modal Form Buka Shift Kasir -->
<div id="openShiftModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-400">key</span>
                Buka Shift Kasir Baru (Open Shift)
            </h3>
            <button type="button" onclick="closeOpenShiftModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('shifts.open') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Modal Kas Awal Kasir (Rp) <span class="text-rose-400">*</span></label>
                <input type="text" name="starting_cash" required data-type="currency" value="200.000" placeholder="200.000" class="input-currency w-full bg-slate-950 text-sm font-black text-emerald-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-emerald-500">
                <p class="text-[11px] text-slate-400 mt-1">Uang pecahan/kembalian awal di laci kasir (*Cash Drawer*).</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Buka Shift</label>
                <textarea name="notes" rows="2" placeholder="Catatan opsional..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeOpenShiftModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-glow">🟢 BUKA KASIR SEKARANG</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form Tutup Shift Kasir -->
<div id="closeShiftModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-400">lock</span>
                Tutup Shift Kasir (Close Shift & Hitung Uang)
            </h3>
            <button type="button" onclick="closeCloseShiftModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('shifts.close') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Hasil Hitung Fisik Uang Tunai di Drawer (Rp) <span class="text-rose-400">*</span></label>
                <input type="text" name="actual_cash" required data-type="currency" placeholder="Contoh: 350.000" class="input-currency w-full bg-slate-950 text-sm font-black text-rose-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-rose-500">
                <p class="text-[11px] text-slate-400 mt-1">Hitung seluruh uang fisik kertas & koin di laci kasir saat penutupan.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Penutupan / Keterangan Selisih</label>
                <textarea name="notes" rows="2" placeholder="Catatan selisih kas atau penyerahan kas ke owner..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeCloseShiftModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs shadow-glow">🔴 TUTUP KASIR & HITUNG REKONSILIASI</button>
            </div>
        </form>
    </div>
</div>

<!-- Admin Force Close Modal -->
<div id="adminForceCloseModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-400">lock</span>
                Penutupan Paksa Shift (Admin)
            </h3>
            <button type="button" onclick="closeAdminForceCloseModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="adminForceCloseForm" method="POST" class="space-y-4">
            @csrf
            
            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-400">No. Shift:</span>
                    <span id="fc_shift_number" class="font-bold text-cyan-300 font-mono"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Operator Kasir:</span>
                    <span id="fc_shift_user" class="font-bold text-white"></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Hasil Hitung Fisik Kas (Rp) <span class="text-rose-400">*</span></label>
                <input type="text" name="actual_cash" required data-type="currency" placeholder="0" class="input-currency w-full bg-slate-950 text-sm font-black text-rose-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Penutupan Admin</label>
                <textarea name="notes" rows="2" placeholder="Alasan penutupan paksa..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeAdminForceCloseModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs shadow-glow">🔴 TUTUP SHIFT KASIR</button>
            </div>
        </form>
    </div>
</div>

<!-- Admin Edit Shift Modal -->
<div id="adminEditShiftModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-400">edit</span>
                Edit Data Shift Kasir (Admin)
            </h3>
            <button type="button" onclick="closeAdminEditShiftModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="adminEditShiftForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-400">No. Shift:</span>
                    <span id="es_shift_number" class="font-bold text-cyan-300 font-mono"></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Modal Kas Awal (Rp) <span class="text-rose-400">*</span></label>
                <input type="text" id="es_starting_cash" name="starting_cash" required data-type="currency" class="input-currency w-full bg-slate-950 text-sm font-black text-emerald-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div id="es_actual_cash_group">
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Fisik Kas Hitung Akhir (Rp)</label>
                <input type="text" id="es_actual_cash" name="actual_cash" data-type="currency" class="input-currency w-full bg-slate-950 text-sm font-black text-rose-400 rounded-xl px-3.5 py-2.5 border border-slate-800 focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Catatan Shift</label>
                <textarea id="es_notes" name="notes" rows="2" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeAdminEditShiftModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-extrabold text-xs shadow-glow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openOpenShiftModal() {
        document.getElementById('openShiftModal').classList.remove('hidden');
    }
    function closeOpenShiftModal() {
        document.getElementById('openShiftModal').classList.add('hidden');
    }
    function openCloseShiftModal() {
        document.getElementById('closeShiftModal').classList.remove('hidden');
    }
    function closeCloseShiftModal() {
        document.getElementById('closeShiftModal').classList.add('hidden');
    }

    function openAdminForceCloseModal(shift) {
        document.getElementById('adminForceCloseForm').action = "/shifts/" + shift.id + "/force-close";
        document.getElementById('fc_shift_number').textContent = shift.shift_number;
        document.getElementById('fc_shift_user').textContent = shift.user ? shift.user.name : 'Kasir';
        document.getElementById('adminForceCloseModal').classList.remove('hidden');
    }
    function closeAdminForceCloseModal() {
        document.getElementById('adminForceCloseModal').classList.add('hidden');
    }

    function openAdminEditShiftModal(shift) {
        document.getElementById('adminEditShiftForm').action = "/shifts/" + shift.id;
        document.getElementById('es_shift_number').textContent = shift.shift_number;
        document.getElementById('es_starting_cash').value = formatRupiah(shift.starting_cash);
        
        const actualInput = document.getElementById('es_actual_cash');
        if (actualInput) {
            actualInput.value = shift.actual_cash ? formatRupiah(shift.actual_cash) : '';
        }
        document.getElementById('es_notes').value = shift.notes || '';
        document.getElementById('adminEditShiftModal').classList.remove('hidden');
    }
    function closeAdminEditShiftModal() {
        document.getElementById('adminEditShiftModal').classList.add('hidden');
    }
</script>
@endsection
