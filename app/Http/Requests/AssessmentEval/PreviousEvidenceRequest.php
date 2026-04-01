<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;

class PreviousEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'search' => ['nullable', 'string'],
            'filters' => ['nullable', 'array'],
            'filters.*' => ['nullable', 'string'],
        ];
    }
}
