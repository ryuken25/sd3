<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: 5 Tahun Ajaran
 *
 *   2023/2024 Ganjil  → nonaktif, Kunci  (history)
 *   2023/2024 Genap   → nonaktif, Kunci  (history)
 *   2024/2025 Ganjil  → nonaktif, Kunci  (history)
 *   2024/2025 Genap   → nonaktif, Kunci  (history)
 *   2025/2026 Ganjil  → AKTIF,    Buka   (TA berjalan)
 *
 * Plus: hapus TA 2026/2027 dulu kalau ada.
 * Idempotent: aman dijalankan ulang.
 */
class SD3_TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [1/9] TahunAjaran ... ";

        // Hapus TA 2026/2027 kalau ada
        $this->db->table('tahun_ajaran')
            ->where('tahun_ajaran', '2026/2027')
            ->delete();

        $tas = [
            ['2023/2024', 'Ganjil', '2023-07-17', '2023-12-22', 'nonaktif', 'Kunci'],
            ['2023/2024', 'Genap',  '2024-01-08', '2024-06-21', 'nonaktif', 'Kunci'],
            ['2024/2025', 'Ganjil', '2024-07-15', '2024-12-20', 'nonaktif', 'Kunci'],
            ['2024/2025', 'Genap',  '2025-01-06', '2025-06-20', 'nonaktif', 'Kunci'],
            ['2025/2026', 'Ganjil', '2025-07-14', '2025-12-19', 'aktif',    'Buka'],
        ];

        $upserted = 0;

        foreach ($tas as [$tahun, $sem, $mulai, $selesai, $aktif, $status]) {
            $existing = $this->db->table('tahun_ajaran')
                ->where('tahun_ajaran', $tahun)
                ->where('semester', $sem)
                ->get()->getRow();

            $payload = [
                'tahun_ajaran'     => $tahun,
                'semester'         => $sem,
                'aktif'            => $aktif,
                'status_pengisian' => $status,
                'tanggal_mulai'    => $mulai,
                'tanggal_selesai'  => $selesai,
                'updated_at'       => date('Y-m-d H:i:s'),
            ];

            if (!$existing) {
                $payload['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('tahun_ajaran')->insert($payload);
            } else {
                $this->db->table('tahun_ajaran')
                    ->where('id_tahun_ajaran', $existing->id_tahun_ajaran)
                    ->update($payload);
            }
            $upserted++;
        }

        // Pastikan PERSIS satu TA aktif: 2025/2026 Ganjil
        $ta2025 = $this->db->table('tahun_ajaran')
            ->where('tahun_ajaran', '2025/2026')
            ->where('semester', 'Ganjil')
            ->get()->getRow();

        if ($ta2025) {
            $this->db->table('tahun_ajaran')
                ->where('id_tahun_ajaran !=', $ta2025->id_tahun_ajaran)
                ->update(['aktif' => 'nonaktif']);
        }

        echo "✓ ($upserted TA upserted)\n";
    }
}
