<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_client_assignments', function (Blueprint $table) {
            $table->integer('gst_count')->default(0)->after('commission_value');
            $table->decimal('custom_package_amount', 12, 2)->nullable()->after('gst_count');
        });
    }

    public function down(): void
    {
        Schema::table('employee_client_assignments', function (Blueprint $table) {
            $table->dropColumn(['gst_count', 'custom_package_amount']);
        });
    }
};
