import { Page, expect } from '@playwright/test';

/**
 * Kredensial login — DISESUAIKAN dengan data seeder nyata (SD3MekarsariSeeder):
 *   - admin & guru   : login pakai username + password 'password123'
 *   - orang tua      : login pakai NIS siswa sebagai username & password
 *
 * Login route = '/' (Auth::index menampilkan form, POST ke /auth/process).
 */
export const CREDENTIALS = {
  admin:           { username: 'kasrinayanti', password: 'password123' },
  guruKelas3:      { username: 'raipitriani',  password: 'password123' }, // wali kelas 3
  guruKelas6:      { username: 'ariwidnya',    password: 'password123' }, // wali kelas 6
  orangTuaKelas3:  { username: '909',          password: '909' },         // ortu DANENDRA (NIS 909)
  orangTuaKelas6:  { username: '871',          password: '871' },         // ortu I GEDE RAMA (NIS 871)
} as const;

export type Role = keyof typeof CREDENTIALS;

/** Login lewat form di halaman '/'. Field csrf hidden sudah ada di DOM. */
export async function login(page: Page, role: Role): Promise<void> {
  const { username, password } = CREDENTIALS[role];
  await page.goto('/');
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
}

export async function logout(page: Page): Promise<void> {
  await page.goto('/logout');
  await expect(page).toHaveURL(/\/$|\/login/);
}
