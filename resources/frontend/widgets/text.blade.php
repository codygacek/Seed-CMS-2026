<div class="widget__{{$widget->type}} widget">
	<div class="widget-title">{{ $widget->title }}</div>

	<div class="p-4">
		{!! $options->content ?? '' !!}
	</div>
</div>