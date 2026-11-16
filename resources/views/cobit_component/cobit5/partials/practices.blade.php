<table class="c5-table c5-practices">
    <colgroup><col style="width:38%"><col style="width:12%"><col style="width:19%"><col style="width:19%"><col style="width:12%"></colgroup>
    <thead><tr><th colspan="5" class="c5-section-title">{{ $document['code'] }} Process Practices, Inputs/Outputs and Activities @if($continued)<em>(cont.)</em>@endif</th></tr></thead>
    <tbody>
        @forelse($practices as $practice)
            <tr class="c5-subhead c5-flow-heading" id="c5-flow-{{ str_replace('.', '-', $practice['code']) }}"><th scope="col">{{ $document['area'] === 'Governance' ? 'Governance' : 'Management' }} Practice
                @can('design-factors.input')
                    @if(!empty($practice['practice_id']))<button type="button" class="c5-flow-edit" data-c5-flow-edit="{{ $practice['code'] }}" aria-label="Edit Information Flow {{ $practice['code'] }}">Edit</button>@endif
                @endcan
            </th><th colspan="2">Inputs</th><th colspan="2">Outputs</th></tr>
            <tr>
                <td rowspan="{{ count($practice['flow_rows']) + 1 }}"><strong>{{ $practice['code'] }} {{ $practice['title'] }}</strong><br>{{ $practice['description'] }}</td>
                <th scope="col" class="c5-flow-label">From</th><th scope="col" class="c5-flow-label">Description</th><th scope="col" class="c5-flow-label">Description</th><th scope="col" class="c5-flow-label">To</th>
            </tr>
            @foreach($practice['flow_rows'] as $flow)
                <tr>
                    @if($flow['input'] !== null)
                        <td rowspan="{{ $flow['input']['rowspan'] }}">@include('cobit_component.cobit5.partials.text', ['text' => $flow['input']['from']])</td>
                        <td rowspan="{{ $flow['input']['rowspan'] }}">@include('cobit_component.cobit5.partials.text', ['text' => $flow['input']['description']])</td>
                    @endif
                    @if($flow['output'] !== null)
                        <td rowspan="{{ $flow['output']['rowspan'] }}">@if(!empty($flow['output']['wp_id']))<div class="c5-wp-code">{{ $flow['output']['wp_id'] }}</div>@endif @include('cobit_component.cobit5.partials.text', ['text' => $flow['output']['description']])</td>
                        <td rowspan="{{ $flow['output']['rowspan'] }}">@include('cobit_component.cobit5.partials.text', ['text' => $flow['output']['to']])</td>
                    @endif
                </tr>
            @endforeach
            <tr class="c5-activities-heading"><th colspan="5">Activities</th></tr>
            @forelse($practice['activities'] as $activity)
                <tr><td colspan="5" class="c5-activity">{{ $loop->iteration }}. {{ $activity }}</td></tr>
            @empty
                <tr><td colspan="5" class="c5-empty">Belum ada activity untuk practice ini.</td></tr>
            @endforelse
        @empty
            <tr><td colspan="5" class="c5-empty">Belum ada practice untuk proses ini.</td></tr>
        @endforelse
    </tbody>
</table>
