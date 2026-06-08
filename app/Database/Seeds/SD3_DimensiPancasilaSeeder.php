<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: 7 dimensi Profil Pelajar Pancasila (Pek 5.2).
 * Urutan sesuai PDF rapor referensi.
 */
class SD3_DimensiPancasilaSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ Master Dimensi Pancasila ... ";

        $items = [
            [1, 'keimanan dan ketakwaan terhadap Tuhan Yang Maha Esa'],
            [2, 'kewargaan'],
            [3, 'penalaran kritis'],
            [4, 'kreativitas'],
            [5, 'kemandirian'],
            [6, 'kolaborasi'],
            [7, 'komunikasi'],
        ];

        $inserted = 0;
        $skipped  = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($items as [$urutan, $nama]) {
            // Pasca konsolidasi: tabel master_referensi dengan jenis='dimensi'.
            $exists = $this->db->table('master_referensi')
                ->where('jenis', 'dimensi')->where('urutan', $urutan)->get()->getRow();
            if ($exists) {
                $skipped++;
                continue;
            }
            $this->db->table('master_referensi')->insert([
                'jenis'        => 'dimensi',
                'urutan'       => $urutan,
                'nama_dimensi' => $nama,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $inserted++;
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
