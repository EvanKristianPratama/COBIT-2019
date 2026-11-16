<?php

namespace Tests\Unit\Services\Assessment;

use App\Models\MstActivities;
use App\Models\MstObjective;
use App\Models\MstPractice;
use App\Models\TrsActivityeval;
use App\Services\Assessment\Export\GamoExcelExport;
use Illuminate\Support\Fluent;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class GamoExcelExportTest extends TestCase
{
    public function test_workbook_contains_summary_and_all_level_activities_with_saved_values(): void
    {
        $objective = (new MstObjective)->forceFill(['objective_id' => 'EDM01', 'objective' => 'Governance']);
        $practice = (new MstPractice)->forceFill(['practice_id' => 'EDM01.01', 'practice_name' => 'Evaluate']);
        $practice->setRelation('activities', collect([
            (new MstActivities)->forceFill(['activity_id' => 1, 'description' => 'First activity', 'capability_lvl' => 2]),
            (new MstActivities)->forceFill(['activity_id' => 2, 'description' => 'Unrated activity', 'capability_lvl' => 2]),
            (new MstActivities)->forceFill(['activity_id' => 3, 'description' => 'Higher level', 'capability_lvl' => 3]),
            (new MstActivities)->forceFill(['activity_id' => 4, 'description' => 'Largely activity', 'capability_lvl' => 3]),
            (new MstActivities)->forceFill(['activity_id' => 5, 'description' => 'None activity', 'capability_lvl' => 3]),
        ]));
        $practice->setRelation('infoflowoutput', collect([new Fluent(['description' => 'Guidance', 'to' => 'APO01'])]));
        $objective->setRelation('practices', collect([$practice]));
        $data = collect([
            1 => (new TrsActivityeval)->forceFill(['level_achieved' => 'F', 'evidence' => 'Policy document', 'notes' => '=1+1']),
            3 => (new TrsActivityeval)->forceFill(['level_achieved' => 'P', 'evidence' => null, 'notes' => '{"evidence":"Legacy evidence","note":"Legacy note"}']),
            4 => (new TrsActivityeval)->forceFill(['level_achieved' => 'L']),
            5 => (new TrsActivityeval)->forceFill(['level_achieved' => 'N']),
        ]);
        $book = app(GamoExcelExport::class)->build(collect([$objective]), $data);
        $path = tempnam(sys_get_temp_dir(), 'gamo-');
        try {
            (new Xlsx($book))->save($path);
            $sheet = IOFactory::load($path)->getActiveSheet();
            $values = $sheet->toArray(null, false, false, false);
            $flat = array_merge(...$values);
            foreach (['Practice Summary EDM01', 'Unrated activity', 'Fully', 'Policy document', 'Legacy evidence', 'Legacy note', 'Guidance'."\n".'To: APO01'] as $value) {
                $this->assertContains($value, $flat);
            }
            $this->assertContains('0,50', $flat);
            $this->assertNotEmpty($sheet->getMergeCells());
            $expectedColors = ['Fully' => '198754', 'Largely' => '0DCAF0', 'Partially' => 'FFE69C', 'None' => 'F8D7DA'];
            foreach ($sheet->getRowIterator() as $row) {
                $cell = $sheet->getCell('E'.$row->getRowIndex());
                if (isset($expectedColors[$cell->getValue()])) {
                    $this->assertSame($expectedColors[$cell->getValue()], $cell->getStyle()->getFill()->getStartColor()->getRGB());
                    unset($expectedColors[$cell->getValue()]);
                }
            }
            $this->assertSame([], $expectedColors);
            foreach ($sheet->getRowIterator() as $row) {
                $cell = $sheet->getCell('H'.$row->getRowIndex());
                if ($cell->getValue() === '=1+1') {
                    $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
                    return;
                }
            }
            $this->fail('Saved note was not found.');
        } finally {
            @unlink($path);
        }
    }
}
