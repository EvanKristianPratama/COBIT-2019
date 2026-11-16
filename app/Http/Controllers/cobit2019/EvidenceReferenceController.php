<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstEvidenceRef;
use App\Services\Assessment\Evidence\EvidenceReferenceCatalog;
use App\Services\Assessment\Evidence\ReferenceInfoflowMapping;
use App\Services\Cobit\GamoOverviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EvidenceReferenceController extends Controller
{
    public function __construct(
        private readonly EvidenceReferenceCatalog $catalog,
        private readonly GamoOverviewService $overview,
        private readonly ReferenceInfoflowMapping $flowMapping
    ) {
    }

    public function index(Request $request)
    {
        $request->validate([
            'focus_area' => ['nullable', 'integer'],
            'objective_id' => ['nullable', 'string', 'max:255'],
        ]);

        return view('cobit_component.cobit2019.helicopter', $this->overview->data(
            (int) $request->query('focus_area', 1),
            $request->query('objective_id')
        ));
    }

    public function store(Request $request)
    {
        abort_unless($this->catalog->available(), 409, 'Katalog referensi dokumen belum diaktifkan.');
        $data = $this->validateReference($request);
        DB::transaction(function () use ($data) {
            $reference = MstEvidenceRef::create(Arr::except($data, ['flow_mappings']) + ['is_active' => true]);
            $this->flowMapping->synchronize($reference, $data);
        });

        return $this->redirectAfterSave($request, $data['focus_area_id'], $data['objective_id'])
            ->with('success', 'Referensi dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, int $referenceId)
    {
        abort_unless($this->catalog->available(), 409, 'Katalog referensi dokumen belum diaktifkan.');
        $reference = MstEvidenceRef::findOrFail($referenceId);
        // A reference keeps its original GAMO and model when its wording is edited.
        $request->merge([
            'focus_area_id' => $reference->focus_area_id,
            'objective_id' => $reference->objective_id,
        ]);
        $data = $this->validateReference($request, $referenceId);
        DB::transaction(function () use ($reference, $data) {
            $reference = MstEvidenceRef::whereKey($reference->id)->lockForUpdate()->firstOrFail();
            $reference->update(Arr::except($data, ['flow_mappings']));
            $this->flowMapping->synchronize($reference, $data);
        });

        return $this->redirectAfterSave($request, $reference->focus_area_id, $reference->objective_id)
            ->with('success', 'Referensi dokumen berhasil diperbarui.');
    }

    private function validateReference(Request $request, ?int $ignoreId = null): array
    {
        if ($request->boolean('flow_mappings_present') && ! $request->has('flow_mappings')) {
            $request->merge(['flow_mappings' => []]);
        }
        $data = $request->validate([
            'focus_area_id' => ['required', 'integer', Rule::exists('mst_focusarea', 'id')->where('version', '2019')],
            'objective_id' => ['required', 'string', Rule::exists('mst_objective', 'objective_id')
                ->where('focus_area_id', (int) $request->input('focus_area_id'))],
            'nama_dokumen' => ['required', 'string', 'max:255', Rule::unique('mst_evidence_ref', 'nama_dokumen')
                ->where('focus_area_id', (int) $request->input('focus_area_id'))
                ->where('objective_id', $request->input('objective_id'))->ignore($ignoreId)],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'referensi_sumber' => ['nullable', 'string', 'max:2000'],
            'return_context' => ['nullable', Rule::in(['component', 'dictionary', 'master'])],
            'return_organization_id' => ['nullable', 'integer'],
            'return_search' => ['nullable', 'string', 'max:150'],
            'return_grup' => ['nullable', Rule::in(['EDM', 'APO', 'BAI', 'DSS', 'MEA'])],
            'return_tipe' => ['nullable', Rule::in(['Design', 'Implementation'])],
            'flow_mappings' => ['sometimes', 'array', 'max:100'],
            'flow_mappings.*.direction' => ['required', Rule::in(['input', 'output'])],
            'flow_mappings.*.flow_id' => ['required', 'integer', 'min:1'],
        ]);

        if (in_array($data['return_context'] ?? 'component', ['dictionary', 'master'], true)) {
            abort_unless($request->user()->can('assessments.view'), 403);
            if (! empty($data['return_organization_id'])) {
                abort_unless($request->user()->assignedOrganizations()->contains('organization_id', (int) $data['return_organization_id']), 403);
            }
        }

        return Arr::except($data, ['return_context', 'return_organization_id', 'return_search', 'return_grup', 'return_tipe']);
    }

    private function redirectAfterSave(Request $request, int $focusAreaId, string $objectiveId)
    {
        if ($request->input('return_context') === 'master') {
            $parameters = ['reference_gamo' => $objectiveId];
            if ($request->filled('return_organization_id')) {
                $parameters['organization_id'] = $request->integer('return_organization_id');
            }
            foreach (['search', 'grup', 'tipe'] as $filter) {
                if ($request->filled('return_'.$filter)) {
                    $parameters[$filter] = $request->input('return_'.$filter);
                }
            }

            return redirect()->route('master-evidence.index', $parameters);
        }

        $dictionary = $request->input('return_context') === 'dictionary';
        $parameters = ['focus_area' => $focusAreaId, 'objective_id' => $objectiveId];
        if ($dictionary && $request->filled('return_organization_id')) {
            $parameters['organization_id'] = $request->integer('return_organization_id');
        }

        return redirect()->route($dictionary ? 'master-evidence.dictionary' : 'cobit_component.helicopter', $parameters);
    }
}
