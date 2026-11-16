<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\MstEvidence;
use App\Models\MstOrganization;
use App\Models\User;
use App\Http\Requests\AssessmentEval\UpsertEvidenceRequest;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Evidence\AssessmentEvidenceService;
use App\Services\Assessment\Evidence\EvidenceReferenceCatalog;
use App\Services\Cobit\GamoOverviewService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterEvidenceController extends Controller
{
    public function __construct(
        private readonly AssessmentAccessService $assessmentAccessService,
        private readonly EvidenceReferenceCatalog $referenceCatalog,
        private readonly GamoOverviewService $gamoOverview
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $organizationOptions = $user->assignedOrganizations();
        $selectedOrganizationId = null;
        $selectedOrganization = null;
        $evidences = null;
        $manageableEvaluationIds = [];
        $canManageMasterEvidence = false;
        $referenceCatalogReady = $this->referenceCatalog->available();
        $evidenceReferenceOptions = $this->referenceCatalog->options();
        $searchTerm = trim((string) $request->query('search', ''));
        $selectedGroup = (string) $request->query('grup', '');
        $selectedType = (string) $request->query('tipe', '');
        $selectedReferenceGamo = $request->query('reference_gamo');

        $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'grup' => ['nullable', Rule::in(['EDM', 'APO', 'BAI', 'DSS', 'MEA'])],
            'tipe' => ['nullable', Rule::in(['Design', 'Implementation'])],
            'reference_gamo' => ['nullable', 'string', 'max:255'],
        ]);

        $referenceOverview = $this->gamoOverview->data(1, $selectedReferenceGamo);
        $selectedReferenceGamo = $referenceOverview['objective']->objective_id;

        if ($request->filled('organization_id')) {
            $selectedOrganizationId = filter_var(
                $request->query('organization_id'),
                FILTER_VALIDATE_INT
            );

            abort_unless($selectedOrganizationId, 404);

            $selectedOrganization = $organizationOptions->firstWhere(
                'organization_id',
                (int) $selectedOrganizationId
            );

            abort_unless($selectedOrganization, 403);

            $evaluationQuery = $this->evaluationsForOrganization($user, $selectedOrganization);

            $canManageMasterEvidence = $user->can('assessments.input');

            if ($canManageMasterEvidence) {
                $manageableEvaluationIds = (clone $evaluationQuery)
                    ->orderByDesc('tahun')
                    ->get(['mst_eval.eval_id', 'mst_eval.user_id', 'mst_eval.organization_id', 'mst_eval.tahun'])
                    ->filter(fn ($evaluation): bool => $this->assessmentAccessService->canManage($user, $evaluation))
                    ->pluck('eval_id')
                    ->map(fn ($evalId): int => (int) $evalId)
                    ->values()
                    ->all();
            }

            $evidenceQuery = MstEvidence::query()
                ->with(['evaluation.organization', 'evaluation.user', 'organization'])
                ->where(function (Builder $query) use ($evaluationQuery, $selectedOrganization): void {
                    $query->where(function (Builder $masterQuery) use ($selectedOrganization): void {
                        $masterQuery->whereNull('eval_id')
                            ->where('organization_id', $selectedOrganization->organization_id);
                    })->orWhere(function (Builder $assessmentQuery) use ($evaluationQuery): void {
                        $assessmentQuery->whereNotNull('eval_id')
                            ->whereIn('eval_id', $evaluationQuery->select('mst_eval.eval_id'));
                    });
                });

            if ($referenceCatalogReady) {
                $evidenceQuery->with('references');
            }

            if ($searchTerm !== '') {
                $evidenceQuery->where(function (Builder $query) use ($searchTerm, $referenceCatalogReady): void {
                    $search = '%'.$searchTerm.'%';

                    $query->where('judul_dokumen', 'like', $search)
                        ->orWhere('no_dokumen', 'like', $search)
                        ->orWhere('summary', 'like', $search)
                        ->orWhere('grup', 'like', $search)
                        ->orWhere('tipe', 'like', $search)
                        ->orWhere('ket_tipe', 'like', $search)
                        ->orWhere('pemilik_dokumen', 'like', $search)
                        ->orWhere('klasifikasi', 'like', $search)
                        ->orWhereHas('evaluation', function (Builder $evaluationQuery) use ($search): void {
                            $evaluationQuery->where('eval_id', 'like', $search)
                                ->orWhere('tahun', 'like', $search);
                        });

                    if ($referenceCatalogReady) {
                        $query->orWhereHas('references', function (Builder $referenceQuery) use ($search): void {
                            $referenceQuery->where('nama_dokumen', 'like', $search)
                                ->orWhere('objective_id', 'like', $search);
                        });
                    }
                });
            }

            if ($selectedGroup !== '') {
                $evidenceQuery->where('grup', 'like', '%'.$selectedGroup.'%');
            }

            if ($selectedType !== '') {
                $evidenceQuery->where('tipe', $selectedType);
            }

            $evidences = $evidenceQuery
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString()->fragment('company-evidence');
        }

        return view('assessment.master-evidence.index', compact(
            'organizationOptions',
            'selectedOrganization',
            'selectedOrganizationId',
            'evidences',
            'manageableEvaluationIds',
            'canManageMasterEvidence',
            'searchTerm',
            'selectedGroup',
            'selectedType',
            'referenceCatalogReady',
            'evidenceReferenceOptions',
            'referenceOverview',
            'selectedReferenceGamo'
        ));
    }

    public function store(Request $request, AssessmentEvidenceService $evidenceService)
    {
        $user = $request->user();
        $organizationOptions = $user->assignedOrganizations();
        $organizationIds = $organizationOptions->pluck('organization_id')->map(fn ($id): int => (int) $id)->all();

        $validated = $request->validate(array_merge(
            [
                'organization_id' => ['required', 'integer', Rule::in($organizationIds)],
            ],
            UpsertEvidenceRequest::evidenceRules(),
            $this->referenceCatalog->rules()
        ));

        $organization = $organizationOptions->firstWhere('organization_id', (int) $validated['organization_id']);
        abort_unless($organization, 403);

        $evidence = DB::transaction(function () use ($organization, $validated, $evidenceService) {
            $evidence = $evidenceService->storeForOrganization(
                $organization,
                Arr::except($validated, ['organization_id', 'reference_ids'])
            );
            $this->referenceCatalog->synchronize($evidence, $validated);

            return $evidence;
        });

        return response()->json([
            'success' => true,
            'message' => 'Evidence berhasil ditambahkan ke Master Evidence.',
            'data' => $evidence,
        ], 201);
    }

    public function destroy(Request $request, int $evidenceId)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer'],
            'confirmation' => ['required', 'in:Saya Yakin'],
        ]);

        $user = $request->user();
        abort_unless($user->can('assessments.input'), 403);

        $organization = $user->assignedOrganizations()->firstWhere(
            'organization_id',
            (int) $validated['organization_id']
        );
        abort_unless($organization, 403);

        $evidence = MstEvidence::findOrFail($evidenceId);
        $this->ensureEvidenceIsManageable($user, $organization, $evidence);

        $evidence->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evidence berhasil dihapus dari Master Evidence.',
        ]);
    }

    public function update(Request $request, int $evidenceId, AssessmentEvidenceService $evidenceService)
    {
        $validated = $request->validate(array_merge(
            ['organization_id' => ['required', 'integer']],
            UpsertEvidenceRequest::evidenceRules(),
            $this->referenceCatalog->rules()
        ));

        $user = $request->user();
        abort_unless($user->can('assessments.input'), 403);

        $organization = $user->assignedOrganizations()->firstWhere(
            'organization_id',
            (int) $validated['organization_id']
        );
        abort_unless($organization, 403);

        $evidence = MstEvidence::findOrFail($evidenceId);
        $this->ensureEvidenceIsManageable($user, $organization, $evidence);

        $evidence = DB::transaction(function () use ($evidence, $validated, $evidenceService) {
            $evidence = $evidenceService->update($evidence, Arr::except($validated, ['organization_id', 'reference_ids']));
            $this->referenceCatalog->synchronize($evidence, $validated);

            return $evidence;
        });

        return response()->json([
            'success' => true,
            'message' => 'Evidence berhasil diperbarui.',
            'data' => $evidence,
        ]);
    }

    private function ensureEvidenceIsManageable(User $user, MstOrganization $organization, MstEvidence $evidence): void
    {
        if ($evidence->eval_id === null) {
            abort_unless((int) $evidence->organization_id === (int) $organization->organization_id, 403);

            return;
        }

        $evaluation = $this->evaluationsForOrganization($user, $organization)
            ->where('mst_eval.eval_id', $evidence->eval_id)
            ->first();

        abort_unless(
            $evaluation && $this->assessmentAccessService->canManage($user, $evaluation),
            403
        );
    }

    private function evaluationsForOrganization(User $user, MstOrganization $organization): Builder
    {
        $organizationId = (int) $organization->organization_id;

        return $this->assessmentAccessService
            ->queryAccessible($user)
            ->where(function (Builder $query) use ($organization, $organizationId): void {
                $query->where('organization_id', $organizationId)
                    ->orWhere(function (Builder $legacyQuery) use ($organization, $organizationId): void {
                        $legacyQuery->whereNull('organization_id')
                            ->whereHas('user', function (Builder $ownerQuery) use ($organization, $organizationId): void {
                                $ownerQuery->where('organization_id', $organizationId)
                                    ->orWhereHas('organizations', fn (Builder $organizationQuery) =>
                                        $organizationQuery->where('mst_organization.organization_id', $organizationId)
                                    )
                                    ->orWhere('organisasi', $organization->organization_name);
                            });
                    });
            });
    }
}
