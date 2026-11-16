<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schema only. Data is injected from database/sql/cobit2019_goal_cascade_cpanel.sql.
 * Goals and metrics themselves stay in mst_entergoals / mst_aligngoals (+ *metr).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_cobit2019_goal_dimensions')) {
            Schema::create('mst_cobit2019_goal_dimensions', function (Blueprint $table) {
                $table->id();
                $table->string('goal_type', 10)->index(); // enterprise | alignment
                $table->string('goal_id', 20)->unique();  // EG01 / AG01
                $table->string('bsc_dimension', 40);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('trs_cobit2019_eg_ag_maps')) {
            Schema::create('trs_cobit2019_eg_ag_maps', function (Blueprint $table) {
                $table->id();
                $table->string('entergoals_id', 20);
                $table->string('aligngoals_id', 20)->index();
                $table->char('level', 1); // P / S
                $table->timestamps();
                $table->unique(['entergoals_id', 'aligngoals_id'], 'trs_c19_eg_ag_unique');
            });
        }

        if (! Schema::hasTable('trs_cobit2019_ag_objective_maps')) {
            Schema::create('trs_cobit2019_ag_objective_maps', function (Blueprint $table) {
                $table->id();
                $table->string('aligngoals_id', 20);
                $table->string('objective_id', 20)->index(); // EDM01, APO01, ...
                $table->char('level', 1); // P / S
                $table->timestamps();
                $table->unique(['aligngoals_id', 'objective_id'], 'trs_c19_ag_obj_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_cobit2019_ag_objective_maps');
        Schema::dropIfExists('trs_cobit2019_eg_ag_maps');
        Schema::dropIfExists('mst_cobit2019_goal_dimensions');
    }
};
