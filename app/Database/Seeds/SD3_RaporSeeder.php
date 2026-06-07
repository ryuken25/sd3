<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: rapor untuk SEMUA 5 TA.
 *
 * TA nonaktif (history) → is_finalized = 1, finalized_at = tanggal_selesai + 14 hari.
 * TA aktif (2025/2026 Ganjil) → is_finalized = 0 (belum difinalisasi).
 *
 * Absensi deterministik. Status kenaikan: Kelas 6 = Lulus, remedial+alpa > 1 = Tidak Naik, sisanya = Naik.
 */
class SD3_RaporSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [8/9] Rapor ... ";

        $allTa = $this->db->table('tahun_ajaran')
            ->orderBy('id_tahun_ajaran', 'ASC')
            ->get()->getResultArray();

        if (empty($allTa)) {
            echo "✗ Tidak ada data tahun ajaran!\n";
            return;
        }

        $inserted = 0;
        $skipped  = 0;

        foreach ($allTa as $ta) {
            $taId       = (int) $ta['id_tahun_ajaran'];
            $isHistory  = ($ta['aktif'] === 'nonaktif');
            $finalizedAt = null;

            if ($isHistory && !empty($ta['tanggal_selesai'])) {
                $finalizedAt = date('Y-m-d H:i:s', strtotime($ta['tanggal_selesai'] . ' +14 days'));
            }

            $allSiswa = $this->db->table('siswa')
                ->where('id_tahun_ajaran', $taId)
                ->where('status', 'aktif')
                ->get()->getResultArray();

            foreach ($allSiswa as $siswa) {
                $siswaId = (int) $siswa['id_siswa'];

                $existing = $this->db->table('rapor')
                    ->where('id_siswa', $siswaId)
                    ->where('id_tahun_ajaran', $taId)
                    ->get()->getRow();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Pasca merge: status_kelulusan inline di tabel `nilai`.
                $hasRemedial = $this->db->table('nilai')
                    ->where('id_siswa', $siswaId)
                    ->where('id_tahun_ajaran', $taId)
                    ->where('status_kelulusan', 'Remedial')
                    ->countAllResults() > 0;

                $kelasRow = $this->db->table('kelas')
                    ->where('id_kelas', $siswa['id_kelas'])
                    ->get()->getRow();
                $tingkat  = $kelasRow ? (int) $kelasRow->tingkat : 1;

                $seed  = $siswaId * 13;
                $sakit = $hasRemedial ? ($seed % 4) : ($seed % 3);
                $izin  = ($seed * 3) % 3;
                $alpa  = $hasRemedial ? (($seed * 7) % 2) : 0;

                if ($tingkat === 6) {
                    $statusKenaikan = 'Lulus';
                } elseif ($hasRemedial && $alpa > 1) {
                    $statusKenaikan = 'Tidak Naik';
                } else {
                    $statusKenaikan = 'Naik';
                }

                $catatan = $hasRemedial
                    ? 'Siswa perlu perhatian lebih dalam belajar. Diharapkan orang tua ikut memotivasi.'
                    : 'Siswa menunjukkan perkembangan yang baik selama semester ini.';

                $this->db->table('rapor')->insert([
                    'id_siswa'           => $siswaId,
                    'id_tahun_ajaran'    => $taId,
                    'sakit'              => $sakit,
                    'izin'               => $izin,
                    'alpa'               => $alpa,
                    'catatan_wali_kelas' => $catatan,
                    'status_kenaikan'    => $statusKenaikan,
                    'is_finalized'       => $isHistory ? 1 : 0,
                    'finalized_at'       => $isHistory ? $finalizedAt : null,
                    'finalized_by'       => null,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            }
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
