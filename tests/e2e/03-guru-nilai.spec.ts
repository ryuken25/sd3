import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';

/**
 * Guru — Nilai Akhir. Fokus: menu sudah di-rename "Nilai Akhir"
 * (bukan "Nilai Akhir & Remedial") dan halaman dapat diakses.
 */
test.describe('Guru Nilai Akhir', () => {
  test('navbar guru menampilkan "Nilai Akhir" (tanpa "& Remedial")', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/guru/dashboard');
    const sidebar = page.locator('.sidebar, nav, aside').first();
    await expect(sidebar.locator('a:has-text("Nilai Akhir")').first()).toBeVisible();
    await expect(page.locator('a:has-text("Nilai Akhir & Remedial")')).toHaveCount(0);
  });

  test('halaman Nilai Akhir dapat diakses', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/guru/nilai-akhir');
    await expect(page).toHaveURL(/nilai-akhir/);
    await expect(page.locator('body')).not.toContainText('Whoops');
  });
});
