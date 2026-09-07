{{-- ============================================================================
     The site. One document, every page.

     Before this each page was a standalone <!DOCTYPE html> repeating the same
     twenty lines: charset, viewport, robots, two preconnects, the DM Sans link,
     and the four stylesheets every page loads — then @include('partials.head')
     and @include('partials.foot') at the ends. Five copies of one head. Adding a
     stylesheet or a meta tag meant five edits, and the /coche page had already
     drifted: it carried `interactive-widget=resizes-content` in its viewport and
     /catalogo did not.

     A page now brings its content and nothing else. What it may add:

       @section('title')      the <title>, WHOLE. Not a fragment with the company
                              name appended: /inicio leads with it — "IV MOTORCLASS
                              — Coches alemanes premium en Málaga" — and the others
                              trail it. A layout that appends cannot express both,
                              and quietly rewrote the home page's title when it
                              tried.
       @section('current')    which nav item is marked. Required.
       @section('body')       extra <body> classes — /coche's sold theme uses it.
       @push('css')           page-only stylesheets, after the shared four.
       @push('head')          preloads and anything else in <head>.
       @section('content')    the page.
       @section('after')      what sits BELOW the footer: the phone dock, the
                              full-screen photo viewer. Rare, and explicit.
       @push('js')            page scripts, after the DOM they act on.
     ========================================================================= --}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
{{-- interactive-widget: the phone keyboard resizes the viewport instead of
     scrolling the page under it, which is what a page with forms wants. It was
     on three pages of five before this. --}}
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>@yield('title')</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">

{{-- The four every page loads, in the order the cascade needs: tokens, then the
     system, then the page furniture, then the footer. --}}
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
<link rel="stylesheet" href="{{ asset('css/foot.css') }}">
@stack('css')
@stack('head')
</head>
<body class="bb cat @yield('body')">

@include('partials.head', ['current' => trim($__env->yieldContent('current'))])

@yield('content')

@include('partials.foot')

@yield('after')
@stack('js')
</body>
</html>
