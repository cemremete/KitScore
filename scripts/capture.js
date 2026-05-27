/**
 * KitScore — Playwright screenshot capture (dark mode only)
 * Run: node capture.js  (from the scripts/ folder)
 * Saves all screenshots to ../screenshots/
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://kitscore.local';
const SCREENSHOTS_DIR = path.resolve(__dirname, '../screenshots');

if (!fs.existsSync(SCREENSHOTS_DIR)) {
  fs.mkdirSync(SCREENSHOTS_DIR, { recursive: true });
}

async function goTo(page, url) {
  await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
  await page.waitForTimeout(2500);
}

async function setDark(page) {
  await page.evaluate(() => {
    document.body.classList.add('dark-mode');
    document.documentElement.classList.add('dark-mode');
    localStorage.setItem('ks-dark', 'true');
  });
  await page.waitForTimeout(1000);
}

async function capture(page, filename) {
  const outPath = path.join(SCREENSHOTS_DIR, filename);
  try {
    await page.screenshot({ path: outPath, fullPage: true });
    const size = fs.statSync(outPath).size;
    const kb = (size / 1024).toFixed(1);
    console.log(`  ✓  ${filename.padEnd(40)} ${kb} KB  success`);
    return { file: filename, sizeKB: kb, ok: true };
  } catch (err) {
    console.log(`  ✗  ${filename.padEnd(40)} 0 KB  FAIL: ${err.message}`);
    return { file: filename, sizeKB: 0, ok: false };
  }
}

async function fetchFirstProductUrl() {
  const res = await fetch(`${BASE_URL}/wp-json/wp/v2/product_review?per_page=1`);
  if (!res.ok) throw new Error(`REST API error ${res.status}`);
  const data = await res.json();
  if (!data || !data[0]) throw new Error('No product_review posts found');
  return data[0].link;
}

(async () => {
  const results = [];
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.setViewportSize({ width: 1440, height: 900 });

  console.log('\nKitScore Screenshot Capture — dark mode only\n' + '='.repeat(50));

  // ── 1. Homepage ───────────────────────────────────────────────────
  console.log('\n[1] Homepage');
  await goTo(page, BASE_URL);
  await setDark(page);
  results.push(await capture(page, 'homepage-dark.png'));

  // ── 2. Browse by Sport section ────────────────────────────────────
  console.log('\n[2] Browse by Sport section');
  await goTo(page, BASE_URL);
  await setDark(page);
  await page.evaluate(() => {
    const el = document.querySelector('.gs-categories-section, [class*="categor"]');
    if (el) el.scrollIntoView({ behavior: 'instant', block: 'start' });
  });
  await page.waitForTimeout(600);
  results.push(await capture(page, 'categories-dark.png'));

  // ── 3. Top Rated Equipment section ───────────────────────────────
  console.log('\n[3] Top Rated Equipment section');
  await goTo(page, BASE_URL);
  await setDark(page);
  await page.evaluate(() => {
    const sec = document.querySelector('.gs-top-rated, .gs-home-featured');
    if (sec) { sec.scrollIntoView({ behavior: 'instant', block: 'start' }); return; }
    const headings = [...document.querySelectorAll('h2')];
    const target = headings.find(h => /top.?rated/i.test(h.textContent));
    if (target) target.scrollIntoView({ behavior: 'instant', block: 'start' });
  });
  await page.waitForTimeout(600);
  results.push(await capture(page, 'top-rated-dark.png'));

  // ── 4. Category page — Hiking ─────────────────────────────────────
  console.log('\n[4] Category page — /sport/hiking/');
  await goTo(page, `${BASE_URL}/sport/hiking/`);
  await setDark(page);
  results.push(await capture(page, 'category-hiking-dark.png'));

  // ── 5. Category page — Running ────────────────────────────────────
  console.log('\n[5] Category page — /sport/running/');
  await goTo(page, `${BASE_URL}/sport/running/`);
  await setDark(page);
  results.push(await capture(page, 'category-running-dark.png'));

  // ── Fetch first product URL once; reuse for 6, 7, 8 ─────────────
  let productUrl = `${BASE_URL}/reviews/`;
  try {
    productUrl = await fetchFirstProductUrl();
    console.log(`\n  → First product: ${productUrl}`);
  } catch (err) {
    console.warn(`\n  ! REST API unavailable (${err.message}), falling back to /reviews/`);
  }

  // ── 6. Single product page ────────────────────────────────────────
  console.log('\n[6] Single product page');
  await goTo(page, productUrl);
  await setDark(page);
  results.push(await capture(page, 'single-product-dark.png'));

  // ── 7. Comparison Snapshot section ───────────────────────────────
  console.log('\n[7] Comparison Snapshot section');
  await goTo(page, productUrl);
  await setDark(page);
  await page.evaluate(() => {
    const headings = [...document.querySelectorAll('h2')];
    const target = headings.find(h => /comparison/i.test(h.textContent));
    if (target) target.scrollIntoView({ behavior: 'instant', block: 'start' });
  });
  await page.waitForTimeout(600);
  results.push(await capture(page, 'comparison-dark.png'));

  // ── 8. Customer Reviews section ───────────────────────────────────
  console.log('\n[8] Customer Reviews section');
  await goTo(page, productUrl);
  await setDark(page);
  await page.evaluate(() => {
    const el = document.querySelector('#customer-reviews, .gs-customer-reviews-section');
    if (el) { el.scrollIntoView({ behavior: 'instant', block: 'start' }); return; }
    const headings = [...document.querySelectorAll('h2')];
    const target = headings.find(h => /customer\s+reviews/i.test(h.textContent));
    if (target) target.scrollIntoView({ behavior: 'instant', block: 'start' });
  });
  await page.waitForTimeout(600);
  results.push(await capture(page, 'reviews-section-dark.png'));

  // ── 9. Compare page ───────────────────────────────────────────────
  console.log('\n[9] Compare page');
  await goTo(page, `${BASE_URL}/compare/`);
  await setDark(page);
  results.push(await capture(page, 'compare-dark.png'));

  // ── 10. Reviews page — empty state ───────────────────────────────
  console.log('\n[10] Reviews page (empty state)');
  await goTo(page, `${BASE_URL}/reviews/`);
  await setDark(page);
  results.push(await capture(page, 'reviews-page-dark.png'));

  // ── 11. Reviews page — with loaded reviews ────────────────────────
  console.log('\n[11] Reviews page — loaded');
  await goTo(page, `${BASE_URL}/reviews/`);
  await setDark(page);
  try {
    await page.selectOption('#ks-cat-select, select[id*="cat"]', { index: 1 });
    await page.waitForTimeout(1000);
    await page.selectOption('#ks-prod-select, select[id*="prod"]', { index: 1 });
    await page.waitForTimeout(500);
    await page.click('button:has-text("Load Reviews"), #gs-load-reviews, .gs-reviews-load-btn');
    await page.waitForTimeout(2000);
  } catch (err) {
    console.warn(`  ! Interaction step failed: ${err.message}`);
  }
  results.push(await capture(page, 'reviews-loaded-dark.png'));

  // ── Navigation flow verification ──────────────────────────────────
  console.log('\n[12] Navigation flow — /sport/cycling/ → product page');
  await goTo(page, `${BASE_URL}/sport/cycling/`);
  await setDark(page);
  results.push(await capture(page, 'nav-flow-1-category-dark.png'));

  try {
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle', timeout: 20000 }),
      page.locator('.gs-product-card a, .gs-home-product-card, a[href*="product_review"]').first().click(),
    ]);
    await setDark(page);
    const landedUrl = page.url();
    console.log(`  → Landed on: ${landedUrl}`);
    results.push(await capture(page, 'nav-flow-2-product-dark.png'));
  } catch (err) {
    console.warn(`  ! Click-through failed: ${err.message}`);
    results.push({ file: 'nav-flow-2-product-dark.png', sizeKB: 0, ok: false });
  }

  await browser.close();

  // ── Summary ───────────────────────────────────────────────────────
  console.log('\n' + '='.repeat(50));
  console.log('Summary:\n');
  results.forEach(({ file, sizeKB, ok }) => {
    const status = ok ? 'success' : 'FAIL';
    console.log(`  ${ok ? '✓' : '✗'}  ${file.padEnd(42)} ${String(sizeKB).padStart(8)} KB  ${status}`);
  });
  const totalKB = results.reduce((a, { sizeKB }) => a + Number(sizeKB), 0);
  const passed = results.filter(r => r.ok).length;
  console.log(`\n  ${passed}/${results.length} succeeded — ${totalKB.toFixed(1)} KB total`);
  console.log(`  Saved to: ${SCREENSHOTS_DIR}\n`);
})();
