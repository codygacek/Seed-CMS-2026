@extends(layouts_uri('default'))

@section('content')
    <div class="flex flex-wrap">
        <div class="w-full">
                <div class="flex flex-wrap">
                    {!! $calendar->make($events) !!}
                </div>
        </div>
    </div>
@endsection