// The A4 placard to PDF (Admin\SheetController::placardPdf): node tools/cartel/print.mjs in.html out.pdf
// Chrome's own --print-to-pdf prints Letter whatever the page's @page says; through the
// DevTools protocol it takes the page's size (A4) and waits for the fonts and the photo.
import puppeteer from 'puppeteer-core';

const [, , input, output, chrome] = process.argv;
const browser = await puppeteer.launch({
  executablePath: chrome || '/opt/mc-chrome/chrome-headless-shell-linux64/chrome-headless-shell',
  headless: 'shell',
  args: ['--no-sandbox', '--disable-gpu', '--font-render-hinting=none'],
});
try {
  const page = await browser.newPage();
  // laid out as it will print, so the page measures itself (what fits on page one)
  // against the paper and not against a screen
  await page.emulateMediaType('print');
  await page.goto('file://' + input, { waitUntil: 'load', timeout: 30000 });
  await page.evaluate(() => document.fonts.ready);
  await page.waitForFunction(() => !document.querySelector('script') || document.documentElement.dataset.ready === '1', { timeout: 10000 }).catch(() => {});
  await page.pdf({ path: output, preferCSSPageSize: true, printBackground: true, timeout: 30000 });
} finally {
  await browser.close();
}
