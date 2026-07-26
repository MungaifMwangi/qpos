<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            // Accounting & Ledger
            'chart_of_accounts_view',
            'general_ledger_view',
            'trial_balance_view',
            'debtors_view',
            'debtors_receipt_create',
            'creditors_view',
            'creditors_payment_create',

            // LPO Procurement
            'lpo_view',
            'lpo_create',
            'grn_receive',
            'lpo_invoice_match',
        ];

        $adminRole = Role::where('name', 'Admin')->first();

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            if ($adminRole) {
                $adminRole->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        $permissions = [
            'chart_of_accounts_view',
            'general_ledger_view',
            'trial_balance_view',
            'debtors_view',
            'debtors_receipt_create',
            'creditors_view',
            'creditors_payment_create',
            'lpo_view',
            'lpo_create',
            'grn_receive',
            'lpo_invoice_match',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
};
