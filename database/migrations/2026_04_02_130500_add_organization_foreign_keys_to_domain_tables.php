<?php

use App\Support\Organization\OrganizationNameNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('instansi')
                ->constrained('mst_organization', 'organization_id')
                ->nullOnDelete();
        });

        Schema::table('mst_eval', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('user_id')
                ->constrained('mst_organization', 'organization_id')
                ->nullOnDelete();
        });

        Schema::table('target_maturities', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('user_id')
                ->constrained('mst_organization', 'organization_id')
                ->nullOnDelete();
        });

        Schema::table('mst_targetcap', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('user_id')
                ->constrained('mst_organization', 'organization_id')
                ->nullOnDelete();
        });

        $organizationRows = $this->organizationRows();

        if ($organizationRows->isNotEmpty()) {
            DB::table('mst_organization')->upsert(
                $organizationRows->all(),
                ['organization_key'],
                ['organization_name', 'is_active', 'updated_at']
            );
        }

        $organizations = DB::table('mst_organization')
            ->select(['organization_id', 'organization_key', 'organization_name'])
            ->get()
            ->keyBy('organization_key');

        DB::table('assessment')
            ->select(['assessment_id', 'instansi'])
            ->orderBy('assessment_id')
            ->get()
            ->each(function ($assessment) use ($organizations) {
                $organizationKey = OrganizationNameNormalizer::key($assessment->instansi);
                $organization = $organizationKey !== null ? $organizations->get($organizationKey) : null;

                if ($organization === null) {
                    return;
                }

                DB::table('assessment')
                    ->where('assessment_id', $assessment->assessment_id)
                    ->update([
                        'organization_id' => $organization->organization_id,
                        'instansi' => $organization->organization_name,
                    ]);
            });

        DB::table('mst_eval')
            ->join('users', 'users.id', '=', 'mst_eval.user_id')
            ->update(['mst_eval.organization_id' => DB::raw('users.organization_id')]);

        DB::table('target_maturities')
            ->select(['id', 'user_id', 'organisasi'])
            ->orderBy('id')
            ->get()
            ->each(function ($target) use ($organizations) {
                $organizationKey = OrganizationNameNormalizer::key($target->organisasi);
                $organization = $organizationKey !== null ? $organizations->get($organizationKey) : null;
                $fallbackOrganizationId = DB::table('users')->where('id', $target->user_id)->value('organization_id');

                $resolvedOrganizationId = $organization->organization_id ?? $fallbackOrganizationId;
                $resolvedOrganizationName = $organization->organization_name
                    ?? DB::table('mst_organization')->where('organization_id', $fallbackOrganizationId)->value('organization_name')
                    ?? OrganizationNameNormalizer::display($target->organisasi);

                DB::table('target_maturities')
                    ->where('id', $target->id)
                    ->update([
                        'organization_id' => $resolvedOrganizationId,
                        'organisasi' => $resolvedOrganizationName,
                    ]);
            });

        DB::table('mst_targetcap')
            ->select(['target_id', 'user_id', 'organisasi'])
            ->orderBy('target_id')
            ->get()
            ->each(function ($target) use ($organizations) {
                $organizationKey = OrganizationNameNormalizer::key($target->organisasi);
                $organization = $organizationKey !== null ? $organizations->get($organizationKey) : null;
                $fallbackOrganizationId = DB::table('users')->where('id', $target->user_id)->value('organization_id');

                $resolvedOrganizationId = $organization->organization_id ?? $fallbackOrganizationId;
                $resolvedOrganizationName = $organization->organization_name
                    ?? DB::table('mst_organization')->where('organization_id', $fallbackOrganizationId)->value('organization_name')
                    ?? OrganizationNameNormalizer::display($target->organisasi);

                DB::table('mst_targetcap')
                    ->where('target_id', $target->target_id)
                    ->update([
                        'organization_id' => $resolvedOrganizationId,
                        'organisasi' => $resolvedOrganizationName,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('mst_targetcap', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('target_maturities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('mst_eval', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('assessment', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });
    }

    private function organizationRows(): Collection
    {
        $now = now();

        return collect([
            DB::table('assessment')->pluck('instansi'),
            DB::table('target_maturities')->pluck('organisasi'),
            DB::table('mst_targetcap')->pluck('organisasi'),
        ])
            ->flatten()
            ->pipe(fn (Collection $organizations) => collect(OrganizationNameNormalizer::unique($organizations)))
            ->map(fn (string $organization): array => [
                'organization_name' => $organization,
                'organization_key' => OrganizationNameNormalizer::key($organization),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values();
    }
};
