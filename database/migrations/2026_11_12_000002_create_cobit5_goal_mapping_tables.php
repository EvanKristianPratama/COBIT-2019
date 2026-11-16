<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schema only. Data is injected from database/sql/cobit5_goal_mapping_cpanel.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_cobit5_enterprise_goals')) {
            Schema::create('mst_cobit5_enterprise_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('code')->unique();
                $table->string('bsc_dimension', 40)->index();
                $table->string('title', 255);
                $table->char('relation_benefits_realisation', 1)->nullable();
                $table->char('relation_risk_optimisation', 1)->nullable();
                $table->char('relation_resource_optimisation', 1)->nullable();
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mst_cobit5_it_goals')) {
            Schema::create('mst_cobit5_it_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('code')->unique();
                $table->string('bsc_dimension', 40)->index();
                $table->string('title', 255);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mst_cobit5_processes')) {
            Schema::create('mst_cobit5_processes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('domain_code', 5)->index();
                $table->string('domain_name', 60);
                $table->string('title', 255);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('trs_cobit5_enterprise_it_goal_maps')) {
            Schema::create('trs_cobit5_enterprise_it_goal_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enterprise_goal_id')->constrained('mst_cobit5_enterprise_goals')->cascadeOnDelete();
                $table->foreignId('it_goal_id')->constrained('mst_cobit5_it_goals')->cascadeOnDelete();
                $table->char('level', 1); // P / S
                $table->timestamps();
                $table->unique(['enterprise_goal_id', 'it_goal_id'], 'trs_c5_eg_itg_unique');
            });
        }

        if (! Schema::hasTable('trs_cobit5_it_goal_process_maps')) {
            Schema::create('trs_cobit5_it_goal_process_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('it_goal_id')->constrained('mst_cobit5_it_goals')->cascadeOnDelete();
                $table->foreignId('process_id')->constrained('mst_cobit5_processes')->cascadeOnDelete();
                $table->char('level', 1); // P / S
                $table->timestamps();
                $table->unique(['it_goal_id', 'process_id'], 'trs_c5_itg_proc_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_cobit5_it_goal_process_maps');
        Schema::dropIfExists('trs_cobit5_enterprise_it_goal_maps');
        Schema::dropIfExists('mst_cobit5_processes');
        Schema::dropIfExists('mst_cobit5_it_goals');
        Schema::dropIfExists('mst_cobit5_enterprise_goals');
    }
};
