@php
    $workflow = [
        ['title' => 'Understand the enterprise context and strategy', 'items' => ['Understand enterprise strategy.', 'Understand enterprise goals.', 'Understand the risk profile.', 'Understand current I&T-related issues.']],
        ['title' => 'Determine the initial scope of the governance system', 'items' => ['Consider enterprise strategy.', 'Consider enterprise goals and apply the COBIT goals cascade.', 'Consider the risk profile of the enterprise.', 'Consider current I&T-related issues.']],
        ['title' => 'Refine the scope of the governance system', 'items' => ['Consider the threat landscape.', 'Consider compliance requirements.', 'Consider the role of IT.', 'Consider the sourcing model.', 'Consider IT implementation methods.', 'Consider the IT adoption strategy.', 'Consider enterprise size.']],
        ['title' => 'Conclude the governance system design', 'items' => ['Resolve inherent priority conflicts.', 'Conclude the governance system design.']],
    ];
@endphp
<ol class="df-workflow">
    @foreach($workflow as $index => $phase)
        <li class="df-workflow-step df-workflow-step-{{ $index + 1 }}">
            @can('design-factors.view')
                <a class="df-step-circle" href="{{ route('cobit.design-factors.index', ['step' => $index + 1]) }}" aria-label="Step {{ $index + 1 }}: {{ $phase['title'] }}">
                    <span class="df-step-number">STEP {{ $index + 1 }}</span><span class="df-step-title">{{ $phase['title'] }}.</span>
                </a>
            @else
                <div class="df-step-circle"><span class="df-step-number">STEP {{ $index + 1 }}</span><span class="df-step-title">{{ $phase['title'] }}.</span></div>
            @endcan
            <ol class="df-step-details">
                @foreach($phase['items'] as $item)
                    <li><span>{{ $index + 1 }}.{{ $loop->iteration }}</span>{{ $item }}</li>
                @endforeach
            </ol>
        </li>
    @endforeach
</ol>
