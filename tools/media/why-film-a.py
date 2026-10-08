#!/usr/bin/env python3
"""Film A of /por-que-nosotros, cut as a film (python3 stdlib + ffmpeg only).

  python3 tools/media/why-film-a.py SRC OUTDIR [--only p|d] [--draft]

1. Stabilise (vidstab, two passes): the gimbal already kills the shake; what is
   left is the walk, a 1-2 Hz nod of +-3..10 px. Smoothed over 1 s it glides.
2. Speed ramps. Each chapter is one camera move from a held frame to the next
   held frame: source time follows smoothstep in output time, so the move
   starts from rest, runs at ~1.5x its mean speed in the middle and comes to
   rest on the hold. Below 1x the frames are blended (framerate), above 2x a
   1-2-1 shutter (tmix) gives the motion blur a real camera would have.
3. Framing. Each output (p upright phone, d 16:9) has its own window per held
   frame (centre x, centre y, height in source px), moved on the same curve as
   the time, so reframing and dolly are one gesture (perspective, sub-pixel).
4. Grade: one curve and colour for the film, the white wall pulled down to a
   cool slate where it is bright AND high in the frame (a luma key times a
   vertical ramp: a per-row tone curve, no spatial key, so no halo on the cars),
   then the vignette that carries the words.
"""
import json, os, subprocess, sys, tempfile

SRC, OUT = sys.argv[1], sys.argv[2]
ONLY = sys.argv[sys.argv.index('--only') + 1] if '--only' in sys.argv else None
DRAFT = '--draft' in sys.argv
STAB = os.environ.get('STAB')  # reuse a stabilised intermediate

# ---- the cut: (source end of the chapter, output length) — holds at the ends ----
CH = [(3.40, 2.2), (8.00, 2.6), (12.95, 2.8), (18.45, 3.0), (24.55, 3.2)]
# windows at each hold: frame 0, then the end of each chapter.  (cx, cy, h) in source px
WIN = {
  #     title           cabrio nose      C-Class star     Octavia grille   2 Series face    the row, hills
  'p': [(640, 540, 1000), (830, 560, 1000), (930, 540, 1000), (1180, 560, 1000), (840, 575, 1000), (1400, 540, 1000)],
  'd': [(980, 560, 920),  (1100, 600, 900), (1100, 600, 880), (1180, 640, 760), (900, 600, 880), (960, 560, 960)],
}
SIZE = {'p': (608, 1080), 'd': (1600, 900)}
ASPECT = {'p': 608 / 1080, 'd': 16 / 9}

def chapters():
    s0, o0, out = 0.0, 0.0, []
    for s1, d in CH:
        out.append((s0, s1, o0, d)); s0, o0 = s1, o0 + d
    return out

def f(x):
    return ('%.5f' % x).rstrip('0').rstrip('.')

def remap_expr():
    """output time for source time T (setpts)."""
    e = None
    for s0, s1, o0, d in reversed(chapters()):
        y = '(clip((T-%s)/%s,0,1))' % (f(s0), f(s1 - s0))
        seg = '(%s+%s*(0.5-sin(asin(1-2*%s)/3)))' % (f(o0), f(d), y)
        e = seg if e is None else 'if(lt(T,%s),%s,%s)' % (f(s1), seg, e)
    return e

def blur_ranges(th=2.0):
    """source-time ranges where the speed passes th (x real time)."""
    r = []
    for s0, s1, o0, d in chapters():
        L = s1 - s0; peak = 1.5 * L / d
        if peak <= th: continue
        # v(tau) = 6 tau (1-tau) L/d > th  ->  tau in (a, 1-a); source P(tau)
        import math
        k = th * d / (6 * L); a = (1 - math.sqrt(1 - 4 * k)) / 2
        P = lambda t: t * t * (3 - 2 * t)
        r.append((s0 + L * P(a), s0 + L * P(1 - a)))
    return r

def win_expr(kind, comp):
    """window parameter (0 cx, 1 cy, 2 h) at output time t = in/60."""
    W = WIN[kind]; e = None
    for i, (s0, s1, o0, d) in reversed(list(enumerate(chapters()))):
        a, b = W[i][comp], W[i + 1][comp]
        u = 'clip((in/60-%s)/%s,0,1)' % (f(o0), f(d))
        seg = '(%s+%s*%s*%s*(3-2*%s))' % (f(a), f(b - a), u, u, u) if a != b else f(a)
        e = seg if e is None else 'if(lt(in/60,%s),%s,%s)' % (f(o0 + d), seg, e)
    return e

def persp(kind):
    cx, cy, h = (win_expr(kind, i) for i in range(3))
    w = '(%s)*%s' % (h, f(ASPECT[kind]))
    X0, X1 = '(%s-%s/2)' % (cx, w), '(%s+%s/2)' % (cx, w)
    Y0, Y1 = '(%s-(%s)/2)' % (cy, h), '(%s+(%s)/2)' % (cy, h)
    return "perspective=x0='%s':y0='%s':x1='%s':y1='%s':x2='%s':y2='%s':x3='%s':y3='%s':interpolation=cubic:eval=frame" % (
        X0, Y0, X1, Y0, X0, Y1, X1, Y1)

