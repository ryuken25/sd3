import { test } from '@playwright/test';
import { login } from './fixtures/auth';

/**
 * QA visual pass — capture full-page screenshots of all major pages
 * to spot styling bugs (broken filters, misaligned tables, sidebar
 * issues, etc). Bukan assertion test; outputnya untuk direview manual.
 *
 * Jalankan: npx playwright test qa-style-check
 * Output: hasil/qa/*.png
 */

const QA = 'hasil/qa';

async function snap(page: any, name: string) {
  await page.waitForLoadState('networkidle');
  // Beri sedikit jeda untuk transisi (modal, dropdown, dll)
  await page.waitForTimeout(200);
  await page.screenshot({ path: `${QA}/${name}.png`, fullPage: true });
}

test.describe('QA — login & landing', () => {
  test('login (logged out)', async ({ page }) => {
    await page.goto('/');
    await snap(page, '00-login');
  });
});

test.describe('QA — Admin', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, 'admin');
  });

  test('admin dashboard', async ({ page }) => {
    await page.goto('/admin/dashboard');
    await snap(page, 'admin-00-dashboard');
  });

  test('admin siswa', async ({ page }) => {
    await page.goto('/admin/siswa');
    await snap(page, 'admin-01-siswa');
  });

  test('admin guru', async ({ page }) => {
    await page.goto('/admin/guru');
    await snap(page, 'admin-02-guru');
  });

  test('admin kelas', async ({ page }) => {
    await page.goto('/admin/kelas');
    await snap(page, 'admin-03-kelas');
  });

  test('admin mapel', async ({ page }) => {
    await page.goto('/admin/mapel');
    await snap(page, 'admin-04-mapel');
  });

  test('admin kkm (filter alignment)', async ({ page }) => {
    await page.goto('/admin/kkm');
    await snap(page, 'admin-05-kkm-no-filter');
    await page.goto('/admin/kkm?id_tahun_ajaran=5&id_kelas=3');
    await snap(page, 'admin-05b-kkm-with-filter');
  });

  test('admin tahun ajaran (reorder)', async ({ page }) => {
    await page.goto('/admin/tahun-ajaran');
    await snap(page, 'admin-06-tahun-ajaran');
  });

  test('admin rapor list', async ({ page }) => {
    await page.goto('/admin/rapor');
    await snap(page, 'admin-07-rapor-list');
    await page.goto('/admin/rapor?id_tahun_ajaran=5&id_kelas=3');
    await snap(page, 'admin-07b-rapor-filtered');
  });

  test('admin rapor detail (kop)', async ({ page }) => {
    await page.goto('/admin/rapor/preview/244/5');
    await snap(page, 'admin-08-rapor-detail-kop');
  });

  test('admin import siswa', async ({ page }) => {
    await page.goto('/admin/import');
    await snap(page, 'admin-09-import');
  });

  test('admin request buka nilai', async ({ page }) => {
    await page.goto('/admin/request-buka-nilai');
    await snap(page, 'admin-10-request-buka-nilai');
  });

  // Template catatan wali kelas tidak di-manage admin (route tidak ada);
  // diambil dari MasterTemplateCatatanModel, dipakai langsung di form guru wali kelas.
});

test.describe('QA — Guru', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, 'guruKelas3');
  });

  test('guru dashboard', async ({ page }) => {
    await page.goto('/guru/dashboard');
    await snap(page, 'guru-00-dashboard');
  });

  test('guru nilai-harian (filter form)', async ({ page }) => {
    await page.goto('/guru/nilai-harian');
    await snap(page, 'guru-01-nilai-harian');
  });

  test('guru nilai-ujian (filter form)', async ({ page }) => {
    await page.goto('/guru/nilai-ujian');
    await snap(page, 'guru-02-nilai-ujian');
  });

  test('guru penilaian-agregat (list+input form)', async ({ page }) => {
    await page.goto('/guru/penilaian-agregat');
    await snap(page, 'guru-03-penilaian-agregat-pick');
    await page.goto('/guru/penilaian-agregat/input?id_kelas=3&id_mapel=1&id_tahun_ajaran=5');
    await snap(page, 'guru-03b-penilaian-agregat-input');
  });

  test('guru capaian kompetensi pick', async ({ page }) => {
    await page.goto('/guru/capaian-kompetensi');
    await snap(page, 'guru-04-capaian-pick');
  });

  test('guru template capaian (band picker)', async ({ page }) => {
    await page.goto('/guru/template-capaian');
    await snap(page, 'guru-05-template-pick');
    await page.goto('/guru/template-capaian?id_mapel=1&fase=B&semester=Ganjil');
    await snap(page, 'guru-05b-template-band-form');
  });

  test('guru nilai akhir', async ({ page }) => {
    await page.goto('/guru/nilai-akhir');
    await snap(page, 'guru-06-nilai-akhir-pick');
  });

  test('guru rekap remedial', async ({ page }) => {
    await page.goto('/guru/nilai-akhir/rekap-remedial');
    await snap(page, 'guru-07-rekap-remedial');
  });

  test('guru wali kelas (list anak)', async ({ page }) => {
    await page.goto('/guru/wali-kelas');
    await snap(page, 'guru-08-wali-kelas-list');
  });

  test('guru wali kelas siswa (form lengkap)', async ({ page }) => {
    await page.goto('/guru/wali-kelas/siswa/244');
    await snap(page, 'guru-08b-wali-kelas-siswa');
  });

  test('guru request buka nilai', async ({ page }) => {
    await page.goto('/guru/request-buka-nilai');
    await snap(page, 'guru-09-request-buka-nilai');
  });
});

test.describe('QA — Orang Tua', () => {
  test.beforeEach(async ({ page }) => {
    await login(page, 'orangTuaKelas3');
  });

  test('ortu dashboard', async ({ page }) => {
    await page.goto('/orangtua/dashboard');
    await snap(page, 'ortu-00-dashboard');
  });

  test('ortu rapor (kop + isi)', async ({ page }) => {
    await page.goto('/orangtua/rapor/244/5');
    await snap(page, 'ortu-01-rapor-kop');
  });
});
