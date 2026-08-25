<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->date('joining_date');
            $table->string('role_title')->nullable();
            $table->enum('salary_type', ['fixed', 'package_based', 'both'])->default('fixed');
            $table->decimal('fixed_salary', 12, 2)->default(0);
            $table->enum('commission_type', ['fixed_amount', 'percentage'])->default('fixed_amount');
            $table->decimal('commission_value', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('name');
        });

        Schema::create('employee_client_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('assigned_date');
            $table->date('unassigned_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['client_id', 'status']);
        });

        Schema::create('employee_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_cycle_id')->nullable()->constrained('client_billing_cycles')->nullOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('package_amount', 12, 2)->default(0);
            $table->enum('commission_type', ['fixed_amount', 'percentage']);
            $table->decimal('commission_value', 12, 2)->default(0);
            $table->decimal('calculated_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'month', 'year']);
            $table->index(['employee_id', 'status']);
        });

        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('total_commission', 12, 2)->default(0);
            $table->decimal('advance_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('net_payable', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index('status');
        });

        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('advance_date');
            $table->decimal('deducted', 12, 2)->default(0);
            $table->decimal('remaining', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'partially_deducted', 'fully_deducted'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('employee_commissions');
        Schema::dropIfExists('employee_client_assignments');
        Schema::dropIfExists('employees');
    }
};
