#!/usr/bin/env python3
"""The films of /por-que-nosotros, each cut as a film (python3 stdlib + ffmpeg only).

  python3 tools/media/why-film.py a|b SRC OUTDIR [--only p|d] [--draft] [--stills]
  (STAB=<file> reuses a stabilised intermediate)

Both films get the same treatment, so they read as one film:
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
4. One grade for both: deep blacks, lit paint (an S-curve that keeps the
   speculars), greens quieted, the bright background pulled down to a cool
   slate where it is bright AND high in the frame (a luma key times a vertical
   ramp: a per-row tone curve, no spatial key, so no halo on the cars), a
   graduated ND from the top, the vignette that carries the words, and a black
   floor of exactly the page's #05080F, so a film dips into the page with no seam.
"""
import json, math, os, subprocess, sys, tempfile

FILM = sys.argv[1]; SRC, OUT = sys.argv[2], sys.argv[3]
ONLY = sys.argv[sys.argv.index('--only') + 1] if '--only' in sys.argv else None
DRAFT = '--draft' in sys.argv
STAB = os.environ.get('STAB')  # reuse a stabilised intermediate

# ---- the cuts: CH = (source end of the chapter, output length) — holds at the ends;
# WIN = the window at each hold (frame 0, then each chapter's end): (cx, cy, h) in source px
FILMS = {
  # film A, the row from the front (2026-10-08)
  'a': dict(fps=60, crf=(22, 24), rate=(None, None),
    CH=[(3.40, 2.2), (8.00, 2.6), (12.95, 2.8), (18.45, 3.0), (24.55, 3.2)],
    WIN={
      #     title           cabrio nose      C-Class star     Octavia grille   2 Series face    the row, hills
      'p': [(640, 540, 1000), (830, 560, 1000), (930, 540, 1000), (1180, 560, 1000), (840, 575, 1000), (1400, 540, 1000)],
      'd': [(980, 560, 920),  (1100, 600, 900), (1100, 600, 880), (1180, 640, 760), (900, 600, 880), (960, 560, 960)],
    }),
  # film B, the same cars from behind (2026-10-08, late): 30 fps and capped, so it
  # never stalls on a slow connection (at 60 fps / 6.6 Mb/s it did)
  'b': dict(fps=30, crf=(23, 24), rate=('1600k', '3000k'),
    CH=[(4.75, 2.6), (6.60, 2.6), (8.40, 2.6), (12.50, 2.8), (16.00, 3.0)],
    WIN={
      #     poster: cabrio   the cabrio square  its flank, wheel  the open cabin    the C-Class star  C-Class 3/4, cabrio behind
      'p': [(1330, 540, 1000), (710, 540, 1000), (860, 530, 1000), (700, 440, 820), (1120, 650, 780), (960, 540, 1000)],
      'd': [(1120, 520, 840),  (720, 520, 760),  (830, 500, 880),  (700, 380, 700), (1120, 700, 680), (960, 540, 960)],
    }),
}
F = FILMS[FILM]; CH, WIN, FPS = F['CH'], F['WIN'], F['fps']
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
        k = th * d / (6 * L); a = (1 - math.sqrt(1 - 4 * k)) / 2
        P = lambda t: t * t * (3 - 2 * t)
        r.append((s0 + L * P(a), s0 + L * P(1 - a)))
    return r

def win_expr(kind, comp):
    """window parameter (0 cx, 1 cy, 2 h) at output time t = in/FPS."""
    W = WIN[kind]; e = None; t = 'in/%d' % FPS
    for i, (s0, s1, o0, d) in reversed(list(enumerate(chapters()))):
        a, b = W[i][comp], W[i + 1][comp]
        u = 'clip((%s-%s)/%s,0,1)' % (t, f(o0), f(d))
        seg = '(%s+%s*%s*%s*(3-2*%s))' % (f(a), f(b - a), u, u, u) if a != b else f(a)
        e = seg if e is None else 'if(lt(%s,%s),%s,%s)' % (t, f(o0 + d), seg, e)
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

