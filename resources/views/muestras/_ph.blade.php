{{-- A customer's photograph, never cropped.
     The box is drawn at $box (default: the photo's own shape, clamped) and may
     be capped in height by the page (--cap). The picture is `contain` inside
     it, and its blurred placeholder is drawn `contain` too, so it covers
     exactly the picture and never the ground around it. ($sizes is what the
     slot measures; $max caps the derivative ladder.) --}}
<{{ $tag ?? 'div' }} class="ms-ph {{ $cls ?? '' }}" style="--r:{{ (float) ($box ?? $r->box) }}">
  <x-img :src="$r->img" :alt="$alt ?? $r->caption" :sizes="$sizes" :max="$max ?? 1080" :priority="$priority ?? false" />
</{{ $tag ?? 'div' }}>
