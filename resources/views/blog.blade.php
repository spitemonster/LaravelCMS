
            <!DOCTYPE html>
            <html>
                <head>
                    <title></title>
                </head>
                <body>
                    @foreach ($children as $child)
        <div style="border: 2px solid black; margin: 1rem; width: 280px; padding: 1rem;">
            <h3><a href="{{ $child->url }}">{{ $child->title }}</a></h3>
        </div>
    @endforeach
                </body>
            </html>
