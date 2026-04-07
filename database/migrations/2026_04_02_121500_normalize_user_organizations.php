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
        Schema::create('mst_organization', function (Blueprint $table) {
            $table->bigIncrements('organization_id');
            $table->string('organization_name');
            $table->string('organization_key')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('organisasi')
                ->constrained('mst_organization', 'organization_id')
                ->nullOnDelete();
        });

        Schema::create('trs_userorganization', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('mst_organization', 'organization_id')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'organization_id'], 'trs_userorganization_unique');
        });

        $now = now();
        $organizationRows = $this->organizationSeedRows($now);

        if ($organizationRows->isNotEmpty()) {
            DB::table('mst_organization')->insert($organizationRows->all());
        }

        $organizationMap = DB::table('mst_organization')
            ->select(['organization_id', 'organization_key', 'organization_name'])
            ->get()
            ->keyBy('organization_key');

        DB::table('users')
            ->select(['id', 'organisasi'])
            ->orderBy('id')
            ->get()
            ->map(function ($user) use ($now, $organizationMap) {
                $organizationKey = OrganizationNameNormalizer::key($user->organisasi);
                $organization = $organizationKey !== null ? $organizationMap->get($organizationKey) : null;

                if ($organization === null) {
                    return null;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'organization_id' => $organization->organization_id,
                        'organisasi' => $organization->organization_name,
                    ]);

                return [
                    'user_id' => $user->id,
                    'organization_id' => $organization->organization_id,
                    'is_primary' => true,
                    'assigned_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->chunk(200)
            ->each(function ($chunk) {
                DB::table('trs_userorganization')->insert($chunk->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_userorganization');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::dropIfExists('mst_organization');
    }

    private function organizationSeedRows($now): Collection
    {
        return collect([
            DB::table('users')->pluck('organisasi'),
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
