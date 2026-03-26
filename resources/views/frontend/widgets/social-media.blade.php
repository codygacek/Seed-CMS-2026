<div class="widget__{{$widget->type}} sidebar-social widget">
	<div class="widget-title">{{ $widget->title }}</div>

	<div class="p-4">
		@if($social_media)
			@foreach($social_media as $site)
				<a class="social-link social-link--{{$site->icon}}" href="{{ $site->link }}" target="_blank">
					<span class="social-icon">
						@if(in_array($site->icon, ['desktop']))
							<i class="fas {{ str_replace(':', '-', $site->icon) }} fa-fw"></i> 
						@elseif(in_array($site->icon, ['envelope']))
							<i class="far {{ str_replace(':', '-', $site->icon) }} fa-fw"></i> 
						@else
							<i class="fab {{ str_replace(':', '-', $site->icon) }} fa-fw"></i> 
						@endif
						<span class="social-label">{{ $site->label }}
					</span></a>
			@endforeach
		@endif
	</div>
</div>