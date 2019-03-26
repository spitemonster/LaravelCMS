<!DOCTYPE html>
<html>
    <head>
        <title></title>
    </head>
    <body>
        @if($errors->any())
            <div class="alert alert-error">
                <strong>Uh Oh</strong><br>
                @foreach($errors->all() as $message)
                    {{$message}} <br />
                @endforeach
            </div>
        @endif
        <form action="/page" method="post" accept-charset="utf-8">
            @csrf
            <input type="text" name="title" />

            <select name="parent_id">
                <option value="" selected="selected">—</option>

                @foreach ($pages as $page)
                    <option value="{{ $page->id }}">{{ $page->title }}</option>
                @endforeach
            </select>

            <input type="text" name="url" />

            <textarea name="content"></textarea>
            <button type="submit">Submit</button>
        </form>
    </body>
</html>