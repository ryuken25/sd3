<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed ISI rapor per-siswa untuk Tahun Ajaran AKTIF — supaya e-rapor & PDF
 * tampil lengkap sesuai contoh (Rapor_Kelas3.pdf / Rapor_Kelas6.pdf):
 *
 *   - nilai_capaian_kompetensi  : CP per (nilai_akhir) — split tercapai/perlu
 *   - siswa_ekstrakurikuler     : 4 ekskul per siswa (keterangan default)
 *   - siswa_kokurikuler_dimensi : 7 dimensi P5 per siswa (subdimensi + level)
 *
 * Plus finalisasi rapor kelas 3 & 6 di TA aktif supaya e-rapor bisa dibuka
 * (gate e-rapor = rapor.is_finalized).
 *
 * Idempotent. Hanya TA aktif yang diisi (di situ master CP Ganjil + tema ada).
 */
class SD3_RaporIsiSeeder extends Seeder
{
    /** Subdimensi P5 default per dimensi (sumber: layout PDF rapor). */
    private array $subdimensiPerDimensi = [
        'keimanan dan ketakwaan terhadap Tuhan Yang Maha Esa' => 'hubungan dengan sesama manusia',
        'kewargaan'        => 'kewargaan nasional',
        'penalaran kritis' => 'pengambilan keputusan',
        'kreativitas'      => 'gagasan baru',
        'kemandirian'      => 'bertanggung jawab',
        'kolaborasi'       => 'peduli',
        'komunikasi'       => 'menyimak',
    ];

    public function run(): void
    {
        echo "▶ Isi Rapor (CP + Ekskul + Kokurikuler per siswa) ... \n";

        $db = $this->db;

        $taAktif = $db->table('tahun_ajaran')->where('aktif', 'aktif')->get()->getRowArray();
        if (!$taAktif) {
            echo "   ✗ Tidak ada TA aktif.\n";
            return;
        }
        $taId = (int) $taAktif['id_tahun_ajaran'];

        $cpCount    = $this->seedCapaian($taId);
        $ekskulCnt  = $this->seedEkstrakurikuler($taId);
        $kokoCount  = $this->seedKokurikuler($taId);
        $finalCount = $this->finalisasiKelas36($taId);

        echo "   ✓ nilai_capaian_kompetensi : {$cpCount} record\n";
        echo "   ✓ siswa_ekstrakurikuler    : {$ekskulCnt} record\n";
        echo "   ✓ siswa_kokurikuler_dimensi: {$kokoCount} record\n";
        echo "   ✓ rapor kelas 3 & 6 difinalisasi: {$finalCount}\n";
    }

