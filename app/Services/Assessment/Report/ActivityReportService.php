<?php

namespace App\Services\Assessment\Report;

use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\MstObjective;
use App\Models\TrsActivityeval;
use App\Models\TrsDomain;
use App\Services\EvaluationService;
use InvalidArgumentException;

class ActivityReportService
{
    public function __construct(
        private readonly EvaluationService $evaluationService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(MstEval $evaluation, string $objectiveId, $filterLevel = null): array
    {
        $evalId = $evaluation->eval_id;
        $owner = $evaluation->relationLoaded('user') ? $evaluation->user : $evaluation->user()->first();
        $organization = $evaluation->relationLoaded('organization')
            ? $evaluation->organization?->organization_name
            : $evaluation->organization()->value('organization_name');

        if (! $organization) {
            $organization = $owner?->organisasi ?? 'N/A';
        }

        $objective = MstObjective::with(['practices.activities'])
            ->where('objective_id', $objectiveId)
            ->first();

        if (! $objective) {
            throw new InvalidArgumentException('Objective not found');
        }

        $areaObjective = TrsDomain::where('objective_id', $objectiveId)->first();

        $maxLevel = 0;
        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $level = (int) ($activity->capability_lvl ?? $activity->capability_level ?? 0);
                if ($level > $maxLevel) {
                    $maxLevel = $level;
                }
            }
        }

        $evaluationActivityData = TrsActivityeval::where('eval_id', $evalId)
            ->get()
            ->keyBy('activity_id');
        $metrics = $this->evaluationService->calculateObjectiveAssessmentMetrics($objective, $evaluationActivityData);
        $currentLevel = $metrics['final_level'];
        [$practiceSummaryRows, $practiceSummaryTotals] = $this->buildPracticeSummary($objective);
        $practiceSummaryLevelMetrics = $this->buildPracticeSummaryLevelMetrics(
            $practiceSummaryTotals,
            $metrics['level_scores'] ?? []
        );
        $practiceSummaryCapability = [
            'value' => $this->formatSummaryNumber((float) ($metrics['display_value'] ?? 0)),
            'rating' => $metrics['rating_string'] ?? '0N',
            'level' => (string) ($metrics['final_level'] ?? 0),
        ];

        $targetCapabilityMap = $this->evaluationService->fetchTargetCapabilities($evaluation);
        $targetLevel = $targetCapabilityMap[$objectiveId] ?? null;
        $ratingString = $metrics['rating_string'];
        $displayValue = $metrics['display_value_label'];
        $evalEvidences = MstEvidence::where('eval_id', $evalId)->get()->keyBy('id');

        $activityData = [];
        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $capabilityLevel = $activity->capability_lvl ?? $activity->capability_level ?? '';
                if ($filterLevel && (string) $capabilityLevel !== (string) $filterLevel) {
                    continue;
                }

                $activityEval = $evaluationActivityData->get($activity->activity_id);

                if (! $activityEval || ! $activityEval->level_achieved) {
                    continue;
                }

                $evidenceDisplay = [];
                $rawEvidence = $activityEval->evidence;

