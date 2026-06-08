<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: master Capaian Pembelajaran (Pek 3).
 *
 * Data extract dari Rapor_Kelas3.pdf (Fase B, Semester Ganjil) — sumber
 * referensi single source of truth. Untuk Fase C dan semester Genap,
 * data ditambah secara minimal supaya struktur lengkap; admin/guru bisa
 * tambah custom via UI.
 *
 * Idempotent: skip jika kombinasi (id_mapel, fase, semester, deskripsi) sudah ada.
 */
class SD3_CapaianPembelajaranSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ Master Capaian Pembelajaran ... ";

        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();
        $byNama = [];
        foreach ($mapelRows as $m) {
            $byNama[$m['nama_mapel']] = (int) $m['id_mapel'];
        }

        // CP Fase B Semester Ganjil (sumber: Rapor_Kelas3.pdf)
        $cpFaseBGanjil = [
            'Pendidikan Agama Hindu dan Budi Pekerti' => [
                'Mengenal metologi Hindu dalam Purana dan Nilai-nilai dalam Ramayana',
                'Memahami Tri Parartha, Subha dan Subha Karma serta sifat wiweka',
                'Menunjukkan Kemahakuasaan Hyang Widhi sebagai pencipta alam semesta Aspek Trimurti dan Cadu Sakti',
            ],
            'Pendidikan Pancasila' => [
                'menghargai identitas diri, teman, dan keluarga sesuai budaya, suku bangsa, bahasa, agama, dan kepercayaan',
                'memahami hak serta kewajiban di rumah dan di sekolah dengan baik',
                'memahami aturan',
                'mengidentifikasi lingkungan tempat tinggal sebagai bagian terpisah dari wilayah NKRI',
            ],
            'Bahasa Indonesia' => [
                'menemukan dan mengelompokkan gambar benda',
                'memeriksa penggunaan huruf kapital, tanda baca, dan spasi',
                'menyimpulkan ide pokoknya dengan tepat',
                'menuliskan ide pokok, ide pendukung, dan kesimpulan bacaan',
                'memahami dan mengikuti instruksi guru dengan tepat',
                'berbicara dengan sopan saat meminta tolong kepada orang lain',
                'menulis cerita dengan struktur awal, tengah, akhir',
            ],
            'Matematika' => [
                'membaca dan menulis bilangan cacah sampai 1.000',
                'membandingkan dua bilangan dan mengurutkan beberapa bilangan cacah sampai 1000',
                'menjelaskan langkah-langkah penyelesaian untuk kalimat Matematika dengan pengurangan',
                'menentukan hasil penjumlahan, pengurangan, dan perkalian, pembagian dua bilangan cacah sampai 100',
                'mengukur panjang dan berat benda menggunakan satuan baku',
                'menentukan hubungan antarsatuan baku panjang dan berat',
            ],
            'IPAS' => [
                'mengenali dan menjelaskan ciri-ciri memahami keragaman hewan di sekitar kita',
                'mengidentifikasi bentuk dan sumber energi yang ada di sekitarnya',
                'melakukan penyelidikan mengenai cara menghemat salah satu energi',
                'menjelaskan tahap siklus yang dilalui oleh makhluk hidup',
                'menganalisis dan mengetahui komponen biotik dan abiotik dalam suatu ekosistem',
            ],
            'PJOK' => [
                'mempraktikkan variasi pola gerak dasar pada gerakan nonlokomotor',
                'mempraktikkan aktivitas pembelajaran pola gerak dominan pada senam',
                'mempraktikkan variasi pola gerak dasar pada gerakan manipulatif',
                'mempraktikkan variasi pola gerak dasar pada gerakan lokomotor',
            ],
            'Seni Budaya' => [
                'mengidentifikasi berbagai jenis garis, bentuk, dan warna dalam seni rupa',
                'memahami mengenai komposisi dalam perbuatan karya seni rupa',
                'mengenal gambar ilustrasi',
                'mengenal berbagai jenis tekstur benda',
            ],
            'Bahasa Bali' => [
                'Mengenal Nama, cara berkembangbiak, Makanan Hewan dalam Bahasa Bali dan Memahami Kruna Pengarep',
                'Memahami, Menulis, dan membaca Aksara Wreastra',
                'Mengenal Parinama Entik-entikan dalam Bahasa Bali',
                'Memahami Bentuk, Menulis dan Berhitung Angka Bali',
            ],
            'Bahasa Inggris' => [
                'Menjawab dan membuat pertanyaan menggunakan kata tanya (what, who, why, when, where, how)',
                'Mengidentifikasi dan mengucapkan kata kerja bentuk lampau (past activity)',
                'Berbicara dengan menggunakan kata "was/were" pada kalimat bentuk lampau',
                'Menceritakan pengalamannya dalam bentuk kalimat lampau (past tense)',
            ],
        ];

        $inserted = 0;
        $skipped  = 0;
        $now = date('Y-m-d H:i:s');

        // Tabel mapel mungkin pakai nama sedikit beda. Mapping nama fallback.
        $namaMap = [
            'IPAS'        => ['IPAS', 'Ilmu Pengetahuan Alam dan Sosial', 'Ilmu Pengetahuan Alam dan Sosial (IPAS)'],
            'PJOK'        => ['PJOK', 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
            'Seni Budaya' => ['Seni Budaya', 'Seni Rupa', 'Seni Suara'],
        ];

        $resolveMapelId = function (string $namaMapel) use ($byNama, $namaMap): ?int {
            if (isset($byNama[$namaMapel])) {
                return $byNama[$namaMapel];
            }
            foreach ($namaMap[$namaMapel] ?? [] as $alias) {
                if (isset($byNama[$alias])) {
                    return $byNama[$alias];
                }
            }
            return null;
        };

        // Seed Fase B Ganjil (sumber utama dari PDF)
        foreach ($cpFaseBGanjil as $namaMapel => $cpList) {
            $idMapel = $resolveMapelId($namaMapel);
            if (!$idMapel) {
                continue;
            }

            // Pasca konsolidasi Phase 2: CP di master_referensi jenis='cp'.
            foreach ($cpList as $deskripsi) {
                $exists = $this->db->table('master_referensi')
                    ->where('jenis', 'cp')
                    ->where('id_mapel', $idMapel)
                    ->where('fase', 'B')
                    ->where('semester', 'Ganjil')
                    ->where('deskripsi', $deskripsi)
                    ->get()->getRow();
                if ($exists) {
                    $skipped++;
                    continue;
                }
                $this->db->table('master_referensi')->insert([
                    'jenis'      => 'cp',
                    'id_mapel'   => $idMapel,
                    'fase'       => 'B',
                    'semester'   => 'Ganjil',
                    'deskripsi'  => $deskripsi,
                    'aktif'      => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted++;
            }
        }

        // Seed Fase C Ganjil — sama dengan Fase B PLUS Bahasa Inggris.
        foreach ($cpFaseBGanjil as $namaMapel => $cpList) {
            $idMapel = $resolveMapelId($namaMapel);
            if (!$idMapel) {
                continue;
            }

            foreach ($cpList as $deskripsi) {
                $exists = $this->db->table('master_referensi')
                    ->where('jenis', 'cp')
                    ->where('id_mapel', $idMapel)
                    ->where('fase', 'C')
                    ->where('semester', 'Ganjil')
                    ->where('deskripsi', $deskripsi)
                    ->get()->getRow();
                if ($exists) {
                    $skipped++;
                    continue;
                }
                $this->db->table('master_referensi')->insert([
                    'jenis'      => 'cp',
                    'id_mapel'   => $idMapel,
                    'fase'       => 'C',
                    'semester'   => 'Ganjil',
                    'deskripsi'  => $deskripsi,
                    'aktif'      => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted++;
            }
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
