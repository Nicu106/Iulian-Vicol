{{-- The six facts that decide a viewing. One partial, one place: they live in the
     right-hand column under the price, and below 1000px that column is simply the
     next block on the page. --}}
<dl class="mc-specs car-specs">
  @foreach($specs as $k => $v)
    <div class="mc-specs__row"><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
  @endforeach
</dl>
