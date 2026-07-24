<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique(); // e.g. JE-2026-00001
            $table->date('entry_date');
            $table->string('reference_type')->nullable(); // e.g. Order, Purchase, Expense, Receipt, Payment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('narration');
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('posted');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
