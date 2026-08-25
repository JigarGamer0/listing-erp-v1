<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_package_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_package', 12, 2);
            $table->decimal('new_package', 12, 2);
            $table->date('change_date');
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('client_id');
            $table->index('change_date');
        });

        Schema::create('client_gst_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('gst_type', ['flipkart', 'meesho']);
            $table->decimal('old_amount', 12, 2);
            $table->decimal('new_amount', 12, 2);
            $table->date('change_date');
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'gst_type']);
            $table->index('change_date');
        });

        Schema::create('client_manager_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('old_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('change_date');
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_manager_history');
        Schema::dropIfExists('client_gst_history');
        Schema::dropIfExists('client_package_history');
    }
};
