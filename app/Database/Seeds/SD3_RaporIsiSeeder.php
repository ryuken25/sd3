<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed ISI rapor per-siswa supaya e-rapor & PDF tampil lengkap sesuai contoh
 * (Rapor_Kelas3.pdf / Rapor_Kelas6.pdf):
 *
 *   - nilai_capaian_kompetensi  : CP per nilai_akhir — SEMUA TA
 *   - rapor.catatan_wali_kelas  : catatan dari 8 master template — SEMUA rapor
 *   - siswa_ekstrakurikuler     : 4 ekskul per siswa (TA aktif)
 *   - siswa_kokurikuler_dimensi : 7 dimensi P5 per siswa (TA aktif)
 *
 * Plus finalisasi rapor kelas 3 & 6 di TA aktif supaya e-rapor bisa dibuka
 * (gate e-rapor = rapor.is_finalized).
 *
 * CP: master CP cuma ter-seed untuk Fase B/C Ganjil. Untuk mapel/fase yang
 * tidak punya master CP dipakai fallback: master CP mapel sama dari fase lain,
 * lalu fallback terakhir ke deskripsi_custom generik. Hasilnya tiap nilai_akhir
 * PASTI punya CP — e-rapor tidak pernah "Belum ada capaian".
 *
 * Idempotent.
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

    /** CP generik untuk mapel tanpa master CP sama sekali (mis. Tematik Terpadu). */
    private array $cpCustomFallback = [
        'Memahami konsep dasar pada tema pembelajaran terpadu',
        'Mengerjakan tugas tematik dengan tertib dan tepat waktu',
        'Menunjukkan keaktifan dalam kegiatan pembelajaran tematik',
        'Mengaitkan materi antar muatan pelajaran dalam satu tema',
        'Menyampaikan hasil belajar tematik secara runtut',
    ];

    /** Penanda urutan/gelar pada nama Bali — dibuang saat ambil nama panggilan. */
    private array $namaMarker = [
        'i', 'ni', 'ida', 'dewa', 'gusti', 'gst', 'anak', 'agung',
        'wayan', 'made', 'nengah', 'ketut', 'komang', 'kadek', 'putu',
        'gede', 'luh', 'nyoman', 'ngurah', 'bagus', 'bgs', 'ayu', 'pt',
    ];

    public function run(): void
    {
        echo "▶ Isi Rapor (CP semua TA + Catatan + Ekskul + Kokurikuler) ... \n";

        $taAktif = $this->db->table('tahun_ajaran')->where('aktif', 'aktif')->get()->getRowArray();
        if (!$taAktif) {
            echo "   ✗ Tidak ada TA aktif.\n";
            return;
        }
        $taId = (int) $taAktif['id_tahun_ajaran'];

        $cpCount      = $this->seedCapaian();
        $catatanCount = $this->seedCatatan();
        $ekskulCnt    = $this->seedEkstrakurikuler($taId);
        $kokoCount    = $this->seedKokurikuler($taId);
        $finalCount   = $this->finalisasiKelas36($taId);

        echo "   ✓ nilai_capaian_kompetensi : {$cpCount} record (semua TA)\n";
        echo "   ✓ rapor.catatan_wali_kelas : {$catatanCount} rapor di-update (semua TA)\n";
        echo "   ✓ siswa_ekstrakurikuler    : {$ekskulCnt} record (TA aktif)\n";
        echo "   ✓ siswa_kokurikuler_dimensi: {$kokoCount} record (TA aktif)\n";
        echo "   ✓ rapor kelas 3 & 6 difinalisasi: {$finalCount}\n";
    }

    /**
     * CP per nilai_akhir untuk SEMUA TA. Mapel/fase tanpa master CP pakai
     * fallback: master CP mapel sama fase lain → lalu deskripsi_custom generik.
     */
    private function seedCapaian(): int
    {
        $db = $this->db;

        // Master CP: kelompokkan per (id_mapel|fase) dan per id_mapel (any fase).
        $masterRows = $db->table('master_capaian_pembelajaran')
            ->where('aktif', 1)
            ->orderBy('id_master_cp', 'ASC')
            ->get()->getResultArray();
        $byMapelFase = [];
        $byMapel     = [];
        foreach ($masterRows as $m) {
            $byMapelFase[$m['id_mapel'] . '|' . $m['fase']][] = (int) $m['id_master_cp'];
            $byMapel[(int) $m['id_mapel']][] = (int) $m['id_master_cp'];
        }

        // Semua nilai_akhir + tingkat kelas siswa (untuk tentukan fase).
        $rows = $db->table('nilai_akhir na')
            ->select('na.id_nilai_akhir, na.id_mapel, na.id_siswa, k.tingkat')
            ->join('siswa s', 's.id_siswa = na.id_siswa')
            ->join('kelas k', 'k.id_kelas = s.id_kelas')
            ->get()->getResultArray();

        $inserted = 0;
        foreach ($rows as $r) {
            // Idempotent: skip kalau nilai_akhir ini sudah punya CP.
            $exists = $db->table('nilai_capaian_kompetensi')
                ->where('id_nilai_akhir', $r['id_nilai_akhir'])
                ->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $tingkat = (int) $r['tingkat'];
            $fase    = $tingkat <= 2 ? 'A' : ($tingkat <= 4 ? 'B' : 'C');
            $mapelId = (int) $r['id_mapel'];

            // Prioritas: master CP fase tepat → master CP fase lain → custom.
            $cpIds  = $byMapelFase[$mapelId . '|' . $fase] ?? ($byMapel[$mapelId] ?? []);
            $useCustom = empty($cpIds);
            $items  = $useCustom ? $this->cpCustomFallback : $cpIds;

            $total  = count($items);
            $offset = (int) $r['id_siswa'] % $total;
            $batasTercapai = (int) ceil($total * 0.6); // ~60% tercapai, sisanya perlu peningkatan

            foreach ($items as $i => $item) {
                $pos    = ($i + $offset) % $total;
                $status = $pos < $batasTercapai ? 'tercapai_sangat_baik' : 'perlu_peningkatan';
                $db->table('nilai_capaian_kompetensi')->insert([
                    'id_nilai_akhir'   => $r['id_nilai_akhir'],
                    'master_cp_id'     => $useCustom ? null : $item,
                    'deskripsi_custom' => $useCustom ? $item : null,
                    'status'           => $status,
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            }
        }
        return $inserted;
    }

    /**
     * Isi rapor.catatan_wali_kelas untuk SEMUA rapor dari 8 master template.
     * Template dipilih deterministik per siswa; {nama_panggilan} di-replace.
     * Selalu di-set ulang (idempotent — hasil sama tiap run).
     */
    private function seedCatatan(): int
    {
        $db = $this->db;

        $templates = $db->table('master_template_catatan')
            ->where('aktif', 1)->orderBy('id_template', 'ASC')
            ->get()->getResultArray();
        if (empty($templates)) {
            return 0;
        }

        $raporRows = $db->table('rapor r')
            ->select('r.id_rapor, r.id_siswa, s.nama_siswa')
            ->join('siswa s', 's.id_siswa = r.id_siswa')
            ->get()->getResultArray();

        $updated = 0;
        foreach ($raporRows as $r) {
            $tpl      = $templates[(int) $r['id_siswa'] % count($templates)];
            $catatan  = str_replace(
                '{nama_panggilan}',
                $this->namaPanggilan((string) $r['nama_siswa']),
                (string) $tpl['isi_template']
            );

            $db->table('rapor')
                ->where('id_rapor', $r['id_rapor'])
                ->update([
                    'catatan_wali_kelas' => $catatan,
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);
            $updated++;
        }
        return $updated;
    }

    /** Ambil nama panggilan: kata distinctif pertama setelah membuang gelar/urutan Bali. */
    private function namaPanggilan(string $namaLengkap): string
    {
        $parts = preg_split('/\s+/', trim($namaLengkap)) ?: [];
        foreach ($parts as $p) {
            if (!in_array(strtolower($p), $this->namaMarker, true)) {
                return ucfirst(strtolower($p));
            }
        }
        return $parts[0] !== '' ? ucfirst(strtolower($parts[0])) : 'Ananda';
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
     * orang tua. Kelas lain dibiarkan belum final untuk tetap mendemokan gating.
     */
    private function finalisasiKelas36(int $taId): int
    {
        $db = $this->db;

        $kelasIds = $db->table('kelas')->whereIn('tingkat', [3, 6])->get()->getResultArray();
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
