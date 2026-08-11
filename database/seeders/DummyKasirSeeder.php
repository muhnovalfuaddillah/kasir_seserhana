<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DummyKasirSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users & Accounts
        $admin = User::create([
            'name' => 'Pemilik Toko (Admin)',
            'email' => 'admin@kasir.com',
            'role' => 'admin',
            'password' => Hash::make('password123'),
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop',
        ]);

        $kasir = User::create([
            'name' => 'Siti Rahmawati',
            'email' => 'kasir@kasir.com',
            'role' => 'kasir',
            'password' => Hash::make('password123'),
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=256&auto=format&fit=crop',
        ]);

        // 2. Categories
        $categoriesData = [
            ['name' => 'Kopi & Coffee', 'icon' => 'local_cafe'],
            ['name' => 'Non-Coffee & Milk', 'icon' => 'emoji_food_beverage'],
            ['name' => 'Makanan Ringan / Snack', 'icon' => 'cookie'],
            ['name' => 'Makanan Utama', 'icon' => 'flatware'],
            ['name' => 'Bumbu & Bahan Baku', 'icon' => 'kitchen'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['name']] = Category::create([
                'name' => $c['name'],
                'slug' => Str::slug($c['name']),
                'icon' => $c['icon'],
            ]);
        }

        // 3. Products
        $productsData = [
            [
                'category' => 'Kopi & Coffee',
                'barcode' => '8991001001',
                'name' => 'Kopi Arabika Gayo Premium 250g',
                'purchase_price' => 28000,
                'selling_price' => 45000,
                'stock' => 3,
                'unit' => 'pcs',
            ],
            [
                'category' => 'Kopi & Coffee',
                'barcode' => '8991001002',
                'name' => 'Kopi Robusta Temanggung 500g',
                'purchase_price' => 35000,
                'selling_price' => 60000,
                'stock' => 25,
                'unit' => 'pcs',
            ],
            [
                'category' => 'Kopi & Coffee',
                'barcode' => '8991001003',
                'name' => 'Espresso Blend House 1kg',
                'purchase_price' => 95000,
                'selling_price' => 150000,
                'stock' => 12,
                'unit' => 'pack',
            ],

            [
                'category' => 'Non-Coffee & Milk',
                'barcode' => '8992001001',
                'name' => 'Matcha Powder Uji Japan 500g',
                'purchase_price' => 48000,
                'selling_price' => 75000,
                'stock' => 18,
                'unit' => 'pack',
            ],
            [
                'category' => 'Non-Coffee & Milk',
                'barcode' => '8992001002',
                'name' => 'Susu UHT Full Cream 1 Liter',
                'purchase_price' => 14000,
                'selling_price' => 22000,
                'stock' => 5,
                'unit' => 'botol',
            ],
            [
                'category' => 'Non-Coffee & Milk',
                'barcode' => '8992001003',
                'name' => 'Sirup Karamel Premium 700ml',
                'purchase_price' => 90000,
                'selling_price' => 140000,
                'stock' => 8,
                'unit' => 'botol',
            ],

            [
                'category' => 'Makanan Ringan / Snack',
                'barcode' => '8993001001',
                'name' => 'Croissant Butter Original',
                'purchase_price' => 12000,
                'selling_price' => 22000,
                'stock' => 30,
                'unit' => 'pcs',
            ],
            [
                'category' => 'Makanan Ringan / Snack',
                'barcode' => '8993001002',
                'name' => 'Donat Gula Halus (Box isi 6)',
                'purchase_price' => 20000,
                'selling_price' => 35000,
                'stock' => 15,
                'unit' => 'box',
            ],
            [
                'category' => 'Makanan Ringan / Snack',
                'barcode' => '8993001003',
                'name' => 'Kentang Goreng Truffle Cheese',
                'purchase_price' => 15000,
                'selling_price' => 28000,
                'stock' => 40,
                'unit' => 'porsi',
            ],

            [
                'category' => 'Makanan Utama',
                'barcode' => '8994001001',
                'name' => 'Nasi Goreng Wagyu Special',
                'purchase_price' => 25000,
                'selling_price' => 45000,
                'stock' => 50,
                'unit' => 'porsi',
            ],
            [
                'category' => 'Makanan Utama',
                'barcode' => '8994001002',
                'name' => 'Spaghetti Carbonara Creamy',
                'purchase_price' => 22000,
                'selling_price' => 38000,
                'stock' => 25,
                'unit' => 'porsi',
            ],

            [
                'category' => 'Bumbu & Bahan Baku',
                'barcode' => '8995001001',
                'name' => 'Paper Cup Hot 12oz (Pack 50pcs)',
                'purchase_price' => 18000,
                'selling_price' => 32000,
                'stock' => 20,
                'unit' => 'pack',
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $p) {
            $createdProducts[] = Product::create([
                'category_id' => $categories[$p['category']]->id,
                'barcode' => $p['barcode'],
                'name' => $p['name'],
                'purchase_price' => $p['purchase_price'],
                'selling_price' => $p['selling_price'],
                'stock' => $p['stock'],
                'unit' => $p['unit'],
            ]);
        }

        // 4. Sample Transactions
        $paymentMethods = ['cash', 'qris', 'edc'];
        $cashiers = ['Siti Rahmawati', 'Budi Santoso'];

        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trxCount = rand(3, 8);

            for ($j = 1; $j <= $trxCount; $j++) {
                $p1 = $createdProducts[array_rand($createdProducts)];
                $p2 = $createdProducts[array_rand($createdProducts)];
                
                $qty1 = rand(1, 3);
                $qty2 = rand(1, 2);

                $subtotal1 = $p1->selling_price * $qty1;
                $subtotal2 = $p2->selling_price * $qty2;
                $total = $subtotal1 + $subtotal2;

                $method = $paymentMethods[array_rand($paymentMethods)];
                $cashierName = $cashiers[array_rand($cashiers)];

                $trx = Transaction::create([
                    'invoice_number' => 'TRX-' . $date->format('Ymd') . '-' . sprintf('%04d', rand(100, 9999)),
                    'cashier_name' => $cashierName,
                    'customer_name' => 'Pelanggan #' . rand(10, 99),
                    'total_amount' => $total,
                    'discount_amount' => 0,
                    'pay_amount' => $total + ($method === 'cash' ? 10000 : 0),
                    'change_amount' => $method === 'cash' ? 10000 : 0,
                    'payment_method' => $method,
                    'status' => 'completed',
                    'created_at' => $date->setTime(rand(8, 20), rand(0, 59)),
                    'updated_at' => $date,
                ]);

                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $p1->id,
                    'product_name' => $p1->name,
                    'purchase_price' => $p1->purchase_price,
                    'selling_price' => $p1->selling_price,
                    'quantity' => $qty1,
                    'subtotal' => $subtotal1,
                    'created_at' => $date,
                ]);

                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $p2->id,
                    'product_name' => $p2->name,
                    'purchase_price' => $p2->purchase_price,
                    'selling_price' => $p2->selling_price,
                    'quantity' => $qty2,
                    'subtotal' => $subtotal2,
                    'created_at' => $date,
                ]);
            }
        }
    }
}
