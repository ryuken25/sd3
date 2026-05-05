<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: Rapor per siswa per tahun ajaran aktif
 *
 * Data presensi contoh (realistis):
 *   - Mayoritas 0-1 alpha, 0-2 izin, 0-2 sakit
 *   - Siswa remedial sedikit lebih banyak absen
 *
 * Status kenaikan:
 *   - Jika ada nilai Remedial di nilai_akhir => cek apakah sudah selesai
 *   - Untuk seeder awal: jika semua Tuntas => Naik, jika ada remedial => tetap Naik (asumsi remedial diselesaikan)
 *   - Kelas 6: status = Lulus
 */
class SD3_RaporSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [8/9] Rapor ... ";

        $ta = $this->db->table('tahun_ajaran')
            ->where('aktif', 'aktif')
            ->get()->getRow();

        if (!$ta) {
            echo "✗ Tahun ajaran aktif tidak ditemukan!\n";
            return;
        }

        $taId = (int) $ta->id_tahun_ajaran;

        $inserted = 0;
        $skipped = 0;

        for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
            $kelas = $this->db->table('kelas')
                ->where('tingkat', $tingkat)
                ->get()->getRow();

            if (!$kelas)
                continue;

            $siswaList = $this->db->table('siswa')
                ->where('id_kelas', $kelas->id_kelas)
                ->where('status', 'aktif')
                ->get()->getResultArray();

            foreach ($siswaList as $siswa) {
                $siswaId = (int) $siswa['id_siswa'];

                // Cek apakah rapor sudah ada
                $existing = $this->db->table('rapor')
                    ->where('id_siswa', $siswaId)
                    ->where('id_tahun_ajaran', $taId)
                    ->get()->getRow();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Cek apakah ada nilai remedial
                $hasRemedial = $this->db->table('nilai_akhir')
                    ->where('id_siswa', $siswaId)
                    ->where('id_tahun_ajaran', $taId)
                    ->where('status_kelulusan', 'Remedial')
                    ->countAllResults() > 0;

                // Absensi contoh deterministik
                $seed = $siswaId * 13;
                $sakit = ($hasRemedial) ? ($seed % 4) : ($seed % 3);
                $izin = ($seed * 3) % 3;
                $alpa = ($hasRemedial) ? (($seed * 7) % 2) : 0;

                // Status kenaikan
                if ($tingkat === 6) {
                    $statusKenaikan = 'Lulus';
                } elseif ($hasRemedial && $alpa > 1) {
                    $statusKenaikan = 'Tidak Naik';
                } else {
                    $statusKenaikan = 'Naik';
                }

                // Catatan wali kelas contoh
                $catatan = $hasRemedial
                    ? 'Siswa perlu perhatian lebih dalam belajar. Diharapkan orang tua ikut memotivasi.'
                    : 'Siswa menunjukkan perkembangan yang baik selama semester ini.';

                $this->db->table('rapor')->insert([
                    'id_siswa' => $siswaId,
                    'id_tahun_ajaran' => $taId,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'catatan_wali_kelas' => $catatan,
                    'status_kenaikan' => $statusKenaikan,
                    'is_finalized' => 0,
                    'finalized_at' => null,
                    'finalized_by' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            }
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
