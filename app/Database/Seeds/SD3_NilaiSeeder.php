<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: Nilai Siswa, Nilai Akhir, Remedial
 *
 * Distribusi nilai contoh (realistis):
 *   - 20% siswa: sangat baik  => range 88-98
 *   - 50% siswa: baik         => range 78-87
 *   - 20% siswa: cukup        => range 72-77
 *   - 10% siswa: perlu remedi => range 60-71
 *
 * Rumus final aktif di PHP service:
 *   nilai_akhir = (rata_rata_harian * 0.4) + (nilai_uts * 0.3) + (nilai_uas * 0.3)
 *
 * Nilai huruf: A>=90, B>=80, C>=70, D>=60, E<60
 */
class SD3_NilaiSeeder extends Seeder
{
    private int $taId = 0;

    // Mapel per kelas
    private array $mapelPerKelas = [
        1 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        2 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        3 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        4 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        5 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        6 => ['Matematika', 'Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Bahasa Inggris'],
    ];

    private function getKategori(int $siswaIndex, int $totalSiswa): string
    {
        $pct = $siswaIndex / $totalSiswa;
        if ($pct < 0.20)
            return 'sangat_baik';
        if ($pct < 0.70)
            return 'baik';
        if ($pct < 0.90)
            return 'cukup';
        return 'remedial';
    }

    /**
     * Generate nilai dengan variasi per mapel.
     * seed deterministik agar idempotent pada run ulang.
     */
    private function genNilai(string $kategori, int $siswaId, int $mapelId, string $type): float
    {
        // Seed deterministik
        $seed = ($siswaId * 31 + $mapelId * 7 + crc32($type)) & 0x7FFFFFFF;

        $ranges = [
            'sangat_baik' => [88, 98],
            'baik' => [78, 87],
            'cukup' => [72, 77],
            'remedial' => [60, 71],
        ];

        [$min, $max] = $ranges[$kategori];
        // Pseudo-random deterministik
        $val = $min + ($seed % ($max - $min + 1));

        // Variasi minor per tipe (+/-3)
        $offset = (($siswaId + $mapelId + crc32($type)) % 7) - 3;
        $val = max($min - 2, min($max + 2, $val + $offset));

        return (float) $val;
    }

    private function hitungNilaiHuruf(float $nilai): string
    {
        if ($nilai >= 90)
            return 'A';
        if ($nilai >= 80)
            return 'B';
        if ($nilai >= 70)
            return 'C';
        if ($nilai >= 60)
            return 'D';
        return 'E';
    }

