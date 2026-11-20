@props(['active' => 'cobit5'])

<div class="c5-version-nav d-inline-flex align-items-center bg-white p-1 rounded-pill border border-secondary-subtle shadow-xs mb-4">
    <a href="{{ route('cobit.home') }}" 
       class="btn btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 {{ $active === 'cobit2019' ? 'active-tab' : 'inactive-tab' }}">
        <span class="fw-semibold" style="font-size: 0.85rem;">COBIT 2019 <small class="opacity-75 fw-normal">(Design Factor)</small></span>
    </a>
    <a href="{{ route('cobit5.scope.index') }}" 
       class="btn btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 {{ $active === 'cobit5' ? 'active-tab' : 'inactive-tab' }}">
        <span class="fw-semibold" style="font-size: 0.85rem;">COBIT 5 <small class="opacity-75 fw-normal">(Assessment Scope)</small></span>
    </a>
</div>

<style>
.c5-version-nav {
    border-color: #d1d5db !important;
}
.c5-version-nav .active-tab {
    background-color: #111827 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}
.c5-version-nav .inactive-tab {
    color: #4b5563 !important;
    text-decoration: none;
    background: transparent;
}
.c5-version-nav .inactive-tab:hover {
    color: #111827 !important;
    background-color: #f3f4f6;
}
</style>
