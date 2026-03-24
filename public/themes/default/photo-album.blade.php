@extends(layouts_uri('default'))

@section('content')
    <h1 class="page-title">{{ $photo_album->title }}</h1>  

    @if($photo_album->description)
    	{!! $photo_album->description !!}
    @endif

	@if($photo_album->image_items)
		<div class="flex flex-wrap">
	        @foreach($photo_album->image_items as $image_item)
	        	<div class="w-full sm:w-1/3 md:w-1/4 sm:px-4 mb-4">
					<div class="max-w-sm rounded overflow-hidden shadow-lg">
						<a data-fancybox="images" href="/{{$image_item->image}}">
					  		<img class="w-full block" src="{{ resize(public_path().'/'.$image_item->image, ['w'=> '300', 'h'=>300, 'crop' => true]) }}" alt="{{ $image_item->title }}">
						</a>
					</div>
				</div>
			@endforeach
	    </div>
	@endif
	
@endsection