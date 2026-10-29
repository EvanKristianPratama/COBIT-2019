<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('trs_roadmap', 'organization_id')) {
            Schema::table('trs_roadmap', function (Blueprint $table): void {
                // Legacy global rows remain unassigned until their company is known.
                $table->unsignedBigInteger('organization_id')->nullable()->index();
            });
        }

        if (! Schema::hasIndex('trs_roadmap', 'roadmap_org_objective_year_unique')) {
            Schema::table('trs_roadmap', function (Blueprint $table): void {
                $table->unique(['organization_id', 'objective_id', 'year'], 'roadmap_org_objective_year_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('trs_roadmap', function (Blueprint $table): void {
            $table->dropUnique('roadmap_org_objective_year_unique');
            $table->dropColumn('organization_id');
        });
    }
};
