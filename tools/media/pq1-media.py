#!/usr/bin/env python3
"""/muestras/por-que/1 "Cine" — the pictures, made once from the client's photos.

Run with the rembg venv (rembg, pillow, numpy, scipy):
    $VENV/bin/python tools/media/pq1-media.py [--only NAME ...] [--ext | --ext-only] [--keep-mask] [--raw]
    (--keep-mask: re-grade the heroes on their existing, touched-up masks;
     --raw: no grade, the photographs as shot)
    $VENV/bin/python tools/media/pq1-media.py --map km equip     (where words can stand)

Writes storage/app/public/why/pq1/ (then chown -R www-data:www-data it):

  <k>-car.webp    the car cut out (WebP with alpha), same 2400x1800 frame as the
                  photo, so it sits exactly over it. isnet-general-use; the mask
                  is touched up: holes inside the body filled (a red sliver at
                  the C-Class bumper), stray islands dropped, edge feathered 1px.
  <k>-wall.jpg    the photo with the car taken out (a rough fill: normalised
                  diffusion from the edge, plus the asphalt's own grain). The
                  car layer scales more than the wall as you scroll; whatever
                  the scaled car no longer covers shows wall, never a ghost car.
  <k>-p.jpg       phone frame: a 0.56:1 upright crop around the frame's focal
                  point (960x1714), so a phone downloads only what it shows.
  <k>-d.jpg       a cover frame's whole photograph for wider screens, graded.
  <k>-ph.jpg      an anchored frame's photograph (show, me, the record), graded.
  <k>-ext.jpg     the wall carried on around an anchored frame (see ext()).

Everything is printed through one grade (see GRADE below): one film.

Every crop has its own focal point, chosen by looking at the photo (no centre
crop). The words are placed where the luminance grid says the frame is calm.
"""
import sys, os
import numpy as np
from PIL import Image, ImageFilter
from scipy import ndimage as ndi

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
SRC = os.path.join(ROOT, 'storage/app/public/why/photos')
OUT = os.path.join(ROOT, 'storage/app/public/why/pq1')

HERO = {   # key: photo
    'open': '21_IMG_1274',
    'sel':  '44_IMG_1871',
    'end':  '20_IMG_1273',
}
# phone crops: key -> (photo, focal x as a fraction of the width)
PHONE = {
    'revw':  ('33_IMG_1739', 0.70),
    'rev':   ('35_IMG_1750', 0.52),
    'prep':  ('40_IMG_1862', 0.26),
    'disf':  ('24_IMG_1280', 0.58),
    'mot':   ('36_IMG_1757', 0.50),
    'color': ('22_IMG_1276', 0.30),
    'equip': ('30_IMG_1350', 0.74),
    'cuid':  ('05_DJI_20250318_103924_729', 0.46),
    'km':    ('25_IMG_1283', 0.34),
    'acc':   ('55_IMG_2853', 0.36),
    'ext':   ('14_DJI_20260329_154326_944', 0.70),
    'int':   ('18_DJI_20260329_155058_223', 0.35),
    'desp':  ('16_DJI_20260329_154527_712', 0.40),
    'enc':   ('17_DJI_20260329_154542_802', 0.52),
    'noves': ('46_IMG_1877', 0.225),
    'conf':  ('48_IMG_1880', 0.62),
}
CROP_W = 1008   # of a 2400x1800 frame: 0.56:1 — a phone is 0.46-0.56
CROP_OUT = (960, 1714)   # stored size: 1.5x up on a 3x phone, inside the frame's 1.06 settle;
                         # measured: the page's frames 3.3 MB -> see the commit

