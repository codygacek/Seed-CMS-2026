@if(get_setting('special_banner_image'))
	<div class="special-banner py-4 px-4 sm:px-0">
		@if(get_setting('special_banner_link'))
			<a class="block" href="{{ get_setting('special_banner_link') }}">
				<img src="{{ get_setting('special_banner_image') }}" alt="">
			</a>
		@else
			<img src="{{ get_setting('special_banner_image') }}" alt="">
		@endif
	</div>
@endif