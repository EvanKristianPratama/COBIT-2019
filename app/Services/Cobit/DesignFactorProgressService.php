<?php

namespace App\Services\Cobit;

use App\Data\Cobit\Df10Data;
use App\Data\Cobit\Df1Data;
use App\Data\Cobit\Df2Data;
use App\Data\Cobit\Df3Data;
use App\Data\Cobit\Df4Data;
use App\Data\Cobit\Df5Data;
use App\Data\Cobit\Df6Data;
use App\Data\Cobit\Df7Data;
use App\Data\Cobit\Df8Data;
use App\Data\Cobit\Df9Data;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DesignFactorProgressService
{
    private const QUESTION_COUNTS = [
        1 => Df1Data::INPUT_COUNT, 2 => Df2Data::INPUT_COUNT, 3 => Df3Data::INPUT_COUNT,
        4 => Df4Data::INPUT_COUNT, 5 => Df5Data::INPUT_COUNT, 6 => Df6Data::INPUT_COUNT,
        7 => Df7Data::INPUT_COUNT, 8 => Df8Data::INPUT_COUNT, 9 => Df9Data::INPUT_COUNT,
        10 => Df10Data::INPUT_COUNT,
    ];

    public function forAssessments(Collection $assessments, int $userId): array
    {
        if ($assessments->isEmpty()) {
            return [];
        }

        $ids = $assessments->pluck('assessment_id')->all();
        $progress = [];
        foreach ($assessments as $assessment) {
            $progress[$assessment->assessment_id] = [
                'factors' => [], 'steps' => [],
                'last_updated_at' => null,
            ];
        }

        foreach (self::QUESTION_COUNTS as $number => $total) {
            $table = $number === 3 ? 'design_factor_3_a' : 'design_factor_'.$number;
            $primaryKey = 'df'.$number.'_id';
            $fields = array_map(fn ($index) => 'input'.$index.'df'.$number, range(1, $total));
            // DF saves append a new submission. Read one latest submission per
            // assessment, without combining answers from different histories.
            $latest = DB::table($table)->whereIn('assessment_id', $ids)->where('id', $userId)
                ->select('assessment_id')->selectRaw('MAX('.$primaryKey.') as latest_id')->groupBy('assessment_id');
            $records = DB::table($table.' as answers')->where('answers.id', $userId)
                ->joinSub($latest, 'latest', fn ($join) => $join->on('answers.'.$primaryKey, '=', 'latest.latest_id'))
                ->select(array_map(fn ($field) => 'answers.'.$field, ['assessment_id', 'created_at', 'updated_at', ...$fields]))
                ->get()->keyBy('assessment_id');

            foreach ($ids as $id) {
                $record = $records->get($id);
                $answered = $record ? collect($fields)->filter(fn ($field) => $this->isAnswered($number, $record->{$field}))->count() : 0;
                $progress[$id]['factors'][$number] = ['answered' => $answered, 'total' => $total];
                $this->updateTimestamp($progress[$id], $record?->updated_at ?? $record?->created_at);
            }
        }

        foreach ([2, 3, 4] as $step) {
            // Step results belong to a respondent. Never combine partial results
            // from several respondents into a misleading complete 40/40 status.
            $records = DB::table('trs_step'.$step)->whereIn('assessment_id', $ids)->where('user_id', $userId)
                ->whereBetween('objective_code', [1, 40])->groupBy('assessment_id', 'user_id')
                ->select('assessment_id', 'user_id')
                ->selectRaw('COUNT(DISTINCT objective_code) as saved_count, MAX(COALESCE(updated_at, created_at)) as saved_at, MAX(id) as latest_id')
                ->orderByDesc('saved_at')->orderByDesc('latest_id')->get()->unique('assessment_id')->keyBy('assessment_id');

            foreach ($ids as $id) {
                $record = $records->get($id);
                $count = (int) ($record?->saved_count ?? 0);
                $progress[$id]['steps'][$step] = ['saved' => $count, 'complete' => $count === 40];
                $this->updateTimestamp($progress[$id], $record?->saved_at);
            }
        }

        return $progress;
    }

    private function isAnswered(int $factor, mixed $value): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        $percentage = in_array($factor, [5, 6, 8, 9, 10], true);
        $minimum = $percentage ? 0 : 1;
        $maximum = $percentage ? 100 : match ($factor) {
            3 => 25, 4 => 3, default => 5
        };

        return (float) $value >= $minimum && (float) $value <= $maximum;
    }

    private function updateTimestamp(array &$progress, mixed $value): void
    {
        if ($value === null) {
            return;
        }
        $date = Carbon::parse($value);
        if (! $progress['last_updated_at'] || $date->greaterThan($progress['last_updated_at'])) {
            $progress['last_updated_at'] = $date;
        }
    }
}
