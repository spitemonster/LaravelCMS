<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{!! $body !!}</p>

    @foreach ($children as $child)
        {{ $child->title }}
    @endforeach
</body>
</html>