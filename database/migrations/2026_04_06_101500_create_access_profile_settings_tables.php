<?php

use App\Enums\UserAccessProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_access_profiles')) {
            Schema::create('mst_access_profiles', function (Blueprint $table): void {
                $table->bigIncrements('access_profile_id');
                $table->string('access_profile_key')->unique();
                $table->string('access_profile_label');
                $table->boolean('is_system')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('trs_access_profile_permissions')) {
            Schema::create('trs_access_profile_permissions', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('access_profile_id');
                $table->string('permission_name');
                $table->timestamps();

                $table->unique(['access_profile_id', 'permission_name'], 'trs_access_profile_permissions_unique');
                $table->foreign('access_profile_id', 'trs_access_profile_permissions_profile_fk')
                    ->references('access_profile_id')
                    ->on('mst_access_profiles')
                    ->cascadeOnDelete();
            });
        }

        $now = now();

        foreach (UserAccessProfile::cases() as $profile) {
            DB::table('mst_access_profiles')->updateOrInsert(
                ['access_profile_key' => $profile->value],
                [
                    'access_profile_label' => $profile->label(),
                    'is_system' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        foreach (UserAccessProfile::cases() as $profile) {
            $accessProfileId = DB::table('mst_access_profiles')
                ->where('access_profile_key', $profile->value)
                ->value('access_profile_id');

            if (! $accessProfileId) {
                continue;
            }

            foreach ($profile->defaultPermissions() as $permission) {
                DB::table('trs_access_profile_permissions')->updateOrInsert(
                    [
                        'access_profile_id' => $accessProfileId,
                        'permission_name' => $permission,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_access_profile_permissions');
        Schema::dropIfExists('mst_access_profiles');
    }
};
