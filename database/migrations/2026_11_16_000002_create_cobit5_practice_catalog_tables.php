<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * COBIT 5 GAMO and practice catalog used by the GAMO mapping pages (picker, names, 5 -> 4.1 grouping).
 * Seeded from config/cobit5-practice-catalog.php, which stays as the seed source and as the fallback
 * while these tables do not exist. cPanel import: database/sql/cobit5_practice_catalog_cpanel.sql.
 *
 * is_verified = 0 marks codes that appear in the supplied mapping source but not in the COBIT 5 framework (e.g. DSS08.02).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_cobit5_catalog_gamos')) {
            Schema::create('mst_cobit5_catalog_gamos', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('domain_code', 10)->index();
                $table->string('domain_title', 100);
                $table->string('title', 255);
                $table->boolean('is_verified')->default(true);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mst_cobit5_catalog_practices')) {
            Schema::create('mst_cobit5_catalog_practices', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('gamo_code', 10)->index();
                $table->string('title', 500);
                $table->text('review_note')->nullable();
                $table->boolean('is_verified')->default(true);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        $catalog = config('cobit5-practice-catalog');
        if (! is_array($catalog) || $catalog === []) {
            $path = config_path('cobit5-practice-catalog.php');
            $catalog = is_file($path) ? require $path : [];
        }

        $now = now();
        $gamoOrder = 0;
        $practiceOrder = 0;

        foreach ($catalog['domain_order'] ?? [] as $domainCode) {
            $domain = $catalog['domains'][$domainCode] ?? null;
            foreach ($domain['processes'] ?? [] as $gamoCode => $process) {
                DB::table('mst_cobit5_catalog_gamos')->updateOrInsert(['code' => $gamoCode], [
                    'domain_code' => $domainCode,
                    'domain_title' => $domain['title'],
                    'title' => $process['title'],
                    'is_verified' => true,
                    'order_no' => ++$gamoOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($process['practices'] as $practice) {
                    DB::table('mst_cobit5_catalog_practices')->updateOrInsert(['code' => $practice['code']], [
                        'gamo_code' => $gamoCode,
                        'title' => $practice['title'],
                        'review_note' => $practice['review_note'] ?? null,
                        'is_verified' => true,
                        'order_no' => ++$practiceOrder,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        foreach ($catalog['unverified_practices'] ?? [] as $practice) {
            DB::table('mst_cobit5_catalog_gamos')->updateOrInsert(['code' => $practice['gamo_code']], [
                'domain_code' => $practice['domain_code'],
                'domain_title' => $practice['domain_title'],
                'title' => $practice['gamo_title'],
                'is_verified' => false,
                'order_no' => ++$gamoOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('mst_cobit5_catalog_practices')->updateOrInsert(['code' => $practice['code']], [
                'gamo_code' => $practice['gamo_code'],
                'title' => $practice['title'],
                'review_note' => $practice['review_note'] ?? null,
                'is_verified' => false,
                'order_no' => ++$practiceOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_cobit5_catalog_practices');
        Schema::dropIfExists('mst_cobit5_catalog_gamos');
    }
};
