@extends('layouts.ad')

@section('title', 'Cómo funcionan las recomendaciones — IV MOTORCLASS')

@section('content')

{{-- Where "Cómo funciona" goes when the popup cannot open (JavaScript off, or
     a browser without the popover API). Same words. --}}
<div class="ad-head">
  <div class="ad-head__t">
    <h1 class="ad-h1">Cómo funciona</h1>
    <p class="ad-head__p">Recomendaciones sin cuentas ni contraseñas.</p>
  </div>
  <div class="ad-head__go">
    <a class="ad-btn ad-btn--q" href="{{ route('admin.referrals.index') }}">Volver</a>
  </div>
</div>

<div class="ad-panel">
  @include('admin.referrals._how')
</div>

@endsection
