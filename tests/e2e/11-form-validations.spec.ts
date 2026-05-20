import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';

/**
 * Validasi & keamanan dasar — role gating dan halaman bantuan.
 */
test.describe('Validasi & Authorization', () => {
  test('orang tua tidak bisa membuka area admin (role gating)', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await page.goto('/admin/rapor');
    // RoleFilter mengarahkan kembali ke area sendiri / bukan halaman admin.
    await expect(page).not.toHaveURL(/\/admin\/rapor/);
  });

  test('guru tidak bisa membuka area admin', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/admin/dashboard');
    await expect(page).not.toHaveURL(/\/admin\/dashboard$/);
  });

  test('akses tanpa login diarahkan ke halaman login', async ({ page }) => {
    await page.context().clearCookies();
    await page.goto('/admin/rapor');
    await expect(page.locator('input[name="username"]')).toBeVisible();
  });

  test('halaman panduan bantuan dapat diakses semua role login', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto('/help/panduan-rapor');
    await expect(page.locator('text=Panduan Penggunaan Rapor').first()).toBeVisible();
    await expect(page.locator('text=Glosarium').first()).toBeVisible();
  });
});
