{{-- The home section's own heading and paragraph, word for word. Inside the
     showroom's frames (?embed=1) it is left out: the showroom names each
     proposal itself, and ten copies of it would fill every first screen. --}}
@unless(!empty($embed))
<div class="ms-wrap ms-head">
  <h2 class="ms-h2" id="h-op">La confianza se gana. Ellos te cuentan cómo.</h2>
  <p class="ms-lede">Conoce a quienes ya han comprado su coche en IV Motorclass. Estas son
    sus fotos el día de la entrega y sus opiniones sobre la experiencia.</p>
</div>
@else
<h2 class="ms-sr" id="h-op">La confianza se gana. Ellos te cuentan cómo.</h2>
@endunless
