<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `orders` MODIFY COLUMN `payment_status` "
            . "ENUM('pending', 'paid', 'payment_failed', 'voided') NOT NULL DEFAULT 'paid'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')->where('payment_status', 'voided')->update(['payment_status' => 'paid']);

        DB::statement(
            "ALTER TABLE `orders` MODIFY COLUMN `payment_status` "
            . "ENUM('pending', 'paid', 'payment_failed') NOT NULL DEFAULT 'paid'"
        );
    }
};
