{{-- Template Name: Home Page --}}

@include(layouts_uri('partials.header'))

	@include(layouts_uri('partials.slider'))

    @shortcodes($page->content)

@include(layouts_uri('partials.footer'))