# ---- the grade: one film ------------------------------------------------------
# Every photograph of the page is printed on the same stock. The white wall of
# each is the grey card: its robust median (top 12% of the frame, brightest 60%)
# is brought, in linear light, to ONE wall (sRGB 208,207,202: neutral with a
# breath of warmth, between the neutral walls and the client's welcome, which is
# warmer and stays exactly as approved), exposure capped at +-0.35 EV. A frame
# with no wall (interiors, close details) takes the gains of its own session's
# walls. Then one tone curve for all: a soft shoulder (white leather and sky
# never clip; print white 250), a gentle S in the mids, the blacks lifted off
# zero to the page's own night (#05080F at 80%) so a photo's shadows and the
# black around it are the same black, and the highlights a little less
# saturated, as film does. No grain in the files: it is what WebP spends bytes
# on (measured -20% when it was taken out), and the screen should not look dirty.
WALL_TARGET = np.array([208, 207, 202], np.float32)
SESSION = {   # photo number -> the walls whose gains it takes
    5: [2, 3, 4], 18: [14, 17], 24: [20, 21, 22, 27], 25: [20, 21, 22, 27],
    30: [20, 21, 22, 27], 35: [33], 36: [33],
}
WB_ONLY = {46}               # a close shot whose wall is lit brighter: colour, not exposure
TONE_ONLY = {57, 'portrait'} # his portrait at dusk: the sky is not a wall
GRADE = '--raw' not in sys.argv

def _lin(a): return np.where(a <= .04045, a / 12.92, ((a + .055) / 1.055) ** 2.4)
def _srgb(a):
    a = np.clip(a, 0, 1); return np.where(a <= .0031308, a * 12.92, 1.055 * a ** (1 / 2.4) - .055)
_Y = np.array([.2126, .7152, .0722], np.float32)

def _raw(name):
    path = name if name.startswith('/') else os.path.join(SRC, name + '.jpg')
    return Image.open(path).convert('RGB')

def _wall_gains(name):
    a = np.asarray(_raw(name).resize((600, 450), Image.BILINEAR)).astype(np.float32) / 255
    r = _lin(a[:54]).reshape(-1, 3); y = r @ _Y
    w = np.median(r[y > np.percentile(y, 40)], 0)
    g = _lin(WALL_TARGET / 255) / w
    ev = np.log2(g @ _Y)
    if abs(ev) > .35: g = g / 2 ** (ev - np.sign(ev) * .35)
    return g

def _num(name):
    b = os.path.basename(name)
    return 'portrait' if b.startswith('portrait') else int(b[:2])

def gains(name):
    n = _num(name)
    if n in TONE_ONLY: return np.ones(3, np.float32)
    if n in SESSION:
        import glob
        return np.mean([_wall_gains(os.path.basename(glob.glob(os.path.join(SRC, '%02d_*.jpg' % k))[0])[:-4]) for k in SESSION[n]], 0)
    g = _wall_gains(name)
    if n in WB_ONLY: g = g / (g @ _Y)
    return g

NIGHT_BLACK = np.array([5, 8, 15], np.float32) / 255 * .8
def tone(rgb_lin):
    """the print: shoulder, S, lifted blacks, softer highlights (linear in, sRGB 0-1 out)"""
    L = rgb_lin
    k = .78; L = np.where(L > k, k + (1 - k) * (1 - np.exp(-(L - k) / (1 - k))), L)
    Y = L @ _Y
    sat = .97 - .14 * np.clip((Y - .45) / .5, 0, 1)            # highlights a little paler
    L = Y[..., None] + (L - Y[..., None]) * sat[..., None]
    v = _srgb(L)
    v = v + .10 * (v * v * (3 - 2 * v) - v)                     # a gentle S in the mids
    return NIGHT_BLACK + (250 / 255 - NIGHT_BLACK) * v          # blacks to the night, print white 250

_cache = {}
def load(name):
    if not GRADE: return _raw(name)
    if name in _cache: return _cache[name]
    a = _lin(np.asarray(_raw(name)).astype(np.float32) / 255) * gains(name)
    im = Image.fromarray((tone(a) * 255 + .5).clip(0, 255).astype(np.uint8))
    _cache[name] = im
    return im

def phone(key, name, fx):
    im = load(name)
    W, H = im.size
    x0 = int(round(fx * W - CROP_W / 2))
    x0 = max(0, min(W - CROP_W, x0))
    # a 3x3 median after the resize: the sensor's grain goes (it is what the WebP
    # spends its bytes on: -20% measured), the edges stay where they are
    im.crop((x0, 0, x0 + CROP_W, H)).resize(CROP_OUT, Image.LANCZOS).filter(ImageFilter.MedianFilter(3)).save(os.path.join(OUT, key + '-p.jpg'), quality=90, optimize=True)
    print(key, 'phone crop x0', x0)

