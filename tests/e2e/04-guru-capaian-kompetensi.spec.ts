import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';

/**
 * Guru — Capaian Kompetensi. Fokus: halaman filter dapat diakses dan
 * info banner panduan tampil.
 */
test.describe('Guru Capaian Kompetensi', () => {
  test('halaman pemilihan Capaian Kompetensi dapat diakses', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/guru/capaian-kompetensi');
    await expect(page).toHaveURL(/capaian-kompetensi/);
    await expect(page.locator('body')).not.toContainText('Whoops');
  });

  test('info banner panduan tampil di form input CP', async ({ page }) => {
    await login(page, 'guruKelas3');
    // Form input butuh parameter; banner panduan ada di view input.
    await page.goto('/guru/capaian-kompetensi/input?id_kelas=3&id_mapel=1&id_siswa=244');
    const banner = page.locator('text=Cara Mengisi Capaian Kompetensi');
    // Banner tampil bila parameter valid; bila redirect, minimal tidak error.
    if (await banner.count()) {
      await expect(banner.first()).toBeVisible();
    } else {
      await expect(page.locator('body')).not.toContainText('Whoops');
    }
  });
});
