<div class="c5-raci-scroll">
    <table class="c5-table c5-raci">
        <colgroup><col style="width:22.5%">@foreach($document['roles'] as $role)<col style="width:{{ 77.5 / max(1, count($document['roles'])) }}%">@endforeach</colgroup>
        <thead>
            <tr><th colspan="{{ count($document['roles']) + 1 }}" class="c5-section-title">{{ $document['code'] }} RACI Chart</th></tr>
            <tr><th scope="col" class="c5-raci-practice">{{ $document['area'] === 'Governance' ? 'Governance' : 'Management' }} Practice</th>
                @foreach($document['roles'] as $role)<th scope="col" class="c5-raci-role"><span>{{ $role }}</span></th>@endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($document['practices'] as $practice)
                <tr><td><strong>{{ $practice['code'] }}</strong><br>{{ $practice['title'] }}</td>
                    @foreach($document['roles'] as $role)<td class="c5-raci-value">{{ $practice['raci'][$loop->index] ?? '' }}</td>@endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