# ---- the grade (both films; masks per output size) ------------------------------
# 2026-10-08 (late): film A read murky on the black page — paint dim, the whole
# frame grey under a heavy ND, vignette and runtime shade. Now: a real toe (deep
# blacks), mids where the footage has them, the speculars kept (the gloss), the
# paint's blue and the lamps' red let through; the wall and the ND lighter.
G = dict(
  base=("curves=master='0/0 0.05/0.022 0.18/0.12 0.4/0.36 0.6/0.6 0.8/0.83 0.93/0.95 1/1',"
        "colorbalance=rs=-0.02:bs=0.03:rm=-0.005:bm=0.01:rh=0.012:bh=-0.006,"
        "huesaturation=saturation=-0.6:colors=g+y,vibrance=intensity=0.18"),
  # the bright background where it is high: down to a cool slate
  dark=("curves=master='0/0 0.4/0.2 0.7/0.31 1/0.4',hue=s=0.25,"
        "colorbalance=bs=0.05:bm=0.06:rm=-0.025:gm=-0.01:bh=0.04:rh=-0.025"),
  key=(0.30, 0.72),          # luma that counts as background: smoothstep between
  ramp=(0.0, 0.62, 0.9),     # ... times a ramp: full from the top, none from 62% down
  nd=(0.0, 0.5, 0.5),        # graduated ND: 50% transmission at the top, clear from 50%
  vig={'p': (0.36, 0), 'd': (0.4, 0.5)},   # under the words: bottom strength, left strength
  floor=(5, 8, 15),          # the page's black, #05080F
)
for k, v in json.loads(os.environ.get('GRADE', '{}')).items():   # for trying a grade: GRADE='{"nd":[0,0.5,0.6]}'
    G[k] = tuple(v) if isinstance(v, list) else v

def sm(v, a, b):
    c = 'clip((%s-%s)/(%s-%s),0,1)' % (v, f(a), f(b), f(a))
    return '(%s*%s*(3-2*%s))' % (c, c, c)

def masks(kind, tmp):
    w, h = SIZE[kind]; out = {}
    def mk(name, expr):
        pth = os.path.join(tmp, '%s-%s.png' % (kind, name))
        run(['ffmpeg', '-v', 'error', '-y', '-f', 'lavfi', '-i', 'color=black:s=%dx%d' % (w, h), '-frames:v', '1',
             '-vf', "format=gray16le,geq=lum='%s'" % expr, pth]); out[name] = pth
    R, N = G['ramp'], G['nd']
    mk('ramp', '65535*%s*(1-%s)' % (f(R[2]), sm('Y/H', R[0], R[1])))
    mk('nd', '65535*(%s+%s*%s)' % (f(N[2]), f(1 - N[2]), sm('Y/H', N[0], N[1])))
    mk('desat', '65535*(1-%s)' % sm('Y/H', N[0], N[1]))
    b, l = G['vig'][kind]
    S = lambda x: '(clip(%s,0,1)*clip(%s,0,1)*(3-2*clip(%s,0,1)))' % (x, x, x)
    if w < h:
        e = 'clip(1-%s*%s*(1-0.3*X/W)-0.16*((X/W-0.55)*(X/W-0.55)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)' % (f(b), S('(Y/H-0.36)/0.5'))
    else:
        e = 'clip(1-%s*%s*(1-%s*%s)-0.14*((X/W-0.6)*(X/W-0.6)+(Y/H-0.4)*(Y/H-0.4))*2.2,0.3,1)' % (f(b), S('(Y/H-0.38)/0.48'), f(l), S('(X/W-0.3)/0.6'))
    mk('vig', '65535*' + e)
    return out

def chain(kind, src, i0):
    """filtergraph for one output from [src]; mask inputs start at index i0."""
    w, h = SIZE[kind]; k = kind
    sk = sm('val/65535', G['key'][0], G['key'][1])
    fl = G['floor']
    return (
      f"[{src}]{persp(k)},scale={w}:{h}:flags=lanczos,format=gbrp16le,split[{k}a][{k}k0];"
      f"[{k}a]{G['base']},split[{k}b][{k}d0];[{k}d0]{G['dark']}[{k}d];"
      f"[{k}k0]format=gray16le,lut=y='65535*{sk}'[{k}k];[{i0}]format=gray16le[{k}r];"
      f"[{k}k][{k}r]blend=all_mode=multiply:shortest=1,format=gbrp16le[{k}m];"
      f"[{k}b][{k}d][{k}m]maskedmerge,split[{k}g][{k}g0];[{k}g0]hue=s=0[{k}gd];"
      f"[{i0+2}]format=gray16le,format=gbrp16le[{k}dm];[{k}g][{k}gd][{k}dm]maskedmerge[{k}g2];"
      f"[{i0+1}]format=gbrp16le[{k}n];[{k}g2][{k}n]blend=all_mode=multiply:shortest=1[{k}g3];"
      f"[{i0+3}]format=gbrp16le[{k}v];[{k}g3][{k}v]blend=all_mode=multiply:shortest=1,"
      f"colorlevels=romin={fl[0]/255:.5f}:gomin={fl[1]/255:.5f}:bomin={fl[2]/255:.5f},"
      f"format=yuv444p16le,unsharp=5:5:0.25:5:5:0,scale=out_range=tv:out_color_matrix=bt709,format=yuv420p[{k}out]")

MASKS = ('ramp', 'nd', 'desat', 'vig')

