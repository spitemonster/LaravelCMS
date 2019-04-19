@component('head')
    @slot('title')
        {{ $title }}
    @endslot
@endcomponent

    <h1>{{ $title }}</h1>
    {!! $body !!}

@component('footer')
@endcomponent