@component('head')
    @slot('title')
        {{ $title }}
    @endslot
    @slot('description')
        {{ $description }}
    @endslot
@endcomponent
    {!! $body !!}
    </body>
</html>
