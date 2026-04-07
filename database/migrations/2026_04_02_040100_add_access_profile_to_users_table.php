<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'access_profile')) {
                $table->string('access_profile')->nullable()->after('role');
            }
        });

        DB::table('users')
            ->where('role', 'pic')
            ->update([
                'role' => 'user',
                'access_profile' => 'assessor',
            ]);

        DB::table('users')
            ->where('role', 'admin')
            ->update(['access_profile' => null]);

        DB::table('users')
            ->where(function ($query) {
                $query->whereNull('role')
                    ->orWhere('role', '')
                    ->orWhere('role', 'user')
                    ->orWhere('role', 'guest');
            })
            ->update([
                'role' => 'user',
                'access_profile' => 'viewer',
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'access_profile')) {
                $table->dropColumn('access_profile');
            }
        });
    }
};
