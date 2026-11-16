<?php

namespace App\Http\Controllers\Cobit5;

use App\Http\Controllers\Controller;
use App\Models\MstEvidenceRef;
use App\Models\MstObjective;
use App\Services\Cobit5\DocumentService;
use App\Services\Cobit5\WorkProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkProductController extends Controller
{
    public function save(Request $request, string $objectiveId)
    {
        $request->validate(['focus_area_id' => ['required', 'integer'], 'reference_id' => ['nullable', 'integer']]);

        return DB::transaction(function () use ($request, $objectiveId) {
            $objective = MstObjective::with('focusArea')->where('objective_id', $objectiveId)
                ->where('focus_area_id', $request->integer('focus_area_id'))->lockForUpdate()->firstOrFail();
            abort_unless((string) $objective->focusArea?->version === '5', 404);
            $reference = $request->filled('reference_id')
                ? MstEvidenceRef::where('focus_area_id', $objective->focus_area_id)
                    ->where('objective_id', $objective->objective_id)
                    ->where('referensi_sumber', 'like', WorkProductService::SOURCE_PREFIX.'%')
                    ->lockForUpdate()->findOrFail($request->integer('reference_id'))
                : new MstEvidenceRef;
            $code = app(DocumentService::class)->code($objective->objective_id);
            $data = $request->validate([
                'wp_id' => ['required', 'string', 'max:30', 'regex:/^'.preg_quote($code, '/').'-WP[1-9][0-9]*$/'],
                'wp' => ['required', 'string', 'max:255', Rule::unique('mst_evidence_ref', 'nama_dokumen')
                    ->where('focus_area_id', $objective->focus_area_id)->where('objective_id', $objective->objective_id)->ignore($reference->id)],
                'description' => ['nullable', 'string', 'max:20000'],
                'revision' => [$reference->exists ? 'required' : 'nullable', 'string', 'size:64'],
            ]);
            if ($reference->exists) {
                abort_unless(hash_equals(app(WorkProductService::class)->revision($reference), $data['revision']), 409, 'WP sudah berubah. Muat ulang halaman sebelum mengedit.');
            }
            $duplicate = MstEvidenceRef::where('focus_area_id', $objective->focus_area_id)
                ->where('objective_id', $objective->objective_id)
                ->where('referensi_sumber', WorkProductService::SOURCE_PREFIX.$data['wp_id'])
                ->when($reference->exists, fn ($query) => $query->where('id', '!=', $reference->id))->exists();
            if ($duplicate) {
                throw \Illuminate\Validation\ValidationException::withMessages(['wp_id' => 'WP ID ini sudah digunakan pada proses ini.']);
            }
            $reference->fill([
                'focus_area_id' => $objective->focus_area_id, 'objective_id' => $objective->objective_id,
                'nama_dokumen' => $data['wp'], 'keterangan' => $data['description'] ?? '',
                'referensi_sumber' => WorkProductService::SOURCE_PREFIX.$data['wp_id'], 'is_active' => true,
            ])->save();
            if (\Illuminate\Support\Facades\Schema::hasColumn('mst_infoflowoutput', 'work_product_id')) {
                DB::table('mst_infoflowoutput')->where('work_product_id', $reference->id)->update(['description' => $reference->nama_dokumen]);
            }

            return redirect()->route('cobit_component.show', ['id' => $objective->objective_id, 'focus_area' => $objective->focus_area_id])
                ->withFragment('c5-work-products')->with('success', 'Work Product COBIT 5 berhasil disimpan.');
        });
    }
}
