import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';

/**
 * Tahun Ajaran — urutan kronologis menurun + hanya satu TA aktif.
 */
test.describe('Tahun Ajaran Ordering', () => {
  test('daftar TA urut kronologis menurun (terbaru di atas)', async ({ page }) => {
    await login(page, 'admin');
    await page.goto('/admin/tahun-ajaran');

    const rows = page.locator('table tbody tr');
    const count = await rows.count();
    expect(count).toBeGreaterThan(1);

    // Kumpulkan teks tiap baris, ekstrak "YYYY/YYYY" + semester.
    const labels: string[] = [];
    for (let i = 0; i < count; i++) {
      labels.push(((await rows.nth(i).textContent()) || '').replace(/\s+/g, ' ').trim());
    }

    // Hitung skor urut: tahun*10 + (Genap=2, Ganjil=1). Harus menurun.
    const skor = labels.map((t) => {
      const th = t.match(/(\d{4})\s*\/\s*\d{4}/);
      const tahun = th ? parseInt(th[1], 10) : 0;
      const sem = /genap/i.test(t) ? 2 : 1;
      return tahun * 10 + sem;
    });
    for (let i = 1; i < skor.length; i++) {
      expect(skor[i - 1]).toBeGreaterThanOrEqual(skor[i]);
    }
  });

  test('hanya satu Tahun Ajaran berstatus aktif', async ({ page }) => {
    await login(page, 'admin');
    await page.goto('/admin/tahun-ajaran');
    // Badge TA aktif berteks persis "Aktif Saat Ini" — harus tepat satu.
    const aktif = page.locator('.badge', { hasText: 'Aktif Saat Ini' });
    expect(await aktif.count()).toBe(1);
  });
});
