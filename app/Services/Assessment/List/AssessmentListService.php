<?php

namespace App\Services\Assessment\List;

use App\Models\MstOrganization;
use App\Models\MstEval;
use App\Models\TargetCapability;
use App\Models\TargetMaturity;
use App\Models\TrsActivityeval;
use App\Models\TrsEvalDetail;
use App\Models\TrsObjectiveScore;
use App\Models\TrsScoping;
use App\Models\User;
use App\Services\Assessment\Access\AssessmentAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentListService
{
    public function __construct(
        private readonly AssessmentAccessService $assessmentAccessService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getIndexData(User $user): array
    {
        $organizationOptions = $user->assignedOrganizations();
        $activeOrgIds = $user->activeOrganizationIds($organizationOptions);
        $activeOrgId = $user->activeOrganizationId($organizationOptions);
        $hasVisibilityColumn = Schema::hasColumn('mst_eval', 'show_on_user_page');

        $applyOrgFilter = function (Builder $query) use ($activeOrgIds) {
            if ($activeOrgIds !== []) {
                $query->where(function (Builder $builder) use ($activeOrgIds) {
                    $builder->whereIn('mst_eval.organization_id', $activeOrgIds)
                        ->orWhere(function (Builder $fallbackQuery) use ($activeOrgIds) {
                            $fallbackQuery->whereNull('mst_eval.organization_id')
                                ->whereHas('user', function (Builder $ownerQuery) use ($activeOrgIds) {
                                    $ownerQuery->whereIn('organization_id', $activeOrgIds)
                                        ->orWhereHas('organizations', fn (Builder $organizationQuery) => $organizationQuery->whereIn('mst_organization.organization_id', $activeOrgIds));
                                });
                        });
                });
            }
        };

        $myQuery = $this->assessmentAccessService
            ->queryAccessible($user)
            ->when($hasVisibilityColumn, fn (Builder $query) => $query->where('mst_eval.show_on_user_page', true))
            ->with(['user', 'organization', 'maturityScore', 'accessAssignments'])
            ->where('mst_eval.user_id', $user->id)
            ->orderBy('mst_eval.created_at', 'desc');
        $applyOrgFilter($myQuery);

        $assignedQuery = $this->assessmentAccessService
            ->queryAccessible($user)
            ->when($hasVisibilityColumn, fn (Builder $query) => $query->where('mst_eval.show_on_user_page', true))
            ->with(['user', 'organization', 'maturityScore', 'accessAssignments'])
            ->where('mst_eval.user_id', '!=', $user->id)
            ->orderBy('mst_eval.created_at', 'desc');
        $applyOrgFilter($assignedQuery);

        $myAssessments = $myQuery->paginate(10, ['*'], 'my_page');
        $assignedAssessments = $assignedQuery->paginate(10, ['*'], 'assigned_page');

        $this->enrichEvaluations(
            collect($myAssessments->items())->merge($assignedAssessments->items())
        );

        foreach (collect($myAssessments->items())->merge($assignedAssessments->items()) as $evaluation) {
            $evaluation->can_manage = $this->assessmentAccessService->canManage($user, $evaluation);
        }

        $statsQuery = $this->assessmentAccessService->queryAccessible($user)
            ->when($hasVisibilityColumn, fn (Builder $query) => $query->where('mst_eval.show_on_user_page', true));
        $applyOrgFilter($statsQuery);

        $totalAssessments = (clone $statsQuery)->count();
        $finishedAssessments = (clone $statsQuery)->where('mst_eval.status', 'finished')->count();

        return [
            'myAssessments' => $myAssessments,
            'assignedAssessments' => $assignedAssessments,
            'totalAssessments' => $totalAssessments,
            'finishedAssessments' => $finishedAssessments,
            'draftAssessments' => max(0, $totalAssessments - $finishedAssessments),
            'organizationOptions' => $organizationOptions,
            'selectedOrganizationId' => $activeOrgId ?: $organizationOptions->first()?->organization_id,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getAdminIndexData(array $filters = []): array
    {
        $query = MstEval::query()
            ->with(['user', 'organization', 'maturityScore'])
            ->withCount('accessAssignments')
            ->orderByDesc('created_at');

        $this->applyAdminFilters($query, $filters);

        $evaluations = $query->paginate(15)->withQueryString();
        $this->enrichEvaluations(collect($evaluations->items()));

        $baseStatsQuery = MstEval::query();

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'finished' => (clone $baseStatsQuery)->where('status', 'finished')->count(),
            'organizations' => (clone $baseStatsQuery)
                ->whereNotNull('organization_id')
                ->distinct('organization_id')
                ->count('organization_id'),
            'owners' => (clone $baseStatsQuery)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id'),
        ];
        $stats['draft'] = max(0, $stats['total'] - $stats['finished']);

        return [
            'evaluations' => $evaluations,
            'stats' => $stats,
            'organizationOptions' => MstOrganization::query()
                ->where('is_active', true)
                ->orderBy('organization_name')
                ->get(['organization_id', 'organization_name']),
            'yearOptions' => MstEval::query()
                ->whereNotNull('tahun')
                ->distinct()
                ->orderByDesc('tahun')
                ->pluck('tahun'),
        ];
    }

    /**
     * @param  Collection<int, MstEval>  $evaluations
     */
    private function enrichEvaluations(Collection $evaluations): void
    {
        if ($evaluations->isEmpty()) {
            return;
        }

        $evalIds = $evaluations->pluck('eval_id')->unique()->values()->all();

        $scopesByEvaluation = TrsScoping::whereIn('eval_id', $evalIds)
            ->orderBy('id')
            ->get(['id', 'eval_id', 'nama_scope'])
            ->groupBy('eval_id');

        $scopeIds = $scopesByEvaluation->flatten(1)->pluck('id')->all();
        $detailsByScope = $scopeIds === []
            ? collect()
            : TrsEvalDetail::whereIn('scoping_id', $scopeIds)
                ->get(['scoping_id', 'domain_id'])
                ->groupBy('scoping_id');

        $scoresByEvaluation = TrsObjectiveScore::whereIn('eval_id', $evalIds)
            ->get(['eval_id', 'objective_id', 'level'])
            ->groupBy('eval_id')
            ->map(fn (Collection $scores) => $scores->keyBy('objective_id'));

        $lastActivityDates = TrsActivityeval::whereIn('eval_id', $evalIds)
            ->select('eval_id', DB::raw('MAX(updated_at) as last_activity_at'))
            ->groupBy('eval_id')
            ->pluck('last_activity_at', 'eval_id');

        foreach ($evaluations as $evaluation) {
            $targetCapability = $this->findTargetCapability($evaluation);
            $objectiveScores = $scoresByEvaluation->get($evaluation->eval_id, collect());
            $scopeSummaries = ($scopesByEvaluation->get($evaluation->eval_id) ?? collect())
                ->values()
                ->map(function (TrsScoping $scope, int $index) use ($detailsByScope, $objectiveScores, $targetCapability): array {
                    $objectiveIds = ($detailsByScope->get($scope->id) ?? collect())
                        ->pluck('domain_id')
                        ->map(fn ($id) => strtoupper(trim((string) $id)))
                        ->filter()
                        ->unique()
                        ->values();

                    $scopeScore = $objectiveIds->isEmpty()
                        ? null
                        : round($objectiveIds->sum(fn ($objectiveId) => (float) ($objectiveScores->get($objectiveId)?->level ?? 0)) / $objectiveIds->count(), 2);

                    return [
                        'id' => (int) $scope->id,
                        'number' => $index + 1,
                        'name' => trim((string) $scope->nama_scope),
                        'objective_ids' => $objectiveIds->all(),
                        'gamo_count' => $objectiveIds->count(),
                        'maturity_score' => $scopeScore,
                        'avg_target_capability' => $this->averageTargetCapability($targetCapability, $objectiveIds->all()),
                    ];
                });

            $evaluation->scope_summaries = $scopeSummaries;
            $evaluation->scope_count = $scopeSummaries->count();
            $evaluation->last_saved_at = $lastActivityDates[$evaluation->eval_id] ?? $evaluation->created_at;
            $evaluation->avg_target_capability = $this->averageTargetCapability($targetCapability);
            $evaluation->target_maturity = $this->resolveTargetMaturity($evaluation);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyAdminFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                if (is_numeric($search)) {
                    $builder->where('eval_id', (int) $search)
                        ->orWhere('tahun', 'like', '%' . $search . '%');
                } else {
                    $builder->where('tahun', 'like', '%' . $search . '%');
                }

                $builder->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('organization', function (Builder $organizationQuery) use ($search): void {
                        $organizationQuery->where('organization_name', 'like', '%' . $search . '%');
                    });
            });
        }

        if (filled($filters['organization_id'] ?? null)) {
            $query->where('organization_id', (int) $filters['organization_id']);
        }

        if (filled($filters['status'] ?? null)) {
            if ((string) $filters['status'] === 'finished') {
                $query->where('status', 'finished');
            } else {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('status')
                        ->orWhere('status', '!=', 'finished');
                });
            }
        }

        if (filled($filters['year'] ?? null)) {
            $query->where('tahun', (string) $filters['year']);
        }
    }

    private function findTargetCapability(MstEval $evaluation): ?TargetCapability
    {
        return TargetCapability::query()
            ->where('tahun', $evaluation->tahun)
            ->when(
                filled($evaluation->organization_id),
                fn ($query) => $query->where('organization_id', $evaluation->organization_id),
                fn ($query) => $query->where('user_id', $evaluation->user_id)
            )
            ->latest('target_id')
            ->first();
    }

    /** @param array<int, string>|null $objectiveIds */
    private function averageTargetCapability(?TargetCapability $target, ?array $objectiveIds = null): float
    {
        if (! $target) {
            return 0;
        }

        $domainCols = [
            'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
            'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07', 'APO08', 'APO09', 'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
            'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
            'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
            'MEA01', 'MEA02', 'MEA03', 'MEA04',
        ];

        $sum = 0;
        $count = 0;
        foreach ($domainCols as $col) {
            if ($objectiveIds !== null && ! in_array($col, $objectiveIds, true)) {
                continue;
            }

            $value = $target->{$col};
            if ($value !== null && is_numeric($value)) {
                $sum += $value;
                $count++;
            }
        }

        return $count > 0 ? round($sum / $count, 2) : 0;
    }

    private function resolveTargetMaturity(MstEval $evaluation): ?float
    {
        $target = TargetMaturity::query()
            ->where('tahun', $evaluation->tahun)
            ->when(
                filled($evaluation->organization_id),
                fn ($query) => $query->where('organization_id', $evaluation->organization_id),
                fn ($query) => $query->where('user_id', $evaluation->user_id)
            )
            ->latest('id')
            ->first();

        return $target?->target_maturity;
    }
}
