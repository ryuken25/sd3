<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder khusus persiapan dokumentasi/capture screenshot.
 *
 * Tujuan:
 * 1) Menjamin guru memiliki akses aktif saat semester terkunci
 *    (agar halaman input by-class/by-student tidak redirect).
 * 2) Menjamin minimal satu rapor finalized untuk akun orang tua contoh
 *    (agar halaman e-rapor bisa di-capture).
 */
class SD3_CapturePrepSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [9/9] Capture Prep ... ";

        $db = $this->db;
        $today = date('Y-m-d');
        $expire = date('Y-m-d', strtotime('+30 days'));
        $now = date('Y-m-d H:i:s');

        $taAktif = $db->table('tahun_ajaran')->where('aktif', 'aktif')->get()->getRowArray();
        if (!$taAktif) {
            echo "✗ Tahun ajaran aktif tidak ditemukan\n";
            return;
        }

        $idTahunAjaran = (int) $taAktif['id_tahun_ajaran'];

        // --- 1) Grant akses buka nilai aktif ke akun guru capture ---
        $guru = $db->table('users')->where('username', 'guru1')->where('level', 'guru')->get()->getRowArray();
        if (!$guru) {
            $guru = $db->table('users')->where('level', 'guru')->orderBy('id_user', 'ASC')->get()->getRowArray();
        }

        $admin = $db->table('users')->where('username', 'admin')->where('level', 'admin')->get()->getRowArray();
        if (!$admin) {
            $admin = $db->table('users')->where('level', 'admin')->orderBy('id_user', 'ASC')->get()->getRowArray();
        }

        if ($guru) {
            $idGuru = (int) $guru['id_user'];
            $idAdmin = $admin ? (int) $admin['id_user'] : null;

            $existingAccess = $db->table('request_buka_nilai')
                ->where('id_guru', $idGuru)
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->where('status', 'disetujui')
                ->where('tanggal_akses >=', $today)
                ->get()->getRowArray();

            if (!$existingAccess) {
                $db->table('request_buka_nilai')->insert([
                    'id_guru' => $idGuru,
                    'id_tahun_ajaran' => $idTahunAjaran,
                    'alasan' => 'Akses dibuka untuk dokumentasi implementasi dan validasi alur input nilai.',
                    'status' => 'disetujui',
                    'tanggal_akses' => $expire,
                    'approved_by' => $idAdmin,
                    'catatan_admin' => 'Auto-approve untuk kebutuhan capture dokumen.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // --- 2) Finalize minimal satu rapor untuk orang tua capture ---
        $ortu = $db->table('users')->where('username', 'ortu1')->where('level', 'orang_tua')->get()->getRowArray();

        $targetSiswa = null;
        if ($ortu) {
            $targetSiswa = $db->table('siswa')
                ->where('id_user_ortu', (int) $ortu['id_user'])
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->where('status', 'aktif')
                ->orderBy('id_siswa', 'ASC')
                ->get()->getRowArray();
        }

        if (!$targetSiswa) {
            $targetSiswa = $db->table('siswa')
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->where('status', 'aktif')
                ->orderBy('id_siswa', 'ASC')
                ->get()->getRowArray();
        }

        if ($targetSiswa) {
            $idSiswa = (int) $targetSiswa['id_siswa'];

            $rapor = $db->table('rapor')
                ->where('id_siswa', $idSiswa)
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->get()->getRowArray();

            $finalizedPayload = [
                'is_finalized' => 1,
                'finalized_at' => $now,
                'finalized_by' => $admin ? (int) $admin['id_user'] : null,
                'updated_at' => $now,
            ];

            if ($rapor) {
                $db->table('rapor')
                    ->where('id_rapor', (int) $rapor['id_rapor'])
                    ->update($finalizedPayload);
            } else {
                $db->table('rapor')->insert([
                    'id_siswa' => $idSiswa,
                    'id_tahun_ajaran' => $idTahunAjaran,
                    'sakit' => 0,
                    'izin' => 0,
                    'alpa' => 0,
                    'catatan_wali_kelas' => 'Finalisasi otomatis untuk kebutuhan dokumentasi implementasi.',
                    'status_kenaikan' => 'Naik',
                    'created_at' => $now,
                    ...$finalizedPayload,
                ]);
            }
        }

        echo "✓\n";
    }
}

