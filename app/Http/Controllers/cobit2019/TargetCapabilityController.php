<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstOrganization;
use App\Models\TargetCapability;
use App\Services\Organization\OrganizationRegistryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class TargetCapabilityController extends Controller
{
    public function __construct(
        private readonly OrganizationRegistryService $organizationRegistryService
    ) {
    }

    private const FIELDS = [
        'EDM01','EDM02','EDM03','EDM04','EDM05',
        'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
        'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
        'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
        'MEA01','MEA02','MEA03','MEA04',
    ];

    // maksimum per field (urutan mengikuti FIELDS)
    private const DATA_MAXIMUM = [
        4, 5, 4, 4, 4, 5, 4, 5, 4, 5, 5, 4, 5, 4, 5, 5, 5, 5, 5, 5,
        4, 4, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4
    ];

    public function edit(Request $request, $id = null)
    {
        $user = Auth::user();
        $organizationOptions = $user->organizations()
            ->select('mst_organization.organization_id', 'organization_name')
            ->orderByPivot('is_primary', 'desc')
            ->orderBy('organization_name')
            ->get();

        $targetQuery = TargetCapability::with('organization')
            ->where('user_id', $user->id);

        $target = $id
            ? (clone $targetQuery)->findOrFail($id)
            : null;

        $selectedOrganizationId = $this->resolveSelectedOrganizationId($request, $target);

        if (! $id) {
            $target = (clone $targetQuery)
                ->when($selectedOrganizationId, fn ($query) => $query->where('organization_id', $selectedOrganizationId))
                ->latest('updated_at')
                ->first();
        }

        // Domain mapping
        $domains = [
            'EDM' => ['EDM01','EDM02','EDM03','EDM04','EDM05'],
            'APO' => ['APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14'],
            'BAI' => ['BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11'],
            'DSS' => ['DSS01','DSS02','DSS03','DSS04','DSS05','DSS06'],
            'MEA' => ['MEA01','MEA02','MEA03','MEA04'],
        ];

        // Flatten codes
        $flatCodes = collect($domains)->flatten()->values()->all();

        // buat map code => max untuk view
        $maxMap = array_combine(self::FIELDS, self::DATA_MAXIMUM);
        $totalFields = count(self::FIELDS);

        // ambil seluruh target user untuk ditampilkan berdampingan per tahun
        $allTargets = TargetCapability::where('user_id', $user->id)
            ->with('organization')
            ->when($selectedOrganizationId, fn ($query) => $query->where('organization_id', $selectedOrganizationId))
            ->orderBy('tahun', 'desc')
            ->get();

        $title = 'Target Capability & Maturity';

        return view('cobit2019.targetCapability', compact(
            'target', 
            'maxMap', 
            'totalFields', 
            'allTargets',
            'domains',
            'flatCodes',
            'title',
            'organizationOptions',
            'selectedOrganizationId'
        ));
    }

    public function save(Request $request)
    {
        // gunakan validasi yang sudah memeriksa max per field
        $data = $request->validate(array_merge($this->baseRules(), $this->capabilityRules()));

        $payload = $this->extractPayload($data);

        if (!empty($data['target_id'])) {
            $model = TargetCapability::query()
                ->where('user_id', Auth::id())
                ->findOrFail($data['target_id']);
            $model->fill($payload)->save();
            $id = $model->target_id;
        } else {
            $model = TargetCapability::create($payload);
            $id = $model->target_id;
        }

        return Redirect::back()->with('success', 'Target capability saved.')->with('target_id', $id);
    }

    /**
     * Buat tahun baru (salin organisasi & user) -> redirect ke edit record baru
     */
    public function addYear(Request $request)
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'organization_id' => [
                'required',
                'integer',
                'exists:mst_organization,organization_id',
                Rule::in(Auth::user()?->organizationIds() ?? []),
            ],
        ]);

        $userId = Auth::id();
        $organizationId = (int) $validated['organization_id'];

        // cari tahun terbaru milik user
        $latest = TargetCapability::where('user_id', $userId)
            ->where('organization_id', $organizationId)
            ->orderBy('tahun', 'desc')
            ->first();
        $nextYear = isset($validated['tahun']) ? (int) $validated['tahun'] : ($latest ? ((int) $latest->tahun + 1) : (int) now()->year);

        $payload = [
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'organisasi' => $this->organizationRegistryService->resolveName($organizationId, Auth::user()->organisasi),
            'tahun' => $nextYear,
        ];

        // set semua capability null
        foreach (self::FIELDS as $code) {
            $payload[$code] = null;
        }

        $model = TargetCapability::create($payload);

        return Redirect::route('target-capability.edit', ['id' => $model->target_id])
            ->with('success', 'Tahun baru dibuat.')
            ->with('target_id', $model->target_id);
    }

    private function baseRules(): array
    {
        return [
            'target_id'  => 'nullable|integer',
            'organization_id' => [
                'required',
                'integer',
                'exists:mst_organization,organization_id',
                Rule::in(Auth::user()?->organizationIds() ?? []),
            ],
            'tahun'      => 'required|integer|min:2000|max:2099',
        ];
    }

    private function capabilityRules(): array
    {
        // buat map code => max sesuai DATA_MAXIMUM
        $map = array_combine(self::FIELDS, self::DATA_MAXIMUM);
        $rules = [];
        foreach (self::FIELDS as $code) {
            $max = $map[$code] ?? 5; // fallback 5 jika sesuatu aneh
            $rules[$code] = "nullable|integer|min:0|max:{$max}";
        }
        // tambahkan total_target jika perlu (frontend mengirimkan hidden field)
        $rules['total_target'] = 'nullable|numeric';
        return $rules;
    }

    private function extractPayload(array $data): array
    {
        $organizationId = (int) $data['organization_id'];
        $resolvedOrganizationName = $this->organizationRegistryService->resolveName(
            $organizationId,
            Auth::user()->organisasi
        );

        $payload = [
            'user_id'    => Auth::id(),
            'organization_id' => $organizationId,
            'organisasi' => $resolvedOrganizationName,
            'tahun'      => $data['tahun'],
        ];

        foreach (self::FIELDS as $code) {
            $payload[$code] = $data[$code] ?? null;
        }

        return $payload;
    }

    private function resolveSelectedOrganizationId(Request $request, ?TargetCapability $target = null): ?int
    {
        $user = Auth::user();
        $requestedOrganizationId = $request->integer('organization_id');

        if ($target?->organization_id) {
            return (int) $target->organization_id;
        }

        if ($requestedOrganizationId && $user->hasOrganizationId($requestedOrganizationId)) {
            return $requestedOrganizationId;
        }

        if ($user->organization_id) {
            return (int) $user->organization_id;
        }

        return MstOrganization::query()
            ->whereIn('organization_id', $user->organizationIds())
            ->orderBy('organization_name')
            ->value('organization_id');
    }
}
