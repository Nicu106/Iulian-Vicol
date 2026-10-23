{{-- The coches.net sheet, read into this form (Admin\SheetController::read, script in
     _sheet-script). He picks the PDF that coches.net PRO prints with "Imprimir
     vehículo"; what it says fills the empty fields, and where he already wrote
     something else the sheet's value waits under the field with "Usar". Nothing is
     saved until he presses Guardar: the form only carries the sheet's key. --}}
@php
  $sh = is_array($v['sheet'] ?? null) ? $v['sheet'] : null;
  $shRead = $sh && ($sh['source'] ?? '') === 'coches.net';
@endphp
<section class="ad-box ad-cnet {{ $shRead ? 'has-sheet' : '' }}" id="ad-cnet"
         data-read="{{ route('admin.sheets.read') }}" data-slug="{{ $slug ?? '' }}">
  <input type="text" name="sheet_key" id="ad-cnet-key" value="{{ old('sheet_key') }}" hidden tabindex="-1" aria-hidden="true">
  <div class="ad-cnet__top">
    <div class="ad-cnet__say">
      <h2 class="ad-fs__h">{{ $shRead ? 'Ficha de coches.net' : 'Rellenar con la ficha de coches.net' }}</h2>
      @if($shRead)
        <p class="ad-cnet__p">
          Leída el {{ \Illuminate\Support\Carbon::parse($sh['imported_at'] ?? now())->format('d/m/Y') }}:
          {{ count($sh['extras'] ?? []) ? count($sh['extras']) . ' extras de fábrica, ' : '' }}precio de nuevo{{ !empty($sh['new_total']) ? ' ' . number_format($sh['new_total'], 0, ',', '.') . ' €' : '' }}.
          @if(!empty($sh['pdf']))<a class="ad-cnet__a" href="{{ route('admin.sheets.original', $slug) }}" target="_blank" rel="noopener">Ver el PDF</a>@endif
        </p>
      @else
        <p class="ad-cnet__p">Sube el PDF de «Imprimir vehículo» y relleno el coche por ti: datos, descripción, extras con su precio y el precio de nuevo. Antes de guardar ves lo que cambia.</p>
      @endif
    </div>
    <label class="ad-btn ad-cnet__pick">
      <input class="ad-vh" type="file" id="ad-cnet-file" accept="application/pdf,.pdf">
      <span>{{ $shRead ? 'Subir otra ficha' : 'Subir el PDF' }}</span>
    </label>
  </div>
  <div class="ad-cnet__out" id="ad-cnet-out" role="status" aria-live="polite" hidden></div>
  {{-- what was read, to look at and confirm before anything goes in the form (_sheet-script) --}}
  <dialog class="ad-cnet-dlg" id="ad-cnet-dlg" aria-labelledby="ad-cnet-dlg-h"></dialog>
  @if($shRead)
    <label class="ad-cnet__rm"><input type="checkbox" name="sheet_remove" value="1"> Quitar la ficha de este coche</label>
  @endif
</section>
