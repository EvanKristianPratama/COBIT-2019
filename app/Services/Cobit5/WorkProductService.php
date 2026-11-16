<?php

namespace App\Services\Cobit5;

use App\Models\MstObjective;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WorkProductService
{
    // Keep the document provenance and WP ID in the existing source-reference column.
    public const SOURCE_PREFIX = 'COBIT 5 Figure 19 | ';

    public function forObjective(MstObjective $objective): Collection
    {
        abort_unless((string) $objective->focusArea?->version === '5', 404);
        if (! Schema::hasTable('mst_evidence_ref')) {
            return collect();
        }

        return DB::table('mst_evidence_ref')
            ->where('focus_area_id', $objective->focus_area_id)
            ->where('objective_id', $objective->objective_id)
            ->where('is_active', true)
            ->where('referensi_sumber', 'like', self::SOURCE_PREFIX.'%')
            ->get(['id', 'nama_dokumen', 'keterangan', 'referensi_sumber', 'is_active'])
            ->map(function ($row) {
                $row->wp = $row->nama_dokumen;
                $row->description = $row->keterangan;
                $row->revision = $this->revision($row);
                $row->wp_id = substr($row->referensi_sumber, strlen(self::SOURCE_PREFIX));

                return $row;
            })
            ->sortBy(fn ($row) => (int) substr($row->wp_id, strpos($row->wp_id, '-WP') + 3))
            ->values();
    }

    public function revision(object $reference): string
    {
        return hash('sha256', json_encode([$reference->nama_dokumen, $reference->keterangan ?? '', $reference->referensi_sumber, (bool) $reference->is_active]));
    }

    public function forModel(int $focusAreaId): Collection
    {
        if (! Schema::hasTable('mst_evidence_ref')) {
            return collect();
        }

        return DB::table('mst_evidence_ref as wp')->join('mst_focusarea as f', 'f.id', '=', 'wp.focus_area_id')
            ->where('f.version', '5')->where('wp.focus_area_id', $focusAreaId)->where('wp.is_active', true)
            ->where('wp.referensi_sumber', 'like', self::SOURCE_PREFIX.'%')
            ->get(['wp.id', 'wp.objective_id', 'wp.nama_dokumen as wp', 'wp.referensi_sumber'])
            ->map(function ($row) {
                $row->wp_id = substr($row->referensi_sumber, strlen(self::SOURCE_PREFIX));

                return $row;
            })->sortBy('wp_id', SORT_NATURAL)->values();
    }
}
