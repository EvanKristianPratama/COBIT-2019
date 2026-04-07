<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserAccessProfile;
use App\Enums\UserRole;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can(PermissionCatalog::UsersManage);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', 'string', Rule::in(UserRole::values())],
            'access_profile' => [
                'nullable',
                'string',
                Rule::requiredIf(fn (): bool => $this->input('role') === UserRole::User->value),
                Rule::in(UserAccessProfile::values()),
            ],
            'jabatan' => ['required', 'string', 'max:255'],
            'organization_ids' => ['required', 'array', 'min:1'],
            'organization_ids.*' => ['integer', 'exists:mst_organization,organization_id'],
        ];
    }
}
