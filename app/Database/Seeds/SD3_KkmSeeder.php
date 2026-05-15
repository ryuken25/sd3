<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: KKM per (mapel, kelas, tahun_ajaran) — semua 5 TA.
 *
 * KKM default = 75. TA 2023/2024 diberi nilai 72 (threshold historis lebih rendah).
 * Idempotent: skip baris yang sudah ada.
 */
class SD3_KkmSeeder extends Seeder
{
    private array $mapelPerKelas = [
        1 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        2 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        3 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        4 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        5 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        6 => ['Matematika', 'Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Bahasa Inggris'],
    ];

    public function run(): void
    {
        echo "▶ [6/9] KKM ... ";

        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();
        $mapelByNama = [];
        foreach ($mapelRows as $m) {
            $mapelByNama[$m['nama_mapel']] = (int) $m['id_mapel'];
        }

        $kelasByTingkat = [];
        for ($t = 1; $t <= 6; $t++) {
            $k = $this->db->table('kelas')->where('tingkat', $t)->get()->getRow();
            if ($k) {
                $kelasByTingkat[$t] = $k;
            }
        }

        $allTa = $this->db->table('tahun_ajaran')
            ->orderBy('id_tahun_ajaran', 'ASC')
            ->get()->getResultArray();

        $inserted = 0;
        $skipped  = 0;

        foreach ($allTa as $ta) {
            $taId       = (int) $ta['id_tahun_ajaran'];
            $defaultKkm = (str_contains($ta['tahun_ajaran'], '2023/2024')) ? 72.0 : 75.0;

            for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
                $kelas = $kelasByTingkat[$tingkat] ?? null;
                if (!$kelas) {
                    continue;
                }

                foreach ($this->mapelPerKelas[$tingkat] ?? [] as $namaMapel) {
                    $mapelId = $mapelByNama[$namaMapel] ?? null;
                    if (!$mapelId) {
                        continue;
                    }

                    $existing = $this->db->table('kkm')
                        ->where('id_mapel', $mapelId)
                        ->where('id_kelas', $kelas->id_kelas)
                        ->where('id_tahun_ajaran', $taId)
                        ->get()->getRow();

                    if ($existing) {
                        $skipped++;
                        continue;
                    }

                    $this->db->table('kkm')->insert([
                        'id_mapel'        => $mapelId,
                        'id_kelas'        => $kelas->id_kelas,
                        'id_tahun_ajaran' => $taId,
                        'nilai_kkm'       => $defaultKkm,
                        'created_at'      => date('Y-m-d H:i:s'),
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
                    $inserted++;
                }
            }
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