                if ($rawEvidence) {
                    $decoded = json_decode($rawEvidence, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $evidenceItem) {
                            if (is_numeric($evidenceItem) && isset($evalEvidences[$evidenceItem])) {
                                $evidenceDisplay[] = $evalEvidences[$evidenceItem]->judul_dokumen ?? "Evidence #{$evidenceItem}";
                            } elseif (is_array($evidenceItem)) {
                                $evidenceDisplay[] = $evidenceItem['name'] ?? $evidenceItem['judul_dokumen'] ?? json_encode($evidenceItem);
                            } else {
                                $evidenceDisplay[] = (string) $evidenceItem;
                            }
                        }
                    } else {
                        $evidenceDisplay[] = $rawEvidence;
                    }
                }

                $activityData[] = [
                    'practice_id' => $practice->practice_id,
                    'practice_name' => $practice->practice_name,
                    'activity_id' => $activity->activity_id,
                    'activity_description' => $activity->description ?? $activity->activity ?? '',
                    'capability_level' => (string) $capabilityLevel,
                    'answer' => $activityEval->level_achieved,
                    'evidence' => $evidenceDisplay,
                    'notes' => $activityEval->notes,
                ];
            }
        }

        usort($activityData, function ($left, $right) {
            $levelLeft = (int) $left['capability_level'];
            $levelRight = (int) $right['capability_level'];

            if ($levelLeft !== $levelRight) {
                return $levelLeft <=> $levelRight;
            }

            $practiceComparison = strcmp($left['practice_id'], $right['practice_id']);
            if ($practiceComparison !== 0) {
                return $practiceComparison;
            }

            return strcmp($left['activity_id'], $right['activity_id']);
        });

        $practices = collect($activityData)
            ->map(fn ($activity) => [
                'id' => $activity['practice_id'],
                'name' => $activity['practice_name'],
            ])
            ->unique('id')
            ->sortBy('id')
            ->values()
            ->all();

        $practiceRows = $this->buildPracticeRows($activityData);

        $levelRatings = [];
        $activitiesByLevel = collect($activityData)->groupBy('capability_level');

        foreach ($activitiesByLevel as $level => $activities) {
            $totalScore = 0;
            $count = 0;

            foreach ($activities as $activity) {
                $totalScore += $this->evaluationService->getRatingNumericValue($activity['answer']);
                $count++;
            }

            if ($count > 0) {
                $averageScore = $totalScore / $count;
                $letter = $this->evaluationService->getScoreLetter($averageScore);

                $levelRatings[$level] = [
                    'rating' => $level.$letter,
                    'score' => round($averageScore * 100, 1),
                    'count' => $count,
                ];
            } else {
                $levelRatings[$level] = [
                    'rating' => $level.'N',
                    'score' => 0,
                    'count' => 0,
                ];
            }
        }

        $levelRows = $this->buildLevelRows($activityData, $levelRatings);

        return compact(
            'evaluation',
            'evalId',
            'objective',
            'areaObjective',
            'activityData',
            'practiceRows',
            'levelRows',
            'filterLevel',
            'currentLevel',
            'maxLevel',
            'organization',
            'targetLevel',
            'ratingString',
            'displayValue',
            'practices',
            'levelRatings',
            'practiceSummaryRows',
            'practiceSummaryTotals',
            'practiceSummaryLevelMetrics',
            'practiceSummaryCapability'
        );
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<string|int, int>}
     */
    private function buildPracticeSummary(MstObjective $objective): array
    {
        $rows = [];
        $totals = [
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            'total' => 0,
        ];

        foreach ($objective->practices as $practice) {
            $levelCounts = [
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
            ];

            foreach ($practice->activities ?? [] as $activity) {
                $rawLevel = (string) ($activity->capability_lvl ?? $activity->capability_level ?? '');
                preg_match('/(\d+)/', $rawLevel, $matches);
                $levelNumber = isset($matches[1]) ? (int) $matches[1] : null;

                if ($levelNumber !== null && array_key_exists($levelNumber, $levelCounts)) {
                    $levelCounts[$levelNumber]++;
                }
            }

            $practiceTotal = array_sum($levelCounts);
            if ($practiceTotal === 0) {
                continue;
            }

            foreach ([2, 3, 4, 5] as $levelNumber) {
                $totals[$levelNumber] += $levelCounts[$levelNumber];
            }
            $totals['total'] += $practiceTotal;

            $rows[] = [
                'practice_id' => trim((string) $practice->practice_id, '"'),
                'practice_name' => trim((string) $practice->practice_name, '"'),
                'counts' => $levelCounts,
                'total' => $practiceTotal,
            ];
        }

        usort($rows, fn (array $left, array $right) => strcmp($left['practice_id'], $right['practice_id']));

        return [$rows, $totals];
    }

    /**
     * @param  array<string|int, int>  $practiceSummaryTotals
     * @param  array<int, array<string, mixed>>  $levelScores
     * @return array<int, array<string, string>>
     */
    private function buildPracticeSummaryLevelMetrics(array $practiceSummaryTotals, array $levelScores): array
    {
        $result = [];

        foreach ([2, 3, 4, 5] as $level) {
            $hasActivities = (int) ($practiceSummaryTotals[$level] ?? 0) > 0;
            $result[$level] = [
                'index' => $hasActivities ? $this->formatSummaryNumber((float) ($levelScores[$level]['score'] ?? 0)) : '',
                'rating' => $hasActivities ? (string) ($levelScores[$level]['letter'] ?? 'N') : '',
            ];
        }

        return $result;
    }

    private function formatSummaryNumber(float $value): string
    {
        return number_format($value, 2, ',', '');
    }

    /**
     * @param  array<int, array<string, mixed>>  $activityData
     * @return array<int, array<string, mixed>>
     */
    private function buildPracticeRows(array $activityData): array
    {
        $groupCounts = collect($activityData)
            ->groupBy(fn ($item) => $item['capability_level'].'-'.$item['practice_id'])
            ->map(fn ($items) => $items->count())
            ->all();

        $seenGroups = [];
        $rowIndex = 0;

        return array_map(function (array $activity) use (&$seenGroups, &$rowIndex, $groupCounts) {
            $groupId = $activity['capability_level'].'-'.$activity['practice_id'];
            $isFirstInGroup = ! isset($seenGroups[$groupId]);

            if ($isFirstInGroup) {
                $seenGroups[$groupId] = true;
                $rowIndex++;
            }

            return array_merge($activity, [
                'show_group' => $isFirstInGroup,
                'group_rowspan' => $isFirstInGroup ? ($groupCounts[$groupId] ?? 1) : 0,
                'group_row_number' => $isFirstInGroup ? $rowIndex : null,
            ]);
        }, $activityData);
    }

    /**
     * @param  array<int, array<string, mixed>>  $activityData
     * @param  array<string, array<string, mixed>>  $levelRatings
     * @return array<int, array<string, mixed>>
     */
    private function buildLevelRows(array $activityData, array $levelRatings): array
    {
        $activityDataByLevel = collect($activityData)
            ->sortBy([
                ['capability_level', 'asc'],
                ['practice_id', 'asc'],
                ['activity_id', 'asc'],
            ])
            ->values()
            ->all();

        $groupCounts = collect($activityDataByLevel)
            ->groupBy(fn ($item) => $item['capability_level'].'-'.$item['practice_id'])
            ->map(fn ($items) => $items->count())
            ->all();

        $seenGroups = [];
        $rowIndex = 0;
        $previousLevel = null;

        return array_map(function (array $activity) use (&$seenGroups, &$rowIndex, &$previousLevel, $groupCounts, $levelRatings) {
            $currentLevel = (string) $activity['capability_level'];
            $groupId = $currentLevel.'-'.$activity['practice_id'];
            $isFirstInGroup = ! isset($seenGroups[$groupId]);
            $showLevelSeparator = $previousLevel === null || $currentLevel !== $previousLevel;

            if ($isFirstInGroup) {
                $seenGroups[$groupId] = true;
                $rowIndex++;
            }

            $ratingData = $levelRatings[$currentLevel] ?? null;
            $separatorScore = $ratingData ? number_format(($ratingData['score'] ?? 0) / 100, 2) : '0.00';
            $separatorLetter = $ratingData
                ? $this->evaluationService->getScoreLetter(($ratingData['score'] ?? 0) / 100)
                : 'N';

            $previousLevel = $currentLevel;

            return array_merge($activity, [
                'show_group' => $isFirstInGroup,
                'group_rowspan' => $isFirstInGroup ? ($groupCounts[$groupId] ?? 1) : 0,
                'group_row_number' => $isFirstInGroup ? $rowIndex : null,
                'show_level_separator' => $showLevelSeparator,
                'separator_level' => $currentLevel,
                'separator_rating_display' => $separatorLetter.' '.$separatorScore,
            ]);
        }, $activityDataByLevel);
    }
}
