<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed template Capaian berbasis BAND PREDIKAT (Megaprompt Revisi 2 / Part C6).
 *
 * Untuk SETIAP mapel × fase (A/B/C) × semester (Ganjil/Genap) × band (A/B/C/D),
 * isi master_capaian_pembelajaran dengan satu narasi default (kolom predikat).
 * Narasi dirakit dari kalimat capaian per mapel dengan framing per band.
 *
 * Idempotent: skip bila (id_mapel, fase, semester, predikat) sudah ada.
 * Jalankan: php spark db:seed SD3_CapaianBandSeeder
 */
class SD3_CapaianBandSeeder extends Seeder
{
    /** Kalimat capaian sumber per mapel (dipakai merangkai narasi band). */
    private array $sumber = [
        'Pendidikan Agama Hindu dan Budi Pekerti' => [
            'mengenal metologi Hindu dalam Purana dan nilai-nilai dalam Ramayana',
            'memahami Tri Parartha, Subha dan Asubha Karma',
            'menunjukkan kemahakuasaan Hyang Widhi sebagai pencipta alam semesta',
        ],
        'Pendidikan Pancasila' => [
            'menghargai identitas diri, teman, dan keluarga',
            'memahami hak serta kewajiban di rumah dan di sekolah',
            'memahami aturan dan mengidentifikasi lingkungan tempat tinggal sebagai bagian wilayah NKRI',
        ],
        'Bahasa Indonesia' => [
            'menemukan dan mengelompokkan ide pokok bacaan',
            'memeriksa penggunaan huruf kapital, tanda baca, dan spasi',
            'menulis cerita dengan struktur awal, tengah, akhir',
            'berbicara dengan sopan dan mengikuti instruksi dengan tepat',
        ],
        'Matematika' => [
            'membaca, menulis, dan membandingkan bilangan cacah sampai 1.000',
            'menentukan hasil penjumlahan, pengurangan, perkalian, dan pembagian',
            'mengukur panjang dan berat benda menggunakan satuan baku',
        ],
        'IPAS' => [
            'mengenali ciri dan keragaman makhluk hidup di sekitar',
            'mengidentifikasi bentuk dan sumber energi serta cara menghematnya',
            'menganalisis komponen biotik dan abiotik dalam ekosistem',
        ],
        'PJOK' => [
            'mempraktikkan variasi pola gerak dasar lokomotor dan nonlokomotor',
            'mempraktikkan pola gerak dominan pada senam',
            'mempraktikkan pola gerak manipulatif dalam permainan',
        ],
        'Seni Budaya' => [
            'mengidentifikasi jenis garis, bentuk, dan warna dalam seni rupa',
            'memahami komposisi dalam pembuatan karya seni rupa',
            'mengenal gambar ilustrasi dan tekstur benda',
        ],
        'Bahasa Bali' => [
            'mengenal kruna dan parinama dalam Bahasa Bali',
            'memahami, menulis, dan membaca Aksara Wreastra',
            'memahami bentuk dan berhitung Angka Bali',
        ],
        'Bahasa Inggris' => [
            'menjawab dan membuat pertanyaan dengan kata tanya (what, who, when, where, how)',
            'mengidentifikasi dan mengucapkan kata kerja bentuk lampau',
            'menceritakan pengalaman dalam kalimat bentuk lampau (past tense)',
        ],
    ];

    /** Alias nama mapel di DB. */
    private array $alias = [
        'IPAS'        => ['IPAS', 'Ilmu Pengetahuan Alam dan Sosial', 'Ilmu Pengetahuan Alam dan Sosial (IPAS)'],
        'PJOK'        => ['PJOK', 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
        'Seni Budaya' => ['Seni Budaya', 'Seni Rupa', 'Seni Suara'],
    ];

    public function run(): void
    {
        echo "▶ Template Capaian per Band (A/B/C/D) ... ";

        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();

        // Cari kunci $sumber yang cocok untuk satu nama mapel DB.
        $cariSumber = function (string $namaMapel): ?array {
            if (isset($this->sumber[$namaMapel])) {
                return $this->sumber[$namaMapel];
            }
            foreach ($this->alias as $kunci => $aliasList) {
                if (in_array($namaMapel, $aliasList, true) && isset($this->sumber[$kunci])) {
                    return $this->sumber[$kunci];
                }
            }
            return null;
        };

        $inserted = 0;
        $skipped  = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($mapelRows as $m) {
            $idMapel = (int) $m['id_mapel'];
            $nama    = (string) $m['nama_mapel'];
            $kalimat = $cariSumber($nama);

            foreach (['A', 'B', 'C'] as $fase) {
                foreach (['Ganjil', 'Genap'] as $semester) {
                    foreach (['A', 'B', 'C', 'D'] as $band) {
                        $exists = $this->db->table('master_capaian_pembelajaran')
                            ->where('id_mapel', $idMapel)
                            ->where('fase', $fase)
                            ->where('semester', $semester)
                            ->where('predikat', $band)
                            ->get()->getRow();
                        if ($exists) {
                            $skipped++;
                            continue;
                        }

                        $this->db->table('master_capaian_pembelajaran')->insert([
                            'id_mapel'   => $idMapel,
                            'fase'       => $fase,
                            'semester'   => $semester,
                            'predikat'   => $band,
                            'deskripsi'  => $this->buildNarasi($band, $nama, $kalimat),
                            'aktif'      => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $inserted++;
                    }
                }
            }
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }

    /** Rakit narasi per band dari kalimat sumber; fallback generik bila kosong. */
    private function buildNarasi(string $band, string $namaMapel, ?array $kalimat): string
    {
        if (empty($kalimat)) {
            // Generik sopan per mapel.
            return match ($band) {
                'A' => "Mencapai kompetensi dengan sangat baik pada seluruh materi {$namaMapel}.",
                'B' => "Mencapai kompetensi dengan baik pada sebagian besar materi {$namaMapel}. Perlu peningkatan pada beberapa bagian.",
                'C' => "Mencapai kompetensi dengan cukup pada materi dasar {$namaMapel}. Perlu peningkatan pada beberapa bagian.",
                default => "Mulai berkembang pada materi dasar {$namaMapel}. Perlu bimbingan lebih lanjut.",
            };
        }

        $utama = array_slice($kalimat, 0, max(1, count($kalimat) - 1));
        $minor = array_slice($kalimat, max(1, count($kalimat) - 1));
        $utamaStr = implode(', ', $utama);
        $minorStr = implode(', ', $minor);

        return match ($band) {
            'A' => "Mencapai kompetensi dengan sangat baik dalam hal {$utamaStr}." . ($minorStr ? " Perlu peningkatan dalam hal {$minorStr}." : ''),
            'B' => "Mencapai kompetensi dengan baik dalam hal {$utamaStr}." . ($minorStr ? " Perlu peningkatan dalam hal {$minorStr}." : ''),
            'C' => "Mencapai kompetensi dengan cukup dalam hal {$utamaStr}." . ($minorStr ? " Perlu peningkatan dalam hal {$minorStr}." : ''),
            default => "Mulai berkembang dalam hal {$utamaStr}." . ($minorStr ? " Perlu bimbingan lebih dalam hal {$minorStr}." : ''),
        };
    }
}
