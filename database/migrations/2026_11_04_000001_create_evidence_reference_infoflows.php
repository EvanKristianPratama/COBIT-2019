<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trs_evidence_ref_infoflow')) {
            return;
        }
        Schema::create('trs_evidence_ref_infoflow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_ref_id')->constrained('mst_evidence_ref')->cascadeOnDelete();
            $table->unsignedBigInteger('input_id')->nullable();
            $table->unsignedBigInteger('output_id')->nullable();
            $table->foreign('input_id')->references('input_id')->on('mst_infoflowinput')->cascadeOnDelete();
            $table->foreign('output_id')->references('output_id')->on('mst_infoflowoutput')->cascadeOnDelete();
            $table->unique(['evidence_ref_id', 'input_id'], 'evidence_ref_input_unique');
            $table->unique(['evidence_ref_id', 'output_id'], 'evidence_ref_output_unique');
            $table->timestamps();
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE trs_evidence_ref_infoflow ADD CONSTRAINT evidence_ref_flow_direction_check CHECK ((input_id IS NOT NULL) <> (output_id IS NOT NULL))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_evidence_ref_infoflow');
    }
};
