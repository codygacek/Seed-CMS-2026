<meta property="og:url"                content="{{ url()->full() }}" />
@if(isset($page))
	<meta property="og:title"              content="{{ $page->title }}" />
@elseif(isset($event))
	<meta property="og:title"              content="{{ $event->title }}" />
@elseif(isset($article))
	<meta property="og:title"              content="{{ $article->title }}" />
@elseif(isset($photo_album))
	<meta property="og:title"              content="{{ $photo_album->title }}" />
@elseif(isset($page_title))
	<meta property="og:title"              content="{{ $page_title }}" />
@endif
<meta property="og:image"              content="{{ env('OG_IMAGE', url('/themes/fraternity/assets/img/logo.png') )}}" />