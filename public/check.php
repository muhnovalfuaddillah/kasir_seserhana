<?php
// Script Diagnosa Laravel di Hosting
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Diagnosa Laravel Server</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f6f8; color: #333; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 700px; margin: auto; }
        h2 { margin-top: 0; color: #1e293b; }
        .item { padding: 10px; margin: 8px 0; border-radius: 5px; font-weight: bold; }
        .ok { background: #dcfce7; color: #166534; }
        .fail { background: #fee2e2; color: #991b1b; }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Hasil Diagnosa Server Laravel</h2>
    <?php
    $phpVersion = PHP_VERSION;
    $phpOk = version_compare($phpVersion, '8.2.0', '>=');
    echo "<div class='item ".($phpOk ? 'ok' : 'fail')."'>PHP Version: $phpVersion ".($phpOk ? '✓ (Sesuai)' : '✗ (Minimal PHP 8.2/8.3 dibutuhkan!)')."</div>";

    $vendorPath = __DIR__ . '/../vendor/autoload.php';
    $vendorOk = file_exists($vendorPath);
    echo "<div class='item ".($vendorOk ? 'ok' : 'fail')."'>Folder Vendor: ".($vendorOk ? '✓ Ditemukan' : "✗ Tidak ditemukan di $vendorPath")."</div>";

    $envPath = __DIR__ . '/../.env';
    $envOk = file_exists($envPath);
    echo "<div class='item ".($envOk ? 'ok' : 'fail')."'>File .env: ".($envOk ? '✓ Ditemukan' : "✗ Tidak ditemukan di $envPath")."</div>";

    $storagePath = __DIR__ . '/../storage';
    $storageWritable = is_dir($storagePath) && is_writable($storagePath);
    echo "<div class='item ".($storageWritable ? 'ok' : 'fail')."'>Folder storage writable: ".($storageWritable ? '✓ Bisa ditulis' : '✗ Tidak bisa ditulis (Set Chmod 775/777)')."</div>";

    $bootstrapCache = __DIR__ . '/../bootstrap/cache';
    $bootstrapWritable = is_dir($bootstrapCache) && is_writable($bootstrapCache);
    echo "<div class='item ".($bootstrapWritable ? 'ok' : 'fail')."'>Folder bootstrap/cache writable: ".($bootstrapWritable ? '✓ Bisa ditulis' : '✗ Tidak bisa ditulis (Set Chmod 775/777)')."</div>";

    $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'fileinfo', 'bcmath'];
    $missingExt = [];
    foreach ($extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExt[] = $ext;
        }
    }
    if (empty($missingExt)) {
        echo "<div class='item ok'>Ekstensi PHP: ✓ Lengkap</div>";
    } else {
        echo "<div class='item fail'>Ekstensi PHP Hilang: " . implode(', ', $missingExt) . "</div>";
    }
    ?>
    <p style="margin-top:20px; font-size:14px; color:#64748b;">
        * Hapus file <code>check.php</code> ini jika diagnosa sudah selesai.
    </p>
</div>
</body>
</html>
