<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * TestDataSeeder — data deterministik untuk E2E Playwright (Megaprompt v2 Bagian 4).
 *
 * Kredensial login MEMAKAI akun existing dari SD3MekarsariSeeder (sesuai
 * keputusan: test disesuaikan ke kredensial nyata) — seeder ini TIDAK membuat
 * user baru. Tugasnya hanya menjamin keadaan deterministik:
 *
 *   - Minimal satu nilai_akhir = 75 (borderline) dengan catatan_remedial terisi
 *     + flag_borderline_75 = 1, untuk siswa DANENDRA (NIS 909) di TA aktif —
 *     supaya badge "Catatan dari guru" bisa diuji.
 *   - Rapor siswa tersebut difinalisasi supaya e-rapor & PDF dapat dibuka.
 *
 * Idempotent. Jalankan setelah SD3MekarsariSeeder.
 */
class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ TestDataSeeder (data deterministik E2E) ... ";

        $db = $this->db;

        $taAktif = $db->table('tahun_ajaran')->where('aktif', 'aktif')->get()->getRowArray();
        if (!$taAktif) {
            echo "✗ Tidak ada TA aktif.\n";
            return;
        }
        $taId = (int) $taAktif['id_tahun_ajaran'];

        // Siswa DANENDRA (NIS 909) di TA aktif.
        $siswa = $db->table('siswa')
            ->where('nis', '909')->where('id_tahun_ajaran', $taId)
            ->get()->getRowArray();
        if (!$siswa) {
            echo "✗ Siswa NIS 909 di TA aktif tidak ditemukan.\n";
            return;
        }
        $idSiswa = (int) $siswa['id_siswa'];

        // Pasca merge: tabel `nilai` (id_nilai). Ambil satu baris → set borderline 75.
        $na = $db->table('nilai')
            ->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $taId)
            ->where('nilai_akhir IS NOT NULL', null, false)
            ->orderBy('id_nilai', 'ASC')
            ->get()->getRowArray();

        if ($na) {
            $db->table('nilai')
                ->where('id_nilai', (int) $na['id_nilai'])
                ->update([
                    'nilai_akhir'        => 75,
                    'nilai_huruf'        => 'C',
                    'status_kelulusan'   => 'Tuntas',
                    'flag_borderline_75' => 1,
                    'catatan_remedial'   => 'Nilai 75 merupakan hasil remedial. Ananda perlu pendampingan tambahan pada materi terkait.',
                    'tindak_lanjut'      => null,
                    'status_remedial'    => null,
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);
        }

        // Pastikan rapor siswa difinalisasi (e-rapor & PDF dapat diakses).
        $rapor = $db->table('rapor')
            ->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $taId)
            ->get()->getRowArray();
        if ($rapor && (int) $rapor['is_finalized'] !== 1) {
            $db->table('rapor')
                ->where('id_rapor', (int) $rapor['id_rapor'])
                ->update([
                    'is_finalized' => 1,
                    'finalized_at' => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
        }

        echo "✓ (siswa 909: 1 nilai borderline-75, rapor final)\n";
    }
}
