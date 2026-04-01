<?php

namespace App\Services\Assessment\Evidence;

use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\User;

class AssessmentEvidenceService
{
    public function getEvidences(MstEval $evaluation)
    {
        return MstEvidence::where('eval_id', $evaluation->eval_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPreviousEvidences(MstEval $evaluation, array $filters): array
    {
        $owner = User::find($evaluation->user_id);
        $orgName = trim((string) ($owner?->organisasi ?? ''));

        $userIds = User::when($orgName !== '', function ($query) use ($orgName) {
            $query->whereRaw('LOWER(TRIM(organisasi)) = ?', [strtolower($orgName)]);
        })
            ->orWhere('id', $evaluation->user_id)
            ->pluck('id');

        $evalIds = MstEval::whereIn('user_id', $userIds)
            ->where('eval_id', '!=', $evaluation->eval_id)
            ->pluck('eval_id');

        if ($evalIds->isEmpty()) {
            return [
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ];
        }

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(5, min(100, (int) ($filters['per_page'] ?? 20)));
        $search = (string) ($filters['search'] ?? '');
        $columnFilters = is_array($filters['filters'] ?? null) ? $filters['filters'] : [];

        $query = MstEvidence::whereIn('eval_id', $evalIds)
            ->with(['evaluation' => function ($query) {
                $query->select('eval_id', 'tahun');
            }]);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('judul_dokumen', 'like', "%{$search}%")
                    ->orWhere('no_dokumen', 'like', "%{$search}%")
                    ->orWhere('pemilik_dokumen', 'like', "%{$search}%")
                    ->orWhere('grup', 'like', "%{$search}%");
            });
        }

        $allowedFields = [
            'judul_dokumen', 'no_dokumen', 'grup', 'tipe',
            'tahun_terbit', 'tahun_kadaluarsa', 'pemilik_dokumen',
            'pengesahan', 'klasifikasi', 'summary', 'ket_tipe',
        ];

        foreach ($columnFilters as $field => $value) {
            if ($value === null || $value === '' || ! in_array($field, $allowedFields, true)) {
                continue;
            }

            $query->where($field, 'like', '%'.$value.'%');
        }

        $total = $query->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $evidences = $query->orderByDesc('created_at')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get([
                'id', 'eval_id', 'judul_dokumen', 'no_dokumen', 'grup', 'tipe',
                'tahun_terbit', 'tahun_kadaluarsa', 'pemilik_dokumen', 'pengesahan',
                'klasifikasi', 'summary', 'link', 'ket_tipe', 'created_at',
            ]);

        return [
            'data' => $evidences->map(function (MstEvidence $evidence) {
                return [
                    'id' => $evidence->id,
                    'eval_id' => $evidence->eval_id,
                    'judul_dokumen' => $evidence->judul_dokumen,
                    'no_dokumen' => $evidence->no_dokumen,
                    'grup' => $evidence->grup,
                    'tipe' => $evidence->tipe,
                    'tahun_terbit' => $evidence->tahun_terbit,
                    'tahun_kadaluarsa' => $evidence->tahun_kadaluarsa,
                    'pemilik_dokumen' => $evidence->pemilik_dokumen,
                    'pengesahan' => $evidence->pengesahan,
                    'klasifikasi' => $evidence->klasifikasi,
                    'summary' => $evidence->summary,
                    'link' => $evidence->link,
                    'ket_tipe' => $evidence->ket_tipe,
                    'created_at' => $evidence->created_at,
                    'assessment_year' => optional($evidence->evaluation)->tahun,
                ];
            }),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(MstEval $evaluation, array $validated): MstEvidence
    {
        return MstEvidence::create([
            'eval_id' => $evaluation->eval_id,
            ...$validated,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(MstEvidence $evidence, array $validated): MstEvidence
    {
        $evidence->update($validated);

        return $evidence->fresh();
    }
}
