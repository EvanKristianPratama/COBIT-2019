<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_cobit4_cobit5_mapping_sources')) {
            Schema::create('mst_cobit4_cobit5_mapping_sources', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('domain', 10)->index();
                $table->string('title', 255);
                $table->string('mapping_status', 20)->default('mapped');
                $table->text('mapping_note')->nullable();
                $table->text('review_note')->nullable();
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('trs_cobit4_cobit5_mappings')) {
            Schema::create('trs_cobit4_cobit5_mappings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('source_id')
                    ->constrained('mst_cobit4_cobit5_mapping_sources')
                    ->cascadeOnDelete();
                $table->string('target_code', 20);
                $table->string('target_level', 20);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
                $table->unique(['source_id', 'target_code']);
                $table->index('target_code');
            });
        }

        $now = now();
        $sources = config('cobit4-cobit5-mapping.sources');
        if (! is_array($sources) || $sources === []) {
            $mappingConfigPath = config_path('cobit4-cobit5-mapping.php');
            if (is_file($mappingConfigPath)) {
                $mappingConfig = require $mappingConfigPath;
                $sources = $mappingConfig['sources'] ?? [];
            }
        }

        foreach (is_array($sources) ? $sources : [] as $sourceOrder => $source) {
            DB::table('mst_cobit4_cobit5_mapping_sources')->updateOrInsert(
                ['code' => $source['code']],
                [
                'code' => $source['code'],
                'domain' => $source['domain'],
                'title' => $source['title'],
                'mapping_status' => $source['mapping_status'] ?? 'mapped',
                'mapping_note' => $source['mapping_note'] ?? null,
                'review_note' => $source['review_note'] ?? null,
                'order_no' => $sourceOrder + 1,
                'created_at' => $now,
                'updated_at' => $now,
                ]
            );
            $sourceId = DB::table('mst_cobit4_cobit5_mapping_sources')
                ->where('code', $source['code'])
                ->value('id');

            $targets = collect($source['targets'] ?? [])->values()->map(
                fn (string $targetCode, int $targetOrder) => [
                    'source_id' => $sourceId,
                    'target_code' => $targetCode,
                    'target_level' => str_contains($targetCode, '.') ? 'practice' : 'process',
                    'order_no' => $targetOrder + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            )->all();

            if ($targets !== []) {
                DB::table('trs_cobit4_cobit5_mappings')->upsert(
                    $targets,
                    ['source_id', 'target_code'],
                    ['target_level', 'order_no', 'updated_at']
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_cobit4_cobit5_mappings');
        Schema::dropIfExists('mst_cobit4_cobit5_mapping_sources');
    }
};
