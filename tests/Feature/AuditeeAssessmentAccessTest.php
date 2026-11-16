<?php

namespace Tests\Feature;

use App\Models\MstAccessProfile;
use App\Models\MstEval;
use App\Models\MstOrganization;
use App\Models\User;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Eval\AssessmentManagementService;
use App\Services\Assessment\Eval\AssessmentDuplicationService;
use App\Services\Assessment\Eval\EvaluationReadService;
use App\Services\Auth\AccessProfilePermissionService;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditeeAssessmentAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use an isolated database; never run these checks against the configured application DB.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('access_profile')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->boolean('isActivated')->default(true);
            $table->timestamps();
        });
        Schema::create('mst_organization', function (Blueprint $table): void {
            $table->id('organization_id');
            $table->string('organization_name');
            $table->timestamps();
        });
        Schema::create('trs_userorganization', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->boolean('is_primary')->default(true);
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
        });
        Schema::create('mst_eval', function (Blueprint $table): void {
            $table->id('eval_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('tahun')->default('2026');
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('access_assignments', function (Blueprint $table): void {
            $table->id();
            $table->morphs('assignable');
            $table->unsignedBigInteger('user_id');
            $table->string('access_profile');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_04_02_034817_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_04_06_101500_create_access_profile_settings_tables.php'))->up();
        foreach (PermissionCatalog::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->withoutVite();
    }

    public function test_auditee_can_read_even_with_older_missing_view_permission(): void
    {
        $user = $this->user();
        $this->assertTrue($user->can(PermissionCatalog::AssessmentsView));
        $this->assertFalse($user->can(PermissionCatalog::AssessmentsInput));
    }

    public function test_auditee_cannot_write_even_with_an_older_direct_or_role_grant(): void
    {
        $user = $this->user();
        $user->givePermissionTo(PermissionCatalog::AssessmentsInput);
        $role = Role::findOrCreate('user', 'web');
        $role->givePermissionTo(PermissionCatalog::AssessmentsInput);
        $user->assignRole($role);

        $this->assertFalse($user->can(PermissionCatalog::AssessmentsInput));
        $this->actingAs($user);
        foreach ([
            ['post', '/assessment/create'],
            ['post', '/assessment/1/duplicate'],
            ['post', '/assessment/1/update-scope'],
            ['delete', '/assessment/delete-scope'],
            ['post', '/assessment/1/save'],
            ['delete', '/assessment/1'],
            ['post', '/assessment/1/finish'],
            ['post', '/assessment/1/unlock'],
            ['post', '/assessment/1/evidence'],
            ['put', '/assessment/evidence/1'],
            ['post', '/assessment/1/summary/save-note'],
            ['post', '/assessment/target-maturity'],
            ['delete', '/assessment/target-maturity/1'],
        ] as [$method, $url]) {
            $this->json($method, $url, [])->assertForbidden();
        }
    }

    public function test_pending_auditee_is_not_granted_assessment_access(): void
    {
        $user = $this->user(['organization_id' => null]);
        $this->assertFalse($user->can(PermissionCatalog::AssessmentsView));
        $this->actingAs($user)->getJson('/assessment/1/load')->assertForbidden();
    }

    public function test_admin_and_assessor_keep_their_existing_input_permissions(): void
    {
        foreach ([['role' => 'admin'], ['access_profile' => 'assessor']] as $attributes) {
            $user = $this->user($attributes);
            $user->givePermissionTo([PermissionCatalog::AssessmentsView, PermissionCatalog::AssessmentsInput]);
            $this->assertTrue($user->can(PermissionCatalog::AssessmentsView));
            $this->assertTrue($user->can(PermissionCatalog::AssessmentsInput));
        }
    }

    public function test_duplicate_requires_access_and_a_valid_year(): void
    {
        $assessor = $this->user(['access_profile' => 'assessor']);
        $assessor->givePermissionTo([PermissionCatalog::AssessmentsView, PermissionCatalog::AssessmentsInput]);
        $source = MstEval::create(['user_id' => $assessor->id, 'organization_id' => 1, 'tahun' => 2025]);
        $other = MstEval::create(['user_id' => $this->user(['organization_id' => 2])->id, 'organization_id' => 2]);
        $this->actingAs($assessor)->post('/assessment/'.$other->eval_id.'/duplicate', ['tahun' => 2026])->assertNotFound();
        $this->post('/assessment/'.$source->eval_id.'/duplicate', ['tahun' => 2100])->assertSessionHasErrors('tahun');

        $copy = MstEval::create(['user_id' => $assessor->id, 'organization_id' => 1, 'tahun' => 2026]);
        $this->mock(AssessmentDuplicationService::class)->shouldReceive('duplicate')->once()->andReturn($copy);
        $this->post('/assessment/'.$source->eval_id.'/duplicate', ['tahun' => 2026])
            ->assertRedirect()->assertSessionHas('success');
    }

    public function test_auditee_can_list_and_load_an_assessment_in_their_organization(): void
    {
        $user = $this->user();
        $evaluation = MstEval::create(['user_id' => $this->user()->id, 'organization_id' => 1]);
        $other = MstEval::create(['user_id' => $this->user(['organization_id' => 2])->id, 'organization_id' => 2]);
        $access = app(AssessmentAccessService::class);

        $this->assertSame([$evaluation->eval_id], $access->queryAccessible($user)->pluck('eval_id')->all());
        $this->assertTrue($access->canView($user, $evaluation));
        $this->assertFalse($access->canView($user, $other));
        $this->assertFalse($access->canManage($user, $evaluation));

        $this->mock(EvaluationReadService::class)->shouldReceive('getEvaluationById')
            ->andReturnUsing(fn ($id) => (int) $id === $evaluation->eval_id ? $evaluation : $other);
        $management = $this->mock(AssessmentManagementService::class);
        $management->shouldReceive('loadAssessment')->once()->andReturn(['notes' => ['1' => 'Existing note']]);
        $this->actingAs($user)->getJson('/assessment/'.$evaluation->eval_id.'/load')
            ->assertOk()->assertJsonPath('data.canManageAssessment', false)->assertJsonPath('data.notes.1', 'Existing note');
        $this->getJson('/assessment/'.$other->eval_id.'/load')->assertForbidden();

        $this->useMinimalLayout();
        $management->shouldReceive('buildShowData')->once()->andReturn([...$this->detailData(), 'evaluation' => $evaluation]);
        $this->get('/assessment/'.$evaluation->eval_id)->assertOk()->assertSee('Mode baca saja.');
    }

    public function test_read_only_owner_and_explicitly_assigned_auditee_can_still_view(): void
    {
        $user = $this->user();
        $owned = MstEval::create(['user_id' => $user->id, 'organization_id' => 2]);
        $assigned = MstEval::create(['user_id' => $this->user(['organization_id' => 2])->id, 'organization_id' => 2]);
        $assigned->accessAssignments()->create(['user_id' => $user->id, 'access_profile' => 'viewer']);
        $access = app(AssessmentAccessService::class);

        $this->assertTrue($access->canView($user, $owned));
        $this->assertTrue($access->canView($user, $assigned));
        $this->assertSame([$owned->eval_id, $assigned->eval_id], $access->queryAccessible($user)->orderBy('eval_id')->pluck('eval_id')->all());
    }

    public function test_gamo_excel_requires_assessment_access_and_valid_scope(): void
    {
        $user = $this->user();
        $evaluation = MstEval::create(['user_id' => $this->user()->id, 'organization_id' => 1]);
        $other = MstEval::create(['user_id' => $this->user(['organization_id' => 2])->id, 'organization_id' => 2]);
        $reader = $this->mock(EvaluationReadService::class);
        $reader->shouldReceive('getEvaluationById')->andReturnUsing(
            fn ($id) => (int) $id === $evaluation->eval_id ? $evaluation : $other
        );
        $management = $this->mock(AssessmentManagementService::class);
        $management->shouldReceive('buildShowData')->once()->with($evaluation, 99)->andReturn([
            'activeScope' => (object) ['id' => 5],
            'objectives' => collect([(object) ['objective_id' => 'EDM01']]),
        ]);

        $this->actingAs($user)->get('/assessment/'.$other->eval_id.'/gamo/excel')->assertNotFound();
        $this->get('/assessment/'.$evaluation->eval_id.'/gamo/excel?scope_id=99')->assertNotFound();
    }

    public function test_auditee_can_download_gamo_excel(): void
    {
        Schema::create('trs_activityeval', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('eval_id');
            $table->unsignedBigInteger('activity_id');
            $table->string('level_achieved')->nullable();
            $table->text('evidence')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $user = $this->user();
        $evaluation = MstEval::create(['user_id' => $this->user()->id, 'organization_id' => 1]);
        $objective = (new \App\Models\MstObjective)->forceFill(['objective_id' => 'EDM01', 'objective' => 'Governance']);
        $objective->setRelation('practices', collect());
        $second = (new \App\Models\MstObjective)->forceFill(['objective_id' => 'APO01', 'objective' => 'Management']);
        $second->setRelation('practices', collect());
        $this->mock(EvaluationReadService::class)->shouldReceive('getEvaluationById')->once()->andReturn($evaluation);
        $this->mock(AssessmentManagementService::class)->shouldReceive('buildShowData')->once()->with($evaluation, 5)->andReturn([
            'activeScope' => (object) ['id' => 5],
            'selectedDomains' => ['EDM01', 'APO01'],
            'objectives' => collect([$second, $objective]),
        ]);

        $response = $this->actingAs($user)->get('/assessment/'.$evaluation->eval_id.'/gamo/excel?scope_id=5');
        $response->assertOk()->assertDownload('Assessment-'.$evaluation->eval_id.'-Scope-5.xlsx');
        $path = tempnam(sys_get_temp_dir(), 'gamo-scope-');
        try {
            file_put_contents($path, $response->streamedContent());
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $this->assertSame(['EDM01', 'APO01'], $book->getSheetNames());
            $this->assertSame('EDM01 - Governance', $book->getSheet(0)->getCell('A1')->getValue());
            $this->assertSame('APO01 - Management', $book->getSheet(1)->getCell('A1')->getValue());
        } finally {
            @unlink($path);
        }
    }

    public function test_empty_scope_does_not_export_all_objectives(): void
    {
        $user = $this->user();
        $evaluation = MstEval::create(['user_id' => $user->id, 'organization_id' => 1]);
        $this->mock(EvaluationReadService::class)->shouldReceive('getEvaluationById')->twice()->andReturn($evaluation);
        $this->mock(AssessmentManagementService::class)->shouldReceive('buildShowData')->twice()->with($evaluation, 5)->andReturn([
            'activeScope' => (object) ['id' => 5],
            'selectedDomains' => [],
            'objectives' => collect([(object) ['objective_id' => 'EDM01']]),
        ]);

        $this->actingAs($user)->get('/assessment/'.$evaluation->eval_id.'/gamo/excel?scope_id=5')
            ->assertRedirect()->assertSessionHas('export_error');
        $this->getJson('/assessment/'.$evaluation->eval_id.'/gamo/excel?scope_id=5')
            ->assertStatus(422)->assertJsonPath('message', 'Scope belum memiliki GAMO untuk diekspor.');
    }

    public function test_permission_matrix_and_saved_auditee_profile_remain_read_only(): void
    {
        $profile = MstAccessProfile::where('access_profile_key', 'viewer')->firstOrFail();
        $profile->permissions()->delete();
        $profile->permissions()->create(['permission_name' => PermissionCatalog::AssessmentsInput]);
        $service = app(AccessProfilePermissionService::class);
        $this->assertSame([PermissionCatalog::AssessmentsView], $service->permissionsForProfile('viewer'));

        $service->replacePermissions($profile, [PermissionCatalog::CobitView, PermissionCatalog::AssessmentsInput]);
        $this->assertEqualsCanonicalizing([PermissionCatalog::CobitView, PermissionCatalog::AssessmentsView], $profile->permissions()->pluck('permission_name')->all());
    }

    public function test_read_only_detail_has_no_visible_edit_fields_or_mutation_buttons(): void
    {
        $this->useMinimalLayout();
        $data = $this->detailData();
        $html = view('assessment.assessmentDetail.index', $data)->render();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(0, $xpath->query('//select[not(@disabled)] | //textarea[not(@disabled)]')->length);
        foreach (['save-assessment', 'btn-finish-assessment', 'btn-unlock-assessment', 'editScopeModal', 'evidenceModal'] as $id) {
            $this->assertSame(0, $xpath->query('//*[@id="'.$id.'"]')->length);
        }
        $this->assertSame(0, $xpath->query('//button[contains(@class,"evidence-modal-trigger")]')->length);
        $this->assertStringContainsString('activity-rating-display', $html);
        $this->assertStringContainsString('activity-note-display', $html);
        $this->assertSame(1, $xpath->query('//*[@id="export-assessment-excel"]')->length);
        $this->assertSame(1, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " hero-header ")]//*[@id="export-assessment-excel"]')->length);

        $html = view('assessment.assessmentDetail.index', [...$data, 'canManageAssessment' => true])->render();
        $this->assertStringContainsString('id="save-assessment"', $html);
        $this->assertStringContainsString('evidence-modal-trigger', $html);
    }

    private function user(array $attributes = []): User
    {
        $user = User::create([...['name' => 'Test user', 'role' => 'user', 'access_profile' => 'viewer', 'organization_id' => 1], ...$attributes]);
        $user->isActivated = true;
        $user->setRelation('organizations', collect([(new MstOrganization)->forceFill(['organization_id' => $user->organization_id])]));

        return $user;
    }

    public function test_summary_preserves_saved_content_without_forms_or_an_editable_roadmap(): void
    {
        $this->useMinimalLayout();
        $data = $this->detailData();
        foreach ([
            'current_score' => 3,
            'max_level' => 4,
            'rating_string' => '3F',
            'target_level' => 4,
            'saved_note' => [
                'kesimpulan' => 'Existing conclusion',
                'rekomendasi' => 'Existing recommendation',
                'roadmap_rekomendasi' => ['cells' => [['Existing roadmap', '2027']]],
            ],
        ] as $key => $value) {
            $data['objectives']->first()->{$key} = $value;
        }
        $html = view('assessment.report.summary', $data)->render();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $this->assertStringContainsString('Existing conclusion', $html);
        $this->assertStringContainsString('Existing recommendation', $html);
        $this->assertStringContainsString('Existing roadmap', $html);
        $this->assertSame(0, $xpath->query('//form | //textarea | //*[@id="saveAllNotesBtn"] | //div[starts-with(@id,"roadmap-rekomendasi-container-")]')->length);
        $this->assertSame(2, $xpath->query('//a[@target="_blank"]')->length);

        $html = view('assessment.report.summary', [...$data, 'canManageAssessment' => true])->render();
        $this->assertStringContainsString('id="saveAllNotesBtn"', $html);
        $this->assertStringContainsString('name="kesimpulan"', $html);
    }

    public function test_evidence_library_and_target_maturity_hide_mutation_forms_for_auditee(): void
    {
        $this->useMinimalLayout();
        $this->actingAs($this->user());
        $data = [...$this->detailData(), 'evidences' => collect(), 'targets' => collect()];

        foreach (['assessment.assessmentDetail.evidence', 'design_factor.targetMaturity'] as $view) {
            $html = view($view, $data)->render();
            $dom = new \DOMDocument;
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            $this->assertSame(0, $xpath->query('//form | //*[@id="addEvidenceModal"] | //*[@id="importEvidenceModal"] | //*[@id="btn-add-new"] | //*[@id="btn-save-evidence"]')->length);
        }
    }

    private function useMinimalLayout(): void
    {
        view()->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
    }

    private function detailData(): array
    {
        $activity = new Fluent(['activity_id' => 1, 'capability_lvl' => 2, 'description' => 'Test activity']);
        $practice = new Fluent(['practice_id' => 'EDM01.01', 'practice_name' => 'Test practice', 'activities' => collect([$activity])]);
        $objective = new Fluent(['objective_id' => 'EDM01', 'objective' => 'Test governance', 'practices' => collect([$practice])]);

        return [
            'evalId' => 1,
            'evaluation' => (new MstEval)->forceFill(['eval_id' => 1, 'status' => 'draft', 'tahun' => '2026']),
            'activeScope' => null,
            'allScopes' => collect(),
            'allObjectives' => collect([$objective]),
            'objectives' => collect([$objective]),
            'canManageAssessment' => false,
        ];
    }
}
