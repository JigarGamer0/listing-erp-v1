<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Temporarily expand enum options to include 'active'
        DB::statement("ALTER TABLE employee_advances MODIFY COLUMN status ENUM('pending', 'active', 'partially_deducted', 'fully_deducted') NOT NULL DEFAULT 'pending'");

        // 2. Update existing 'pending' rows to 'active'
        DB::statement("UPDATE employee_advances SET status = 'active' WHERE status = 'pending'");

        // 3. Remove 'pending' from the enum list and set default to 'active'
        DB::statement("ALTER TABLE employee_advances MODIFY COLUMN status ENUM('active', 'partially_deducted', 'fully_deducted') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // 1. Temporarily expand enum options to include 'pending'
        DB::statement("ALTER TABLE employee_advances MODIFY COLUMN status ENUM('pending', 'active', 'partially_deducted', 'fully_deducted') NOT NULL DEFAULT 'active'");

        // 2. Update existing 'active' rows to 'pending'
        DB::statement("UPDATE employee_advances SET status = 'pending' WHERE status = 'active'");

        // 3. Remove 'active' from enum list and set default to 'pending'
        DB::statement("ALTER TABLE employee_advances MODIFY COLUMN status ENUM('pending', 'partially_deducted', 'fully_deducted') NOT NULL DEFAULT 'pending'");
    }
};
