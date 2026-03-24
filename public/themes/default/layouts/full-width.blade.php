{{-- Template Name: Default --}}

@include(layouts_uri('partials.header'))

	<div class="p-4">
		@yield('title')
	    @yield('content')
	</div>

@include(layouts_uri('partials.footer'))