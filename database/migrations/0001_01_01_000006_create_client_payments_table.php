<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('billing_start');
            $table->date('billing_end');
            $table->decimal('package_amount', 12, 2)->default(0);
            $table->decimal('flipkart_gst', 12, 2)->default(0);
            $table->decimal('meesho_gst', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0); // positive = due, negative = advance
            $table->enum('status', ['pending', 'partial', 'paid', 'advance', 'overdue'])->default('pending');
            $table->timestamps();

            $table->index(['client_id', 'billing_start']);
            $table->index('status');
            $table->index('billing_end');
        });

        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_cycle_id')->nullable()->constrained('client_billing_cycles')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'upi', 'cheque', 'other'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'payment_date']);
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payments');
        Schema::dropIfExists('client_billing_cycles');
    }
};
