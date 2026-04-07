<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;

class SaveSummaryNoteRequest extends FormRequest
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
            'objective_id' => ['required', 'string', 'max:255'],
            'kesimpulan' => ['nullable', 'string'],
            'rekomendasi' => ['nullable', 'string'],
            'roadmap_rekomendasi' => ['nullable'],
        ];
    }
}
