<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * IMPORTANT: In production (client environments), only structural seeders
     * should run. Dummy/demo data seeders must remain commented out.
     * The system update pipeline NEVER runs db:seed — it only runs migrations.
     */
    public function run(): void
    {
        $this->call([
            // Structural — safe for production (uses updateOrCreate / firstOrCreate)
            ChartOfAccountsSeeder::class,

            // ─── DEMO / DUMMY DATA — COMMENT OUT IN PRODUCTION ───
            // StartUpSeeder creates demo users, customers, suppliers.
            // Only run on fresh local installs, NEVER on client databases.
            // StartUpSeeder::class,
            // ProductSeeder::class,
            // CustomerSeeder::class,
            // SupplierSeeder::class,
            // PurchaseSeeder::class,
        ]);
    }
}
