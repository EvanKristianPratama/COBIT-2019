@php
    $workProducts = $workProducts ?? collect();
    $wpErrors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $wpCode = app(\App\Services\Cobit5\DocumentService::class)->code($objective->objective_id);
@endphp
<section class="c5-wp-panel" aria-label="Master Work Product COBIT 5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Master Work Product</h2>
            <div class="small text-muted">{{ $wpCode }} · {{ $workProducts->count() }} Work Product</div>
        </div>
        @can('design-factors.input')
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#c5WpModal" data-c5-wp-new>Tambah WP</button>
        @endcan
    </div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($wpErrors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($wpErrors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="table-responsive">
        <table class="c5-table c5-wp-table">
            <colgroup><col style="width:12%"><col style="width:25%"><col></colgroup>
            <thead><tr class="c5-subhead"><th scope="col">WP ID</th><th scope="col">WP</th><th scope="col">Description</th></tr></thead>
            <tbody>
                @forelse($workProducts as $wp)
                    <tr>
                        <td>{{ $wp->wp_id }}
                            @can('design-factors.input')
                                <button class="btn btn-link btn-sm p-0 d-block text-decoration-none" type="button" data-bs-toggle="modal" data-bs-target="#c5WpModal"
                                    data-c5-wp-edit="{{ $wp->id }}">Edit</button>
                            @endcan
                        </td>
                        <td>{{ $wp->wp }}</td>
                        <td class="c5-text">{{ $wp->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="c5-empty">Belum ada Work Product untuk proses {{ $wpCode }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@can('design-factors.input')
    <div class="modal fade" id="c5WpModal" tabindex="-1" aria-labelledby="c5WpModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('cobit5.work-products.save', ['objectiveId' => $objective->objective_id]) }}">
            @csrf
            <input type="hidden" name="focus_area_id" value="{{ $focusAreaId }}">
            <input type="hidden" name="reference_id" id="c5WpReferenceId" value="{{ old('reference_id') }}">
            <input type="hidden" name="revision" id="c5WpRevision" value="{{ old('revision') }}">
            <div class="modal-header"><h2 class="modal-title fs-5" id="c5WpModalTitle">Work Product {{ $wpCode }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label" for="c5WpId">WP ID</label><input class="form-control" id="c5WpId" name="wp_id" maxlength="30" required value="{{ old('wp_id') }}" placeholder="{{ $wpCode }}-WP1"></div>
                <div class="mb-3"><label class="form-label" for="c5WpName">WP</label><input class="form-control" id="c5WpName" name="wp" maxlength="255" required value="{{ old('wp') }}"></div>
                <div><label class="form-label" for="c5WpDescription">Description</label><textarea class="form-control" id="c5WpDescription" name="description" rows="6" maxlength="20000">{{ old('description') }}</textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" type="submit">Simpan</button></div>
        </form></div>
    </div>
@endcan
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rows = @json($workProducts->values());
    function activate() {
        const trigger = document.querySelector('[data-bs-target="#c5-work-products"]');
        if (trigger && window.bootstrap?.Tab) bootstrap.Tab.getOrCreateInstance(trigger).show();
    }
    if (location.hash === '#c5-work-products' || @json($wpErrors->any())) {
        activate();
        // The management page keeps its master panel hidden until selected.
        const masterPanel = document.getElementById('masterPanel');
        if (masterPanel) document.getElementById('masterToggleBtn')?.click();
    }
    const modal = document.getElementById('c5WpModal');
    modal?.addEventListener('show.bs.modal', event => {
        if (!event.relatedTarget) return;
        const row = rows.find(row => String(row.id) === event.relatedTarget.dataset.c5WpEdit);
        document.getElementById('c5WpReferenceId').value = row?.id || '';
        document.getElementById('c5WpRevision').value = row?.revision || '';
        document.getElementById('c5WpId').value = row?.wp_id || '';
        document.getElementById('c5WpName').value = row?.wp || '';
        document.getElementById('c5WpDescription').value = row?.description || '';
    });
    if (@json($wpErrors->any()) && modal && window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(modal).show();
});
</script>
