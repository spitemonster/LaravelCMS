@include('head')

{!! $body !!}

{{-- {{ $tags }} --}}

@foreach ($tags as $tag)
    <a href="/tag?tag_name={{ $tag->name }}">{{ $tag->name }}</a>
@endforeach

@include('footer')
