<?php

namespace App\Http\Controllers\Cobit4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{
    /**
     * Create a new custom COBIT 4.1 GAMO/Process.
     */
    public function createCobit4Process(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'title' => 'required|string|max:255',
            'domain_code' => 'required|string|in:PO,AI,DS,ME',
            'description' => 'nullable|string',
            'focus_area_id' => 'nullable|integer',
        ]);

        $focusAreaId = (int) ($validated['focus_area_id'] ?? 27);
        $svc = app(\App\Services\Cobit4\Cobit4Service::class);
        $process = $svc->createCustomProcess($validated, $focusAreaId);

        return response()->json([
            'success' => true,
            'message' => "Proses COBIT 4.1 {$process->code} - {$process->title} berhasil ditambahkan!",
            'data' => $process,
            'redirect_url' => route('cobit_component.show', [
                'id' => $process->objective_id,
                'focus_area' => $focusAreaId,
            ]),
        ]);
    }

    /**
     * Delete a COBIT 4.1 process.
     */
    public function deleteCobit4Process(Request $request, $code)
    {
        $focusAreaId = (int) $request->input('focus_area_id', 27);
        $svc = app(\App\Services\Cobit4\Cobit4Service::class);
        $deleted = $svc->deleteProcess($code, $focusAreaId);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => "Proses {$code} berhasil dihapus.",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Proses {$code} tidak ditemukan.",
        ], 404);
    }

    /**
     * Get aggregated COBIT 4 component data for View by Component.
     */
    public function getCobit4ComponentData(Request $request)
    {
        $component = $request->query('component', 'overview');
        $focusAreaId = (int) $request->query('focus_area_id', 27);

        $svc = app(\App\Services\Cobit4\Cobit4Service::class);
        $data = $svc->getComponentData($component, $focusAreaId);

        return response()->json([
            'success' => true,
            'component' => $component,
            'data' => $data,
        ]);
    }

    /**
     * Save custom COBIT 4.1 process data (Input Mode).
     */
    public function saveCobit4Data(Request $request)
    {
        $processCode = $request->input('process_code');
        $objectiveId = $request->input('objective_id');
        $focusAreaId = $request->input('focus_area_id', 27);
        $data = $request->input('data', []);

        if (!$processCode || empty($data)) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap.'], 422);
        }

        $hasWorkProducts = is_array($data) && array_key_exists('work_products', $data);
        if ($hasWorkProducts) {
            abort_unless($request->user()?->can('design-factors.input'), 403);
            $request->validate([
                'process_code' => ['required', 'string', 'max:20'],
                'focus_area_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('mst_focusarea', 'id')->where('version', '4.1')],
                'data.work_products' => ['present', 'array', 'max:200'],
                'data.work_products_revision' => ['required', 'string', 'size:64'],
                'data.work_products.*.original_wp_id' => ['nullable', 'string', 'max:30'],
                'data.work_products.*.wp_id' => ['required', 'string', 'max:30'],
                'data.work_products.*.wp' => ['required', 'string', 'max:255'],
                'data.work_products.*.description' => ['nullable', 'string', 'max:20000'],
                'work_products_only' => ['nullable', 'boolean'],
            ]);
        }

        $workProductsOnly = $hasWorkProducts && $request->boolean('work_products_only');
        $svc = app(\App\Services\Cobit4\Cobit4Service::class);
        $workProductService = app(\App\Services\Cobit4\Cobit4WorkProductService::class);
        $savedWorkProducts = DB::transaction(function () use ($svc, $workProductService, $hasWorkProducts, $workProductsOnly, $processCode, $data, $objectiveId, $focusAreaId) {
            // Validate and save WP before saving the remaining process components;
            // the outer transaction rolls back both when either operation fails.
            $rows = $hasWorkProducts ? $workProductService->synchronize($processCode, (int) $focusAreaId, $data['work_products'], $data['work_products_revision']) : null;
            if (! $workProductsOnly) {
                $svc->saveProcessData($processCode, $data, $objectiveId, (int) $focusAreaId);
            }

            return $rows;
        });

        return response()->json([
            'success' => true,
            'message' => "Data {$processCode} berhasil disimpan ke database!",
            'data' => $data,
            'work_products' => $savedWorkProducts,
            'work_products_revision' => $savedWorkProducts === null ? null : $workProductService->revision($savedWorkProducts),
        ]);
    }

    /**
     * Reset COBIT 4.1 process data back to ISACA baseline.
     */
    public function resetCobit4Data(Request $request)
    {
        $processCode = $request->input('process_code');
        $objectiveId = $request->input('objective_id');
        $focusAreaId = $request->input('focus_area_id', 27);

        if (!$processCode) {
            return response()->json(['success' => false, 'message' => 'Kode proses tidak valid.'], 422);
        }

        $svc = app(\App\Services\Cobit4\Cobit4Service::class);
        $svc->resetProcessData($processCode, $objectiveId, (int)$focusAreaId);

        return response()->json([
            'success' => true,
            'message' => "Data {$processCode} berhasil di-reset ke standar ISACA!",
        ]);
    }
}
