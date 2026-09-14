@extends('layouts.ad')

@section('title', 'Más — IV MOTORCLASS')

@section('content')

@php
  $all = \App\Support\AdminNav::items();
  [$bar, $over] = \App\Support\AdminNav::split($all);
@endphp

{{-- Where "Más" goes when the sheet cannot open: JavaScript off, or a browser
     without the popover API (Safari before 17). Same list, as a page. With five
     sections or fewer there is no "Más" tab at all, and anyone who lands here
     by URL is shown every section rather than an empty page. --}}
<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Más secciones</h1>
  </div>
</div>

@include('admin.partials.nav-list', ['items' => $over ?: $all])

@endsection
