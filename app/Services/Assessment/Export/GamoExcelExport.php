<?php

namespace App\Services\Assessment\Export;

use App\Models\MstObjective;
use App\Services\Assessment\Report\ActivityReportService;
use App\Services\Assessment\Scoring\ObjectiveAssessmentService;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GamoExcelExport
{
    public function __construct(
        private readonly ActivityReportService $reports,
        private readonly ObjectiveAssessmentService $scoring,
    ) {}

    public function build(Collection $objectives, Collection $activityData): Spreadsheet
    {
        $book = new Spreadsheet();
        $book->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $book->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

        foreach ($objectives->values() as $index => $objective) {
            $sheet = $index === 0 ? $book->getActiveSheet() : $book->createSheet();
            $this->populateSheet($sheet, $objective, $activityData);
        }

        return $book;
    }

    private function populateSheet(Worksheet $sheet, MstObjective $objective, Collection $activityData): void
    {
        $sheet->setTitle(substr($objective->objective_id, 0, 31));
        foreach (['A' => 7, 'B' => 16, 'C' => 28, 'D' => 52, 'E' => 16, 'F' => 45, 'G' => 35, 'H' => 40] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $this->mergedText($sheet, 'A1:H1', $objective->objective_id.' - '.$objective->objective);
        $this->heading($sheet, 'A1:H1', '102C60', 'FFFFFF');
        $row = 3;
        foreach (['Description' => $objective->objective_description, 'Purpose' => $objective->objective_purpose] as $label => $value) {
            if ($value) {
                $this->text($sheet, 'A'.$row, $label);
                $this->mergedText($sheet, 'B'.$row.':H'.$row, $value);
                $row++;
            }
        }
        $row++;
        $this->mergedText($sheet, "A{$row}:H{$row}", 'Practice Summary '.$objective->objective_id);
        $this->heading($sheet, "A{$row}:H{$row}", 'E9EDF6');
        $row++;
        $this->mergedText($sheet, "A{$row}:C".($row + 1), 'Practice');
        $this->mergedText($sheet, "D{$row}:G{$row}", 'Total of Activities');
        $this->mergedText($sheet, "H{$row}:H".($row + 1), 'Total');
        foreach ([2, 3, 4, 5] as $index => $level) {
            $this->text($sheet, chr(68 + $index).($row + 1), 'LV '.$level);
        }
        $this->heading($sheet, "A{$row}:H".($row + 1), 'E9EDF6');
        $row += 2;
        [$summary, $totals] = $this->reports->buildPracticeSummary($objective);
        $metrics = $this->scoring->calculateObjectiveAssessmentMetrics($objective, $activityData);
        $levelMetrics = $this->reports->buildPracticeSummaryLevelMetrics($totals, $metrics['level_scores']);
        foreach ($summary as $practice) {
            $this->mergedText($sheet, "A{$row}:C{$row}", $practice['practice_id'].' - '.$practice['practice_name']);
            foreach ([2, 3, 4, 5] as $index => $level) {
                $this->text($sheet, chr(68 + $index).$row, $practice['counts'][$level] ?: '-');
            }
            $this->text($sheet, 'H'.$row, $practice['total']);
            $row++;
        }
        foreach (['Total', 'Index', 'Rating'] as $label) {
            $this->mergedText($sheet, "A{$row}:C{$row}", $label);
            foreach ([2, 3, 4, 5] as $index => $level) {
                $value = match ($label) {
                    'Total' => $totals[$level] ?: '-',
                    'Index' => $levelMetrics[$level]['index'],
                    default => $levelMetrics[$level]['rating'],
                };
                $cell = chr(68 + $index).$row;
                $this->text($sheet, $cell, $value);
                if ($label === 'Rating') {
                    $this->ratingStyle($sheet, $cell, $value);
                }
            }
            if ($label === 'Total') {
                $this->text($sheet, 'H'.$row, $totals['total'] ?: '-');
            }
            $row++;
        }
        $this->mergedText($sheet, "A{$row}:C{$row}", 'Capability');
        $this->mergedText($sheet, "D{$row}:E{$row}", number_format($metrics['display_value'], 2, ',', ''));
        $this->mergedText($sheet, "F{$row}:G{$row}", $metrics['rating_string']);
        $this->ratingStyle($sheet, "F{$row}:G{$row}", $metrics['rating_letter']);
        $this->text($sheet, 'H'.$row, $metrics['final_level']);
        $sheet->getStyle('A'.($row - count($summary) - 5).':H'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D5DCE5');

        $levels = [];
        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $level = (int) $activity->capability_lvl;
                if ($level >= 2 && $level <= 5) {
                    $levels[$level][$practice->practice_id]['practice'] = $practice;
                    $levels[$level][$practice->practice_id]['activities'][] = $activity;
                }
            }
        }
        ksort($levels);
        foreach ($levels as $level => $practices) {
            $row += 2;
            $count = array_sum(array_map(fn ($group) => count($group['activities']), $practices));
            $score = $metrics['level_scores'][$level] ?? ['letter' => 'N', 'score' => 0];
            $this->mergedText($sheet, "A{$row}:H{$row}", sprintf('Level %d    %d activities    %s (%.2f)', $level, $count, $score['letter'], $score['score']));
            $this->heading($sheet, "A{$row}:H{$row}", '102C60', 'FFFFFF');
            $row++;
            foreach (['No', 'Practice', 'Practice Name', 'Activity', 'Rating', 'Reference', 'Evidence', 'Notes'] as $index => $label) {
                $this->text($sheet, chr(65 + $index).$row, $label);
            }
            $this->heading($sheet, "A{$row}:H{$row}", 'E9EDF6');
            $firstRow = $row;
            $counter = 0;
            foreach ($practices as $group) {
                $practice = $group['practice'];
                $reference = $practice->infoflowoutput->map(function ($item) {
                    $description = trim((string) $item->description, '"');
                    $to = trim((string) $item->to, '"');
                    return trim($description.($to !== '' ? "\nTo: ".$to : ''));
                })->filter()->implode("\n\n");
                $start = $row + 1;
                foreach ($group['activities'] as $activity) {
                    $row++;
                    $evaluation = $activityData->get($activity->activity_id);
                    [$evidence, $notes] = $this->evidenceAndNotes($evaluation);
                    $values = [++$counter, trim($practice->practice_id, '"'), trim($practice->practice_name, '"'), trim($activity->description, '"'), match ($evaluation?->level_achieved) {'N' => 'None', 'P' => 'Partially', 'L' => 'Largely', 'F' => 'Fully', default => ''}, '', $evidence, $notes];
                    foreach ($values as $index => $value) {
                        $this->text($sheet, chr(65 + $index).$row, $value);
                    }
                    $this->ratingStyle($sheet, 'E'.$row, $evaluation?->level_achieved);
                    $lines = max(
                        $this->wrappedLines($values[2], 28),
                        $this->wrappedLines($values[3], 52),
                        $this->wrappedLines($values[6], 35),
                        $this->wrappedLines($values[7], 40),
                    );
                    $sheet->getRowDimension($row)->setRowHeight(min(409, max(24, $lines * 15)));
                }
                if ($row > $start) {
                    $sheet->mergeCells("F{$start}:F{$row}");
                }
                $this->text($sheet, 'F'.$start, $reference ?: 'No reference available');
                $referenceHeight = $this->wrappedLines($reference, 45) * 15;
                $availableHeight = 0;
                for ($referenceRow = $start; $referenceRow <= $row; $referenceRow++) {
                    $availableHeight += $sheet->getRowDimension($referenceRow)->getRowHeight();
                }
                if ($referenceHeight > $availableHeight) {
                    $height = $sheet->getRowDimension($start)->getRowHeight() + $referenceHeight - $availableHeight;
                    $sheet->getRowDimension($start)->setRowHeight(min(409, $height));
                }
            }
            $sheet->getStyle("A{$firstRow}:H{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D5DCE5');
        }
        $sheet->freezePane('A2');
    }

    private function wrappedLines(string $text, int $width): int
    {
        return array_sum(array_map(
            fn ($line) => max(1, (int) ceil(mb_strlen($line) / $width)),
            explode("\n", $text)
        ));
    }

    private function ratingStyle(Worksheet $sheet, string $range, ?string $rating): void
    {
        $colors = [
            'N' => ['F8D7DA', '842029'],
            'P' => ['FFE69C', '5C4705'],
            'L' => ['0DCAF0', '042C55'],
            'F' => ['198754', 'FFFFFF'],
        ];
        if (! isset($colors[$rating])) {
            return;
        }

        [$background, $foreground] = $colors[$rating];
        $style = $sheet->getStyle($range);
        $style->getFill()->setFillType('solid')->getStartColor()->setRGB($background);
        $style->getFont()->getColor()->setRGB($foreground);
    }

    private function evidenceAndNotes($evaluation): array
    {
        if (! $evaluation) {
            return ['', ''];
        }
        if ($evaluation->evidence !== null) {
            return [(string) $evaluation->evidence, (string) $evaluation->notes];
        }
        $decoded = json_decode((string) $evaluation->notes, true);
        if (is_array($decoded) && ! array_is_list($decoded)) {
            return [(string) ($decoded['evidence'] ?? $decoded['comment'] ?? ''), (string) ($decoded['note'] ?? $decoded['notes'] ?? '')];
        }
        return ['', (string) $evaluation->notes];
    }

    private function text(Worksheet $sheet, string $cell, mixed $value): void
    {
        $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
    }

    private function mergedText(Worksheet $sheet, string $range, mixed $value): void
    {
        $sheet->mergeCells($range);
        $this->text($sheet, explode(':', $range)[0], $value);
    }

    private function heading(Worksheet $sheet, string $range, string $background, string $foreground = '18345D'): void
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setRGB($foreground);
        $style->getFill()->setFillType('solid')->getStartColor()->setRGB($background);
    }
}
