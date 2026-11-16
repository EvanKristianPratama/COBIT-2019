<?php

namespace Tests\Feature;

use App\Services\Cobit4\Cobit4WorkProductService;
use App\Services\Cobit4\Cobit4Service;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Cobit4WorkProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
        ]);
        DB::purge('sqlite');
    }

    public function test_missing_or_partially_imported_catalog_returns_no_rows(): void
    {
        $catalog = app(Cobit4WorkProductService::class);
        $this->assertTrue($catalog->forProcess('PO1', 27)->isEmpty());

        DB::statement('CREATE TABLE `mst_wp_4.1` (wp_id TEXT PRIMARY KEY)');
        $this->assertTrue($catalog->forProcess('PO1', 27)->isEmpty());
    }

    public function test_work_products_follow_relationships_process_focus_area_and_document_order(): void
    {
        DB::statement('CREATE TABLE mst_cobit4_processes (id INTEGER PRIMARY KEY, code TEXT, focus_area_id INTEGER)');
        DB::statement('CREATE TABLE `mst_wp_4.1` (wp_id TEXT PRIMARY KEY, wp TEXT, description TEXT, order_no INTEGER)');
        DB::statement('CREATE TABLE `trs_wp_to_obj_4.1` (process_id INTEGER, wp_id TEXT)');
        DB::table('mst_cobit4_processes')->insert([
            ['id' => 1, 'code' => 'PO1', 'focus_area_id' => 27],
            ['id' => 2, 'code' => 'PO2', 'focus_area_id' => 27],
            ['id' => 3, 'code' => 'PO1', 'focus_area_id' => 28],
            ['id' => 4, 'code' => 'AI1', 'focus_area_id' => 27],
        ]);
        foreach ([['PO1- WP2', 2], ['PO1- WP1', 1], ['PO2-WP1', 3], ['PO1-WP3', 4], ['AI1-WP1', 5], ['PO1-WP4', 6]] as [$id, $order]) {
            DB::insert('INSERT INTO `mst_wp_4.1` VALUES (?, ?, ?, ?)', [$id, $id.' title', 'Description', $order]);
        }
        foreach ([[1, 'PO1- WP2'], [1, 'PO1- WP1'], [2, 'PO2-WP1'], [3, 'PO1-WP3'], [4, 'AI1-WP1']] as [$process, $wp]) {
            DB::insert('INSERT INTO `trs_wp_to_obj_4.1` VALUES (?, ?)', [$process, $wp]);
        }

        $catalog = app(Cobit4WorkProductService::class);
        $this->assertSame(['PO1- WP1', 'PO1- WP2'], $catalog->forProcess(' po1 ', 27)->pluck('wp_id')->all());
        $this->assertSame(['PO2-WP1'], $catalog->forProcess('PO2', 27)->pluck('wp_id')->all());
        $this->assertSame(['PO1-WP3'], $catalog->forProcess('PO1', 28)->pluck('wp_id')->all());
        $this->assertSame(['AI1-WP1'], $catalog->forProcess('AI1', 27)->pluck('wp_id')->all());
        $this->assertTrue($catalog->forProcess('ME1', 27)->isEmpty());
    }

    public function test_document_table_preserves_scope_notes_and_escapes_stored_content(): void
    {
        $html = view('cobit_component.cobit4_1.partials.work-products', [
            'processCode' => 'PO2',
            'workProducts' => collect([(object) [
                'wp_id' => 'PO2-WP3',
                'wp' => 'Data dictionary <script>alert(1)</script>',
                'description' => "Database definition.\n\nScope note:  Documentation & management.",
            ]]),
        ])->render();

        $this->assertStringNotContainsString('Figure 17—Level 1 Output Work Products (WPs)', $html);
        $this->assertStringContainsString('<th scope="col">WP ID</th>', $html);
        $this->assertStringContainsString('<strong>Scope note:</strong>', $html);
        $this->assertStringContainsString('Documentation &amp; management.', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_empty_document_table_identifies_the_current_process(): void
    {
        $html = view('cobit_component.cobit4_1.partials.work-products', [
            'processCode' => 'DS1',
            'workProducts' => collect(),
        ])->render();

        $this->assertStringContainsString('Belum ada Work Product untuk proses DS1.', $html);
        $this->assertStringContainsString('colspan="3"', $html);
    }

    public function test_empty_process_can_save_and_reload_its_first_work_product(): void
    {
        $catalog = $this->writableCatalog();
        $saved = $catalog->synchronize('AI1', 27, [$this->newRow()], $catalog->revision(collect()));
        $this->assertSame('AI1-WP1', $saved->first()->wp_id);
        $this->assertSame('New work product', $catalog->forProcess('AI1', 27)->first()->wp);
        $this->assertSame(1, DB::table(DB::raw('`trs_wp_to_obj_4.1`'))->where('process_id', 1)->count());
    }

    public function test_existing_id_name_and_description_can_be_edited_with_shared_links_preserved(): void
    {
        $catalog = $this->writableCatalog();
        $saved = $catalog->synchronize('AI1', 27, [$this->newRow()], $catalog->revision(collect()));
        DB::insert('INSERT INTO `trs_wp_to_obj_4.1` (process_id, wp_id) VALUES (3, ?)', ['AI1-WP1']);
        $catalog->synchronize('AI1', 27, [[
            'original_wp_id' => 'AI1-WP1', 'wp_id' => 'AI1-WP9', 'wp' => 'Updated title', 'description' => "Updated description\n\nScope note: Retained.",
        ]], $catalog->revision($saved));
        $this->assertSame('AI1-WP9', $catalog->forProcess('AI1', 27)->first()->wp_id);
        $this->assertSame('Updated title', $catalog->forProcess('AI1', 28)->first()->wp);
        $this->assertSame(2, DB::table(DB::raw('`trs_wp_to_obj_4.1`'))->where('wp_id', 'AI1-WP9')->count());
        $this->assertStringContainsString('Scope note:', $catalog->forProcess('AI1', 27)->first()->description);
    }

    public function test_duplicate_ids_wrong_processes_and_foreign_originals_are_rejected_atomically(): void
    {
        $catalog = $this->writableCatalog();
        foreach ([
            [$this->newRow(), $this->newRow()],
            [array_replace($this->newRow(), ['wp_id' => 'PO2-WP1'])],
            [array_replace($this->newRow(), ['original_wp_id' => 'PO2-WP1'])],
            [$this->newRow(), array_replace($this->newRow(), ['wp_id' => 'AI1- WP1'])],
        ] as $rows) {
            try {
                $catalog->synchronize('AI1', 27, $rows, $catalog->revision(collect()));
                $this->fail('Invalid work products were accepted.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
                $this->assertSame(0, DB::table(DB::raw('`mst_wp_4.1`'))->count());
            }
        }
    }

    public function test_removal_only_unlinks_the_current_process_and_removes_unused_master_rows(): void
    {
        $catalog = $this->writableCatalog();
        $saved = $catalog->synchronize('AI1', 27, [$this->newRow(), array_replace($this->newRow(), ['wp_id' => 'AI1-WP2'])], $catalog->revision(collect()));
        DB::insert('INSERT INTO `trs_wp_to_obj_4.1` (process_id, wp_id) VALUES (3, ?)', ['AI1-WP1']);
        $catalog->synchronize('AI1', 27, [], $catalog->revision($saved));
        $this->assertTrue($catalog->forProcess('AI1', 27)->isEmpty());
        $this->assertSame('AI1-WP1', $catalog->forProcess('AI1', 28)->first()->wp_id);
        $this->assertSame(1, DB::table(DB::raw('`mst_wp_4.1`'))->count());
    }

    public function test_stale_edits_cannot_overwrite_newer_work_products(): void
    {
        $catalog = $this->writableCatalog();
        $revision = $catalog->revision(collect());
        $catalog->synchronize('AI1', 27, [$this->newRow()], $revision);
        try {
            $catalog->synchronize('AI1', 27, [], $revision);
            $this->fail('Stale revision was accepted.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $this->assertCount(1, $catalog->forProcess('AI1', 27));
    }

    public function test_wp_save_requires_input_permission_and_valid_fields(): void
    {
        $catalog = $this->writableCatalog();
        $this->editor(false);
        $payload = $this->payload($catalog, [$this->newRow()]);
        $this->postJson(route('cobit4.save'), $payload)->assertForbidden();
        $this->editor(true);
        $payload['data']['work_products'][0]['wp'] = '';
        $this->postJson(route('cobit4.save'), $payload)->assertUnprocessable();
        $this->assertTrue($catalog->forProcess('AI1', 27)->isEmpty());
    }

    public function test_existing_save_endpoint_saves_wp_and_rolls_it_back_if_process_save_fails(): void
    {
        $catalog = $this->writableCatalog();
        $this->editor(true);
        $this->mock(Cobit4Service::class, fn ($mock) => $mock->shouldReceive('saveProcessData')->once()->andReturn(true));
        $this->postJson(route('cobit4.save'), $this->payload($catalog, [$this->newRow()]))
            ->assertOk()->assertJsonPath('work_products.0.wp_id', 'AI1-WP1');
        $this->assertCount(1, $catalog->forProcess('AI1', 27));
        $this->mock(Cobit4Service::class, fn ($mock) => $mock->shouldReceive('saveProcessData')->once()->andThrow(new \RuntimeException('Process save failed')));
        $this->postJson(route('cobit4.save'), $this->payload($catalog, []))->assertStatus(500);
        $this->assertCount(1, $catalog->forProcess('AI1', 27));
    }

    public function test_wp_only_save_does_not_rebuild_other_process_components(): void
    {
        $catalog = $this->writableCatalog();
        $this->editor(true);
        $this->mock(Cobit4Service::class, fn ($mock) => $mock->shouldNotReceive('saveProcessData'));
        $this->postJson(route('cobit4.save'), $this->payload($catalog, [$this->newRow()]) + ['work_products_only' => true])
            ->assertOk()->assertJsonPath('work_products.0.wp', 'New work product');
        $this->assertCount(1, $catalog->forProcess('AI1', 27));
    }

    private function newRow(): array
    {
        return ['original_wp_id' => null, 'wp_id' => 'AI1-WP1', 'wp' => 'New work product', 'description' => 'Description'];
    }

    private function payload(Cobit4WorkProductService $catalog, array $rows): array
    {
        return ['process_code' => 'AI1', 'objective_id' => 'AI01.M27', 'focus_area_id' => 27,
            'data' => ['title' => 'AI1', 'work_products' => $rows, 'work_products_revision' => $catalog->revision($catalog->forProcess('AI1', 27))]];
    }

    private function writableCatalog(): Cobit4WorkProductService
    {
        DB::statement('CREATE TABLE mst_focusarea (id INTEGER PRIMARY KEY, version TEXT)');
        DB::statement("INSERT INTO mst_focusarea VALUES (27, '4.1'), (28, '4.1')");
        DB::statement('CREATE TABLE mst_cobit4_processes (id INTEGER PRIMARY KEY, code TEXT, focus_area_id INTEGER)');
        DB::statement("INSERT INTO mst_cobit4_processes VALUES (1, 'AI1', 27), (2, 'PO2', 27), (3, 'AI1', 28)");
        DB::statement('CREATE TABLE `mst_wp_4.1` (wp_id TEXT PRIMARY KEY, wp TEXT, description TEXT, order_no INTEGER, created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE `trs_wp_to_obj_4.1` (process_id INTEGER, wp_id TEXT REFERENCES `mst_wp_4.1`(wp_id) ON DELETE CASCADE ON UPDATE CASCADE, created_at TEXT, updated_at TEXT, PRIMARY KEY (process_id, wp_id))');

        return app(Cobit4WorkProductService::class);
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
        $user = User::create(['name' => 'Editor', 'role' => 'user', 'access_profile' => 'assessor', 'organization_id' => 1]);
        $user->isActivated = true;
        $user->givePermissionTo($canEdit ? ['cobit.view', 'design-factors.input'] : ['cobit.view']);
        $this->actingAs($user);
    }
}
