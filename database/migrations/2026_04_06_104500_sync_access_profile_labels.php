<?php

use App\Enums\UserAccessProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_access_profiles')) {
            return;
        }

        foreach (UserAccessProfile::cases() as $profile) {
            DB::table('mst_access_profiles')
                ->where('access_profile_key', $profile->value)
                ->update(['access_profile_label' => $profile->label()]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mst_access_profiles')) {
            return;
        }

        DB::table('mst_access_profiles')
            ->where('access_profile_key', UserAccessProfile::Viewer->value)
            ->update(['access_profile_label' => 'View Only']);
    }
};
