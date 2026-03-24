<div class="widget__{{$widget->type}} widget">
	<div class="widget-title">{{ $widget->title }}</div>

	@if($articles = App\Models\Article::where('is_published', 1)->take($options->quantity)->orderBy('created_at', 'DESC')->get())
		@if($articles->count())
			<ul class="widget__feed-list">
				@foreach($articles as $article)
				<li class="widget__feed-list-item"><a href="/news/{{ $article->slug }}">{{ $article->title }}</a></li>
				@endforeach
			</ul>

			<a class="widget__button" href="/news">{{ $options->view_more_text ?? 'View All' }}</a>
		@else
			<p class="p-4 mb-0">No Recent News</p>
		@endif
	@endif
</div>