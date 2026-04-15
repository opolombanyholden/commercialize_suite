<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::where('name', 'company_admin')->first();
        if (!$role) {
            return;
        }

        $perms = Permission::where('name', 'like', 'roles.%')->get();
        foreach ($perms as $perm) {
            if (!$role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        $role = Role::where('name', 'company_admin')->first();
        if (!$role) {
            return;
        }

        // Restore the original (limited) set
        foreach (['roles.create', 'roles.edit', 'roles.delete'] as $name) {
            $perm = Permission::where('name', $name)->first();
            if ($perm && $role->hasPermissionTo($perm)) {
                $role->revokePermissionTo($perm);
            }
        }
    }
};
