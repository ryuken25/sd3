<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed template Capaian berbasis BAND PREDIKAT (Megaprompt Revisi 2 / Part C6).
 *
 * Untuk SETIAP mapel × fase (A/B/C) × semester (Ganjil/Genap) × band (A/B/C/D),
 * isi master_capaian_pembelajaran (kolom predikat) dengan satu narasi default
 * yang RINGKAS. Narasi ini hanya saran awal — guru mengisi detailnya sendiri
 * lewat menu Capaian Kompetensi.
 *
 * Refreshable: bila baris band sudah ada, deskripsinya di-update ke teks default
 * terbaru. Jalankan ulang untuk meringkas template lama:
 *   php spark db:seed SD3_CapaianBandSeeder
 */
class SD3_CapaianBandSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ Template Capaian per Band (A/B/C/D) ... ";

        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();

        $inserted = 0;
        $updated  = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($mapelRows as $m) {
            $idMapel = (int) $m['id_mapel'];
            $nama    = (string) $m['nama_mapel'];

            foreach (['A', 'B', 'C'] as $fase) {
                foreach (['Ganjil', 'Genap'] as $semester) {
                    foreach (['A', 'B', 'C', 'D'] as $band) {
                        $deskripsi = $this->buildNarasi($band, $nama);

                        $exists = $this->db->table('master_capaian_pembelajaran')
                            ->where('id_mapel', $idMapel)
                            ->where('fase', $fase)
                            ->where('semester', $semester)
                            ->where('predikat', $band)
                            ->get()->getRow();

                        if ($exists) {
                            $this->db->table('master_capaian_pembelajaran')
                                ->where('id_master_cp', $exists->id_master_cp)
                                ->update(['deskripsi' => $deskripsi, 'updated_at' => $now]);
                            $updated++;
                            continue;
                        }

                        $this->db->table('master_capaian_pembelajaran')->insert([
                            'id_mapel'   => $idMapel,
                            'fase'       => $fase,
                            'semester'   => $semester,
                            'predikat'   => $band,
                            'deskripsi'  => $deskripsi,
                            'aktif'      => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $inserted++;
                    }
                }
            }
        }

        echo "✓ ($inserted inserted, $updated updated)\n";
    }

    /** Narasi default RINGKAS per band — guru melengkapi sendiri sesuai siswa. */
    private function buildNarasi(string $band, string $namaMapel): string
    {
        return match ($band) {
            'A'     => "Menunjukkan capaian sangat baik pada mata pelajaran {$namaMapel}.",
            'B'     => "Menunjukkan capaian baik pada mata pelajaran {$namaMapel}.",
            'C'     => "Menunjukkan capaian cukup pada mata pelajaran {$namaMapel}.",
            default => "Mulai berkembang pada mata pelajaran {$namaMapel}; perlu bimbingan lebih lanjut.",
        };
    }
}
