import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';
import { RAPOR_SECTIONS, SISWA, TA_AKTIF_ID } from './fixtures/data';

/**
 * Orang Tua e-Rapor — layout lengkap + tombol cetak.
 */
test.describe('Orang Tua e-Rapor', () => {
  const url = `/orangtua/rapor/${SISWA.danendraKelas3.idSiswa}/${TA_AKTIF_ID}`;

  test('e-rapor menampilkan semua section PDF', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto(url);
    for (const sec of RAPOR_SECTIONS) {
      await expect(page.locator(`text=${sec}`).first()).toBeVisible();
    }
    await expect(page.locator('th:has-text("Capaian Kompetensi")')).toBeVisible();
  });

  test('tombol Cetak Rapor (PDF) tersedia', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto(url);
    await expect(page.locator('a:has-text("Cetak Rapor")')).toBeVisible();
  });

  test('dashboard orang tua tampil tanpa error', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto('/orangtua/dashboard');
    await expect(page.locator('text=/Dashboard/i').first()).toBeVisible();
  });
});
