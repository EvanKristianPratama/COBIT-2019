<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mst_objective', 'cobit5_document')) {
            Schema::table('mst_objective', fn (Blueprint $table) => $table->json('cobit5_document')->nullable());
        }
        if (! Schema::hasColumn('mst_infoflowoutput', 'work_product_id')) {
            Schema::table('mst_infoflowoutput', function (Blueprint $table) {
                $table->foreignId('work_product_id')->nullable()->constrained('mst_evidence_ref')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('mst_infoflowoutput', fn (Blueprint $table) => $table->dropConstrainedForeignId('work_product_id'));
        Schema::table('mst_objective', fn (Blueprint $table) => $table->dropColumn('cobit5_document'));
    }
};
