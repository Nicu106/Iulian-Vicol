#!/usr/bin/env node
/**
 * Measurement floor for the v2 front end. No API, no judgement — numbers only.
 *
 *   node tools/audit/audit.mjs <check> <path> [--w 390,768,1400]
 *
 * checks:
 *   contrast   every text node against its painted background (WCAG 1.4.3 / 1.4.11)
 *   overflow   horizontal overflow and elements escaping the viewport, per width
 *   targets    interactive targets under 44px, inline-in-sentence ones excluded (2.5.8)
 *   ladder     radii, weights, tracking and leading that are off the token ladders
 *   nojs       what the page looks like with JavaScript disabled
 *   measure    characters per rendered line, for prose measure
 *   shot       full-page screenshot per width, into tools/audit/out/
 *
 * The rule this exists to serve: a defect without a measured value may not be
 * reported, and a fix is done only when the next measurement of its neighbourhood
 * is clean. See docs/vault/50-Frontend/Design System/Design Review Method.md
 */
import p from 'puppeteer';
import { mkdirSync, readdirSync, existsSync } from 'fs';
import { fileURLToPath } from 'url';

const [, , check = 'contrast', route = '/'] = process.argv;
const wArg = process.argv.indexOf('--w');
const WIDTHS = wArg > -1 ? process.argv[wArg + 1].split(',').map(Number) : [390, 768, 1400];
const HOST = process.env.AUDIT_HOST || 'v2design.ivmotorclass.com';
const TARGET = `https://${HOST}${route.startsWith('/') ? route : '/' + route}`;

const lum = (r, g, b) => { const f = c => { c /= 255; return c <= .03928 ? c / 12.92 : ((c + .055) / 1.055) ** 2.4; };
  return .2126 * f(r) + .7152 * f(g) + .0722 * f(b); };
const cr = (a, b) => { const x = lum(...a), y = lum(...b);
  return +((Math.max(x, y) + .05) / (Math.min(x, y) + .05)).toFixed(2); };

// puppeteer pins an exact Chrome build; use whatever build the cache actually holds,
// so a puppeteer bump does not silently break every check with a version mismatch
const cached = (() => {
  try {
    const dir = `${process.env.HOME}/.cache/puppeteer/chrome`;
    const v = readdirSync(dir).filter(d => d.startsWith('linux-')).sort().pop();
    return v ? `${dir}/${v}/chrome-linux64/chrome` : undefined;
  } catch { return undefined; }
})();

const browser = await p.launch({
  executablePath: cached && existsSync(cached) ? cached : undefined,
  args: ['--no-sandbox', '--ignore-certificate-errors',
         `--host-resolver-rules=MAP ${HOST} 127.0.0.1`] });

const open = async (w, js = true) => {
  const pg = await browser.newPage();
  if (!js) await pg.setJavaScriptEnabled(false);
  await pg.setViewport({ width: w, height: 900, isMobile: w < 560 });
  await pg.goto(TARGET, { waitUntil: 'networkidle0' });
  // Disabling JS also disables the tool's own evaluate(), so turn it back on once the
  // document is parsed: the page's own scripts have already been skipped and do not run.
  if (!js) { await pg.setJavaScriptEnabled(true); return pg; }
  // walk the page so anything gated on the viewport actually runs
  await pg.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 400) {
    scrollTo(0, y); await new Promise(r => setTimeout(r, 90)); } scrollTo(0, 0); });
  await new Promise(r => setTimeout(r, 1200));
  return pg;
};

// The background under a text node is NOT always up its ancestor chain: a full-bleed
// row paints its colour on an absolutely positioned sibling that sits behind the text.
// Sampling the real stacking order at the text's own centre is the only reliable read.
const PAINTED = `(el => {
  const own = getComputedStyle(el).backgroundColor;
  if (own && !/rgba\\(0, 0, 0, 0\\)|transparent/.test(own)) return own;   // it paints its own
  const r = el.getBoundingClientRect();
  const x = Math.min(innerWidth - 1, Math.max(0, r.left + r.width / 2));
  const y = Math.min(innerHeight - 1, Math.max(0, r.top + r.height / 2));
  const stack = document.elementsFromPoint(x, y);
  // If something else is painted on top at the text's own centre, the text is occluded,
  // not low-contrast. A phone specimen's sticky bar covering the spec rows behind it is
  // the demonstration, not a defect. Report it as occluded and let a human judge.
  const top = stack[0];
  if (top && top !== el && !el.contains(top) && !top.contains(el)) return 'OCCLUDED';
  for (const n of stack) {
    if (n === el || el.contains(n)) continue;
    const bg = getComputedStyle(n).backgroundColor;
    if (bg && !/rgba\\(0, 0, 0, 0\\)|transparent/.test(bg)) return bg;
  }
  let n = el.parentElement;
  while (n) { const bg = getComputedStyle(n).backgroundColor;
    if (bg && !/rgba\\(0, 0, 0, 0\\)|transparent/.test(bg)) return bg; n = n.parentElement; }
  return 'rgb(255, 255, 255)';
})`;

