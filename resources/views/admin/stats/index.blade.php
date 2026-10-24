@extends('layouts.ad')

@section('title', 'Estadísticas — IV MOTORCLASS')

@section('content')

  <div class="ad-head">
    <div class="ad-head__t">
      <h1 class="ad-h1">Estadísticas</h1>
      <p class="ad-head__p">{{ $car ? 'Un coche' : 'Lo que pasa en la web' }}</p>
    </div>
    <div class="ad-head__go">@include('admin.stats._switch')</div>
  </div>

  @include($inner)

@endsection

@push('js')
  @include('admin.stats._script')
  <script>window.mcStats && window.mcStats.wire(document);</script>
@endpush
