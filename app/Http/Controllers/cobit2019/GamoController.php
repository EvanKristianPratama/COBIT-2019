<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstCobitEvolution;
use App\Services\Cobit\CobitEvolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GamoController extends Controller
{
    /**
     * Render the interactive GAMO Information and RACI Flow analysis.
     */
    public function gamoAnalysis()
    {
        return view('cobit_component.shared.gamoanalisis');
    }

    /**
     * Render the COBIT GAMO Mapping Evolution view: COBIT 2019 practice -> COBIT 5 practice -> COBIT 4.1 control objective.
     */
    public function gamoEvolution(CobitEvolutionService $evolution)
    {
        $domains = Schema::hasTable('mst_cobit_evolution_domains')
            ? DB::table('mst_cobit_evolution_domains')->orderBy('order_no')->get()
                ->mapWithKeys(fn ($d) => [$d->code => ['name' => $d->name, 'type' => $d->type]])
                ->all()
            : [];

        $gamos = $evolution->gamos();

        // Domains used by the data but missing from the domain table still get listed, so no row is ever hidden.
        foreach ($gamos->pluck('domain')->unique() as $domainKey) {
            $domains[$domainKey] ??= ['name' => $domainKey, 'type' => ''];
        }

        $stats = [
            'total' => $gamos->count(),
            'new' => $gamos->where('status', 'new')->count(),
            'restructured' => $gamos->where('status', 'restructured')->count(),
            'evolved' => $gamos->where('status', 'evolved')->count(),
        ];

        $canManageEvolution = auth()->user()?->can('design-factors.input') ?? false;
        $tableReady = Schema::hasTable('mst_cobit_evolution');
        $practiceEditReady = $canManageEvolution && $evolution->manualTableReady();
        $controlEditReady = $canManageEvolution && $evolution->control4Ready();
        $editorData = $canManageEvolution ? ['practiceEditable' => $practiceEditReady] + $evolution->editorData() : [];

        return view('cobit_component.shared.gamo_evolution', compact(
            'domains', 'gamos', 'stats', 'canManageEvolution', 'tableReady', 'practiceEditReady', 'controlEditReady', 'editorData'
        ));
    }

    /**
     * Save one inline editing session. The browser sends the changed GAMOs and practices as a JSON "payload".
     */
    public function saveEvolution(Request $request, CobitEvolutionService $evolution)
    {
        $payload = json_decode((string) $request->input('payload'), true);
        $payload = is_array($payload) ? $payload : [];

        $validator = Validator::make($payload, [
            'gamos' => 'nullable|array',
            'gamos.*.id' => 'required|integer|exists:mst_cobit_evolution,id',
            'gamos.*.status' => 'required|in:new,restructured,evolved',
            'gamos.*.cobit5_id' => 'nullable|string|max:100',
            'gamos.*.notes' => 'nullable|string|max:5000',
            'practices' => 'nullable|array',
            'practices.*.code' => ['required', 'regex:/^[A-Z]{3}\d{2}\.\d{2}$/'],
            'practices.*.reset' => 'nullable|boolean',
            'practices.*.targets' => 'nullable|array',
            'practices.*.targets.*' => ['string', Rule::in($evolution->cobit5PracticeCodes())],
            'controls' => 'nullable|array',
            'controls.*.target' => ['required', Rule::in($evolution->cobit5PracticeCodes())],
            'controls.*.add' => 'nullable|array',
            'controls.*.add.*' => ['string', Rule::in($evolution->cobit4ControlCodes())],
            'controls.*.remove' => 'nullable|array',
            'controls.*.remove.*' => ['string', Rule::in($evolution->cobit4ControlCodes())],
        ]);
        $validator->after(function ($validator) use ($payload, $evolution) {
            if (! empty($payload['controls']) && ! $evolution->control4Ready()) {
                $validator->errors()->add('controls', 'Tabel mapping COBIT 4.1 ke COBIT 5 belum ada di database.');
            }
            if (empty($payload['practices'])) {
                return;
            }
            if (! $evolution->manualTableReady()) {
                $validator->errors()->add('practices', 'Tabel trs_cobit2019_cobit5_mappings belum ada.');

                return;
            }
            foreach ($payload['practices'] as $index => $practice) {
                if (is_array($practice) && is_string($practice['code'] ?? null) && ! $evolution->practiceExists($practice['code'])) {
                    $validator->errors()->add("practices.$index.code", "Practice {$practice['code']} tidak dikenal.");
                }
            }
        });
        $data = $validator->validate();

        $evolution->saveChanges($data['gamos'] ?? [], $data['practices'] ?? [], $data['controls'] ?? []);

        $anchor = isset($data['gamos'][0]['id'])
            ? MstCobitEvolution::whereKey($data['gamos'][0]['id'])->value('cobit2019_id')
            : (isset($data['practices'][0]['code']) ? substr($data['practices'][0]['code'], 0, 5) : null);
        $anchor = $anchor ? "#gamo-{$anchor}" : '';

        return redirect()
            ->to(route('cobit.evolution').$anchor)
            ->with('success', 'Perubahan'.($anchor ? ' '.substr($anchor, 6) : '').' berhasil disimpan.');
    }
}
