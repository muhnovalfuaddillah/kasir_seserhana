<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\CashCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Matikan proteksi Foreign Key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Bersihkan seluruh tabel data dummy / seeder operasional
        $tablesToTruncate = [
            'transaction_details',
            'transactions',
            'product_wholesale_prices',
            'product_batches',
            'stock_movements',
            'stock_transfers',
            'products',
            'categories',
            'customers',
            'suppliers',
            'branches',
            'payrolls',
            'cash_transactions',
            'cash_categories',
            'employees',
            'user_shifts',
            'shifts',
            'user_activity_logs',
        ];

        foreach ($tablesToTruncate as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // 2. Hapus seluruh user selain akun Admin utama
        User::where('email', '!=', 'admin@kasir.com')->delete();

        // 3. Pastikan Akun Admin Utama Tersimpan & Aktif
        User::updateOrCreate(
            ['email' => 'admin@kasir.com'],
            [
                'name' => 'Pemilik Toko (Admin)',
                'role' => 'admin',
                'password' => Hash::make('password123'),
                'avatar' => null,
            ]
        );

        // 4. Cabang Utama Dasar (Bersih)
        Branch::create([
            'name' => 'Toko Utama (Pusat)',
            'code' => 'CBG-01',
            'phone' => '021-5550199',
            'address' => 'Jl. Utama No. 1, Jakarta Pusat',
            'is_main' => true,
        ]);

        // 5. Kategori Kas Utama Dasar
        CashCategory::create(['name' => 'Gaji & Tunjangan Karyawan', 'type' => 'out', 'description' => 'Pembayaran gaji pokok, bonus, dan tunjangan']);
        CashCategory::create(['name' => 'Beban Listrik, Air & Internet', 'type' => 'out', 'description' => 'Tagihan utilitas operasional bulanan toko']);
        CashCategory::create(['name' => 'Sewa Tempat & Bangunan', 'type' => 'out', 'description' => 'Beban sewa lokasi toko']);
        CashCategory::create(['name' => 'Perlengkapan & Kebersihan Toko', 'type' => 'out', 'description' => 'Bahan habis pakai operasional toko']);
        CashCategory::create(['name' => 'Pemasaran & Iklan', 'type' => 'out', 'description' => 'Beban promosi, brosur, & iklan media sosial']);
        CashCategory::create(['name' => 'Investasi / Tambahan Modal Owner', 'type' => 'in', 'description' => 'Setoran modal segar dari pemilik toko']);
        CashCategory::create(['name' => 'Pemasukan Lain-Lain', 'type' => 'in', 'description' => 'Pendapatan di luar transaksi kasir']);

        // Aktifkan kembali proteksi Foreign Key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
