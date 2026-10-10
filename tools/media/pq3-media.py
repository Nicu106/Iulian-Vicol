#!/usr/bin/env python3
"""/muestras/por-que/3 "Inmersiva" — the photographs the reels travel through,
cut once from the ORIGINALS (the 4032/5712px HEIC and DJI files).

Run with a venv that has pillow, pillow-heif, numpy, opencv-contrib-python-headless:

    $VENV/bin/python tools/media/pq3-media.py pairs   # fit the zoom-through matches
    $VENV/bin/python tools/media/pq3-media.py cuts    # cut the files
    chown -R www-data:www-data storage/app/public/why/pq3

Both read the camera from public/js/pq3.js (through tools/media/pq3-plan.cjs, so
what is fitted and cut is exactly what each screen will see) and write their
result back into it, between the /*PAIRS*/ and /*CROPS*/ markers. Run `pairs`
first: the match moves the photographs, and the cuts follow them.

pairs  For each zoom-through A>B and screen shape (P upright, L wide), starting
       from the first SIFT + RANSAC fit: the similarity that best overlays B's
       edges on A's (normalised cross-correlation of blurred gradient magnitude,
       blind to exposure) over the region of A on screen while B dissolves in,
       weighted to the middle of the screen; Nelder-Mead, 800 -> 1600 -> 3024px.
       pB = k·R(φ)·pA + t, in photo widths. Prints the score before and after.

cuts   Per photograph and set (p: phones upright; l: every other screen), from the
       samples (region seen, zoom, cover width) of its path on every screen of the
       set:
         level 0  p: the union of what is seen while the zoom is <= z1, cut at
                  D_P file px per CSS px at z1;  l: the whole photograph, 2400px.
         level 1  the union of what is seen above z1, at the density of the
                  deepest zoom (capped by the original's own pixels).
       z1 is chosen per photograph to make the fewest pixels. Regions get a 2%
       margin. WebP method 6 (q76 phones, q78 the rest), the original's colour profile
       (Display P3) kept.
       The table gives pq3.js each level's rect and its file pixels per photo width.
"""
import sys, os, re, json, subprocess, io
import numpy as np
from PIL import Image, ImageOps
import pillow_heif

pillow_heif.register_heif_opener()
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
ORIG = '/var/www/motorclass/photo_video_why_us/16 Mar 2025 – 29 Mar 2026/'   # read only
OUT = os.environ.get('PQ3_OUT', os.path.join(ROOT, 'storage/app/public/why/pq3'))
JS = os.environ.get('PQ3_JS', os.path.join(ROOT, 'public/js/pq3.js'))
PLAN = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'pq3-plan.cjs')
CACHE = os.environ.get('PQ3_CACHE', '/tmp/pq3-originals')

FILES = {
    '14': 'DJI_20260329_154326_944.jpg', '15': 'DJI_20260329_154341_391.jpg',
    '19': 'IMG_1252.HEIC', '21': 'IMG_1274.HEIC', '22': 'IMG_1276.HEIC', '24': 'IMG_1280.HEIC',
    '26': 'IMG_1310.HEIC', '27': 'IMG_1313.HEIC', '29': 'IMG_1325.HEIC', '30': 'IMG_1350.HEIC',
    '40': 'IMG_1862.HEIC', '41': 'IMG_1863.HEIC', '43': 'IMG_1869.HEIC',
    '44': 'IMG_1871.HEIC',
}
D_P = float(os.environ.get('PQ3_DP', 1.5))   # phones: file px per CSS px at the deepest zoom of a level
D_L = float(os.environ.get('PQ3_DL', 0.9))   # wide screens (level 1 only; level 0 is the 2400 file)
L0_W = 2400
MARGIN = 0.02
Q = {'p': int(os.environ.get('PQ3_QP', 76)), 'l': int(os.environ.get('PQ3_QL', 78))}   # WebP quality per set


def original(k):
    """the original, upright, as RGB, and its colour profile (bytes or None)"""
    os.makedirs(CACHE, exist_ok=True)
    png = os.path.join(CACHE, k + '.png')
    icc_f = os.path.join(CACHE, k + '.icc')
    if not os.path.exists(png):
        im = Image.open(ORIG + FILES[k])
        icc = im.info.get('icc_profile')
        im = ImageOps.exif_transpose(im).convert('RGB')
        im.save(png)
        if icc:
            open(icc_f, 'wb').write(icc)
    return Image.open(png).convert('RGB'), (open(icc_f, 'rb').read() if os.path.exists(icc_f) else None)


def plan(what):
    return json.loads(subprocess.check_output(['node', PLAN, what, JS]))


def patch(marker, obj, fmt):
    s = open(JS).read()
    a, b = '/*%s*/' % marker, '/*/%s*/' % marker
    i, j = s.index(a) + len(a), s.index(b)
    open(JS, 'w').write(s[:i] + fmt(obj) + s[j:])


