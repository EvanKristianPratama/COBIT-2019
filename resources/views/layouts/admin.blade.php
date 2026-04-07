@extends('layouts.app')

@section('page-mode', 'fluid')
@section('hide_breadcrumb', '1')

@section('content')
@php
    $adminSidebarItems = [
        [
            'label' => 'Manage Organization',
            'icon' => 'fa-building',
            'route' => route('admin.organizations.index'),
            'active' => request()->routeIs('admin.organizations.*'),
        ],
        [
            'label' => 'Manage User',
            'icon' => 'fa-users',
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
        ],
        [
            'label' => 'Manage Akses',
            'icon' => 'fa-key',
            'route' => route('admin.access.index'),
            'active' => request()->routeIs('admin.access.*'),
        ],
        [
            'label' => 'Manage Design Factor',
            'icon' => 'fa-sitemap',
            'route' => route('admin.design-factors.index'),
            'active' => request()->routeIs('admin.design-factors.*'),
        ],
        [
            'label' => 'Manage Assessment',
            'icon' => 'fa-clipboard-check',
            'route' => route('admin.assessments.index'),
            'active' => request()->routeIs('admin.assessments.*', 'admin.dashboard', 'admin.requests*'),
        ],
    ];
@endphp

<style>
    :root {
        --admin-shell-bg: var(--cobit-light);
        --admin-sidebar-bg: var(--cobit-gradient);
        --admin-sidebar-border: rgba(255, 255, 255, 0.12);
        --admin-sidebar-text: rgba(255, 255, 255, 0.92);
        --admin-sidebar-muted: rgba(255, 255, 255, 0.64);
        --admin-sidebar-active: rgba(15, 106, 217, 0.18);
        --admin-sidebar-active-border: rgba(15, 106, 217, 0.34);
        --admin-panel-bg: #ffffff;
        --admin-panel-border: #d7dfeb;
        --admin-panel-text: #0f172a;
        --admin-panel-muted: #64748b;
        --admin-panel-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
    }

    .admin-shell {
        display: grid;
        grid-template-columns: 292px minmax(0, 1fr);
        gap: 1.1rem;
        min-height: calc(100vh - var(--navbar-height) - 1.75rem);
        padding: 1rem 1rem 1.25rem;
        background: var(--admin-shell-bg);
    }

    .admin-sidebar {
        border-radius: 28px;
        background: var(--admin-sidebar-bg);
        border: 1px solid var(--admin-sidebar-border);
        color: var(--admin-sidebar-text);
        padding: 1.2rem 1rem 1rem;
        box-shadow: 0 18px 48px rgba(10, 20, 33, 0.22);
        position: sticky;
        top: calc(var(--navbar-height) + 1rem);
        height: fit-content;
    }

    .admin-sidebar-brand {
        padding: 0.5rem 0.45rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 1rem;
    }

    .admin-sidebar-kicker {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.78);
        margin-bottom: 0.35rem;
    }

    .admin-sidebar-title {
        font-size: 1.3rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }

    .admin-user-card {
        margin: 1rem 0 1.15rem;
        padding: 0.9rem;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .admin-user-name {
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 0.15rem;
    }

    .admin-user-email {
        color: var(--admin-sidebar-muted);
        font-size: 0.8rem;
        margin-bottom: 0.7rem;
    }

    .admin-user-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .admin-user-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        font-size: 0.74rem;
        font-weight: 800;
    }

    .admin-sidebar-section {
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        letter-spacing: 0.07em;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0 0.45rem;
        margin-bottom: 0.55rem;
    }

    .admin-sidebar-links {
        display: grid;
        gap: 0.6rem;
    }

    .admin-sidebar-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 0.82rem 0.9rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid transparent;
        color: var(--admin-sidebar-text);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .admin-sidebar-link:hover {
        color: #fff;
        border-color: rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.12);
        transform: translateX(3px);
    }

    .admin-sidebar-link.active {
        background: var(--admin-sidebar-active);
        border-color: var(--admin-sidebar-active-border);
        box-shadow: 0 12px 26px rgba(8, 18, 29, 0.18);
    }

    .admin-sidebar-link-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .admin-sidebar-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        flex-shrink: 0;
    }

    .admin-sidebar-link.active .admin-sidebar-icon {
        background: rgba(255, 255, 255, 0.16);
    }

    .admin-sidebar-text {
        min-width: 0;
    }

    .admin-sidebar-label {
        display: block;
        font-weight: 800;
        font-size: 0.9rem;
        line-height: 1.2;
        margin-bottom: 0;
    }

    .admin-sidebar-arrow {
        color: rgba(255, 255, 255, 0.48);
        font-size: 0.82rem;
        flex-shrink: 0;
    }

    .admin-stage {
        min-width: 0;
    }

    .admin-stage-topbar {
        border: 1px solid var(--admin-panel-border);
        border-radius: 28px;
        background: linear-gradient(135deg, rgba(15, 43, 92, 0.06), rgba(15, 106, 217, 0.08));
        box-shadow: var(--admin-panel-shadow);
        padding: 1.25rem 1.35rem;
        margin-bottom: 1rem;
    }

    .admin-stage-title {
        font-size: clamp(1.55rem, 2vw, 2.2rem);
        font-weight: 800;
        margin-bottom: 0.3rem;
        color: var(--admin-panel-text);
    }

    .admin-stage-kicker {
        color: var(--cobit-accent);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.73rem;
        font-weight: 800;
        margin-bottom: 0.35rem;
    }

    .admin-stage-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.55rem;
    }

    .admin-stage-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.95rem;
        border-radius: 999px;
        text-decoration: none;
        border: 1px solid #d7dfeb;
        background: #fff;
        color: var(--cobit-secondary);
        font-size: 0.8rem;
        font-weight: 800;
    }

    .admin-stage-action:hover {
        color: var(--cobit-secondary);
        background: #f8fafc;
    }

    .admin-stage-body {
        min-width: 0;
    }

    @media (max-width: 1199.98px) {
        .admin-shell {
            grid-template-columns: 1fr;
        }

        .admin-sidebar {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .admin-shell {
            padding: 0.7rem 0.6rem 1rem;
        }

        .admin-stage-topbar {
            padding: 1rem;
        }

        .admin-stage-actions {
            justify-content: flex-start;
            margin-top: 0.9rem;
        }
    }
</style>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <div class="admin-sidebar-title">Admin Workspace</div>
        </div>

        <div class="admin-user-card">
            <div class="admin-user-name">{{ auth()->user()->name }}</div>
            <div class="admin-user-email">{{ auth()->user()->email }}</div>
            <div class="admin-user-chips">
                <span class="admin-user-chip">{{ auth()->user()->displayRoleLabel() }}</span>
                <span class="admin-user-chip">{{ auth()->user()->displayOrganizationSummary() }}</span>
            </div>
        </div>

        <div class="admin-sidebar-section">Admin Console</div>
        <div class="admin-sidebar-links">
            @foreach($adminSidebarItems as $adminSidebarItem)
                <a href="{{ $adminSidebarItem['route'] }}" class="admin-sidebar-link {{ $adminSidebarItem['active'] ? 'active' : '' }}">
                    <span class="admin-sidebar-link-main">
                        <span class="admin-sidebar-icon"><i class="fas {{ $adminSidebarItem['icon'] }}"></i></span>
                        <span class="admin-sidebar-text">
                            <span class="admin-sidebar-label">{{ $adminSidebarItem['label'] }}</span>
                        </span>
                    </span>
                    <span class="admin-sidebar-arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
            @endforeach
        </div>
    </aside>

    <section class="admin-stage">
        <div class="admin-stage-topbar">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="admin-stage-kicker">Admin Console</div>
                    <h1 class="admin-stage-title">@yield('admin_title', 'Admin Workspace')</h1>
                </div>
                <div class="col-lg-4">
                    <div class="admin-stage-actions">
                        <a href="{{ route('admin.organizations.index') }}" class="admin-stage-action">
                            <i class="fas fa-building"></i> Organisasi
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="admin-stage-action">
                            <i class="fas fa-users"></i> User
                        </a>
                        <a href="{{ route('admin.access.index') }}" class="admin-stage-action">
                            <i class="fas fa-key"></i> Akses
                        </a>
                        <a href="{{ route('admin.design-factors.index') }}" class="admin-stage-action">
                            <i class="fas fa-sitemap"></i> Design Factor
                        </a>
                        <a href="{{ route('admin.assessments.index') }}" class="admin-stage-action">
                            <i class="fas fa-clipboard-check"></i> Assessment
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-stage-body">
            @yield('admin_content')
        </div>
    </section>
</div>
@endsection
