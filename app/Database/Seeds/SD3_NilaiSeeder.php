<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: nilai_siswa + nilai_akhir + remedial untuk SEMUA 5 TA.
 *
 * Tiap TA: ambil siswa via siswa.id_tahun_ajaran, lalu generate nilai
 * dengan seed deterministik yang menyertakan taId agar nilai beda antar TA.
 *
 * Distribusi kategori per kelas:
 *   20% sangat_baik (88-98), 50% baik (78-87), 20% cukup (72-77), 10% remedial (60-71)
 */
class SD3_NilaiSeeder extends Seeder
{
    private array $mapelPerKelas = [
        1 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        2 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        3 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        4 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        5 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        6 => ['Matematika', 'Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Bahasa Inggris'],
    ];

    private function getKategori(int $idx, int $total): string
    {
        $pct = $idx / max(1, $total);
        if ($pct < 0.20) return 'sangat_baik';
        if ($pct < 0.70) return 'baik';
        if ($pct < 0.90) return 'cukup';
        return 'remedial';
    }

    private function genNilai(string $kat, int $siswaId, int $mapelId, int $taId, string $type): float
    {
        $seed   = ($siswaId * 31 + $mapelId * 7 + $taId * 3 + crc32($type)) & 0x7FFFFFFF;
        $ranges = [
            'sangat_baik' => [88, 98],
            'baik'        => [78, 87],
            'cukup'       => [72, 77],
            'remedial'    => [60, 71],
        ];
        [$min, $max] = $ranges[$kat];
        $val    = $min + ($seed % ($max - $min + 1));
        $offset = (($siswaId + $mapelId + $taId + crc32($type)) % 7) - 3;
        return (float) max($min - 2, min($max + 2, $val + $offset));
    }

    private function nilaiHuruf(float $n): string
    {
        if ($n >= 90) return 'A';
        if ($n >= 80) return 'B';
        if ($n >= 70) return 'C';
        if ($n >= 60) return 'D';
        return 'E';
    }

    public function run(): void
    {
        echo "▶ [7/9] Nilai Siswa, Nilai Akhir, Remedial ... ";

        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();
        $mapelByNama = [];
        foreach ($mapelRows as $m) {
            $mapelByNama[$m['nama_mapel']] = (int) $m['id_mapel'];
        }

        $kelasRows = $this->db->table('kelas')->get()->getResultArray();
        $kelasByIdMap = [];
        foreach ($kelasRows as $k) {
            $kelasByIdMap[(int) $k['id_kelas']] = $k;
        }

        $allTa = $this->db->table('tahun_ajaran')
            ->orderBy('id_tahun_ajaran', 'ASC')
            ->get()->getResultArray();

        $totalNilai    = 0;
        $totalAkhir    = 0;
        $totalRemedial = 0;
        $db            = $this->db;

        foreach ($allTa as $ta) {
            $taId = (int) $ta['id_tahun_ajaran'];

            $allSiswa = $db->table('siswa')
                ->where('id_tahun_ajaran', $taId)
                ->where('status', 'aktif')
                ->get()->getResultArray();

            // Group by kelas untuk pengurutan kategori yang konsisten
            $byKelas = [];
            foreach ($allSiswa as $s) {
                $byKelas[(int) $s['id_kelas']][] = $s;
            }

            $db->transStart();

            foreach ($byKelas as $kelasId => $anggota) {
                $kelas = $kelasByIdMap[$kelasId] ?? null;
                if (!$kelas) {
                    continue;
                }

                $tingkat    = (int) $kelas['tingkat'];
                $mapelList  = $this->mapelPerKelas[$tingkat] ?? [];
                $totalSiswa = \count($anggota);

                foreach ($anggota as $idx => $siswa) {
                    $siswaId = (int) $siswa['id_siswa'];
                    $kat     = $this->getKategori($idx, $totalSiswa);

                    foreach ($mapelList as $namaMapel) {
                        $mapelId = $mapelByNama[$namaMapel] ?? null;
                        if (!$mapelId) {
                            continue;
                        }

                        // Pasca merge: komponen + nilai_akhir + remedial digabung jadi
                        // satu baris `nilai` per (siswa,mapel,TA). Idempotent: skip
                        // bila kombinasi sudah ada lengkap dengan nilai_akhir.
                        $existing = $db->table('nilai')
                            ->where('id_siswa', $siswaId)
                            ->where('id_mapel', $mapelId)
                            ->where('id_tahun_ajaran', $taId)
                            ->get()->getRow();

                        if ($existing && $existing->nilai_akhir !== null) {
                            continue;
                        }

                        if ($existing) {
                            $rataHari = (float) ($existing->rata_rata_harian ?? 0);
                            $uts      = (float) ($existing->nilai_uts ?? 0);
                            $uas      = (float) ($existing->nilai_uas ?? 0);
                            $tugas    = (float) ($existing->nilai_tugas ?? 0);
                            $ulangan  = (float) ($existing->nilai_ulangan ?? 0);
                        } else {
                            $tugas    = $this->genNilai($kat, $siswaId, $mapelId, $taId, 'tugas');
                            $ulangan  = $this->genNilai($kat, $siswaId, $mapelId, $taId, 'ulangan');
                            $rataHari = round(($tugas + $ulangan) / 2, 2);
                            $uts      = $this->genNilai($kat, $siswaId, $mapelId, $taId, 'uts');
                            $uas      = $this->genNilai($kat, $siswaId, $mapelId, $taId, 'uas');
                            $totalNilai++;
                        }

                        $kkmRow   = $db->table('kkm')
                            ->where('id_mapel', $mapelId)
                            ->where('id_kelas', $kelasId)
                            ->where('id_tahun_ajaran', $taId)
                            ->get()->getRow();
                        $nilaiKkm = $kkmRow ? (float) $kkmRow->nilai_kkm : 70.0;

                        $akhir    = round(($rataHari * 0.4) + ($uts * 0.3) + ($uas * 0.3), 2);
                        $huruf    = $this->nilaiHuruf($akhir);
                        $status   = ($akhir >= $nilaiKkm) ? 'Tuntas' : 'Remedial';
                        $isRemedial = $status === 'Remedial';

                        $payload = [
                            'id_siswa'         => $siswaId,
                            'id_mapel'         => $mapelId,
                            'id_tahun_ajaran'  => $taId,
                            'nilai_tugas'      => $tugas,
                            'nilai_ulangan'    => $ulangan,
                            'rata_rata_harian' => $rataHari,
                            'nilai_uts'        => $uts,
                            'nilai_uas'        => $uas,
                            'nilai_akhir'      => $akhir,
                            'nilai_huruf'      => $huruf,
                            'status_kelulusan' => $status,
                            'tindak_lanjut'    => $isRemedial ? 'Pengayaan dan latihan soal tambahan oleh guru kelas' : null,
                            'status_remedial'  => $isRemedial ? 'Belum' : null,
                            'updated_at'       => date('Y-m-d H:i:s'),
                        ];

                        if ($existing) {
                            $db->table('nilai')->where('id_nilai', (int) $existing->id_nilai)->update($payload);
                        } else {
                            $payload['created_at'] = $payload['updated_at'];
                            $db->table('nilai')->insert($payload);
                        }
                        $totalAkhir++;
                        if ($isRemedial) {
                            $totalRemedial++;
                        }
                    }
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                echo "✗ Transaksi gagal untuk TA ID $taId!\n";
                return;
            }
        }

        echo "✓\n";
        echo "   nilai_siswa : $totalNilai record\n";
        echo "   nilai_akhir : $totalAkhir record\n";
        echo "   remedial    : $totalRemedial record\n";
    }
}
