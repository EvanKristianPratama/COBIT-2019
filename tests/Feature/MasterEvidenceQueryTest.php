<?php

namespace Tests\Feature;

use App\Services\Cobit\MasterEvidenceQueryService;
use App\Services\Cobit\MasterEvidenceTrackingService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterEvidenceQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'session.driver' => 'array',
        ]);
        DB::purge('sqlite');
        foreach ([
            'CREATE TABLE mst_focusarea (id INTEGER PRIMARY KEY, name TEXT, version TEXT)',
            'CREATE TABLE mst_objective (objective_id TEXT PRIMARY KEY, objective TEXT, focus_area_id INTEGER)',
            'CREATE TABLE mst_practice (practice_id TEXT PRIMARY KEY, objective_id TEXT)',
            'CREATE TABLE mst_infoflowoutput (output_id INTEGER PRIMARY KEY, practice_id TEXT, description TEXT)',
            'CREATE TABLE mst_evidence_ref (id INTEGER PRIMARY KEY, focus_area_id INTEGER, objective_id TEXT, nama_dokumen TEXT, keterangan TEXT, referensi_sumber TEXT, is_active INTEGER)',
            'CREATE TABLE mst_cobit4_processes (id INTEGER PRIMARY KEY, objective_id TEXT, focus_area_id INTEGER)',
            'CREATE TABLE `mst_wp_4.1` (wp_id TEXT PRIMARY KEY, wp TEXT, description TEXT, order_no INTEGER)',
            'CREATE TABLE `trs_wp_to_obj_4.1` (process_id INTEGER, wp_id TEXT)',
        ] as $sql) {
            DB::statement($sql);
        }
        foreach ([[1, 'Core Model', '2019'], [3, 'Security', '2019'], [25, 'COBIT 5', '5'], [27, 'COBIT 4.1', '4.1'], [99, 'Other', 'other']] as [$id, $name, $version]) {
            DB::table('mst_focusarea')->insert(compact('id', 'name', 'version'));
        }
        foreach ([['EDM01', 1], ['EDM01.M3', 3], ['EDM01.M25', 25], ['PO01.M27', 27], ['OTHER', 99]] as [$objective, $focus]) {
            DB::table('mst_objective')->insert(['objective_id' => $objective, 'objective' => $objective.' title', 'focus_area_id' => $focus]);
            DB::table('mst_practice')->insert(['practice_id' => $objective.'.01', 'objective_id' => $objective]);
            DB::table('mst_infoflowoutput')->insert(['practice_id' => $objective.'.01', 'description' => $objective.' output']);
        }
        DB::table('mst_evidence_ref')->insert([
            ['id' => 1, 'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Access policy', 'keterangan' => 'Approval evidence', 'is_active' => 1],
            ['id' => 2, 'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Inactive', 'keterangan' => '', 'is_active' => 0],
            ['id' => 3, 'focus_area_id' => 3, 'objective_id' => 'EDM01.M3', 'nama_dokumen' => 'Security policy', 'keterangan' => '', 'is_active' => 1],
            ['id' => 4, 'focus_area_id' => 3, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Mismatched model', 'keterangan' => '', 'is_active' => 1],
        ]);
        DB::table('mst_cobit4_processes')->insert(['id' => 100, 'objective_id' => 'PO01.M27', 'focus_area_id' => 27]);
        DB::insert('INSERT INTO `mst_wp_4.1` VALUES (?, ?, ?, ?)', ['PO1-WP2', 'Tactical IT plan', 'Tactical description', 2]);
        DB::insert('INSERT INTO `mst_wp_4.1` VALUES (?, ?, ?, ?)', ['PO1-WP1', 'Strategic IT plan', 'Strategic description', 1]);
        DB::insert('INSERT INTO `trs_wp_to_obj_4.1` VALUES (100, ?), (100, ?)', ['PO1-WP2', 'PO1-WP1']);
    }

    public function test_each_version_uses_its_catalog_and_excludes_inactive_or_mismatched_references(): void
    {
        $catalog = app(MasterEvidenceQueryService::class);
        $modern = $catalog->search(['version' => '2019']);
        $this->assertSame(4, $modern->total());
        $this->assertSame(['2019'], collect($modern->items())->pluck('version')->unique()->values()->all());
        $this->assertNotContains('Inactive', collect($modern->items())->pluck('name'));
        $this->assertNotContains('Mismatched model', collect($modern->items())->pluck('name'));
        $this->assertSame(1, $catalog->search(['version' => '5'])->total());
        $legacy = $catalog->search(['version' => '4.1']);
        $this->assertSame(2, $legacy->total());
        $this->assertSame(['PO1-WP1', 'PO1-WP2'], collect($legacy->items())->pluck('reference')->all());
    }

    public function test_model_gamo_and_keyword_filters_apply_together(): void
    {
        $catalog = app(MasterEvidenceQueryService::class);
        $result = $catalog->search(['version' => '2019', 'focus_area' => 1, 'objective_id' => 'EDM01', 'q' => 'Approval']);
        $this->assertSame(1, $result->total());
        $this->assertSame('Access policy', $result->items()[0]->name);
        $this->assertSame(0, $catalog->search(['version' => '5', 'focus_area' => 1])->total());
        $this->assertSame(0, $catalog->search(['version' => '2019', 'objective_id' => 'EDM01.M25'])->total());
        $this->assertSame(1, $catalog->search(['version' => '4.1', 'q' => 'PO1-WP2'])->total());
        $this->assertSame(0, $catalog->search(['version' => '2019', 'q' => "' OR 1=1 --"])->total());
    }

    public function test_pagination_has_stable_non_overlapping_pages(): void
    {
        for ($i = 0; $i < 30; $i++) {
            DB::table('mst_infoflowoutput')->insert(['practice_id' => 'EDM01.M25.01', 'description' => 'Report '.$i]);
        }
        $catalog = app(MasterEvidenceQueryService::class);
        $first = $catalog->search(['version' => '5', 'page' => 1, 'per_page' => 25]);
        $second = $catalog->search(['version' => '5', 'page' => 2, 'per_page' => 25]);
        $this->assertSame(31, $first->total());
        $this->assertCount(25, $first->items());
        $this->assertCount(6, $second->items());
        $this->assertSame([], array_values(array_intersect(collect($first->items())->pluck('source_id')->all(), collect($second->items())->pluck('source_id')->all())));
    }

    public function test_all_gamo_results_start_with_edm_and_display_references_without_quotes(): void
    {
        foreach (['APO01', 'BAI01', 'DSS01', 'MEA01', 'EDM02'] as $code) {
            DB::table('mst_objective')->insert(['objective_id' => $code, 'objective' => $code.' title', 'focus_area_id' => 1]);
            DB::table('mst_practice')->insert(['practice_id' => '"'.$code.'.01"', 'objective_id' => $code]);
            DB::table('mst_infoflowoutput')->insert(['practice_id' => '"'.$code.'.01"', 'description' => $code.' "approved" output']);
        }

        $catalog = app(MasterEvidenceQueryService::class);
        $rows = collect($catalog->search(['version' => '2019', 'focus_area' => 1])->items());
        $this->assertSame(['EDM01', 'EDM02', 'APO01', 'BAI01', 'DSS01', 'MEA01'], $rows->pluck('objective_id')->unique()->values()->all());
        $this->assertSame('APO01.01', $rows->firstWhere('objective_id', 'APO01')->reference);
        $this->assertSame('APO01 "approved" output', $rows->firstWhere('objective_id', 'APO01')->name);
        $this->assertSame('APO01.01', $catalog->forObjectives(['APO01'])->first()->reference);
        $this->assertTrue(DB::table('mst_practice')->where('practice_id', '"APO01.01"')->exists());
    }

    public function test_optional_catalog_tables_can_be_missing_without_breaking_other_versions(): void
    {
        DB::statement('DROP TABLE `trs_wp_to_obj_4.1`');
        DB::statement('DROP TABLE mst_evidence_ref');
        $catalog = app(MasterEvidenceQueryService::class);
        $this->assertSame(0, $catalog->search(['version' => '4.1'])->total());
        $this->assertSame(2, $catalog->search(['version' => '2019'])->total());
    }

    public function test_cobit5_figure19_master_replaces_only_its_own_process_outputs_and_preserves_other_versions(): void
    {
        foreach ([10,2,1] as $number) DB::table('mst_evidence_ref')->insert([
            'focus_area_id'=>25,'objective_id'=>'EDM01.M25','nama_dokumen'=>'WP '.$number,
            'keterangan'=>'Detailed description '.$number,
            'referensi_sumber'=>\App\Services\Cobit5\WorkProductService::SOURCE_PREFIX.'EDM01-WP'.$number,'is_active'=>1,
        ]);
        DB::table('mst_objective')->insert(['objective_id'=>'EDM02.M25','objective'=>'Second process','focus_area_id'=>25]);
        DB::table('mst_practice')->insert(['practice_id'=>'EDM02.M25.01','objective_id'=>'EDM02.M25']);
        DB::table('mst_infoflowoutput')->insert(['practice_id'=>'EDM02.M25.01','description'=>'Not yet imported output']);
        $catalog=app(MasterEvidenceQueryService::class);
        $rows=collect($catalog->search(['version'=>'5'])->items());
        $this->assertCount(4,$rows);
        $this->assertSame(['EDM01-WP1','EDM01-WP2','EDM01-WP10'],$rows->where('objective_id','EDM01.M25')->pluck('reference')->all());
        $this->assertSame('Work Product',$rows->first()->source);
        $this->assertSame('Detailed description 1',$rows->first()->description);
        $this->assertSame('Output Work Product',$rows->last()->source);
        $this->assertSame(1,$catalog->search(['version'=>'5','q'=>'EDM01-WP10'])->total());
        $this->assertSame(4,$catalog->search(['version'=>'2019'])->total());
        $this->assertSame(2,$catalog->search(['version'=>'4.1'])->total());
        $this->assertCount(3,$catalog->forObjectives(['EDM01.M25']));
        $this->assertTrue(DB::table('mst_infoflowoutput')->where('practice_id','EDM01.M25.01')->exists());
    }

    public function test_cobit4_labels_hide_model_suffixes_and_follow_natural_po_ai_ds_me_order(): void
    {
        foreach (['PO02.M27', 'PO10.M27', 'AI01.M27', 'DS01.M27', 'ME01.M27'] as $index => $id) {
            $code = preg_replace_callback('/^([A-Z]+)0*(\d+).*$/', fn ($match) => $match[1].(int) $match[2], $id);
            DB::table('mst_objective')->insert(['objective_id' => $id, 'objective' => $code.' title', 'focus_area_id' => 27]);
            DB::table('mst_cobit4_processes')->insert(['id' => 101 + $index, 'objective_id' => $id, 'focus_area_id' => 27]);
            DB::insert('INSERT INTO `mst_wp_4.1` VALUES (?, ?, ?, ?)', [$code.'-WP1', $code.' output', 'Description', 10 + $index]);
            DB::insert('INSERT INTO `trs_wp_to_obj_4.1` VALUES (?, ?)', [101 + $index, $code.'-WP1']);
        }
        DB::table('mst_objective')->insert(['objective_id' => 'PO2.M27', 'objective' => 'Legacy alias', 'focus_area_id' => 27]);
        $catalog = app(MasterEvidenceQueryService::class);
        $options = $catalog->filters()['objectives']->where('focus_area_id', 27)->values();
        $this->assertSame(['PO1', 'PO2', 'PO10', 'AI1', 'DS1', 'ME1'], $options->pluck('objective_code')->all());
        $this->assertSame('PO02.M27', $options->firstWhere('objective_code', 'PO2')->objective_id);
        $rows = collect($catalog->search(['version' => '4.1'])->items());
        $this->assertSame(['PO1', 'PO2', 'PO10', 'AI1', 'DS1', 'ME1'], $rows->pluck('objective_code')->unique()->values()->all());
        $this->assertSame('PO01.M27', $rows->first()->objective_id);
        $this->assertSame(1, $catalog->search(['version' => '4.1', 'objective_id' => $options[1]->objective_id])->total());
    }

    public function test_filter_options_only_include_supported_models(): void
    {
        $filters = app(MasterEvidenceQueryService::class)->filters();
        $this->assertCount(4, $filters['models']);
        $this->assertCount(4, $filters['objectives']);
        $this->assertNotContains('other', $filters['models']->pluck('version'));
    }

    public function test_query_route_requires_login_and_cobit_view_permission(): void
    {
        $route = app('router')->getRoutes()->getByName('cobit_component.master-evidence.query');
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('permission:cobit.view', $route->gatherMiddleware());
        $this->getJson(route('cobit_component.master-evidence.query'))->assertUnauthorized();
    }

    public function test_query_endpoint_returns_rows_and_validates_filters(): void
    {
        $this->withoutMiddleware();
        $url = route('cobit_component.master-evidence.query');
        $this->getJson($url.'?version=4.1&q=Strategic')->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('data.0.reference', 'PO1-WP1');
        $this->getJson($url.'?version=other')->assertUnprocessable();
        $this->getJson($url.'?page=0&per_page=999')->assertUnprocessable();
    }

    public function test_tracking_follows_saved_mappings_and_preserves_work_product_order(): void
    {
        $this->mappingTable('EDM01', 'PO01');
        $result = app(MasterEvidenceTrackingService::class)->track(['focus_area' => 1, 'objective_id' => 'EDM01']);
        $row = $result['tracking']->items()[0];
        $this->assertSame(['2019', '5', '4.1'], array_column($row['columns'], 'version'));
        $this->assertSame('EDM01', $row['columns'][1]['processes'][0]['code']);
        $this->assertSame('EDM01.M25', $row['columns'][1]['processes'][0]['objective_id']);
        $legacy = $row['columns'][2]['processes'][0];
        $this->assertSame('PO1', $legacy['code']);
        $this->assertSame(['Strategic IT plan', 'Tactical IT plan'], array_column($legacy['evidence'], 'name'));
        $this->assertSame([1, 25, 27], array_column($result['models'], 'id'));
    }

    public function test_tracking_does_not_invent_links_for_unmapped_versions_or_missing_rows(): void
    {
        $this->mappingTable('Belum ada', '-');
        $service = app(MasterEvidenceTrackingService::class);
        $row = $service->track(['focus_area' => 1])['tracking']->items()[0];
        $this->assertSame([], $row['columns'][1]['processes']);
        $this->assertSame([], $row['columns'][2]['processes']);
        DB::table('mst_cobit_evolution')->delete();
        $row = $service->track(['focus_area' => 1])['tracking']->items()[0];
        $this->assertSame([], $row['columns'][1]['processes']);
    }

    public function test_tracking_handles_multiple_mapped_processes_aliases_and_duplicate_outputs(): void
    {
        $this->mappingTable('EDM01', 'PO1, PO02 (parsial), PO1');
        DB::table('mst_objective')->insert([
            ['objective_id' => 'PO02.M27', 'objective' => 'Information architecture', 'focus_area_id' => 27],
            ['objective_id' => 'PO2.M27', 'objective' => 'Legacy alias', 'focus_area_id' => 27],
        ]);
        DB::table('mst_cobit4_processes')->insert(['id' => 101, 'objective_id' => 'PO02.M27', 'focus_area_id' => 27]);
        DB::insert('INSERT INTO `mst_wp_4.1` VALUES (?, ?, ?, ?)', ['PO2-WP1', 'Data dictionary', 'Data description', 3]);
        DB::insert('INSERT INTO `trs_wp_to_obj_4.1` VALUES (101, ?)', ['PO2-WP1']);
        DB::table('mst_practice')->insert(['practice_id' => 'EDM01.M25.02', 'objective_id' => 'EDM01.M25']);
        DB::table('mst_infoflowoutput')->insert(['practice_id' => 'EDM01.M25.02', 'description' => "EDM01.M25\noutput"]);
        $row = app(MasterEvidenceTrackingService::class)->track(['focus_area' => 1])['tracking']->items()[0];
        $this->assertSame(['PO1', 'PO2'], array_column($row['columns'][2]['processes'], 'code'));
        $this->assertSame('Data dictionary', $row['columns'][2]['processes'][1]['evidence'][0]['name']);
        $this->assertCount(1, $row['columns'][1]['processes'][0]['evidence']);
        $this->assertStringContainsString('EDM01.M25.01', $row['columns'][1]['processes'][0]['evidence'][0]['reference']);
        $this->assertStringContainsString('EDM01.M25.02', $row['columns'][1]['processes'][0]['evidence'][0]['reference']);
    }

    public function test_tracking_retains_partial_labels_and_identifies_missing_processes(): void
    {
        $this->mappingTable('Dipisah dari EDM01', 'PO99 (parsial)');
        $row = app(MasterEvidenceTrackingService::class)->track(['focus_area' => 1])['tracking']->items()[0];
        $this->assertSame('Dipisah dari EDM01', $row['columns'][1]['mapping']);
        $this->assertSame('EDM01', $row['columns'][1]['processes'][0]['code']);
        $this->assertSame(['PO99'], $row['columns'][2]['missing_codes']);
        $this->assertSame([], $row['columns'][2]['processes']);
    }

    public function test_tracking_searches_mapped_evidence_and_keeps_modern_focus_areas_separate(): void
    {
        $this->mappingTable('EDM01', 'PO1');
        $service = app(MasterEvidenceTrackingService::class);
        $this->assertSame(1, $service->track(['focus_area' => 1, 'q' => 'Strategic description'])['tracking']->total());
        $this->assertSame(0, $service->track(['focus_area' => 1, 'q' => 'No matching evidence'])['tracking']->total());
        $row = $service->track(['focus_area' => 3])['tracking']->items()[0];
        $names = array_column($row['columns'][0]['processes'][0]['evidence'], 'name');
        $this->assertContains('Security policy', $names);
        $this->assertNotContains('Access policy', $names);
        $this->assertSame('EDM01.M3', $row['objective_id']);
    }

    public function test_tracking_paginates_gamo_rows_and_uses_config_only_when_mapping_table_is_absent(): void
    {
        config(['cobit-evolution.gamo' => ['EDM01' => ['id' => 'EDM01', 'cobit5_id' => 'EDM01', 'cobit4_id' => 'PO1', 'notes' => 'Fallback mapping']]]);
        for ($i = 2; $i <= 8; $i++) {
            DB::table('mst_objective')->insert(['objective_id' => 'EDM0'.$i, 'objective' => 'Governance '.$i, 'focus_area_id' => 1]);
        }
        $service = app(MasterEvidenceTrackingService::class);
        $first = $service->track(['focus_area' => 1]);
        $second = $service->track(['focus_area' => 1, 'page' => 2]);
        $this->assertSame(8, $first['tracking']->total());
        $this->assertCount(5, $first['tracking']->items());
        $this->assertCount(3, $second['tracking']->items());
        $this->assertSame('Fallback mapping', $first['tracking']->items()[0]['notes']);
        $this->assertSame('PO1', $first['tracking']->items()[0]['columns'][2]['processes'][0]['code']);
    }

    public function test_tracking_endpoint_is_protected_and_validates_the_starting_model(): void
    {
        $route = app('router')->getRoutes()->getByName('cobit_component.master-evidence.tracking');
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('permission:cobit.view', $route->gatherMiddleware());
        $url = route('cobit_component.master-evidence.tracking');
        $this->getJson($url)->assertUnauthorized();
        $this->withoutMiddleware();
        $this->mappingTable('EDM01', 'PO1');
        $this->getJson($url.'?focus_area=1&objective_id=EDM01')->assertOk()
            ->assertJsonPath('tracking.data.0.columns.2.processes.0.code', 'PO1');
        $this->getJson($url.'?focus_area=25')->assertUnprocessable();
        $this->getJson($url.'?page=0')->assertUnprocessable();
    }

    public function test_all_optional_sources_can_be_absent(): void
    {
        foreach (['mst_evidence_ref', 'mst_infoflowoutput', '`trs_wp_to_obj_4.1`', '`mst_wp_4.1`'] as $table) {
            DB::statement('DROP TABLE '.$table);
        }
        $this->assertSame(0, app(MasterEvidenceQueryService::class)->search(['version' => '2019'])->total());
        $row = app(MasterEvidenceTrackingService::class)->track(['focus_area' => 1])['tracking']->items()[0];
        $this->assertSame([], $row['columns'][0]['processes'][0]['evidence']);
    }

    private function mappingTable(string $cobit5, string $cobit4): void
    {
        DB::statement('CREATE TABLE mst_cobit_evolution (id INTEGER PRIMARY KEY, cobit2019_id TEXT, cobit5_id TEXT, cobit4_id TEXT, notes TEXT)');
        DB::table('mst_cobit_evolution')->insert(['cobit2019_id' => 'EDM01', 'cobit5_id' => $cobit5, 'cobit4_id' => $cobit4, 'notes' => 'Saved mapping']);
    }
}
