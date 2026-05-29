<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: 4 ekstrakurikuler (Pek 5.1).
 * Deskripsi default sesuai PDF rapor referensi.
 */
class SD3_EkstrakurikulerSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ Master Ekstrakurikuler ... ";

        // [nama, deskripsi_default, wajib]
        $items = [
            ['Pramuka', 'Mampu mempraktekkan gerak dasar pramuka, baris-berbaris dan bekerja sama dalam permainan berkelompok', 1],
            ['Majejahitan', 'Mampu mempraktekkan pembuatan sarana upakara dengan baik', 0],
            ['Yoga', 'Mampu mempraktekkan teknik dasar yoga', 0],
            ['Menari', 'Mampu mempraktekkan tarian dasar dengan baik', 0],
        ];

        $inserted = 0;
        $updated  = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($items as [$nama, $deskripsi, $wajib]) {
            $exists = $this->db->table('master_ekstrakurikuler')->where('nama', $nama)->get()->getRow();
            if ($exists) {
                // Idempotent: pastikan flag wajib tetap sesuai (mis. Pramuka=1).
                $this->db->table('master_ekstrakurikuler')
                    ->where('id_ekskul', $exists->id_ekskul)
                    ->update(['wajib' => $wajib, 'updated_at' => $now]);
                $updated++;
                continue;
            }
            $this->db->table('master_ekstrakurikuler')->insert([
                'nama'              => $nama,
                'deskripsi_default' => $deskripsi,
                'aktif'             => 1,
                'wajib'             => $wajib,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $inserted++;
        }

        echo "✓ ($inserted inserted, $updated updated)\n";
    }
}