# ---- pairs ---------------------------------------------------------------------------
OLD_PAIRS = {   # the first fits (SIFT + RANSAC at 1200px, checked by eye): the starting point
    '14>15': [1.8079, -0.0102, -0.7686, -0.45205], '40>41': [1.8202, 0.03613, -0.39493, -0.42702],
    '43>44': [1.315, 0, -0.16584, -0.24578],
    '19>21': [1.0663, 0, -0.02756, -0.04507],
}


KEEP = {('43>44', 'L'): [1.35449, 0.00296, -0.18465, -0.27182]}


def pairs():
    """refine each match on what will be seen: the similarity that best overlays the
    EDGES of B on those of A (normalised cross-correlation of blurred gradient
    magnitude: blind to exposure) over A's region on screen during the dissolve,
    coarse to fine (800, 1600, 3024px), Nelder-Mead from the first fit"""
    import cv2
    from scipy.optimize import minimize
    roi = plan('pairs')
    ims = {}

    def edges(k, W, sig):
        key = (k, W, sig)
        if key not in ims:
            im, _ = original(k)
            g = cv2.cvtColor(np.asarray(im.resize((W, round(W * 0.75)), Image.LANCZOS)), cv2.COLOR_RGB2GRAY).astype(np.float32)
            gx, gy = cv2.Sobel(g, cv2.CV_32F, 1, 0), cv2.Sobel(g, cv2.CV_32F, 0, 1)
            ims[key] = cv2.GaussianBlur(np.sqrt(gx * gx + gy * gy), (0, 0), sig)
        return ims[key]

    def ncc(p, A, B, m, W, wt=None):
        k, phi, tx, ty = p
        c, s_ = k * np.cos(phi), k * np.sin(phi)
        M = np.float32([[c, -s_, tx * W], [s_, c, ty * W]])          # A px -> B px
        w = cv2.warpAffine(B, M, (A.shape[1], A.shape[0]), flags=cv2.INTER_LINEAR | cv2.WARP_INVERSE_MAP, borderValue=np.nan)
        a, b, q = A[m], w[m], wt[m]
        ok = ~np.isnan(b)
        a, b, q = a[ok], b[ok], q[ok]
        q = q / q.sum()
        a, b = a - (a * q).sum(), b - (b * q).sum()
        return float((q * a * b).sum() / np.sqrt((q * a * a).sum() * (q * b * b).sum() + 1e-12))

    out = {}
    for key, modes in roi.items():
        a, b = key.split('>')
        res = {}
        for mode, r in modes.items():
            x0, y0, x1, y1 = [max(0.0, min(1.0, v)) for v in r[:4]]
            cx, cy = r[4], r[5]
            p = np.array(OLD_PAIRS[key], float)
            p0 = p.copy()
            for W, sig in ((800, 3.0), (1600, 2.0), (3024, 1.5)):
                A, B = edges(a, W, sig), edges(b, W, sig)
                yy, xx = np.mgrid[0:A.shape[0], 0:A.shape[1]]
                m = (xx >= x0 * W) & (xx <= x1 * W) & (yy >= y0 * A.shape[0]) & (yy <= y1 * A.shape[0])
                # the middle of the screen counts most: a gaussian about it, sigma a quarter of the region
                sg = 0.25 * (x1 - x0) * W
                wt = np.exp(-((xx - cx * W) ** 2 + (yy - cy * A.shape[0]) ** 2) / (2 * sg * sg)).astype(np.float32)
                f = lambda q: -ncc(q, A, B, m, W, wt)
                p = minimize(f, p, method='Nelder-Mead', options=dict(xatol=1e-5, fatol=1e-6, maxiter=600,
                             initial_simplex=[p] + [p + np.eye(4)[i] * [0.01, 0.005, 0.004, 0.004][i] for i in range(4)])).x
            first, last = ncc(p0, A, B, m, W, wt), ncc(p, A, B, m, W, wt)
            if (key, mode) in KEEP:
                # the refit is ambiguous here (same score with a 2 degree roll that the
                # camera would then unwind on screen): keep the fit that does not roll
                p = np.array(KEEP[(key, mode)], float)
            res[mode] = [round(float(p[0]), 5), round(float(p[1]), 5), round(float(p[2]), 5), round(float(p[3]), 5)]
            print('%s %s roi %s  first fit ncc %.3f -> %.3f   k %.4f phi %.2f deg t %.4f %.4f' % (
                key, mode, [round(v, 2) for v in (x0, y0, x1, y1)], first, last, p[0], np.degrees(p[1]), p[2], p[3]))
        out[key] = res

    def fmt(o):
        rows = ["    '%s': { P: %s, L: %s }" % (k, json.dumps(v['P']), json.dumps(v['L'])) for k, v in o.items()]
        return '{\n' + ',\n'.join(rows) + '\n  }'
    patch('PAIRS', out, fmt)
    print('pairs written into', JS)


