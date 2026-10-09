<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('trs_cobit4_maturity_levels');
        Schema::dropIfExists('trs_cobit4_goals_metrics');
        Schema::dropIfExists('trs_cobit4_raci_activities');
        Schema::dropIfExists('trs_cobit4_outputs');
        Schema::dropIfExists('trs_cobit4_inputs');
        Schema::dropIfExists('mst_cobit4_control_objectives');
        Schema::dropIfExists('mst_cobit4_maturity_levels');
        Schema::dropIfExists('mst_cobit4_processes');

        // 1. Master / Process Table for COBIT 4.1 (Master Data)
        Schema::create('mst_cobit4_processes', function (Blueprint $table) {
            $table->id();
            $table->integer('focus_area_id')->default(27);
            $table->string('objective_id', 50)->nullable()->index();
            $table->string('code', 20)->index();
            $table->string('domain_code', 10)->index(); // PO, AI, DS, ME
            $table->string('domain_name', 100);
            $table->string('title', 255); // Nama GAMO / Process Title
            $table->text('description')->nullable();

            // Page 1: Information Criteria (values: 'P', 'S', or null)
            $table->string('criteria_effectiveness', 5)->nullable();
            $table->string('criteria_efficiency', 5)->nullable();
            $table->string('criteria_confidentiality', 5)->nullable();
            $table->string('criteria_integrity', 5)->nullable();
            $table->string('criteria_availability', 5)->nullable();
            $table->string('criteria_compliance', 5)->nullable();
            $table->string('criteria_reliability', 5)->nullable();

            // Page 1: Control Statement / Flow
            $table->text('control_over')->nullable();
            $table->text('satisfies_requirement')->nullable();
            $table->text('focusing_on')->nullable();
            $table->json('achieved_by')->nullable();
            $table->json('measured_by')->nullable();

            // Page 1: IT Governance Focus ('Primary', 'Secondary', or null)
            $table->string('gov_strategic_alignment', 20)->nullable();
            $table->string('gov_value_delivery', 20)->nullable();
            $table->string('gov_risk_management', 20)->nullable();
            $table->string('gov_resource_management', 20)->nullable();
            $table->string('gov_performance_measurement', 20)->nullable();

            // Page 1: IT Resources (checked or unchecked)
            $table->boolean('res_applications')->default(true);
            $table->boolean('res_information')->default(true);
            $table->boolean('res_infrastructure')->default(true);
            $table->boolean('res_people')->default(true);

            // Page 4: Maturity Intro
            $table->text('maturity_intro')->nullable();

            $table->timestamps();

            $table->foreign('focus_area_id')
                ->references('id')
                ->on('mst_focusarea')
                ->onDelete('cascade');
        });

        // 2. Master Control Objectives (Master Data - Page 2)
        Schema::create('mst_cobit4_control_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_id');
            $table->string('code', 20); // e.g. PO1.1
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->integer('order_no')->default(0);
            $table->timestamps();

            $table->foreign('process_id')
                ->references('id')
                ->on('mst_cobit4_processes')
                ->onDelete('cascade');
        });

        // 3. Inputs (Transaction / Flow Data - Page 3)
        Schema::create('trs_cobit4_inputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_id');
            $table->string('from_source', 100)->default('*'); // e.g. PO5, PO9, *
            $table->text('input_description');
            $table->integer('order_no')->default(0);
            $table->timestamps();

            $table->foreign('process_id')
                ->references('id')
                ->on('mst_cobit4_processes')
                ->onDelete('cascade');
        });

        // 4. Outputs (Transaction / Flow Data - Page 3)
        Schema::create('trs_cobit4_outputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_id');
            $table->text('output_description');
            $table->string('to_target', 255)->default(''); // e.g. PO2...PO6, AI1, DS1
            $table->integer('order_no')->default(0);
            $table->timestamps();

            $table->foreign('process_id')
                ->references('id')
                ->on('mst_cobit4_processes')
                ->onDelete('cascade');
        });

        // 5. RACI Activities (Transaction / Mapping Data - Page 3)
        Schema::create('trs_cobit4_raci_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_id');
            $table->text('activity');
            $table->json('raci_matrix')->nullable(); // JSON map of roles => RACI value
            $table->integer('order_no')->default(0);
            $table->timestamps();

            $table->foreign('process_id')
                ->references('id')
                ->on('mst_cobit4_processes')
                ->onDelete('cascade');
        });

        // 6. Goals and Metrics (Transaction / Mapping Data - Page 3)
        Schema::create('trs_cobit4_goals_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_id');
            $table->string('category', 50);
            $table->text('content');
            $table->integer('order_no')->default(0);
            $table->timestamps();

            $table->foreign('process_id')
                ->references('id')
                ->on('mst_cobit4_processes')
                ->onDelete('cascade');
        });

        // 7. Maturity Levels (Master Data - Page 4)
        Schema::create('mst_cobit4_maturity_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_id');
            $table->tinyInteger('level'); // 0, 1, 2, 3, 4, 5
            $table->string('name', 100);
            $table->text('description');
            $table->timestamps();

            $table->foreign('process_id')
                ->references('id')
                ->on('mst_cobit4_processes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_cobit4_maturity_levels');
        Schema::dropIfExists('trs_cobit4_goals_metrics');
        Schema::dropIfExists('trs_cobit4_raci_activities');
        Schema::dropIfExists('trs_cobit4_outputs');
        Schema::dropIfExists('trs_cobit4_inputs');
        Schema::dropIfExists('mst_cobit4_control_objectives');
        Schema::dropIfExists('mst_cobit4_processes');
    }
};
