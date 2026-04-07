@php
    $errorSource = isset($errorBag) && $errorBag ? $errors->getBag($errorBag) : $errors;
    $selectedOrganizationIds = collect(old('organization_ids', $organizationIds ?? ($user?->organizations->pluck('organization_id')->all() ?? [])))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->unique()
        ->values()
        ->all();
@endphp

<div class="row g-3">
    <div class="col-lg-6">
        <label for="{{ $prefix }}_name" class="form-label">Nama</label>
        <input
            id="{{ $prefix }}_name"
            type="text"
            name="name"
            class="form-control {{ $errorSource->has('name') ? 'is-invalid' : '' }}"
            value="{{ old('name', $user?->name ?? '') }}"
            required
        >
        @if($errorSource->has('name'))
            <div class="invalid-feedback">{{ $errorSource->first('name') }}</div>
        @endif
    </div>

    <div class="col-lg-6">
        <label for="{{ $prefix }}_email" class="form-label">Email</label>
        <input
            id="{{ $prefix }}_email"
            type="email"
            name="email"
            class="form-control {{ $errorSource->has('email') ? 'is-invalid' : '' }}"
            value="{{ old('email', $user?->email ?? '') }}"
            required
        >
        @if($errorSource->has('email'))
            <div class="invalid-feedback">{{ $errorSource->first('email') }}</div>
        @endif
    </div>

    @if(!empty($includePassword))
        <div class="col-lg-6">
            <label for="{{ $prefix }}_password" class="form-label">Password</label>
            <input
                id="{{ $prefix }}_password"
                type="password"
                name="password"
                class="form-control {{ $errorSource->has('password') ? 'is-invalid' : '' }}"
                required
            >
            @if($errorSource->has('password'))
                <div class="invalid-feedback">{{ $errorSource->first('password') }}</div>
            @endif
        </div>

        <div class="col-lg-6">
            <label for="{{ $prefix }}_password_confirmation" class="form-label">Konfirmasi Password</label>
            <input
                id="{{ $prefix }}_password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control"
                required
            >
        </div>
    @endif

    <div class="col-lg-4">
        <label for="{{ $prefix }}_role" class="form-label">Role</label>
        <select
            id="{{ $prefix }}_role"
            name="role"
            class="form-select {{ $errorSource->has('role') ? 'is-invalid' : '' }}"
            data-role-select
            required
        >
            <option value="user" {{ old('role', $user?->role ?? 'user') === 'user' ? 'selected' : '' }}>User</option>
            <option value="admin" {{ old('role', $user?->role) === 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        @if($errorSource->has('role'))
            <div class="invalid-feedback">{{ $errorSource->first('role') }}</div>
        @endif
    </div>

    <div class="col-lg-4" data-access-wrapper>
        <label for="{{ $prefix }}_access_profile" class="form-label">Paket Akses</label>
        <select
            id="{{ $prefix }}_access_profile"
            name="access_profile"
            class="form-select {{ $errorSource->has('access_profile') ? 'is-invalid' : '' }}"
            data-access-select
        >
            <option value="">Pilih akses</option>
            <option value="viewer" {{ old('access_profile', $user?->access_profile ?? 'viewer') === 'viewer' ? 'selected' : '' }}>Auditee</option>
            <option value="df_editor" {{ old('access_profile', $user?->access_profile) === 'df_editor' ? 'selected' : '' }}>Client DF Editor</option>
            <option value="assessor" {{ old('access_profile', $user?->access_profile) === 'assessor' ? 'selected' : '' }}>Assessor</option>
        </select>
        <div class="form-text">Admin otomatis mendapat akses penuh.</div>
        @if($errorSource->has('access_profile'))
            <div class="invalid-feedback">{{ $errorSource->first('access_profile') }}</div>
        @endif
    </div>

    <div class="col-lg-4">
        <label for="{{ $prefix }}_jabatan" class="form-label">Jabatan</label>
        <input
            id="{{ $prefix }}_jabatan"
            type="text"
            name="jabatan"
            class="form-control {{ $errorSource->has('jabatan') ? 'is-invalid' : '' }}"
            value="{{ old('jabatan', $user?->jabatan ?? '') }}"
            required
        >
        @if($errorSource->has('jabatan'))
            <div class="invalid-feedback">{{ $errorSource->first('jabatan') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label d-block">Organisasi</label>
        <div class="border rounded-3 p-3 bg-white {{ $errorSource->has('organization_ids') || $errorSource->has('organization_ids.*') ? 'border-danger' : '' }}">
            <div class="row g-2">
                @foreach($organizationCatalog as $organization)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input
                                id="{{ $prefix }}_organization_{{ $organization->organization_id }}"
                                type="checkbox"
                                name="organization_ids[]"
                                value="{{ $organization->organization_id }}"
                                class="form-check-input"
                                {{ in_array((int) $organization->organization_id, $selectedOrganizationIds, true) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="{{ $prefix }}_organization_{{ $organization->organization_id }}">
                                {{ $organization->organization_name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @if($errorSource->has('organization_ids'))
            <div class="invalid-feedback d-block">{{ $errorSource->first('organization_ids') }}</div>
        @elseif($errorSource->has('organization_ids.*'))
            <div class="invalid-feedback d-block">{{ $errorSource->first('organization_ids.*') }}</div>
        @endif
    </div>
</div>
