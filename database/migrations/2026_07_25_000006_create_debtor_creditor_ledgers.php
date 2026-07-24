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
        Schema::create('debtor_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('type', ['invoice', 'receipt', 'adjustment']);
            $table->string('reference'); // e.g. INV-000001 or REC-000001
            $table->decimal('amount', 15, 2); // Positive = Debit/Invoice charge, Negative = Credit/Payment receipt
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'transaction_date']);
        });

        Schema::create('creditor_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->unsignedBigInteger('lpo_id')->nullable();
            $table->enum('type', ['invoice', 'payment', 'adjustment']);
            $table->string('reference'); // e.g. SINV-000001 or PV-000001
            $table->decimal('amount', 15, 2); // Positive = Credit/Invoice payable, Negative = Debit/Payment made
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditor_transactions');
        Schema::dropIfExists('debtor_transactions');
    }
};