def mask_for(im):
    from rembg import remove, new_session
    sess = new_session('isnet-general-use')
    rgba = remove(im, session=sess)
    return np.asarray(rgba)[:, :, 3].astype(np.float32) / 255.0

def touch_up(a):
    solid = a > 0.5
    lab, n = ndi.label(solid)
    if n > 1:   # keep the car: the largest piece
        sizes = ndi.sum(solid, lab, range(1, n + 1))
        solid = lab == (1 + int(np.argmax(sizes)))
    filled = ndi.binary_fill_holes(solid)
    holes = filled & ~solid
    print('   holes filled px:', int(holes.sum()), ' islands dropped:', n - 1)
    out = np.where(filled, np.maximum(a, holes.astype(np.float32)), 0.0)
    # inside the body, fully opaque; the soft edge stays as rembg gave it
    core = ndi.binary_erosion(filled, iterations=3)
    out[core] = 1.0
    # nothing outside the filled silhouette
    out[~ndi.binary_dilation(filled, iterations=2)] = 0.0
    return out

def clean_wall(im, a):
    """The photo with the car gone. Wall and asphalt are both even along a row,
    so each row is filled by interpolating between its own pixels left and right
    of the car, then given the asphalt's grain. Only slivers of it ever show."""
    rgb = np.asarray(im).astype(np.float32)
    hole = ndi.binary_dilation(a > 0.02, iterations=14)
    H, W, _ = rgb.shape
    fill = rgb.copy()
    xs = np.arange(W)
    for y in range(H):
        k = ~hole[y]
        if k.all():
            continue
        if not k.any():
            fill[y] = fill[y - 1]
            continue
        # the 24 known pixels nearest each side, averaged, so one speck of grit
        # does not paint a stripe across the car
        kx = xs[k]
        sm = np.stack([ndi.uniform_filter1d(rgb[y, :, c], 24) for c in range(3)], 1)
        for c in range(3):
            fill[y, ~k, c] = np.interp(xs[~k], kx, sm[k, c])
    fill = ndi.gaussian_filter(fill, (10, 2, 0)) * hole[:, :, None] + rgb * (~hole)[:, :, None]
    grain = rgb - ndi.gaussian_filter(rgb, (5, 5, 0))
    shift = int(W * 0.45)
    known = (~hole).astype(np.float32)
    g = np.roll(grain, shift, axis=1) * np.roll(known, shift, axis=1)[:, :, None]
    fill = fill + g * hole[:, :, None]
    soft = ndi.gaussian_filter(hole.astype(np.float32), 4)[:, :, None]
    out = rgb * (1 - soft) + fill * soft
    return Image.fromarray(np.clip(out, 0, 255).astype(np.uint8))

def hero(key, name):
    im = load(name)
    print(key, name)
    car = os.path.join(OUT, key + '-car.webp')
    if ('--walls' in sys.argv or '--keep-mask' in sys.argv) and os.path.exists(car):
        # the cut-out's mask as it was made and touched up; only the picture is new
        a = np.asarray(Image.open(car))[:, :, 3].astype(np.float32) / 255.0
        if '--keep-mask' in sys.argv:
            rgba = np.dstack([np.asarray(im), (a * 255).round().astype(np.uint8)])
            Image.fromarray(rgba, 'RGBA').save(car, quality=86, method=6)
        clean_wall(im, a).save(os.path.join(OUT, key + '-wall.jpg'), quality=88, optimize=True)
        return
    a = touch_up(mask_for(im))
    rgba = np.dstack([np.asarray(im), (a * 255).round().astype(np.uint8)])
    Image.fromarray(rgba, 'RGBA').save(os.path.join(OUT, key + '-car.webp'), quality=86, method=6)
    clean_wall(im, a).save(os.path.join(OUT, key + '-wall.jpg'), quality=90, optimize=True)
    # the car's box (for the ghost check and the CSS anchors)
    ys, xs = np.nonzero(a > 0.5)
    H, W = a.shape
    print('   car box x %.3f-%.3f  y %.3f-%.3f' % (xs.min() / W, xs.max() / W, ys.min() / H, ys.max() / H))
    # what a 1.08x car, scaled about its bottom centre, no longer covers
    ox, oy = (xs.min() + xs.max()) / 2, ys.max()
    for s in (1.04, 1.08):
        yy, xx = np.mgrid[0:H, 0:W]
        sx = ox + (xx - ox) / s; sy = oy + (yy - oy) / s
        inside = ndi.map_coordinates((a > 0.5).astype(np.float32), [sy, sx], order=0, cval=0) > 0.5
        print('   scale %.2f: uncovered car px %d' % (s, int(((a > 0.5) & ~inside).sum())))

