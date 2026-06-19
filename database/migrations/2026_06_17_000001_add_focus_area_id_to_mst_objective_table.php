<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_objective', function (Blueprint $table) {
            $table->unsignedBigInteger('focus_area_id')->after('objective_purpose');
            $table->foreign('focus_area_id')
                  ->references('id')
                  ->on('mst_focusarea')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_objective', function (Blueprint $table) {
            $table->dropForeign(['focus_area_id']);
            $table->dropColumn('focus_area_id');
        });
    }
};
