import { defineConfig, devices } from '@playwright/test';

/**
 * Konfigurasi Playwright E2E — Sistem Manajemen Nilai SDN 3 Mekarsari.
 *
 * webServer otomatis menjalankan `php spark serve` di port 8080.
 * Test berjalan serial (workers: 1) karena berbagi satu database.
 */
export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  timeout: 45_000,
  use: {
    // Port 8089 khusus SD3 — port 8080 dipakai aplikasi lain di mesin dev ini.
    baseURL: 'http://localhost:8089',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],
  webServer: {
    // CI4 spark serve butuh flag dipisah spasi (--port 8089), bukan --port=8089.
    // env app.baseURL meng-override Config\App::$baseURL supaya redirect &
    // base_url() memakai port 8089 (bukan 8080 yang dipakai aplikasi lain).
    command: 'php spark serve --port 8089',
    url: 'http://localhost:8089',
    env: { 'app.baseURL': 'http://localhost:8089/' },
    reuseExistingServer: false,
    timeout: 60_000,
  },
});
