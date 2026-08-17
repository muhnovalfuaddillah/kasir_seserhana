<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Summary Shift Kasir - {{ $shift->shift_number }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            color: #000;
            background: #fff;
            margin: 0 auto;
            padding: 5px;
            width: 58mm;
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        .flex { display: flex; justify-content: space-between; }
        .btn-print {
            background: #000;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 10px;
            width: 100%;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; width: 58mm !important; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">🖨️ CETAK STRUK SUMMARY SHIFT [ENTER / P]</button>

    <div class="text-center">
        <h2 style="margin: 0; font-size: 16px;" class="bold">KINETIC POS</h2>
        <div class="bold">LAPORAN SUMMARY SHIFT KASIR</div>
        <div>No. Shift: {{ $shift->shift_number }}</div>
    </div>

    <div class="line"></div>

    <div class="flex">
        <span>Kasir / Operator:</span>
        <span class="bold">{{ $shift->user->name ?? 'Kasir' }}</span>
    </div>
    <div class="flex">
        <span>Waktu Buka:</span>
        <span>{{ $shift->start_time ? $shift->start_time->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '-' }}</span>
    </div>
    <div class="flex">
        <span>Waktu Tutup:</span>
        <span>{{ $shift->end_time ? $shift->end_time->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '-' }}</span>
    </div>

    <div class="line"></div>

    <div class="bold text-center">RINGKASAN TRANSRAKSI POS</div>
    <div class="flex">
        <span>Total Transaksi Nota:</span>
        <span class="bold">{{ $shift->total_transactions }} Nota</span>
    </div>
    <div class="flex">
        <span>Penjualan Tunai (Cash):</span>
        <span>Rp {{ number_format($shift->total_sales_cash, 0, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>Penjualan QRIS:</span>
        <span>Rp {{ number_format($shift->total_sales_qris, 0, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>Penjualan EDC / Debit:</span>
        <span>Rp {{ number_format($shift->total_sales_edc, 0, ',', '.') }}</span>
    </div>

    <div class="line"></div>

    <div class="bold text-center">REKONSILIASI UANG TUNAI DRAWER</div>
    <div class="flex">
        <span>Modal Kas Awal:</span>
        <span>Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>+ Penjualan Cash:</span>
        <span>Rp {{ number_format($shift->total_sales_cash, 0, ',', '.') }}</span>
    </div>
    <div class="flex bold">
        <span>Ekspektasi Uang Tunai:</span>
        <span>Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}</span>
    </div>
    <div class="flex bold">
        <span>Hasil Hitung Fisik:</span>
        <span>Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}</span>
    </div>

    <div class="line"></div>

    <div class="flex bold" style="font-size: 14px;">
        <span>SELISIH KAS:</span>
        <span>{{ $shift->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}</span>
    </div>

    @if($shift->notes)
        <div class="line"></div>
        <div><strong>Catatan:</strong> {{ $shift->notes }}</div>
    @endif

    <div class="line"></div>
    <div class="text-center" style="margin-top: 15px;">
        <div>Terima Kasih Atas Kerja Keras Anda!</div>
        <div style="font-size: 10px; margin-top: 5px;">Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === 'p' || e.key === 'P') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
