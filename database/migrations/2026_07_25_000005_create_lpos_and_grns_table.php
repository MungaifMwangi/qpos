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
        Schema::create('lpos', function (Blueprint $table) {
            $table->id();
            $table->string('lpo_number')->unique(); // e.g. LPO-2026-0001
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->enum('status', ['requisition', 'issued', 'goods_received', 'invoice_matched', 'posted'])->default('requisition');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->timestamp('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('lpo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpo_id')->constrained('lpos')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('qty_ordered');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->timestamps();
        });

        Schema::create('goods_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique(); // e.g. GRN-2026-0001
            $table->foreignId('lpo_id')->constrained('lpos')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('received_date');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_note_id')->constrained('goods_receipt_notes')->onDelete('cascade');
            $table->foreignId('lpo_item_id')->constrained('lpo_items');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('qty_received');
            $table->timestamps();
        });

        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpo_id')->constrained('lpos')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->decimal('invoice_amount', 15, 2);
            $table->enum('status', ['matched', 'discrepancy'])->default('matched');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('goods_receipt_notes');
        Schema::dropIfExists('lpo_items');
        Schema::dropIfExists('lpos');
    }
};
