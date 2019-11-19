@include('head')

{!! $body !!}

{{-- {{ $tags }} --}}

@foreach ($tags as $tag)
    <a href="/tag?tag_id={{ $tag->tag_id }}">{{ $tag->tag_name }}</a>
@endforeach

@include('footer')
