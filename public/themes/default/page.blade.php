@extends(layouts_uri($page->layout))

@section('content')
    <h1 class="page-title">{{ $page->title }}</h1>  

	@includeIf(layouts_uri('partials.slider'))

	@shortcodes($page->content)
@endsection