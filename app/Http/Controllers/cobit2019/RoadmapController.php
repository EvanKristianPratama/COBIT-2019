<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\TrsEvalDetail;
use App\Models\TrsRoadmap;
use App\Models\TrsScoping;
use App\Models\TrsStep4;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Cobit\CobitAssessmentAccessService;
use App\Services\Cobit\CobitCoreModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoadmapController extends Controller
{
    public function __construct(
        private readonly CobitCoreModelService $coreModel,
        private readonly CobitAssessmentAccessService $designFactorAccess,
        private readonly AssessmentAccessService $assessmentAccess,
    ) {}

    public function index(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $objectives = $this->coreModel->objectives();
        $roadmaps = $this->roadmaps($organizationId, $objectives->pluck('objective_id')->all())->get();
        $mappedRoadmaps = $this->mapRoadmaps($roadmaps);
        $sessionKey = 'roadmap_temp_years.'.$organizationId;
        $years = $roadmaps->pluck('year')->map(fn ($year) => (int) $year)
            ->merge(session($sessionKey, []));
        if ($request->filled('add_year')) {
            $data = $request->validate(['add_year' => 'required|integer|between:2000,2100']);
            $years->push((int) $data['add_year']);
            session([$sessionKey => $years->unique()->values()->all()]);
        }
        $years = $years->unique()->sort()->values()->all() ?: [(int) now()->year];
        $assessments = $this->designFactorAccess->queryForOrganization($request->user(), $organizationId)
            ->orderByDesc('created_at')->get();
        $evals = $request->user()->can('assessments.view') ? $this->assessmentAccess->queryAccessible($request->user())
            ->where('organization_id', $organizationId)
            ->where('focus_area_id', $objectives->first()?->focus_area_id ?? 0)
            ->orderByDesc('created_at')->get() : collect();
        $selectedAssessmentId = $request->query('assessment_id', session('assessment_id'));
        if (! $assessments->contains('assessment_id', $selectedAssessmentId)) {
            $selectedAssessmentId = null;
        }
        $organizations = $this->organizations($request);
        $canManageRoadmap = $request->user()->can('design-factors.input');

        return view('design_factor.roadmap.index', compact('objectives', 'mappedRoadmaps', 'years', 'assessments', 'evals', 'selectedAssessmentId', 'organizationId', 'organizations', 'canManageRoadmap'));
    }

    public function step4Scope(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $data = $request->validate(['assessment_id' => 'required|integer']);
        $assessment = $this->designFactorAccess->queryForOrganization($request->user(), $organizationId)
            ->findOrFail($data['assessment_id']);
        $rows = TrsStep4::where('assessment_id', $assessment->assessment_id)
            ->where('user_id', $request->user()->id)->where('is_selected', 1)
            ->whereIn('objective_id', $this->coreModel->objectives()->pluck('objective_id'))
            ->get(['objective_id', 'agreed_level']);

        return response()->json(['assessment_id' => $assessment->assessment_id, 'objectives' => $rows->map(fn ($row) => [
            'objective_id' => $row->objective_id, 'agreed_level' => (int) $row->agreed_level,
        ])->values()]);
    }

    public function scopingOptions(Request $request)
    {
        abort_unless($request->user()->can('assessments.view'), 403);
        $organizationId = $this->organizationId($request);
        $data = $request->validate(['eval_id' => 'required|integer']);
        $objectives = $this->coreModel->objectives();
        $evaluation = $this->assessmentAccess->queryAccessible($request->user())
            ->where('organization_id', $organizationId)
            ->where('focus_area_id', $objectives->first()?->focus_area_id ?? 0)->findOrFail($data['eval_id']);
        $scopes = TrsScoping::where('eval_id', $evaluation->eval_id)->get();
        $details = TrsEvalDetail::whereIn('scoping_id', $scopes->pluck('id'))->get()->groupBy('scoping_id');
        $payload = $scopes->map(function ($scope) use ($details, $objectives): array {
            $domains = ($details->get($scope->id) ?? collect())->pluck('domain_id')->map(fn ($domain) => strtoupper(trim($domain)));
            $ids = $objectives->pluck('objective_id')->filter(fn ($id) => $domains->contains($id) || $domains->contains(substr($id, 0, 3)))->values()->all();

            return ['id' => $scope->id, 'name' => $scope->nama_scope, 'objectives' => $ids];
        });

        return response()->json(['scopes' => $payload]);
    }

    public function report(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $organizations = $this->organizations($request);
        $objectives = $this->coreModel->objectives();
        $roadmaps = $this->roadmaps($organizationId, $objectives->pluck('objective_id')->all())->get();
        $mappedRoadmaps = $this->mapRoadmaps($roadmaps);
        $years = $roadmaps->pluck('year')->unique()->sort()->values()->all();

        return view('design_factor.roadmap.report', compact('objectives', 'mappedRoadmaps', 'years', 'organizationId', 'organizations'));
    }

    public function store(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $objectives = $this->coreModel->objectives()->keyBy('objective_id');
        $data = $request->validate([
            'roadmap' => 'required|array|max:40',
            'roadmap.*' => 'required|array|max:101',
            'roadmap.*.*' => 'required|array',
            'roadmap.*.*.objective_id' => ['required', 'string', Rule::in($objectives->keys()->all())],
            'roadmap.*.*.year' => 'required|integer|between:2000,2100',
            'roadmap.*.*.level' => 'nullable|integer|between:0,5',
            'roadmap.*.*.rating' => ['nullable', 'string', 'regex:/^[0-5][LFP]$/'],
        ]);
        foreach ($data['roadmap'] as $row) {
            foreach ($row as $item) {
                $level = $item['level'] ?? null;
                $rating = $item['rating'] ?? null;
                $allowed = $level !== null ? [$level.'L', $level.'F', ...($level < 5 ? [($level + 1).'P'] : [])] : [];
                if ($rating !== null && ! in_array($rating, $allowed, true)) {
                    throw ValidationException::withMessages(['roadmap' => 'Rating harus sesuai dengan level target.']);
                }
            }
        }
        DB::transaction(function () use ($data, $organizationId, $objectives): void {
            foreach ($data['roadmap'] as $row) {
                foreach ($row as $item) {
                    TrsRoadmap::updateOrCreate([
                        'organization_id' => $organizationId, 'objective_id' => $item['objective_id'], 'year' => $item['year'],
                    ], [
                        'focus_area_id' => $objectives[$item['objective_id']]->focus_area_id,
                        'level' => $item['level'] ?? null, 'rating' => $item['rating'] ?? null,
                    ]);
                }
            }
        });
        session()->forget('roadmap_temp_years.'.$organizationId);

        return redirect()->route('roadmap.index', ['organization_id' => $organizationId])->with('success', 'Roadmap perusahaan berhasil disimpan.');
    }

    public function addYear(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $data = $request->validate(['year' => 'required|integer|between:2000,2100']);

        return redirect()->route('roadmap.index', ['organization_id' => $organizationId, 'add_year' => $data['year']]);
    }

    public function deleteYear(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $data = $request->validate(['year' => 'required|integer|between:2000,2100']);
        $this->roadmaps($organizationId, $this->coreModel->objectives()->pluck('objective_id')->all())
            ->where('year', $data['year'])->delete();
        $key = 'roadmap_temp_years.'.$organizationId;
        session([$key => array_values(array_filter(session($key, []), fn ($year) => (int) $year !== (int) $data['year']))]);

        return redirect()->route('roadmap.index', ['organization_id' => $organizationId])->with('success', 'Roadmap tahun '.$data['year'].' untuk perusahaan ini berhasil dihapus.');
    }

    private function organizations(Request $request)
    {
        $organizations = $request->user()->assignedOrganizations();

        return $organizations->whereIn('organization_id', $request->user()->activeOrganizationIds($organizations))->values();
    }

    private function organizationId(Request $request): int
    {
        $ids = $request->user()->activeOrganizationIds();
        $data = $request->validate(['organization_id' => ['nullable', 'integer', Rule::in($ids)]]);
        $id = $data['organization_id'] ?? $request->user()->activeOrganizationId();
        abort_unless($id && in_array((int) $id, $ids, true), 403, 'Pilih perusahaan terlebih dahulu.');

        return (int) $id;
    }

    private function roadmaps(int $organizationId, array $objectiveIds)
    {
        return TrsRoadmap::where('organization_id', $organizationId)->whereIn('objective_id', $objectiveIds);
    }

    private function mapRoadmaps($roadmaps): array
    {
        $mapped = [];
        foreach ($roadmaps as $roadmap) {
            $mapped[$roadmap->objective_id][$roadmap->year] = ['level' => $roadmap->level, 'rating' => $roadmap->rating];
        }

        return $mapped;
    }
}
