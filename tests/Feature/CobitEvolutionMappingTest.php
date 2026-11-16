<?php

namespace Tests\Feature;

use App\Http\Controllers\cobit2019\GamoController;
use App\Services\Cobit\CobitEvolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CobitEvolutionMappingTest extends TestCase
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
        $this->withoutVite();

        DB::statement('CREATE TABLE mst_focusarea (id INTEGER PRIMARY KEY, code TEXT, name TEXT, description TEXT, version TEXT)');
        DB::statement('CREATE TABLE mst_objective (objective_id TEXT PRIMARY KEY, objective TEXT, focus_area_id INTEGER)');
        DB::statement('CREATE TABLE mst_practice (practice_id TEXT PRIMARY KEY, objective_id TEXT, practice_name TEXT, practice_description TEXT, focus_area_id INTEGER)');
        DB::statement('CREATE TABLE mst_cobit_evolution (id INTEGER PRIMARY KEY AUTOINCREMENT, cobit2019_id TEXT, domain TEXT, title_2019 TEXT, desc_2019 TEXT, cobit5_id TEXT, cobit5_title TEXT, cobit4_id TEXT, cobit4_title TEXT, status TEXT, notes TEXT, created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE trs_cobit2019_cobit5_mappings (id INTEGER PRIMARY KEY AUTOINCREMENT, source_code TEXT, target_code TEXT NULL, order_no INTEGER, created_at TEXT, updated_at TEXT)');

        DB::table('mst_focusarea')->insert([
            ['id' => 1, 'name' => 'Core Model', 'version' => '2019'],
            ['id' => 25, 'name' => 'Cobit 5 Core Model', 'version' => '5'],
            ['id' => 27, 'name' => 'COBIT 4.1', 'version' => '4.1'],
        ]);

        // Core-model rows store ids and names wrapped in literal double quotes.
        DB::table('mst_objective')->insert(['objective_id' => 'EDM01', 'objective' => 'Ensured Governance Framework Setting and Maintenance', 'focus_area_id' => 1]);
        DB::table('mst_practice')->insert([
            ['practice_id' => '"EDM01.01"', 'objective_id' => 'EDM01', 'practice_name' => '"Evaluate the governance system."', 'focus_area_id' => 1],
            ['practice_id' => '"EDM01.09"', 'objective_id' => 'EDM01', 'practice_name' => '"Direct the governance system."', 'focus_area_id' => 1],
            ['practice_id' => '"EDM01.10"', 'objective_id' => 'EDM01', 'practice_name' => '"Something with no COBIT 5 counterpart."', 'focus_area_id' => 1],
        ]);
        // Stale seed: wrong 2019 title, and one coarse process (ME4) for every EDM GAMO.
        DB::table('mst_cobit_evolution')->insert([
            'cobit2019_id' => 'EDM01', 'domain' => 'EDM',
            'title_2019' => 'Evaluated, Directed and Monitored Governance Framework Setting and Maintenance',
            'cobit5_id' => 'EDM01', 'cobit5_title' => 'Ensure Governance Framework Setting and Maintenance',
            'cobit4_id' => 'ME4', 'cobit4_title' => 'ME4 Provide IT Governance', 'status' => 'restructured',
        ]);
    }

    private function practices(): array
    {
        return collect(app(CobitEvolutionService::class)->gamos()->firstWhere('code', 'EDM01')['practices'])->keyBy('code')->all();
    }

    public function test_names_come_from_the_gamo_and_ids_are_unquoted(): void
    {
        $gamo = app(CobitEvolutionService::class)->gamos()->firstWhere('code', 'EDM01');

        $this->assertSame('Ensured Governance Framework Setting and Maintenance', $gamo['name']);
        $this->assertSame(['EDM01.01', 'EDM01.09', 'EDM01.10'], array_keys($this->practices()));
        $this->assertSame('Evaluate the governance system.', $this->practices()['EDM01.01']['title']);
    }

    public function test_cobit41_control_objectives_follow_the_cobit5_practice_not_the_whole_process(): void
    {
        $evaluate = $this->practices()['EDM01.01'];

        $this->assertSame('auto', $evaluate['source']);
        $this->assertSame(['EDM01.01'], $evaluate['target_codes']);
        $this->assertSame(['PO3.3', 'ME4.1'], array_column($evaluate['targets'][0]['controls'], 'code'));
    }

    public function test_renumbered_practice_is_matched_by_name_and_unrelated_one_stays_unmapped(): void
    {
        $this->assertSame(['EDM01.02'], $this->practices()['EDM01.09']['target_codes']);
        $this->assertSame('none', $this->practices()['EDM01.10']['source']);
        $this->assertSame([], $this->practices()['EDM01.10']['targets']);
    }

    public function test_saved_mapping_overrides_automatic_match_and_can_be_reset(): void
    {
        $service = app(CobitEvolutionService::class);

        $service->savePracticeMapping('EDM01.01', ['EDM01.03', 'EDM01.02']);
        $saved = $this->practices()['EDM01.01'];
        $this->assertSame('manual', $saved['source']);
        $this->assertSame(['EDM01.03', 'EDM01.02'], $saved['target_codes']);
        $this->assertContains('ME4.6', array_column($saved['targets'][0]['controls'], 'code'));

        $service->savePracticeMapping('EDM01.01', []);
        $this->assertSame('none', $this->practices()['EDM01.01']['source']);
        $this->assertSame([], $this->practices()['EDM01.01']['targets']);

        $service->resetPracticeMapping('EDM01.01');
        $this->assertSame('auto', $this->practices()['EDM01.01']['source']);
    }

    private function save(array $payload)
    {
        return app(GamoController::class)->saveEvolution(
            Request::create('/x', 'PUT', ['payload' => json_encode($payload)]),
            app(CobitEvolutionService::class)
        );
    }

    public function test_save_rejects_unknown_cobit5_codes_and_unknown_practices(): void
    {
        try {
            $this->save(['practices' => [['code' => 'EDM01.01', 'targets' => ['ZZZ99.99']]]]);
            $this->fail('Expected a validation error for an unknown COBIT 5 code.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('practices.0.targets.0', $exception->errors());
        }

        try {
            $this->save(['practices' => [['code' => 'EDM99.99', 'targets' => ['EDM01.01']]]]);
            $this->fail('Expected a validation error for an unknown COBIT 2019 practice.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('practices.0.code', $exception->errors());
        }

        try {
            $this->save(['gamos' => [['id' => 1, 'status' => 'bogus']]]);
            $this->fail('Expected a validation error for an unknown status.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('gamos.0.status', $exception->errors());
        }

        $this->assertSame(0, DB::table('trs_cobit2019_cobit5_mappings')->count());
        $this->assertSame('restructured', DB::table('mst_cobit_evolution')->value('status'));
    }

    public function test_save_applies_gamo_and_practice_changes_together_and_reset_restores_auto(): void
    {
        $this->save([
            'gamos' => [['id' => 1, 'status' => 'evolved', 'notes' => 'catatan baru', 'cobit5_id' => 'EDM01']],
            'practices' => [['code' => 'EDM01.01', 'targets' => ['EDM01.03']]],
        ]);

        $gamo = app(CobitEvolutionService::class)->gamos()->firstWhere('code', 'EDM01');
        $this->assertSame('evolved', $gamo['status']);
        $this->assertSame('catatan baru', $gamo['notes']);
        $this->assertSame(['EDM01.03'], $this->practices()['EDM01.01']['target_codes']);
        $this->assertTrue($this->practices()['EDM01.01']['saved']);

        $this->save(['practices' => [['code' => 'EDM01.01', 'reset' => true]]]);
        $this->assertSame('auto', $this->practices()['EDM01.01']['source']);
        $this->assertFalse($this->practices()['EDM01.01']['saved']);
        $this->assertSame(['EDM01.01'], $this->practices()['EDM01.01']['auto_target_codes']);
    }

    public function test_editor_data_exposes_cobit4_controls_per_cobit5_practice(): void
    {
        $data = app(CobitEvolutionService::class)->editorData();

        $this->assertSame('Evaluate the governance system.', $data['cobit5']['EDM01.01']['title']);
        $this->assertSame(['PO3.3', 'ME4.1'], array_column($data['controls']['EDM01.01'], 'code'));
        $group = collect($data['groups'])->firstWhere('gamo', 'EDM01');
        $this->assertContains('EDM01.01', $group['practices']);
    }

    private function createControlTables(): void
    {
        $migration = require base_path('database/migrations/2026_11_06_000001_create_cobit4_cobit5_mapping_tables.php');
        $migration->up();
    }

    private function controlCodes(string $practice, string $target): array
    {
        $row = collect($this->practices()[$practice]['targets'])->firstWhere('code', $target);

        return array_column($row['controls'] ?? [], 'code');
    }

    public function test_control_objective_can_be_added_and_removed_under_a_cobit5_practice(): void
    {
        $this->createControlTables();
        $this->assertTrue(app(CobitEvolutionService::class)->control4Ready());
        $this->assertSame(['PO3.3', 'ME4.1'], $this->controlCodes('EDM01.01', 'EDM01.01'));

        // PO3.3 maps to EDM01.01 directly; ME4.3 is added from master data.
        $this->save(['controls' => [['target' => 'EDM01.01', 'remove' => ['PO3.3'], 'add' => ['ME4.3']]]]);

        $this->assertSame(['ME4.1', 'ME4.3'], $this->controlCodes('EDM01.01', 'EDM01.01'));
        // The 4.1 -> 5 mapping is shared: the other COBIT 5 practices are untouched.
        $this->assertSame(['ME4.1'], $this->controlCodes('EDM01.09', 'EDM01.02'));
    }

    public function test_removing_one_practice_from_a_process_level_mapping_keeps_its_siblings(): void
    {
        $this->createControlTables();
        $this->assertSame(['ME4.1'], $this->controlCodes('EDM01.09', 'EDM01.02')); // ME4.1 targets the whole EDM01 process

        $this->save(['controls' => [['target' => 'EDM01.01', 'remove' => ['ME4.1']]]]);

        $this->assertSame(['PO3.3'], $this->controlCodes('EDM01.01', 'EDM01.01'));
        $this->assertSame(['ME4.1'], $this->controlCodes('EDM01.09', 'EDM01.02'));
        $this->assertSame(['ME4.1'], collect(app(CobitEvolutionService::class)->editorData()['controls']['EDM01.03'])->pluck('code')->intersect(['ME4.1'])->values()->all());
    }

    public function test_control_objective_status_follows_whether_it_still_has_a_cobit5_target(): void
    {
        $this->createControlTables();

        // ME4.2 is "deleted" in the master mapping; mapping it to a practice makes it mapped.
        $this->save(['controls' => [['target' => 'EDM01.01', 'add' => ['ME4.2']]]]);
        $this->assertSame('mapped', DB::table('mst_cobit4_cobit5_mapping_sources')->where('code', 'ME4.2')->value('mapping_status'));
        $this->assertContains('ME4.2', $this->controlCodes('EDM01.01', 'EDM01.01'));

        // Removing its only target turns it back to deleted.
        $this->save(['controls' => [['target' => 'EDM01.01', 'remove' => ['ME4.2']]]]);
        $this->assertSame('deleted', DB::table('mst_cobit4_cobit5_mapping_sources')->where('code', 'ME4.2')->value('mapping_status'));
    }

    public function test_control_changes_are_rejected_for_unknown_codes_or_without_the_mapping_tables(): void
    {
        try {
            $this->save(['controls' => [['target' => 'EDM01.01', 'add' => ['ZZ9.9']]]]);
            $this->fail('Expected a validation error for an unknown COBIT 4.1 code.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('controls.0.add.0', $exception->errors());
        }

        try {
            $this->save(['controls' => [['target' => 'EDM01.01', 'add' => ['ME4.3']]]]);
            $this->fail('Expected a validation error when the mapping tables do not exist.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('controls', $exception->errors());
        }
    }

    public function test_cobit5_catalog_is_read_from_the_database_once_its_tables_exist(): void
    {
        $source = app(\App\Services\Cobit\Cobit41To5MappingSource::class);
        $fromConfig = $source->practiceCatalog();

        $migration = require base_path('database/migrations/2026_11_16_000002_create_cobit5_practice_catalog_tables.php');
        $migration->up();

        // The seeded tables give back exactly the config catalog, including the unverified DSS08.02 entry.
        $this->assertEquals($fromConfig, $source->practiceCatalog());
        $this->assertSame(0, DB::table('mst_cobit5_catalog_practices')->where('code', 'EDM01.01')->where('is_verified', 0)->count());
        $this->assertSame(1, DB::table('mst_cobit5_catalog_practices')->where('is_verified', 0)->count());

        // From now on the database wins over the config file.
        DB::table('mst_cobit5_catalog_practices')->where('code', 'EDM01.01')->update(['title' => 'Evaluate the governance system (DB).']);
        $this->assertSame('Evaluate the governance system (DB).', $source->practiceCatalog()['domains']['EDM']['processes']['EDM01']['practices'][0]['title']);
        $this->assertSame('Evaluate the governance system (DB).', $this->practices()['EDM01.01']['targets'][0]['title']);
    }
}
