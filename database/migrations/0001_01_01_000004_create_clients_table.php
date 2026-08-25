<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 20);
            $table->string('email')->nullable();
            $table->date('joining_date');
            $table->date('service_start_date');
            $table->decimal('current_package', 12, 2)->default(0);
            $table->decimal('current_flipkart_gst', 12, 2)->default(0);
            $table->decimal('current_meesho_gst', 12, 2)->default(0);
            $table->enum('work_location', ['client_office', 'our_office', 'hybrid'])->default('our_office');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('mobile');
            $table->index('name');
            $table->index('service_start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
