@extends(layouts_uri('2col-right'))

@section('content')
	<div class="flex flex-wrap">
		<div class="w-full">
			<h1>{{ $event->title }}</h1>

			@if($event->starts_at)
	            <p class="text-lg italic">
	            	@if($event->starts_at)
		                {{ $event->starts_at->format('M j, Y') }}
		            @endif
		            @if($event->ends_at && ($event->ends_at->format('M j, Y') != $event->ends_at->format('M j, Y')))
		                 - {{ $event->ends_at->format('M j, Y') }}
		            @endif
	            </p>
            @endif
            
        	@if($event->image)
				<p><img src="{{ resize(public_path() . $event->image, ['w'=> 870]) }}"></p>
			@endif

			@if($event->content)
				<p class="text-2xl">Event Information</p>
				{!! $event->content !!}
			@endif

			@if($event->venue_name || $event->venue_address || $event->venue_website)
				<p class="text-2xl">Venue Information</p>

				@if($event->venue_name)
					<strong>Venue Name:</strong> {{ $event->venue_name }}<br>
				@endif
				@if($event->venue_address)
					<strong>Venue Address:</strong> {{ $event->venue_address }}<br>
				@endif
				@if($event->venue_website)
					<strong>Venue Website:</strong> <a href="//{{ $event->venue_website }}" target="_blank">{{ $event->venue_website }}</a>
				@endif
			@endif				
        </div>
    </div>
@endsection