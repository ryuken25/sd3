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
  // Full page (untuk konteks)
  await page.screenshot({ path: 'hasil/ss.png', fullPage: true });
  // Crop area kop (header rapor) saja untuk verifikasi detail
  const kop = page.locator('.rapor-kop');
  if (await kop.count()) {
    await kop.screenshot({ path: 'hasil/ss-kop.png' });
  }
});
