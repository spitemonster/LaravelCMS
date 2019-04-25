@component('head')
    @slot('title')
        {{ $title }}
    @endslot
@endcomponent
    {!! $body !!}

    </body>
</html>
