<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_evidence', function (Blueprint $table): void {
            $table->unsignedBigInteger('eval_id')->nullable()->change();
            $table->foreignId('organization_id')
                ->nullable()
                ->after('eval_id')
                ->constrained('mst_organization', 'organization_id')
                ->nullOnDelete();
        });

        DB::table('mst_evidence')
            ->join('mst_eval', 'mst_evidence.eval_id', '=', 'mst_eval.eval_id')
            ->leftJoin('users', 'mst_eval.user_id', '=', 'users.id')
            ->whereNull('mst_evidence.organization_id')
            ->update([
                'mst_evidence.organization_id' => DB::raw('COALESCE(mst_eval.organization_id, users.organization_id)'),
            ]);
    }

    public function down(): void
    {
        if (DB::table('mst_evidence')->whereNull('eval_id')->exists()) {
            throw new RuntimeException('Master evidence exists. Reassign or remove it before rolling back this migration.');
        }

        Schema::table('mst_evidence', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('organization_id');
            $table->unsignedBigInteger('eval_id')->nullable(false)->change();
        });
    }
};
