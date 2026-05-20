import { test } from '@playwright/test';
import { login } from './fixtures/auth';

/**
 * Utility spec — bukan assertion test, tapi merekam screenshot hasil
 * perubahan ke folder /hasil untuk dokumentasi visual.
 *
 * Jalankan: npx playwright test 00-screenshots
 */

const HASIL = 'hasil';

test.describe('Screenshot dokumentasi /hasil', () => {
  test('e-rapor orang tua (unified layout)', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto('/orangtua/rapor/244/5');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/01-erapor-ortu.png`, fullPage: true });
  });

  test('admin detail rapor (unified layout + aksi admin)', async ({ page }) => {
    await login(page, 'admin');
    await page.goto('/admin/rapor/preview/244/5');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/02-admin-detail-rapor.png`, fullPage: true });
  });

  test('admin manajemen rapor (daftar)', async ({ page }) => {
    await login(page, 'admin');
    await page.goto('/admin/rapor?id_tahun_ajaran=5&id_kelas=3');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/03-admin-manajemen-rapor.png`, fullPage: true });
  });

  test('dashboard orang tua', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto('/orangtua/dashboard');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/04-dashboard-ortu.png`, fullPage: true });
  });
});
