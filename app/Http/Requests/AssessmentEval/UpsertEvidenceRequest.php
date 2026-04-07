<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;

class UpsertEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('assessments.input');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'judul_dokumen' => ['required', 'string', 'max:255'],
            'no_dokumen' => ['nullable', 'string', 'max:100'],
            'tahun_terbit' => ['nullable', 'integer'],
            'tahun_kadaluarsa' => ['nullable', 'integer'],
            'tipe' => ['nullable', 'string', 'max:100'],
            'pengesahan' => ['nullable', 'string', 'max:255'],
            'pemilik_dokumen' => ['nullable', 'string', 'max:255'],
            'klasifikasi' => ['nullable', 'string', 'max:100'],
            'grup' => ['nullable', 'string', 'max:100'],
            'link' => ['nullable', 'string'],
            'ket_tipe' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
        ];
    }
}
