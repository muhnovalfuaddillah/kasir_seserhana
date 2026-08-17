@extends('layouts.app')

@section('content')
<script>window.needsJQuery = true;</script>
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
                <span class="material-symbols-outlined text-purple-400 text-3xl">badge</span>
                Manajemen Gaji Karyawan & Payroll
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Proses penggajian bulanan staf toko, tunjangan, potongan, dan otomatis terhubung dengan pencatatan Kas Keluar.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <button type="button" onclick="openEmployeeModal()" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-1.5 border border-slate-700 shadow transition-all">
                <span class="material-symbols-outlined text-lg">person_add</span>
                <span>+ Data Karyawan Baru</span>
            </button>
            <button type="button" onclick="openPayrollModal()" class="px-4 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">payments</span>
                <span>+ Proses Gaji Karyawan</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('financial.index') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Ringkasan Keuangan</span>
        </a>
        <a href="{{ route('financial.cash_in') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">south_east</span>
            <span>Kas Masuk</span>
        </a>
        <a href="{{ route('financial.cash_out') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">north_east</span>
            <span>Kas Keluar</span>
        </a>
        <a href="{{ route('financial.categories') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">category</span>
            <span>Kategori Pengeluaran</span>
        </a>
        <a href="{{ route('financial.payrolls') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-black whitespace-nowrap bg-purple-600 text-white shadow-glow transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">badge</span>
            <span>Gaji Karyawan</span>
        </a>
        <a href="{{ route('financial.cashflow') }}" class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">water_drop</span>
            <span>Laporan Arus Kas</span>
        </a>
    </div>

    <!-- Data Karyawan Cards List -->
    <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400">badge</span>
                Daftar Staf & Karyawan Aktif Toko
            </h3>
            <span class="px-2.5 py-1 rounded-full bg-purple-500/20 text-purple-300 text-xs font-black">{{ $employees->count() }} Orang</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($employees as $emp)
                <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2 flex items-center justify-between">
                    <div>
                        <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px] font-bold border border-purple-500/30 uppercase">{{ $emp->position }}</span>
                        <h4 class="text-sm font-extrabold text-white mt-1">{{ $emp->name }}</h4>
                        <p class="text-xs text-slate-400 font-mono">Gaji Pokok: <strong class="text-emerald-400">Rp {{ number_format($emp->base_salary, 0, ',', '.') }}</strong></p>
                    </div>
                    <button type="button" onclick="quickPay({{ json_encode($emp) }})" class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs shadow flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-sm">payments</span>
                        <span>Bayar Gaji</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Table History Payroll / Riwayat Penggajian -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <h3 class="text-sm font-black text-white flex items-center gap-2 pb-3 border-b border-slate-800">
            <span class="material-symbols-outlined text-purple-400">history</span>
            Riwayat Slip & Penggajian Karyawan
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">No. Slip</th>
                        <th class="p-3.5">Nama Karyawan</th>
                        <th class="p-3.5 text-center">Periode Bulanan</th>
                        <th class="p-3.5 text-right">Gaji Pokok</th>
                        <th class="p-3.5 text-right">Tunjangan (+)</th>
                        <th class="p-3.5 text-right">Potongan (-)</th>
                        <th class="p-3.5 text-right">Gaji Bersih (Net)</th>
                        <th class="p-3.5 text-center rounded-r-xl">Tgl Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($payrolls as $py)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-cyan-300">{{ $py->payroll_number }}</td>
                            <td class="p-3.5 font-bold text-white">
                                <div>{{ $py->employee->name ?? 'Karyawan' }}</div>
                                <div class="text-[10px] text-slate-400 font-normal">{{ $py->employee->position ?? '' }}</div>
                            </td>
                            <td class="p-3.5 text-center font-mono font-bold text-purple-300">{{ $py->period_month }}</td>
                            <td class="p-3.5 text-right font-mono text-slate-300">Rp {{ number_format($py->base_salary, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono text-emerald-400">+Rp {{ number_format($py->allowance, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono text-rose-400">-Rp {{ number_format($py->deduction, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-black font-mono text-purple-300 text-sm">
                                Rp {{ number_format($py->net_salary, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-center font-mono text-slate-400 text-[11px]">{{ $py->payment_date ? $py->payment_date->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">Belum ada riwayat penggajian karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $payrolls->links() }}
        </div>
    </div>
</div>

<!-- Modal Form Prosess Gaji -->
<div id="payrollModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-lg w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400">payments</span>
                Proses Gaji Karyawan
            </h3>
            <button type="button" onclick="closePayrollModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('financial.payrolls.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Pilih Karyawan <span class="text-rose-400">*</span></label>
                <select name="employee_id" id="py_employee_id" required onchange="updateBaseSalary(this)" data-placeholder="-- Cari & Pilih Karyawan --" class="select-searchable w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                    <option value=""></option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}" data-salary="{{ $e->base_salary }}">{{ $e->name }} ({{ $e->position }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Periode Bulan <span class="text-rose-400">*</span></label>
                    <input type="month" name="period_month" required value="{{ date('Y-m') }}" class="w-full bg-slate-950 text-xs font-bold text-white rounded-xl px-3.5 py-2 border border-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Tanggal Bayar <span class="text-rose-400">*</span></label>
                    <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 text-xs font-bold text-white rounded-xl px-3.5 py-2 border border-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Gaji Pokok (Rp)</label>
                    <input type="text" name="base_salary" id="py_base_salary" required data-type="currency" value="0" class="input-currency w-full bg-slate-950 text-xs font-bold text-white rounded-xl px-3 py-2 border border-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Tunjangan (+)</label>
                    <input type="text" name="allowance" data-type="currency" value="0" placeholder="0" class="input-currency w-full bg-slate-950 text-xs font-bold text-emerald-400 rounded-xl px-3 py-2 border border-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Potongan (-)</label>
                    <input type="text" name="deduction" data-type="currency" value="0" placeholder="0" class="input-currency w-full bg-slate-950 text-xs font-bold text-rose-400 rounded-xl px-3 py-2 border border-slate-800">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Metode Pembayaran</label>
                <select name="payment_method" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3 py-2 border border-slate-800">
                    <option value="cash">Tunai (Cash)</option>
                    <option value="bank_transfer">Transfer Bank</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan penggajian..." class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closePayrollModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs shadow-glow">PROSES BAYAR GAJI</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form Tambah Karyawan -->
<div id="employeeModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-card rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-700 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400">person_add</span>
                Tambah Data Karyawan Baru
            </h3>
            <button type="button" onclick="closeEmployeeModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('financial.employees.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Nama Lengkap Karyawan <span class="text-rose-400">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Ahmad Rizky" class="w-full bg-slate-950 text-xs sm:text-sm text-white rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-extrabold text-slate-300 mb-1">Jabatan <span class="text-rose-400">*</span></label>
                    <input type="text" name="position" required placeholder="Kasir / Barista" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">No. HP / WA</label>
                    <input type="text" name="phone" placeholder="0812-xxxx-xxxx" class="w-full bg-slate-950 text-xs text-white rounded-xl px-3.5 py-2 border border-slate-800">
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-300 mb-1">Gaji Pokok Standar (Rp) <span class="text-rose-400">*</span></label>
                <input type="number" name="base_salary" required min="0" placeholder="3000000" class="w-full bg-slate-950 text-xs sm:text-sm font-black text-emerald-400 rounded-xl px-3.5 py-2.5 border border-slate-800">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-800">
                <button type="button" onclick="closeEmployeeModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs shadow-glow">SIMPAN KARYAWAN</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPayrollModal() {
        document.getElementById('payrollModal').classList.remove('hidden');
    }
    function closePayrollModal() {
        document.getElementById('payrollModal').classList.add('hidden');
    }
    function openEmployeeModal() {
        document.getElementById('employeeModal').classList.remove('hidden');
    }
    function closeEmployeeModal() {
        document.getElementById('employeeModal').classList.add('hidden');
    }

    function quickPay(employee) {
        openPayrollModal();
        $('#py_employee_id').val(employee.id).trigger('change');
        document.getElementById('py_base_salary').value = employee.base_salary;
    }

    function updateBaseSalary(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (opt && opt.dataset.salary) {
            document.getElementById('py_base_salary').value = opt.dataset.salary;
        }
    }
</script>
@endsection
