@extends(layouts_uri('default'))

@section('content')
    <h1 class="page-title">Executive Committee</h1>  

	<div class="py-4">
		@if(count($current_committee) > 0)
			<h2>{{ (get_setting('current_ec_members_label') == '') ? 'Current Committee' : get_setting('current_ec_members_label') }}</h2>
			@include(layouts_uri('partials.executive-committee.member-loop'), [ 'members' => 
			$current_committee ])
		@endif

		@if(count($previous_committee) > 0)
			<h2>{{ (get_setting('previous_ec_members_label') == '') ? 'Previous Committee' : get_setting('previous_ec_members_label') }}</h2>
			@include(layouts_uri('partials.executive-committee.member-loop'), [ 'members' => 
			$previous_committee ])
		@endif
	</div>
	
@endsection