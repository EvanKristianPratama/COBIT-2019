<?php

namespace Tests\Feature;

use App\Http\Controllers\cobit2019\FocusAreaController;
use App\Services\Cobit4\Cobit4Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FocusAreaLibraryTest extends TestCase
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
        view()->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        DB::statement('CREATE TABLE mst_focusarea (id INTEGER PRIMARY KEY, code TEXT, name TEXT, description TEXT, version TEXT)');
        DB::statement('CREATE TABLE mst_objective (objective_id TEXT PRIMARY KEY, objective TEXT, focus_area_id INTEGER)');
        DB::table('mst_focusarea')->insert(['id' => 27, 'code' => 'COBIT41', 'name' => 'COBIT 4.1', 'version' => '4.1']);
        foreach (app(Cobit4Service::class)->getMasterProcesses() as $process) {
            DB::table('mst_objective')->insert(['objective_id' => $process['id'], 'objective' => $process['title'], 'focus_area_id' => 27]);
        }
        DB::table('mst_objective')->insert(['objective_id' => 'PO2.M27', 'objective' => 'Define the Information Architecture', 'focus_area_id' => 27]);
    }

    public function test_library_counts_34_unique_processes_when_po2_has_a_legacy_alias(): void
    {
        $view = app(FocusAreaController::class)->index(Request::create('/focus-areas?view=cards&version=4.1'));
        $model = $view->getData()['focusAreasByVersion']->get('4.1')->first();
        $this->assertSame(34, $model->objectives_count);
        $this->assertSame(35, DB::table('mst_objective')->where('focus_area_id', 27)->count());

        $document = new \DOMDocument;
        @$document->loadHTML($view->render());
        $xpath = new \DOMXPath($document);
        $stat = $xpath->query('//*[@id="viewCobit4"]//dd[contains(@class,"model-stat-value")]')->item(0);
        $this->assertSame('34', trim($stat->textContent));
    }

    public function test_real_custom_processes_are_counted_instead_of_capping_the_total_at_34(): void
    {
        DB::table('mst_objective')->insert(['objective_id' => 'PO11.M27', 'objective' => 'Custom process', 'focus_area_id' => 27]);
        $view = app(FocusAreaController::class)->index(Request::create('/focus-areas?version=4.1'));

        $this->assertSame(35, $view->getData()['focusAreasByVersion']->get('4.1')->first()->objectives_count);
        $this->assertSame(36, DB::table('mst_objective')->count());
    }
}
