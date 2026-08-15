<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', ['penjualan', 'pengeluaran', 'pembelian', 'bayar_hutang'])->default('penjualan')->after('invoice_number');
            $table->text('description')->nullable()->after('type');
            $table->string('supplier_name')->nullable()->after('customer_name');
            $table->decimal('debt_amount', 15, 2)->default(0)->after('total_amount');
            $table->date('due_date')->nullable()->after('debt_amount');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'supplier_name', 'debt_amount', 'due_date']);
        });
    }
};
