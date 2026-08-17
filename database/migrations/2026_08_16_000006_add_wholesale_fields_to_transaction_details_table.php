<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_details', 'normal_price')) {
                $table->decimal('normal_price', 12, 2)->nullable()->after('purchase_price');
            }
            if (!Schema::hasColumn('transaction_details', 'is_wholesale')) {
                $table->boolean('is_wholesale')->default(false)->after('subtotal');
            }
            if (!Schema::hasColumn('transaction_details', 'wholesale_label')) {
                $table->string('wholesale_label')->nullable()->after('is_wholesale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn(['normal_price', 'is_wholesale', 'wholesale_label']);
        });
    }
};
