{{-- Template Name: 2 Column Left --}}

@include(layouts_uri('partials.header'))

	<div class="flex flex-wrap">
		<div class="w-full md:w-1/3 p-4">
        	{!! widgets('default-sidebar') !!}
        </div>
		<div class="w-full md:w-2/3 p-4">
	        @yield('content')
        </div>
    </div>

@include(layouts_uri('partials.footer'))