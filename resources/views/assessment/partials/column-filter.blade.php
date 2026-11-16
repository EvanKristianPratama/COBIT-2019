<div class="text-end">
    <details class="column-filter">
        <summary class="btn btn-sm btn-outline-primary">
            <i class="fas fa-sliders-h me-1"></i> Pilih Kolom
        </summary>
        <div class="column-filter-panel text-start">
            <div class="fw-semibold mb-2">Kolom yang ditampilkan</div>
            @foreach($assessmentColumns as $columnKey => $columnLabel)
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" value="{{ $columnKey }}" data-column-toggle checked>
                    <span class="form-check-label">{{ $columnLabel }}</span>
                </label>
            @endforeach
        </div>
    </details>
</div>
