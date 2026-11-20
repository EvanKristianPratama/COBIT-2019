<div class="c5-tab-sheet bg-white border border-secondary-subtle rounded-bottom overflow-hidden">
    <!-- Header Titles matching Excel Sheet 4 -->
    <div class="px-4 py-3 bg-light border-bottom">
        <h2 class="h6 fw-bold text-dark mb-1 text-uppercase" style="letter-spacing: 0.5px;">Hierarchy of COBIT Processes to Achieve IT-related and Enterprise Goals</h2>
        <p class="text-secondary small mb-0">Based on mapping in ISACA's COBIT 5: Enabling Processes, Appendix C</p>
    </div>

    <!-- Toolbar (Clean Typography, No Icons) -->
    <div class="p-3 bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 480px;">
            <input type="text" class="form-control form-control-sm text-dark" 
                   placeholder="Cari proses (misal: EDM01, APO02) atau ITRG..." 
                   x-model="itrgSearch">
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="text-secondary small fw-semibold">Filter:</span>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-sm" 
                        :class="levelFilter === 'ALL' ? 'btn-dark' : 'btn-outline-dark'"
                        @click="levelFilter = 'ALL'">
                    Semua
                </button>
                <button type="button" class="btn btn-sm" 
                        :class="levelFilter === 'P' ? 'btn-dark' : 'btn-outline-dark'"
                        @click="levelFilter = 'P'">
                    Primary (P)
                </button>
                <button type="button" class="btn btn-sm" 
                        :class="levelFilter === 'S' ? 'btn-dark' : 'btn-outline-dark'"
                        @click="levelFilter = 'S'">
                    Secondary (S)
                </button>
            </div>
        </div>
    </div>

    <!-- Excel-like Table with Exact Sizing -->
    <div class="c5-table-container">
        <table class="c5-grid-table">
            <colgroup>
                <col style="width: 44%;">
                <col style="width: 38%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
            </colgroup>
            <thead>
                <tr class="c5-grid-th">
                    <th class="ps-3 text-start">IT-related Goals</th>
                    <th class="ps-3 text-start">COBIT Processes</th>
                    <th class="text-center">Primary and Secondary</th>
                    <th class="text-center">Number of Related COBIT Processes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itrgHierarchy as $item)
                    <!-- ITRG HEADER ROW -->
                    <tr class="c5-dim-row" 
                        id="itrg-target-{{ preg_replace('/\s+/', '-', $item['code']) }}"
                        :class="{'c5-highlighted-row': highlightedItrg === '{{ $item['code'] }}'}"
                        x-show="matchesItrgHeader({{ json_encode($item) }})">
                        <td class="ps-3 py-2 fw-bold text-dark">
                            <span class="badge border bg-white text-dark me-1" style="font-size: 0.72rem;">{{ $item['code'] }}</span>
                            <span>{{ $item['title'] }}</span>
                        </td>
                        <td class="bg-cell-empty"></td>
                        <td class="bg-cell-empty"></td>
                        <td class="text-center fw-bold text-dark fs-6">{{ $item['count'] }}</td>
                    </tr>

                    @foreach($item['processes'] ?? [] as $proc)
                        <!-- COBIT PROCESS ROW -->
                        <tr class="c5-itrg-row" 
                            x-show="matchesProcessItem('{{ $proc['code'] }}', '{{ addslashes($proc['title']) }}', '{{ $proc['level'] }}', {{ json_encode($item) }})">
                            <td class="bg-cell-empty"></td>
                            <td class="ps-4 py-2">
                                <span class="fw-bold text-dark me-2" style="font-size: 0.82rem;">{{ $proc['code'] }}</span>
                                <span class="text-secondary" style="font-size: 0.85rem;">{{ $proc['title'] }}</span>
                            </td>
                            <td class="text-center py-2">
                                <span class="badge {{ $proc['level'] === 'P' ? 'bg-dark text-white' : 'bg-white text-dark border' }}" 
                                      style="font-size: 0.72rem; font-weight: 700; min-width: 26px;">
                                    {{ $proc['level'] }}
                                </span>
                            </td>
                            <td class="bg-cell-empty"></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
