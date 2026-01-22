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
        Schema::create('trs_step2', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('objective_code')->unsigned()->comment('1-40 objective codes');
            
            // Relative Importance per DF
            $table->decimal('rel_imp_df1', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df2', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df3', 5, 2)->default(0.00);
            $table->decimal('rel_imp_df4', 5, 2)->default(0.00);
            
            // Total untuk objective ini (sum weighted rel_imp DF1-4)
            $table->decimal('total_objective', 8, 2)->default(0.00);
            
            // Initial Scope Objectives Score
            $table->decimal('initial_scope_score', 8, 2)->default(0.00);
            
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
        Schema::dropIfExists('trs_step2');
    }
};
