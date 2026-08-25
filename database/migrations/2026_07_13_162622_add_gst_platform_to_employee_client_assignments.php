<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_client_assignments', function (Blueprint $table) {
            $table->string('gst_platform')->nullable()->after('gst_count'); // 'flipkart', 'meesho', or null
        });
    }

    public function down(): void
    {
        Schema::table('employee_client_assignments', function (Blueprint $table) {
            $table->dropColumn('gst_platform');
        });
    }
};
