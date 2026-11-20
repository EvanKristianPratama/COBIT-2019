<div class="c5-tab-sheet bg-white border border-secondary-subtle rounded-bottom overflow-hidden">
    <!-- Header Legend / Guidance matching Excel Sheet 5 -->
    <div class="px-4 py-3 bg-light border-bottom">
        <h2 class="h6 fw-bold text-dark mb-2 text-uppercase" style="letter-spacing: 0.5px;">COBIT 5 Processes Self-Diagnostic</h2>
        <div class="row g-2 text-dark" style="font-size: 0.84rem; line-height: 1.5;">
            <div class="col-md-6">
                <div><strong>Importance</strong> = How important it is for the enterprise on a scale from 1 (not at all) to 5 (very)</div>
                <div><strong>Performance</strong> = How well it is done from 1 (do not know or badly) to 5 (very well)</div>
            </div>
            <div class="col-md-6">
                <div><strong>Formality</strong> = Existence of a contract, an SLA or a clearly documented procedure (Yes, No or ?)</div>
                <div><strong>Audited</strong> = Yes, No or ?</div>
                <div><strong>Accountable</strong> = Name or &lsquo;do not know&rsquo;</div>
            </div>
        </div>
    </div>

    <!-- Toolbar (Clean Typography, No Icons) -->
    <div class="p-3 bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
            <input type="text" class="form-control form-control-sm text-dark" 
                   placeholder="Cari Process ID atau Nama Proses..." 
                   x-model="diagnosticSearch">
        </div>

        <div class="d-flex align-items-center gap-2">
            @can('design-factors.input')
                <button type="button" class="btn btn-sm btn-dark px-3"
                        @click="saveChanges()" :disabled="saving">
                    <span x-show="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    <span x-text="saving ? 'Menyimpan...' : 'Simpan Self-Diagnostic'">Simpan Self-Diagnostic</span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Table with Exact Sizing -->
    <div class="c5-table-container">
        <table class="c5-grid-table">
            <colgroup>
                <col style="width: 9%;">
                <col style="width: 37%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 16%;">
            </colgroup>
            <thead>
                <tr class="c5-grid-th">
                    <th class="text-center">Process ID</th>
                    <th class="ps-3 text-start">Processes for Governance of Enterprise IT</th>
                    <th class="text-center">Importance<br><small class="fw-normal text-muted">(1 - 5)</small></th>
                    <th class="text-center">Performance<br><small class="fw-normal text-muted">(1 - 5)</small></th>
                    <th class="text-center">Formality<br><small class="fw-normal text-muted">(Yes / No / ?)</small></th>
                    <th class="text-center">Audited<br><small class="fw-normal text-muted">(Yes / No / ?)</small></th>
                    <th class="ps-3 text-start">Who is accountable?</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selfDiagnostic as $dIdx => $dom)
                    <!-- DOMAIN HEADER ROW -->
                    <tr class="c5-dim-row" x-show="matchesDiagnosticDomain('{{ addslashes($dom['name']) }}')">
                        <td colspan="7" class="ps-3 py-2 fw-bold text-dark fs-6 text-uppercase" style="letter-spacing: 0.5px;">
                            {{ $dom['name'] }}
                        </td>
                    </tr>

                    @foreach($dom['processes'] ?? [] as $pIdx => $proc)
                        <!-- PROCESS ROW -->
                        <tr class="c5-itrg-row" x-show="matchesDiagnosticItem('{{ $proc['code'] }}', '{{ addslashes($proc['title']) }}', '{{ addslashes($dom['name']) }}')">
                            <!-- Process ID -->
                            <td class="text-center fw-bold text-dark py-2">
                                <span class="badge border bg-white text-dark" style="font-size: 0.75rem;">{{ $proc['code'] }}</span>
                            </td>
                            <!-- Process Name -->
                            <td class="ps-3 py-2">
                                <span class="fw-medium text-dark" style="font-size: 0.88rem;">{{ $proc['title'] }}</span>
                            </td>
                            <!-- Importance (1 - 5) -->
                            <td class="text-center p-1">
                                <div class="d-flex justify-content-center">
                                    <select class="form-select form-select-sm c5-select-box" 
                                            x-model="diagnosticDomains[{{ $dIdx }}].processes[{{ $pIdx }}].importance" 
                                            title="Importance (1-5)">
                                        <option value="">-</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </td>
                            <!-- Performance (1 - 5) -->
                            <td class="text-center p-1">
                                <div class="d-flex justify-content-center">
                                    <select class="form-select form-select-sm c5-select-box" 
                                            x-model="diagnosticDomains[{{ $dIdx }}].processes[{{ $pIdx }}].performance" 
                                            title="Performance (1-5)">
                                        <option value="">-</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </td>
                            <!-- Formality (Yes / No / ?) -->
                            <td class="text-center p-1">
                                <div class="d-flex justify-content-center">
                                    <select class="form-select form-select-sm c5-select-box" 
                                            x-model="diagnosticDomains[{{ $dIdx }}].processes[{{ $pIdx }}].formality" 
                                            title="Formality (Yes / No / ?)">
                                        <option value="">-</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="?">?</option>
                                    </select>
                                </div>
                            </td>
                            <!-- Audited (Yes / No / ?) -->
                            <td class="text-center p-1">
                                <div class="d-flex justify-content-center">
                                    <select class="form-select form-select-sm c5-select-box" 
                                            x-model="diagnosticDomains[{{ $dIdx }}].processes[{{ $pIdx }}].audited" 
                                            title="Audited (Yes / No / ?)">
                                        <option value="">-</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="?">?</option>
                                    </select>
                                </div>
                            </td>
                            <!-- Who is accountable? -->
                            <td class="p-1">
                                <input type="text" 
                                       class="form-control form-control-sm c5-text-box" 
                                       placeholder="Name or role..." 
                                       x-model="diagnosticDomains[{{ $dIdx }}].processes[{{ $pIdx }}].accountable">
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
