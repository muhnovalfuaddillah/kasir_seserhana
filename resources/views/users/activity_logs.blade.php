@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1700px] mx-auto p-4 sm:p-6">
    
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-5 rounded-3xl border border-slate-800 shadow-xl">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400 text-3xl">shield</span>
                Log Aktivitas User & Audit Trail
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">Rekam jejak histori tindakan seluruh pengguna sistem (Login, Transaksi, Edit Stok, Buka/Tutup Kas, Payroll).</p>
        </div>

        <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center gap-2 border border-slate-700 shadow transition-all">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            <span>Kembali ke Pengguna</span>
        </a>
    </div>

    <!-- Table Activity Logs -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 space-y-4 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold text-slate-400 uppercase bg-slate-900 border-b border-slate-800">
                    <tr>
                        <th class="p-3.5 rounded-l-xl">Waktu Audit</th>
                        <th class="p-3.5">User / Operator</th>
                        <th class="p-3.5 text-center">Aksi / Action</th>
                        <th class="p-3.5">Rincian Deskripsi Audit</th>
                        <th class="p-3.5 rounded-r-xl">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="p-3.5 font-bold text-white">
                                <div>{{ $log->user->name ?? 'System' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $log->user->email ?? '' }}</div>
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black font-mono uppercase bg-slate-800 text-cyan-300 border border-slate-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-200">{{ $log->description }}</td>
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $log->ip_address ?: '127.0.0.1' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">Belum ada catatan log aktivitas sistem.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
