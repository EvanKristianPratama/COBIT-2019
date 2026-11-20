<div class="c5-tab-sheet bg-white border border-secondary-subtle rounded-bottom overflow-hidden">
    <!-- Header Titles matching Excel Sheet 3 -->
    <div class="px-4 py-3 bg-light border-bottom">
        <h2 class="h6 fw-bold text-dark mb-1 text-uppercase" style="letter-spacing: 0.5px;">Hierarchy of COBIT Processes to Achieve IT and Business Goals</h2>
        <p class="text-secondary small mb-0">Based on mapping in ISACA's COBIT process capability assessment model</p>
    </div>

    <!-- Toolbar (Clean Typography, No Icons, No Collapse Toggle) -->
    <div class="p-3 bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 360px;">
            <input type="text" class="form-control form-control-sm text-dark" 
                   placeholder="Cari ITRG atau Enterprise Goal..." 
                   x-model="searchQuery">
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-dark"
                    @click="setAllScores(1)" title="Set semua nilai ITRG ke 1">
                Set Semua 1
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark"
                    @click="setAllScores(0)" title="Set semua nilai ITRG ke 0">
                Set Semua 0
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark"
                    @click="resetToDefault()" :disabled="saving">
                Reset
            </button>
            <a href="{{ route('cobit5.scope.export') }}" class="btn btn-sm btn-outline-dark">
                Export CSV
            </a>
            @can('design-factors.input')
                <button type="button" class="btn btn-sm btn-dark px-3"
                        @click="saveChanges()" :disabled="saving">
                    <span x-show="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    <span x-text="saving ? 'Menyimpan...' : 'Simpan Nilai'">Simpan Nilai</span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Excel-like Table with Exact Sizing -->
    <div class="c5-table-container">
        <table class="c5-grid-table">
            <colgroup>
                <col style="width: 15%;">
                <col style="width: 27%;">
                <col style="width: 35%;">
                <col style="width: 8%;">
                <col style="width: 7.5%;">
                <col style="width: 7.5%;">
            </colgroup>
            <thead>
                <tr class="c5-grid-th">
                    <th class="ps-3 text-start">Balanced Scorecard</th>
                    <th class="ps-3 text-start">Enterprise Goal</th>
                    <th class="ps-3 text-start">IT-related Goal (ITRG)</th>
                    <th class="text-center">COBIT Process</th>
                    <th class="text-center">Number of Enterprise Goals</th>
                    <th class="text-center">Number of IT-related Goals</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dimensions as $dimKey => $dim)
                    <!-- DIMENSION ROW (e.g. Financial) -->
                    <tr class="c5-dim-row">
                        <td class="ps-3 py-2 fw-bold text-dark text-nowrap">
                            {{ $dim['dimension'] }}
                        </td>
                        <td class="bg-cell-empty"></td>
                        <td class="bg-cell-empty"></td>
                        <td class="bg-cell-empty"></td>
                        <td class="text-center fw-bold text-dark fs-6" x-text="calculateDimEgCount('{{ $dimKey }}')">
                            {{ $dim['eg_count'] ?? 0 }}
                        </td>
                        <td class="text-center fw-bold text-dark fs-6" x-text="calculateDimTotalScore('{{ $dimKey }}')">
                            {{ $dim['total_score'] ?? 0 }}
                        </td>
                    </tr>

                    @foreach($dim['enterprise_goals'] ?? [] as $egIdx => $eg)
                        <!-- ENTERPRISE GOAL ROW (Title ONLY at initial row) -->
                        <tr class="c5-eg-row" x-show="matchesEgRow({{ json_encode($eg) }})">
                            <td class="bg-cell-empty"></td>
                            <td class="ps-3 py-2">
                                <span class="fw-semibold text-dark" style="font-size: 0.88rem;">
                                    {{ $eg['code'] }}. {{ $eg['title'] }}
                                </span>
                            </td>
                            <td class="bg-cell-empty"></td>
                            <td class="bg-cell-empty"></td>
                            <td class="text-center p-1">
                                <div class="d-flex justify-content-center">
                                    <input type="number" min="0" max="99" 
                                           class="c5-input-box" 
                                           x-model.number="dimensions['{{ $dimKey }}'].enterprise_goals[{{ $egIdx }}].weight"
                                           @input="recalc()"
                                           title="Bobot Enterprise Goal">
                                </div>
                            </td>
                            <td class="text-center fw-bold text-dark" 
                                x-text="calculateEgSubtotal(dimensions['{{ $dimKey }}'].enterprise_goals[{{ $egIdx }}])">
                                {{ $eg['subtotal'] ?? 0 }}
                            </td>
                        </tr>

                        @foreach($eg['itrgs'] ?? [] as $itrgIdx => $itrg)
                            <!-- ITRG ROW (Enterprise Goal column is EMPTY, Weight column is EMPTY) -->
                            <tr class="c5-itrg-row" 
                                x-show="matchesItrgRow('{{ $itrg['code'] }}', '{{ addslashes($itrg['title']) }}', {{ json_encode($eg) }})">
                                <td class="bg-cell-empty"></td>
                                <td class="bg-cell-empty"></td>
                                <td class="ps-3 py-2">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <a href="#it-hierarchy" 
                                           @click.prevent="goToItrg('{{ $itrg['code'] }}')" 
                                           class="text-decoration-none text-dark fw-medium c5-itrg-link" 
                                           title="Klik untuk melihat COBIT Processes di Tab 2 (IT-related Goals Hierarchy)">
                                            <span class="badge border bg-white text-dark me-1" style="font-size: 0.72rem;">{{ $itrg['code'] }}</span>
                                            <span style="font-size: 0.85rem;">{{ $itrg['title'] }}</span>
                                        </a>
                                        <span class="badge {{ ($itrg['level'] ?? '') === 'P' ? 'bg-dark text-white' : 'bg-white text-dark border' }}" 
                                              style="font-size: 0.68rem; font-weight: 600;">
                                            {{ ($itrg['level'] ?? '') === 'P' ? 'Primary' : 'Secondary' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center py-1">
                                    <button type="button" 
                                            @click.prevent="goToItrg('{{ $itrg['code'] }}')"
                                            class="btn btn-outline-dark btn-xs px-2 py-0" 
                                            style="font-size: 0.72rem;"
                                            title="Lihat proses terkait di Tab 2">
                                        Detail &rarr;
                                    </button>
                                </td>
                                <td class="bg-cell-empty"></td>
                                <td class="text-center p-1">
                                    <div class="d-flex justify-content-center">
                                        <input type="number" min="0" max="99" 
                                               class="c5-input-box" 
                                               x-model.number="dimensions['{{ $dimKey }}'].enterprise_goals[{{ $egIdx }}].itrgs[{{ $itrgIdx }}].score"
                                               @input="recalc()"
                                               title="Nilai input ITRG (misal: 1 atau 0)">
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="c5-grid-tfoot">
                    <th colspan="4" class="ps-3 py-3 text-uppercase fw-bold text-dark">
                        Grand Total Assessment Scope
                    </th>
                    <th class="text-center fw-bold fs-6 text-dark py-3" x-text="grandTotalWeight">{{ $totalEgWeight }}</th>
                    <th class="text-center fw-bold fs-6 text-dark py-3" x-text="grandTotalScore">{{ $totalItrgScore }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
