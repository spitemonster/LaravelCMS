<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
    <h1>{{ $title }}</h1>
    {!! $body !!}

    @foreach ($tags as $tag)
        <p>{{ $tag->name }}</p>
    @endforeach
</body>
</html>