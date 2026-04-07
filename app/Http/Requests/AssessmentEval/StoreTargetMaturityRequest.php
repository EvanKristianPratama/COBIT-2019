<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTargetMaturityRequest extends FormRequest
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
            'organization_id' => [
                'required',
                'integer',
                'exists:mst_organization,organization_id',
                Rule::in(auth()->user()?->organizationIds() ?? []),
            ],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2099'],
            'target_maturity' => ['required', 'numeric', 'min:0', 'max:5'],
        ];
    }
}
