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
        Schema::table('trs_summaryreport', function (Blueprint $table) {
            $table->json('roadmap_rekomendasi')->nullable()->after('rekomendasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trs_summaryreport', function (Blueprint $table) {
            $table->dropColumn('roadmap_rekomendasi');
        });
    }
};
