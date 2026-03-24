<div class="flex flex-wrap py-6">
    @foreach($members as $member)
    	<div class="w-full sm:w-1/2 mb-8 px-4">
    		<div class="flex flex-wrap bg-white shadow-md">
        		<div class="w-full md:w-2/5 p-4">
					<img class="profile-image rounded-full" src="{{ $member->image }}" alt="">
				</div>
				<div class="w-full md:w-3/5 p-4 text-center md:text-left flex">
					<div class="w-full my-auto">
						<h3 class="member-name mb-2">{{ $member->name }}</h3>
						<div>
							@if($member->position != '')
								<span class="member-position inline-block mb-2">{{ $member->position }}</span><br>
							@endif
							@if($member->position != '')
								<span class="member-date">{{ (get_setting('initiation_or_graduation') == 'Initiation') ? 'Initiated' : 'Graduated' }}: {{ $member->date }}</span><br>
							@endif
							@if($member->major != '')
								<span class="member-major">Major: {{ $member->major }}</span><br>
							@endif
							@if($member->other_position != '')
								<span class="member-other-positions">Other Position(s): {{ $member->other_position }}</span><br>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	@endforeach
</div>