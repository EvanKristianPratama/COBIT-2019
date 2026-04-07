<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('assessments.input');
    }

    protected function prepareForValidation(): void
    {
        $scopes = $this->input('scopes', []);

        if (is_string($scopes)) {
            $scopes = array_filter(array_map('trim', explode(',', $scopes)));
        }

        $this->merge([
            'scopes' => is_array($scopes) ? array_values($scopes) : [],
            'is_new' => filter_var($this->input('is_new', false), FILTER_VALIDATE_BOOL),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['string', 'regex:/^(EDM|APO|BAI|DSS|MEA)\d{2}$/'],
            'scope_id' => ['nullable', 'integer'],
            'nama_scope' => ['required', 'string', 'max:255'],
            'is_new' => ['nullable', 'boolean'],
        ];
    }
}
