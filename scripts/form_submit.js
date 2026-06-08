// Bulk submit Google Form dari data.xlsx
// Usage:
//   node scripts/form_submit.js              # submit semua (R2-R36)
//   node scripts/form_submit.js 2 3          # submit hanya row 2 & 3 (1-based row di xlsx)
//   HEADFUL=1 node scripts/form_submit.js    # jalankan headful (untuk debug)
//   DRYRUN=1 node scripts/form_submit.js     # isi tapi tidak klik submit
//
// Mapping kolom xlsx -> Q form:
//   C "Nama"                                 -> Page1: text
//   D "Kategori responden"                   -> Page1: radio (Umum)
//   E "Saya bersedia..."                     -> Page1: radio (Ya/ya/Tidak)
//   F-O 10 SUS questions, skala 1-5          -> Page2: 10 radios

const { chromium } = require('playwright');
const openpyxlPath = require('path');
const { execSync } = require('child_process');
const fs = require('fs');

const URL = 'https://docs.google.com/forms/d/e/1FAIpQLSf_Xdwb1cIBrd9At5C6_Ubs8thPknmLE4uP0M9YzaKP3W5-Fw/viewform';
const HEADFUL = !!process.env.HEADFUL;
const DRYRUN  = !!process.env.DRYRUN;

// Parse xlsx via python (sudah ada openpyxl)
function loadRows() {
  const out = execSync(
    `python -c "import openpyxl,json; wb=openpyxl.load_workbook('data.xlsx',data_only=True); ws=wb.active; rows=[[c.value for c in row] for row in ws.iter_rows()]; print(json.dumps(rows, default=str))"`,
    { encoding: 'utf8', maxBuffer: 16 * 1024 * 1024 }
  );
  return JSON.parse(out);
}

function normCell(v) {
  if (v === null || v === undefined) return '';
  return String(v).trim();
}

async function clickRadioInItem(itemHandle, targetLabel, exact = false) {
  // Cari role=radio di dalam item yang aria-label-nya match
  const handles = await itemHandle.$$('[role="radio"]');
  const want = targetLabel.toLowerCase();
  for (const h of handles) {
    const label = (await h.getAttribute('aria-label') || '').trim().toLowerCase();
    if (exact ? label === want : label.includes(want)) {
      await h.click();
      return true;
    }
  }
  return false;
}

async function fillPage1(page, row) {
  await page.waitForSelector('div[role="listitem"]');
  await page.waitForTimeout(300);

  const nama       = normCell(row[2]);
  const kategori   = normCell(row[3]);
  const bersedia   = normCell(row[4]).toLowerCase();

  const items = await page.$$('div[role="listitem"]');

  // Field 1: Nama
  const textInput = await items[0].$('input[type="text"], textarea');
  if (!textInput) throw new Error('Text input Nama tidak ditemukan');
  await textInput.fill(nama);

  // Field 2: Kategori (substring match — "Umum" cocok dengan opsi "Umum")
  if (!(await clickRadioInItem(items[1], kategori, false))) {
    throw new Error(`Kategori "${kategori}" tidak ditemukan`);
  }

  // Field 3: Bersedia — form pakai "ya" lowercase / "Tidak"
  const bersediaLabel = (bersedia === 'ya' || bersedia === 'y') ? 'ya' : 'Tidak';
  if (!(await clickRadioInItem(items[2], bersediaLabel, true))) {
    throw new Error(`Bersedia "${bersediaLabel}" tidak ditemukan`);
  }

  // Klik "Berikutnya"
  const nextBtn = page.locator('div[role="button"]', { hasText: /Berikutnya|Next/ }).first();
  await nextBtn.click();
  await page.waitForLoadState('networkidle');
}

async function fillPage2(page, row) {
  await page.waitForSelector('div[role="listitem"]');
  await page.waitForTimeout(300);

  // Page 2 punya 11 items: idx 0 = section header (unknown), idx 1-10 = 10 SUS radios
  // Tapi recon pakai role="listitem" — header juga listitem. Pilih hanya yang punya radio.
  const items = await page.$$('div[role="listitem"]');
  // Filter items yang punya radio
  const radioItems = [];
  for (const it of items) {
    if (await it.$('[role="radio"]')) radioItems.push(it);
  }
  if (radioItems.length !== 10) {
    console.warn(`  ! Expected 10 SUS items, got ${radioItems.length}`);
  }

  // SUS values di xlsx kolom F-O (indeks 5-14)
  for (let q = 0; q < radioItems.length; q++) {
    const val = normCell(row[5 + q]);
    if (!val) continue;
    if (!(await clickRadioInItem(radioItems[q], val, true))) {
      throw new Error(`SUS Q${q + 1} value "${val}" tidak ditemukan`);
    }
  }
}

async function submit(page) {
  if (DRYRUN) {
    console.log('  [DRYRUN] skip submit');
    return;
  }
  const submitBtn = page.locator('div[role="button"]', { hasText: /Kirim|Submit/ }).first();
  await submitBtn.click();
  // Tunggu halaman konfirmasi
  await page.waitForFunction(
    () => /respon|terima kasih|response|recorded/i.test(document.body.innerText),
    { timeout: 15000 }
  ).catch(() => {});
}

async function submitOne(browser, row, idxLabel) {
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  try {
    await page.goto(URL, { waitUntil: 'networkidle' });
    await fillPage1(page, row);
    await fillPage2(page, row);
    await submit(page);
    console.log(`  ✓ [${idxLabel}] ${normCell(row[2])} (${normCell(row[3])})`);
  } finally {
    await ctx.close();
  }
}

(async () => {
  const rows = loadRows();
  console.log(`Loaded ${rows.length} rows (incl. header) from data.xlsx`);

  // Argument: angka row 1-based di xlsx (yang punya data, mulai dari 2)
  const argRows = process.argv.slice(2).map(n => parseInt(n, 10)).filter(n => !isNaN(n));
  const targetRows = argRows.length
    ? argRows.map(i => ({ rowNo: i, row: rows[i - 1] }))
    : rows.slice(1).map((r, i) => ({ rowNo: i + 2, row: r }));

  console.log(`Will submit ${targetRows.length} response(s)${DRYRUN ? ' [DRYRUN]' : ''}${HEADFUL ? ' [HEADFUL]' : ''}`);

  const browser = await chromium.launch({ headless: !HEADFUL, slowMo: HEADFUL ? 150 : 0 });

  let ok = 0, fail = 0;
  for (const { rowNo, row } of targetRows) {
    try {
      await submitOne(browser, row, `R${rowNo}`);
      ok++;
    } catch (e) {
      console.error(`  ✗ [R${rowNo}] ${normCell(row[2])} -> ${e.message}`);
      fail++;
    }
    // Random delay 1-3s antar submit supaya tidak terlihat bot
    const delay = 1000 + Math.floor(Math.random() * 2000);
    await new Promise(r => setTimeout(r, delay));
  }

  await browser.close();
  console.log(`\nDone. ok=${ok}, fail=${fail}`);
  process.exit(fail ? 1 : 0);
})();
