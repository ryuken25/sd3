import { test, expect } from '@playwright/test';
import { login } from './fixtures/auth';
import { RAPOR_SECTIONS, SISWA, TA_AKTIF_ID } from './fixtures/data';

/**
 * Admin "Manajemen Rapor" — daftar + halaman detail rapor lengkap.
 */
test.describe('Admin Manajemen Rapor', () => {
  test('daftar rapor tampil setelah filter TA + kelas', async ({ page }) => {
    await login(page, 'admin');
    await page.goto(`/admin/rapor?id_tahun_ajaran=${TA_AKTIF_ID}&id_kelas=3`);
    await expect(page.locator('table tbody tr').first()).toBeVisible();
    // Tombol menuju detail rapor lengkap tersedia.
    await expect(page.locator('a:has-text("Lihat Rapor Lengkap")').first()).toBeVisible();
  });

  test('detail rapor menampilkan SEMUA section PDF (bukan tabel ringkas)', async ({ page }) => {
    await login(page, 'admin');
    await page.goto(`/admin/rapor/preview/${SISWA.danendraKelas3.idSiswa}/${TA_AKTIF_ID}`);

    for (const sec of RAPOR_SECTIONS) {
      await expect(page.locator(`text=${sec}`).first()).toBeVisible();
    }
    // Kolom Capaian Kompetensi WAJIB ada (audit v1: dulu cuma KKM/Nilai/Huruf).
    await expect(page.locator('th:has-text("Capaian Kompetensi")')).toBeVisible();
    // Tanda tangan dengan NIP kepala sekolah.
    await expect(page.locator('text=198408132014062008')).toBeVisible();
  });

  test('detail rapor punya bar aksi admin', async ({ page }) => {
    await login(page, 'admin');
    await page.goto(`/admin/rapor/preview/${SISWA.danendraKelas3.idSiswa}/${TA_AKTIF_ID}`);
    await expect(page.locator('text=Aksi Admin')).toBeVisible();
  });
});
