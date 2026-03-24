@extends(layouts_uri('2col-right'))

@section('content')
    <div class="flex flex-wrap">
        <div class="w-full">
            <h1 class="page-title">News</h1> 
            @foreach($articles as $article)
                <article class="feed-item">
                    <h2 class="title is-4"><a href="/news/{{ $article->slug }}">{{ $article->title }}</a></h2>
                    {!! $article->excerpt() !!}
                </article>
            @endforeach 

            <div class="pagination">
                {{ $articles->render() }}
            </div>
        </div>
    </div>
@endsection