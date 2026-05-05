<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: Mata Pelajaran (dari gambar jadwal mapel.png)
 *
 * Normalisasi nama dari jadwal:
 *   - Pend.Pancasila / Pend.Pancasila     => Pendidikan Pancasila         (Kelompok A)
 *   - Agama / Agama Budi Pk.              => Pendidikan Agama Hindu dan Budi Pekerti (Kelompok A)
 *   - Bhs.Indonesia / BHS.Indonesia       => Bahasa Indonesia              (Kelompok A)
 *   - Matematika                          => Matematika                    (Kelompok A)
 *   - IPAS                                => IPAS                          (Kelompok A)
 *   - Penjas Orkes / PJOK                 => PJOK                          (Kelompok A)
 *   - Tematik Ter / Tematik ter           => Tematik Terpadu               (Kelompok A) -- utk Kelas 1-3
 *   - Bhs.Inggris                         => Bahasa Inggris                (Kelompok B)
 *   - Bhs.Bali / Bhs.Daerah              => Bahasa Bali                   (Kelompok B)
 *   - Seni Rupa                           => Seni Rupa                     (Kelompok B)
 *   - Seni Suara                          => Seni Suara                    (Kelompok B)
 *
 *   Skip: Up. Bendera, Istirahat, slot kosong
 */
class SD3_MapelSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [4/9] Mata Pelajaran ... ";

        $mapelList = [
            // --- Kelompok A (Nasional) ---
            ['kode_mapel' => 'PKWU', 'nama_mapel' => 'Pendidikan Pancasila',                    'kelompok' => 'A'],
            ['kode_mapel' => 'AGAMA','nama_mapel' => 'Pendidikan Agama Hindu dan Budi Pekerti', 'kelompok' => 'A'],
            ['kode_mapel' => 'BIND', 'nama_mapel' => 'Bahasa Indonesia',                         'kelompok' => 'A'],
            ['kode_mapel' => 'MAT',  'nama_mapel' => 'Matematika',                               'kelompok' => 'A'],
            ['kode_mapel' => 'IPAS', 'nama_mapel' => 'IPAS',                                     'kelompok' => 'A'],
            ['kode_mapel' => 'PJOK', 'nama_mapel' => 'PJOK',                                     'kelompok' => 'A'],
            ['kode_mapel' => 'TEM',  'nama_mapel' => 'Tematik Terpadu',                          'kelompok' => 'A'],
            // --- Kelompok B (Muatan Lokal) ---
            ['kode_mapel' => 'BING', 'nama_mapel' => 'Bahasa Inggris',                           'kelompok' => 'B'],
            ['kode_mapel' => 'BBALI','nama_mapel' => 'Bahasa Bali',                              'kelompok' => 'B'],
            ['kode_mapel' => 'SRUPA','nama_mapel' => 'Seni Rupa',                                'kelompok' => 'B'],
            ['kode_mapel' => 'SSUAR','nama_mapel' => 'Seni Suara',                               'kelompok' => 'B'],
        ];

        $inserted = 0;
        $updated  = 0;

        foreach ($mapelList as $m) {
            // Cek duplikat berdasarkan nama_mapel (bukan kode, untuk anti-duplikat singkatan)
            $existing = $this->db->table('mata_pelajaran')
                ->where('nama_mapel', $m['nama_mapel'])
                ->get()->getRow();

            if (!$existing) {
                // Cek kode tidak bentrok
                $existingKode = $this->db->table('mata_pelajaran')
                    ->where('kode_mapel', $m['kode_mapel'])
                    ->get()->getRow();

                if ($existingKode) {
                    // Tambah suffix agar unik
                    $m['kode_mapel'] = $m['kode_mapel'] . '2';
                }

                $this->db->table('mata_pelajaran')->insert([
                    'kode_mapel'  => $m['kode_mapel'],
                    'nama_mapel'  => $m['nama_mapel'],
                    'kelompok'    => $m['kelompok'],
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            } else {
                $this->db->table('mata_pelajaran')
                    ->where('nama_mapel', $m['nama_mapel'])
                    ->update([
                        'kode_mapel' => $m['kode_mapel'],
                        'kelompok'   => $m['kelompok'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                $updated++;
            }
        }

        echo "✓ ($inserted inserted, $updated updated)\n";
    }
}
