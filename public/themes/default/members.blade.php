@extends(layouts_uri('default'))

@section('content')
    <h1 class="page-title">{{ $page_title }}</h1>  

	<div class="flex flex-wrap py-6">
        @foreach($members as $member)
        	<div class="w-full sm:w-1/2 md:w-1/4">
        		<div class="member-card">
					<img class="profile-image block w-full" src="{{ $member->image }}" alt="">
					<div class="member-info-overlay flex">
						<div class="member-info m-auto text-center">
							<h3 class="member-name inline-block pb-2 mb-2">{{ $member->name }}</h3>
							@if($member->alt_info != '')
								{!! $member->alt_info !!}
							@else
								<div>
									@if($member->position != '')
										<span class="member-position">{{ $member->position }}</span><br>
									@endif
									@if($member->date != '')
										<span class="member-date">{{ (get_setting('initiation_or_graduation') == 'Initiation') ? 'Initiated' : 'Graduated' }}: {{ $member->date }}</span><br>
									@endif
									@if($member->major != '')
										<span class="member-major">Major(s): {{ $member->major }}</span><br>
									@endif
									@if($member->current_position != '')
										<span class="member-other-positions">Current Position(s): {{ $member->current_position }}</span><br>
									@endif
									@if($member->other_position != '')
										<span class="member-other-positions">Other Position(s): {{ $member->other_position }}</span><br>
									@endif
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		@endforeach
    </div>
	
@endsection