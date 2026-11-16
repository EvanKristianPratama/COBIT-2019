<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editable COBIT 2019 practice -> COBIT 5 practice mapping, same shape as trs_cobit4_cobit5_mappings.
 * Practices without a row here fall back to an automatic name match on the evolution page.
 * A row with a NULL target_code means "reviewed, no COBIT 5 equivalent".
 * Schema only. cPanel import: database/sql/cobit2019_cobit5_mapping_cpanel.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trs_cobit2019_cobit5_mappings')) {
            Schema::create('trs_cobit2019_cobit5_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('source_code', 20);
                $table->string('target_code', 20)->nullable();
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
                $table->unique(['source_code', 'target_code']);
                $table->index('target_code');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_cobit2019_cobit5_mappings');
    }
};
