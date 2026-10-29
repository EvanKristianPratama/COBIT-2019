<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\MstObjective;
use App\Models\TrsRoadmap;
use App\Models\TrsStep2;
use App\Models\TrsStep3;
use App\Models\TrsStep4;
use App\Models\User;
use App\Services\Cobit\CobitCoreModelService;
use App\Services\Cobit\DesignFactorDeletionService;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DesignFactorWorkspaceTest extends TestCase
{
    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->string('access_profile');
            $table->unsignedBigInteger('organization_id');
            $table->string('organisasi')->nullable();
            $table->string('jabatan')->nullable();
            $table->timestamps();
        });
        Schema::create('mst_organization', function (Blueprint $table): void {
            $table->id('organization_id');
            $table->string('organization_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('trs_userorganization', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
        });
        Schema::create('assessment', function (Blueprint $table): void {
            $table->id('assessment_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('instansi');
            $table->string('kode_assessment');
            $table->timestamps();
        });
        Schema::create('access_assignments', function (Blueprint $table): void {
            $table->id();
            $table->morphs('assignable');
            $table->unsignedBigInteger('user_id');
            $table->string('access_profile');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
        });
        Schema::create('mst_eval', function (Blueprint $table): void {
            $table->id('eval_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->unsignedInteger('focus_area_id');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('mst_focusarea', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('version')->nullable();
        });
        Schema::create('mst_objective', function (Blueprint $table): void {
            $table->string('objective_id')->primary();
            $table->unsignedInteger('focus_area_id');
            $table->string('objective');
        });
        Schema::create('trs_roadmap', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('focus_area_id');
            $table->string('objective_id');
            $table->integer('year');
            $table->integer('level')->nullable();
            $table->string('rating')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_29_000001_add_organization_to_trs_roadmap_table.php'))->up();
        foreach ([2, 3] as $step) {
            (require database_path('migrations/2026_01_22_18000'.($step - 2).'_create_trs_step'.$step.'_table.php'))->up();
            if ($step === 2) {
                // SQLite index names are global; the historical MySQL migrations reuse this name.
                Schema::table('trs_step2', function (Blueprint $table): void {
                    $table->dropUnique('unique_assessment_user_objective');
                    $table->unique(['assessment_id', 'user_id', 'objective_code'], 'step2_assessment_user_objective_unique');
                });
            }
            Schema::create('df_step'.$step, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('assessment_id');
                $table->unsignedBigInteger('user_id');
                $table->json('weights');
                $table->timestamps();
            });
        }
        Schema::create('trs_step4', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('objective_code');
            $table->string('objective_id');
            foreach (['adjustment', 'suggested_level', 'agreed_level', 'is_selected'] as $column) {
                $table->integer($column)->default(0);
            }
            $table->decimal('concluded_priority')->default(0);
            $table->text('reason_adjust')->nullable();
            $table->text('reason_target')->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'user_id', 'objective_code']);
        });
        for ($number = 1; $number <= 10; $number++) {
            $inputTable = $number === 3 ? 'design_factor_3_a' : 'design_factor_'.$number;
            Schema::create($inputTable, function (Blueprint $table) use ($number): void {
                $table->id('df'.$number.'_id');
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('assessment_id');
                $dataClass = 'App\\Data\\Cobit\\Df'.$number.'Data';
                for ($index = 1; $index <= $dataClass::INPUT_COUNT; $index++) {
                    $table->decimal('input'.$index.'df'.$number)->nullable();
                }
                $table->timestamps();
            });
            Schema::create('design_factor_'.$number.'_score', function (Blueprint $table) use ($number, $inputTable): void {
                $table->id();
                $table->unsignedBigInteger('assessment_id');
                $table->unsignedBigInteger('df'.$number.'_id');
                $table->foreign('df'.$number.'_id')->references('df'.$number.'_id')->on($inputTable)->restrictOnDelete();
            });
            Schema::create('design_factor_'.$number.'_relative_importance', function (Blueprint $table) use ($number): void {
                $table->id();
                $table->unsignedBigInteger('assessment_id');
                for ($code = 1; $code <= 40; $code++) {
                    $table->decimal('r_df'.$number.'_'.$code)->default(0);
                }
                $table->timestamps();
            });
        }
        foreach (['design_factor_3_b', 'design_factor_3_c'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('assessment_id');
            });
        }
        (require database_path('migrations/2026_04_02_034817_create_permission_tables.php'))->up();
        foreach (PermissionCatalog::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        DB::table('mst_organization')->insert([
            ['organization_id' => 1, 'organization_name' => 'Company Alpha'], ['organization_id' => 2, 'organization_name' => 'Company Beta'], ['organization_id' => 3, 'organization_name' => 'Company Other'],
        ]);
        $this->editor = User::create(['name' => 'DF Editor', 'role' => 'user', 'access_profile' => 'df_editor', 'organization_id' => 1, 'organisasi' => 'Company Alpha', 'jabatan' => 'IT Managers']);
        $this->editor->givePermissionTo(['cobit.view', 'design-factors.view', 'design-factors.input', 'assessments.view']);
        $this->editor->organizations()->attach([1 => ['is_primary' => true], 2 => ['is_primary' => false]]);
        $this->actingAs($this->editor)->withSession(['active_organization_ids' => [1], 'active_organization_id' => 1]);
        $this->withoutVite();
        view()->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        $this->seedCore();
    }

    public function test_landing_has_four_steps_and_company_list_filters_on_the_server(): void
    {
        $own = $this->assessment(1, 'ALPHA-DF');
        $other = $this->assessment(2, 'BETA-DF');
        $this->get(route('cobit.home'))->assertOk()->assertSee('STEP 4')->assertSee('Design Factor List')->assertDontSee('ALPHA-DF');
        $this->get(route('cobit.design-factors.index'))->assertOk()->assertSee('ALPHA-DF')->assertDontSee('BETA-DF')->assertSeeInOrder(['Organisasi', 'Kode Design Factor', 'Aksi']);
        $this->post(route('cobit.design-factors.open', $other), ['step' => 4])->assertForbidden();
        $this->withSession(['assessment_id' => $own->assessment_id, 'step4' => ['reason_adjust' => [1 => 'Stale other assessment']]])
            ->post(route('cobit.design-factors.open', $own), ['step' => 3])->assertRedirect(route('step3.index'));
        $this->withSession(['assessment_id' => $other->assessment_id])->get(route('step4.index'))
            ->assertRedirect(route('cobit.design-factors.index'))->assertSessionMissing('assessment_id');
        $this->withSession(['assessment_id' => $other->assessment_id])->postJson(route('step4.store'), [])->assertForbidden();
        $this->getJson(route('cobit.design-factors.index', ['organization_id' => 3]))->assertUnprocessable();
    }

    public function test_multiple_selected_companies_and_legacy_company_records_are_supported(): void
    {
        $this->assessment(1, 'ALPHA-DF');
        $this->assessment(2, 'BETA-DF');
        Assessment::create(['user_id' => $this->editor->id, 'organization_id' => null, 'instansi' => 'Company Alpha', 'kode_assessment' => 'LEGACY-ALPHA']);
        $this->withSession(['active_organization_ids' => [1, 2]])->get(route('cobit.design-factors.index'))
            ->assertOk()->assertSee('ALPHA-DF')->assertSee('BETA-DF')->assertSee('LEGACY-ALPHA');
        $this->postJson(route('assessment.join.store'), ['kode_assessment' => 'new', 'organization_id' => 3])->assertUnprocessable();
    }

    public function test_step_results_are_persisted_and_reloaded_without_session_fallback(): void
    {
        $assessment = $this->assessment(1, 'ALPHA-DF');
        for ($number = 1; $number <= 10; $number++) {
            DB::table('design_factor_'.$number.'_relative_importance')->insert(['assessment_id' => $assessment->assessment_id, 'r_df'.$number.'_1' => $number]);
        }
        $this->withSession(['assessment_id' => $assessment->assessment_id]);
        $this->postJson(route('step2.store'), ['weights' => json_encode([1, 1, 1, 1]), 'initial_scope_scores' => json_encode([100])])->assertOk();
        $this->get(route('step2.index'))->assertOk()->assertViewHas('savedWeights', [1, 1, 1, 1]);
        $this->postJson(route('step3.store'), ['weights3' => json_encode([1, 1, 1, 1, 1, 1]), 'refinedScopes' => json_encode([100])])->assertOk();
        $this->get(route('step3.index'))->assertOk()->assertViewHas('savedWeights3', [1, 1, 1, 1, 1, 1]);
        $payload = ['assessment_id' => $assessment->assessment_id, 'adjustment' => [1 => 5], 'reason_adjust' => [1 => 'Approved adjustment'], 'reason_target' => [1 => 'Company target'], 'concluded_priority' => [1 => 105], 'suggested_level' => [1 => 4], 'agreed_level' => [1 => 3], 'selected' => [1 => 1]];
        $this->post(route('step4.store'), $payload)->assertRedirect(route('step4.index'));
        $this->post(route('step4.store'), $payload)->assertRedirect();
        $this->assertSame(40, TrsStep2::count());
        $this->assertSame(40, TrsStep3::count());
        $this->assertSame(40, TrsStep4::count());
        $this->assertEquals(10, TrsStep2::where('objective_code', 1)->value('total_objective'));
        $this->assertEquals(55, TrsStep3::where('objective_code', 1)->value('total_combined'));
        $this->withSession(['step4' => ['reason_adjust' => [2 => 'Foreign session reason']]])->get(route('step4.index'))
            ->assertOk()->assertViewHas('step4ReasonAdj', fn ($reasons) => $reasons[1] === 'Approved adjustment' && $reasons[2] === '');
        $this->get(route('cobit.design-factors.index'))->assertOk()->assertSee('Tersimpan');
    }

    public function test_roadmap_only_contains_core_objectives_and_the_selected_company_targets(): void
    {
        $this->roadmap(1, 2026, 2);
        $this->roadmap(2, 2030, 5);
        $this->roadmap(null, 2025, 4);
        $this->get(route('roadmap.index'))->assertOk()->assertViewHas('objectives', fn ($items) => $items->count() === 40 && $items->every(fn ($item) => $item->focus_area_id === 7))
            ->assertViewHas('years', [2026])->assertDontSee('AI01.M27')->assertDontSee('EDM01.M8');
        $this->get(route('roadmap.report'))->assertOk()->assertViewHas('years', [2026]);
    }

    public function test_roadmap_writes_and_year_deletion_cannot_affect_other_companies_or_models(): void
    {
        $this->roadmap(1, 2026, 2);
        $this->roadmap(2, 2026, 5);
        $this->roadmap(null, 2026, 4);
        $payload = ['organization_id' => 1, 'roadmap' => [[2026 => ['objective_id' => 'EDM01', 'year' => 2026, 'level' => 3, 'rating' => '3F']]]];
        $this->post(route('roadmap.store'), $payload)->assertRedirect();
        $this->assertEquals(3, TrsRoadmap::where('organization_id', 1)->value('level'));
        $this->assertEquals(5, TrsRoadmap::where('organization_id', 2)->value('level'));
        $payload['roadmap'][0][2026]['objective_id'] = 'AI01.M27';
        $this->postJson(route('roadmap.store'), $payload)->assertUnprocessable();
        $this->postJson(route('roadmap.delete-year'), ['organization_id' => 2, 'year' => 2026])->assertUnprocessable();
        $this->post(route('roadmap.delete-year'), ['organization_id' => 1, 'year' => 2026])->assertRedirect();
        $this->assertSame(0, TrsRoadmap::where('organization_id', 1)->count());
        $this->assertSame(2, TrsRoadmap::count());
    }

    public function test_roadmap_step4_sources_are_restricted_to_company_and_core(): void
    {
        $assessment = $this->assessment(1, 'ALPHA-DF');
        $other = $this->assessment(2, 'BETA-DF');
        foreach (['EDM01', 'AI01.M27'] as $index => $id) {
            TrsStep4::create(['assessment_id' => $assessment->assessment_id, 'user_id' => $this->editor->id, 'objective_code' => $index + 1, 'objective_id' => $id, 'agreed_level' => 3, 'is_selected' => 1]);
        }
        $this->getJson(route('roadmap.step4-scope', ['assessment_id' => $assessment->assessment_id]))->assertOk()->assertJsonCount(1, 'objectives')->assertJsonPath('objectives.0.objective_id', 'EDM01');
        $this->getJson(route('roadmap.step4-scope', ['assessment_id' => $other->assessment_id]))->assertNotFound();
        $this->editor->revokePermissionTo('design-factors.input');
        $this->postJson(route('roadmap.store'), [])->assertForbidden();
        $this->editor->revokePermissionTo('assessments.view');
        $this->getJson(route('roadmap.scopes', ['eval_id' => 1]))->assertForbidden();
    }

    public function test_assessment_summary_consumes_the_evaluation_company_roadmap(): void
    {
        $this->roadmap(1, 2026, 2);
        $this->roadmap(2, 2026, 5);
        $this->roadmap(null, 2025, 4);
        $service = app(\App\Services\Assessment\Summary\AssessmentSummaryService::class);
        $data = $service->getRoadmapTargetCapability('EDM01', 2);
        $this->assertSame([2026], $data['years']);
        $this->assertEquals(5, $data['objectives']->first()->roadmap_values[2026]['level']);
        $current = $service->getRoadmapTargetCapability('EDM01');
        $this->assertEquals(2, $current['objectives']->first()->roadmap_values[2026]['level']);
    }

    public function test_comparison_pdf_renders_each_assessment_company_roadmap_separately(): void
    {
        $this->roadmap(1, 2026, 2);
        $this->roadmap(2, 2026, 5);
        $service = app(\App\Services\Assessment\Summary\AssessmentSummaryService::class);
        $selectedData = [];
        $companyRoadmaps = [];
        foreach ([1 => 'Company Alpha', 2 => 'Company Beta'] as $id => $name) {
            $selectedData[] = ['organization_id' => $id, 'year' => 2026, 'scope_name' => $name.' scope', 'maturity_scores' => ['EDM01' => 1], 'effective_target' => 3];
            $companyRoadmaps[] = ['organization_id' => $id, 'organization_name' => $name, 'roadmap' => $service->getRoadmapTargetCapability('EDM01', $id)];
        }
        $html = view('assessment.report.export.all-pdf', ['objectives' => MstObjective::where('objective_id', 'EDM01')->get(), 'selectedData' => $selectedData, 'companyRoadmaps' => $companyRoadmaps, 'showMaxLevel' => false, 'includeRoadmap' => true])->render();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $alpha = $xpath->query('//section[@data-roadmap-organization="1"]')->item(0)->textContent;
        $beta = $xpath->query('//section[@data-roadmap-organization="2"]')->item(0)->textContent;
        $this->assertStringContainsString('2F', $alpha);
        $this->assertStringNotContainsString('5F', $alpha);
        $this->assertStringContainsString('5F', $beta);
        $this->assertStringNotContainsString('2F', $beta);
    }

    public function test_progress_table_counts_latest_saved_answers_instead_of_input_rows(): void
    {
        $assessment = $this->assessment(1, 'PROGRESS-DF');
        DB::table('assessment')->where('assessment_id', $assessment->assessment_id)->update(['created_at' => '2025-01-01 08:00:00', 'updated_at' => '2025-01-01 08:00:00']);
        $base = ['assessment_id' => $assessment->assessment_id, 'id' => $this->editor->id, 'created_at' => '2026-05-11 08:00:00', 'updated_at' => '2026-05-12 09:00:00'];
        DB::table('design_factor_1')->insert([...$base, 'created_at' => '2026-05-10 08:00:00', 'input1df1' => 3, 'input2df1' => 3, 'input3df1' => 3, 'input4df1' => 3]);
        DB::table('design_factor_1')->insert([...$base, 'input1df1' => 5, 'input2df1' => 3, 'input3df1' => 0]);
        $df2 = $base;
        for ($number = 1; $number <= 9; $number++) {
            $df2['input'.$number.'df2'] = 3;
        }
        DB::table('design_factor_2')->insert([...$df2, 'input10df2' => 0, 'input12df2' => 6]);
        DB::table('design_factor_3_a')->insert([...$base, 'input1df3' => 9, 'input2df3' => 0, 'input3df3' => 26]);
        DB::table('design_factor_4')->insert([...$base, 'input1df4' => 1, 'input2df4' => 3, 'input3df4' => 0, 'input4df4' => 4]);
        DB::table('design_factor_5')->insert([...$base, 'input1df5' => 0, 'input2df5' => 100]);
        DB::table('design_factor_6')->insert([...$base, 'input1df6' => 0, 'input3df6' => 100]);
        DB::table('design_factor_7')->insert([...$base, 'input1df7' => 5, 'input2df7' => 0]);
        DB::table('design_factor_8')->insert([...$base, 'input1df8' => 50, 'input2df8' => 50, 'input3df8' => 0]);
        DB::table('design_factor_9')->insert([...$base, 'input2df9' => 0, 'input3df9' => 100]);
        for ($code = 1; $code <= 40; $code++) {
            DB::table('trs_step2')->insert(['assessment_id' => $assessment->assessment_id, 'user_id' => $this->editor->id, 'objective_code' => $code, 'created_at' => '2026-05-13 10:00:00', 'updated_at' => '2026-05-13 10:00:00']);
            DB::table('trs_step3')->insert(['assessment_id' => $assessment->assessment_id, 'user_id' => $code <= 20 ? $this->editor->id : 999, 'objective_code' => $code, 'created_at' => '2026-05-14 11:00:00', 'updated_at' => '2026-05-14 11:00:00']);
        }
        $response = $this->get(route('cobit.design-factors.index'))->assertOk()->assertSee('Progress')->assertSee('Last Update')->assertSee('Created At')->assertDontSee('Design Factor terisi');
        $progress = $response->viewData('progress')[$assessment->assessment_id];
        foreach ([1 => [2, 4], 2 => [9, 13], 3 => [1, 19], 4 => [2, 20], 5 => [2, 2], 6 => [2, 3], 7 => [1, 4], 8 => [3, 3], 9 => [2, 3], 10 => [0, 3]] as $factor => [$answered, $total]) {
            $this->assertSame(['answered' => $answered, 'total' => $total], $progress['factors'][$factor]);
        }
        $this->assertSame(['saved' => 40, 'complete' => true], $progress['steps'][2]);
        $this->assertSame(['saved' => 20, 'complete' => false], $progress['steps'][3]);
        $this->assertSame(['saved' => 0, 'complete' => false], $progress['steps'][4]);
        $this->assertSame('2026-05-14 11:00', $progress['last_updated_at']->format('Y-m-d H:i'));
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $row = '//tr[@data-assessment-id="'.$assessment->assessment_id.'"]';
        $this->assertSame(18, $xpath->query($row.'/td')->length);
        $this->assertSame(1, $xpath->query('//th[@colspan="13" and normalize-space(.)="Progress"]')->length);
        $this->assertSame('2/4', trim($xpath->query($row.'/td[@data-df-progress="1"]')->item(0)->textContent));
        $this->assertSame('9/13', trim($xpath->query($row.'/td[@data-df-progress="2"]')->item(0)->textContent));
        $this->assertSame('Tersimpan', trim($xpath->query($row.'/td[@data-step-progress="2"]')->item(0)->textContent));
        $this->assertSame('20/40', trim($xpath->query($row.'/td[@data-step-progress="3"]')->item(0)->textContent));
        $this->assertSame('Belum disimpan', trim($xpath->query($row.'/td[@data-step-progress="4"]')->item(0)->textContent));
    }

    public function test_deleting_a_design_factor_removes_its_related_rows_and_preserves_other_records(): void
    {
        $assessment = $this->assessment(1, 'REMOVE-DF');
        $other = $this->assessment(1, 'KEEP-DF');
        $tables = $this->seedDeletionData($assessment);
        $this->seedDeletionData($other);
        DB::table('mst_eval')->insert(['eval_id' => $assessment->assessment_id, 'user_id' => $this->editor->id, 'organization_id' => 1, 'focus_area_id' => 7]);
        DB::table('access_assignments')->insert(['assignable_type' => \App\Models\MstEval::class, 'assignable_id' => $assessment->assessment_id, 'user_id' => $this->editor->id, 'access_profile' => 'df_editor']);
        $this->roadmap(1, 2026, 3);
        $this->withSession(['assessment_id' => $assessment->assessment_id, 'instansi' => 'Company Alpha', 'step4' => ['adjustment' => [1 => 5]]])
            ->delete(route('cobit.design-factors.destroy', $assessment), ['confirmation_code' => 'REMOVE-DF', 'confirmation_text' => 'saya yakin'])
            ->assertRedirect(route('cobit.design-factors.index', ['step' => 1]))->assertSessionMissing('assessment_id')->assertSessionMissing('step4');
        $this->assertDatabaseMissing('assessment', ['assessment_id' => $assessment->assessment_id]);
        $this->assertDatabaseHas('assessment', ['assessment_id' => $other->assessment_id]);
        foreach ($tables as $table) {
            $this->assertSame(0, DB::table($table)->where('assessment_id', $assessment->assessment_id)->count(), $table);
            $this->assertSame(1, DB::table($table)->where('assessment_id', $other->assessment_id)->count(), $table);
        }
        $this->assertSame(0, $assessment->accessAssignments()->count());
        $this->assertSame(1, $other->accessAssignments()->count());
        $this->assertDatabaseHas('access_assignments', ['assignable_type' => \App\Models\MstEval::class, 'assignable_id' => $assessment->assessment_id]);
        $this->assertDatabaseHas('mst_eval', ['eval_id' => $assessment->assessment_id]);
        $this->assertSame(1, TrsRoadmap::count());
    }

    public function test_deletion_requires_the_matching_code_and_active_company_input_permission(): void
    {
        $assessment = $this->assessment(1, 'ALPHA-DF');
        $other = $this->assessment(2, 'BETA-DF');
        $this->deleteJson(route('cobit.design-factors.destroy', $assessment), [])->assertUnprocessable();
        $this->deleteJson(route('cobit.design-factors.destroy', $assessment), ['confirmation_code' => 'ALPHA-DF'])->assertUnprocessable();
        $this->deleteJson(route('cobit.design-factors.destroy', $assessment), ['confirmation_code' => 'ALPHA-DF', 'confirmation_text' => 'yakin'])->assertUnprocessable();
        $this->deleteJson(route('cobit.design-factors.destroy', $assessment), ['confirmation_code' => 'WRONG', 'confirmation_text' => 'saya yakin'])->assertUnprocessable();
        $this->deleteJson(route('cobit.design-factors.destroy', $other), ['confirmation_code' => 'BETA-DF'])->assertForbidden();
        $this->get(route('cobit.design-factors.index'))->assertOk()->assertSee('data-delete-url=', false)->assertSee('deleteDesignFactorModal');
        $this->editor->revokePermissionTo('design-factors.input');
        $this->deleteJson(route('cobit.design-factors.destroy', $assessment), ['confirmation_code' => 'ALPHA-DF'])->assertForbidden();
        $this->get(route('cobit.design-factors.index'))->assertOk()->assertDontSee('data-delete-url=', false)->assertDontSee('deleteDesignFactorModal');
        $this->assertSame(2, Assessment::count());
    }

    public function test_failed_deletion_rolls_back_inputs_scores_and_step_results(): void
    {
        $assessment = $this->assessment(1, 'ROLLBACK-DF');
        $tables = $this->seedDeletionData($assessment);
        DB::statement("CREATE TRIGGER block_df_delete BEFORE DELETE ON assessment BEGIN SELECT RAISE(ABORT, 'Simulated failure'); END");
        try {
            app(DesignFactorDeletionService::class)->delete($assessment, $this->editor);
            $this->fail('Deletion should have failed.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertStringContainsString('Simulated failure', $exception->getMessage());
        }
        $this->assertDatabaseHas('assessment', ['assessment_id' => $assessment->assessment_id]);
        foreach ($tables as $table) {
            $this->assertSame(1, DB::table($table)->where('assessment_id', $assessment->assessment_id)->count(), $table);
        }
        $this->assertSame(1, $assessment->accessAssignments()->count());
    }

    private function seedDeletionData(Assessment $assessment): array
    {
        $tables = [];
        for ($number = 1; $number <= 10; $number++) {
            $inputTable = $number === 3 ? 'design_factor_3_a' : 'design_factor_'.$number;
            $id = DB::table($inputTable)->insertGetId(['assessment_id' => $assessment->assessment_id]);
            $scoreTable = 'design_factor_'.$number.'_score';
            DB::table($scoreTable)->insert(['assessment_id' => $assessment->assessment_id, 'df'.$number.'_id' => $id]);
            $relativeTable = 'design_factor_'.$number.'_relative_importance';
            DB::table($relativeTable)->insert(['assessment_id' => $assessment->assessment_id]);
            array_push($tables, $inputTable, $scoreTable, $relativeTable);
        }
        foreach (['design_factor_3_b', 'design_factor_3_c'] as $table) {
            DB::table($table)->insert(['assessment_id' => $assessment->assessment_id]);
            $tables[] = $table;
        }
        foreach ([2, 3] as $step) {
            DB::table('df_step'.$step)->insert(['assessment_id' => $assessment->assessment_id, 'user_id' => $this->editor->id, 'weights' => '[]']);
            DB::table('trs_step'.$step)->insert(['assessment_id' => $assessment->assessment_id, 'user_id' => $this->editor->id, 'objective_code' => 1]);
            array_push($tables, 'df_step'.$step, 'trs_step'.$step);
        }
        TrsStep4::create(['assessment_id' => $assessment->assessment_id, 'user_id' => $this->editor->id, 'objective_code' => 1, 'objective_id' => 'EDM01']);
        $tables[] = 'trs_step4';
        $assessment->accessAssignments()->create(['user_id' => $this->editor->id, 'access_profile' => 'df_editor']);

        return $tables;
    }

    private function assessment(int $organizationId, string $code): Assessment
    {
        return Assessment::create(['user_id' => $this->editor->id, 'organization_id' => $organizationId, 'instansi' => $organizationId === 1 ? 'Company Alpha' : 'Company Beta', 'kode_assessment' => $code]);
    }

    private function roadmap(?int $organizationId, int $year, int $level): void
    {
        TrsRoadmap::create(['organization_id' => $organizationId, 'focus_area_id' => 7, 'objective_id' => 'EDM01', 'year' => $year, 'level' => $level, 'rating' => $level.'F']);
    }

    private function seedCore(): void
    {
        DB::table('mst_focusarea')->insert([
            ['id' => 7, 'code' => 'CORE MODEL', 'name' => 'Core Model', 'version' => '2019'],
            ['id' => 8, 'code' => 'SECURITY', 'name' => 'Security', 'version' => '2019'],
            ['id' => 27, 'code' => 'COBIT41', 'name' => 'COBIT 4.1', 'version' => '4.1'],
        ]);
        foreach (app(CobitCoreModelService::class)->codes() as $code) {
            MstObjective::create(['objective_id' => $code, 'focus_area_id' => 7, 'objective' => 'Core '.$code]);
        }
        MstObjective::create(['objective_id' => 'EDM01.M8', 'focus_area_id' => 8, 'objective' => 'Security clone']);
        MstObjective::create(['objective_id' => 'AI01.M27', 'focus_area_id' => 27, 'objective' => 'COBIT 4 process']);
    }
}
