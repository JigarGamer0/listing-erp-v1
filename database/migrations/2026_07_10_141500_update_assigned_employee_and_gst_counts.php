<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop foreign key constraint on clients table if it exists
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['assigned_employee_id']);
        });

        // 2. Change column type and reference to employees table
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_employee_id')->nullable()->change();
            $table->foreign('assigned_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 3. Change Flipkart and Meesho GST columns in clients table from decimal to integer
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('current_flipkart_gst')->default(0)->change();
            $table->integer('current_meesho_gst')->default(0)->change();
        });

        // 4. Change old_amount and new_amount columns in client_gst_history table from decimal to integer
        Schema::table('client_gst_history', function (Blueprint $table) {
            $table->integer('old_amount')->default(0)->change();
            $table->integer('new_amount')->default(0)->change();
        });
    }

    public function down(): void
    {
        // Revert columns in client_gst_history table
        Schema::table('client_gst_history', function (Blueprint $table) {
            $table->decimal('old_amount', 12, 2)->change();
            $table->decimal('new_amount', 12, 2)->change();
        });

        // Revert columns in clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('current_flipkart_gst', 12, 2)->default(0)->change();
            $table->decimal('current_meesho_gst', 12, 2)->default(0)->change();
            $table->dropForeign(['assigned_employee_id']);
        });

        // Revert foreign key on clients table to reference users
        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('assigned_employee_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
