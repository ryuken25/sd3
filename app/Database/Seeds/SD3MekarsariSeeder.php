<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * SD3MekarsariSeeder - Master seeder idempotent
 * Menjalankan semua sub-seeder secara berurutan.
 * Aman dijalankan berulang kali tanpa membuat data ganda.
 *
 * Cara jalankan:
 *   php spark db:seed SD3MekarsariSeeder
 */
class SD3MekarsariSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n╔══════════════════════════════════════════════╗\n";
        echo "║  SD3 Mekarsari - Import Data Awal           ║\n";
        echo "╚══════════════════════════════════════════════╝\n\n";

        $this->call(SD3_TahunAjaranSeeder::class);
        $this->call(SD3_GuruSeeder::class);
        $this->call(SD3_KelasSeeder::class);
        $this->call(SD3_MapelSeeder::class);
        $this->call(SD3_MapelKelasGuruSeeder::class);
        $this->call(SD3_SiswaSeeder::class);
        $this->call(SD3_KkmSeeder::class);
        $this->call(SD3_NilaiSeeder::class);
        $this->call(SD3_RaporSeeder::class);

        // Megaprompt revisi rapor (Tier B/C masters)
        $this->call(SD3_TemplateCatatanSeeder::class);
        $this->call(SD3_EkstrakurikulerSeeder::class);
        $this->call(SD3_DimensiPancasilaSeeder::class);
        $this->call(SD3_KokurikulerTemaSeeder::class);
        $this->call(SD3_CapaianPembelajaranSeeder::class);
        $this->call(SD3_CapaianBandSeeder::class);

        // Isi rapor per-siswa (CP/ekskul/koko) + finalisasi kelas 3 & 6 TA aktif
        $this->call(SD3_RaporIsiSeeder::class);

        $this->call(SD3_CapturePrepSeeder::class);

        echo "\n✅  Semua data berhasil di-import!\n\n";
    }
}
