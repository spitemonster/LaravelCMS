<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
    @if (!$pages)
        @foreach ($tags as $tag)
            <p>{{ $tag }}</p>
        @endforeach
    @else
        @foreach ($pages as $page)
            <p>{{ $page }}</p>
        @endforeach
    @endif
</body>
</html>