<?php

namespace Tests\Unit\Services\Cobit;

use App\Models\MstFocusArea;
use App\Models\MstObjective;
use App\Services\Cobit\CobitModelMapService;
use Tests\TestCase;

class CobitModelMapServiceTest extends TestCase
{
    public function test_core_navigation_keeps_the_core_focus_area_when_other_models_have_matching_codes(): void
    {
        $security = $this->model(3, 'SECURITY', '2019', 'EDM01.M3');
        $core = $this->model(1, 'CORE MODEL', '2019', 'EDM01');
        $map = app(CobitModelMapService::class)->build(collect([$security, $core]));

        $this->assertSame($core, $map['core']);
        $this->assertSame(['EDM' => 5, 'APO' => 14, 'BAI' => 11, 'DSS' => 6, 'MEA' => 4], $map['domains']->map(fn ($domain) => $domain['objectives']->count())->all());
        $this->assertSame(route('cobit_component.show', ['id' => 'EDM01', 'focus_area' => 1]), $map['domains']['EDM']['objectives'][0]['url']);
        $this->assertSame(route('cobit_component.show', ['id' => 'EDM01.M3', 'focus_area' => 3]), $map['focusLinks']['Security']);
        $this->assertSame($map['libraryUrl'], $map['focusLinks']['SME']);
    }

    public function test_missing_models_and_objectives_link_to_the_library_instead_of_nonexistent_objectives(): void
    {
        $map = app(CobitModelMapService::class)->build(collect());

        $this->assertNull($map['core']);
        $this->assertCount(40, $map['domains']->pluck('objectives')->flatten(1));
        foreach ($map['domains'] as $domain) {
            foreach ($domain['objectives'] as $objective) {
                $this->assertSame($map['libraryUrl'], $objective['url']);
            }
        }
    }

    public function test_page_displays_map_by_default_and_keeps_the_card_and_model_panels_hidden(): void
    {
        $xpath = $this->renderPage();

        $this->assertSame(1, $xpath->query('//*[@id="modelMapContainer" and not(@hidden)]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="wizardStep1Container" and @hidden]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="wizardStep2Container" and @hidden]')->length);
        $this->assertSame(40, $xpath->query('//*[@data-map-objective]')->length);
        $this->assertSame(2, $xpath->query('//*[@data-model-view]')->length);
        $standardsLink = $xpath->query('//a[contains(@aria-label,"Standards, Frameworks, Regulations")]')->item(0);
        $this->assertSame(route('cobit_component.show', ['id' => 'EDM01', 'focus_area' => 1, 'component' => 'policies']), $standardsLink->getAttribute('href'));
    }

    public function test_a_model_deep_link_selects_the_right_library_even_before_javascript_runs(): void
    {
        $xpath = $this->renderPage(['initialStep' => 2, 'selectedView' => 'cards', 'selectedVersion' => '5']);

        $this->assertSame(1, $xpath->query('//*[@id="modelMapContainer" and @hidden]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="wizardStep2Container" and not(@hidden)]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="viewCobit5" and not(@hidden)]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="viewCobit2019" and @hidden]')->length);
    }

    private function model(int $id, string $code, string $version, string $objectiveId): MstFocusArea
    {
        $model = (new MstFocusArea)->forceFill(['id' => $id, 'code' => $code, 'name' => $code, 'version' => $version, 'objectives_count' => 1]);
        $model->setRelation('objectives', collect([(new MstObjective)->forceFill(['objective_id' => $objectiveId, 'objective' => 'Test governance objective', 'focus_area_id' => $id])]));

        return $model;
    }

    private function renderPage(array $overrides = []): \DOMXPath
    {
        $this->withoutVite();
        view()->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        $focusAreas = collect([$this->model(1, 'CORE MODEL', '2019', 'EDM01'), $this->model(25, 'COBIT5', '5', 'EDM01.M25')]);
        $map = app(CobitModelMapService::class)->build($focusAreas);
        $html = view('focus_area.index', [
            'focusAreasByVersion' => $focusAreas->groupBy('version'),
            'selectedVersion' => '2019', 'selectedView' => 'map', 'initialStep' => 1,
            'modelMap' => $map, 'modelLinks' => $map['modelLinks'], ...$overrides,
        ])->render();
        $document = new \DOMDocument;
        @$document->loadHTML($html);

        return new \DOMXPath($document);
    }
}
