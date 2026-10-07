#!/bin/bash
# The films of /por-que-nosotros (public/js/why.js plays them chapter by chapter).
# From his original DJI footage (1920x1080, 60 fps, full-range BT.709), read only.
#   storage/app/public/why/film/<a|b>-p.mp4   upright 608x1080 crop (phones), native pixels
#   storage/app/public/why/film/<a|b>-d.mp4   1600x900, lanczos
# Twice the speed at 60 fps = every second source frame: an even cadence, no judder.
# A keyframe on every chapter end (the times in the view's data-ends), so a dissolve
# back to a chapter lands at once. H.264 High, CRF 22 (phone) / 24 (desk), faststart.
# Grade, baked in. Film A (the opening, white words over it) is darker and moodier:
# mids ~0.6 stop down, highlights on paint and chrome kept, blacks lifted to 0.015,
# cooler shadows / warmer highlights, and a soft vignette multiplied in, deepest
# where the words sit (bottom, and the left on wide screens). Film B: the original
# grade (S-curve, warm mids, +10% vibrance) with a lighter version of the same
# vignette. Light luma unsharp after the scale on both; no denoise (noise 0.8 levels).
set -e
SRC=${SRC:-/var/www/motorclass/photo_video_why_us}
OUT=${OUT:-storage/app/public/why/film}
TMP=$(mktemp -d)
A=$SRC/DJI_20260222_170706_112_video.mp4
B=$SRC/DJI_20260222_173235_677_video.mp4
C="in_range=full:in_color_matrix=bt709"
GA="curves=master='0/0.015 0.1/0.065 0.3/0.205 0.5/0.37 0.75/0.64 0.9/0.83 1/0.95',colorbalance=rs=-0.01:bs=0.03:rm=0.015:bm=-0.015:rh=0.02:bh=-0.02,vibrance=intensity=0.10"
GB="curves=master='0/0.02 0.07/0.055 0.25/0.21 0.5/0.47 0.75/0.75 0.92/0.91 1/0.97',colorbalance=rm=0.025:bm=-0.025:rh=0.008:bh=-0.012,vibrance=intensity=0.10"
SH="format=yuv444p,unsharp=5:5:0.25:5:5:0,scale=out_range=tv:out_color_matrix=bt709,format=yuv420p"
S(){ echo "(clip($1,0,1)*clip($1,0,1)*(3-2*clip($1,0,1)))"; }
mask() { # size strength-of-bottom strength-of-left out
  local w=${1%x*} h=${1#*x}
  if [ $w -lt $h ]; then e="255*clip(1-$2*$(S "(Y/H-0.36)/0.5")*(1-0.3*X/W)-0.22*((X/W-0.55)*(X/W-0.55)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)"
  else e="255*clip(1-$2*$(S "(Y/H-0.38)/0.48")*(1-$3*$(S "(X/W-0.3)/0.6"))-0.2*((X/W-0.6)*(X/W-0.6)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)"; fi
  ffmpeg -v error -y -f lavfi -i color=black:s=$1 -frames:v 1 -vf "format=gray,geq=lum='$e'" "$4"
}
mask 608x1080 0.58 0 $TMP/ap.png; mask 1600x900 0.6 0.55 $TMP/ad.png
mask 608x1080 0.3 0 $TMP/bp.png;  mask 1600x900 0.3 0.4 $TMP/bd.png
one() { # src seconds geometry grade mask crf keys out
  ffmpeg -v error -y -t $2 -i "$1" -loop 1 -i "$5" -an -filter_complex \
    "[0]setpts=PTS/2,fps=60,$3:$C,format=gbrp,$4[v];[1]format=gbrp[m];[v][m]blend=all_mode=multiply:shortest=1,$SH" \
    -c:v libx264 -profile:v high -preset slow -tune film -crf $6 -g 120 -keyint_min 60 -bf 2 -force_key_frames "$7" \
    -color_range tv -colorspace bt709 -color_primaries bt709 -color_trc bt709 -movflags +faststart "$8"
}
mkdir -p "$OUT"
PH="crop=608:1080:656:0,scale=608:1080"; DH="scale=1600:900:flags=lanczos"
KA="1.75,4,6.5,9.25"; KB="2.5,5.75,8.5,11.25"
one "$A" 24.6 "$PH" "$GA" $TMP/ap.png 22 $KA "$OUT/a-p.mp4" & one "$A" 24.6 "$DH" "$GA" $TMP/ad.png 24 $KA "$OUT/a-d.mp4" &
one "$B" 30 "$PH" "$GB" $TMP/bp.png 22 $KB "$OUT/b-p.mp4" & one "$B" 30 "$DH" "$GB" $TMP/bd.png 24 $KB "$OUT/b-d.mp4" & wait
# posters: the first frame of each, the same picture the film starts on
for f in a-p a-d b-p b-d; do ffmpeg -v error -y -i "$OUT/$f.mp4" -frames:v 1 -vf "scale=in_range=tv:in_color_matrix=bt709,format=bgra" -c:v libwebp -quality 80 "$OUT/$f.webp"; done
chown -R www-data:www-data "$OUT"; rm -rf $TMP
