@extends(layouts_uri('default'))

@section('content')
    <div class="content">
    	<h1 class="page-title">{{ $article->title }}</h1>
        {!! $article->content !!}
    </div>
@endsection