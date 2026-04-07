<?php

use App\Models\User;
use App\Enums\UserRole;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guardName = 'web';
        $now = now();
        $userMorphType = (new User())->getMorphClass();

        foreach (PermissionCatalog::all() as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => $guardName],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach (UserRole::values() as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role, 'guard_name' => $guardName],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $adminRoleId = DB::table('roles')->where('name', UserRole::Admin->value)->value('id');
        $userRoleId = DB::table('roles')->where('name', UserRole::User->value)->value('id');

        foreach (PermissionCatalog::all() as $permission) {
            $permissionId = DB::table('permissions')->where('name', $permission)->value('id');

            if ($permissionId && $adminRoleId) {
                DB::table('role_has_permissions')->updateOrInsert(
                    ['permission_id' => $permissionId, 'role_id' => $adminRoleId],
                    []
                );
            }
        }

        DB::table('model_has_roles')->where('model_type', $userMorphType)->delete();

        DB::table('users')->select(['id', 'role'])->orderBy('id')->get()->each(function ($user) use ($adminRoleId, $userMorphType, $userRoleId) {
            $roleId = $user->role === UserRole::Admin->value ? $adminRoleId : $userRoleId;

            if (! $roleId) {
                return;
            }

            DB::table('model_has_roles')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'model_type' => $userMorphType,
                    'model_id' => $user->id,
                ],
                []
            );
        });
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $userMorphType = (new User())->getMorphClass();

        DB::table('role_has_permissions')->whereIn('role_id', function ($query) {
            $query->select('id')
                ->from('roles')
                ->whereIn('name', UserRole::values());
        })->delete();

        DB::table('model_has_roles')->where('model_type', $userMorphType)->delete();

        DB::table('roles')->whereIn('name', UserRole::values())->delete();
        DB::table('permissions')->whereIn('name', PermissionCatalog::all())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
