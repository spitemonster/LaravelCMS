@component('head')
    @slot('title')
        {{ $title }}
    @endslot
@endcomponent
                    <img src="{{ $media }}" alt="" />
                </body>
            </html>
