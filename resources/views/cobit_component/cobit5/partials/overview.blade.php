<table class="c5-table c5-overview">
    <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
    <thead>
        <tr><th colspan="2" class="c5-section-title c5-header-cell"><div class="c5-process-header">
            <span>{{ $document['code'] }} {{ $document['title'] }}</span>
            <span>Area: {{ $document['area'] }}<br>Domain: {{ $document['domain'] }}</span>
        </div></th></tr>
    </thead>
    <tbody>
        <tr class="c5-description"><td colspan="2"><strong>Process Description</strong><br>{{ $document['description'] }}</td></tr>
        <tr class="c5-description"><td colspan="2"><strong>Process Purpose Statement</strong><br>{{ $document['purpose'] }}</td></tr>
        <tr class="c5-band"><th colspan="2">The process supports the achievement of a set of primary IT-related goals:</th></tr>
        <tr class="c5-subhead"><th scope="col">IT-related Goal</th><th scope="col">Related Metrics</th></tr>
        @forelse($document['it_goals'] as $goal)
            <tr><td>{{ $goal['code'] }} {{ $goal['title'] }}</td><td>@include('cobit_component.cobit5.partials.text', ['text' => $goal['metrics']])</td></tr>
        @empty
            <tr><td colspan="2" class="c5-empty">Belum ada IT-related Goal untuk proses ini.</td></tr>
        @endforelse
        <tr class="c5-band"><th colspan="2">Process Goals and Metrics</th></tr>
        <tr class="c5-subhead"><th scope="col">Process Goal</th><th scope="col">Related Metrics</th></tr>
        @forelse($document['process_goals'] as $goal)
            <tr><td>{{ $loop->iteration }}. {{ $goal['title'] }}</td><td>@include('cobit_component.cobit5.partials.text', ['text' => $goal['metrics']])</td></tr>
        @empty
            <tr><td colspan="2" class="c5-empty">Belum ada Process Goal untuk proses ini.</td></tr>
        @endforelse
    </tbody>
</table>
