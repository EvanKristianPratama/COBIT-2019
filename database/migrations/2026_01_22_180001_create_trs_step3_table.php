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
        Schema::create('trs_step3', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('objective_code')->unsigned()->comment('1-40 objective codes');
            
            // Relative Importance per DF
            $table->decimal('rel_imp_df5', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df6', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df7', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df8', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df9', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df10', 5, 2)->default(0.00);
            
            // Total untuk step3 objective ini (sum weighted rel_imp DF5-10)
            $table->decimal('total_step3_objective', 8, 2)->default(0.00);
            
            // Combined total (step2 + step3) untuk objective ini
            $table->decimal('total_combined', 8, 2)->default(0.00)->comment('total_step2 + total_step3');
            
            // Refined Scope: Governance/Management Objectives Score
            $table->decimal('refined_scope_score', 8, 2)->default(0.00);
            
            $table->timestamps();
            
            // Indexes
            $table->index('assessment_id');
            $table->index('user_id');
            $table->index('objective_code');
            $table->unique(['assessment_id', 'user_id', 'objective_code'], 'unique_assessment_user_objective');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trs_step3');
    }
};
