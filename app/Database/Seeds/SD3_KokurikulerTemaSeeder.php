<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: tema kokurikuler P5 per (kelas, TA aktif) (Pek 5.2).
 * Tema default per fase sesuai megaprompt.
 */
class SD3_KokurikulerTemaSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ Kokurikuler Tema ... ";

        $taAktif = $this->db->table('tahun_ajaran')->where('aktif', 'aktif')->get()->getRow();
        if (!$taAktif) {
            echo "✗ Tidak ada TA aktif.\n";
            return;
        }
        $taId = (int) $taAktif->id_tahun_ajaran;

        // Tema default per tingkat (fase B = kelas 1-4, fase C = kelas 5-6)
        $temaPerTingkat = [
            1 => 'Aku Cinta Indonesia: Permainan Daerahku',
            2 => 'Aku Cinta Indonesia: Permainan Daerahku',
            3 => 'Kreasi dan Permainan Tradisional: Gelanggang Ceria Nusantara',
            4 => 'Kreasi dan Permainan Tradisional: Gelanggang Ceria Nusantara',
            5 => 'Bank Mini Kelas: Celengan Impian',
            6 => 'Bank Mini Kelas: Celengan Impian',
        ];

        $inserted = 0;
        $skipped  = 0;
        $now = date('Y-m-d H:i:s');

        $kelasRows = $this->db->table('kelas')->orderBy('tingkat', 'ASC')->get()->getResultArray();
        foreach ($kelasRows as $k) {
            $tingkat = (int) $k['tingkat'];
            $nama    = $temaPerTingkat[$tingkat] ?? 'Tema Profil Pelajar Pancasila';

            // Pasca konsolidasi Phase 2: tema P5 di master_referensi jenis='koko_tema'.
            $exists = $this->db->table('master_referensi')
                ->where('jenis', 'koko_tema')
                ->where('id_kelas', $k['id_kelas'])
                ->where('id_tahun_ajaran', $taId)
                ->get()->getRow();

            if ($exists) {
                $skipped++;
                continue;
            }

            $this->db->table('master_referensi')->insert([
                'jenis'           => 'koko_tema',
                'nama_tema'       => $nama,
                'id_kelas'        => $k['id_kelas'],
                'id_tahun_ajaran' => $taId,
                'narasi_pembuka'  => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            $inserted++;
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
