@include('head')

<h1>{{ $title }}</h1>

{!! $body !!}

@foreach ($tags as $tag)
    <a href="/tag?tag_id={{$tag->tag_id}}">{{ $tag->name }}</a>
@endforeach

@include('footer')