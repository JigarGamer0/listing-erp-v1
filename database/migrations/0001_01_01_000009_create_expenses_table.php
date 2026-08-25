<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->enum('type', ['monthly', 'one_time'])->default('one_time');
            $table->text('notes')->nullable();
            $table->string('receipt')->nullable(); // file path
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('expense_date');
            $table->index('category_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
