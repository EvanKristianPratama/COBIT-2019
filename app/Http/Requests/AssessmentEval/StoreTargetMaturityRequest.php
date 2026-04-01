<?php

namespace App\Http\Requests\AssessmentEval;

use Illuminate\Foundation\Http\FormRequest;

class StoreTargetMaturityRequest extends FormRequest
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
            'tahun' => ['required', 'integer', 'min:2000', 'max:2099'],
            'target_maturity' => ['required', 'numeric', 'min:0', 'max:5'],
        ];
    }
}