def run(cmd):
    print('+', ' '.join(cmd)[:400], file=sys.stderr)
    subprocess.run(cmd, check=True)

# ---- the grade (all outputs; masks per output size) ----------------------------
BASE = ("curves=master='0/0.004 0.06/0.03 0.25/0.17 0.5/0.43 0.75/0.72 0.9/0.89 1/0.98',"
        "colorbalance=rs=-0.02:bs=0.035:rm=-0.005:bm=0.012:rh=0.015:bh=-0.01,"
        "huesaturation=saturation=-0.6:colors=g+y,vibrance=intensity=0.12")
# the wall where it is bright: down to a cool slate
DARK = ("curves=master='0/0 0.4/0.12 0.7/0.19 1/0.24',hue=s=0.2,"
        "colorbalance=bs=0.06:bm=0.07:rm=-0.03:gm=-0.01:bh=0.05:rh=-0.03")
KEY = (0.30, 0.72)            # luma that counts as wall: smoothstep between
RAMP = (0.0, 0.66, 0.94)      # ... times a ramp: full from the top, none from 66% down
ND = (0.0, 0.55, 0.28)        # graduated ND: 28% transmission at the top, clear from 55%
# the vignette under the words (as before): bottom strength, left strength (wide)
VIG = {'p': (0.5, 0), 'd': (0.55, 0.55)}

def sm(v, a, b):
    c = 'clip((%s-%s)/(%s-%s),0,1)' % (v, f(a), f(b), f(a))
    return '(%s*%s*(3-2*%s))' % (c, c, c)

def masks(kind, tmp):
    w, h = SIZE[kind]; out = {}
    def mk(name, expr):
        pth = os.path.join(tmp, '%s-%s.png' % (kind, name))
        run(['ffmpeg', '-v', 'error', '-y', '-f', 'lavfi', '-i', 'color=black:s=%dx%d' % (w, h), '-frames:v', '1',
             '-vf', "format=gray16le,geq=lum='%s'" % expr, pth]); out[name] = pth
    mk('ramp', '65535*%s*(1-%s)' % (f(RAMP[2]), sm('Y/H', RAMP[0], RAMP[1])))
    mk('nd', '65535*(%s+%s*%s)' % (f(ND[2]), f(1 - ND[2]), sm('Y/H', ND[0], ND[1])))
    mk('desat', '65535*(1-%s)' % sm('Y/H', ND[0], ND[1]))
    b, l = VIG[kind]
    S = lambda x: '(clip(%s,0,1)*clip(%s,0,1)*(3-2*clip(%s,0,1)))' % (x, x, x)
    if w < h:
        e = 'clip(1-%s*%s*(1-0.3*X/W)-0.22*((X/W-0.55)*(X/W-0.55)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)' % (f(b), S('(Y/H-0.36)/0.5'))
    else:
        e = 'clip(1-%s*%s*(1-%s*%s)-0.2*((X/W-0.6)*(X/W-0.6)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)' % (f(b), S('(Y/H-0.38)/0.48'), f(l), S('(X/W-0.3)/0.6'))
    mk('vig', '65535*' + e)
    return out

def chain(kind, src, m, i0):
    """filtergraph for one output from [src]; mask inputs start at index i0."""
    w, h = SIZE[kind]; k = kind
    sk = sm('val/65535', KEY[0], KEY[1])
    return (
      f"[{src}]{persp(k)},scale={w}:{h}:flags=lanczos,format=gbrp16le,split[{k}a][{k}k0];"
      f"[{k}a]{BASE},split[{k}b][{k}d0];[{k}d0]{DARK}[{k}d];"
      f"[{k}k0]format=gray16le,lut=y='65535*{sk}'[{k}k];[{i0}]format=gray16le[{k}r];"
      f"[{k}k][{k}r]blend=all_mode=multiply:shortest=1,format=gbrp16le[{k}m];"
      f"[{k}b][{k}d][{k}m]maskedmerge,split[{k}g][{k}g0];[{k}g0]hue=s=0[{k}gd];"
      f"[{i0+2}]format=gray16le,format=gbrp16le[{k}dm];[{k}g][{k}gd][{k}dm]maskedmerge[{k}g2];"
      f"[{i0+1}]format=gbrp16le[{k}n];[{k}g2][{k}n]blend=all_mode=multiply:shortest=1[{k}g3];"
      f"[{i0+3}]format=gbrp16le[{k}v];[{k}g3][{k}v]blend=all_mode=multiply:shortest=1,"
      f"format=yuv444p16le,unsharp=5:5:0.25:5:5:0,scale=out_range=tv:out_color_matrix=bt709,format=yuv420p[{k}out]")

