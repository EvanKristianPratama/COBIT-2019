<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;

class CreateAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $selected = $this->input('selected_gamos', []);

        if (is_string($selected)) {
            $selected = array_filter(array_map('trim', explode(',', $selected)));
        }

        $this->merge([
            'selected_gamos' => is_array($selected) ? array_values($selected) : [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'nama_scope' => ['nullable', 'string', 'max:255'],
            'selected_gamos' => ['nullable', 'array'],
            'selected_gamos.*' => ['string', 'regex:/^(EDM|APO|BAI|DSS|MEA)\d{2}$/'],
        ];
    }
}
