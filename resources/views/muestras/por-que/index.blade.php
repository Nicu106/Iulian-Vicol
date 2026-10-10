@extends('layouts.site')
@section('title', 'Proposals — Why IV MOTORCLASS')
@section('robots', 'noindex, nofollow')
@section('content')
{{-- Review index, in English (the proposals themselves are Spanish). Versions of the
     same approach are grouped, newest first; every earlier version stays one click away. --}}
@php
  $groups = [];
  foreach ($variants as $k => $x) { $groups[$x[2]][$k] = $x; }
  foreach ($groups as &$g) { krsort($g, SORT_NATURAL); } unset($g);
@endphp
<main class="cat-wrap" lang="en" style="padding-block:var(--s-8)">
  <h1 style="margin:0 0 var(--s-3);font-size:clamp(2rem,7vw,3.4rem);line-height:1.02;letter-spacing:-.035em">Why IV MOTORCLASS<br>Page proposals</h1>
  <p style="margin:0 0 var(--s-7);max-width:40rem;color:var(--mc-ink-2);font-size:var(--t-prose)">Each proposal is the whole page with today's texts. Open it on the phone and on the computer. Earlier versions stay here, so you can always go back.</p>
  <div style="display:grid;gap:var(--s-7)">
    @foreach($groups as $approach => $list)
      <section style="border-top:1px solid var(--mc-hairline);padding-top:var(--s-5)">
        <h2 style="margin:0 0 var(--s-4);font-size:var(--t-h2);letter-spacing:-.02em">{{ $approach }}</h2>
        <ul style="list-style:none;margin:0;padding:0;display:grid;gap:var(--s-5)">
          @foreach($list as $k => [$name, $what])
            <li>
              @if(count($list) > 1)
                <h3 style="margin:0 0 var(--s-2);font-size:var(--t-h3)">{{ $name }}@if($loop->first) <span style="color:var(--mc-ink-2);font-weight:500">· latest</span>@endif</h3>
              @endif
              <p style="margin:0 0 var(--s-3);max-width:40rem;color:var(--mc-ink-2)">{{ $what }}</p>
              <a href="/muestras/por-que/{{ $k }}" style="display:inline-flex;align-items:center;min-height:48px;padding:0 var(--s-5);border-radius:10px;font-weight:600;text-decoration:none;{{ $loop->first ? 'background:var(--mc-ink);color:#fff' : 'border:1px solid var(--mc-ink);color:var(--mc-ink)' }}">Open {{ str_contains((string) $k, '.') ? $k : $k . '.0' }}</a>
            </li>
          @endforeach
        </ul>
      </section>
    @endforeach
  </div>
</main>
@endsection
