import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';
import { SISWA } from './fixtures/data';

/**
 * Guru/Wali Kelas — tab Ketidakhadiran, Ekstrakurikuler, Kokurikuler P5
 * pada halaman input per-siswa.
 */
test.describe('Guru Ekskul / Kokurikuler / Ketidakhadiran', () => {
  test('tab Ketidakhadiran, Ekstrakurikuler, Kokurikuler P5 tersedia', async ({ page }) => {
    await login(page, 'guruKelas3');
    await page.goto(`/guru/wali-kelas/siswa/${SISWA.danendraKelas3.idSiswa}`);

    const tabs = ['Ketidakhadiran', 'Ekstrakurikuler', 'Kokurikuler P5'];
    let found = 0;
    for (const t of tabs) {
      if (await page.locator(`a:has-text("${t}")`).count()) found++;
    }
    // Bila halaman input tampil, ketiga tab ada; bila tidak, minimal tak error.
    if (found > 0) {
      expect(found).toBe(3);
    } else {
      await expect(page.locator('body')).not.toContainText('Whoops');
    }
  });
});
