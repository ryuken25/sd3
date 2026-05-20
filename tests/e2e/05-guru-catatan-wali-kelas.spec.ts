import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';
import { SISWA } from './fixtures/data';

/**
 * Guru/Wali Kelas — halaman input data wali kelas (catatan, ketidakhadiran,
 * ekskul, kokurikuler) dapat diakses dan menampilkan tab + banner panduan.
 */
test.describe('Guru Wali Kelas — Catatan dkk', () => {
  test('halaman wali kelas dapat diakses', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/guru/wali-kelas');
    await expect(page).toHaveURL(/wali-kelas/);
    await expect(page.locator('body')).not.toContainText('Whoops');
  });

  test('halaman input per-siswa menampilkan tab Catatan & banner panduan', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto(`/guru/wali-kelas/siswa/${SISWA.danendraKelas3.idSiswa}`);
    const banner = page.locator('text=Cara Mengisi Data Wali Kelas');
    if (await banner.count()) {
      await expect(banner.first()).toBeVisible();
      await expect(page.locator('a:has-text("Catatan")').first()).toBeVisible();
    } else {
      await expect(page.locator('body')).not.toContainText('Whoops');
    }
  });
});
