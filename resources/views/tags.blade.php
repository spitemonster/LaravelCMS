<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>

        @foreach ($tags as $tag)
            <h2>{{ $tag->tag_name }}</h2>
            <p>Tag ID: {{ $tag->tag_id }}</p>

            <h4>Pages</h4>
            @if ($tag->pages)
                @foreach ($tag->pages as $page)
                    <div>
                        <a href="{{ $page->url }}"><h3>{{ $page->title }}</h3></a>
                        <p>Page ID: {{ $page->page_id }}</p>
                    </div>
                @endforeach
            @endif
        @endforeach
</body>
</html>