# the wall around an anchored frame: (photo or built wall, sides kind)
EXT = {
    'open': ('open-wall', 'wall'),
    'sel':  ('sel-wall', 'wall'),
    'end':  ('end-wall', 'wall'),
    'show': ('13_DJI_20260329_154306_816', 'wall'),
    'me':   ('57_IMG_2968', 'dark'),
    'yrs':  ('yrs-wall', 'wall'),
    'vid':  ('vid-wall', 'wall'),
    'far':  ('far-wall', 'wall'),
}
# the record's cars reach the photo's edges: the floor carried sideways is
# built from a clean plate (the car taken out, as for the heroes), or a
# mirrored wheel shows beside the photo (it did, 2026-10-10)
PLATE = {'yrs': '03_DJI_20250318_102100_822', 'vid': '07_DJI_20260329_145042_933', 'far': '47_IMG_1878'}

def plate(key, name):
    im = load(name)
    a = touch_up(mask_for(im))
    clean_wall(im, a).save(os.path.join(OUT, key + '-wall.jpg'), quality=88, optimize=True)
    print(key, 'clean plate')
# anchored frames that are not heroes: the graded photograph itself (<k>-ph.jpg),
# laid in its box over the carried-on wall. The record's three cars stand whole
# at the foot of the screen (car boxes, rembg, measured 2026-10-10:
# 03 x .057-.953 y .252-.788; 07 x .079-.927 y .104-.882; 47 x .136-.898 y .071-.883)
BOXPH = {
    'show': '13_DJI_20260329_154306_816',
    'me':   os.path.join(os.path.dirname(SRC), 'portrait.jpg'),
    'yrs':  '03_DJI_20250318_102100_822',
    'vid':  '07_DJI_20260329_145042_933',
    'far':  '47_IMG_1878',
}
# cover frames on a wide screen: the whole graded photograph (<k>-d.jpg)
DESK = dict((k, n) for k, (n, fx) in PHONE.items())

def boxph(key, name):
    load(name).resize((2400, 1800), Image.LANCZOS).save(os.path.join(OUT, key + '-ph.jpg'), quality=88, optimize=True)
    print(key, 'box photo')

def desk(key, name):
    load(name).save(os.path.join(OUT, key + '-d.jpg'), quality=88, optimize=True)
    print(key, 'desk')
# where the detector misreads the wall line, the line as read off the photo
# (none now: 44's pale concrete strip along the wall really runs to 0.58)
WALL_LINE = {}
UP = {'yrs': (1.0, 0.6), 'vid': (1.0, 0.6), 'far': (1.0, 0.6)}   # night exactly at the canvas top (1.6)   # (fall starts, length) in photo heights above it
EXT_L, EXT_T, EXT_B = 1.0, 1.6, 0.3   # canvas margins, in photo widths / heights
EXT_SCALE = 0.25                        # it is wall: a quarter of the resolution is plenty
NIGHT = np.array([5, 8, 15], np.float32)

