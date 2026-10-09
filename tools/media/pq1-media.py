#!/usr/bin/env python3
"""/muestras/por-que/1 "Cine" — the pictures, made once from the client's photos.

Run with the rembg venv (rembg, pillow, numpy, scipy):
    $VENV/bin/python tools/media/pq1-media.py [--only NAME ...]

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
    'equip': ('30_IMG_1350', 0.62),
    'cuid':  ('05_DJI_20250318_103924_729', 0.46),
    'km':    ('25_IMG_1283', 0.24),
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

def load(name):
    return Image.open(os.path.join(SRC, name + '.jpg')).convert('RGB')

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
    if '--walls' in sys.argv and os.path.exists(car):
        a = np.asarray(Image.open(car))[:, :, 3].astype(np.float32) / 255.0
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
}
# where the detector misreads the wall line, the line as read off the photo
# (none now: 44's pale concrete strip along the wall really runs to 0.58)
WALL_LINE = {}
EXT_L, EXT_T, EXT_B = 1.0, 1.6, 0.3   # canvas margins, in photo widths / heights
EXT_SCALE = 0.25                        # it is wall: a quarter of the resolution is plenty
NIGHT = np.array([5, 8, 15], np.float32)

def ext(key, src, sides):
    path = os.path.join(OUT, src + '.jpg') if src.endswith('-wall') else os.path.join(SRC, src + '.jpg')
    im = Image.open(path).convert('RGB')
    W0, H0 = im.size
    W, H = int(W0 * EXT_SCALE), int(H0 * EXT_SCALE)
    ph = np.asarray(im.resize((W, H), Image.LANCZOS)).astype(np.float32)
    L, T, B = int(W * EXT_L), int(H * EXT_T), int(H * EXT_B)
    CW, CH = W + 2 * L, T + H + B
    cv = np.zeros((CH, CW, 3), np.float32)
    cv[T:T + H, L:L + W] = ph
    # above: each column's top rows, carried up (the panel seams continue)
    top = ph[:4].mean(0)                                  # (W,3)
    cv[:T, L:L + W] = top[None]
    # the wall line at each edge: the first row (from the top) where the strip
    # stops being calm wall (texture or a step in brightness)
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
    print(key, 'wall line left %.3f right %.3f' % (yl / H, yr / H))
    yy = np.arange(CH)[:, None]
    for side in ('l', 'r'):
        yw = yl if side == 'l' else yr
        edge = ph[:, :6].mean(1) if side == 'l' else ph[:, -6:].mean(1)      # (H,3)
        d = (np.arange(L)[::-1] + 1)[None, :] if side == 'l' else (np.arange(L) + 1)[None, :]   # distance from the photo
        reg = np.zeros((CH, L, 3), np.float32)
        if sides == 'wall':
            # wall rows: the edge column carried sideways; the rows above it the same
            colv = np.concatenate([np.repeat(edge[:1], T, 0), edge, np.repeat(edge[-1:], B, 0)], 0)
            reg[:] = colv[:, None, :]
            # floor rows: the real asphalt beside the car (the outer 8% of the
            # photo, never the filled-in car), mirror-tiled outwards, falling
            # into night
            sw = max(8, int(W * 0.08))
            strip = ph[:, :sw] if side == 'l' else ph[:, -sw:][:, ::-1]      # [edge ... inward]
            tile = np.concatenate([strip, strip[:, ::-1]], 1)                 # edge-out mirror pair
            reps = int(np.ceil(L / tile.shape[1])) + 1
            mir = np.concatenate([tile] * reps, 1)[:, :L]                      # distance 0 = at the photo
            if side == 'l':
                mir = mir[:, ::-1]
            floor = np.zeros((CH, L, 3), np.float32)
            floor[T:T + H] = mir
            floor[T + H:] = mir[::-1][:B]
            # the further from the photo, the softer: a mirrored tile repeated
            # sharp reads as a pattern (it did, at 1440); out of focus it is floor
            soft = ndi.gaussian_filter(floor, (3, 9, 0))
            ks = np.clip(d / (0.12 * W), 0, 1)[..., None]
            floor = floor * (1 - ks) + soft * ks
            # the edge column's own streaks never reach the floor: below the
            # wall line the side is floor only
            fy = np.clip((yy - (T + yw)) / (0.04 * H), 0, 1)[..., None]      # 0 on the wall, 1 on the floor
            fall = np.exp(-(d / (0.24 * W)) ** 1.5)[..., None]
            reg = reg * (1 - fy) + (floor * fall + NIGHT * (1 - fall)) * fy
            # the wall itself dims a little with distance, like the light falling off
            reg = reg * (1 - 0.18 * np.clip(d / L, 0, 1) ** 2)[..., None] + 0
        else:
            fall = np.exp(-d / (0.10 * W))[..., None]
            colv = np.concatenate([np.repeat(edge[:1], T, 0), edge, np.repeat(edge[-1:], B, 0)], 0)
            reg = colv[:, None, :] * fall + NIGHT * (1 - fall)
        if side == 'l':
            cv[:, :L] = reg
        else:
            cv[:, L + W:] = reg
    # below the photo: its floor mirrored, falling into night
    below = ph[::-1][:B]
    fb = np.exp(-np.arange(1, B + 1) / (0.12 * H))[:, None, None]
    cv[T + H:, L:L + W] = below * fb + NIGHT * (1 - fb)
    # sides below the photo were built above; the top: light falls off upward
    up = np.clip((T - 0.55 * H - yy) / (0.9 * H), 0, 1) ** 1.3        # 0 near the photo, 1 at the canvas top
    cv = cv * (1 - up[..., None]) + NIGHT * up[..., None]
    Image.fromarray(np.clip(cv, 0, 255).astype(np.uint8)).save(os.path.join(OUT, key + '-ext.jpg'), quality=84, optimize=True)
    print('   canvas', CW, 'x', CH)

if __name__ == '__main__':
    only = [x for x in sys.argv[sys.argv.index('--only') + 1:] if not x.startswith('--')] if '--only' in sys.argv else None
    os.makedirs(OUT, exist_ok=True)
    for k, (n, fx) in PHONE.items():
        if only is None or k in only:
            phone(k, n, fx)
    for k, n in HERO.items():
        if only is None or k in only:
            hero(k, n)
    if '--ext' in sys.argv:
        for k, (src, sides) in EXT.items():
            if only is None or k in only:
                ext(k, src, sides)
