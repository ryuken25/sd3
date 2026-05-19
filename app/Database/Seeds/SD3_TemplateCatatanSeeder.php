<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: 8 template catatan wali kelas (Pek 4 - Megaprompt revisi).
 * Idempotent: upsert by nama_template.
 */
class SD3_TemplateCatatanSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ Template Catatan Wali Kelas ... ";

        $templates = [
            ['Pertahankan Prestasi', 'positif',
             'Pertahankan nilai yang sudah bagus ini bila perlu ditingkatkan lagi'],
            ['Giatkan Membaca/Menulis/Berhitung', 'perlu_perbaikan',
             'Belajar lebih giat lagi terutama membaca, menulis, dan berhitung!'],
            ['Potensi Besar - Hilangkan Distraksi', 'netral',
             '{nama_panggilan} memiliki kemampuan yang baik dan potensi besar dalam akademis. Kami melihat {nama_panggilan} dapat mencapai hasil terbaik jika lebih serius di kelas. Mari hilangkan distraksi, fokus pada pelajaran, dan tunjukkan komitmen penuhmu dalam setiap sesi belajar.'],
            ['Tingkatkan Semangat', 'netral',
             '{nama_panggilan} memiliki potensi besar untuk sukses dalam bidang akademik. Kami yakin {nama_panggilan} dapat mencapai hasil yang lebih optimal. Mari tingkatkan lagi semangat dan motivasi belajarmu agar kemampuan terbaikmu semakin terlihat nyata.'],
            ['Kerajinan & Kedisiplinan', 'netral',
             '{nama_panggilan} menunjukkan kualitas belajar yang baik. Kami menyarankan {nama_panggilan} untuk meningkatkan lagi kerajinan, serta menjaga kedisiplinan kehadiran di kelas agar proses belajar menjadi lebih optimal dan hasilnya lebih baik lagi.'],
            ['Intensitas Belajar di Rumah', 'netral',
             '{nama_panggilan} adalah siswa yang menunjukkan kemajuan yang baik dan selalu berusaha. Dengan hasil yang sudah positif ini, kami mendorong {nama_panggilan} untuk lebih meningkatkan intensitas dan waktu belajar di luar jam sekolah agar potensimu dapat terwujud sepenuhnya.'],
            ['Kerajinan Luar Biasa', 'positif',
             '{nama_panggilan} menunjukkan kerajinan belajar yang luar biasa dan konsisten. Capaian {nama_panggilan} saat ini sangat membanggakan. Teruslah tingkatkan rasa ingin tahu dan jangan cepat merasa puas, karena potensi belajarmu masih sangat luas.'],
            ['Rajin dan Tekun', 'positif',
             '{nama_panggilan} adalah siswa yang rajin dan tekun dalam mengikuti pelajaran. Dasar akademisnya sudah kuat. Dengan sedikit peningkatan intensitas belajar, kami percaya {nama_panggilan} mampu mencapai peringkat yang jauh lebih cemerlang lagi.'],
        ];

        $inserted = 0;
        $skipped  = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($templates as [$nama, $kategori, $isi]) {
            $exists = $this->db->table('master_template_catatan')
                ->where('nama_template', $nama)
                ->get()->getRow();

            if ($exists) {
                $skipped++;
                continue;
            }

            $this->db->table('master_template_catatan')->insert([
                'nama_template' => $nama,
                'isi_template'  => $isi,
                'kategori'      => $kategori,
                'aktif'         => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
            $inserted++;
        }

        echo "✓ ($inserted inserted, $skipped skipped)\n";
    }
}