def build(stab, kinds, tmp, t_end=None):
    ends = [o0 + d for s0, s1, o0, d in chapters()]
    blur = '+'.join("between(t,%s,%s)" % (f(a), f(b)) for a, b in blur_ranges())
    pre = (f"[0]trim=end={f(CH[-1][0] + 1/120)},tmix=frames=3:weights='1 2 1':enable='{blur}',"
           f"setpts='({remap_expr()})/TB',"
           f"framerate=fps=60:interp_start=0:interp_end=255:scene=100,trim=end={f(ends[-1] + 1/120)},setpts=PTS-STARTPTS")
    ends = [o0 + d for s0, s1, o0, d in chapters()]
    inputs = ['-i', stab]; graph = [pre + ('[r]' if len(kinds) == 1 else ',split=%d%s' % (len(kinds), ''.join('[r%s]' % k for k in kinds)))]
    outs = []
    for k in kinds:
        m = masks(k, tmp); i0 = inputs.count('-i')
        for n in ('ramp', 'nd', 'desat', 'vig'):
            inputs += ['-loop', '1', '-framerate', '60', '-t', f(ends[-1] + 0.05), '-i', m[n]]
        graph.append(chain(k, 'r' if len(kinds) == 1 else 'r' + k, m, i0))
    fc = ';'.join(graph)
    return inputs, fc, ends

def encode(stab, outdir, kinds):
    tmp = tempfile.mkdtemp()
    inputs, fc, ends = build(stab, kinds, tmp)
    keys = ','.join(f(e) for e in ends[:-1])
    cmd = ['ffmpeg', '-v', 'error', '-y'] + inputs + ['-filter_complex', fc]
    for k in kinds:
        crf = {'p': CRF_P, 'd': CRF_D}[k]
        cmd += ['-map', '[%sout]' % k, '-an', '-shortest', '-c:v', 'libx264', '-profile:v', 'high', '-preset', 'veryfast' if DRAFT else 'slow',
                '-tune', 'film', '-crf', str(crf), '-g', '120', '-keyint_min', '60', '-bf', '2', '-force_key_frames', keys,
                '-color_range', 'tv', '-colorspace', 'bt709', '-color_primaries', 'bt709', '-color_trc', 'bt709',
                '-movflags', '+faststart', os.path.join(outdir, 'a-%s.mp4' % k)]
    run(cmd)
    return ends

CRF_P = int(os.environ.get('CRF_P', 22)); CRF_D = int(os.environ.get('CRF_D', 24))

def stabilise(src, tmp):
    trf = os.path.join(tmp, 'a.trf'); out = os.path.join(tmp, 'stab.mp4')
    run(['ffmpeg', '-v', 'error', '-y', '-i', src, '-vf', 'vidstabdetect=shakiness=6:accuracy=15:result=' + trf, '-f', 'null', '-'])
    run(['ffmpeg', '-v', 'error', '-y', '-i', src, '-an', '-vf',
         'vidstabtransform=input=%s:smoothing=%d:optalgo=gauss:optzoom=0:zoom=0:crop=black:interpol=bicubic,'
         'scale=in_range=full:out_range=tv:in_color_matrix=bt709:out_color_matrix=bt709,format=yuv444p10le' % (trf, SMOOTH),
         '-c:v', 'libx264', '-preset', 'fast', '-crf', '6', '-g', '30', out])
    return out
SMOOTH = int(os.environ.get('SMOOTH', 60))

def stills(stab, outdir, kinds):
    """the held frames only, through the same chain: for composing and grading."""
    tmp = tempfile.mkdtemp(); holds = [0.0] + [c[0] for c in CH]
    for k in kinds:
        m = masks(k, tmp)
        for i, t in enumerate(holds):
            saved = WIN[k]; WIN[k] = [saved[i]] * len(saved)
            fc = chain(k, 'r', m, 1).replace('[%sout]' % k, ',format=rgb24[%sout]' % k)
            WIN[k] = saved
            run(['ffmpeg', '-v', 'error', '-y', '-ss', f(max(0, t - 0.001)), '-i', stab] +
                sum([['-i', m[n]] for n in ('ramp', 'nd', 'desat', 'vig')], []) +
                ['-filter_complex', '[0]trim=end_frame=1,setpts=PTS-STARTPTS[r];' + fc, '-map', '[%sout]' % k, '-frames:v', '1',
                 os.path.join(outdir, '%s-hold%d.png' % (k, i))])

if __name__ == '__main__':
    os.makedirs(OUT, exist_ok=True)
    stab = STAB or stabilise(SRC, tempfile.mkdtemp())
    kinds = [ONLY] if ONLY else ['p', 'd']
    if '--stills' in sys.argv: stills(stab, OUT, kinds); sys.exit()
    ends = encode(stab, OUT, kinds)
    print(json.dumps({'ends': [round(e, 3) for e in ends]}))
