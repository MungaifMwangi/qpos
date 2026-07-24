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
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('credit_limit', 15, 2)->default(0.00)->after('address');
            $table->integer('credit_terms_days')->default(30)->after('credit_limit');
            $table->string('email')->nullable()->after('phone');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'stk_push', 'debtor'])->default('cash')->after('due');
            $table->enum('payment_status', ['pending', 'paid', 'payment_failed'])->default('paid')->after('payment_method');
            $table->string('daraja_checkout_request_id')->nullable()->after('payment_status');
            $table->decimal('vat_amount', 15, 2)->default(0.00)->after('total');
            $table->decimal('net_total', 15, 2)->default(0.00)->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'credit_terms_days', 'email']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'daraja_checkout_request_id', 'vat_amount', 'net_total']);
        });
    }
};
