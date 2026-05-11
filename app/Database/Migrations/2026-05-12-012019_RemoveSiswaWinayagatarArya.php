<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSiswaWinayagatarArya extends Migration
{
    public function up(): void
    {
        $needle = '%winayagatar arya bhanu%';

        // --- 1. Hapus siswa bernama tersebut (kalau ada) + data turunan ---
        if ($this->db->tableExists('siswa')) {
            $siswaIds = array_map(
                static fn(array $row): int => (int) $row['id_siswa'],
                $this->db->table('siswa')
                    ->select('id_siswa')
                    ->where("LOWER(nama_siswa) LIKE", $needle)
                    ->get()->getResultArray()
            );

            foreach ($siswaIds as $idSiswa) {
                $this->deleteSiswaDerivatives($idSiswa);
                $this->db->table('siswa')->where('id_siswa', $idSiswa)->delete();
            }
        }

        // --- 2. Hapus user bernama tersebut (kalau ada) + bersihkan FK ---
        if ($this->db->tableExists('users')) {
            $userIds = array_map(
                static fn(array $row): int => (int) $row['id_user'],
                $this->db->table('users')
                    ->select('id_user')
                    ->where("LOWER(nama_lengkap) LIKE", $needle)
                    ->get()->getResultArray()
            );

            foreach ($userIds as $idUser) {
                if ($this->db->tableExists('mapel_kelas') && $this->db->fieldExists('id_guru', 'mapel_kelas')) {
                    $this->db->table('mapel_kelas')
                        ->where('id_guru', $idUser)
                        ->update(['id_guru' => null]);
                }

                if ($this->db->tableExists('siswa') && $this->db->fieldExists('id_user_ortu', 'siswa')) {
                    $this->db->table('siswa')
                        ->where('id_user_ortu', $idUser)
                        ->update(['id_user_ortu' => null]);
                }

                if ($this->db->tableExists('kelas') && $this->db->fieldExists('wali_kelas', 'kelas')) {
                    $this->db->table('kelas')
                        ->where('wali_kelas', $idUser)
                        ->update(['wali_kelas' => null]);
                }

                $this->db->table('users')->where('id_user', $idUser)->delete();
            }
        }
    }

    public function down(): void
    {
        // Penghapusan permanen, tidak ada rollback.
    }

    private function deleteSiswaDerivatives(int $idSiswa): void
    {
        $turunan = ['remedial', 'rapor', 'nilai_akhir', 'nilai_ujian', 'nilai_harian', 'nilai_siswa'];

        foreach ($turunan as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('id_siswa', $table)) {
                $this->db->table($table)->where('id_siswa', $idSiswa)->delete();
            }
        }
    }
}
