<?php

namespace App\Services\Assessment\Scope;

use App\Models\MstEval;
use App\Models\TrsEvalDetail;
use App\Models\TrsScoping;
use App\Services\EvaluationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssessmentScopeService
{
    public function __construct(
        private readonly EvaluationService $evaluationService
    ) {
    }

    public function createInitialScope(MstEval $evaluation, array $selectedDomains, ?string $scopeName = null): ?TrsScoping
    {
        $domains = $this->normalizeDomains($selectedDomains);

        if ($domains === []) {
            return null;
        }

        return DB::transaction(function () use ($evaluation, $domains, $scopeName) {
            $scope = TrsScoping::firstOrCreate([
                'eval_id' => $evaluation->eval_id,
                'nama_scope' => $scopeName ?: 'Default',
            ]);

            TrsEvalDetail::where('scoping_id', $scope->id)->delete();
            $this->insertScopeDetails($evaluation->eval_id, $scope->id, $domains);

            return $scope;
        });
    }

    public function syncScope(MstEval $evaluation, array $selectedDomains, string $scopeName, ?int $scopeId = null, bool $isNew = false): TrsScoping
    {
        $domains = $this->normalizeDomains($selectedDomains);

        if ($domains === []) {
            throw new InvalidArgumentException('At least one scope objective is required.');
        }

        $scope = DB::transaction(function () use ($evaluation, $domains, $scopeName, $scopeId, $isNew) {
            if ($isNew) {
                $scope = TrsScoping::create([
                    'eval_id' => $evaluation->eval_id,
                    'nama_scope' => $scopeName,
                ]);
            } else {
                $scope = $scopeId
                    ? TrsScoping::where('id', $scopeId)->where('eval_id', $evaluation->eval_id)->first()
                    : TrsScoping::where('eval_id', $evaluation->eval_id)->first();

                if (! $scope) {
                    throw new InvalidArgumentException('Scope not found.');
                }

                $scope->update(['nama_scope' => $scopeName]);
                TrsEvalDetail::where('scoping_id', $scope->id)->delete();
            }

            $this->insertScopeDetails($evaluation->eval_id, $scope->id, $domains);

            return $scope;
        });

        $this->evaluationService->updateCalculatedScores($evaluation->eval_id);

        return $scope;
    }

    public function deleteScope(TrsScoping $scope): void
    {
        DB::transaction(function () use ($scope) {
            TrsEvalDetail::where('scoping_id', $scope->id)->delete();
            $scope->delete();
        });

        $this->evaluationService->updateCalculatedScores($scope->eval_id);
    }

    /**
     * @return array<int, array{name: string, domains: array<int, string>}>
     */
    public function buildScopeDetails(Collection $scopes): array
    {
        return $scopes->mapWithKeys(function (TrsScoping $scope) {
            return [$scope->id => [
                'name' => $scope->nama_scope,
                'domains' => TrsEvalDetail::where('scoping_id', $scope->id)->pluck('domain_id')->toArray(),
            ]];
        })->all();
    }

    /**
     * @return array<int, string>
     */
    private function normalizeDomains(array $selectedDomains): array
    {
        return collect($selectedDomains)
            ->map(fn ($domain) => trim((string) $domain))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $domains
     */
    private function insertScopeDetails(int $evalId, int $scopeId, array $domains): void
    {
        $rows = array_map(function (string $domain) use ($evalId, $scopeId) {
            return [
                'eval_id' => $evalId,
                'scoping_id' => $scopeId,
                'domain_id' => $domain,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $domains);

        TrsEvalDetail::insert($rows);
    }
}
