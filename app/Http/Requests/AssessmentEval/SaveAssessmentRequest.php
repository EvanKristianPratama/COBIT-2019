<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;

class SaveAssessmentRequest extends FormRequest
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
            'assessmentData' => ['nullable', 'array'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable'],
            'evidence' => ['nullable', 'array'],
            'evidence.*' => ['nullable'],
            'evidenceNames' => ['nullable', 'array'],
            'evidenceNames.*' => ['nullable', 'array'],
            'evidenceNames.*.*' => ['string'],
        ];
    }
}
