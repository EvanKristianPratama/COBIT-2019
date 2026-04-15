@if (!empty($practiceSummaryRows ?? []))
    <div class="practice-summary-report">
        <div class="practice-summary-report-header">
            <div class="practice-summary-report-title">Practice Summary {{ $objective->objective_id }}</div>
        </div>
        <div class="practice-summary-report-body">
            <table class="practice-summary-report-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle practice-col">Practice</th>
                        <th colspan="4" class="text-center align-middle">Total of Activities</th>
                        <th rowspan="2" class="text-center align-middle total-col">Total</th>
                    </tr>
                    <tr>
                        <th class="text-center level-col">LV 2</th>
                        <th class="text-center level-col">LV 3</th>
                        <th class="text-center level-col">LV 4</th>
                        <th class="text-center level-col">LV 5</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($practiceSummaryRows as $summaryRow)
                        <tr>
                            <td class="practice-cell">
                                <span class="practice-summary-code">{{ $summaryRow['practice_id'] }}</span>
                                <span class="practice-summary-name">- {{ $summaryRow['practice_name'] }}</span>
                            </td>
                            @foreach ([2, 3, 4, 5] as $levelNumber)
                                <td class="text-center">
                                    {{ ($summaryRow['counts'][$levelNumber] ?? 0) > 0 ? $summaryRow['counts'][$levelNumber] : '-' }}
                                </td>
                            @endforeach
                            <td class="text-center total-col">{{ $summaryRow['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="fw-bold">Total</td>
                        @foreach ([2, 3, 4, 5] as $levelNumber)
                            <td class="text-center fw-bold">
                                {{ ($practiceSummaryTotals[$levelNumber] ?? 0) > 0 ? $practiceSummaryTotals[$levelNumber] : '-' }}
                            </td>
                        @endforeach
                        <td class="text-center fw-bold total-col">
                            {{ ($practiceSummaryTotals['total'] ?? 0) > 0 ? $practiceSummaryTotals['total'] : '-' }}
                        </td>
                    </tr>
                    <tr class="practice-summary-metric-row">
                        <td class="fw-bold">Index</td>
                        @foreach ([2, 3, 4, 5] as $levelNumber)
                            <td class="text-center">{{ $practiceSummaryLevelMetrics[$levelNumber]['index'] ?? '' }}</td>
                        @endforeach
                        <td class="text-center"></td>
                    </tr>
                    <tr class="practice-summary-metric-row">
                        <td class="fw-bold">Rating</td>
                        @foreach ([2, 3, 4, 5] as $levelNumber)
                            <td class="text-center">{{ $practiceSummaryLevelMetrics[$levelNumber]['rating'] ?? '' }}</td>
                        @endforeach
                        <td class="text-center"></td>
                    </tr>
                    <tr class="practice-summary-score-header-row">
                        <td colspan="2" class="text-center">Value</td>
                        <td colspan="2" class="text-center">Rating</td>
                        <td colspan="2" class="text-center">Capability</td>
                    </tr>
                    <tr class="practice-summary-score-value-row">
                        <td colspan="2" class="text-center">{{ $practiceSummaryCapability['value'] ?? '0,00' }}</td>
                        <td colspan="2" class="text-center">{{ $practiceSummaryCapability['rating'] ?? '0N' }}</td>
                        <td colspan="2" class="text-center">{{ $practiceSummaryCapability['level'] ?? '0' }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif
