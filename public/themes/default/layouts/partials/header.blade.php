<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ get_setting('site_title') }}</title>

    @include(layouts_uri('partials.og'))

    <!-- Styles -->
    <link href="https://use.fontawesome.com/releases/v5.0.2/css/all.css" rel="stylesheet">
    <link href="{{ theme_uri('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ theme_uri('assets/css/swiper.min.css') }}" rel="stylesheet">
    <link href="{{ theme_uri('assets/css/jquery.fancybox.min.css') }}" rel="stylesheet">
    <link href="{{ theme_uri('assets/css/style.css') }}" rel="stylesheet">

    {{-- Custom head HTML (site verification, small scripts, etc.) --}}
    @if (function_exists('get_setting') && filled(get_setting('head_html')))
        {!! get_setting('head_html') !!}
    @endif

    {{-- Google Tag Manager --}}
    @if (function_exists('get_setting') && ($gtm = get_setting('google_tag_manager_id')))
        <!-- Google Tag Manager -->
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $gtm }}');
        </script>
        <!-- End Google Tag Manager -->
    @endif

</head>
<body class="bg-grey-lighter font-sans leading-normal">
    {{-- Custom HTML after opening body --}}
    @if (function_exists('get_setting') && filled(get_setting('body_open_html')))
        {!! get_setting('body_open_html') !!}
    @endif

    {{-- Google Tag Manager (noscript) --}}
    @if (isset($gtm))
        <!-- Google Tag Manager (noscript) -->
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif

    <header class="section">
    	<div class="fluid-container mx-auto p-4">
    		<div class="column is-12">
    			<h1 class="title is-1">{{ get_setting('site_title') }}</h1>
    		</div>
    	</div>
    </header>

    <button class="menu-toggle"><i class="fas fa-bars"></i></button>

    <div class="site-navigation bg-grey-light">
        <div class="container m-auto">
            {!! navigation_menu('main-menu') !!}
        </div>
    </div>

    <section class="section">
        <div class="fluid-container mx-auto sm:py-4 sm:px-1 md:p-4">

        @include(layouts_uri('partials.special-banner'))