{{-- One platform's mark, drawn the way the platform draws it.

     First version: 24px one-colour masks, all grey. Second: each glyph in white
     on a square tile of the platform's colour. Seen at 3x, the tiles read as
     stickers — a small note lost in a black square, Instagram's glyph thin on a
     washed gradient, and Facebook a white circle inside a blue square, which is
     two shapes where the real mark is one.

     So no tiles. Each mark is the brand's own form:
       TikTok     the note in ink with its cyan and red halves offset, which is
                  how TikTok itself sits on a light ground (the lead row is white)
       Instagram  the camera glyph filled with Instagram's gradient
       Facebook   the blue disc with the f knocked out in white

     The TikTok note is a tall narrow shape, so its viewBox is cropped tighter
     than the other two: drawn in the same 24-unit box it looked a size smaller.
     Every non-text mark clears 3:1 against its ground (WCAG 1.4.11).
     aria-hidden: the platform's name is in the text of the link. --}}
@php
  $k   = $k ?? 'tiktok';
  $gid = 'igg-' . substr(md5(uniqid('', true)), 0, 8);
@endphp
<span class="hm-soc__mark hm-soc__mark--{{ $k }}" aria-hidden="true">
  @if($k === 'tiktok')
    <svg viewBox="1.5 1.5 21 21" focusable="false">
      <path fill="#25F4EE" transform="translate(-0.75 -0.75)" d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 0 1-2.59 2.5 2.59 2.59 0 0 1 0-5.18c.27 0 .52.04.76.12v-3.2a5.74 5.74 0 0 0-.76-.05A5.72 5.72 0 0 0 4.15 15.3 5.72 5.72 0 0 0 9.86 21a5.72 5.72 0 0 0 5.71-5.71V9.01a7.35 7.35 0 0 0 4.28 1.38V7.3a4.28 4.28 0 0 1-3.25-1.48z"/>
      <path fill="#FE2C55" transform="translate(0.75 0.75)" d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 0 1-2.59 2.5 2.59 2.59 0 0 1 0-5.18c.27 0 .52.04.76.12v-3.2a5.74 5.74 0 0 0-.76-.05A5.72 5.72 0 0 0 4.15 15.3 5.72 5.72 0 0 0 9.86 21a5.72 5.72 0 0 0 5.71-5.71V9.01a7.35 7.35 0 0 0 4.28 1.38V7.3a4.28 4.28 0 0 1-3.25-1.48z"/>
      <path fill="currentColor" d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 0 1-2.59 2.5 2.59 2.59 0 0 1 0-5.18c.27 0 .52.04.76.12v-3.2a5.74 5.74 0 0 0-.76-.05A5.72 5.72 0 0 0 4.15 15.3 5.72 5.72 0 0 0 9.86 21a5.72 5.72 0 0 0 5.71-5.71V9.01a7.35 7.35 0 0 0 4.28 1.38V7.3a4.28 4.28 0 0 1-3.25-1.48z"/>
    </svg>
  @elseif($k === 'instagram')
    <svg viewBox="0 0 24 24" focusable="false">
      <defs>
        <radialGradient id="{{ $gid }}" cx="0.28" cy="1.02" r="1.25">
          <stop offset="0"    stop-color="#FFD776"/>
          <stop offset="0.24" stop-color="#F3A554"/>
          <stop offset="0.48" stop-color="#F13C5E"/>
          <stop offset="0.72" stop-color="#C62F97"/>
          <stop offset="1"    stop-color="#7B3BE3"/>
        </radialGradient>
      </defs>
      <path fill="url(#{{ $gid }})" d="M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.25.07 1.65.07 4.85s0 3.6-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.25.06-1.65.07-4.85.07s-3.6 0-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.8 3.8 0 0 1-1.38-.9 3.8 3.8 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.21 15.6 2.2 15.2 2.2 12s0-3.6.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.44 2.21 8.84 2.2 12 2.2zm0 1.98c-3.14 0-3.51.01-4.75.07-1.15.05-1.77.24-2.18.4-.55.22-.94.47-1.35.88-.41.41-.66.8-.88 1.35-.16.41-.35 1.03-.4 2.18-.06 1.24-.07 1.61-.07 4.75s.01 3.51.07 4.75c.05 1.15.24 1.77.4 2.18.22.55.47.94.88 1.35.41.41.8.66 1.35.88.41.16 1.03.35 2.18.4 1.24.06 1.61.07 4.75.07s3.51-.01 4.75-.07c1.15-.05 1.77-.24 2.18-.4.55-.22.94-.47 1.35-.88.41-.41.66-.8.88-1.35.16-.41.35-1.03.4-2.18.06-1.24.07-1.61.07-4.75s-.01-3.51-.07-4.75c-.05-1.15-.24-1.77-.4-2.18a3.6 3.6 0 0 0-.88-1.35 3.6 3.6 0 0 0-1.35-.88c-.41-.16-1.03-.35-2.18-.4-1.24-.06-1.61-.07-4.75-.07zm0 3.37a5.45 5.45 0 1 1 0 10.9 5.45 5.45 0 0 1 0-10.9zm0 8.99a3.54 3.54 0 1 0 0-7.08 3.54 3.54 0 0 0 0 7.08zm6.94-9.21a1.27 1.27 0 1 1-2.55 0 1.27 1.27 0 0 1 2.55 0z"/>
    </svg>
  @else
    <svg viewBox="0 0 24 24" focusable="false">
      {{-- The path is the disc with the f cut out; a white disc of the same
           radius underneath is what shows through the cut. --}}
      <circle cx="12" cy="12.06" r="9.8" fill="#FFFFFF"/>
      <path fill="#0866FF" d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.52 1.5-3.91 3.77-3.91 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.45 2.91h-2.33V22c4.78-.76 8.44-4.92 8.44-9.94z"/>
    </svg>
  @endif
</span>
