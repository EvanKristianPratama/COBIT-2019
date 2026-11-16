<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schema only. Data is injected from database/sql/cobit_gamo_evolution_cpanel.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_cobit_evolution_domains')) {
            Schema::create('mst_cobit_evolution_domains', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('name', 100);
                $table->string('type', 30);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_cobit_evolution_domains');
    }
};
