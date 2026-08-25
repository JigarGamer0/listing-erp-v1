<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create client_payment_ledger alias/copy table
        if (!Schema::hasTable('client_payment_ledger')) {
            Schema::create('client_payment_ledger', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->date('payment_date');
                $table->string('payment_method');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Create reports table
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('report_type');
                $table->string('file_path')->nullable();
                $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 3. Create activity_logs table
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->nullableMorphs('subject', 'subject');
                $table->nullableMorphs('causer', 'causer');
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_ledger');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('activity_logs');
    }
};
