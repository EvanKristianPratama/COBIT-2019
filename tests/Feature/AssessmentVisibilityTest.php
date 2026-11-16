<?php

namespace Tests\Feature;

use App\Models\MstEval;
use App\Models\User;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\List\AssessmentListService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssessmentVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');

        foreach ([
            'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, role TEXT, access_profile TEXT, organization_id INTEGER, organisasi TEXT, isActivated INTEGER DEFAULT 1, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE mst_organization (organization_id INTEGER PRIMARY KEY, organization_name TEXT, is_active INTEGER DEFAULT 1, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE trs_userorganization (user_id INTEGER, organization_id INTEGER, is_primary INTEGER, assigned_by INTEGER, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE mst_eval (eval_id INTEGER PRIMARY KEY, user_id INTEGER, organization_id INTEGER, status TEXT, tahun TEXT, created_at TEXT, updated_at TEXT, deleted_at TEXT)',
            'CREATE TABLE access_assignments (id INTEGER PRIMARY KEY, assignable_id INTEGER, assignable_type TEXT, user_id INTEGER, access_profile TEXT, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE trs_maturity_score (id INTEGER PRIMARY KEY, eval_id INTEGER, score REAL)',
            'CREATE TABLE trs_scoping (id INTEGER PRIMARY KEY, eval_id INTEGER, nama_scope TEXT, deleted_at TEXT)',
            'CREATE TABLE trs_objective_score (id INTEGER PRIMARY KEY, eval_id INTEGER, objective_id TEXT, level REAL)',
            'CREATE TABLE trs_activityeval (id INTEGER PRIMARY KEY, eval_id INTEGER, updated_at TEXT, deleted_at TEXT)',
            'CREATE TABLE mst_targetcap (target_id INTEGER PRIMARY KEY, user_id INTEGER, organization_id INTEGER, tahun TEXT)',
            'CREATE TABLE target_maturities (id INTEGER PRIMARY KEY, user_id INTEGER, organization_id INTEGER, tahun TEXT, target_maturity REAL)',
        ] as $sql) {
            DB::statement($sql);
        }
        (require database_path('migrations/2026_11_09_000001_add_show_on_user_page_to_mst_eval.php'))->up();
        (require database_path('migrations/2026_04_02_034817_create_permission_tables.php'))->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->withoutVite();
        DB::table('mst_organization')->insert(['organization_id' => 1, 'organization_name' => 'Organisasi A']);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin', 'access_profile' => null, 'organization_id' => 1],
            ['id' => 2, 'name' => 'User', 'email' => 'user@example.com', 'role' => 'user', 'access_profile' => 'viewer', 'organization_id' => 1],
            ['id' => 3, 'name' => 'Owner', 'email' => 'owner@example.com', 'role' => 'user', 'access_profile' => 'viewer', 'organization_id' => 1],
        ]);
        DB::table('mst_eval')->insert([
            ['eval_id' => 1, 'user_id' => 2, 'organization_id' => 1, 'status' => 'draft', 'tahun' => '2025'],
            ['eval_id' => 2, 'user_id' => 3, 'organization_id' => 1, 'status' => 'finished', 'tahun' => '2025'],
            ['eval_id' => 3, 'user_id' => 3, 'organization_id' => 1, 'status' => 'draft', 'tahun' => '2025'],
        ]);
    }

    public function test_admin_can_save_visible_checkboxes_and_restore_hidden_assessments(): void
    {
        $this->actingAs($this->admin())->from('/admin/assessments?organization_id=1&page=2')
            ->put(route('admin.assessments.visibility'), ['assessment_ids' => [1, 2], 'visible_ids' => [2]])
            ->assertRedirect('/admin/assessments?organization_id=1&page=2')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mst_eval', ['eval_id' => 1, 'show_on_user_page' => false]);
        $this->assertDatabaseHas('mst_eval', ['eval_id' => 2, 'show_on_user_page' => true]);
        $this->assertDatabaseHas('mst_eval', ['eval_id' => 3, 'show_on_user_page' => true]);

        $this->put(route('admin.assessments.visibility'), ['assessment_ids' => [1], 'visible_ids' => [1]])->assertRedirect();
        $this->assertDatabaseHas('mst_eval', ['eval_id' => 1, 'show_on_user_page' => true]);
        $this->put(route('admin.assessments.visibility'), ['assessment_ids' => [1]])->assertRedirect();
        $this->assertDatabaseHas('mst_eval', ['eval_id' => 1, 'show_on_user_page' => false]);
    }

    public function test_user_list_and_totals_include_only_visible_assessments(): void
    {
        DB::table('mst_eval')->where('eval_id', 2)->update(['show_on_user_page' => false]);
        $data = app(AssessmentListService::class)->getIndexData(User::findOrFail(2));

        $this->assertSame([1], $data['myAssessments']->pluck('eval_id')->all());
        $this->assertSame([3], $data['assignedAssessments']->pluck('eval_id')->all());
        $this->assertSame(2, $data['totalAssessments']);
        $this->assertSame(0, $data['finishedAssessments']);
        $this->assertSame(2, $data['draftAssessments']);
        $this->assertNotNull(MstEval::find(2));
        $this->assertTrue(app(AssessmentAccessService::class)->canView(User::findOrFail(2), MstEval::findOrFail(2)));
    }

    public function test_hiding_all_assessments_produces_empty_lists_and_zero_totals(): void
    {
        DB::table('mst_eval')->update(['show_on_user_page' => false]);
        $data = app(AssessmentListService::class)->getIndexData(User::findOrFail(2));

        $this->assertSame(0, $data['myAssessments']->total());
        $this->assertSame(0, $data['assignedAssessments']->total());
        $this->assertSame(0, $data['totalAssessments']);
        $this->assertSame(0, $data['finishedAssessments']);
        $this->assertSame(0, $data['draftAssessments']);
    }

    public function test_user_list_works_before_the_visibility_column_is_installed(): void
    {
        Schema::table('mst_eval', fn ($table) => $table->dropColumn('show_on_user_page'));

        $data = app(AssessmentListService::class)->getIndexData(User::findOrFail(2));

        $this->assertSame([1], $data['myAssessments']->pluck('eval_id')->all());
        $this->assertEqualsCanonicalizing([2, 3], $data['assignedAssessments']->pluck('eval_id')->all());
        $this->assertSame(3, $data['totalAssessments']);
    }

    public function test_admin_directory_displays_each_checklist_and_keeps_hidden_rows_manageable(): void
    {
        DB::table('mst_eval')->where('eval_id', 2)->update(['show_on_user_page' => false]);

        $this->actingAs($this->admin())->get('/admin/assessments')
            ->assertOk()
            ->assertSee('Eval #1')
            ->assertSee('Eval #2')
            ->assertSee('Simpan tampilan')
            ->assertSee('name="assessment_ids[]"', false)
            ->assertSee('id="show-evaluation-2"', false);
    }

    public function test_non_admin_cannot_change_visibility_and_invalid_selection_is_rejected(): void
    {
        $this->actingAs(User::findOrFail(2))->put(route('admin.assessments.visibility'), ['assessment_ids' => [1]])->assertForbidden();
        $this->assertDatabaseHas('mst_eval', ['eval_id' => 1, 'show_on_user_page' => true]);

        $this->actingAs($this->admin())->from('/admin/assessments')
            ->put(route('admin.assessments.visibility'), ['assessment_ids' => [1], 'visible_ids' => [2]])
            ->assertSessionHasErrors('visible_ids');
        $this->assertDatabaseHas('mst_eval', ['eval_id' => 1, 'show_on_user_page' => true]);
    }

    private function admin(): User
    {
        $admin = User::findOrFail(1);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        return $admin;
    }
}
