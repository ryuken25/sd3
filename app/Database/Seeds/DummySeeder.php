<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DummySeeder extends Seeder
{
    public function run()
    {
        // 1. Users
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $users = [
            [
                'username' => 'admin',
                'password' => $password,
                'nama_lengkap' => 'Administrator',
                'level' => 'admin',
                'status' => 'aktif'
            ],
            [
                'username' => 'guru1',
                'password' => $password,
                'nama_lengkap' => 'Budi Santoso, S.Pd',
                'level' => 'guru',
                'status' => 'aktif'
            ],
            [
                'username' => 'ortu1',
                'password' => $password,
                'nama_lengkap' => 'Bapak Wati',
                'level' => 'orang_tua',
                'status' => 'aktif'
            ]
        ];
        $this->db->table('users')->insertBatch($users);
        $adminId = 1;
        $guruId = 2;
        $ortuId = 3;

        // 2. Tahun Ajaran
        $this->db->table('tahun_ajaran')->insert([
            'tahun_ajaran' => '2023/2024',
            'semester' => 'Ganjil',
            'aktif' => 'aktif',
            'status_pengisian' => 'Buka',
            'tanggal_mulai' => '2023-07-01',
            'tanggal_selesai' => '2023-12-31'
        ]);
        $taId = 1;

        // 3. Kelas
        $this->db->table('kelas')->insert([
            'nama_kelas' => '1',
            'tingkat' => '1',
            'wali_kelas' => $guruId
        ]);
        $kelasId = 1;

        // 4. Mata Pelajaran
        $this->db->table('mata_pelajaran')->insert([
            'kode_mapel' => 'MAT',
            'nama_mapel' => 'Matematika',
            'kelompok' => 'A'
        ]);
        $mapelId = 1;

        // 5. Siswa
        $this->db->table('siswa')->insert([
            'nis' => '12345',
            'nisn' => '0012345678',
            'password' => password_hash('12345', PASSWORD_DEFAULT),
            'nama_siswa' => 'Wati Santoso',
            'jenis_kelamin' => 'P',
            'id_kelas' => $kelasId,
            'id_user_ortu' => $ortuId,
            'status' => 'aktif'
        ]);
        $siswaId = 1;

        // 6. KKM
        $this->db->table('kkm')->insert([
            'id_mapel' => $mapelId,
            'id_kelas' => $kelasId,
            'id_tahun_ajaran' => $taId,
            'nilai_kkm' => 75
        ]);

        echo "DummySeeder berhasil dijalankan!";
    }
}