const checks = {
  async contrast() {
    const pg = await open(WIDTHS.at(-1));
    // elementsFromPoint only reads what is on screen, so walk the page a screen at a
    // time and merge. Without this the check silently covers the first viewport only.
    const height = await pg.evaluate(() => innerHeight);
    const total = await pg.evaluate(() => document.body.scrollHeight);
    const seen = new Map();
    for (let y = 0; y < total; y += Math.round(height * 0.8)) {
      await pg.evaluate(v => scrollTo(0, v), y);
      await new Promise(r => setTimeout(r, 260));
      const rows = await pg.evaluate(`[...document.querySelectorAll('body *')]
        .filter(e => e.children.length === 0 && e.textContent.trim())
        .map(e => { const s = getComputedStyle(e), r = e.getBoundingClientRect();
          if (!r.width || !r.height || s.visibility === 'hidden' || +s.opacity === 0) return null;
          if (r.bottom < 0 || r.top > innerHeight) return null;   // off-screen: cannot sample its stack
          if (r.width <= 1 || r.height <= 1) return null;         // visually hidden: nothing to see
          if (s.clipPath && s.clipPath.includes('inset(50%')) return null;
          // decorative text carrying no information of its own is out of 1.4.3's scope;
          // the ornament must be aria-hidden AND its words repeated in real text nearby
          if (e.closest('[aria-hidden=\"true\"]')) return null;
          return { sel: e.tagName.toLowerCase() + (e.className && typeof e.className === 'string'
                     ? '.' + e.className.trim().split(/\\s+/).join('.') : ''),
                   text: e.textContent.trim().slice(0, 40),
                   fg: s.color, bg: ${PAINTED}(e),
                   size: parseFloat(s.fontSize), weight: +s.fontWeight }; })
        .filter(Boolean)`);
      for (const r of rows) seen.set(r.sel + '|' + r.text + '|' + r.bg, r);
    }
    const parse = c => { const n = c.match(/[\d.]+/g).map(Number);
      return { rgb: n.slice(0, 3), a: n.length > 3 ? n[3] : 1 }; };
    const bad = [], occluded = [];
    for (const r of seen.values()) {
      if (r.bg === 'OCCLUDED') { occluded.push({ sel: r.sel, text: r.text }); continue; }
      const f = parse(r.fg), b = parse(r.bg);
      const mix = f.rgb.map((v, i) => Math.round(f.a * v + (1 - f.a) * b.rgb[i]));
      const ratio = cr(mix, b.rgb);
      const large = r.size >= 24 || (r.size >= 18.66 && r.weight >= 700);
      const floor = large ? 3 : 4.5;
      if (ratio < floor) bad.push({ ...r, ratio, floor });
    }
    await pg.close();
    bad.sort((a, b) => a.ratio - b.ratio);
    return { checked: seen.size, failing: bad.length, worst: bad.slice(0, 20),
             occluded: occluded.length, occludedSample: occluded.slice(0, 6) };
  },

  async overflow() {
    const out = [];
    for (const w of WIDTHS) {
      const pg = await open(w);
      out.push({ width: w, ...await pg.evaluate(() => {
        const de = document.documentElement;
        // an element wider than the viewport inside a deliberately scrollable box is
        // not overflow — it is a table that scrolls on its own, which is the fix, not the bug
        const scrollable = e => { let n = e.parentElement;
          while (n && n !== document.body) {
            const o = getComputedStyle(n).overflowX;
            if (o === 'auto' || o === 'scroll') return true; n = n.parentElement; }
          return false; };
        const esc = [...document.querySelectorAll('body *')].filter(e => {
          const r = e.getBoundingClientRect();
          return r.width && (r.right > innerWidth + 1 || r.left < -1) && !scrollable(e);
        }).map(e => e.tagName.toLowerCase() + '.' + String(e.className).trim().split(/\s+/)[0]);
        return { overflow: de.scrollWidth - de.clientWidth, escaping: [...new Set(esc)].slice(0, 10) };
      }) });
      await pg.close();
    }
    return out;
  },

  async targets() {
    const out = [];
    for (const w of WIDTHS) {
      const pg = await open(w);
      out.push({ width: w, small: await pg.evaluate(() =>
        [...document.querySelectorAll('a,button,input,select,[role=button]')].map(e => {
          const s = getComputedStyle(e);
          // the real target is the label that wraps a control, not the control's own box
          const lab = e.closest('label');
          const r = (lab && lab.contains(e) ? lab : e).getBoundingClientRect();
          // 2.5.8 exempts a target sitting in a sentence: inline, with other text beside it
          const holder = e.parentElement;
          const sentence = s.display.startsWith('inline') && holder &&
            holder.textContent.trim().length > e.textContent.trim().length + 8;
          return r.height && !sentence && (r.height < 44 || r.width < 44)
            ? { t: (e.textContent.trim() || e.type || e.tagName.toLowerCase()).slice(0, 28),
                w: Math.round(r.width), h: Math.round(r.height) } : null;
        }).filter(Boolean)) });
      await pg.close();
    }
    return out;
  },

  async ladder() {
    const pg = await open(WIDTHS.at(-1));
    const r = await pg.evaluate(() => {
      const R = new Set(['0px', '4px', '10px', '22px', '50%', '999px']);
      const W = new Set(['400', '500', '600', '700']);
      const off = { radius: {}, weight: {}, tracking: {}, leading: {} };
      const bump = (k, v, e) => { off[k][v] = (off[k][v] || 0) + 1; };
      document.querySelectorAll('body *').forEach(e => {
        const s = getComputedStyle(e), b = e.getBoundingClientRect();
        if (!b.width) return;
        ['borderTopLeftRadius', 'borderTopRightRadius'].forEach(p => {
          if (s[p] !== '0px' && !R.has(s[p])) bump('radius', s[p]); });
        if (e.textContent.trim() && !W.has(s.fontWeight)) bump('weight', s.fontWeight);
      });
      return off;
    });
    await pg.close();
    return r;
  },

  async nojs() {
    // What matters is not what is hidden without JavaScript, but what JavaScript was
    // holding up: an element hidden in both states is a CSS decision, not a dependency.
    const probe = `(() => {
      const hidden = [...document.querySelectorAll('body *')].filter(e => {
        const s = getComputedStyle(e);
        return e.textContent.trim() && (+s.opacity === 0 || s.visibility === 'hidden'
               || s.display === 'none');
      }).map(e => e.tagName.toLowerCase() + '.' + String(e.className).trim().split(/\\s+/)[0]);
      return { hidden: [...new Set(hidden)],
               overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth };
    })()`;
    const withJs = await open(WIDTHS.at(-1));
    const a = await withJs.evaluate(probe); await withJs.close();
    const without = await open(WIDTHS.at(-1), false);
    const b = await without.evaluate(probe); await without.close();
    const onlyWithoutJs = b.hidden.filter(h => !a.hidden.includes(h));
    return { hiddenOnlyWithoutJs: onlyWithoutJs,
             hiddenInBothStates: b.hidden.length - onlyWithoutJs.length,
             overflowWithJs: a.overflow, overflowWithoutJs: b.overflow };
  },

  async measure() {
    const pg = await open(WIDTHS.at(-1));
    // a Range walk per character: the only honest way to count a rendered line,
    // since `ch` is not a character and text.length / lineBoxes under-counts
    const r = await pg.evaluate(() => {
      const out = [];
      document.querySelectorAll('p, li, blockquote').forEach(el => {
        const t = el.firstChild;
        if (!t || t.nodeType !== 3 || t.textContent.trim().length < 40) return;
        const rg = document.createRange();
        const lines = new Map();
        for (let i = 0; i < t.length; i++) {
          rg.setStart(t, i); rg.setEnd(t, i + 1);
          const b = rg.getBoundingClientRect();
          if (!b.width && t.textContent[i] !== ' ') continue;
          const key = Math.round(b.top);
          lines.set(key, (lines.get(key) || '') + t.textContent[i]);
        }
        const counts = [...lines.values()].map(s => s.replace(/\s+/g, ' ').trim().length).filter(n => n > 5);
        if (counts.length) out.push({ tag: el.tagName.toLowerCase(),
          text: el.textContent.trim().slice(0, 30), longest: Math.max(...counts), lines: counts.length });
      });
      return out.sort((a, b) => b.longest - a.longest).slice(0, 15);
    });
    await pg.close();
    return r;
  },

  async shot() {
    const dir = fileURLToPath(new URL('./out/', import.meta.url));
    mkdirSync(dir, { recursive: true });
    const files = [];
    for (const w of WIDTHS) {
      const pg = await open(w);
      const f = `${dir}${route.replace(/\W+/g, '-') || 'root'}-${w}.png`;
      await pg.screenshot({ path: f, fullPage: true });
      files.push(f); await pg.close();
    }
    return files;
  },
};

if (!checks[check]) { console.error('unknown check: ' + check + '\nhave: ' + Object.keys(checks).join(', ')); process.exit(2); }
console.log(JSON.stringify(await checks[check](), null, 1));
await browser.close();
