import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';
import { SISWA, TA_AKTIF_ID, KEPSEK } from './fixtures/data';
import * as fs from 'fs';
import * as path from 'path';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const pdfParse = require('pdf-parse'); // v1.1.1 — export fungsi langsung

/**
 * Generasi PDF rapor — cek konten lewat ekstraksi teks.
 */
test.describe('Generasi PDF Rapor', () => {
  test('PDF ter-download, valid, berisi section utama, TANPA badge online', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto(`/orangtua/rapor/${SISWA.danendraKelas3.idSiswa}/${TA_AKTIF_ID}`);

    const downloadPromise = page.waitForEvent('download');
    await page.click('a:has-text("Cetak Rapor")');
    const download = await downloadPromise;

    expect(download.suggestedFilename()).toMatch(/Rapor.*\.pdf$/i);

    const filePath = path.join('test-results', 'rapor-e2e.pdf');
    await download.saveAs(filePath);
    const data = await pdfParse(fs.readFileSync(filePath));

    // Section utama HARUS ada di PDF.
    expect(data.text).toContain('LAPORAN HASIL BELAJAR');
    expect(data.text).toMatch(/SD NEGERI 3 MEKARSARI/i);
    expect(data.text).toContain('Ketidakhadiran');
    expect(data.text).toContain('Catatan Wali Kelas');
    expect(data.text).toContain(KEPSEK.nama);
    expect(data.text).toContain(KEPSEK.nip);

    // Badge "Catatan dari guru" hanya untuk view online — TIDAK boleh di PDF.
    expect(data.text).not.toMatch(/Catatan dari guru/i);
  });
});
