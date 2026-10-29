<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>COBIT 2019</title>

    <link rel="icon" href="{{ asset('images/cobit.png') }}" type="image/png">

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --cobit-primary: #0f2b5c;
            --cobit-secondary: #1a3d6b;
            --cobit-accent: #0f6ad9;
            --cobit-light: #f8fafc;
            --cobit-gradient: linear-gradient(135deg, #081a3d, #0f2b5c, #1a3d6b);
            --navbar-height: 68px;
        }

        body {
            background: var(--cobit-light);
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: var(--navbar-height);
        }

        #app {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .app-navbar {
            background: var(--cobit-gradient) !important;
            border-bottom: 0;
            box-shadow: 0 4px 20px rgba(15, 43, 92, 0.4);
            min-height: var(--navbar-height);
            z-index: 1030;
        }

        .app-navbar .navbar-brand img {
            height: 40px;
            width: auto;
            filter: brightness(0) invert(1);
            transition: transform 0.3s ease;
        }

        .app-navbar .navbar-brand:hover img {
            transform: scale(1.05);
        }

        .app-navbar .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: none !important;
        }

        .app-navbar .navbar-toggler-icon {
            filter: none;
        }

        .app-nav-link {
            color: rgba(255, 255, 255, 0.92) !important;
            font-weight: 600;
            padding: 0.5rem 0.9rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .app-nav-link:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff !important;
        }

        .top-user-trigger {
            background: #fff;
            border: 1px solid #dbe4ee;
            border-radius: 999px;
            padding: 0.2rem 0.5rem 0.2rem 0.28rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 4px 14px rgba(15, 43, 92, 0.08);
            color: #1f2937;
            transition: all 0.2s ease;
        }

        .top-user-trigger:hover {
            border-color: #c0d1e5;
            box-shadow: 0 6px 18px rgba(15, 43, 92, 0.14);
        }

        .top-user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a3d6b, #0f2b5c);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        .top-user-name {
            color: #0f172a;
            font-weight: 600;
            line-height: 1;
            font-size: 0.78rem;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Organization Pill & User Trigger matching user screenshot */
        .top-org-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 1.05rem;
            max-width: 280px;
            border-radius: 999px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
            text-decoration: none;
            outline: none;
        }

        .top-org-chip:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }

        .top-org-text {
            color: #0f172a;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .top-user-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px 4px 5px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            text-decoration: none;
        }

        .top-user-btn:hover, .top-user-btn:focus-visible, .top-user-btn[aria-expanded="true"] {
            background: #f8fafc;
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(71, 85, 105, 0.12);
        }

        .top-user-avatar-sq {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #e5e7eb;
            color: #374151;
            font-size: 0.95rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            flex-shrink: 0;
        }

        .top-user-name {
            color: #0f172a;
            font-weight: 600;
            font-size: 0.88rem;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .top-user-chevron {
            font-size: 0.72rem;
            color: #64748b;
            transition: transform 0.2s ease;
        }

        .top-user-btn[aria-expanded="true"] .top-user-chevron {
            transform: rotate(180deg);
        }

        .user-company-dropdown {
            position: absolute !important;
            top: calc(100% + 10px) !important;
            right: 0 !important;
            left: auto !important;
            min-width: 320px !important;
            max-width: 360px !important;
            padding: 0 !important;
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12) !important;
            z-index: 99999 !important;
            display: none !important;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity 0.15s ease, transform 0.15s ease;
            pointer-events: none;
        }

        .user-company-dropdown.show {
            display: block !important;
            opacity: 1 !important;
            transform: translateY(0) !important;
            pointer-events: auto !important;
        }

        .user-select-company-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            line-height: 1.2;
        }

        .org-available-badge {
            padding: 0.25rem 0.55rem;
            border-radius: 999px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .org-checklist-container {
            max-height: 240px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .org-checklist-item {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.6rem 0.75rem;
            margin-bottom: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            cursor: pointer;
            user-select: none;
            transition: background-color 0.12s ease, border-color 0.12s ease;
        }

        .org-checklist-item:hover,
        .org-checklist-item.is-selected {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .org-checkbox {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: #475569;
            flex-shrink: 0;
            cursor: pointer;
        }

        .org-checkbox:focus-visible {
            outline: 2px solid #64748b;
            outline-offset: 2px;
        }

        .org-name-text {
            min-width: 0;
            color: #374151;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .org-selection-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-top: 0.25rem;
        }

        .org-selection-status {
            color: #6b7280;
            font-size: 0.75rem;
        }

        .org-selection-apply {
            padding: 0.4rem 0.75rem;
            border: 1px solid #374151;
            border-radius: 6px;
            background: #374151;
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 500;
            transition: background-color 0.12s ease;
        }

        .org-selection-apply:hover:not(:disabled) {
            background: #1f2937;
        }

        .org-selection-apply:disabled {
            border-color: #d1d5db;
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .breadcrumb-wrapper {
            background: transparent;
            padding: 12px 0 0;
            position: sticky;
            top: var(--navbar-height);
            z-index: 1020;
        }

        .breadcrumb {
            margin-bottom: 0;
            font-size: 0.88rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.55rem 0.95rem;
            box-shadow: 0 4px 14px rgba(15, 43, 92, 0.05);
            overflow-x: auto;
            white-space: nowrap;
        }

        .breadcrumb-item a {
            color: var(--cobit-secondary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .breadcrumb-item a:hover {
            color: var(--cobit-accent);
            text-decoration: none;
        }

        .breadcrumb-item a.breadcrumb-link-disabled {
            pointer-events: none;
            cursor: default;
            color: #94a3b8 !important;
            opacity: 0.95;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "/";
            font-weight: 600;
            font-size: 0.9rem;
            color: #ccc;
        }

        .breadcrumb-item.active {
            color: #6c757d;
            font-weight: 500;
        }

        .breadcrumb-item a.active {
            color: var(--cobit-accent) !important;
            font-weight: 700;
        }

        .offcanvas {
            background: var(--cobit-gradient);
            color: #fff;
        }

        .offcanvas-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .offcanvas .nav-link {
            color: rgba(255, 255, 255, 0.95) !important;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .offcanvas .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .sidebar-user-meta {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.6rem;
        }

        .sidebar-meta-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
        }

        .sidebar-section-label {
            display: inline-block;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.4rem;
            margin-bottom: 0.5rem;
            padding-left: 0.7rem;
        }

        .offcanvas .logout-btn {
            color: #dc3545 !important;
        }

        .main-content {
            padding-top: 2rem;
            padding-bottom: 1.25rem;
            flex: 1;
        }

        .app-footer {
            padding: 0.35rem 0 0.9rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.72rem;
            letter-spacing: 0.02em;
        }
    </style>
</head>

<body class="{{ Route::is('login', 'register') ? 'login' : '' }}">
    <div id="app">
        @php
            $isFluidPage = trim($__env->yieldContent('page-mode')) === 'fluid';
            $hideBreadcrumb = trim($__env->yieldContent('hide_breadcrumb')) === '1';
        @endphp

        <nav class="navbar navbar-expand-md navbar-dark fixed-top app-navbar">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('images/logo-divusi.png') }}" alt="Divusi Logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto align-items-center">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link app-nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>
                                        {{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            @php
                                $currentUser = Auth::user();
                                $assignedOrgs = $currentUser->assignedOrganizations();
                                $activeOrgIds = $currentUser->activeOrganizationIds($assignedOrgs);
                                $activeOrgId = $currentUser->activeOrganizationId($assignedOrgs);
                                $activeOrgName = $assignedOrgs->firstWhere('organization_id', $activeOrgId)?->organization_name
                                    ?? $currentUser->activeOrganizationName();
                                $topOrgChipLabel = count($activeOrgIds) > 1
                                    ? count($activeOrgIds) . ' PERUSAHAAN'
                                    : strtoupper($activeOrgName);
                            @endphp
                            <li class="nav-item d-flex align-items-center gap-2">
                                <button class="top-org-chip d-none d-sm-inline-flex" id="topOrgChip" title="{{ count($activeOrgIds) }} perusahaan dipilih. Klik untuk mengubah." type="button">
                                    <span class="top-org-text" id="topOrgChipText">{{ $topOrgChipLabel }}</span>
                                </button>

                                <div class="dropdown position-relative">
                                    <button class="top-user-btn" id="userMenuDropdown" type="button" aria-expanded="false" aria-controls="userCompanyDropdownMenu">
                                        <span class="top-user-avatar-sq">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
                                        <span class="top-user-name d-none d-lg-inline">{{ $currentUser->name }}</span>
                                        <i class="fas fa-chevron-down top-user-chevron" aria-hidden="true"></i>
                                    </button>

                                    <div class="user-company-dropdown" id="userCompanyDropdownMenu" aria-labelledby="userMenuDropdown">
                                        <div class="px-3 py-2 pt-3">
                                            <div class="fw-bold text-dark fs-6">{{ $currentUser->name }}</div>
                                            <div class="text-muted small">{{ $currentUser->email }}</div>
                                        </div>

                                        <hr class="my-1 border-light-subtle">

                                        <div class="px-3 py-2">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="user-select-company-label">Pilih perusahaan</span>
                                                <span class="org-available-badge">{{ $assignedOrgs->count() }} tersedia</span>
                                            </div>

                                            <div class="org-checklist-container" id="organizationChecklist">
                                                @forelse($assignedOrgs as $assignedOrg)
                                                    @php
                                                        $organizationId = (int) $assignedOrg->organization_id;
                                                        $isSelected = in_array($organizationId, $activeOrgIds, true);
                                                    @endphp
                                                    <label class="org-checklist-item {{ $isSelected ? 'is-selected' : '' }}" for="organization-option-{{ $organizationId }}">
                                                        <input class="org-checkbox" id="organization-option-{{ $organizationId }}" type="checkbox" value="{{ $organizationId }}" {{ $isSelected ? 'checked' : '' }}>
                                                        <span class="org-name-text text-truncate">{{ $assignedOrg->organization_name }}</span>
                                                    </label>
                                                @empty
                                                    <div class="text-muted small py-2 text-center">
                                                        Belum ada perusahaan yang di-assign.
                                                    </div>
                                                @endforelse
                                            </div>

                                            <div class="org-selection-actions">
                                                <span class="org-selection-status" id="organizationSelectionStatus" role="status" aria-live="polite"></span>
                                                <button class="org-selection-apply" id="applyOrganizationSelection" type="button" disabled>
                                                    Terapkan pilihan
                                                </button>
                                            </div>
                                        </div>

                                        <hr class="my-1 border-light-subtle">

                                        <!-- Menu Actions -->
                                        <div class="p-2">
                                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="#" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                                                <i class="fas fa-bars text-secondary" style="width: 18px;"></i>
                                                <span class="small fw-semibold">Menu Navigasi Lengkap</span>
                                            </a>
                                            @if(Auth::user()->isAdmin())
                                                <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                                                    <i class="fas fa-shield-alt text-secondary" style="width: 18px;"></i>
                                                    <span class="small fw-semibold">Admin Console</span>
                                                </a>
                                            @endif
                                            <a class="dropdown-item rounded-2 py-2 text-secondary d-flex align-items-center gap-2 logout-btn" href="{{ route('logout') }}">
                                                <i class="fas fa-sign-out-alt text-secondary" style="width: 18px;"></i>
                                                <span class="small fw-semibold">Logout</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @auth
            @php
                $authUser = Auth::user();
                $disableBreadcrumbClick = request()->routeIs(
                    'df1.*',
                    'df2.*',
                    'df3.*',
                    'df4.*',
                    'df5.*',
                    'df6.*',
                    'df7.*',
                    'df8.*',
                    'df9.*',
                    'df10.*',
                    'step2.*',
                    'step3.*',
                    'step4.*',
                    'target-capability.*',
                    'target-maturity.*'
                );
                $approvalPending = $authUser->requiresAdminApproval();
                $canAccessCobit = ! $approvalPending && ($authUser->isAdmin() || $authUser->can(\App\Support\Authorization\PermissionCatalog::CobitView));
                $canAccessAssessments = ! $approvalPending && ($authUser->isAdmin() || $authUser->can(\App\Support\Authorization\PermissionCatalog::AssessmentsView));
                $canAccessSpreadsheet = ! $approvalPending;
            @endphp
            @unless($hideBreadcrumb)
                <div class="breadcrumb-wrapper">
                    <div class="container">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a
                                        href="{{ $disableBreadcrumbClick ? '#' : route('home') }}"
                                        class="{{ Route::is('home') ? 'active' : '' }} {{ $disableBreadcrumbClick ? 'breadcrumb-link-disabled' : '' }}"
                                        @if($disableBreadcrumbClick) tabindex="-1" aria-disabled="true" @endif
                                    >
                                        <i class="fas fa-home"></i> Home
                                    </a>
                                </li>
                                @if($canAccessCobit)
                                    <li class="breadcrumb-item">
                                        <a
                                            href="{{ $disableBreadcrumbClick ? '#' : route('focus-areas.index') }}"
                                            class="{{ (Route::is('cobit_component.*') || Route::is('focus-areas.*')) && !Route::is('cobit.evolution') ? 'active' : '' }} {{ $disableBreadcrumbClick ? 'breadcrumb-link-disabled' : '' }}"
                                            @if($disableBreadcrumbClick) tabindex="-1" aria-disabled="true" @endif
                                        >
                                            <i class="fas fa-book"></i> Governance System Component
                                        </a>
                                    </li>
                                    @if(Route::is('cobit.evolution'))
                                        <li class="breadcrumb-item active" aria-current="page">
                                            <i class="fas fa-code-branch"></i> GAMO Mapping Evolution
                                        </li>
                                    @endif
                                    <li class="breadcrumb-item">
                                        <a
                                            href="{{ $disableBreadcrumbClick ? '#' : route('cobit.home') }}"
                                            class="{{ Route::is('cobit.*') ? 'active' : '' }} {{ $disableBreadcrumbClick ? 'breadcrumb-link-disabled' : '' }}"
                                            @if($disableBreadcrumbClick) tabindex="-1" aria-disabled="true" @endif
                                        >
                                            <i class="fas fa-tools"></i> Design I&T Tailored Governance System
                                        </a>
                                    </li>
                                @endif
                                @if($canAccessAssessments)
                                    <li class="breadcrumb-item">
                                        <a
                                            href="{{ $disableBreadcrumbClick ? '#' : route('assessment.index') }}"
                                            class="{{ Route::is('assessment.*') ? 'active' : '' }} {{ $disableBreadcrumbClick ? 'breadcrumb-link-disabled' : '' }}"
                                            @if($disableBreadcrumbClick) tabindex="-1" aria-disabled="true" @endif
                                        >
                                            <i class="fas fa-clipboard-check"></i> Assessment Maturity & Capability
                                        </a>
                                    </li>
                                @endif
                                @if($canAccessSpreadsheet)
                                    <li class="breadcrumb-item">
                                        <a
                                            href="{{ $disableBreadcrumbClick ? '#' : route('spreadsheet.index') }}"
                                            class="{{ Route::is('spreadsheet.*') ? 'active' : '' }} {{ $disableBreadcrumbClick ? 'breadcrumb-link-disabled' : '' }}"
                                            @if($disableBreadcrumbClick) tabindex="-1" aria-disabled="true" @endif
                                        >
                                            <i class="fas fa-table"></i> Spreadsheet Tools
                                        </a>
                                    </li>
                                @endif
                            </ol>
                        </nav>
                    </div>
                </div>
            @endunless
        @endauth

        <main class="container-fluid main-content">
            <div class="{{ $isFluidPage ? 'container-fluid px-0' : 'container' }}">
                @yield('content')
            </div>
        </main>

        <footer class="app-footer">
            COBIT 2019 &middot; {{ config('app.version', '1.5.2') }}
        </footer>

        @auth
            <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas"
                aria-labelledby="sidebarOffcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">
                        <i class="fas fa-user-circle me-2"></i> Menu Pengguna
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-light text-primary mx-auto mb-2 d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem; font-weight: bold;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                        <small class="text-white-50">{{ Auth::user()->email }}</small>
                        <div class="sidebar-user-meta">
                            <span class="sidebar-meta-badge" id="sidebarOrgBadge">{{ $activeOrgName }}</span>
                            <span class="sidebar-meta-badge">{{ Auth::user()->jabatan ?? 'Jabatan' }}</span>
                        </div>
                    </div>
                    <hr class="border-light">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <span class="sidebar-section-label">Navigasi</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">
                                <i class="fas fa-home me-2"></i> Home
                            </a>
                        </li>
                        @if($canAccessCobit)
                            <li class="nav-item">
                                <a class="nav-link {{ (Route::is('focus-areas.*') || Route::is('cobit_component.*')) && !Route::is('cobit.evolution') ? 'active' : '' }}" href="{{ route('focus-areas.index') }}">
                                    <i class="fas fa-book me-2"></i> Governance System Component
                                </a>
                            </li>
                            <li class="nav-item ps-3">
                                <a class="nav-link {{ Route::is('cobit.evolution') ? 'active' : '' }}" href="{{ route('cobit.evolution') }}" style="font-size: 0.88rem;">
                                    <i class="fas fa-code-branch me-2 text-warning"></i> COBIT GAMO Mapping Evolution
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cobit.home') }}">
                                    <i class="fas fa-tools me-2"></i> Design Factor
                                </a>
                            </li>
                        @endif
                        @if($canAccessAssessments)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('assessment.index') }}">
                                    <i class="fas fa-clipboard-check me-2"></i> Assessment
                                </a>
                            </li>
                        @endif
                        @if($canAccessSpreadsheet)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('spreadsheet.index') }}">
                                    <i class="fas fa-table me-2"></i> Spreadsheet Tools
                                </a>
                            </li>
                        @endif

                        @if($authUser->isAdmin())
                            <li class="nav-item mt-2">
                                <span class="sidebar-section-label">Admin Console</span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.assessments.index') }}">
                                    <i class="fas fa-clipboard-check me-2"></i> Manage Assessment
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users.index') }}">
                                    <i class="fas fa-users me-2"></i> Manage User
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.access.index') }}">
                                    <i class="fas fa-key me-2"></i> Manage Akses
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.design-factors.index') }}">
                                    <i class="fas fa-sitemap me-2"></i> Manage Design Factor
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.organizations.index') }}">
                                    <i class="fas fa-building me-2"></i> Manage Organization
                                </a>
                            </li>
                        @endif

                        <li class="nav-item mt-2">
                            <a class="nav-link logout-btn text-danger bg-white rounded" href="{{ route('logout') }}">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @endauth

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Standalone reliable toggle for Company Dropdown
            function toggleCompanyDropdown(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                const menu = document.getElementById('userCompanyDropdownMenu');
                const btn = document.getElementById('userMenuDropdown');
                if (!menu) return;

                const isShown = menu.classList.contains('show');
                if (isShown) {
                    menu.classList.remove('show');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                } else {
                    menu.classList.add('show');
                    if (btn) btn.setAttribute('aria-expanded', 'true');
                }
            }

            function closeCompanyDropdown() {
                const menu = document.getElementById('userCompanyDropdownMenu');
                const btn = document.getElementById('userMenuDropdown');
                if (menu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                }
            }

            // Click listener for PT pill & User Profile button
            const topOrgChip = document.getElementById('topOrgChip');
            if (topOrgChip) {
                topOrgChip.addEventListener('click', toggleCompanyDropdown);
            }

            const userMenuDropdown = document.getElementById('userMenuDropdown');
            if (userMenuDropdown) {
                userMenuDropdown.addEventListener('click', toggleCompanyDropdown);
            }

            // Prevent closing when clicking inside the dropdown content
            const userCompanyDropdownMenu = document.getElementById('userCompanyDropdownMenu');
            if (userCompanyDropdownMenu) {
                userCompanyDropdownMenu.addEventListener('click', function(e) {
                    if (!e.target.closest('[data-bs-toggle="offcanvas"]') && !e.target.closest('.logout-btn') && !e.target.closest('.org-checklist-item')) {
                        e.stopPropagation();
                    }
                });
            }

            // Close on click outside
            document.addEventListener('click', function(e) {
                const menu = document.getElementById('userCompanyDropdownMenu');
                const btn = document.getElementById('userMenuDropdown');
                const chip = document.getElementById('topOrgChip');
                if (!menu || !menu.classList.contains('show')) return;

                if ((btn && btn.contains(e.target)) || (chip && chip.contains(e.target)) || menu.contains(e.target)) {
                    return;
                }
                closeCompanyDropdown();
            });

            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeCompanyDropdown();
                }
            });

            const organizationCheckboxes = Array.from(document.querySelectorAll('.org-checkbox'));
            const applyOrganizationSelection = document.getElementById('applyOrganizationSelection');
            const organizationSelectionStatus = document.getElementById('organizationSelectionStatus');
            const initialOrganizationIds = organizationCheckboxes
                .filter(function(checkbox) { return checkbox.checked; })
                .map(function(checkbox) { return checkbox.value; })
                .sort();

            function selectedOrganizationIds() {
                return organizationCheckboxes
                    .filter(function(checkbox) { return checkbox.checked; })
                    .map(function(checkbox) { return checkbox.value; });
            }

            function updateOrganizationSelection() {
                const selectedIds = selectedOrganizationIds().sort();
                const isChanged = selectedIds.length !== initialOrganizationIds.length
                    || selectedIds.some(function(id, index) { return id !== initialOrganizationIds[index]; });

                if (organizationSelectionStatus) {
                    organizationSelectionStatus.textContent = selectedIds.length === 0
                        ? 'Pilih minimal satu perusahaan.'
                        : selectedIds.length + ' perusahaan dipilih';
                }

                if (applyOrganizationSelection) {
                    applyOrganizationSelection.disabled = selectedIds.length === 0 || !isChanged;
                }
            }

            organizationCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    this.closest('.org-checklist-item').classList.toggle('is-selected', this.checked);
                    updateOrganizationSelection();
                });
            });

            if (applyOrganizationSelection) {
                applyOrganizationSelection.addEventListener('click', function() {
                    const organizationIds = selectedOrganizationIds();
                    if (organizationIds.length === 0) return;

                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
                    this.disabled = true;
                    this.textContent = 'Menyimpan...';
                    organizationCheckboxes.forEach(function(checkbox) { checkbox.disabled = true; });
                    if (organizationSelectionStatus) {
                        organizationSelectionStatus.textContent = 'Menyimpan pilihan...';
                    }

                    fetch('{{ route("user.active-organization") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ organization_ids: organizationIds })
                    })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            if (!response.ok || !data.success) {
                                throw new Error(data.message || 'Pilihan perusahaan gagal disimpan.');
                            }

                            return data;
                        });
                    })
                    .then(function() {
                        window.location.reload();
                    })
                    .catch(function(error) {
                        organizationCheckboxes.forEach(function(checkbox) { checkbox.disabled = false; });
                        applyOrganizationSelection.textContent = 'Terapkan pilihan';
                        updateOrganizationSelection();
                        if (organizationSelectionStatus) {
                            organizationSelectionStatus.textContent = error.message || 'Pilihan perusahaan gagal disimpan.';
                        }
                    });
                });
            }

            updateOrganizationSelection();

            // Logout confirmation
            document.querySelectorAll('.logout-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Logout?',
                        text: 'Anda akan keluar dari sesi ini.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0f2b5c',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
