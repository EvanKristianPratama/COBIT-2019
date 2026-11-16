@can('design-factors.input')
    @if($workProductsReady ?? false)
        <div class="c4-edit-field mb-3">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addWorkProductRow()">Tambah Work Product</button>
            <span class="small text-muted ms-2">Contoh WP ID: {{ $processCode }}-WP1</span>
        </div>
    @endif
@endcan
<div class="c4-wp-scroll" role="region" aria-label="Work Products {{ $processCode }}" tabindex="0">
    <table class="c4-wp-table">
        <colgroup>
            <col style="width: 11%">
            <col style="width: 19%">
            <col style="width: 70%">
        </colgroup>
        <thead>
            <tr>
                <th scope="col">WP ID</th>
                <th scope="col">WP</th>
                <th scope="col">Description</th>
            </tr>
        </thead>
        <tbody id="workProductsTableBody">
            @forelse($workProducts as $workProduct)
                <tr>
                    <td>{{ $workProduct->wp_id }}</td>
                    <td>{{ $workProduct->wp }}</td>
                    <td>
                        @php($descriptionParts = explode('Scope note:', $workProduct->description, 2))
                        <div class="c4-wp-description">{{ trim($descriptionParts[0]) }}</div>
                        @if(isset($descriptionParts[1]))
                            <p class="c4-wp-description c4-wp-scope-note"><strong>Scope note:</strong> {{ trim($descriptionParts[1]) }}</p>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="c4-wp-empty">Belum ada Work Product untuk proses {{ $processCode }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
