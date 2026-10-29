<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_cobit_evolution', function (Blueprint $table) {
            $table->id();
            $table->string('cobit2019_id', 20)->unique();
            $table->string('domain', 10)->index();
            $table->string('title_2019');
            $table->text('desc_2019')->nullable();
            $table->string('cobit5_id', 100)->nullable();
            $table->string('cobit5_title')->nullable();
            $table->string('cobit4_id', 100)->nullable();
            $table->string('cobit4_title')->nullable();
            $table->string('status', 30)->default('evolved')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $now = now();
        $rows = collect(config('cobit-evolution.gamo', []))
            ->map(fn (array $item) => [
                'cobit2019_id' => $item['id'],
                'domain' => $item['domain'],
                'title_2019' => $item['title_2019'],
                'desc_2019' => $item['desc_2019'] ?? null,
                'cobit5_id' => $item['cobit5_id'] ?? null,
                'cobit5_title' => $item['cobit5_title'] ?? null,
                'cobit4_id' => $item['cobit4_id'] ?? null,
                'cobit4_title' => $item['cobit4_title'] ?? null,
                'status' => $item['status'] ?? 'evolved',
                'notes' => $item['notes'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($rows !== []) {
            DB::table('mst_cobit_evolution')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_cobit_evolution');
    }
};
