#!/bin/bash
# The films of /por-que-nosotros (public/js/why.js plays them chapter by chapter).
# From his original DJI footage (1920x1080, 60 fps, full-range BT.709), read only.
#   storage/app/public/why/film/<a|b>-p.mp4   upright 608x1080 (phones)
#   storage/app/public/why/film/<a|b>-d.mp4   1600x900
#   <a|b>-<p|d>.webp                          the first frame (the poster)
# Both films are cut as one film by tools/media/why-film.py (2026-10-08): stabilised
# (vidstab, 1 s), a speed ramp per chapter (from rest, through the move, to rest
# on a composed held frame), their own framing per held frame for phone and desk,
# one grade (deep blacks on the page's #05080F, lit paint, greens quieted, the
# bright background pulled to slate where it is high, a light ND from the top,
# the vignette that carries the words). H.264 High, a keyframe on every chapter end
# (the times in the view's data-ends), faststart.
#   A  the row from the front, 60 fps, CRF 22/24.       ends 2.2,4.8,7.6,10.6,13.8
#   B  the same cars from behind, 30 fps, CRF 23/24 capped at 1.6 / 3 Mb/s (at
#      60 fps / 6.6 Mb/s it stalled on the client's connection). ends 2.6,5.2,7.8,10.6,13.6
#   FILMS="a" or FILMS="b" renders one of them (default both).
set -e
cd "$(dirname "$0")/../.."
SRC=${SRC:-/var/www/motorclass/photo_video_why_us}
OUT=${OUT:-storage/app/public/why/film}
FILMS=${FILMS:-"a b"}
TMP=$(mktemp -d)
mkdir -p "$OUT"
declare -A FILE=([a]=DJI_20260222_170706_112_video.mp4 [b]=DJI_20260222_173235_677_video.mp4)
for x in $FILMS; do
  TMPDIR=$TMP python3 tools/media/why-film.py $x "$SRC/${FILE[$x]}" "$OUT"
  # the poster: the first frame, the same picture the film starts on
  for k in p d; do
    ffmpeg -v error -y -i "$OUT/$x-$k.mp4" -frames:v 1 -vf "scale=in_range=tv:in_color_matrix=bt709,format=bgra" -c:v libwebp -quality 80 "$OUT/$x-$k.webp"
  done
done
chown -R www-data:www-data "$OUT"; rm -rf $TMP