def ext(key, src, sides):
    """The canvas the photograph sits in on a wide screen (and above it on a
    phone): three photos wide, 2.9 tall, the photo one width in, 1.6 heights down.

    Above the photo: each column's top rows carried up (the panel seams go on),
    the light falling off into night. Beside it, for a wall frame: the wall
    carried sideways from the edge column, continuing the photo's own wall line
    at its own slope (the camera is never quite level), and below that line a
    FLOOR THAT IS LIGHT, NOT TEXTURE: the asphalt's real grain only next to the
    photo (mirrored, so it is continuous at the seam), dissolving within 0.28
    of a photo width into the floor's own smooth tone, which falls off slowly
    like the edge of a pool of light. A mirrored texture carried far reads as a
    pattern, and blurred it reads as smudges (it did, at 1440: 2026-10-09)."""
    # a built wall is already graded; a photograph is graded here
    im = Image.open(os.path.join(OUT, src + '.jpg')).convert('RGB') if src.endswith('-wall') else load(src)
    W0, H0 = im.size
    W, H = int(W0 * EXT_SCALE), int(H0 * EXT_SCALE)
    ph = np.asarray(im.resize((W, H), Image.LANCZOS)).astype(np.float32)
    L, T, B = int(W * EXT_L), int(H * EXT_T), int(H * EXT_B)
    CW, CH = W + 2 * L, T + H + B
    cv = np.zeros((CH, CW, 3), np.float32)
    cv[T:T + H, L:L + W] = ph
    top = ph[:4].mean(0)
    cv[:T, L:L + W] = top[None]
    def wall_line(strip):
        lum = strip.mean(2)
        sd = ndi.uniform_filter1d(lum.std(1), 9)
        mean = ndi.uniform_filter1d(lum.mean(1), 9)
        ref = mean[:max(8, H // 10)].mean()
        bad = (sd > 9) | (mean < ref - 28)
        idx = np.nonzero(bad[H // 20:])[0]
        return (idx[0] + H // 20) if len(idx) else H
    yl, yr = wall_line(ph[:, :12]), wall_line(ph[:, -12:])
    if key in WALL_LINE:
        yl, yr = int(WALL_LINE[key][0] * H), int(WALL_LINE[key][1] * H)
    slope = (yr - yl) / float(W)          # rows per column
    print(key, 'wall line left %.3f right %.3f' % (yl / H, yr / H))
    yy = np.arange(CH, dtype=np.float32)[:, None]
    for side in ('l', 'r'):
        yw0 = yl if side == 'l' else yr
        edge = ph[:, :6].mean(1) if side == 'l' else ph[:, -6:].mean(1)      # (H,3)
        d = ((np.arange(L)[::-1] + 1) if side == 'l' else (np.arange(L) + 1)).astype(np.float32)[None, :]
        if sides == 'wall':
            # the wall line, carried on outwards at the photo's own slope
            yw = T + yw0 + (-slope * d if side == 'l' else slope * d)          # (1,L)
            colv = np.concatenate([np.repeat(edge[:1], T, 0), edge, np.repeat(edge[-1:], B, 0)], 0)
            wall = np.repeat(colv[:, None, :], L, 1)
            wall = wall * (1 - 0.16 * np.clip(d / L, 0, 1) ** 1.5)[..., None]
            # the floor: real grain near the photo, its smooth tone beyond
            sw = max(8, int(W * 0.10))
            strip = ph[:, :sw] if side == 'l' else ph[:, -sw:][:, ::-1]      # [edge ... inward]
            tile = np.concatenate([strip, strip[:, ::-1]], 1)
            reps = int(np.ceil(L / tile.shape[1])) + 1
            mir = np.concatenate([tile] * reps, 1)[:, :L]
            if side == 'l':
                mir = mir[:, ::-1]
            rows = np.zeros((CH, L, 3), np.float32)
            rows[T:T + H] = mir
            rows[T + H:] = mir[::-1][:B]
            rows[:T] = mir[:1]
            # the smooth tone: the floor strip's per-row median, held steady
            med = strip.mean(1)                                                # (H,3)
            med = np.concatenate([np.repeat(med[:1], T, 0), med, med[::-1][:B]], 0)
            med = ndi.gaussian_filter1d(med, 0.03 * H, axis=0)
            tone = np.repeat(med[:, None, :], L, 1)
            kt = np.clip(1 - d / (0.28 * W), 0, 1); kt = kt * kt * (3 - 2 * kt)   # grain weight
            floor = rows * kt[..., None] + tone * (1 - kt[..., None])
            # a whisper of grain so the tone never bands (fixed seed: rebuilds are identical)
            rng = np.random.default_rng(7 if side == 'l' else 11)
            floor += ndi.gaussian_filter(rng.normal(0, 1.6, (CH, L, 1)), 0.7) * (1 - kt[..., None])
            # light: full at the photo, falling off like the edge of a pool
            light = 0.30 + 0.70 * np.exp(-(d / (0.5 * W)) ** 2)
            floor = floor * light[..., None] + NIGHT * (1 - light[..., None])
            # wall above its line, floor below; a soft contact line where they meet
            fy = np.clip((yy - yw) / (0.012 * H), 0, 1)[..., None]
            contact = 1 - 0.14 * np.exp(-((yy - yw - 0.01 * H) / (0.012 * H)) ** 2)[..., None]
            reg = (wall * (1 - fy) + floor * fy) * contact
        else:
            fall = np.exp(-d / (0.10 * W))[..., None]
            colv = np.concatenate([np.repeat(edge[:1], T, 0), edge, np.repeat(edge[-1:], B, 0)], 0)
            reg = colv[:, None, :] * fall + NIGHT * (1 - fall)
        if side == 'l':
            cv[:, :L] = reg
        else:
            cv[:, L + W:] = reg
    below = ph[::-1][:B]
    fb = np.exp(-np.arange(1, B + 1) / (0.12 * H))[:, None, None]
    cv[T + H:, L:L + W] = below * fb + NIGHT * (1 - fb)
    # the light falls off into night above the photo; the record's frames keep
    # their wall lit higher (their words stand there, in ink)
    u0, ul = UP.get(key, (0.55, 0.9))
    up = np.clip((T - u0 * H - yy) / (ul * H), 0, 1) ** 1.3
    cv = cv * (1 - up[..., None]) + NIGHT * up[..., None]
    Image.fromarray(np.clip(cv, 0, 255).astype(np.uint8)).save(os.path.join(OUT, key + '-ext.jpg'), quality=84, optimize=True)
    print('   canvas', CW, 'x', CH)

def lummap(key, view=(390, 844)):
    """Where words can stand on a phone frame: the visible part of the crop at
    `view`, as a grid of mean luminance (0-9) and calm ('.' flat, ':' some
    texture, '~' busy). Ink goes on 7-9 '.', white on 0-2 '.'; never a scrim."""
    n, fx = PHONE[key]
    im = load(n).convert('L'); W, H = im.size
    x0 = max(0, min(W - CROP_W, int(round(fx * W - CROP_W / 2))))
    c = np.asarray(im.crop((x0, 0, x0 + CROP_W, H))).astype(float)
    vw = int(H * view[0] / view[1]); off = (CROP_W - vw) // 2
    c = c[:, max(0, off):off + vw]
    h, w = c.shape
    print(key, n, 'fx', fx, 'visible', w, 'x', h)
    for r in range(24):
        row = ''
        for k in range(10):
            b = c[r * h // 24:(r + 1) * h // 24, k * w // 10:(k + 1) * w // 10]
            row += str(min(9, int(b.mean() / 25.6))) + ('.' if b.std() < 10 else ':' if b.std() < 22 else '~') + ' '
        print('%3d%% ' % (r * 100 // 24) + row)

if __name__ == '__main__':
    if '--map' in sys.argv:          # pq1-media.py --map km [equip ...]
        for k in sys.argv[sys.argv.index('--map') + 1:]:
            lummap(k)
        sys.exit()
    only = [x for x in sys.argv[sys.argv.index('--only') + 1:] if not x.startswith('--')] if '--only' in sys.argv else None
    os.makedirs(OUT, exist_ok=True)
    extonly = '--ext-only' in sys.argv     # just the canvases (no rembg, no crops)
    for k, (n, fx) in PHONE.items():
        if (only is None or k in only) and not extonly:
            phone(k, n, fx)
    for k, n in HERO.items():
        if (only is None or k in only) and not extonly:
            hero(k, n)
    for k, n in BOXPH.items():
        if (only is None or k in only) and not extonly:
            boxph(k, n)
    for k, n in PLATE.items():
        if (only is None or k in only) and not extonly:
            plate(k, n)
    for k, n in DESK.items():
        if (only is None or k in only) and not extonly:
            desk(k, n)
    if '--ext' in sys.argv or extonly:
        for k, (src, sides) in EXT.items():
            if only is None or k in only:
                ext(k, src, sides)
