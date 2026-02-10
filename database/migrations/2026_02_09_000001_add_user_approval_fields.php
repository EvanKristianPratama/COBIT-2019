<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user');
            });
        }

        if (!Schema::hasColumn('users', 'jabatan')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('jabatan')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'organisasi')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('organisasi')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'sso_user_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('sso_user_id')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'isActivated')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('isActivated')->default(true);
            });
        }

        if (!Schema::hasColumn('users', 'approval_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('approval_status')->default('approved');
            });
        }

        // Ensure existing rows are marked approved/active if missing
        if (Schema::hasColumn('users', 'approval_status')) {
            DB::table('users')
                ->whereNull('approval_status')
                ->update(['approval_status' => 'approved']);
        }

        if (Schema::hasColumn('users', 'isActivated')) {
            DB::table('users')
                ->whereNull('isActivated')
                ->update(['isActivated' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
            if (Schema::hasColumn('users', 'isActivated')) {
                $table->dropColumn('isActivated');
            }
            if (Schema::hasColumn('users', 'sso_user_id')) {
                $table->dropColumn('sso_user_id');
            }
            if (Schema::hasColumn('users', 'organisasi')) {
                $table->dropColumn('organisasi');
            }
            if (Schema::hasColumn('users', 'jabatan')) {
                $table->dropColumn('jabatan');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
