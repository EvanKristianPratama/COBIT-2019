<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel child yang perlu ditambah focus_area_id:
     * - mst_practice
     * - mst_policy
     * - mst_skill
     * - mst_keyculture
     * - mst_SIA
     * - trs_domain (pivot objective-area)
     * - trs_entergoals (pivot objective-entergoals)
     * - trs_aligngoals (pivot objective-aligngoals)
     * - mst_eval (hasMany objective)
     * - trs_roadmap (hasMany objective)
     * - trs_objectiveguidance (pivot)
     */
    public function up(): void
    {
        // Child tables yang langsung FK ke mst_objective.objective_id
        $childTables = [
            'mst_practice' => 'objective_id',
            'mst_policy' => 'objective_id', 
            'mst_skill' => 'objective_id',
            'mst_keyculture' => 'objective_id',
            'mst_SIA' => 'objective_id',
        ];

        foreach ($childTables as $table => $fkColumn) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('focus_area_id')->nullable()->after($t->getColumnListing($t->getTable())[0] ?? 'id');
            });
            
            // Set default value = 1 (default/base)
            DB::statement("UPDATE {$table} SET focus_area_id = 1");
            
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('focus_area_id')->nullable(false)->change();
                $t->foreign('focus_area_id')->references('id')->on('mst_focusarea')->onDelete('cascade');
            });
        }

        // Pivot tables yang relate ke objective
        $pivotTables = [
            'trs_domain' => 'objective_id',
            'trs_entergoals' => 'objective_id',
            'trs_aligngoals' => 'objective_id',
            'trs_objectiveguidance' => 'objective_id',
        ];

        foreach ($pivotTables as $table => $fkColumn) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('focus_area_id')->nullable();
            });
            
            DB::statement("UPDATE {$table} SET focus_area_id = 1");
            
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('focus_area_id')->nullable(false)->change();
                $t->foreign('focus_area_id')->references('id')->on('mst_focusarea')->onDelete('cascade');
            });
        }

        // Tables with hasMany relationship to objective
        $hasManyTables = [
            'mst_eval' => 'objective_id',
            'trs_roadmap' => 'objective_id',
        ];

        foreach ($hasManyTables as $table => $fkColumn) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('focus_area_id')->nullable();
            });
            
            DB::statement("UPDATE {$table} SET focus_area_id = 1");
            
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('focus_area_id')->nullable(false)->change();
                $t->foreign('focus_area_id')->references('id')->on('mst_focusarea')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allTables = [
            'mst_practice', 'mst_policy', 'mst_skill', 'mst_keyculture', 'mst_SIA',
            'trs_domain', 'trs_entergoals', 'trs_aligngoals', 'trs_objectiveguidance',
            'mst_eval', 'trs_roadmap',
        ];

        foreach ($allTables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['focus_area_id']);
                $t->dropColumn('focus_area_id');
            });
        }
    }
};