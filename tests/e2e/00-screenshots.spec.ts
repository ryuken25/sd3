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

  test('halaman panduan in-app (/help/panduan-rapor)', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/help/panduan-rapor');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/05-help-panduan-rapor.png`, fullPage: true });
  });

  test('kelola template capaian 4-band', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/guru/template-capaian?id_mapel=1&fase=B&semester=Ganjil');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/06-template-capaian-band.png`, fullPage: true });
  });

  test('input CP per siswa (prefill band → textarea)', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/guru/capaian-kompetensi/input?id_kelas=3&id_mapel=1&id_tahun_ajaran=5');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/07-input-cp-band.png`, fullPage: true });
  });

  test('wali kelas — ekskul wajib + koko narasi', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/guru/wali-kelas/siswa/244');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${HASIL}/08-wali-kelas-siswa.png`, fullPage: true });
  });
});
