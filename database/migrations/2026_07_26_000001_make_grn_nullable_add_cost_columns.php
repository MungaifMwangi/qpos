<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipt_notes', function (Blueprint $table) {
            $table->foreignId('lpo_id')->nullable()->change();
            $table->foreignId('purchase_id')->nullable()->after('lpo_id')->constrained('purchases')->nullOnDelete();
        });

        Schema::table('grn_items', function (Blueprint $table) {
            $table->foreignId('lpo_item_id')->nullable()->change();
            $table->decimal('unit_cost', 15, 2)->default(0)->after('product_id');
            $table->decimal('line_total', 15, 2)->default(0)->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->change();
            $table->decimal('line_total', 15, 2)->change();
            $table->foreignId('lpo_item_id')->nullable(false)->change();
        });

        Schema::table('goods_receipt_notes', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropColumn('purchase_id');
            $table->foreignId('lpo_id')->nullable(false)->change();
        });
    }
};
