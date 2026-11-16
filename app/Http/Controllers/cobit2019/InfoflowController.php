<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstInfoflowInput;
use App\Models\MstInfoflowOutput;
use App\Models\MstObjective;
use App\Models\MstPractice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InfoflowController extends Controller
{
    /**
     * Update an information flow input row.
     */
    public function updateInfoflowInput(Request $request, $inputId)
    {
        $input = MstInfoflowInput::findOrFail($inputId);

        $data = $request->validate([
            'from' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($input, $data) {
            $this->lockCobit5Flow($input->practice);
            $input->update($data);
            $this->markCobit5Flow($input->practice);
        });

        return response()->json($input->fresh());
    }

    /**
     * Create an information flow input row.
     */
    public function createInfoflowInput(Request $request)
    {
        $data = $request->validate([
            'practice_id' => 'required|string|exists:mst_practice,practice_id',
            'from' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $input = DB::transaction(function () use ($data) {
            $practice = MstPractice::with('objective.focusArea')->findOrFail($data['practice_id']);
            $this->lockCobit5Flow($practice);
            $nextInputId = $this->nextInfoflowInputId();

            $input = MstInfoflowInput::create([
                'input_id' => $nextInputId,
                'practice_id' => $data['practice_id'],
                'from' => $data['from'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
            $this->markCobit5Flow($practice);

            return $input;
        });

        return response()->json($input->fresh(), 201);
    }

    /**
     * Update an information flow output row.
     */
    public function updateInfoflowOutput(Request $request, $outputId)
    {
        $output = MstInfoflowOutput::findOrFail($outputId);
        $practice = $output->practice;
        $isCobit5 = (string) $practice?->objective?->focusArea?->version === '5';

        $data = $request->validate([
            'to' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'input_id' => 'nullable|integer|exists:mst_infoflowinput,input_id',
        ] + ($isCobit5 ? ['work_product_id' => $this->cobit5WorkProductRules($practice)] : []));

        DB::transaction(function () use ($output, $data, $practice, $isCobit5) {
            $this->lockCobit5Flow($practice);
            $output->update([
                'to' => $data['to'] ?? $output->to,
                'description' => $isCobit5 ? \App\Models\MstEvidenceRef::findOrFail($data['work_product_id'])->nama_dokumen : ($data['description'] ?? $output->description),
            ] + ($isCobit5 ? ['work_product_id' => $data['work_product_id']] : []));

            if (! $isCobit5 || array_key_exists('input_id', $data)) {
                DB::table('trs_infoflowio')->where('output_id', $output->output_id)->delete();
            }
            if (!empty($data['input_id'])) {
                DB::table('trs_infoflowio')->insert([
                    'input_id' => $data['input_id'],
                    'output_id' => $output->output_id,
                ]);
            }
            $this->markCobit5Flow($practice);
        });

        return response()->json($output->fresh());
    }

    /**
     * Create an information flow output row and optionally connect to input.
     */
    public function createInfoflowOutput(Request $request)
    {
        $request->validate(['practice_id' => 'required|string|exists:mst_practice,practice_id']);
        $practice = MstPractice::with('objective.focusArea')->findOrFail($request->input('practice_id'));
        $isCobit5 = (string) $practice->objective?->focusArea?->version === '5';
        $data = $request->validate([
            'practice_id' => 'required|string|exists:mst_practice,practice_id',
            'input_id' => 'nullable|integer|exists:mst_infoflowinput,input_id',
            'to' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ] + ($isCobit5 ? ['work_product_id' => $this->cobit5WorkProductRules($practice)] : []));

        $output = DB::transaction(function () use ($data, $practice, $isCobit5) {
            $this->lockCobit5Flow($practice);
            $nextOutputId = $this->nextInfoflowOutputId();

            $output = MstInfoflowOutput::create([
                'output_id' => $nextOutputId,
                'practice_id' => $data['practice_id'],
                'to' => $data['to'] ?? null,
                'description' => $isCobit5 ? \App\Models\MstEvidenceRef::findOrFail($data['work_product_id'])->nama_dokumen : ($data['description'] ?? null),
            ] + ($isCobit5 ? ['work_product_id' => $data['work_product_id']] : []));

            if (!empty($data['input_id'])) {
                $input = MstInfoflowInput::findOrFail($data['input_id']);
                $input->connectedoutputs()->syncWithoutDetaching([$output->output_id]);
            }
            $this->markCobit5Flow($practice);

            return $output;
        });

        return response()->json($output->fresh(), 201);
    }

    public function deleteInfoflowInput($inputId)
    {
        $input = MstInfoflowInput::findOrFail($inputId);
        DB::transaction(function () use ($input) {
            $this->lockCobit5Flow($input->practice);
            $input->connectedoutputs()->detach();
            $input->delete();
            $this->markCobit5Flow($input->practice);
        });
        return response()->json(['success' => true]);
    }

    public function deleteInfoflowOutput($outputId)
    {
        $output = MstInfoflowOutput::findOrFail($outputId);
        DB::transaction(function () use ($output) {
            $this->lockCobit5Flow($output->practice);
            DB::table('trs_infoflowio')->where('output_id', $output->output_id)->delete();
            $output->delete();
            $this->markCobit5Flow($output->practice);
        });
        return response()->json(['success' => true]);
    }

    private function cobit5WorkProductRules(MstPractice $practice): array
    {
        return ['required', 'integer', \Illuminate\Validation\Rule::exists('mst_evidence_ref', 'id')
            ->where('focus_area_id', $practice->objective->focus_area_id)->where('objective_id', $practice->objective_id)
            ->where('is_active', true)->where(fn ($query) => $query->where('referensi_sumber', 'like', \App\Services\Cobit5\WorkProductService::SOURCE_PREFIX.'%'))];
    }

    private function lockCobit5Flow(?MstPractice $practice): void
    {
        if ((string) $practice?->objective?->focusArea?->version === '5') {
            abort_unless(request()->user()?->can('cobit.view'), 403);
            abort_unless(Schema::hasColumn('mst_infoflowoutput', 'work_product_id') && Schema::hasColumn('mst_objective', 'cobit5_document'),
                409, 'Aktifkan pembaruan database editor COBIT 5 terlebih dahulu.');
            MstObjective::whereKey($practice->objective_id)->lockForUpdate()->firstOrFail();
        }
    }

    private function markCobit5Flow(?MstPractice $practice): void
    {
        if ((string) $practice?->objective?->focusArea?->version === '5') {
            app(\App\Services\Cobit5\DocumentService::class)->markFlowChanged($practice->objective, app(\App\Services\Cobit5\DocumentService::class)->code($practice->practice_id));
        }
    }

    protected function nextInfoflowInputId(): int
    {
        $latest = DB::table('mst_infoflowinput')
            ->select('input_id')
            ->orderByDesc('input_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->input_id ?? 0)) + 1;
    }

    protected function nextInfoflowOutputId(): int
    {
        $latest = DB::table('mst_infoflowoutput')
            ->select('output_id')
            ->orderByDesc('output_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->output_id ?? 0)) + 1;
    }
}
