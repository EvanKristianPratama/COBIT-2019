<div class="d-flex justify-content-between align-items-center gap-2 mb-3 flex-wrap">
    <div>
        @if(request()->filled('year'))
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-3 rounded-pill d-inline-flex align-items-center gap-2" style="font-size: 0.8rem; font-weight: 500;">
                <i class="fas fa-filter text-primary small"></i>
                <span>Tahun: <strong>{{ request('year') }}</strong></span>
                <a href="{{ request()->fullUrlWithQuery(['year' => null, 'my_page' => 1, 'assigned_page' => 1]) }}" class="text-primary text-decoration-none ms-1" title="Hapus filter tahun">
                    <i class="fas fa-times-circle"></i>
                </a>
            </span>
        @endif
    </div>

    <div class="d-flex align-items-center gap-2 ms-auto">
        {{-- Dropdown Urutan / Filter Tahun --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle table-filter-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-sort-numeric-{{ ($sortDirection ?? 'desc') === 'asc' ? 'down' : 'down-alt' }} me-1"></i>
                <span>Tahun: <strong>{{ ($sortDirection ?? 'desc') === 'asc' ? 'Terlama' : 'Terbaru' }}</strong></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 table-filter-dropdown" style="min-width: 220px;">
                <li class="dropdown-header">Urutan Tahun</li>
                <li>
                    <a class="dropdown-item d-flex align-items-center justify-content-between {{ ($sortBy ?? 'year') === 'year' && ($sortDirection ?? 'desc') === 'desc' ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_direction' => 'desc', 'my_page' => 1, 'assigned_page' => 1]) }}">
                        <span><i class="fas fa-sort-numeric-down-alt me-2 opacity-75"></i>Terbaru ke Terlama</span>
                        @if(($sortBy ?? 'year') === 'year' && ($sortDirection ?? 'desc') === 'desc')
                            <i class="fas fa-check small ms-2"></i>
                        @endif
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center justify-content-between {{ ($sortBy ?? 'year') === 'year' && ($sortDirection ?? 'desc') === 'asc' ? 'active' : '' }}" 
                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_direction' => 'asc', 'my_page' => 1, 'assigned_page' => 1]) }}">
                        <span><i class="fas fa-sort-numeric-down me-2 opacity-75"></i>Terlama ke Terbaru</span>
                        @if(($sortBy ?? 'year') === 'year' && ($sortDirection ?? 'desc') === 'asc')
                            <i class="fas fa-check small ms-2"></i>
                        @endif
                    </a>
                </li>
                @if(!empty($yearOptions) && count($yearOptions) > 1)
                    <li><hr class="dropdown-divider my-1"></li>
                    <li class="dropdown-header">Filter Tahun</li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between {{ !request()->filled('year') ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['year' => null, 'my_page' => 1, 'assigned_page' => 1]) }}">
                            <span>Semua Tahun</span>
                            @if(!request()->filled('year'))
                                <i class="fas fa-check small ms-2"></i>
                            @endif
                        </a>
                    </li>
                    @foreach($yearOptions as $yr)
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between {{ (string) request('year') === (string) $yr ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['year' => $yr, 'my_page' => 1, 'assigned_page' => 1]) }}">
                                <span>Tahun {{ $yr }}</span>
                                @if((string) request('year') === (string) $yr)
                                    <i class="fas fa-check small ms-2"></i>
                                @endif
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>

        {{-- Dropdown Pilih Kolom --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle table-filter-btn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="fas fa-sliders-h me-1"></i>
                <span>Pilih Kolom</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 table-filter-dropdown p-3" style="width: 230px;">
                <div class="dropdown-header px-0 pt-0 mb-2">Kolom Ditampilkan</div>
                @foreach($assessmentColumns as $columnKey => $columnLabel)
                    <label class="form-check d-flex align-items-center gap-2 mb-1.5 cursor-pointer user-select-none" style="cursor: pointer; font-size: 0.82rem;">
                        <input class="form-check-input mt-0" type="checkbox" value="{{ $columnKey }}" data-column-toggle checked>
                        <span class="form-check-label text-dark">{{ $columnLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>
