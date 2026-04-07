<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserAccessProfile;
use App\Enums\UserRole;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    protected $errorBag = 'createUser';

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can(PermissionCatalog::UsersManage);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'isActivated' => ['nullable', 'boolean'],
        ];
    }
}
