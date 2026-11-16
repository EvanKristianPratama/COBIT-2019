@if(is_array($text))
    <ul class="c5-bullets">@foreach($text as $line)<li>{{ $line }}</li>@endforeach</ul>
@else
    <div class="c5-text">{{ $text }}</div>
@endif
