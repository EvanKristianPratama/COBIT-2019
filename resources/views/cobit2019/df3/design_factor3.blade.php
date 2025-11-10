@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')
  <div class="container">
    <!-- Card Utama -->
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow border-0 rounded">
          <!-- Card Header -->
          <div class="card-header bg-primary text-white text-center py-3">
            <h4 class="mb-0">Design Factor 3 - Risk Profile</h4>
          </div>
          <!-- Card Body -->
          <div class="card-body p-4">
            <form action="{{ route('df3.store') }}" method="POST">
              @csrf
              <input type="hidden" name="df_id" value="{{ $id }}">

        {{-- Admin raw submissions (DF3 multi-part) --}}
  {{-- Admin raw submissions removed per request --}}
              @php
                $historyValues = [];
                if (!empty($historyInputs) && is_array($historyInputs)) {
                  // historyInputs may contain keys like input1df3, impact1, likelihood1, etc.
                  $historyValues = $historyInputs;
                }
              @endphp

              @php
                $labels = [
                  'IT investment decision making, portfolio definition & maintenance',
                  'Program & projects life cycle management',
                  'IT cost & oversight',
                  'IT expertise, skills & behavior',
                  'Enterprise/IT architecture',
                  'IT operational infrastructure incidents',
                  'Unauthorized actions',
                  'Software adoption/usage problems',
                  'Hardware incidents',
                  'Software failures',
                  'Logical attacks (hacking, malware, etc.)',
                  'Third-party/supplier incidents',
                  'Noncompliance',
                  'Geopolitical Issues',
                  'Industrial action',
                  'Acts of nature',
                  'Technology-based innovation',
                  'Environmental',
                  'Data & information management'
                ];
              @endphp

              <!-- Tabel Risk Assessment -->
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <!-- Caption untuk menampilkan tabel legend/keterangan -->
                  <caption class="small caption-top">
                    <strong>Risk Rating:</strong>
                    <table class="table table-sm table-borderless mt-2">
                      <tbody>
                        <tr>
                          <!-- Very High Risk (merah subtle) -->
                          <td class="align-middle">
                            <span class="d-inline-block rounded-circle bg-danger-subtle"
                              style="width:14px; height:14px; margin-right:6px;"></span>
                            Very High Risk
                          </td>

                          <!-- High Risk (oranye subtle) -->
                          <td class="align-middle">
                            <span class="d-inline-block rounded-circle bg-warning-subtle"
                              style="width:14px; height:14px; margin-right:6px;"></span>
                            High Risk
                          </td>
                        </tr>
                        <tr>
                          <!-- Normal Risk (hijau subtle) -->
                          <td class="align-middle">
                            <span class="d-inline-block rounded-circle bg-success-subtle"
                              style="width:14px; height:14px; margin-right:6px;"></span>
                            Normal Risk
                          </td>

                          <!-- Low Risk (abu/gelap subtle) -->
                          <td class="align-middle">
                            <span class="d-inline-block rounded-circle bg-secondary-subtle"
                              style="width:14px; height:14px; margin-right:6px;"></span>
                            Low Risk
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </caption>
                  <!-- Header Tabel DF3 -->
                  <thead class="table-success">
                    <tr>
                      <th scope="col" style="width: 5%;">Reference</th>
                      <th scope="col">Risk Category</th>
                      <th scope="col" style="width: 15%;">Impact 1-5</th>
                      <th scope="col" style="width: 15%;">Likelihood 1-5</th>
                      <th scope="col" style="width: 15%;">Risk Rating</th>
                      <th scope="col" style="width: 15%;">Baseline</th>
                    </tr>
                  </thead>

                  <!-- Body Tabel DF3 -->
                  <tbody>
                    @foreach($labels as $index => $label)
                      <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-primary fw-bold">{{ $label }}</td>
                        <td>
                          @php $impactName = 'impact' . ($index + 1); @endphp
                          <select class="form-select" id="impact{{ $index + 1 }}" name="impact{{ $index + 1 }}"
                            onchange="calculateRiskResult({{ $index + 1 }})">
                            <option value="" selected disabled>Pilih</option>
                            <option value="1" {{ (old($impactName, $historyValues[$impactName] ?? null) == 1) ? 'selected' : '' }}>1</option>
                            <option value="2" {{ (old($impactName, $historyValues[$impactName] ?? null) == 2) ? 'selected' : '' }}>2</option>
                            <option value="3" {{ (old($impactName, $historyValues[$impactName] ?? null) == 3) ? 'selected' : '' }}>3</option>
                            <option value="4" {{ (old($impactName, $historyValues[$impactName] ?? null) == 4) ? 'selected' : '' }}>4</option>
                            <option value="5" {{ (old($impactName, $historyValues[$impactName] ?? null) == 5) ? 'selected' : '' }}>5</option>
                          </select>
                        </td>
                        <td>
                          @php $likelihoodName = 'likelihood' . ($index + 1); @endphp
                          <select class="form-select" id="likelihood{{ $index + 1 }}" name="likelihood{{ $index + 1 }}"
                            onchange="calculateRiskResult({{ $index + 1 }})">
                            <option value="" selected disabled>Pilih</option>
                            <option value="1" {{ (old($likelihoodName, $historyValues[$likelihoodName] ?? null) == 1) ? 'selected' : '' }}>1</option>
                            <option value="2" {{ (old($likelihoodName, $historyValues[$likelihoodName] ?? null) == 2) ? 'selected' : '' }}>2</option>
                            <option value="3" {{ (old($likelihoodName, $historyValues[$likelihoodName] ?? null) == 3) ? 'selected' : '' }}>3</option>
                            <option value="4" {{ (old($likelihoodName, $historyValues[$likelihoodName] ?? null) == 4) ? 'selected' : '' }}>4</option>
                            <option value="5" {{ (old($likelihoodName, $historyValues[$likelihoodName] ?? null) == 5) ? 'selected' : '' }}>5</option>
                          </select>
                        </td>
                        <td>
                          <!-- Hidden input untuk menyimpan nilai Risk Rating -->
                          <input type="hidden" id="result{{ $index + 1 }}" name="input{{ $index + 1 }}df3">
                          <!-- Textbox untuk menampilkan Risk Rating -->
                          <input type="text" class="form-control" id="resultText{{ $index + 1 }}" readonly>
                        </td>
                        <td class="text-center fw-bold text-success fs-5">
                          9
                          <input type="hidden" name="baseline{{ $index + 1 }}" value="9">
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
              <!-- Grafik Input Score -->
              <div class="row mt-4">
                <!-- Bar Chart Input Score -->
                <div class="mb-3">
                  <div class="card h-100">
                    <div class="card-header text-center">Bar Chart Input Score</div>
                    <div class="card-body">
                      <div class="w-100" style="height: 400px;">
                        <canvas id="barChart"></canvas>
                      </div>
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
                            <th class="text-center text-primary">DF3 Score</th>
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
        </div>
      </div>
    </div>
  </div>

  <!-- Include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const historyInputs = @json($historyInputs ?? null);
      const historyScoreData = @json($historyScoreArray ?? null);
      const historyRIData = @json($historyRIArray ?? null);
      const ASSESSMENT_ID = @json(session('assessment_id') ?? null);
      const DF_ID = @json($id);
  const draftKey = `df3:${ASSESSMENT_ID}:${DF_ID}`;
  // number of risks used across this form (declare early so functions can use it)
  const NUM_RISKS = 19;
      // NOTE: restore/prefill will be applied later after charts and functions are initialized

      // save draft on change
      for (let i = 1; i <= 19; i++) {
        const impEl = document.getElementById('impact' + i);
        const likEl = document.getElementById('likelihood' + i);
        [impEl, likEl].forEach(el => {
          if (el) el.addEventListener('change', () => {
            const keys = {};
            for (let j = 1; j <= 19; j++) {
              const a = document.getElementById('impact' + j);
              const b = document.getElementById('likelihood' + j);
              if (a) keys['impact' + j] = a.value || '';
              if (b) keys['likelihood' + j] = b.value || '';
            }
            try { localStorage.setItem(draftKey, JSON.stringify(keys)); } catch (e) {}
          });
        });
      }

      // If server provided history, prefer it and clear any local draft so saved values take precedence
      try {
        if (historyInputs) {
          try { localStorage.removeItem(draftKey); } catch (e) { /* ignore */ }
        }
      } catch (e) { /* ignore */ }
  /***** KONFIGURASI AWAL *****/
  const initialRiskData = new Array(NUM_RISKS).fill(0);

      // Inisialisasi Bar Chart untuk Risk Score dengan style yang konsisten (indexAxis 'y', tooltip & legend disabled, dll)
      const barChart = createBarChart('barChart', [
        'IT investment decision making',
        'Program & projects life cycle',
        'IT cost & oversight',
        'IT expertise, skills & behavior',
        'Enterprise/IT architecture',
        'IT operational infrastructure',
        'Unauthorized actions',
        'Software adoption/usage',
        'Hardware incidents',
        'Software failures',
        'Logical attacks',
        'Third-party/supplier incidents',
        'Noncompliance',
        'Geopolitical Issues',
        'Industrial action',
        'Acts of nature',
        'Technology-based innovation',
        'Environmental',
        'Data & information management'
      ], initialRiskData);

      /***** KONFIGURASI PERHITUNGAN DF3 *****/
      const DF3_MAP = [
        [3.0, 2.0, 3.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 2.0, 0.0, 0.0, 2.0, 2.0, 2.0],
        [3.0, 2.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0, 3.0, 1.0, 3.0],
        [2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 2.0, 0.0, 3.0, 3.0, 0.0, 0.0, 0.0, 2.0, 3.0],
        [3.0, 0.0, 4.0, 3.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 1.0, 0.0, 2.0, 0.0, 0.0, 2.0, 3.0],
        [3.0, 1.0, 3.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 1.0, 0.0, 1.0, 3.0, 3.0, 0.0, 0.0, 0.0, 2.0, 2.0],
        [2.0, 3.0, 2.0, 0.0, 2.0, 2.0, 4.0, 2.0, 0.0, 2.0, 3.0, 3.0, 3.0, 0.0, 0.0, 0.0, 3.0, 2.0, 3.0],
        [2.0, 0.0, 0.0, 0.0, 3.0, 0.0, 0.0, 2.0, 1.0, 0.0, 1.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0, 2.0, 1.0],
        [2.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 2.0, 0.0, 2.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 3.0],
        [0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0],
        [4.0, 2.0, 2.0, 0.0, 2.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0],
        [2.0, 3.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 2.0, 0.0, 0.0, 2.0, 2.0, 0.0],
        [0.0, 0.0, 0.0, 4.0, 0.0, 2.0, 3.0, 3.0, 0.0, 0.0, 2.0, 0.0, 0.0, 2.0, 4.0, 0.0, 2.0, 2.0, 0.0],
        [0.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 4.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 3.0, 0.0, 2.0],
        [0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 2.0, 3.0, 0.0, 1.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 2.0, 2.0, 3.0, 2.0, 2.0, 4.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 0.0, 0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 0.0, 4.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 2.0, 0.0, 0.0, 2.0, 0.0, 3.0, 0.0, 2.0, 4.0, 2.0, 0.0, 4.0],
        [0.0, 4.0, 0.0, 0.0, 2.0, 0.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [2.0, 2.0, 0.0, 0.0, 2.0, 0.0, 0.0, 3.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 3.0, 0.0, 0.0, 2.0, 0.0, 0.0, 2.0, 0.0, 3.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 2.0, 0.0, 2.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 4.0, 0.0, 0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 3.0, 2.0, 0.0, 4.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 2.0, 0.0, 3.0, 0.0, 3.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 2.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 4.0, 0.0, 0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 4.0, 3.0, 0.0, 4.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 2.0, 3.0, 2.0, 2.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 1.0, 4.0, 0.0, 3.0, 1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 3.0, 0.0, 3.0, 0.0, 4.0, 0.0, 2.0, 0.0, 3.0, 4.0, 0.0, 0.0, 2.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 4.0, 0.0, 2.0, 0.0, 4.0, 0.0, 3.0, 0.0, 3.0, 2.0, 0.0, 0.0, 3.0],
        [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 4.0, 2.0, 0.0, 0.0, 2.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0],
        [1.0, 2.0, 2.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 2.0, 3.0, 2.0, 2.0, 2.0, 0.0, 2.0, 0.0, 0.0, 2.0],
        [1.0, 2.0, 2.0, 0.0, 0.0, 3.0, 3.0, 0.0, 0.0, 2.0, 3.0, 2.0, 2.0, 3.0, 0.0, 2.0, 0.0, 0.0, 2.0],
        [0.0, 1.0, 0.0, 0.0, 0.0, 1.0, 2.0, 0.0, 0.0, 0.0, 3.0, 2.0, 4.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0],
        [1.0, 2.0, 0.0, 0.0, 0.0, 0.0, 3.0, 0.0, 0.0, 2.0, 3.0, 2.0, 2.0, 4.0, 0.0, 2.0, 2.0, 0.0, 2.0]
      ];


      /***** KONFIGURASI RISK BASELINE *****/
      const DF3_BASELINE = [
        [9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9]
      ];
      const DF3_SCORE_BASELINE = [
        [
          189, 135, 162, 198, 189, 324, 144, 171, 45, 144,
          153, 216, 153, 117, 216, 99, 90, 99, 198, 81,
          117, 117, 9, 72, 135, 117, 135, 36, 99, 36,
          135, 144, 108, 216, 216, 144, 216, 243, 153, 225
        ]
      ];

      /***** FUNGSI HELPER *****/
      function mround(value, precision) {
        return Math.round(value / precision) * precision;
      }
      function getRiskProductInputs() {
        const productInputs = [];
        for (let i = 1; i <= NUM_RISKS; i++) {
          const impact = parseFloat(document.getElementById(`impact${i}`).value) || 0;
          const likelihood = parseFloat(document.getElementById(`likelihood${i}`).value) || 0;
          productInputs.push(impact * likelihood);
        }
        return productInputs;
      }
      function computeDF3Score(productInputs) {
        return DF3_MAP.map(row => row.reduce((sum, bValue, j) => sum + bValue * productInputs[j], 0));
      }
      function average(numbers) {
        return numbers.reduce((sum, num) => sum + num, 0) / numbers.length;
      }
      function computeDF3RelativeImportance(DF3_SCORE, productInputs) {
        const inputAverage = average(productInputs);
        const baselineValues = DF3_BASELINE.flat();
        const baselineAverage = average(baselineValues);
        const G28 = inputAverage !== 0 ? baselineAverage / inputAverage : 0;
        return DF3_SCORE.map((score, i) => {
          const baseline = DF3_SCORE_BASELINE[0][i];
          if (baseline && baseline !== 0) {
            const computed = mround((G28 * 100 * score) / baseline, 5) - 100;
            return computed === -100 ? 0 : computed;
          }
          return 0;
        });
      }

      /***** UPDATE CHART & TABEL *****/
      function updateBarChart() {
        const riskData = [];
        for (let i = 1; i <= NUM_RISKS; i++) {
          const result = parseFloat(document.getElementById(`result${i}`).value) || 0;
          riskData.push(result);
        }
        barChart.data.datasets[0].data = riskData;
        barChart.update();
      }
      function updateRelativeTable(DF3_SCORE, DF3_RELATIVE_IMPORTANCE) {
        const tbody = document.querySelector('#results-table tbody');
        tbody.innerHTML = '';
        DF3_SCORE.forEach((score, i) => {
          const row = document.createElement('tr');
          let scoreClass = '';
          if (DF3_RELATIVE_IMPORTANCE[i] > 0) {
            scoreClass = 'bg-primary-subtle text-dark';
          } else if (DF3_RELATIVE_IMPORTANCE[i] < 0) {
            scoreClass = 'bg-danger-subtle text-dark';
          }
          row.innerHTML = `
          <td class="text-center">${i + 1}</td>
          <td class="text-center">${score.toFixed(2)}</td>
          <td class="text-center ${scoreClass}">${DF3_RELATIVE_IMPORTANCE[i]}</td>
          `;
          tbody.appendChild(row);
        });
      }
      function updateRelativeBarChart(chartInstance, data) {
        chartInstance.data.datasets[0].data = data;
        chartInstance.data.datasets[0].backgroundColor = data.map(val =>
          val > 0 ? 'rgba(54, 162, 235, 0.6)' :
            val < 0 ? 'rgba(255, 99, 132, 0.6)' :
              'rgba(201, 201, 201, 0.6)'
        );
        chartInstance.data.datasets[0].borderColor = data.map(val =>
          val > 0 ? 'rgba(54, 162, 235, 1)' :
            val < 0 ? 'rgba(255, 99, 132, 1)' :
              'rgba(201, 201, 201, 1)'
        );
        chartInstance.update();
      }
      function updateRelativeRadarChart(chartInstance, data) {
        chartInstance.data.datasets[0].data = data;
        chartInstance.data.datasets[0].borderColor = data.map(val =>
          val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
        );
        chartInstance.data.datasets[0].pointBackgroundColor = data.map(val =>
          val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
        );
        chartInstance.update();
      }

      /***** HITUNG HASIL RISIKO *****/
      function calculateRiskResult(index) {
        const impactValue = parseFloat(document.getElementById(`impact${index}`).value) || 0;
        const likelihoodValue = parseFloat(document.getElementById(`likelihood${index}`).value) || 0;
        const result = impactValue * likelihoodValue;
        document.getElementById(`result${index}`).value = result;
        const resultText = document.getElementById(`resultText${index}`);
        resultText.value = result;
        resultText.classList.remove('bg-success-subtle', 'bg-warning-subtle', 'bg-danger-subtle');
        if (result >= 0 && result <= 6) {
          resultText.classList.add('bg-success-subtle');
        } else if (result > 6 && result <= 12) {
          resultText.classList.add('bg-warning-subtle');
        } else if (result > 12) {
          resultText.classList.add('bg-danger-subtle');
        }
        updateBarChart();
        computeAndUpdateDF3Metrics();
      }
      function computeAndUpdateDF3Metrics() {
        const productInputs = getRiskProductInputs();
        const DF3_SCORE = computeDF3Score(productInputs);
        const DF3_RELATIVE_IMPORTANCE = computeDF3RelativeImportance(DF3_SCORE, productInputs);
        updateRelativeTable(DF3_SCORE, DF3_RELATIVE_IMPORTANCE);
        if (relativeBarChart) {
          updateRelativeBarChart(relativeBarChart, DF3_RELATIVE_IMPORTANCE);
        }
        if (relativeRadarChart) {
          updateRelativeRadarChart(relativeRadarChart, DF3_RELATIVE_IMPORTANCE);
        }
      }

      /***** PEMBUATAN CHART MENGGUNAKAN CHART.JS (STYLE SAMA DENGAN DF1 & DF2) *****/
      function createBarChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Risk Score',
              data: data,
              backgroundColor: 'rgba(54, 162, 235, 0.5)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
              x: {
                max: 25, // Maksimum disesuaikan dengan range skor risiko
                display: false
              },
              y: {
                ticks: { font: { size: 12 } }
              }
            },
            plugins: {
              legend: { display: false },
              tooltip: { enabled: false }
            }
          }
        });
      }
      function createRelativeBarChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Relative Importance Score',
              data: data,
              backgroundColor: data.map(val =>
                val > 0 ? 'rgba(54, 162, 235, 0.6)' :
                  val < 0 ? 'rgba(255, 99, 132, 0.6)' :
                    'rgba(201, 201, 201, 0.6)'
              ),
              borderColor: data.map(val =>
                val > 0 ? 'rgba(54, 162, 235, 1)' :
                  val < 0 ? 'rgba(255, 99, 132, 1)' :
                    'rgba(201, 201, 201, 1)'
              )
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
                  color: ctx => ctx.tick.value === 0
                    ? 'rgba(0, 0, 0, 0.3)'
                    : 'rgba(200, 200, 200, 0.3)',
                  lineWidth: ctx => ctx.tick.value === 0 ? 2 : 1
                }
              },
              y: { ticks: { font: { size: 12 }, autoSkip: false } }
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
      }
      function createRelativeRadarChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
          type: 'radar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Relative Importance',
              data: data,
              backgroundColor: 'rgba(235, 54, 54, 0.2)',
              borderColor: data.map(val =>
                val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
              ),
              borderWidth: 2,
              pointBackgroundColor: data.map(val =>
                val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
              ),
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: data.map(val =>
                val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
              ),
              borderJoinStyle: 'round',
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
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
            },
            elements: { line: { tension: 0.4 } }
          }
        });
      }

      /***** DEKLARASI VARIABEL UNTUK CHART RELATIVE *****/
      let relativeBarChart;
      let relativeRadarChart;

      /***** LABEL UNTUK RELATIVE IMPORTANCE *****/
      const RELATIVE_LABELS = [
        'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
        'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07', 'APO08', 'APO09', 'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
        'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
        'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
        'MEA01', 'MEA02', 'MEA03', 'MEA04'
      ];

      /***** EVENT LISTENER *****/
      for (let i = 1; i <= NUM_RISKS; i++) {
        document.querySelectorAll(`#impact${i}, #likelihood${i}`).forEach(input => {
          input.addEventListener('input', () => {
            calculateRiskResult(i);
          });
        });
      }

      // Panggil perhitungan awal (jika ada nilai default)
      computeAndUpdateDF3Metrics();

      // Inisialisasi Relative Importance Charts dengan label RELATIVE_LABELS
      const initialRelativeData = new Array(RELATIVE_LABELS.length).fill(0);
      relativeBarChart = createRelativeBarChart('relativeImportanceChart', RELATIVE_LABELS, initialRelativeData);
      relativeRadarChart = createRelativeRadarChart('relativeImportanceRadarChart', RELATIVE_LABELS, initialRelativeData);

      // Apply server history or local draft now that charts and helper functions are ready
      (function applyHistoryAndCompute() {
        try {
          const raw = localStorage.getItem(draftKey);
          if (!historyInputs && raw) {
            const draft = JSON.parse(raw);
            if (draft && typeof draft === 'object') {
              Object.keys(draft).forEach(k => {
                const el = document.getElementById(k);
                if (el) el.value = draft[k];
              });
              for (let i = 1; i <= NUM_RISKS; i++) calculateRiskResult(i);
            }
          } else if (historyInputs) {
            for (let i = 1; i <= NUM_RISKS; i++) {
              const imp = historyInputs['impact' + i];
              const lik = historyInputs['likelihood' + i];
              const impEl = document.getElementById('impact' + i);
              const likEl = document.getElementById('likelihood' + i);
              if (imp !== undefined && impEl) impEl.value = imp;
              if (lik !== undefined && likEl) likEl.value = lik;
              calculateRiskResult(i);
            }
          }
        } catch (e) { console.error(e); }
      })();
    });
  </script>

@endsection