def build(stab, kinds, tmp):
    ends = [o0 + d for s0, s1, o0, d in chapters()]
    br = blur_ranges()
    blur = (",tmix=frames=3:weights='1 2 1':enable='%s'" % '+'.join("between(t,%s,%s)" % (f(a), f(b)) for a, b in br)) if br else ''
    pre = (f"[0]trim=end={f(CH[-1][0] + 1/120)}{blur},"
           f"setpts='({remap_expr()})/TB',"
           f"framerate=fps={FPS}:interp_start=0:interp_end=255:scene=100,trim=end={f(ends[-1] + 0.5/FPS)},setpts=PTS-STARTPTS")
    inputs = ['-i', stab]; graph = [pre + ('[r]' if len(kinds) == 1 else ',split=%d%s' % (len(kinds), ''.join('[r%s]' % k for k in kinds)))]
    for k in kinds:
        m = masks(k, tmp); i0 = inputs.count('-i')
        for n in MASKS:
            inputs += ['-loop', '1', '-framerate', str(FPS), '-t', f(ends[-1] + 0.05), '-i', m[n]]
        graph.append(chain(k, 'r' if len(kinds) == 1 else 'r' + k, i0))
    return inputs, ';'.join(graph), ends

def encode(stab, outdir, kinds):
    tmp = tempfile.mkdtemp()
    inputs, fc, ends = build(stab, kinds, tmp)
    keys = ','.join(f(e) for e in ends[:-1])
    cmd = ['ffmpeg', '-v', 'error', '-y'] + inputs + ['-filter_complex', fc]
    for k in kinds:
        i = 0 if k == 'p' else 1
        crf = int(os.environ.get('CRF_' + k.upper(), F['crf'][i])); rate = F['rate'][i]
        cap = ['-maxrate', rate, '-bufsize', rate] if rate else []
        cmd += ['-map', '[%sout]' % k, '-an', '-shortest', '-c:v', 'libx264', '-profile:v', 'high', '-preset', 'veryfast' if DRAFT else 'slow',
                '-tune', 'film', '-crf', str(crf)] + cap + ['-g', str(2 * FPS), '-keyint_min', str(FPS), '-bf', '2', '-force_key_frames', keys,
                '-color_range', 'tv', '-colorspace', 'bt709', '-color_primaries', 'bt709', '-color_trc', 'bt709',
                '-movflags', '+faststart', os.path.join(outdir, '%s-%s.mp4' % (FILM, k))]
    run(cmd)
    return ends

SMOOTH = int(os.environ.get('SMOOTH', 60))

def stabilise(src, tmp):
    trf = os.path.join(tmp, FILM + '.trf'); out = os.path.join(tmp, 'stab.mp4')
    t = ['-t', f(CH[-1][0] + 1)]   # only what the cut uses (and a second for the smoothing)
    run(['ffmpeg', '-v', 'error', '-y'] + t + ['-i', src, '-vf', 'vidstabdetect=shakiness=6:accuracy=15:result=' + trf, '-f', 'null', '-'])
    run(['ffmpeg', '-v', 'error', '-y'] + t + ['-i', src, '-an', '-vf',
         'vidstabtransform=input=%s:smoothing=%d:optalgo=gauss:optzoom=0:zoom=0:crop=black:interpol=bicubic,'
         'scale=in_range=full:out_range=tv:in_color_matrix=bt709:out_color_matrix=bt709,format=yuv444p10le' % (trf, SMOOTH),
         '-c:v', 'libx264', '-preset', 'fast', '-crf', '6', '-g', '30', out])
    return out

def stills(stab, outdir, kinds):
    """the held frames only, through the same chain: for composing and grading."""
    tmp = tempfile.mkdtemp(); holds = [0.0] + [c[0] for c in CH]
    for k in kinds:
        m = masks(k, tmp)
        for i, t in enumerate(holds):
            saved = WIN[k]; WIN[k] = [saved[i]] * len(saved)
            fc = chain(k, 'r', 1).replace('[%sout]' % k, ',format=rgb24[%sout]' % k)
            WIN[k] = saved
            run(['ffmpeg', '-v', 'error', '-y', '-ss', f(max(0, t - 0.001)), '-i', stab] +
                sum([['-i', m[n]] for n in MASKS], []) +
                ['-filter_complex', '[0]trim=end_frame=1,setpts=PTS-STARTPTS[r];' + fc, '-map', '[%sout]' % k, '-frames:v', '1',
                 os.path.join(outdir, '%s-%s-hold%d.png' % (FILM, k, i))])

if __name__ == '__main__':
    os.makedirs(OUT, exist_ok=True)
    stab = STAB or stabilise(SRC, tempfile.mkdtemp())
    kinds = [ONLY] if ONLY else ['p', 'd']
    if '--stills' in sys.argv: stills(stab, OUT, kinds); sys.exit()
    ends = encode(stab, OUT, kinds)
    print(json.dumps({'film': FILM, 'ends': [round(e, 3) for e in ends]}))
