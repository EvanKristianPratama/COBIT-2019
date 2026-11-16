<?php

namespace Tests\Feature;

use App\Models\MstEvidenceRef;
use App\Models\MstObjective;
use App\Models\User;
use App\Services\Cobit5\WorkProductService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class Cobit5WorkProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'session.driver' => 'array']);
        DB::purge('sqlite');
        DB::statement('CREATE TABLE mst_focusarea (id INTEGER PRIMARY KEY, version TEXT)');
        DB::statement("INSERT INTO mst_focusarea VALUES (25,'5'), (26,'5'), (1,'2019'), (27,'4.1')");
        DB::statement('CREATE TABLE mst_objective (objective_id TEXT PRIMARY KEY, focus_area_id INTEGER)');
        foreach ([['EDM01.M25', 25], ['EDM02.M25', 25], ['EDM01.M26', 26], ['EDM01', 1], ['PO1.M27', 27]] as [$id,$focus]) {
            DB::table('mst_objective')->insert(['objective_id' => $id, 'focus_area_id' => $focus]);
        }
        DB::statement('CREATE TABLE mst_evidence_ref (id INTEGER PRIMARY KEY AUTOINCREMENT, focus_area_id INTEGER, objective_id TEXT, nama_dokumen TEXT, keterangan TEXT, referensi_sumber TEXT, is_active INTEGER DEFAULT 1, created_at TEXT, updated_at TEXT, UNIQUE(focus_area_id,objective_id,nama_dokumen))');
        DB::statement('CREATE TABLE trs_ref_evidence (evidence_ref_id INTEGER, evidence_id INTEGER)');
    }

    public function test_source_contains_all_434_work_products_in_document_order(): void
    {
        $handle = fopen(database_path('data/cobit5-work-products.tsv'), 'r');
        $this->assertSame(['wp_id', 'wp', 'description'], fgetcsv($handle, 0, "\t", '"', ''));
        $rows = [];
        while (($row = fgetcsv($handle, 0, "\t", '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        $this->assertCount(434, $rows);
        $this->assertCount(434, array_unique(array_column($rows, 0)));
        $counts = ['EDM' => [6, 6, 8, 8, 5], 'APO' => [16, 14, 12, 12, 12, 13, 15, 9, 10, 13, 14, 15, 6], 'BAI' => [33, 9, 18, 10, 17, 6, 13, 7, 12, 10], 'DSS' => [10, 14, 11, 18, 14, 11], 'MEA' => [7, 12, 8]];
        $expected = [];
        foreach ($counts as $domain => $processes) {
            foreach ($processes as $index => $count) {
                for ($number = 1; $number <= $count; $number++) {
                    $expected[] = sprintf('%s%02d-WP%d', $domain, $index + 1, $number);
                }
            }
        }
        $this->assertSame($expected, array_column($rows, 0));
        $byId = array_column($rows, null, 0);
        $this->assertSame('Authority levels', $byId['EDM01-WP3'][1]);
        $this->assertSame('Self explanatory and part of the normal PMO setup and process', $byId['BAI01-WP20'][2]);
        $this->assertSame('See data retention polices or operating practices.', $byId['DSS06-WP9'][2]);
        $this->assertStringContainsString('‘as needed’', $byId['MEA03-WP3'][2]);
    }

    public function test_catalog_is_scoped_to_version_model_process_and_active_source_with_numeric_wp_order(): void
    {
        $this->reference('EDM01-WP10');
        $this->reference('EDM01-WP2');
        $this->reference('EDM01-WP1', 26, 'EDM01.M26');
        $this->reference('EDM02-WP1', 25, 'EDM02.M25');
        $inactive = $this->reference('EDM01-WP3');
        $inactive->update(['is_active' => false]);
        MstEvidenceRef::create(['focus_area_id' => 25, 'objective_id' => 'EDM01.M25', 'nama_dokumen' => 'Custom reference', 'referensi_sumber' => 'Custom', 'is_active' => true]);
        $rows = app(WorkProductService::class)->forObjective($this->objective());
        $this->assertSame(['EDM01-WP2', 'EDM01-WP10'], $rows->pluck('wp_id')->all());
        $this->assertSame($this->referenceRevision($rows->first()->id), $rows->first()->revision);
    }

    public function test_existing_master_can_add_and_edit_wp_without_changing_reference_ids_or_evidence_links(): void
    {
        $this->editor(true);
        $this->post($this->url(), $this->payload())->assertRedirect();
        $row = MstEvidenceRef::firstOrFail();
        DB::table('trs_ref_evidence')->insert(['evidence_ref_id' => $row->id, 'evidence_id' => 900]);
        $this->post($this->url(), $this->payload() + ['reference_id' => $row->id, 'revision' => $this->referenceRevision($row->id)])
            ->assertRedirect(route('cobit_component.show', ['id' => 'EDM01.M25', 'focus_area' => 25]).'#c5-work-products');
        $payload = array_replace($this->payload(), ['wp_id' => 'EDM01-WP9', 'wp' => 'Updated title', 'description' => 'Updated description', 'reference_id' => $row->id, 'revision' => $this->referenceRevision($row->id)]);
        $this->post($this->url(), $payload)->assertRedirect();
        $this->assertSame(1, MstEvidenceRef::count());
        $this->assertSame('Updated title', $row->fresh()->nama_dokumen);
        $this->assertSame(WorkProductService::SOURCE_PREFIX.'EDM01-WP9', $row->fresh()->referensi_sumber);
        $this->assertSame($row->id, DB::table('trs_ref_evidence')->value('evidence_ref_id'));
    }

    public function test_writes_require_both_input_and_cobit_permissions(): void
    {
        $this->postJson($this->url(), $this->payload())->assertUnauthorized();
        $this->editor(false);
        $this->postJson($this->url(), $this->payload())->assertForbidden();
        $this->assertSame(0, MstEvidenceRef::count());
    }

    public function test_wrong_version_model_reference_process_prefix_and_duplicate_wp_ids_are_rejected(): void
    {
        $this->editor(true);
        $this->postJson($this->url(), array_replace($this->payload(), ['wp_id' => 'EDM02-WP1']))->assertUnprocessable();
        $this->postJson(route('cobit5.work-products.save', ['objectiveId' => 'EDM01']), array_replace($this->payload(), ['focus_area_id' => 1]))->assertNotFound();
        $foreign = $this->reference('EDM01-WP1', 26, 'EDM01.M26');
        $this->postJson($this->url(), $this->payload() + ['reference_id' => $foreign->id])->assertNotFound();
        $existing = $this->reference('EDM01-WP1');
        $this->postJson($this->url(), array_replace($this->payload(), ['wp' => 'Different title']))->assertUnprocessable();
        $this->assertSame(2, MstEvidenceRef::count());
        $this->assertSame('EDM01-WP1 title', $existing->fresh()->nama_dokumen);
    }

    public function test_stale_edits_cannot_overwrite_newer_data(): void
    {
        $this->editor(true);
        $row = $this->reference('EDM01-WP1');
        $revision = $this->referenceRevision($row->id);
        $row->update(['keterangan' => 'Newer description']);
        $this->postJson($this->url(), $this->payload() + ['reference_id' => $row->id, 'revision' => $revision])->assertStatus(409);
        $this->assertSame('Newer description', $row->fresh()->keterangan);
    }

    public function test_document_table_escapes_text_and_read_only_users_have_no_editor(): void
    {
        $row = $this->reference('EDM01-WP1');
        $row->update(['nama_dokumen' => '<script>name</script>', 'keterangan' => '<b>description</b>']);
        $this->editor(false);
        $html = view('cobit_component.cobit5.partials.work-products', ['objective' => $this->objective(), 'focusAreaId' => 25, 'workProducts' => app(WorkProductService::class)->forObjective($this->objective())])->render();
        $this->assertStringContainsString('&lt;script&gt;name&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;b&gt;description&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('data-c5-wp-edit', $html);
        $this->assertStringNotContainsString('name="wp_id"', $html);
        $this->editor(true);
        $html = view('cobit_component.cobit5.partials.work-products', ['objective' => $this->objective(), 'focusAreaId' => 25, 'workProducts' => app(WorkProductService::class)->forObjective($this->objective())])->render();
        $this->assertStringContainsString('data-c5-wp-edit', $html);
        $this->assertStringContainsString('name="revision"', $html);
    }

    private function objective(): MstObjective
    {
        return MstObjective::with('focusArea')->findOrFail('EDM01.M25');
    }

    private function referenceRevision(int $id): string
    {
        return app(WorkProductService::class)->revision(MstEvidenceRef::findOrFail($id));
    }

    private function url(): string
    {
        return route('cobit5.work-products.save', ['objectiveId' => 'EDM01.M25']);
    }

    private function payload(): array
    {
        return ['focus_area_id' => 25, 'wp_id' => 'EDM01-WP1', 'wp' => 'Enterprise governance guiding principles', 'description' => 'Source description'];
    }

    private function reference(string $id, int $focus = 25, string $objective = 'EDM01.M25'): MstEvidenceRef
    {
        return MstEvidenceRef::create(['focus_area_id' => $focus, 'objective_id' => $objective, 'nama_dokumen' => $id.' title', 'keterangan' => $id.' description', 'referensi_sumber' => WorkProductService::SOURCE_PREFIX.$id, 'is_active' => true]);
    }

    private function editor(bool $canEdit): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
            DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, access_profile TEXT, organization_id INTEGER, isActivated INTEGER DEFAULT 1, created_at TEXT, updated_at TEXT)');
            (require database_path('migrations/2026_04_02_034817_create_permission_tables.php'))->up();
            Permission::findOrCreate('cobit.view', 'web');
            Permission::findOrCreate('design-factors.input', 'web');
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
        $user = User::create(['name' => 'WP user', 'role' => 'user', 'access_profile' => 'assessor', 'organization_id' => 1]);
        $user->isActivated = true;
        $user->givePermissionTo($canEdit ? ['cobit.view', 'design-factors.input'] : ['cobit.view']);
        $this->actingAs($user);
    }
}
