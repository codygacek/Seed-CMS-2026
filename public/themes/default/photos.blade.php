@extends(layouts_uri('default'))

@section('content')
    <h1 class="page-title">Photos</h1>  

	@if($photo_albums)
		<div class="flex flex-wrap">
	        @foreach($photo_albums as $photo_album)
	        	<div class="w-full sm:w-1/3 md:w-1/4 sm:px-4 mb-4">
					<div class="max-w-sm rounded overflow-hidden shadow-lg">
						<a href="/photos/{{$photo_album->slug}}">
					  		<img class="w-full" src="{{ resize(public_path().'/'.$photo_album->image_items->first()->image, ['w'=> '300', 'h'=>300, 'crop' => true]) }}" alt="{{ $photo_album->title }}">
						</a>
					  <div class="px-6 py-4">
					    <div class="font-bold mb-2">
					    	<a href="/photos/{{$photo_album->slug}}">{{ $photo_album->title }}</a>
					    </div>
					  </div>
					</div>
				</div>
			@endforeach
	    </div>
	@endif
	
@endsection