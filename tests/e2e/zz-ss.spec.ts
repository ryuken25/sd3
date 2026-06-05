import { test } from '@playwright/test';
import { login } from './fixtures/auth';

// Single-shot screenshot rapor untuk verifikasi kop & shadow.
// Output: hasil/ss.png
test('rapor admin detail — verify kop centered + no shadow', async ({ page }) => {
  await login(page, 'admin');
  await page.waitForURL(/\/admin\/dashboard/, { timeout: 10_000 });
  await page.goto('/admin/rapor/preview/244/5');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(400);
  await page.screenshot({ path: 'hasil/ss.png', fullPage: true });
});
