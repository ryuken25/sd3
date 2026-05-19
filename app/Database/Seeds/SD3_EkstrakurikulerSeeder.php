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

        $items = [
            ['Pramuka', 'Mampu mempraktekkan gerak dasar pramuka, baris-berbaris dan bekerja sama dalam permainan berkelompok'],
            ['Majejahitan', 'Mampu mempraktekkan pembuatan sarana upakara dengan baik'],
            ['Yoga', 'Mampu mempraktekkan teknik dasar yoga'],
            ['Menari', 'Mampu mempraktekkan tarian dasar dengan baik'],
        ];

        $inserted = 0;
        $skipped  = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($items as [$nama, $deskripsi]) {
            $exists = $this->db->table('master_ekstrakurikuler')->where('nama', $nama)->get()->getRow();
            if ($exists) {
                $skipped++;
                continue;
            }
            $this->db->table('master_ekstrakurikuler')->insert([
                'nama'              => $nama,
                'deskripsi_default' => $deskripsi,
                'aktif'             => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $inserted++;
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
