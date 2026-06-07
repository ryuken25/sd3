<?php

namespace App\Models;

use CodeIgniter\Model;

class RaporModel extends Model
{
    protected $table = 'rapor';
    protected $primaryKey = 'id_rapor';
    protected $allowedFields = [
        'id_siswa',
        'id_tahun_ajaran',
        'sakit',
        'izin',
        'alpa',
        'catatan_wali_kelas',
        'narasi_koko',
        'status_kenaikan',
        'is_finalized',
        'finalized_at',
        'finalized_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getFinalizedReport(int $idSiswa, int $idTahunAjaran): ?array
    {
        return $this->where([
            'id_siswa' => $idSiswa,
            'id_tahun_ajaran' => $idTahunAjaran,
            'is_finalized' => 1,
        ])->first();
    }

    public function hasFinalGrades(int $idSiswa, int $idTahunAjaran): bool
    {
        return $this->db->table('nilai')
            ->where('id_siswa', $idSiswa)
            ->where('id_tahun_ajaran', $idTahunAjaran)
            ->where('nilai_akhir IS NOT NULL', null, false)
            ->countAllResults() > 0;
    }

    public function hasIncompleteRemedial(int $idSiswa, int $idTahunAjaran): bool
    {
        return $this->db->table('nilai')
            ->where('id_siswa', $idSiswa)
            ->where('id_tahun_ajaran', $idTahunAjaran)
            ->where('status_kelulusan', 'Remedial')
            ->where("(tindak_lanjut IS NULL OR TRIM(tindak_lanjut) = '')", null, false)
            ->countAllResults() > 0;
    }

    public function getRequiredMapelsForClass(int $idKelas, int $idTahunAjaran): array
    {
        $mapels = $this->db->table('mata_pelajaran')
            ->select('mata_pelajaran.id_mapel, mata_pelajaran.nama_mapel, mata_pelajaran.kelompok')
            ->join('mapel_kelas', 'mapel_kelas.id_mapel = mata_pelajaran.id_mapel')
            ->where('mapel_kelas.id_kelas', $idKelas)
            ->orderBy('mata_pelajaran.kelompok', 'ASC')
            ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
            ->get()
            ->getResultArray();

        if (!empty($mapels)) {
            return $mapels;
        }

        return $this->db->table('mata_pelajaran')
            ->select('mata_pelajaran.id_mapel, mata_pelajaran.nama_mapel, mata_pelajaran.kelompok')
            ->join('kkm', 'kkm.id_mapel = mata_pelajaran.id_mapel')
            ->where('kkm.id_kelas', $idKelas)
            ->where('kkm.id_tahun_ajaran', $idTahunAjaran)
            ->groupBy('mata_pelajaran.id_mapel, mata_pelajaran.nama_mapel, mata_pelajaran.kelompok')
            ->orderBy('mata_pelajaran.kelompok', 'ASC')
            ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getFinalizationIssuesForClass(int $idKelas, int $idTahunAjaran): array
    {
        $issues = [];
        $students = $this->db->table('siswa')
            ->select('id_siswa, nama_siswa')
            ->where('id_kelas', $idKelas)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($students)) {
            return ['Belum ada siswa aktif pada kelas ini.'];
        }

        $mapels = $this->getRequiredMapelsForClass($idKelas, $idTahunAjaran);
        if (empty($mapels)) {
            $issues[] = 'Belum ada mata pelajaran yang dihubungkan dengan kelas ini.';
        }

        foreach ($students as $student) {
            $idSiswa = (int) $student['id_siswa'];
            $namaSiswa = (string) $student['nama_siswa'];
            $rapor = $this->where('id_siswa', $idSiswa)
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->first();

            if (!$rapor) {
                $issues[] = "{$namaSiswa} belum memiliki draft rapor.";
            } else {
                if ($rapor['sakit'] === null || $rapor['izin'] === null || $rapor['alpa'] === null) {
                    $issues[] = "{$namaSiswa} belum memiliki data absensi lengkap.";
                }

                if (trim((string) ($rapor['catatan_wali_kelas'] ?? '')) === '') {
                    $issues[] = "{$namaSiswa} belum memiliki catatan wali kelas.";
                }

                if (trim((string) ($rapor['status_kenaikan'] ?? '')) === '') {
                    $issues[] = "{$namaSiswa} belum memiliki status kenaikan/kelulusan.";
                }
            }

            foreach ($mapels as $mapel) {
                $idMapel = (int) $mapel['id_mapel'];
                $namaMapel = (string) $mapel['nama_mapel'];

                // Satu query: ambil baris nilai gabungan (komponen + nilai_akhir + remedial)
                $nilaiRow = $this->db->table('nilai')
                    ->where('id_siswa', $idSiswa)
                    ->where('id_mapel', $idMapel)
                    ->where('id_tahun_ajaran', $idTahunAjaran)
                    ->get()
                    ->getRowArray();

                if (!$nilaiRow) {
                    $issues[] = "{$namaSiswa} belum memiliki nilai {$namaMapel}.";
                    continue;
                }

                $missingComponents = [];
                foreach (['nilai_tugas' => 'tugas', 'nilai_ulangan' => 'ulangan', 'nilai_uts' => 'UTS', 'nilai_uas' => 'UAS'] as $field => $label) {
                    if ($nilaiRow[$field] === null || $nilaiRow[$field] === '') {
                        $missingComponents[] = $label;
                    }
                }
                if (!empty($missingComponents)) {
                    $issues[] = "{$namaSiswa} belum lengkap nilai {$namaMapel} (" . implode(', ', $missingComponents) . ").";
                }

                if ($nilaiRow['nilai_akhir'] === null || trim((string) ($nilaiRow['status_kelulusan'] ?? '')) === '') {
                    $issues[] = "{$namaSiswa} belum memiliki nilai akhir {$namaMapel}.";
                    continue;
                }

                if (($nilaiRow['status_kelulusan'] ?? null) === 'Remedial'
                    && trim((string) ($nilaiRow['tindak_lanjut'] ?? '')) === '') {
                    $issues[] = "{$namaSiswa} belum memiliki tindak lanjut remedial untuk {$namaMapel}.";
                }
            }
        }

        return array_values(array_unique($issues));
    }

    public function getFinalizationStatusForStudent(int $idSiswa, int $idKelas, int $idTahunAjaran, ?array $rapor = null): array
    {
        $mapels = $this->getRequiredMapelsForClass($idKelas, $idTahunAjaran);
        $requiredMapelIds = array_map(static fn ($mapel) => (int) $mapel['id_mapel'], $mapels);
        $issues = [];

        if ($rapor === null) {
            $rapor = $this->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $idTahunAjaran)->first();
        }

        if (!$rapor) {
            $issues[] = 'Draft rapor belum dibuat.';
        } else {
            if ($rapor['sakit'] === null || $rapor['izin'] === null || $rapor['alpa'] === null) {
                $issues[] = 'Absensi belum lengkap.';
            }
            if (trim((string) ($rapor['catatan_wali_kelas'] ?? '')) === '') {
                $issues[] = 'Catatan wali kelas belum diisi.';
            }
            if (trim((string) ($rapor['status_kenaikan'] ?? '')) === '') {
                $issues[] = 'Status kenaikan/kelulusan belum diisi.';
            }
        }

        if (empty($requiredMapelIds)) {
            $issues[] = 'Belum ada mapel berjalan untuk kelas ini.';
        }

        $nilaiAkhirRows = empty($requiredMapelIds)
            ? []
            : $this->db->table('nilai')
                ->where('id_siswa', $idSiswa)
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->whereIn('id_mapel', $requiredMapelIds)
                ->where('nilai_akhir IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

        $nilaiByMapel = array_column($nilaiAkhirRows, null, 'id_mapel');
        foreach ($mapels as $mapel) {
            $idMapel = (int) $mapel['id_mapel'];
            $nilaiAkhir = $nilaiByMapel[$idMapel] ?? null;
            if (!$nilaiAkhir || $nilaiAkhir['nilai_akhir'] === null || trim((string) ($nilaiAkhir['status_kelulusan'] ?? '')) === '') {
                $issues[] = 'Nilai akhir ' . $mapel['nama_mapel'] . ' belum lengkap.';
            }
        }

        $incompleteRemedial = empty($nilaiAkhirRows)
            ? 0
            : $this->db->table('nilai')
                ->where('id_siswa', $idSiswa)
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->whereIn('id_mapel', $requiredMapelIds)
                ->where('status_kelulusan', 'Remedial')
                ->where("(tindak_lanjut IS NULL OR TRIM(tindak_lanjut) = '')", null, false)
                ->countAllResults();

        if ($incompleteRemedial > 0) {
            $issues[] = $incompleteRemedial . ' tindak lanjut remedial belum lengkap.';
        }

        return [
            'jumlah_mapel_berjalan' => count($requiredMapelIds),
            'jumlah_nilai_akhir' => count($nilaiAkhirRows),
            'remedial_belum_lengkap' => $incompleteRemedial,
            'draft_ada' => !empty($rapor),
            'status_kenaikan_ada' => !empty($rapor) && trim((string) ($rapor['status_kenaikan'] ?? '')) !== '',
            'is_complete' => empty($issues),
            'issues' => array_values(array_unique($issues)),
        ];
    }
}
