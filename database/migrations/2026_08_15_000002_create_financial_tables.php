<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create cash_categories table
        Schema::create('cash_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['in', 'out'])->default('out');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Create cash_transactions table
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['in', 'out']);
            $table->foreignId('cash_category_id')->nullable()->constrained('cash_categories')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('cash');
            $table->string('account_name')->default('Kas Utama');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 3. Create employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->default('Staf Kasir');
            $table->string('phone')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 4. Create payrolls table
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('period_month'); // e.g. 2026-08
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('deduction', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('cash_categories');
    }
};
