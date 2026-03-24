@if($slides)
	<div class="swiper-container">
		<div class="swiper-wrapper">
		@foreach($slides as $slide)
			<div class="swiper-slide"><img src="{{ resize(public_path().'/'.$slide->image, ['w'=> '844', 'h'=>400]) }}" alt="{{ $slide->title }}"></div>
		@endforeach
		</div>

		<!-- Add Pagination -->
	    <div class="swiper-pagination"></div>
	    <!-- Add Arrows -->
	    <div class="swiper-button-next swiper-button-white"></div>
		<div class="swiper-button-prev swiper-button-white"></div>
	</div>
@endif