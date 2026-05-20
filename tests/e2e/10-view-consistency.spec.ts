import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';
import { RAPOR_SECTIONS, SISWA, TA_AKTIF_ID } from './fixtures/data';

/**
 * Test KONSISTENSI — admin "Detail Rapor" dan e-rapor orang tua HARUS
 * merender section yang sama (keduanya pakai shared partial _full_layout).
 */
test.describe('Konsistensi View Admin vs Orang Tua', () => {
  const idSiswa = SISWA.danendraKelas3.idSiswa;

  test('admin detail rapor menampilkan semua section PDF', async ({ page }) => {
    await login(page, 'admin');
    await page.goto(`/admin/rapor/preview/${idSiswa}/${TA_AKTIF_ID}`);
    for (const sec of RAPOR_SECTIONS) {
      await expect(page.locator(`text=${sec}`).first()).toBeVisible();
    }
    await expect(page.locator('th:has-text("Capaian Kompetensi")')).toBeVisible();
  });

  test('e-rapor orang tua menampilkan semua section PDF', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto(`/orangtua/rapor/${idSiswa}/${TA_AKTIF_ID}`);
    for (const sec of RAPOR_SECTIONS) {
      await expect(page.locator(`text=${sec}`).first()).toBeVisible();
    }
    await expect(page.locator('th:has-text("Capaian Kompetensi")')).toBeVisible();
  });

  test('admin & orang tua merender container rapor yang sama', async ({ browser }) => {
    const ctxAdmin = await browser.newContext();
    const ctxOrtu = await browser.newContext();
    const pageAdmin = await ctxAdmin.newPage();
    const pageOrtu = await ctxOrtu.newPage();

    await login(pageAdmin, 'admin');
    await login(pageOrtu, 'orangTuaKelas3');

    await pageAdmin.goto(`/admin/rapor/preview/${idSiswa}/${TA_AKTIF_ID}`);
    await pageOrtu.goto(`/orangtua/rapor/${idSiswa}/${TA_AKTIF_ID}`);

    // Kedua view memakai partika rapor/_full_layout → div.rapor-container ada di keduanya.
    await expect(pageAdmin.locator('.rapor-container')).toBeVisible();
    await expect(pageOrtu.locator('.rapor-container')).toBeVisible();

    // Header section group ("Mata Pelajaran Wajib") sama persis.
    const adminGroups = await pageAdmin.locator('.rapor-row-group').allTextContents();
    const ortuGroups = await pageOrtu.locator('.rapor-row-group').allTextContents();
    expect(adminGroups).toEqual(ortuGroups);

    await ctxAdmin.close();
    await ctxOrtu.close();
  });
});
