@include('head')

{!! $body !!}

@foreach ($tags as $tag)
    <a href="/tag?tag_id={{$tag->tag->tag_id}}">{{ $tag->tag->name }}</a>
@endforeach

@include('footer')