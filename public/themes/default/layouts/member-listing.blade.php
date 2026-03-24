{{-- Template Name: Member Listing --}}

@include(layouts_uri('partials.header'))

	<div class="flex flex-wrap">
		<div class="w-full md:w-2/3 p-4">
	        @yield('content')

	        <strong>Total Records:</strong> {{ count($members = get_members(env('CLIENT_CODE', NULL), $page)) }}<br><br>

            <div class="@if(count($members) > 100)three-columns @else two-columns @endif">
                @foreach($members as $index => $member)
                <div>{{ $member['full_name'] }}
                @if($member[env('CLIENT_DATE_TYPE', 'init_date')] !== '')
                     '{{ two_digit_year_from_date_string($member[env('CLIENT_DATE_TYPE', 'init_date')]) }}
                @endif
                </div>
                @endforeach
            </div>
        </div>
        <div class="w-full md:w-1/3 p-4">
        	{!! widgets('default-sidebar') !!}
        </div>
    </div>

@include(layouts_uri('.partials.footer'))