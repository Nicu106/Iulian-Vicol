{{-- One platform's mark, drawn the way the platform draws it.

     First version: 24px one-colour masks, all grey. Second: each glyph in white
     on a square tile of the platform's colour. Seen at 3x, the tiles read as
     stickers — a small note lost in a black square, Instagram's glyph thin on a
     washed gradient, and Facebook a white circle inside a blue square, which is
     two shapes where the real mark is one.

     So no tiles. Each mark is the brand's own form:
       TikTok     the note in ink with its cyan and red halves offset, which is
                  how TikTok itself sits on a light ground (the lead row is white)
       Instagram  the app icon: the gradient square with the camera in white
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
    {{-- The app icon: Instagram's gradient square with the camera in white.
         The glyph used before came from an old icon set and was not the
         mark — measured at 3x its frame was taller than wide, thin at the top
         and a thick band at the bottom, with the lens off centre. Redrawn
         from primitives on the official proportions (corner 29%, camera
         frame 57%, lens 27.5%), the gradient in two layers: yellow to orange
         to magenta from the bottom-left, and the blue-violet wash from the
         top-left. Filled rather than outlined, so it carries the same weight
         as the Facebook disc beside it. --}}
    <svg viewBox="0 0 24 24" focusable="false">
      <defs>
        <radialGradient id="{{ $gid }}a" gradientUnits="userSpaceOnUse" cx="7.3" cy="23.5" r="23">
          <stop offset="0"   stop-color="#FFDD55"/>
          <stop offset="0.1" stop-color="#FFDD55"/>
          <stop offset="0.5" stop-color="#FF543E"/>
          <stop offset="1"   stop-color="#C837AB"/>
        </radialGradient>
        <radialGradient id="{{ $gid }}b" gradientUnits="userSpaceOnUse" cx="-1.4" cy="3.4" r="10.5"
                        gradientTransform="matrix(.2 1 -4.1 .8 12.8 1)">
          <stop offset="0"    stop-color="#3771C8"/>
          <stop offset="0.13" stop-color="#3771C8"/>
          <stop offset="1"    stop-color="#6600FF" stop-opacity="0"/>
        </radialGradient>
      </defs>
      <rect x="2" y="2" width="20" height="20" rx="5.8" fill="url(#{{ $gid }}a)"/>
      <rect x="2" y="2" width="20" height="20" rx="5.8" fill="url(#{{ $gid }}b)"/>
      <g fill="none" stroke="#FFFFFF" stroke-width="1.75">
        <rect x="6.3" y="6.3" width="11.4" height="11.4" rx="3.4"/>
        <circle cx="12" cy="12" r="2.75"/>
      </g>
      <circle cx="15.3" cy="8.7" r="0.85" fill="#FFFFFF"/>
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
