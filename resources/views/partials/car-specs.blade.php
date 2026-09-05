{{-- The six facts that decide a viewing. One source, rendered in two places and
     shown in exactly one of them at a time: beside the price on a narrow screen,
     where they have to be near the top; under the offer panels from 1000px up,
     where the client wants the page more compact and the sidebar has already said
     its piece. Only one copy is ever displayed, so only one is ever in the
     accessibility tree. --}}
<dl class="mc-specs car-specs">
  @foreach($specs as $k => $v)
    <div class="mc-specs__row"><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
  @endforeach
</dl>
