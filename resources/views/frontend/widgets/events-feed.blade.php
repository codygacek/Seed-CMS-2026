<div class="widget__{{$widget->type}} widget">
	<div class="widget-title">{{ $widget->title }}</div>

	@if($events = App\Models\Event::getUpcoming()->take($options->quantity))
		@if($events->count())
			<ul class="widget__feed-list">
				@foreach($events as $event)
				<li class="widget__feed-list-item"><a href="/events/{{ $event->slug }}">{{ $event->title }}</a></li>
				@endforeach
			</ul>

			<a class="widget__button" href="/calendar">{{ $options->view_more_text ?? 'View All' }}</a>
		@else
			<p class="p-4 mb-0">No Upcoming Events</p>
		@endif
	@endif
</div>