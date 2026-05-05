<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: KKM per kelas x mapel x tahun_ajaran
 *
 * KKM default = 75 untuk semua mapel
 * Hanya dibuat untuk tahun ajaran aktif (2025/2026 Ganjil)
 *
 * Logika mapel per kelas (dari jadwal):
 *   Kelas 1-2: Tematik Terpadu, Agama Hindu, PJOK, Bhs.Bali, Seni Rupa, Bhs.Inggris, Pend.Pancasila
 *   Kelas 3:   Tematik Terpadu, Agama Hindu, PJOK, Bhs.Bali, Seni Rupa, Bhs.Inggris, Pend.Pancasila
 *   Kelas 4-5: Matematika, B.Indonesia, Pend.Pancasila, IPAS, PJOK, Agama Hindu, Bhs.Bali, Seni Suara, Bhs.Inggris
 *   Kelas 6:   Matematika, Tematik Terpadu, Agama Hindu, PJOK, Bhs.Bali, Bhs.Inggris
 */
class SD3_KkmSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [6/9] KKM ... ";

        // Ambil tahun ajaran aktif
        $ta = $this->db->table('tahun_ajaran')
            ->where('aktif', 'aktif')
            ->get()->getRow();

        if (!$ta) {
            echo "✗ Tahun ajaran aktif tidak ditemukan!\n";
            return;
        }

        $taId = $ta->id_tahun_ajaran;

        // Ambil semua mapel (keyed by nama)
        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();
        $mapelByNama = [];
        foreach ($mapelRows as $m) {
            $mapelByNama[$m['nama_mapel']] = (int) $m['id_mapel'];
        }

        // Definisi mapel per kelas (sesuai jadwal)
        $mapelPerKelas = [
            1 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
            2 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
            3 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
            4 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
            5 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
            6 => ['Matematika', 'Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Bahasa Inggris'],
        ];

        $inserted = 0;
        $updated  = 0;

        for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
            $kelas = $this->db->table('kelas')
                ->where('tingkat', $tingkat)
                ->get()->getRow();

            if (!$kelas) continue;

            $kelasId = $kelas->id_kelas;
            $mapelList = $mapelPerKelas[$tingkat] ?? [];

            foreach ($mapelList as $namaMapel) {
                $mapelId = $mapelByNama[$namaMapel] ?? null;
                if (!$mapelId) continue;

                $existing = $this->db->table('kkm')
                    ->where('id_mapel', $mapelId)
                    ->where('id_kelas', $kelasId)
                    ->where('id_tahun_ajaran', $taId)
                    ->get()->getRow();

                if (!$existing) {
                    $this->db->table('kkm')->insert([
                        'id_mapel'        => $mapelId,
                        'id_kelas'        => $kelasId,
                        'id_tahun_ajaran' => $taId,
                        'nilai_kkm'       => 75.00,
                        'created_at'      => date('Y-m-d H:i:s'),
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
                    $inserted++;
                } else {
                    $updated++;
                }
            }
        }

        echo "✓ ($inserted inserted, $updated already exists)\n";
    }
}
