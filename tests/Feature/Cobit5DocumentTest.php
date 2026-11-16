<?php

namespace Tests\Feature;

use App\Models\MstActivities;
use App\Models\MstAligngoals;
use App\Models\MstAligngoalsmetr;
use App\Models\MstFocusArea;
use App\Models\MstGuidance;
use App\Models\MstInfoflowInput;
use App\Models\MstInfoflowOutput;
use App\Models\MstObjective;
use App\Models\MstPractice;
use App\Services\Cobit5\DocumentService;
use Tests\TestCase;

class Cobit5DocumentTest extends TestCase
{
    public function test_edm01_reference_uses_cobit5_content_without_mutating_shared_master_data(): void
    {
        $objective = $this->objective();
        $document = app(DocumentService::class)->build($objective);
        $this->assertSame('Ensure Governance Framework Setting and Maintenance', $document['title']);
        $this->assertStringContainsString('effective enabling structures', $document['description']);
        $this->assertSame(['01', '03', '07'], array_column($document['it_goals'], 'code'));
        $this->assertCount(3, $document['process_goals']);
        $this->assertCount(26, $document['roles']);
        $this->assertSame([8, 6, 6], array_map(fn ($practice) => count($practice['activities']), $document['practices']));
        $this->assertSame('MEA02.06', $document['practices'][2]['flow_rows'][4]['input']['from']);
        $this->assertSame('All EDM' . "\n" . 'APO01.07', $document['practices'][2]['flow_rows'][0]['output']['to']);
        $this->assertSame(['Committee of Sponsoring Organizations of the Treadway Commission (COSO)', 'ISO/IEC 38500', 'King III', 'Organisation for Economic Co-operation and Development (OECD)'], array_column($document['guidance'], 'standard'));
        $this->assertSame('Stored master description', $objective->objective_description);
    }

    public function test_reference_raci_has_26_columns_and_keeps_blank_assignments(): void
    {
        $document = app(DocumentService::class)->build($this->objective());
        foreach ($document['practices'] as $practice) {
            $this->assertCount(count($document['roles']), $practice['raci']);
        }
        $this->assertSame('', $document['practices'][0]['raci'][5]);
        $this->assertSame('A', $document['practices'][0]['raci'][0]);
        $this->assertSame('R', $document['practices'][1]['raci'][17]);
    }

    public function test_document_layout_keeps_reference_sheets_spans_and_all_activities(): void
    {
        $html = $this->renderDocument();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(3, $xpath->query('//article[contains(@class,"c5-sheet")]')->length);
        $this->assertSame(26, $xpath->query('//th[contains(@class,"c5-raci-role")]')->length);
        $this->assertSame(3, $xpath->query('//table[contains(@class,"c5-raci")]//tbody/tr')->length);
        $this->assertSame(20, $xpath->query('//td[contains(@class,"c5-activity")]')->length);
        $this->assertSame(2, $xpath->query('//table[contains(@class,"c5-practices")]//td[@rowspan="8"]')->length);
        $this->assertSame(3, $xpath->query('//*[@id="c5-process-select"]/option')->length);
        $this->assertStringContainsString('ISO/IEC 38500', $html);
        $this->assertStringNotContainsString('King IV', $xpath->evaluate('string(//*[@id="c5-document"])'));
        $this->assertStringContainsString('masterEvidenceListMode', $html);
    }

    public function test_document_text_is_escaped(): void
    {
        $document = app(DocumentService::class)->build($this->objective());
        $document['description'] = '<script>alert(1)</script>';
        $html = $this->renderDocument($document);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_other_processes_read_their_own_model_relations_and_clean_display_codes(): void
    {
        $objective = $this->objective('APO02.M25');
        $goal = (new MstAligngoals)->forceFill(['aligngoals_id' => 'AG01.M25.1', 'description' => '"Stored goal"']);
        $goal->setRelation('aligngoalsmetr', collect([(new MstAligngoalsmetr)->forceFill(['description' => '"Goal metric"'])]));
        $guidance = (new MstGuidance)->forceFill(['guidance' => '"Stored standard"', 'reference' => '"Section 1"']);
        $practice = (new MstPractice)->forceFill(['practice_id' => '"APO02.01".M25', 'practice_name' => '"Define strategy."', 'practice_description' => 'Stored practice description']);
        $practice->setRelation('activities', collect([(new MstActivities)->forceFill(['activity_id' => 1, 'description' => '"First activity."'])]));
        $practice->setRelation('roles', collect());
        $practice->setRelation('guidances', collect([$guidance]));
        $practice->setRelation('infoflowinput', collect([(new MstInfoflowInput)->forceFill(['input_id' => 1, 'from' => '"MEA01.02".M25', 'description' => 'Input description'])]));
        $practice->setRelation('infoflowoutput', collect([(new MstInfoflowOutput)->forceFill(['output_id' => 1, 'to' => 'All EDM; APO01.01.M25', 'description' => 'Output description'])]));
        $objective->setRelation('aligngoals', collect([$goal]));
        $objective->setRelation('guidance', collect([$guidance]));
        $objective->setRelation('practices', collect([$practice]));
        $document = app(DocumentService::class)->build($objective);
        $this->assertSame('APO02', $document['code']);
        $this->assertSame('Management', $document['area']);
        $this->assertSame('APO02.01', $document['practices'][0]['code']);
        $this->assertSame('MEA01.02', $document['practices'][0]['flow_rows'][0]['input']['from']);
        $this->assertSame("All EDM\nAPO01.01", $document['practices'][0]['flow_rows'][0]['output']['to']);
        $this->assertSame(['First activity.'], $document['practices'][0]['activities']);
        $this->assertCount(1, $document['guidance']);
        $this->assertSame('Stored master description', $document['description']);
    }

    public function test_2019_objective_cannot_load_cobit5_document(): void
    {
        $objective = $this->objective();
        $objective->focusArea->version = '2019';
        try {
            app(DocumentService::class)->build($objective);
            $this->fail('Wrong framework accepted.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    private function objective(string $id = 'EDM01.M25'): MstObjective
    {
        $objective = (new MstObjective)->forceFill(['objective_id' => $id, 'objective' => 'Stored title', 'objective_description' => 'Stored master description', 'objective_purpose' => 'Stored purpose', 'focus_area_id' => 25]);
        $objective->setRelation('focusArea', (new MstFocusArea)->forceFill(['id' => 25, 'name' => 'COBIT 5', 'version' => '5']));

        return $objective;
    }

    private function renderDocument(?array $document = null): string
    {
        $this->withoutVite();
        view()->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        $objective = $this->objective();

        return view('cobit_component.cobit5.show', [
            'objective' => $objective, 'document' => $document ?? app(DocumentService::class)->build($objective),
            'focusAreaId' => 25, 'allObjectives' => collect([$objective, $this->objective('EDM02.M25'), $this->objective('EDM03.M25')]),
            'masterEvidenceFilters' => ['models' => [], 'objectives' => []], 'previous' => null, 'next' => $this->objective('EDM02.M25'),
        ])->render();
    }
}
