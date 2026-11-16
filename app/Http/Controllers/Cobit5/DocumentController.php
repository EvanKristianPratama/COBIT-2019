<?php

namespace App\Http\Controllers\Cobit5;

use App\Http\Controllers\Controller;
use App\Models\MstEvidenceRef;
use App\Models\MstInfoflowInput;
use App\Models\MstInfoflowOutput;
use App\Models\MstObjective;
use App\Services\Cobit5\DocumentService;
use App\Services\Cobit5\WorkProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function overview(Request $request, string $objectiveId, DocumentService $documents)
    {
        return DB::transaction(function () use ($request, $objectiveId, $documents) {
            $objective = $this->objective($request, $objectiveId);
            $data = $request->validate([
                'revision' => ['required', 'string', 'size:64'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['present', 'nullable', 'string', 'max:20000'],
                'purpose' => ['present', 'nullable', 'string', 'max:20000'],
                'it_goals' => ['present', 'array', 'max:50'],
                'it_goals.*.code' => ['required', 'string', 'max:30', 'distinct'],
                'it_goals.*.title' => ['required', 'string', 'max:5000'],
                'it_goals.*.metrics' => ['present', 'array', 'max:50'],
                'it_goals.*.metrics.*' => ['required', 'string', 'max:5000'],
                'process_goals' => ['present', 'array', 'max:50'],
                'process_goals.*.title' => ['required', 'string', 'max:5000'],
                'process_goals.*.metrics' => ['present', 'array', 'max:50'],
                'process_goals.*.metrics.*' => ['required', 'string', 'max:5000'],
            ]);
            $current = $documents->overviewData($documents->build($objective));
            abort_unless(hash_equals($documents->revision($current), $data['revision']), 409, 'Dokumen sudah berubah. Muat ulang halaman sebelum mengedit.');
            $settings = $objective->cobit5_document ?? [];
            $settings['overview_saved'] = true;
            $settings['it_goals'] = collect($data['it_goals'])->map(fn ($goal) => array_intersect_key($goal, array_flip(['code', 'title', 'metrics'])))->all();
            $settings['process_goals'] = collect($data['process_goals'])->map(fn ($goal) => array_intersect_key($goal, array_flip(['title', 'metrics'])))->all();
            $objective->forceFill([
                'objective' => $data['title'], 'objective_description' => $data['description'] ?? '',
                'objective_purpose' => $data['purpose'] ?? '', 'cobit5_document' => $settings,
            ])->save();

            return response()->json(['message' => 'Dokumen COBIT 5 berhasil disimpan.']);
        });
    }

    public function flow(Request $request, string $objectiveId, DocumentService $documents)
    {
        return DB::transaction(function () use ($request, $objectiveId, $documents) {
            $objective = $this->objective($request, $objectiveId);
            $request->validate(['practice_id' => ['required', 'string', 'max:255']]);
            $practice = $objective->practices()->where('practice_id', $request->input('practice_id'))->lockForUpdate()->firstOrFail();
            $code = $documents->code($practice->practice_id);
            $current = collect($documents->build($objective)['practices'])->firstWhere('code', $code);
            abort_unless($current, 404);
            $data = $request->validate([
                'revision' => ['required', 'string', 'size:64'],
                'inputs' => ['present', 'array', 'max:100'],
                'inputs.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('mst_infoflowinput', 'input_id')->where('practice_id', $practice->practice_id)],
                'inputs.*.from' => ['nullable', 'string', 'max:255'],
                'inputs.*.description' => ['nullable', 'string', 'max:20000'],
                'outputs' => ['present', 'array', 'max:100'],
                'outputs.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('mst_infoflowoutput', 'output_id')->where('practice_id', $practice->practice_id)],
                'outputs.*.work_product_id' => ['required', 'integer', Rule::exists('mst_evidence_ref', 'id')
                    ->where('focus_area_id', $objective->focus_area_id)->where('objective_id', $objective->objective_id)
                    ->where('is_active', true)->where(fn ($query) => $query->where('referensi_sumber', 'like', WorkProductService::SOURCE_PREFIX.'%'))],
                'outputs.*.to' => ['nullable', 'string', 'max:255'],
            ]);
            abort_unless(hash_equals($documents->revision($documents->flowData($current)), $data['revision']), 409, 'Information Flow sudah berubah. Muat ulang halaman sebelum mengedit.');
            $inputIds = $outputIds = [];
            foreach ($data['inputs'] as $input) {
                $id = $input['id'] ?? $this->nextId('mst_infoflowinput', 'input_id');
                MstInfoflowInput::updateOrCreate(['input_id' => $id], ['practice_id' => $practice->practice_id,
                    'from' => $input['from'] ?? '', 'description' => $input['description'] ?? '']);
                $inputIds[] = $id;
            }
            foreach ($data['outputs'] as $output) {
                $wp = MstEvidenceRef::findOrFail($output['work_product_id']);
                $id = $output['id'] ?? $this->nextId('mst_infoflowoutput', 'output_id');
                MstInfoflowOutput::updateOrCreate(['output_id' => $id], ['practice_id' => $practice->practice_id,
                    'to' => $output['to'] ?? '', 'description' => $wp->nama_dokumen, 'work_product_id' => $wp->id]);
                $outputIds[] = $id;
            }
            $practice->infoflowinput()->whereNotIn('input_id', $inputIds)->delete();
            $practice->infoflowoutput()->whereNotIn('output_id', $outputIds)->delete();
            $documents->markFlowChanged($objective, $code);

            return response()->json(['message' => 'Information Flow COBIT 5 berhasil disimpan.']);
        });
    }

    private function objective(Request $request, string $objectiveId): MstObjective
    {
        $request->validate(['focus_area_id' => ['required', 'integer']]);
        $objective = MstObjective::with('focusArea')->whereKey($objectiveId)
            ->where('focus_area_id', $request->integer('focus_area_id'))->lockForUpdate()->firstOrFail();
        abort_unless((string) $objective->focusArea?->version === '5', 404);
        abort_unless(Schema::hasColumn('mst_objective', 'cobit5_document') && Schema::hasColumn('mst_infoflowoutput', 'work_product_id'),
            409, 'Aktifkan pembaruan database editor COBIT 5 terlebih dahulu.');

        return $objective;
    }

    private function nextId(string $table, string $key): int
    {
        return ((int) DB::table($table)->orderByDesc($key)->lockForUpdate()->value($key)) + 1;
    }
}
