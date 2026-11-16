<table class="c5-table c5-guidance">
    <colgroup><col style="width:29%"><col style="width:71%"></colgroup>
    <thead>
        <tr><th colspan="2" class="c5-section-title">{{ $document['code'] }} Related Guidance</th></tr>
        <tr class="c5-subhead"><th scope="col">Related Standard</th><th scope="col">Detailed Reference</th></tr>
    </thead>
    <tbody>
        @forelse($document['guidance'] as $guidance)
            <tr><td>{{ $guidance['standard'] }}</td><td>@include('cobit_component.cobit5.partials.text', ['text' => $guidance['reference']])</td></tr>
        @empty
            <tr><td colspan="2" class="c5-empty">Belum ada Related Guidance untuk proses ini.</td></tr>
        @endforelse
    </tbody>
</table>
