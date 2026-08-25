<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create investors master table
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add investor_id to investments table and migrate existing data
        Schema::table('investments', function (Blueprint $table) {
            $table->foreignId('investor_id')->nullable()->after('id')->constrained('investors')->nullOnDelete();
        });

        // Migrate existing investor_name data to investors table
        $existingNames = \Illuminate\Support\Facades\DB::table('investments')
            ->select('investor_name')
            ->distinct()
            ->whereNotNull('investor_name')
            ->get();

        foreach ($existingNames as $row) {
            $investorId = \Illuminate\Support\Facades\DB::table('investors')->insertGetId([
                'name' => $row->investor_name,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::table('investments')
                ->where('investor_name', $row->investor_name)
                ->update(['investor_id' => $investorId]);
        }

        // Now drop investor_name column
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn('investor_name');
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->string('investor_name')->nullable()->after('id');
        });

        // Migrate back
        $investments = \Illuminate\Support\Facades\DB::table('investments')
            ->join('investors', 'investments.investor_id', '=', 'investors.id')
            ->select('investments.id', 'investors.name')
            ->get();

        foreach ($investments as $inv) {
            \Illuminate\Support\Facades\DB::table('investments')
                ->where('id', $inv->id)
                ->update(['investor_name' => $inv->name]);
        }

        Schema::table('investments', function (Blueprint $table) {
            $table->dropForeign(['investor_id']);
            $table->dropColumn('investor_id');
        });

        Schema::dropIfExists('investors');
    }
};
