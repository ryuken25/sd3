// Recon Google Form: capture struktur pertanyaan per halaman + screenshot.
// Output: scripts/form-recon.json + scripts/form-page-N.png
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const URL = 'https://docs.google.com/forms/d/e/1FAIpQLSf_Xdwb1cIBrd9At5C6_Ubs8thPknmLE4uP0M9YzaKP3W5-Fw/viewform?fbzx=7483271838287352170';
const OUT = path.resolve(__dirname);

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  await page.goto(URL, { waitUntil: 'networkidle' });

  const pages = [];
  let pageIdx = 1;

  while (true) {
    await page.waitForTimeout(700);
    // Capture screenshot
    await page.screenshot({ path: path.join(OUT, `form-page-${pageIdx}.png`), fullPage: true });

    const dump = await page.evaluate(() => {
      const out = [];
      // Setiap pertanyaan ada di div[role="listitem"]
      const items = document.querySelectorAll('div[role="listitem"]');
      items.forEach((item, i) => {
        const heading = item.querySelector('[role="heading"]');
        const title = heading ? heading.textContent.trim() : '';
        if (!title) return;

        // Cek radio / textbox / radiogroup
        const radios = Array.from(item.querySelectorAll('[role="radio"]')).map(r => ({
          name: (r.getAttribute('aria-label') || '').trim(),
          checked: r.getAttribute('aria-checked') === 'true',
        }));
        const text = item.querySelector('input[type="text"], textarea');
        const radiogroupCols = item.querySelectorAll('[role="radiogroup"] [role="radio"]');

        out.push({
          index: i,
          title: title.slice(0, 200),
          type: radios.length > 0 ? 'radio' : (text ? 'text' : 'unknown'),
          radio_count: radios.length,
          radio_labels: radios.map(r => r.name).slice(0, 12),
          text_placeholder: text ? (text.getAttribute('aria-label') || text.placeholder || '') : null,
        });
      });
      // Header (judul section)
      const sectionTitle = (document.querySelector('div[role="heading"]')?.textContent || '').trim();
      return { sectionTitle, items: out };
    });

    pages.push({ pageNo: pageIdx, ...dump });
    console.log(`[page ${pageIdx}] section="${dump.sectionTitle}", ${dump.items.length} items`);
    dump.items.forEach(it => console.log(`   - ${it.type.padEnd(7)} | ${it.title}`));

    // Cek tombol next / submit
    const nextBtn = await page.$('div[role="button"]:has-text("Berikutnya"), div[role="button"]:has-text("Next")');
    const submitBtn = await page.$('div[role="button"]:has-text("Kirim"), div[role="button"]:has-text("Submit")');

    if (submitBtn && !nextBtn) {
      console.log('-> Found Submit button. Stopping recon (NOT submitting).');
      break;
    }
    if (!nextBtn) {
      console.log('-> No next button. Stopping.');
      break;
    }

    // Klik next — tapi pertama kita HARUS mengisi minimal field "required" supaya bisa next
    // Untuk recon: cukup pilih opsi pertama tiap radio + isi text "RECON" supaya validasi lewat
    await page.evaluate(() => {
      document.querySelectorAll('div[role="listitem"]').forEach(item => {
        const text = item.querySelector('input[type="text"], textarea');
        if (text && !text.value) {
          text.focus();
          text.value = 'RECON';
          text.dispatchEvent(new Event('input', { bubbles: true }));
          text.dispatchEvent(new Event('change', { bubbles: true }));
          return;
        }
        const radios = item.querySelectorAll('[role="radio"]');
        if (radios.length) radios[0].click();
      });
    });
    await page.waitForTimeout(400);
    await nextBtn.click();
    pageIdx++;
    if (pageIdx > 10) { console.log('Safety stop @ page 10'); break; }
  }

  fs.writeFileSync(path.join(OUT, 'form-recon.json'), JSON.stringify(pages, null, 2));
  console.log(`\nSaved -> ${path.join(OUT, 'form-recon.json')}`);
  await browser.close();
})();
