<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan & Keuntungan - Kinetic POS</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Compiled CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: #ffffff !important; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 sm:p-8 min-h-screen">

    <!-- Floating Top Action Bar for Screen View -->
    <div class="no-print max-w-5xl mx-auto mb-6 p-4 rounded-2xl bg-slate-900 text-white flex items-center justify-between shadow-xl">
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-xs">Print View PDF</span>
            <p class="text-xs text-slate-300">Gunakan dialog cetak browser untuk menyimpan sebagai PDF atau mencetak ke kertas.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg">
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 rounded-xl bg-slate-800 text-slate-300 font-bold text-xs hover:bg-slate-700">
                Tutup
            </button>
        </div>
    </div>

    <!-- Printable Paper Container -->
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-lg border border-slate-200 space-y-6">
        
        <!-- Official KOP Header -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div class="space-y-1">
                <h1 class="text-2xl font-black tracking-wider text-slate-900 uppercase">KINETIC POS STORE</h1>
                <p class="text-xs font-semibold text-slate-600">Jl. Sudirman No. 88, Jakarta Pusat • Telp: (021) 555-0199</p>
                <p class="text-xs text-slate-500">Email: support@kineticpos.com • Website: www.kineticpos.com</p>
            </div>
            <div class="text-right space-y-1">
                <span class="px-3 py-1 rounded-lg bg-slate-900 text-white text-xs font-black uppercase tracking-widest inline-block">
                    LAPORAN FINANSIAL
                </span>
                <p class="text-xs font-bold text-slate-700 mt-1">Periode Laporan:</p>
                <p class="text-xs font-black text-slate-900 bg-slate-100 px-2.5 py-1 rounded border border-slate-300 inline-block">
                    {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM Y') }}
                </p>
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-300 space-y-1">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Omset</span>
                <p class="text-lg font-black text-slate-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-600">{{ $transactionCount }} Transaksi Lunas</p>
            </div>

            <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 space-y-1">
                <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Total Modal (HPP)</span>
                <p class="text-lg font-black text-amber-900">Rp {{ number_format($totalCogs, 0, ',', '.') }}</p>
                <p class="text-[10px] text-amber-700">Harga Beli Barang</p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-400 space-y-1">
                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Laba Bersih (Profit)</span>
                <p class="text-lg font-black text-emerald-900">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
                <p class="text-[10px] text-emerald-700">Omset - Modal HPP</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-300 space-y-1">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Diskon</span>
                <p class="text-lg font-black text-slate-900">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-600">Diskon Pelanggan</p>
            </div>
        </div>

        <!-- Payment Channels Breakdown -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pemasukan Per Metode Pembayaran</h3>
            <div class="grid grid-cols-3 gap-4 text-xs font-semibold">
                <div class="flex justify-between p-2.5 rounded bg-white border border-slate-200">
                    <span class="text-slate-600">Uang Tunai (Cash):</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($cashTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between p-2.5 rounded bg-white border border-slate-200">
                    <span class="text-slate-600">QRIS (Digital):</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($qrisTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between p-2.5 rounded bg-white border border-slate-200">
                    <span class="text-slate-600">EDC / Debit:</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($edcTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Rincian Pemasukan Harian Table -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b pb-1">1. Rincian Pemasukan Harian</h3>
            <table class="w-full text-left text-xs border border-slate-300">
                <thead class="bg-slate-900 text-white font-bold text-[11px] uppercase">
                    <tr>
                        <th class="p-2.5 border-r border-slate-700">Tanggal</th>
                        <th class="p-2.5 border-r border-slate-700">Jumlah Transaksi</th>
                        <th class="p-2.5 border-r border-slate-700">Rata-rata / Trx</th>
                        <th class="p-2.5 text-right">Total Omset Harian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse($dailyReports as $day)
                        <tr class="hover:bg-slate-50">
                            <td class="p-2.5 font-bold border-r">{{ \Carbon\Carbon::parse($day->date)->isoFormat('D MMMM Y') }}</td>
                            <td class="p-2.5 border-r">{{ $day->count }} transaksi</td>
                            <td class="p-2.5 text-slate-600 border-r">Rp {{ number_format($day->count > 0 ? $day->revenue / $day->count : 0, 0, ',', '.') }}</td>
                            <td class="p-2.5 text-right font-extrabold text-slate-900">Rp {{ number_format($day->revenue, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-500">Tidak ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rincian Seluruh Transaksi Table -->
        <div class="space-y-2 pt-2">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b pb-1">2. Daftar Seluruh Transaksi</h3>
            <table class="w-full text-left text-[11px] border border-slate-300">
                <thead class="bg-slate-800 text-white font-bold uppercase">
                    <tr>
                        <th class="p-2 border-r border-slate-700">No Invoice</th>
                        <th class="p-2 border-r border-slate-700">Waktu</th>
                        <th class="p-2 border-r border-slate-700">Kasir</th>
                        <th class="p-2 border-r border-slate-700">Metode</th>
                        <th class="p-2 border-r border-slate-700 text-right">Total Tagihan</th>
                        <th class="p-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse($transactions as $trx)
                        <tr>
                            <td class="p-2 font-bold border-r">#{{ $trx->invoice_number }}</td>
                            <td class="p-2 text-slate-600 border-r">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-2 border-r">{{ $trx->cashier_name }}</td>
                            <td class="p-2 border-r uppercase">{{ $trx->payment_method }}</td>
                            <td class="p-2 text-right font-bold border-r">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                            <td class="p-2 text-center uppercase font-bold {{ $trx->status === 'completed' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $trx->status === 'completed' ? 'LUNAS' : 'BATAL' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-slate-500">Tidak ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Signatures & Report Footer -->
        <div class="pt-8 border-t border-slate-300 flex justify-between items-end text-xs">
            <div class="space-y-1 text-slate-500">
                <p>Dicetak secara otomatis oleh sistem <strong>Kinetic POS</strong></p>
                <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
            </div>
            <div class="text-center space-y-12 pr-6">
                <p class="font-semibold text-slate-700">Penanggung Jawab / Admin,</p>
                <p class="font-bold underline text-slate-900">( {{ auth()->user()->name }} )</p>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
