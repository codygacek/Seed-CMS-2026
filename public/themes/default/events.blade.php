@extends(layouts_uri('2col-right'))

@section('content')
    <div class="flex flex-wrap">
        <div class="w-full">
            <h1 class="page-title">Events</h1>  
        	@foreach($events as $event)
        		<article class="feed-item">
            		<h2 class="title is-4"><a href="/events/{{ $event->slug }}">{{ $event->title }}</a></h2>
                    @if($event->starts_at)
                        {{ $event->starts_at->format('M j, Y') }}
                    @endif
                    @if($event->ends_at)
                         - {{ $event->ends_at->format('M j, Y') }}
                    @endif
                </article>
            @endforeach

            <div class="pagination">
                {{ $events->render() }}
            </div>
        </div>
    </div>
@endsection