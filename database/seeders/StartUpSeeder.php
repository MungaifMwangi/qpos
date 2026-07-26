<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StartUpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * SAFETY: This seeder creates demo data (users, customers, suppliers).
     * It MUST NOT run in production or on client databases.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('SKIPPED: StartUpSeeder does not run in production. Demo data not created.');
            return;
        }

        // Skip if admin user already exists (idempotent guard)
        if (User::where('email', 'demo@qtecsolution.net')->exists()) {
            $this->command?->warn('SKIPPED: StartUpSeeder — demo admin already exists.');
            return;
        }

        $user = User::create([
            'name' => 'Mr Admin',
            'email' => 'demo@qtecsolution.net',
            'password' => bcrypt(87654321),
            'username' => uniqid()
        ]);
        Customer::create([
            'name' => "Walking Customer",
            'phone' => "012345678",
        ]);
        Supplier::create([
            'name' => "Own Supplier",
            'phone' => "012345678",
        ]);
        $role = Role::create(['name' => 'Admin']);
        $user->syncRoles($role);
        $this->call([
            UnitSeeder::class,
            CurrencySeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
