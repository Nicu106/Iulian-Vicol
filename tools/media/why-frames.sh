#!/bin/bash
# The frame sequences of /por-que-nosotros (public/js/why.js), from his original
# DJI footage (1920x1080, 60 fps, full-range BT.709). Re-run only if the films change.
#   storage/app/public/why/seq/<a|b>/p   upright 608x1080 crop (phones), native pixels
#   storage/app/public/why/seq/<a|b>/d   1600x900, lanczos
# Grade, baked in (identical for both films): gentle S-curve with deep-not-crushed
# blacks (floor 0.02) and a highlight shoulder (ceiling 0.975, no clipped sky), warm
# mids, +10% vibrance, light luma unsharp after the scale. No denoise: measured noise
# on a flat wall is 0.8 levels. WebP via libwebp from BGRA (libwebp does its own
# colour conversion, so colours match the originals).
set -e
SRC=${SRC:-/var/www/motorclass/photo_video_why_us}     # read only
OUT=${OUT:-storage/app/public/why/seq}
A=$SRC/DJI_20260222_170706_112_video.mp4
B=$SRC/DJI_20260222_173235_677_video.mp4
C="in_range=full:in_color_matrix=bt709"
G="curves=master='0/0.02 0.07/0.055 0.25/0.225 0.5/0.5 0.75/0.78 0.92/0.925 1/0.975',colorbalance=rm=0.025:bm=-0.025:rh=0.008:bh=-0.012,vibrance=intensity=0.10,format=yuv444p,unsharp=5:5:0.25:5:5:0"
PH="crop=608:1080:656:0,scale=608:1080:$C,$G"
DH="scale=1600:900:flags=lanczos:$C,$G"
one() { # film src seconds set frames filters quality
  local o=$OUT/$1/$4; rm -rf "$o"; mkdir -p "$o"
  ffmpeg -v error -y -ss 0 -t $3 -i "$2" -vf "fps=$(echo "scale=6; $5/$3" | bc):round=near,$6,format=bgra" \
    -start_number 0 -c:v libwebp -quality $7 -compression_level 6 -preset photo -f image2 "$o/%03d.webp"
}
one a "$A" 24.6 p 120 "$PH" 78 & one b "$B" 30 p 144 "$PH" 72 & one a "$A" 24.6 d 120 "$DH" 70 & wait
one b "$B" 30 d 144 "$DH" 62
chown -R www-data:www-data "$OUT"
