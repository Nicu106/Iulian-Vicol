#!/bin/bash
# The films of /por-que-nosotros (public/js/why.js plays them chapter by chapter).
# From his original DJI footage (1920x1080, 60 fps, full-range BT.709), read only.
#   storage/app/public/why/film/<a|b>-p.mp4   upright 608x1080 (phones)
#   storage/app/public/why/film/<a|b>-d.mp4   1600x900
#   <a|b>-<p|d>.webp                          the first frame (the poster)
# H.264 High, CRF 22 (phone) / 24 (desk), a keyframe on every chapter end (the
# times in the view's data-ends), faststart.
#
# Film A (the opening) is cut as a film by tools/media/why-film-a.py (2026-10-08):
# stabilised (vidstab, 1 s), a speed ramp per chapter (from rest, through the
# move, to rest on a composed held frame), its own framing per held frame for
# phone and desk, and a night grade — the white wall pulled down to slate where it
# is bright and high, a graduated ND from the top, deep blacks, lit paint, and the
# vignette that carries the words. Its chapter ends: 2.2,4.8,7.6,10.6,13.8.
#
# Film B: twice the speed at 60 fps (every second source frame), a centre crop
# for phones, the same grade family made lighter — it shows what you will find,
# so nothing is hidden: greens quieted as in A, a graduated ND from the top
# (50% at the top edge) baked into its vignette, which is deepest where the words
# sit. Light luma unsharp after the scale; no denoise.
#   FILMS="a" or FILMS="b" renders one of them (default both).
set -e
cd "$(dirname "$0")/../.."
SRC=${SRC:-/var/www/motorclass/photo_video_why_us}
OUT=${OUT:-storage/app/public/why/film}
FILMS=${FILMS:-"a b"}
TMP=$(mktemp -d)
A=$SRC/DJI_20260222_170706_112_video.mp4
B=$SRC/DJI_20260222_173235_677_video.mp4
mkdir -p "$OUT"

if [[ " $FILMS " == *" a "* ]]; then
  TMPDIR=$TMP python3 tools/media/why-film-a.py "$A" "$OUT"
fi

if [[ " $FILMS " == *" b "* ]]; then
C="in_range=full:in_color_matrix=bt709"
GB="curves=master='0/0.012 0.07/0.045 0.25/0.2 0.5/0.46 0.75/0.75 0.92/0.91 1/0.97',colorbalance=rs=-0.01:bs=0.02:rm=0.015:bm=-0.01:rh=0.008:bh=-0.012,huesaturation=saturation=-0.45:colors=g+y,vibrance=intensity=0.08"
SH="format=yuv444p,unsharp=5:5:0.25:5:5:0,scale=out_range=tv:out_color_matrix=bt709,format=yuv420p"
S(){ echo "(clip($1,0,1)*clip($1,0,1)*(3-2*clip($1,0,1)))"; }
mask() { # size strength-of-bottom strength-of-left out — times the graduated ND
  local w=${1%x*} h=${1#*x} nd="(0.5+0.5*$(S "(Y/H)/0.5"))"
  if [ $w -lt $h ]; then e="255*$nd*clip(1-$2*$(S "(Y/H-0.36)/0.5")*(1-0.3*X/W)-0.22*((X/W-0.55)*(X/W-0.55)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)"
  else e="255*$nd*clip(1-$2*$(S "(Y/H-0.38)/0.48")*(1-$3*$(S "(X/W-0.3)/0.6"))-0.2*((X/W-0.6)*(X/W-0.6)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)"; fi
  ffmpeg -v error -y -f lavfi -i color=black:s=$1 -frames:v 1 -vf "format=gray,geq=lum='$e'" "$4"
}
mask 608x1080 0.3 0 $TMP/bp.png; mask 1600x900 0.3 0.4 $TMP/bd.png
one() { # src seconds geometry grade mask crf keys out
  ffmpeg -v error -y -t $2 -i "$1" -loop 1 -i "$5" -an -filter_complex \
    "[0]setpts=PTS/2,fps=60,$3:$C,format=gbrp,$4[v];[1]format=gbrp[m];[v][m]blend=all_mode=multiply:shortest=1,$SH" \
    -c:v libx264 -profile:v high -preset slow -tune film -crf $6 -g 120 -keyint_min 60 -bf 2 -force_key_frames "$7" \
    -color_range tv -colorspace bt709 -color_primaries bt709 -color_trc bt709 -movflags +faststart "$8"
}
PH="crop=608:1080:656:0,scale=608:1080"; DH="scale=1600:900:flags=lanczos"
KB="2.5,5.75,8.5,11.25"
one "$B" 30 "$PH" "$GB" $TMP/bp.png 22 $KB "$OUT/b-p.mp4" & one "$B" 30 "$DH" "$GB" $TMP/bd.png 24 $KB "$OUT/b-d.mp4" & wait
fi

# posters: the first frame of each, the same picture the film starts on
for x in $FILMS; do for k in p d; do
  ffmpeg -v error -y -i "$OUT/$x-$k.mp4" -frames:v 1 -vf "scale=in_range=tv:in_color_matrix=bt709,format=bgra" -c:v libwebp -quality 80 "$OUT/$x-$k.webp"
done; done
chown -R www-data:www-data "$OUT"; rm -rf $TMP
