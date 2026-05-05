<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: Tahun Ajaran
 * - Pertahankan 2023/2024 Ganjil (data lama, id=1)
 * - Tambah 2025/2026 Ganjil sebagai tahun ajaran AKTIF (sesuai file absensi)
 * - Untuk kebutuhan dokumentasi capture, status pengisian diset "Kunci"
 */
class SD3_TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [1/9] TahunAjaran ... ";

        // Pastikan 2023/2024 Ganjil tetap ada (bisa sudah ada dari DummySeeder)
        $existing2023 = $this->db->table('tahun_ajaran')
            ->where('tahun_ajaran', '2023/2024')
            ->where('semester', 'Ganjil')
            ->get()->getRow();

        if (!$existing2023) {
            $this->db->table('tahun_ajaran')->insert([
                'tahun_ajaran'     => '2023/2024',
                'semester'         => 'Ganjil',
                'aktif'            => 'nonaktif',
                'status_pengisian' => 'Kunci',
                'tanggal_mulai'    => '2023-07-17',
                'tanggal_selesai'  => '2023-12-22',
            ]);
        } else {
            // Set nonaktif karena 2025/2026 yang aktif
            $this->db->table('tahun_ajaran')
                ->where('tahun_ajaran', '2023/2024')
                ->where('semester', 'Ganjil')
                ->update(['aktif' => 'nonaktif', 'status_pengisian' => 'Kunci']);
        }

        // 2025/2026 Ganjil = Tahun ajaran aktif (sesuai file absensi "TAHUN PELAJARAN 2025/2026")
        $existing2025 = $this->db->table('tahun_ajaran')
            ->where('tahun_ajaran', '2025/2026')
            ->where('semester', 'Ganjil')
            ->get()->getRow();

        if (!$existing2025) {
            $this->db->table('tahun_ajaran')->insert([
                'tahun_ajaran'     => '2025/2026',
                'semester'         => 'Ganjil',
                'aktif'            => 'aktif',
                'status_pengisian' => 'Kunci',
                'tanggal_mulai'    => '2025-07-14',
                'tanggal_selesai'  => '2025-12-19',
            ]);
        } else {
            $this->db->table('tahun_ajaran')
                ->where('tahun_ajaran', '2025/2026')
                ->where('semester', 'Ganjil')
                ->update([
                    'aktif'            => 'aktif',
                    'status_pengisian' => 'Kunci',
                ]);
        }

        // Pastikan hanya 1 yang aktif
        $ta2025 = $this->db->table('tahun_ajaran')
            ->where('tahun_ajaran', '2025/2026')
            ->where('semester', 'Ganjil')
            ->get()->getRow();

        if ($ta2025) {
            $this->db->table('tahun_ajaran')
                ->where('id_tahun_ajaran !=', $ta2025->id_tahun_ajaran)
                ->update(['aktif' => 'nonaktif']);
        }

        echo "✓\n";
    }
}
