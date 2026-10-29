@foreach(['error' => 'danger', 'success' => 'success', 'warning' => 'warning'] as $key => $color)
    @if(session($key))
        <div class="alert alert-{{ $color }}" role="alert">{{ session($key) }}</div>
    @endif
@endforeach
@if($errors->any())
    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
@endif
