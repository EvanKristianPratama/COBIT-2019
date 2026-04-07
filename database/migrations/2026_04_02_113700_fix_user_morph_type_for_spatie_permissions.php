<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $userMorphType = (new User())->getMorphClass();

        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->update(['model_type' => $userMorphType]);

        DB::table('model_has_permissions')
            ->where('model_type', User::class)
            ->update(['model_type' => $userMorphType]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $userMorphType = (new User())->getMorphClass();

        DB::table('model_has_roles')
            ->where('model_type', $userMorphType)
            ->update(['model_type' => User::class]);

        DB::table('model_has_permissions')
            ->where('model_type', $userMorphType)
            ->update(['model_type' => User::class]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
