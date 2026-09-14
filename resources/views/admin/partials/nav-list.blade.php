{{-- A list of sections, as full-width rows. Used by the "Más" sheet and by
     /admin/mas, which is the same list for a phone without popover support or
     with JavaScript off. $items comes from App\Support\AdminNav. --}}
<ul class="ad-list ad-navlist">
  @php $g = null; @endphp
  @foreach($items as $i)
    @if($i['group'] && $i['group'] !== $g)
      <li class="ad-navlist__g">{{ $i['group'] }}</li>
    @endif
    @php $g = $i['group']; @endphp
    <li>
      <a class="ad-navlist__i {{ $i['on'] ? 'is-on' : '' }}" href="{{ $i['href'] }}"
         @if($i['on']) aria-current="page" @endif>
        <span>{{ $i['label'] }}</span>
        @if($i['count'])<span class="ad-nav__n">{{ $i['count'] }}</span>@endif
      </a>
    </li>
  @endforeach
</ul>
