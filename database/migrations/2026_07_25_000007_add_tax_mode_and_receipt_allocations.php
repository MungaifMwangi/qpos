<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'tax_mode')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('tax_mode', ['inclusive', 'exclusive'])->default('inclusive')->after('net_total');
            });
        }

        if (!Schema::hasTable('debtor_receipt_allocations')) {
            Schema::create('debtor_receipt_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_transaction_id')->constrained('debtor_transactions')->cascadeOnDelete();
            $table->foreignId('invoice_transaction_id')->constrained('debtor_transactions')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['receipt_transaction_id', 'invoice_transaction_id'], 'debtor_receipt_invoice_unique');
            });
        } else {
            Schema::table('debtor_receipt_allocations', function (Blueprint $table) {
                $table->unique(['receipt_transaction_id', 'invoice_transaction_id'], 'debtor_receipt_invoice_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('debtor_receipt_allocations');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('tax_mode');
        });
    }
};
