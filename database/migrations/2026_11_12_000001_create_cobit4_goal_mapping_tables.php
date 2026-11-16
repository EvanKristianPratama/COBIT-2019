<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CRITERIA = [
        'effectiveness', 'efficiency', 'confidentiality', 'integrity',
        'availability', 'compliance', 'reliability',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('mst_cobit4_business_goals')) {
            Schema::create('mst_cobit4_business_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('code')->unique();
                $table->string('perspective', 40)->index();
                $table->string('title', 255);
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mst_cobit4_it_goals')) {
            Schema::create('mst_cobit4_it_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('code')->unique();
                $table->string('title', 255);
                foreach (self::CRITERIA as $criterion) {
                    $table->char('criteria_' . $criterion, 1)->nullable(); // P / S / null
                }
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('trs_cobit4_business_it_goal_maps')) {
            Schema::create('trs_cobit4_business_it_goal_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_goal_id')->constrained('mst_cobit4_business_goals')->cascadeOnDelete();
                $table->foreignId('it_goal_id')->constrained('mst_cobit4_it_goals')->cascadeOnDelete();
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
                $table->unique(['business_goal_id', 'it_goal_id'], 'trs_c4_bg_itg_unique');
            });
        }

        if (! Schema::hasTable('trs_cobit4_it_goal_process_maps')) {
            Schema::create('trs_cobit4_it_goal_process_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('it_goal_id')->constrained('mst_cobit4_it_goals')->cascadeOnDelete();
                $table->string('process_code', 20)->index();
                $table->unsignedSmallInteger('order_no')->default(0);
                $table->timestamps();
                $table->unique(['it_goal_id', 'process_code'], 'trs_c4_itg_proc_unique');
            });
        }

        $this->seed();
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_cobit4_it_goal_process_maps');
        Schema::dropIfExists('trs_cobit4_business_it_goal_maps');
        Schema::dropIfExists('mst_cobit4_it_goals');
        Schema::dropIfExists('mst_cobit4_business_goals');
    }

    private function seed(): void
    {
        $data = require database_path('data/cobit4/goal-mapping.php');
        $now = now();

        foreach ($data['it_goals'] as $code => [$title, $criteria]) {
            $row = ['title' => $title, 'order_no' => $code, 'created_at' => $now, 'updated_at' => $now];
            foreach (self::CRITERIA as $i => $criterion) {
                $mark = $criteria[$i] ?? '.';
                $row['criteria_' . $criterion] = $mark === '.' ? null : $mark;
            }
            DB::table('mst_cobit4_it_goals')->updateOrInsert(['code' => $code], $row);
        }

        foreach ($data['business_goals'] as $code => [$perspective, $title]) {
            DB::table('mst_cobit4_business_goals')->updateOrInsert(
                ['code' => $code],
                ['perspective' => $perspective, 'title' => $title, 'order_no' => $code, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $bgIds = DB::table('mst_cobit4_business_goals')->pluck('id', 'code');
        $itIds = DB::table('mst_cobit4_it_goals')->pluck('id', 'code');

        foreach ($data['business_goals'] as $bgCode => [, , $itGoals]) {
            foreach (array_values($itGoals) as $i => $itCode) {
                DB::table('trs_cobit4_business_it_goal_maps')->updateOrInsert(
                    ['business_goal_id' => $bgIds[$bgCode], 'it_goal_id' => $itIds[$itCode]],
                    ['order_no' => $i + 1, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        foreach ($data['it_goals'] as $itCode => [, , $processes]) {
            foreach (array_values($processes) as $i => $processCode) {
                DB::table('trs_cobit4_it_goal_process_maps')->updateOrInsert(
                    ['it_goal_id' => $itIds[$itCode], 'process_code' => $processCode],
                    ['order_no' => $i + 1, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
};