# ---- cuts ------------------------------------------------------------------------------
def union(samples, m=MARGIN):
    a = np.array(samples)
    r = [a[:, 0].min() - m, a[:, 1].min() - m * 4 / 3, a[:, 2].max() + m, a[:, 3].max() + m * 4 / 3]
    return [max(0.0, r[0]), max(0.0, r[1]), min(1.0, r[2]), min(1.0, r[3])]


def area(r):
    return (r[2] - r[0]) * (r[3] - r[1])


def choose(samples, origW, d):
    """(level-0 rect, level-0 width per photo width, level-1 rect or None, its width, z1)"""
    s = np.array(samples)
    z, wc = s[:, 4], s[:, 5]
    need = d * z * wc                       # file px per photo width each sample wants
    best = None
    for z1 in sorted(set(np.round(np.linspace(1.0, max(1.0, z.max()), 40), 3)) | {float(z.max()) + 1e-3}):
        lo, hi = s[z <= z1], s[z > z1]
        if not len(lo):
            continue
        r0 = union(lo[:, :4]); w0 = min(origW, need[z <= z1].max())
        cost = area(r0) * w0 * w0
        r1 = w1 = None
        if len(hi):
            r1 = union(hi[:, :4]); w1 = min(origW, need[z > z1].max())
            if w1 <= w0 * 1.08:
                continue                    # not worth a second file
            cost += area(r1) * w1 * w1
        if best is None or cost < best[0]:
            best = (cost, r0, w0, r1, w1, z1)
    if best is None:                        # one level holds it all
        r0 = union(s[:, :4]); w0 = min(origW, need.max())
        best = (0, r0, w0, None, None, 99)
    return best[1:]


def cut(im, r, w_photo, icc, path, q):
    W, H = im.size
    box = (round(r[0] * W), round(r[1] * H), round(r[2] * W), round(r[3] * H))
    ow = max(1, round((r[2] - r[0]) * w_photo)); oh = max(1, round((r[3] - r[1]) * w_photo * 0.75))
    c = im.crop(box).resize((ow, oh), Image.LANCZOS)
    kw = dict(quality=q, method=6)
    if icc:
        kw['icc_profile'] = icc
    c.save(path, 'WEBP', **kw)
    return ow, oh, os.path.getsize(path)


def cuts():
    samples = plan('cuts')
    os.makedirs(OUT, exist_ok=True)
    table, total = {}, {'p': 0, 'l': 0}
    for k in FILES:
        if k not in samples:
            continue
        im, icc = original(k)
        entry = {}
        for st in ('p', 'l'):
            sm = samples[k].get(st)
            if not sm:
                continue
            if st == 'p':
                r0, w0, r1, w1, z1 = choose(sm, im.width, D_P)
            else:
                # the whole photograph at 2400 holds a 1920 screen up to z1; above it, level 1
                z1 = L0_W / (D_L * 1920)          # (pq3.js decides per screen, by density)
                a = np.array(sm); hi = a[a[:, 4] > z1]
                r0, w0 = [0, 0, 1, 1], L0_W
                r1 = union(hi[:, :4]) if len(hi) else None
                w1 = min(im.width, (D_L * hi[:, 4] * np.minimum(hi[:, 5], 1920)).max()) if len(hi) else None
                if r1 and w1 <= L0_W * 1.08:
                    r1 = None
            n0 = cut(im, r0, w0, icc, os.path.join(OUT, '%s-%s0.webp' % (k, st)), Q[st])
            f1 = os.path.join(OUT, '%s-%s1.webp' % (k, st))
            n1 = cut(im, r1, w1, icc, f1, Q[st]) if r1 else None
            if not r1 and os.path.exists(f1):
                os.remove(f1)
            total[st] += n0[2] + (n1[2] if n1 else 0)
            entry[st] = [[round(float(v), 4) for v in r0], [round(float(v), 4) for v in r1] if r1 else 0, round(float(z1), 3),
                         round(n0[0] / (r0[2] - r0[0])), round(n1[0] / (r1[2] - r1[0])) if n1 else 0]
            print('%s %s  L0 %s %dx%d %dK%s  z1 %.2f' % (k, st, [round(float(v), 2) for v in r0], n0[0], n0[1], n0[2] // 1024,
                  ('  L1 %s %dx%d %dK' % ([round(float(v), 2) for v in r1], n1[0], n1[1], n1[2] // 1024)) if n1 else '', z1))
        table[k] = entry
    print('total p %.2f MB, l %.2f MB' % (total['p'] / 1048576, total['l'] / 1048576))

    def fmt(o):
        rows = ["    '%s': { p: %s, l: %s }" % (k, json.dumps(v['p']), json.dumps(v['l'])) for k, v in o.items()]
        return '{\n' + ',\n'.join(rows) + '\n  }'
    patch('CROPS', table, fmt)
    print('cuts written into', JS)


if __name__ == '__main__':
    {'pairs': pairs, 'cuts': cuts}[sys.argv[1]]()
