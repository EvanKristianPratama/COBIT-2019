@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
    @include('cobit2019.cobitPagination')

    <div class="container">
         {{-- Admin raw submissions (generic) --}}
    {{-- Admin raw submissions removed per request --}}
        <!-- Card Utama -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow border-0 rounded">
                    <!-- Card Header -->
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">Design Factor 4 - I&T-Related issues</h4>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body p-4">
                        <form id="df4Form" action="{{ route('df4.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="df_id" value="{{ $id }}">

                            @php
                                $historyValues = [];
                                if (!empty($historyInputs) && is_array($historyInputs)) {
                                    $historyValues = $historyInputs;
                                }
                            @endphp

                            @php
                                $labels = [
                                    "Frustration between different IT entities across the organization because of a perception of low contribution to business value",
                                    "Frustration between business departments (i.e., the IT customer) and the IT department because of failed initiatives or a perception of low contribution to business value",
                                    "Significant IT-related incidents, such as data loss, security breaches, project failure and application errors, linked to IT",
                                    "Service delivery problems by the IT outsourcer(s)",
                                    "Failures to meet IT-related regulatory or contractual requirements",
                                    "Regular audit findings or other assessment reports about poor IT performance or reported IT quality or service problems",
                                    "Substantial hidden and rogue IT spending, that is, IT spending by user departments outside the control of the normal IT investment decision mechanisms and approved budgets",
                                    "Duplications or overlaps between various initiatives, or other forms of wasted resources",
                                    "Insufficient IT resources, staff with inadequate skills or staff burnout/dissatisfaction",
                                    "IT-enabled changes or projects frequently failing to meet business needs and delivered late or over budget",
                                    "Reluctance by board members, executives or senior management to engage with IT, or a lack of committed business sponsorship for IT",
                                    "Complex IT operating model and/or unclear decision mechanisms for IT-related decisions",
                                    "Excessively high cost of IT",
                                    "Obstructed or failed implementation of new initiatives or innovations caused by the current IT architecture and systems",
                                    "Gap between business and technical knowledge, which leads to business users and information and/or technology specialists speaking different languages",
                                    "Regular issues with data quality and integration of data across various sources",
                                    "High level of end-user computing, creating (among other problems) a lack of oversight and quality control over the applications that are being developed and put in operation",
                                    "Business departments implementing their own information solutions with little or no involvement of the enterprise IT department (related to end-user computing, which often stems from dissatisfaction with IT solutions and services)",
                                    "Ignorance of and/or noncompliance with privacy regulations",
                                    "Inability to exploit new technologies or innovate using I&T"
                                ];
                            @endphp

                            <!-- Tabel Assessment DF4 -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <!-- Caption dengan keterangan warna/ikon -->
                                    <caption class="small caption-top">
                                        <strong>Importance :</strong>
                                        <table class="table table-sm table-borderless mt-2">
                                            <tbody>
                                                <tr>

                                                    <td class="align-middle">
                                                        <span class="d-inline-block rounded-circle bg-danger-subtle"
                                                            style="width:14px; height:14px; margin-right:6px;"></span>
                                                        Serious Issue
                                                    </td>

                                                    <!-- (oranye subtle) -->
                                                    <td class="align-middle">
                                                        <span class="d-inline-block rounded-circle bg-warning-subtle"
                                                            style="width:14px; height:14px; margin-right:6px;"></span>
                                                        Issue
                                                    </td>

                                                    <!--  -->
                                                    <td class="align-middle">
                                                        <span class="d-inline-block rounded-circle bg-success-subtle"
                                                            style="width:14px; height:14px; margin-right:6px;"></span>
                                                        No Issue
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </caption>


                                    <thead class="table-success">
                                        <tr>
                                            <th scope="col">IT-Related Issue</th>
                                            <th scope="col" class="text-center" style="width: 20%;">Importance (1-3)</th>
                                            <th scope="col" class="text-center">Baseline</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($labels as $index => $label)
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td>
                                                    @php $inputName = 'input' . ($index + 1) . 'df4'; @endphp
                                                    <select class="form-select input-score" name="{{ $inputName }}" id="{{ $inputName }}"
                                                        required>
                                                        <option value="" selected disabled>Pilih</option>
                                                        <option value="1" {{ (old($inputName, $historyValues[$inputName] ?? null) == 1) ? 'selected' : '' }}>1</option>
                                                        <option value="2" {{ (old($inputName, $historyValues[$inputName] ?? null) == 2) ? 'selected' : '' }}>2</option>
                                                        <option value="3" {{ (old($inputName, $historyValues[$inputName] ?? null) == 3) ? 'selected' : '' }}>3</option>
                                                    </select>
                                                </td>
                                                <td class="text-center fw-bold text-success fs-5">
                                                    2
                                                    <input type="hidden" name="baseline_{{ $index + 1 }}df4" value="3">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                                <!-- Submit Button -->
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Save</button>
                                </div>
                            </div>



                            <!-- DF4 Score Bar Chart (Input Score) -->
                            <div class="row mb-4 mt-4">
                                <div class="col-md">
                                    <div class="card">
                                        <div class="card-header text-center text-primary">
                                            DF4 Bar Chart
                                        </div>
                                        <div class="card-body" style="height: 400px;">
                                            <canvas id="inputBarChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Layout: Relative Importance -->
                            <div class="row mt-4">
                                <!-- Relative Importance Radar Chart -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header text-center text-primary">
                                            Relative Importance (Radar Chart)
                                        </div>
                                        <div class="card-body">
                                            <div class="w-100" style="height: 400px;">
                                                <canvas id="relativeImportanceRadarChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Relative Importance Table -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header text-center text-primary">
                                            Relative Importance Table
                                        </div>
                                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-bordered table-sm" id="results-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-center text-primary">Index</th>
                                                        <th class="text-center text-primary">DF4 Score</th>
                                                        <th class="text-center text-primary">Relative Importance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data akan diisi oleh JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Relative Importance Bar Chart -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header text-center text-primary">
                                            Relative Importance (Bar Chart)
                                        </div>
                                        <div class="card-body">
                                            <div class="w-100" style="height: 700px;">
                                                <canvas id="relativeImportanceChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </form>
                    </div>
                    <!-- end card-body -->
                </div>
            </div>
        </div>
    </div>

    <!-- Sertakan Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Definisikan label untuk DF4 Score Chart
            const df4Labels = [
                "Frustration between different IT entities across the organization because of a perception of low contribution to business value",
                "Frustration between business departments (i.e., the IT customer) and the IT department because of failed initiatives or a perception of low contribution to business value",
                "Significant IT-related incidents, such as data loss, security breaches, project failure and application errors, linked to IT",
                "Service delivery problems by the IT outsourcer(s)",
                "Failures to meet IT-related regulatory or contractual requirements",
                "Regular audit findings or other assessment reports about poor IT performance or reported IT quality or service problems",
                "Substantial hidden and rogue IT spending, that is, IT spending by user departments outside the control of the normal IT investment decision mechanisms and approved budgets",
                "Duplications or overlaps between various initiatives, or other forms of wasted resources",
                "Insufficient IT resources, staff with inadequate skills or staff burnout/dissatisfaction",
                "IT-enabled changes or projects frequently failing to meet business needs and delivered late or over budget",
                "Reluctance by board members, executives or senior management to engage with IT, or a lack of committed business sponsorship for IT",
                "Complex IT operating model and/or unclear decision mechanisms for IT-related decisions",
                "Excessively high cost of IT",
                "Obstructed or failed implementation of new initiatives or innovations caused by the current IT architecture and systems",
                "Gap between business and technical knowledge, which leads to business users and information and/or technology specialists speaking different languages",
                "Regular issues with data quality and integration of data across various sources",
                "High level of end-user computing, creating (among other problems) a lack of oversight and quality control over the applications that are being developed and put in operation",
                "Business departments implementing their own information solutions with little or no involvement of the enterprise IT department (related to end-user computing, which often stems from dissatisfaction with IT solutions and services)",
                "Ignorance of and/or noncompliance with privacy regulations",
                "Inability to exploit new technologies or innovate using I&T"
            ];

            // history / draft support
            const historyInputs = @json($historyInputs ?? null);
            const historyScoreData = @json($historyScoreArray ?? null);
            const historyRIData = @json($historyRIArray ?? null);
            const ASSESSMENT_ID = @json(session('assessment_id') ?? null);
            const DF_ID = @json($id);
            const draftKey = `df4:${ASSESSMENT_ID}:${DF_ID}`;
            // Note: history/draft will be applied after charts are initialized

            // save draft on change
            document.querySelectorAll('select.input-score').forEach(sel => {
                sel.addEventListener('change', () => {
                    const state = {};
                    for (let i = 1; i <= df4Labels.length; i++) {
                        const key = 'input' + i + 'df4';
                        const el = document.getElementById(key);
                        if (el) state[key] = el.value || '';
                    }
                    try { localStorage.setItem(draftKey, JSON.stringify(state)); } catch (e) {}
                });
            });


            // --- Konstanta Perhitungan ---
            const DF4_MAP = [
                [3.0, 3.0, 1.0, 1.0, 2.0, 2.0, 2.0, 1.0, 1.0, 1.0, 3.0, 3.5, 1.0, 1.0, 1.0, 1.0, 2.0, 3.0, 1.5, 1.0],
                [2.5, 3.0, 1.0, 1.0, 1.5, 2.5, 2.0, 1.5, 0.5, 2.5, 1.5, 1.0, 3.0, 2.0, 1.0, 1.0, 2.0, 2.0, 1.0, 2.5],
                [1.0, 1.0, 2.0, 1.0, 2.0, 2.0, 1.0, 1.0, 0.0, 0.5, 1.0, 0.0, 1.0, 1.5, 1.0, 2.0, 1.0, 1.0, 2.5, 1.0],
                [1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 3.0, 3.5, 3.5, 1.0, 1.5, 0.0, 4.0, 2.0, 1.0, 1.5, 2.0, 2.5, 0.0, 1.0],
                [1.0, 1.0, 1.0, 1.0, 1.5, 2.0, 1.0, 1.0, 0.0, 1.0, 3.0, 1.5, 1.5, 0.5, 0.0, 0.5, 1.0, 1.0, 1.0, 0.0],
                [2.0, 1.0, 2.0, 1.0, 2.0, 2.0, 1.0, 1.0, 0.0, 0.5, 1.5, 4.0, 1.0, 2.0, 1.0, 1.0, 1.5, 2.0, 0.5, 1.0],
                [1.5, 1.5, 1.5, 1.5, 1.0, 1.5, 1.0, 1.0, 0.0, 1.0, 2.5, 0.5, 0.5, 1.5, 1.5, 0.5, 2.0, 2.0, 0.0, 2.5],
                [1.0, 1.5, 1.0, 2.0, 0.5, 1.5, 2.0, 1.5, 1.0, 3.5, 0.5, 0.5, 1.0, 4.0, 1.0, 3.5, 2.0, 3.0, 0.0, 2.0],
                [1.0, 1.0, 1.0, 1.0, 0.5, 0.5, 0.5, 0.5, 0.0, 0.0, 0.5, 1.0, 0.5, 2.0, 1.0, 0.0, 0.5, 0.5, 0.0, 4.0],
                [3.0, 3.0, 1.0, 1.5, 2.0, 2.0, 1.5, 3.5, 0.5, 2.0, 2.0, 1.5, 2.0, 1.0, 0.5, 0.0, 2.5, 2.5, 0.0, 2.0],
                [3.5, 2.0, 1.0, 1.5, 1.5, 2.0, 4.0, 3.0, 1.0, 2.0, 1.0, 1.5, 4.0, 0.0, 0.0, 0.0, 1.0, 2.0, 0.0, 0.0],
                [1.5, 1.0, 1.0, 1.0, 1.0, 1.5, 2.0, 2.0, 4.0, 1.0, 0.0, 0.0, 1.0, 0.0, 3.0, 0.0, 0.5, 0.5, 1.5, 1.0],
                [2.5, 2.0, 1.0, 2.5, 1.5, 1.0, 2.5, 2.0, 1.5, 1.0, 3.0, 1.0, 0.5, 1.0, 4.0, 1.0, 3.0, 3.5, 0.0, 0.5],
                [2.0, 1.5, 2.0, 4.0, 1.0, 2.5, 1.5, 2.0, 0.5, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0, 1.0, 1.5, 0.0, 0.0],
                [1.0, 1.0, 2.0, 4.0, 1.5, 1.5, 1.5, 0.0, 1.5, 1.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0, 0.5, 2.0, 1.0, 0.0],
                [1.0, 1.0, 3.0, 1.5, 1.0, 3.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.5, 0.5, 3.0, 2.0, 2.0, 0.0, 1.0],
                [1.0, 0.5, 2.5, 1.5, 2.0, 2.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 1.5, 2.5, 1.0],
                [0.0, 0.0, 3.5, 1.0, 2.0, 1.0, 0.0, 1.0, 0.0, 0.5, 0.0, 0.0, 0.0, 0.0, 0.0, 1.5, 2.0, 1.0, 2.0, 1.0],
                [1.0, 1.5, 3.0, 1.0, 2.5, 1.5, 1.0, 1.5, 0.0, 1.5, 0.0, 0.0, 0.5, 2.5, 0.5, 4.0, 2.5, 2.0, 3.0, 0.5],
                [0.0, 1.0, 1.5, 0.0, 0.0, 0.0, 0.0, 3.0, 1.0, 3.5, 0.0, 0.0, 1.5, 0.5, 1.0, 0.0, 1.5, 2.0, 0.0, 1.0],
                [0.0, 3.0, 0.0, 0.0, 0.5, 2.0, 0.0, 2.0, 0.0, 3.5, 0.0, 1.0, 1.0, 2.0, 2.0, 1.5, 2.5, 3.0, 0.5, 1.0],
                [1.0, 2.0, 2.0, 0.0, 0.0, 2.0, 0.0, 1.0, 0.0, 3.0, 0.0, 0.5, 1.0, 1.0, 1.0, 0.5, 2.0, 2.0, 1.0, 0.5],
                [0.5, 0.0, 2.0, 3.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.5, 0.0, 0.0, 1.0, 1.0, 1.0, 0.0, 0.5],
                [1.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.5, 0.0, 3.0, 1.0, 0.0, 0.0, 0.5, 2.0, 0.0, 0.5, 1.5, 0.0, 1.0],
                [0.0, 0.0, 2.5, 3.0, 0.5, 1.5, 0.0, 1.0, 0.0, 1.5, 0.0, 1.0, 0.5, 1.0, 0.5, 2.0, 2.0, 2.0, 1.0, 1.0],
                [0.0, 1.0, 2.0, 2.0, 0.5, 1.5, 0.0, 0.5, 0.0, 2.0, 0.0, 1.0, 0.0, 1.0, 0.5, 2.0, 2.0, 2.0, 0.0, 1.0],
                [0.0, 0.0, 0.0, 1.5, 0.5, 0.5, 0.0, 1.0, 2.0, 0.5, 0.0, 0.5, 0.0, 1.0, 3.0, 2.0, 1.0, 1.5, 0.0, 0.5],
                [0.5, 0.5, 1.0, 0.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0, 1.0, 0.0, 0.0, 1.0, 1.5, 0.0, 0.0],
                [0.0, 0.0, 2.5, 2.0, 0.5, 0.0, 0.0, 0.5, 0.0, 0.0, 0.0, 0.0, 1.0, 1.5, 0.0, 1.5, 1.0, 2.0, 0.0, 0.0],
                [1.0, 2.0, 2.5, 0.0, 0.0, 0.0, 2.0, 3.0, 1.0, 4.0, 0.0, 0.0, 1.5, 2.0, 0.5, 0.0, 1.0, 1.5, 0.0, 0.5],
                [0.0, 0.0, 2.5, 2.0, 1.0, 2.0, 0.0, 0.5, 0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.5, 1.0, 2.0, 0.0, 0.0],
                [1.0, 1.0, 4.0, 3.0, 1.0, 2.5, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 1.0, 1.0, 1.0, 0.0, 0.0],
                [0.0, 1.0, 3.0, 3.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 1.5, 1.0, 1.0, 1.0, 0.5, 0.0],
                [0.0, 0.0, 3.0, 1.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.5, 1.0, 2.0, 0.0, 0.0],
                [0.0, 0.0, 4.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.5, 1.0, 2.0, 2.0, 0.0],
                [0.0, 1.0, 0.5, 0.0, 3.0, 0.5, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0, 0.0, 1.5, 2.5, 1.5, 1.0, 2.0, 0.0],
                [1.0, 1.5, 2.0, 2.0, 2.5, 3.0, 1.0, 2.0, 1.5, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 1.0, 1.5, 1.0, 2.5, 1.0],
                [0.0, 0.0, 2.0, 2.0, 2.5, 2.0, 2.0, 0.0, 0.5, 2.0, 1.0, 1.0, 1.5, 1.0, 0.0, 2.0, 1.0, 1.0, 2.5, 0.0],
                [0.0, 0.0, 2.0, 2.0, 4.0, 0.5, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 4.0, 0.0],
                [1.0, 1.0, 3.0, 1.5, 3.0, 4.0, 2.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.5, 0.0, 1.0, 1.0, 1.0, 1.0, 2.5, 1.0]
            ];

            const DF4_BASELINE = [
                [2], [2], [2], [2], [2], [2], [2], [2], [2], [2],
                [2], [2], [2], [2], [2], [2], [2], [2], [2], [2]
            ];

            const DF4_SC_BASELINE = [
                [70], [70], [47], [67], [41], [56], [50], [66], [32], [68],
                [62], [47], [70], [43], [39], [43], [52], [33], [60], [35],
                [51], [41], [23], [28], [42], [38], [31], [23], [25], [45],
                [27], [33], [32], [21], [29], [29], [61], [48], [29], [58]
            ];

            // ===============================================================
            // Fungsi pembulatan ke kelipatan tertentu
            // ===============================================================
            function mround(value, multiple) {
                if (multiple === 0) return 0;
                return Math.round(value / multiple) * multiple;
            }

            // ===============================================================
            // Fungsi untuk menghitung DF4_SCORE dan DF4_RELATIVE_IMP
            // ===============================================================
            function updateRelativeImportance() {
                // ---------------------------------------------------------------
                // 1. Ambil nilai input dari form untuk DF4 dan konversi ke angka
                // Loop melalui setiap label DF4 (misalnya, jumlah input sesuai dengan df4Labels)
                // Gunakan tag select untuk mengambil nilai masing-masing input
                // ---------------------------------------------------------------
                const DF4_INPUT = [];
                for (let i = 0; i < df4Labels.length; i++) {
                    const selected = document.querySelector('select[name="input' + (i + 1) + 'df4"]');
                    // Jika ada nilai, konversi ke angka, jika tidak, gunakan 0
                    DF4_INPUT.push([selected && selected.value ? parseFloat(selected.value) : 2]);
                }

                // ---------------------------------------------------------------
                // 2. Hitung rata-rata input dan baseline
                // ---------------------------------------------------------------
                const DF4_INPUT_flat = DF4_INPUT.flat();
                const DF4_INP_AVRG = DF4_INPUT_flat.reduce((sum, num) => sum + num, 0) / DF4_INPUT_flat.length;

                // Pastikan DF4_BASELINE sudah didefinisikan (misalnya sebagai array dua dimensi)
                const DF4_BASELINE_flat = DF4_BASELINE.flat();
                const DF4_BASELINE_AVERAGE = DF4_BASELINE_flat.reduce((sum, num) => sum + num, 0) / DF4_BASELINE_flat.length;

                // Perbandingan rata-rata baseline dengan rata-rata input
                const DF4_IN_BS_AVRG = DF4_BASELINE_AVERAGE / DF4_INP_AVRG;

                // ---------------------------------------------------------------
                // 3. Hitung DF4_SCORE dengan perkalian matriks
                // Gunakan DF4_MAP (matriks dua dimensi yang sudah didefinisikan secara global)
                // Setiap baris pada DF4_MAP dikalikan dengan nilai input yang bersesuaian dan dijumlahkan
                // ---------------------------------------------------------------
                const DF4_SC = DF4_MAP.map((row, i) => {
                    let score = 0;
                    for (let j = 0; j < DF4_INPUT.length; j++) {
                        score += row[j] * DF4_INPUT[j][0];
                    }
                    return score;
                });

                // ---------------------------------------------------------------
                // 4. Hitung DF4_RELATIVE_IMP
                // Untuk setiap skor, bandingkan dengan baseline score yang ada pada DF4_SC_BASELINE
                // Rumus: (DF4_IN_BS_AVRG * 100 * score / baseline) dibulatkan ke kelipatan 5, lalu dikurangi 100
                // ---------------------------------------------------------------
                const DF4_RELATIVE_IMP = DF4_SC.map((score, i) => {
                    if (DF4_SC_BASELINE[i] && DF4_SC_BASELINE[i][0] !== 0) {
                        const calculation = (DF4_IN_BS_AVRG * 100 * score) / DF4_SC_BASELINE[i][0];
                        return mround(calculation, 5) - 100;
                    } else {
                        return 0;
                    }
                });

                // ---------------------------------------------------------------
                // 5. Kembalikan objek yang berisi DF4_SCORE dan DF4_RELATIVE_IMP
                // ---------------------------------------------------------------
                return { score: DF4_SC, relative: DF4_RELATIVE_IMP };
            }


            // -------------------- Fungsi Update Tabel Relative Importance --------------------
            // Fungsi ini akan menggunakan hasil perhitungan DF4 (DF4_SC) dan DF4_RELATIVE_IMP
            function updateRelativeTable(DF4_SCORE, DF4_RELATIVE_IMP) {
                const tbody = document.querySelector('#results-table tbody');
                tbody.innerHTML = Array.from({ length: 40 }, (_, i) => {
                    const score = DF4_SCORE[i] ?? 0;
                    const relative = DF4_RELATIVE_IMP[i] ?? 0;
                    const scoreClass = relative > 0
                        ? 'bg-primary-subtle text-dark'
                        : relative < 0
                            ? 'bg-danger-subtle text-dark'
                            : '';
                    return `<tr>
                                      <td class="text-center">${i + 1}</td>
                                      <td class="text-center">${score.toFixed(2)}</td>
                                      <td class="text-center ${scoreClass}">${relative}</td>
                                    </tr>`;
                }).join('');
            }

            // -------------------- Inisialisasi DF4 Score Bar Chart (Input Score) --------------------
            let scores = new Array(df4Labels.length).fill(0);
            const scoreChartCtx = document.getElementById('inputBarChart').getContext('2d');
            const df4ScoreChart = new Chart(scoreChartCtx, {
                type: 'bar',
                data: {
                    labels: df4Labels,
                    datasets: [{
                        label: 'DF4 Score',
                        data: scores,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            min: 0,
                            max: 3,
                            ticks: { stepSize: 1 }
                        },
                        y: {
                            ticks: { font: { size: 12 }, autoSkip: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    }
                }
            });

            // -------------------- Inisialisasi Relative Importance Charts --------------------
            const relativeImportanceLabels = [
                'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
                'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
                'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
                'APO11', 'APO12', 'APO13', 'APO14',
                'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
                'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
                'MEA01', 'MEA02', 'MEA03', 'MEA04'
            ];
            let relImpResult = updateRelativeImportance();
            let relImpData = relImpResult.relative;

            // Update tabel Relative Importance (menggunakan hasil perhitungan DF4 Score)
            updateRelativeTable(relImpResult.score, relImpData);

            // Relative Importance Bar Chart
            const relBarCtx = document.getElementById('relativeImportanceChart').getContext('2d');
            const relativeBarChart = new Chart(relBarCtx, {
                type: 'bar',
                data: {
                    labels: relativeImportanceLabels,
                    datasets: [{
                        label: 'Relative Importance Score',
                        data: relImpData,
                        backgroundColor: relImpData.map(value =>
                            value > 0 ? 'rgba(54, 162, 235, 0.6)' :
                                value < 0 ? 'rgba(255, 99, 132, 0.6)' :
                                    'rgba(201, 201, 201, 0.6)'
                        ),
                        borderColor: relImpData.map(value =>
                            value > 0 ? 'rgba(54, 162, 235, 1)' :
                                value < 0 ? 'rgba(255, 99, 132, 1)' :
                                    'rgba(201, 201, 201, 1)'
                        ),
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            max: 100,
                            min: -100,
                            beginAtZero: true,
                            ticks: { stepSize: 20 },
                            grid: {
                                color: ctx => ctx.tick.value === 0 ? 'rgba(0, 0, 0, 0.3)' : 'rgba(200, 200, 200, 0.3)',
                                lineWidth: ctx => ctx.tick.value === 0 ? 2 : 1
                            }
                        },
                        y: { ticks: { autoSkip: false, maxTicksLimit: 40 } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    let label = ctx.dataset.label || '';
                                    if (label) label += ': ';
                                    label += ctx.raw >= 0 ? '+' + ctx.raw : ctx.raw;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Relative Importance Radar Chart (data dibalik)
            const reversedLabels = [...relativeImportanceLabels].reverse();
            const reversedData = [...relImpData].reverse();
            const relRadarCtx = document.getElementById('relativeImportanceRadarChart').getContext('2d');
            const relativeRadarChart = new Chart(relRadarCtx, {
                type: 'radar',
                data: {
                    labels: reversedLabels,
                    datasets: [{
                        label: 'Relative Importance',
                        data: reversedData,
                        backgroundColor: 'rgba(235, 54, 54, 0.2)',
                        borderColor: reversedData.map(value =>
                            value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                        ),
                        borderWidth: 2,
                        pointBackgroundColor: reversedData.map(value =>
                            value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                        ),
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: reversedData.map(value =>
                            value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                        ),
                        tension: 0.4
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            suggestedMin: -100,
                            suggestedMax: 100,
                            ticks: { stepSize: 25 },
                            pointLabels: { font: { size: 10 } },
                            angleLines: { color: 'rgba(200, 200, 200, 0.3)' },
                            grid: { color: 'rgba(200, 200, 200, 0.3)' }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    let lbl = ctx.dataset.label || '';
                                    if (lbl) lbl += ': ';
                                    lbl += ctx.raw >= 0 ? '+' + ctx.raw : ctx.raw;
                                    return lbl;
                                }
                            }
                        }
                    }
                }
            });

            // Apply server history or local draft now that charts and helper functions are ready
            (function applyHistoryAndCompute() {
                try {
                    // helper to set color class on a select input according to its value
                    function applyColorClassToSelect(el) {
                        if (!el) return;
                        el.classList.remove('bg-success-subtle', 'bg-warning-subtle', 'bg-danger-subtle');
                        const v = String(el.value);
                        if (v === '1') el.classList.add('bg-success-subtle');
                        else if (v === '2') el.classList.add('bg-warning-subtle');
                        else if (v === '3') el.classList.add('bg-danger-subtle');
                    }
                    const raw = localStorage.getItem(draftKey);
                    if (!historyInputs && raw) {
                        const draft = JSON.parse(raw);
                        if (draft && typeof draft === 'object') {
                            for (let i = 1; i <= df4Labels.length; i++) {
                                const key = 'input' + i + 'df4';
                                const el = document.getElementById(key);
                                if (el && draft[key] !== undefined) el.value = draft[key];
                                // ensure color class matches restored value
                                applyColorClassToSelect(el);
                            }
                        }
                    } else if (historyInputs && typeof historyInputs === 'object') {
                        for (let i = 1; i <= df4Labels.length; i++) {
                            const key = 'input' + i + 'df4';
                            const el = document.getElementById(key);
                            if (el && historyInputs[key] !== undefined) el.value = historyInputs[key];
                            // ensure color class matches restored value
                            applyColorClassToSelect(el);
                        }
                    }

                    // Update scores array and input bar chart
                    for (let i = 0; i < df4Labels.length; i++) {
                        const key = 'input' + (i + 1) + 'df4';
                        const el = document.getElementById(key);
                        scores[i] = el && el.value ? parseInt(el.value) : 0;
                        // ensure color class matches restored value
                        applyColorClassToSelect(el);
                    }
                    df4ScoreChart.data.datasets[0].data = scores;
                    df4ScoreChart.update();

                    // Recompute relative importance and update all visuals
                    const newResult = updateRelativeImportance();
                    const newRelImp = newResult.relative;
                    updateRelativeTable(newResult.score, newRelImp);

                    // update bar chart
                    relativeBarChart.data.datasets[0].data = newRelImp;
                    relativeBarChart.data.datasets[0].backgroundColor = newRelImp.map(value =>
                        value > 0 ? 'rgba(54, 162, 235, 0.6)' : value < 0 ? 'rgba(255, 99, 132, 0.6)' : 'rgba(201, 201, 201, 0.6)'
                    );
                    relativeBarChart.data.datasets[0].borderColor = newRelImp.map(value =>
                        value > 0 ? 'rgba(54, 162, 235, 1)' : value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(201, 201, 201, 1)'
                    );
                    relativeBarChart.update();

                    // update radar
                    const rev = [...newRelImp].reverse();
                    relativeRadarChart.data.datasets[0].data = rev;
                    relativeRadarChart.data.datasets[0].borderColor = rev.map(value =>
                        value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                    );
                    relativeRadarChart.data.datasets[0].pointBackgroundColor = rev.map(value =>
                        value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                    );
                    relativeRadarChart.update();
                } catch (e) { console.error(e); }
            })();

            // -------------------- Event Listener: Update Semua Chart & Tabel Saat Input Berubah --------------------
            document.querySelectorAll('.input-score').forEach(input => {
                input.addEventListener('change', function () {
                    // Update DF4 Score Input (grafik bar & radar)
                    let index = parseInt(this.name.replace('input', '').replace('df4', '')) - 1;
                    scores[index] = parseInt(this.value);
                    df4ScoreChart.data.datasets[0].data = scores;
                    df4ScoreChart.update();
                    // (Jika ada radar chart input score lain, pastikan mengupdate juga di sana)

                    // Hitung ulang perhitungan DF4 berdasarkan nilai input
                    const newResult = updateRelativeImportance();
                    const newRelImp = newResult.relative;

                    // Update Relative Importance Bar Chart
                    relativeBarChart.data.datasets[0].data = newRelImp;
                    relativeBarChart.data.datasets[0].backgroundColor = newRelImp.map(value =>
                        value > 0 ? 'rgba(54, 162, 235, 0.6)' :
                            value < 0 ? 'rgba(255, 99, 132, 0.6)' :
                                'rgba(201, 201, 201, 0.6)'
                    );
                    relativeBarChart.data.datasets[0].borderColor = newRelImp.map(value =>
                        value > 0 ? 'rgba(54, 162, 235, 1)' :
                            value < 0 ? 'rgba(255, 99, 132, 1)' :
                                'rgba(201, 201, 201, 1)'
                    );
                    relativeBarChart.update();

                    // Update Relative Importance Radar Chart (data dibalik)
                    const newReversed = [...newRelImp].reverse();
                    relativeRadarChart.data.datasets[0].data = newReversed;
                    relativeRadarChart.data.datasets[0].borderColor = newReversed.map(value =>
                        value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                    );
                    relativeRadarChart.data.datasets[0].pointBackgroundColor = newReversed.map(value =>
                        value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                    );
                    relativeRadarChart.data.datasets[0].pointHoverBorderColor = newReversed.map(value =>
                        value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                    );
                    relativeRadarChart.update();

                    // Update tabel Relative Importance menggunakan DF4 Score yang dihitung
                    updateRelativeTable(newResult.score, newRelImp);
                });
            });
        });

        // If server provided history, prefer it and clear any local draft so saved values take precedence
        try {
            if (historyInputs && typeof historyInputs === 'object') {
                try { localStorage.removeItem(draftKey); } catch (e) { /* ignore */ }
            }
        } catch (e) { /* ignore */ }

        // -------------------- Fungsi untuk update warna --------------------
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.input-score').forEach(input => {
                input.addEventListener('change', function () {
                    // Hapus kelas warna yang sudah ada
                    this.classList.remove('bg-success-subtle', 'bg-warning-subtle', 'bg-danger-subtle');

                    // Tambahkan kelas sesuai nilai input
                    if (this.value === "1") {
                        this.classList.add('bg-success-subtle');
                    } else if (this.value === "2") {
                        this.classList.add('bg-warning-subtle');
                    } else if (this.value === "3") {
                        this.classList.add('bg-danger-subtle');
                    }
                });
            });
        });

    </script>
@endsection