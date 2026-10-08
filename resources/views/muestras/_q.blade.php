{{-- One review's words. Long ones are cut at a word (or at a sentence end)
     by ReviewShowroomController::cut and offer "Leer más": in place ($mode
     'inline') or in the viewer ($mode 'dialog'). $limitM is a shorter cut for
     phones (under 700px). Without script, the whole text is printed and the
     button is not. $key keeps ids unique when a review is printed twice. --}}
@php
  $C   = \App\Http\Controllers\ReviewShowroomController::class;
  $c   = $C::cut($r->text, $limit ?? 220);
  $cm  = isset($limitM) ? $C::cut($r->text, $limitM) : $c;
  $any = $c['cut'] || $cm['cut'];
  $qid = 'q' . $r->id . '-' . ($key ?? 'a');
@endphp
<blockquote class="ms-q ms-q--{{ $r->tier }} {{ $cls ?? '' }}" @if($any) data-cut @endif>
@if($any)
  @if(isset($limitM))
    <p class="ms-q__short ms-q__short--d">{{ $c['short'] }}</p>
    <p class="ms-q__short ms-q__short--m">{{ $cm['short'] }}</p>
  @else
    <p class="ms-q__short">{{ $c['short'] }}</p>
  @endif
  <div class="ms-q__full" id="{{ $qid }}">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</div>
@else
  @foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach
@endif
</blockquote>
@if($any)
  @php $only = $c['cut'] ? '' : ' ms-more--m'; @endphp
  @if(($mode ?? 'inline') === 'inline')
    <button class="ms-more{{ $only }}" type="button" aria-expanded="false" aria-controls="{{ $qid }}" data-more>Leer más</button>
  @else
    <button class="ms-more{{ $only }}" type="button" aria-haspopup="dialog" data-open="{{ $r->id }}">Leer más</button>
  @endif
@endif
