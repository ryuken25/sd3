import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';

test.describe('Autentikasi', () => {
  test('admin bisa login dan diarahkan ke /admin', async ({ page }) => {
    await login(page, 'admin');
    await expect(page).toHaveURL(/\/admin/);
  });

  test('guru kelas 3 bisa login dan diarahkan ke /guru', async ({ page }) => {
    await login(page, 'guruKelas3');
    await expect(page).toHaveURL(/\/guru/);
  });

  test('orang tua bisa login dan diarahkan ke /orangtua', async ({ page }) => {
    await login(page, 'orangTuaKelas3');
    await expect(page).toHaveURL(/\/orangtua/);
  });

  test('kredensial salah menampilkan pesan error', async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="username"]', 'kasrinayanti');
    await page.fill('input[name="password"]', 'password-salah');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=/salah|invalid|gagal|tidak/i')).toBeVisible();
  });

  test('logout mengembalikan ke halaman login', async ({ page }) => {
    await login(page, 'admin');
    await page.goto('/logout');
    await expect(page.locator('input[name="username"]')).toBeVisible();
  });
});
