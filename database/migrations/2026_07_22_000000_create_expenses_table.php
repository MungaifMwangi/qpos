<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->double('amount', 15, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Seed accounting permissions for the Admin role
        $permissions = [
            'expense_view',
            'expense_create',
            'expense_update',
            'expense_delete',
            'profit_loss_view',
        ];

        $adminRole = Role::where('name', 'Admin')->first();

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            if ($adminRole) {
                $adminRole->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete permissions on rollback
        $permissions = [
            'expense_view',
            'expense_create',
            'expense_update',
            'expense_delete',
            'profit_loss_view',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $permission->delete();
            }
        }

        Schema::dropIfExists('expenses');
    }
};
