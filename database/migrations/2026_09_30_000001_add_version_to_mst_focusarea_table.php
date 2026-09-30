<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('mst_focusarea', 'version')) {
            Schema::table('mst_focusarea', function (Blueprint $table) {
                $table->string('version', 20)->default('2019')->after('name');
            });
        }

        // Set default versions for existing records
        DB::table('mst_focusarea')->where('id', 1)->update([
            'code' => 'COREMOD',
            'name' => 'Core Model',
            'version' => '2019',
        ]);

        DB::table('mst_focusarea')->where('id', 3)->update([
            'version' => '2019',
        ]);

        DB::table('mst_focusarea')->where('id', 10)->update([
            'code' => 'COBIT5',
            'name' => 'COBIT 5',
            'version' => '5',
        ]);

        if (!DB::table('mst_focusarea')->where('version', '4.1')->exists()) {
            DB::table('mst_focusarea')->insert([
                'code' => 'COBIT41',
                'name' => 'COBIT 4.1',
                'version' => '4.1',
                'description' => 'COBIT 4.1 Framework - IT Governance & Control Objectives',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mst_focusarea', 'version')) {
            Schema::table('mst_focusarea', function (Blueprint $table) {
                $table->dropColumn('version');
            });
        }
    }
};
