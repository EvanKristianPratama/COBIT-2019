<?php

namespace Tests\Feature;

use App\Models\MstEvidenceRef;
use App\Models\MstObjective;
use App\Models\User;
use App\Services\Cobit5\DocumentService;
use App\Services\Cobit5\WorkProductService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class Cobit5DocumentEditingTest extends TestCase
{
    private string $practice = '"EDM01.01".M25';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'session.driver' => 'array']);
        DB::purge('sqlite');
        foreach ([
            'CREATE TABLE mst_focusarea (id INTEGER PRIMARY KEY, name TEXT, version TEXT)',
            'CREATE TABLE mst_objective (objective_id TEXT PRIMARY KEY, focus_area_id INTEGER, objective TEXT, objective_description TEXT, objective_purpose TEXT, cobit5_document TEXT)',
            'CREATE TABLE mst_practice (practice_id TEXT PRIMARY KEY, objective_id TEXT, focus_area_id INTEGER, practice_name TEXT, practice_description TEXT)',
            'CREATE TABLE mst_infoflowinput (input_id INTEGER PRIMARY KEY, practice_id TEXT, "from" TEXT, description TEXT)',
            'CREATE TABLE mst_infoflowoutput (output_id INTEGER PRIMARY KEY, practice_id TEXT, "to" TEXT, description TEXT, work_product_id INTEGER)',
            'CREATE TABLE trs_infoflowio (input_id INTEGER, output_id INTEGER)',
            'CREATE TABLE mst_evidence_ref (id INTEGER PRIMARY KEY AUTOINCREMENT, focus_area_id INTEGER, objective_id TEXT, nama_dokumen TEXT, keterangan TEXT, referensi_sumber TEXT, is_active INTEGER, created_at TEXT, updated_at TEXT)',
        ] as $sql) {
            DB::statement($sql);
        }
        foreach ([[25, '5'], [26, '5'], [1, '2019']] as [$id,$version]) {
            DB::table('mst_focusarea')->insert(['id' => $id, 'name' => 'Model '.$id, 'version' => $version]);
        }
        foreach ([['EDM01.M25', 25], ['EDM02.M25', 25], ['EDM01.M26', 26], ['EDM01', 1]] as [$id,$focus]) {
            DB::table('mst_objective')->insert([
                'objective_id' => $id, 'focus_area_id' => $focus, 'objective' => 'Stored '.$id, 'objective_description' => 'Stored description', 'objective_purpose' => 'Stored purpose',
            ]);
        }
        foreach ([[$this->practice, 'EDM01.M25', 25], ['"EDM01.02".M25', 'EDM01.M25', 25], ['"EDM01.03".M25', 'EDM01.M25', 25], ['EDM01.01', 'EDM01', 1], ['EDM01.01.M26', 'EDM01.M26', 26]] as [$id,$objective,$focus]) {
            DB::table('mst_practice')->insert([
                'practice_id' => $id, 'objective_id' => $objective, 'focus_area_id' => $focus, 'practice_name' => 'Practice', 'practice_description' => 'Description',
            ]);
        }
        DB::table('mst_infoflowinput')->insert([
            ['input_id' => 101, 'practice_id' => $this->practice, 'from' => 'MEA03.02', 'description' => 'Stored input'],
            ['input_id' => 102, 'practice_id' => $this->practice, 'from' => 'Outside COBIT', 'description' => 'Stored external input'],
            ['input_id' => 900, 'practice_id' => 'EDM01.01.M26', 'from' => 'Foreign', 'description' => 'Foreign input'],
        ]);
        DB::table('mst_infoflowoutput')->insert([
            ['output_id' => 301, 'practice_id' => $this->practice, 'to' => 'All EDM', 'description' => 'Enterprise governance guiding principles'],
            ['output_id' => 302, 'practice_id' => $this->practice, 'to' => 'APO01.01', 'description' => "Decision-making model\nAuthority levels"],
            ['output_id' => 900, 'practice_id' => 'EDM01.01', 'to' => 'Outside COBIT', 'description' => '2019 output'],
        ]);
        DB::table('trs_infoflowio')->insert(['input_id' => 101, 'output_id' => 301]);
        $rows = array_slice(array_map(fn ($line) => str_getcsv($line, "\t", '"', ''), file(database_path('data/cobit5-work-products.tsv'), FILE_IGNORE_NEW_LINES)), 1, 6);
        foreach ($rows as [$id,$name,$description]) {
            MstEvidenceRef::create([
                'focus_area_id' => 25, 'objective_id' => 'EDM01.M25', 'nama_dokumen' => $name, 'keterangan' => $description,
                'referensi_sumber' => WorkProductService::SOURCE_PREFIX.$id, 'is_active' => true,
            ]);
        }
        foreach ([[7, 25, 'EDM02.M25', true], [8, 26, 'EDM01.M26', true], [9, 25, 'EDM01.M25', false]] as [$id,$focus,$objective,$active]) {
            MstEvidenceRef::create([
                'id' => $id, 'focus_area_id' => $focus, 'objective_id' => $objective, 'nama_dokumen' => 'Other WP '.$id,
                'referensi_sumber' => WorkProductService::SOURCE_PREFIX.'EDM01-WP'.$id, 'is_active' => $active,
            ]);
        }
    }

    public function test_overview_edits_persist_to_database_and_are_reloaded_without_modifying_other_models_or_reference_file(): void
    {
        $this->editor();
        $file = hash_file('sha256', resource_path('data/cobit5/edm01.json'));
        $payload = $this->overviewPayload();
        $payload['title'] = 'Updated governance process';
        $payload['description'] = 'Updated COBIT 5 description';
        $payload['it_goals'][0]['metrics'] = ['Updated metric'];
        $payload['process_goals'] = [['title' => 'Updated process goal', 'metrics' => ['Process metric']]];
        $this->putJson($this->overviewUrl(), $payload)->assertOk();
        $document = $this->document();
        $this->assertSame('Updated governance process', $document['title']);
        $this->assertSame('Updated COBIT 5 description', $document['description']);
        $this->assertSame(['Updated metric'], $document['it_goals'][0]['metrics']);
        $this->assertSame('Updated process goal', $document['process_goals'][0]['title']);
        $this->assertSame('Updated COBIT 5 description', MstObjective::find('EDM01.M25')->objective_description);
        $this->assertSame('Stored description', MstObjective::find('EDM01')->objective_description);
        $this->assertSame('Stored description', MstObjective::find('EDM01.M26')->objective_description);
        $this->assertSame($file, hash_file('sha256', resource_path('data/cobit5/edm01.json')));
    }

    public function test_information_flow_edits_keep_existing_ids_and_links_and_bind_outputs_to_master_wp(): void
    {
        $this->editor();
        $payload = $this->flowPayload();
        $payload['inputs'][0]['from'] = 'MEA03.03';
        $payload['inputs'][0]['description'] = "• First item\n• Second item";
        $payload['outputs'][0]['work_product_id'] = 2;
        $payload['outputs'][0]['description'] = 'Forged free-text output';
        $payload['outputs'][0]['to'] = "All EDM\nAPO01.01";
        $this->putJson($this->flowUrl(), $payload)->assertOk();
        $this->assertSame('MEA03.03', DB::table('mst_infoflowinput')->where('input_id', 101)->value('from'));
        $this->assertSame(2, DB::table('mst_infoflowoutput')->where('output_id', 301)->value('work_product_id'));
        $this->assertSame('Decision-making model', DB::table('mst_infoflowoutput')->where('output_id', 301)->value('description'));
        $this->assertSame(1, DB::table('trs_infoflowio')->where('input_id', 101)->where('output_id', 301)->count());
        $this->assertSame(3, DB::table('mst_infoflowoutput')->where('practice_id', $this->practice)->count());
        $practice = $this->document()['practices'][0];
        $this->assertSame('EDM01-WP2', $practice['flow_rows'][0]['output']['wp_id']);
        $this->assertSame(['First item', 'Second item'], $practice['flow_rows'][0]['input']['description']);
        $this->assertSame(2, $practice['flow_rows'][1]['input']['rowspan']);
        $this->assertSame('Foreign input', DB::table('mst_infoflowinput')->where('input_id', 900)->value('description'));
    }

    public function test_read_only_and_unauthenticated_users_cannot_save_either_editor(): void
    {
        $this->putJson($this->overviewUrl(), $this->overviewPayload())->assertUnauthorized();
        $this->editor(false);
        $this->putJson($this->overviewUrl(), $this->overviewPayload())->assertForbidden();
        $this->putJson($this->flowUrl(), $this->flowPayload())->assertForbidden();
        $this->assertNull(MstObjective::find('EDM01.M25')->cobit5_document);
    }

    public function test_foreign_or_inactive_wp_and_foreign_flow_ids_are_rejected_before_any_writes(): void
    {
        $this->editor();
        $before = DB::table('mst_infoflowoutput')->orderBy('output_id')->get()->toJson();
        foreach ([7, 8, 9] as $foreign) {
            $payload = $this->flowPayload();
            $payload['outputs'][0]['work_product_id'] = $foreign;
            $this->putJson($this->flowUrl(), $payload)->assertUnprocessable();
        }
        $payload = $this->flowPayload();
        $payload['inputs'][0]['id'] = 900;
        $this->putJson($this->flowUrl(), $payload)->assertUnprocessable();
        $this->assertSame($before, DB::table('mst_infoflowoutput')->orderBy('output_id')->get()->toJson());
        $this->assertNull(MstObjective::find('EDM01.M25')->cobit5_document);
    }

    public function test_stale_overview_and_flow_submissions_cannot_overwrite_newer_edits(): void
    {
        $this->editor();
        $payload = $this->overviewPayload();
        $this->putJson($this->overviewUrl(), array_replace($payload, ['title' => 'New title']))->assertOk();
        $this->putJson($this->overviewUrl(), $payload)->assertStatus(409);
        $payload = $this->flowPayload();
        $this->putJson($this->flowUrl(), $payload)->assertOk();
        $this->putJson($this->flowUrl(), $payload)->assertStatus(409);
        $this->assertSame('New title', $this->document()['title']);
    }

    public function test_generic_cobit5_output_endpoint_requires_wp_and_changes_are_visible_in_document(): void
    {
        $this->editor();
        $url = route('cobit_component.infoflow-output.update', ['outputId' => 301]);
        $this->putJson($url, ['description' => 'Free text'])->assertUnprocessable();
        $this->putJson($url, ['work_product_id' => 8])->assertUnprocessable();
        $this->putJson($url, ['work_product_id' => 3, 'description' => 'Ignore this', 'to' => 'APO01.02'])->assertOk()->assertJsonPath('description', 'Authority levels');
        $output = $this->document()['practices'][0]['flow_rows'][0]['output'];
        $this->assertSame('EDM01-WP3', $output['wp_id']);
        $this->assertSame('APO01.02', $output['to']);
        $this->assertSame(1, DB::table('trs_infoflowio')->where('output_id', 301)->count());
        $this->putJson(route('cobit_component.infoflow-output.update', ['outputId' => 900]), ['description' => '2019 free text remains supported'])->assertOk()->assertJsonPath('description', '2019 free text remains supported');
    }

    public function test_generic_input_and_output_creation_are_visible_and_restricted_to_cobit5_wp(): void
    {
        $this->editor();
        $this->putJson(route('cobit_component.infoflow-input.update', ['inputId' => 101]), ['description' => 'Edited native input'])->assertOk();
        $this->assertSame('Edited native input', $this->document()['practices'][0]['flow_rows'][0]['input']['description']);
        $url = route('cobit_component.infoflow-output.store');
        $this->postJson($url, ['practice_id' => $this->practice, 'description' => 'No WP'])->assertUnprocessable();
        $this->postJson($url, ['practice_id' => $this->practice, 'work_product_id' => 4, 'to' => 'All EDM'])->assertCreated()->assertJsonPath('description', 'Enterprise governance communications');
        $this->assertSame(4, DB::table('mst_infoflowoutput')->where('output_id', '>', 900)->value('work_product_id'));
    }

    public function test_renaming_master_wp_updates_linked_output_and_document_name(): void
    {
        $this->editor();
        $this->putJson($this->flowUrl(), $this->flowPayload())->assertOk();
        $reference = MstEvidenceRef::findOrFail(1);
        $this->post(route('cobit5.work-products.save', ['objectiveId' => 'EDM01.M25']), [
            'focus_area_id' => 25, 'reference_id' => 1, 'revision' => app(WorkProductService::class)->revision($reference),
            'wp_id' => 'EDM01-WP1', 'wp' => 'Renamed governance principles', 'description' => 'WP description',
        ])->assertRedirect();
        $this->assertSame('Renamed governance principles', DB::table('mst_infoflowoutput')->where('output_id', 301)->value('description'));
        $this->assertSame('Renamed governance principles', $this->document()['practices'][0]['flow_rows'][0]['output']['description']);
    }

    public function test_version_and_focus_area_cannot_be_switched_through_editor_payload(): void
    {
        $this->editor();
        $this->putJson($this->overviewUrl(), array_replace($this->overviewPayload(), ['focus_area_id' => 26]))->assertNotFound();
        $this->putJson(route('cobit5.document.update', ['objectiveId' => 'EDM01']), array_replace($this->overviewPayload(), ['focus_area_id' => 1]))->assertNotFound();
        $this->assertSame('Stored description', MstObjective::find('EDM01')->objective_description);
    }

    public function test_document_exposes_edit_controls_only_to_editors_and_uses_scoped_wp_choices(): void
    {
        $this->withoutVite();
        view()->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        $this->editor();
        $html = $this->renderDocument();
        $this->assertStringContainsString('data-c5-overview-edit', $html);
        $this->assertSame(3, substr_count($html, 'data-c5-flow-edit='));
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $config = json_decode($xpath->evaluate('string(//*[@id="c5EditorData"])'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(6, $config['workProducts']);
        $this->assertSame(1, $config['flows']['EDM01.01']['outputs'][0]['work_product_id']);
        $this->editor(false);
        $html = $this->renderDocument();
        $this->assertStringNotContainsString('data-c5-overview-edit', $html);
        $this->assertStringNotContainsString('c5EditorData', $html);
    }

    private function document(): array
    {
        return app(DocumentService::class)->build(MstObjective::with('focusArea')->findOrFail('EDM01.M25'));
    }

    private function overviewUrl(): string
    {
        return route('cobit5.document.update', ['objectiveId' => 'EDM01.M25']);
    }

    private function flowUrl(): string
    {
        return route('cobit5.flow.update', ['objectiveId' => 'EDM01.M25']);
    }

    private function overviewPayload(): array
    {
        $documents = app(DocumentService::class);
        $data = $documents->overviewData($this->document());

        return $data + ['focus_area_id' => 25, 'revision' => $documents->revision($data)];
    }

    private function flowPayload(): array
    {
        $documents = app(DocumentService::class);
        $practice = $this->document()['practices'][0];
        $data = $documents->flowData($practice);

        return $data + ['focus_area_id' => 25, 'practice_id' => $this->practice, 'revision' => $documents->revision($data)];
    }

    private function renderDocument(): string
    {
        $objective = MstObjective::with('focusArea')->findOrFail('EDM01.M25');

        return view('cobit_component.cobit5.show', [
            'objective' => $objective, 'document' => $this->document(), 'focusAreaId' => 25, 'allObjectives' => collect([$objective]),
            'masterEvidenceFilters' => ['models' => [], 'objectives' => []], 'workProducts' => app(WorkProductService::class)->forObjective($objective), 'previous' => null, 'next' => null,
        ])->render();
    }

    private function editor(bool $canEdit = true): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
            DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, access_profile TEXT, organization_id INTEGER, isActivated INTEGER DEFAULT 1, created_at TEXT, updated_at TEXT)');
            (require database_path('migrations/2026_04_02_034817_create_permission_tables.php'))->up();
            Permission::findOrCreate('cobit.view', 'web');
            Permission::findOrCreate('design-factors.input', 'web');
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
        $user = User::create(['name' => 'Document user', 'role' => 'user', 'access_profile' => 'assessor', 'organization_id' => 1]);
        $user->isActivated = true;
        $user->givePermissionTo($canEdit ? ['cobit.view', 'design-factors.input'] : ['cobit.view']);
        $this->actingAs($user);
    }
}