    public function run(): void
    {
        echo "▶ [7/8] Nilai Siswa, Nilai Akhir, Remedial ... ";

        // Tahun ajaran aktif
        $ta = $this->db->table('tahun_ajaran')
            ->where('aktif', 'aktif')
            ->get()->getRow();

        if (!$ta) {
            echo "✗ Tahun ajaran aktif tidak ditemukan!\n";
            return;
        }

        $this->taId = (int) $ta->id_tahun_ajaran;

        // Ambil mapel by nama
        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();
        $mapelByNama = [];
        foreach ($mapelRows as $m) {
            $mapelByNama[$m['nama_mapel']] = (int) $m['id_mapel'];
        }

        $totalNilaiSiswa = 0;
        $totalAkhir = 0;
        $totalRemedial = 0;

        $db = $this->db;
        $db->transStart();

        for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
            $kelas = $db->table('kelas')
                ->where('tingkat', $tingkat)
                ->get()->getRow();

            if (!$kelas)
                continue;

            $siswaList = $db->table('siswa')
                ->where('id_kelas', $kelas->id_kelas)
                ->where('status', 'aktif')
                ->get()->getResultArray();

            $totalSiswa = count($siswaList);
            $mapelList = $this->mapelPerKelas[$tingkat] ?? [];

            foreach ($siswaList as $idx => $siswa) {
                $siswaId = (int) $siswa['id_siswa'];
                $kategori = $this->getKategori($idx, $totalSiswa);

                foreach ($mapelList as $namaMapel) {
                    $mapelId = $mapelByNama[$namaMapel] ?? null;
                    if (!$mapelId)
                        continue;

                    $existingNilaiSiswa = $db->table('nilai_siswa')
                        ->where('id_siswa', $siswaId)
                        ->where('id_mapel', $mapelId)
                        ->where('id_tahun_ajaran', $this->taId)
                        ->get()->getRow();

                    if (!$existingNilaiSiswa) {
                        $nilaiTugas = $this->genNilai($kategori, $siswaId, $mapelId, 'tugas');
                        $nilaiUlangan = $this->genNilai($kategori, $siswaId, $mapelId, 'ulangan');
                        $rataHarianFinal = round(($nilaiTugas + $nilaiUlangan) / 2, 2);
                        $utsVal = $this->genNilai($kategori, $siswaId, $mapelId, 'uts');
                        $uasVal = $this->genNilai($kategori, $siswaId, $mapelId, 'uas');

                        $db->table('nilai_siswa')->insert([
                            'id_siswa' => $siswaId,
                            'id_mapel' => $mapelId,
                            'id_tahun_ajaran' => $this->taId,
                            'nilai_tugas' => $nilaiTugas,
                            'nilai_ulangan' => $nilaiUlangan,
                            'rata_rata_harian' => $rataHarianFinal,
                            'nilai_uts' => $utsVal,
                            'nilai_uas' => $uasVal,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $totalNilaiSiswa++;
                    } else {
                        $rataHarianFinal = (float) ($existingNilaiSiswa->rata_rata_harian ?? 0);
                        $utsVal = (float) ($existingNilaiSiswa->nilai_uts ?? 0);
                        $uasVal = (float) ($existingNilaiSiswa->nilai_uas ?? 0);
                    }

                    // ── 2. NILAI AKHIR ────────────────────────────────────────────
                    $existingAkhir = $db->table('nilai_akhir')
                        ->where('id_siswa', $siswaId)
                        ->where('id_mapel', $mapelId)
                        ->where('id_tahun_ajaran', $this->taId)
                        ->get()->getRow();

                    if (!$existingAkhir) {
                        $kkmRow = $db->table('kkm')
                            ->where('id_mapel', $mapelId)
                            ->where('id_kelas', (int) $kelas->id_kelas)
                            ->where('id_tahun_ajaran', $this->taId)
                            ->get()->getRow();
                        $nilaiKkm = $kkmRow ? (float) $kkmRow->nilai_kkm : 70.0;

                        // Rumus: 40% harian + 30% uts + 30% uas
                        $nilaiAkhir = round(($rataHarianFinal * 0.4) + ($utsVal * 0.3) + ($uasVal * 0.3), 2);
                        $nilaiHuruf = $this->hitungNilaiHuruf($nilaiAkhir);
                        $statusKelulusan = ($nilaiAkhir >= $nilaiKkm) ? 'Tuntas' : 'Remedial';

                        $db->table('nilai_akhir')->insert([
                            'id_siswa' => $siswaId,
                            'id_mapel' => $mapelId,
                            'id_tahun_ajaran' => $this->taId,
                            'nilai_akhir' => $nilaiAkhir,
                            'nilai_huruf' => $nilaiHuruf,
                            'status_kelulusan' => $statusKelulusan,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $totalAkhir++;

                        // ── 3. REMEDIAL (jika tidak tuntas) ──────────────────────
                        if ($statusKelulusan === 'Remedial') {
                            $nilaiAkhirId = $db->insertID();

                            $db->table('remedial')->insert([
                                'id_nilai_akhir' => $nilaiAkhirId,
                                'tindak_lanjut' => 'Pengayaan dan latihan soal tambahan oleh guru kelas',
                                'status_remedial' => 'Belum',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                            $totalRemedial++;
                        }
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            echo "✗ Transaksi gagal!\n";
        } else {
            echo "✓\n";
            echo "   nilai_siswa  : $totalNilaiSiswa record\n";
            echo "   nilai_akhir  : $totalAkhir record\n";
            echo "   remedial     : $totalRemedial record\n";
        }
    }
}