    /**
     * CP per nilai_akhir. Untuk tiap mapel yang punya master CP (fase+Ganjil),
     * sebagian besar ditandai tercapai_sangat_baik, sisanya perlu_peningkatan.
     * Offset rotasi pakai id_siswa supaya antar siswa beda komposisi (mirip PDF).
     */
    private function seedCapaian(int $taId): int
    {
        $db = $this->db;

        // Master CP dikelompokkan per (id_mapel, fase) — semester Ganjil saja yang ter-seed.
        $masterRows = $db->table('master_capaian_pembelajaran')
            ->where('semester', 'Ganjil')->where('aktif', 1)
            ->orderBy('id_master_cp', 'ASC')
            ->get()->getResultArray();
        $masterByMapelFase = [];
        foreach ($masterRows as $m) {
            $masterByMapelFase[$m['id_mapel'] . '|' . $m['fase']][] = (int) $m['id_master_cp'];
        }

        // Semua nilai_akhir di TA aktif + tingkat kelas siswa (untuk tentukan fase).
        $rows = $db->table('nilai_akhir na')
            ->select('na.id_nilai_akhir, na.id_mapel, na.id_siswa, k.tingkat')
            ->join('siswa s', 's.id_siswa = na.id_siswa')
            ->join('kelas k', 'k.id_kelas = s.id_kelas')
            ->where('na.id_tahun_ajaran', $taId)
            ->get()->getResultArray();

        $inserted = 0;
        foreach ($rows as $r) {
            $tingkat = (int) $r['tingkat'];
            $fase = $tingkat <= 2 ? 'A' : ($tingkat <= 4 ? 'B' : 'C');
            $cpIds = $masterByMapelFase[$r['id_mapel'] . '|' . $fase] ?? [];
            if (empty($cpIds)) {
                continue; // mapel tanpa master CP (mis. Tematik, fase A) — lewati
            }

            // Sudah ada CP untuk nilai_akhir ini? skip (idempotent)
            $exists = $db->table('nilai_capaian_kompetensi')
                ->where('id_nilai_akhir', $r['id_nilai_akhir'])
                ->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $total  = count($cpIds);
            $offset = (int) $r['id_siswa'] % $total;
            // ~60% pertama (setelah rotasi) = tercapai, sisanya perlu peningkatan.
            $batasTercapai = (int) ceil($total * 0.6);

            foreach ($cpIds as $i => $masterCpId) {
                $pos    = ($i + $offset) % $total;
                $status = $pos < $batasTercapai ? 'tercapai_sangat_baik' : 'perlu_peningkatan';
                $db->table('nilai_capaian_kompetensi')->insert([
                    'id_nilai_akhir'   => $r['id_nilai_akhir'],
                    'master_cp_id'     => $masterCpId,
                    'deskripsi_custom' => null,
                    'status'           => $status,
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            }
        }
        return $inserted;
    }

    /** 4 ekskul per siswa di TA aktif, keterangan = deskripsi_default master. */
    private function seedEkstrakurikuler(int $taId): int
    {
        $db = $this->db;

        $ekskulMaster = $db->table('master_ekstrakurikuler')
            ->where('aktif', 1)->orderBy('id_ekskul', 'ASC')
            ->get()->getResultArray();
        if (empty($ekskulMaster)) {
            return 0;
        }

        $siswaList = $db->table('siswa')
            ->where('id_tahun_ajaran', $taId)->where('status', 'aktif')
            ->get()->getResultArray();

        $inserted = 0;
        foreach ($siswaList as $s) {
            foreach ($ekskulMaster as $e) {
                $exists = $db->table('siswa_ekstrakurikuler')
                    ->where('id_siswa', $s['id_siswa'])
                    ->where('id_ekskul', $e['id_ekskul'])
                    ->where('id_tahun_ajaran', $taId)
                    ->countAllResults();
                if ($exists > 0) {
                    continue;
                }
                $db->table('siswa_ekstrakurikuler')->insert([
                    'id_siswa'        => $s['id_siswa'],
                    'id_ekskul'       => $e['id_ekskul'],
                    'id_tahun_ajaran' => $taId,
                    'keterangan'      => $e['deskripsi_default'] ?: 'Mengikuti kegiatan dengan baik.',
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            }
        }
        return $inserted;
    }

    /** 7 dimensi P5 per siswa, terkait tema kokurikuler kelasnya di TA aktif. */
    private function seedKokurikuler(int $taId): int
    {
        $db = $this->db;

        $dimensiMaster = $db->table('master_dimensi_pancasila')
            ->orderBy('urutan', 'ASC')->get()->getResultArray();
        if (empty($dimensiMaster)) {
            return 0;
        }

        $levels = ['berkembang', 'cakap', 'mahir', 'sangat_mahir'];
        $inserted = 0;

        // Tema per kelas di TA aktif
        $temaRows = $db->table('kokurikuler_tema')
            ->where('id_tahun_ajaran', $taId)->get()->getResultArray();

        foreach ($temaRows as $tema) {
            $siswaList = $db->table('siswa')
                ->where('id_kelas', $tema['id_kelas'])
                ->where('id_tahun_ajaran', $taId)
                ->where('status', 'aktif')
                ->get()->getResultArray();

            foreach ($siswaList as $s) {
                foreach ($dimensiMaster as $idx => $dim) {
                    $exists = $db->table('siswa_kokurikuler_dimensi')
                        ->where('id_siswa', $s['id_siswa'])
                        ->where('id_tema', $tema['id_tema'])
                        ->where('id_dimensi', $dim['id_dimensi'])
                        ->countAllResults();
                    if ($exists > 0) {
                        continue;
                    }

                    $namaDimensi = strtolower(trim((string) $dim['nama_dimensi']));
                    $subdimensi  = $this->subdimensiPerDimensi[$namaDimensi] ?? 'capaian umum';
                    // Level deterministik: mayoritas berkembang, sedikit variasi per siswa.
                    $seed  = ((int) $s['id_siswa'] + $idx) % 10;
                    $level = $seed < 7 ? 'berkembang' : $levels[$seed % 4];

                    $db->table('siswa_kokurikuler_dimensi')->insert([
                        'id_siswa'   => $s['id_siswa'],
                        'id_tema'    => $tema['id_tema'],
                        'id_dimensi' => $dim['id_dimensi'],
                        'subdimensi' => $subdimensi,
                        'level'      => $level,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $inserted++;
                }
            }
        }
        return $inserted;
    }

    /**
     * Finalisasi rapor kelas 3 & 6 di TA aktif supaya e-rapor bisa dibuka
     * orang tua (gate viewRapor = is_finalized). Kelas lain dibiarkan belum
     * final untuk tetap mendemokan gating.
     */
    private function finalisasiKelas36(int $taId): int
    {
        $db = $this->db;

        $kelasIds = $db->table('kelas')
            ->whereIn('tingkat', [3, 6])
            ->get()->getResultArray();
        $kelasIds = array_column($kelasIds, 'id_kelas');
        if (empty($kelasIds)) {
            return 0;
        }

        $siswaIds = $db->table('siswa')
            ->whereIn('id_kelas', $kelasIds)
            ->where('id_tahun_ajaran', $taId)
            ->get()->getResultArray();
        $siswaIds = array_column($siswaIds, 'id_siswa');
        if (empty($siswaIds)) {
            return 0;
        }

        $admin = $db->table('users')->where('level', 'admin')
            ->orderBy('id_user', 'ASC')->get()->getRowArray();

        $db->table('rapor')
            ->whereIn('id_siswa', $siswaIds)
            ->where('id_tahun_ajaran', $taId)
            ->update([
                'is_finalized' => 1,
                'finalized_at' => date('Y-m-d H:i:s'),
                'finalized_by' => $admin ? (int) $admin['id_user'] : null,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        return $db->affectedRows();
    }
}
