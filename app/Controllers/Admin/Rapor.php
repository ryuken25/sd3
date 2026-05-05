<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AttendanceWorkbookParser;
use App\Models\RaporModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\KelasModel;

class Rapor extends BaseController
{
    public function index()
    {
        $raporModel = new RaporModel();
        $siswaModel = new SiswaModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $kelasModel = new KelasModel();
        $db = \Config\Database::connect();

        $filter_ta = $this->request->getGet('id_tahun_ajaran');
        $filter_kelas = $this->request->getGet('id_kelas');
        $selectedTahunAjaran = $filter_ta ? $tahunAjaranModel->find($filter_ta) : null;

        // Build rapor query with filters and include students without rapor rows yet.
        $rapor_data = null;
        if ($filter_ta) {
            $filterTaId = (int) $filter_ta;
            $remedialSubQuery = "(
                SELECT COUNT(*)
                FROM nilai_akhir
                INNER JOIN remedial ON remedial.id_nilai_akhir = nilai_akhir.id_nilai_akhir
                WHERE nilai_akhir.id_siswa = siswa.id_siswa
                  AND nilai_akhir.id_tahun_ajaran = {$filterTaId}
                  AND nilai_akhir.status_kelulusan = 'Remedial'
                  AND (remedial.tindak_lanjut IS NULL OR TRIM(remedial.tindak_lanjut) = '')
            ) AS remedial_belum_lengkap";

            $builder = $siswaModel
                ->select("siswa.id_siswa, siswa.nama_siswa, siswa.nis, siswa.id_kelas, rapor.id_rapor, rapor.id_tahun_ajaran, rapor.sakit, rapor.izin, rapor.alpa, rapor.catatan_wali_kelas, rapor.status_kenaikan, rapor.is_finalized, rapor.finalized_at, {$remedialSubQuery}", false)
                ->join('rapor', 'rapor.id_siswa = siswa.id_siswa AND rapor.id_tahun_ajaran = ' . $filterTaId, 'left')
                ->where('siswa.status', 'aktif');

            if ($filter_kelas) {
                $builder->where('siswa.id_kelas', $filter_kelas);
            }

            $rapor_data = $builder->orderBy('siswa.nama_siswa', 'ASC')->findAll();

            foreach ($rapor_data as &$row) {
                $status = $raporModel->getFinalizationStatusForStudent((int) $row['id_siswa'], (int) $row['id_kelas'], $filterTaId, $row['id_rapor'] ? $row : null);
                $row = array_merge($row, $status);
            }
            unset($row);
        }

        $summary = [
            'total_siswa' => is_array($rapor_data) ? count($rapor_data) : 0,
            'siswa_lengkap' => 0,
            'siswa_belum_lengkap' => 0,
            'siswa_final' => 0,
        ];
        if (is_array($rapor_data)) {
            foreach ($rapor_data as $row) {
                if (!empty($row['is_complete'])) {
                    $summary['siswa_lengkap']++;
                } else {
                    $summary['siswa_belum_lengkap']++;
                }
                if (!empty($row['is_finalized'])) {
                    $summary['siswa_final']++;
                }
            }
        }

        $data = [
            'title' => 'Manajemen Rapor',
            'rapor_data' => $rapor_data,
            'siswa' => $siswaModel->where('status', 'aktif')->findAll(),
            'tahun_ajaran' => $tahunAjaranModel->findAll(),
            'kelas' => $kelasModel->findAll(),
            'filter_ta' => $filter_ta,
            'filter_kelas' => $filter_kelas,
            'selected_tahun_ajaran' => $selectedTahunAjaran,
            'summary' => $summary,
        ];

        return view('admin/rapor/index', $data);
    }

    public function store()
    {
        $raporModel = new RaporModel();

        if (
            !$this->validate([
                'id_siswa' => 'required|integer',
                'id_tahun_ajaran' => 'required|integer',
                'sakit' => 'permit_empty|integer|greater_than_equal_to[0]',
                'izin' => 'permit_empty|integer|greater_than_equal_to[0]',
                'alpa' => 'permit_empty|integer|greater_than_equal_to[0]',
                'status_kenaikan' => 'required|in_list[Naik,Tidak Naik,Lulus]'
            ])
        ) {
            return redirect()->back()->with('error', 'Data tidak lengkap atau tidak valid.');
        }

        // Check if rapor already exists
        $existing = $raporModel->where([
            'id_siswa' => $this->request->getPost('id_siswa'),
            'id_tahun_ajaran' => $this->request->getPost('id_tahun_ajaran')
        ])->first();

        $raporData = [
            'id_siswa' => $this->request->getPost('id_siswa'),
            'id_tahun_ajaran' => $this->request->getPost('id_tahun_ajaran'),
            'sakit' => $this->request->getPost('sakit') ?? 0,
            'izin' => $this->request->getPost('izin') ?? 0,
            'alpa' => $this->request->getPost('alpa') ?? 0,
            'catatan_wali_kelas' => trim((string) ($this->request->getPost('catatan_wali_kelas') ?? '')),
            'status_kenaikan' => $this->request->getPost('status_kenaikan'),
            'is_finalized' => 0,
            'finalized_at' => null,
            'finalized_by' => null,
        ];

        if ($existing) {
            $raporModel->update($existing['id_rapor'], $raporData);
            $msg = !empty($existing['is_finalized'])
                ? 'Rapor berhasil diperbarui dan status finalisasi direset.'
                : 'Rapor berhasil diperbarui.';
        } else {
            $raporModel->insert($raporData);
            $msg = 'Rapor berhasil disimpan.';
        }

        return redirect()->back()->with('success', $msg);
    }

    public function update($id)
    {
        $raporModel = new RaporModel();
        $rapor = $raporModel->find($id);

        if (!$rapor) {
            return redirect()->to(base_url('admin/rapor'))->with('error', 'Data rapor tidak ditemukan.');
        }

        if (!$this->validate([
            'sakit' => 'permit_empty|integer|greater_than_equal_to[0]',
            'izin' => 'permit_empty|integer|greater_than_equal_to[0]',
            'alpa' => 'permit_empty|integer|greater_than_equal_to[0]',
            'status_kenaikan' => 'required|in_list[Naik,Tidak Naik,Lulus]'
        ])) {
            return redirect()->back()->with('error', 'Data rapor tidak lengkap atau tidak valid.');
        }

        $raporData = [
            'sakit' => $this->request->getPost('sakit') ?? 0,
            'izin' => $this->request->getPost('izin') ?? 0,
            'alpa' => $this->request->getPost('alpa') ?? 0,
            'catatan_wali_kelas' => trim((string) ($this->request->getPost('catatan_wali_kelas') ?? '')),
            'status_kenaikan' => $this->request->getPost('status_kenaikan'),
            'is_finalized' => 0,
            'finalized_at' => null,
            'finalized_by' => null,
        ];

        $raporModel->update($id, $raporData);

        $message = !empty($rapor['is_finalized'])
            ? 'Rapor berhasil diperbarui dan status finalisasi direset.'
            : 'Rapor berhasil diperbarui.';

        return redirect()->back()->with('success', $message);
    }

    public function importAttendance()
    {
        $raporModel = new RaporModel();
        $siswaModel = new SiswaModel();
        $kelasModel = new KelasModel();
        $tahunAjaranModel = new TahunAjaranModel();

        if (
            !$this->validate([
                'id_kelas' => 'required|integer',
                'id_tahun_ajaran' => 'required|integer',
                'attendance_file' => 'uploaded[attendance_file]|ext_in[attendance_file,xls,xlsx]'
            ])
        ) {
            return redirect()->back()->with('error', 'File absensi, kelas, dan tahun ajaran wajib dipilih.');
        }

        $idKelas = (int) $this->request->getPost('id_kelas');
        $idTahunAjaran = (int) $this->request->getPost('id_tahun_ajaran');
        $kelas = $kelasModel->find($idKelas);
        $tahunAjaran = $tahunAjaranModel->find($idTahunAjaran);

        if (!$kelas || !$tahunAjaran) {
            return redirect()->back()->with('error', 'Kelas atau tahun ajaran tidak ditemukan.');
        }

        $file = $this->request->getFile('attendance_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File absensi tidak valid.');
        }

        $parser = new AttendanceWorkbookParser();

        try {
            $parsedWorkbook = $parser->parse($file->getTempName());
        } catch (\Throwable $e) {
            log_message('error', 'Attendance workbook parse failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membaca file absensi: ' . $e->getMessage());
        }

        $sheet = $this->resolveAttendanceSheet($parsedWorkbook, $kelas);
        if ($sheet === null) {
            return redirect()->back()->with('error', 'Sheet absensi yang sesuai dengan kelas terpilih tidak ditemukan di workbook.');
        }

        if (empty($sheet['students'])) {
            return redirect()->back()->with('error', 'Sheet absensi ditemukan, tetapi tidak ada data siswa yang dapat diproses.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $updated = 0;
        $created = 0;
        $unmatched = [];
        $locked = [];

        try {
            foreach ($sheet['students'] as $studentRow) {
                $siswa = $this->findStudentForAttendanceImport($studentRow, $idKelas, $siswaModel);

                if (!$siswa) {
                    $unmatched[] = $studentRow['nama_siswa'];
                    continue;
                }

                $rapor = $raporModel->where([
                    'id_siswa' => $siswa['id_siswa'],
                    'id_tahun_ajaran' => $idTahunAjaran,
                ])->first();

                $payload = [
                    'id_siswa' => (int) $siswa['id_siswa'],
                    'id_tahun_ajaran' => $idTahunAjaran,
                    'sakit' => (int) ($studentRow['sakit'] ?? 0),
                    'izin' => (int) ($studentRow['izin'] ?? 0),
                    'alpa' => (int) ($studentRow['alpa'] ?? 0),
                    'is_finalized' => 0,
                    'finalized_at' => null,
                    'finalized_by' => null,
                ];

                if ($rapor) {
                    if (!empty($rapor['is_finalized'])) {
                        $locked[] = $siswa['nama_siswa'];
                        continue;
                    }

                    $raporModel->update((int) $rapor['id_rapor'], [
                        'sakit' => $payload['sakit'],
                        'izin' => $payload['izin'],
                        'alpa' => $payload['alpa'],
                        'is_finalized' => 0,
                        'finalized_at' => null,
                        'finalized_by' => null,
                    ]);
                    $updated++;
                } else {
                    $raporModel->insert($payload);
                    $created++;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal mengimpor rekap absensi ke rapor.');
            }

            $message = "Import absensi selesai. Draft baru: {$created}, draft diperbarui: {$updated}.";
            if (!empty($unmatched)) {
                $message .= ' Siswa tidak cocok: ' . implode(', ', array_slice($unmatched, 0, 5));
                if (count($unmatched) > 5) {
                    $message .= ' dan ' . (count($unmatched) - 5) . ' lainnya';
                }
                $message .= '.';
            }

            if (!empty($locked)) {
                $message .= ' Rapor yang sudah final tidak diubah: ' . implode(', ', array_slice($locked, 0, 5));
                if (count($locked) > 5) {
                    $message .= ' dan ' . (count($locked) - 5) . ' lainnya';
                }
                $message .= '.';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Attendance import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import absensi gagal: ' . $e->getMessage());
        }
    }

    public function finalize($id_siswa, $id_tahun_ajaran)
    {
        $siswaModel = new SiswaModel();
        $raporModel = new RaporModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $siswa = $siswaModel->find($id_siswa);

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);
        if (!$tahunAjaran || ($tahunAjaran['status_pengisian'] ?? null) !== 'Kunci') {
            return redirect()->back()->with('error', 'Finalisasi rapor hanya dapat dilakukan saat semester sudah dikunci.');
        }

        $status = $raporModel->getFinalizationStatusForStudent((int) $id_siswa, (int) $siswa['id_kelas'], (int) $id_tahun_ajaran);
        if (!$status['is_complete']) {
            return redirect()->back()->with('error', 'Finalisasi siswa ditolak karena data belum lengkap.')->with('finalization_errors', $status['issues']);
        }

        $rapor = $raporModel->where('id_siswa', (int) $id_siswa)->where('id_tahun_ajaran', (int) $id_tahun_ajaran)->first();
        if ($rapor) {
            $raporModel->update((int) $rapor['id_rapor'], [
                'is_finalized' => 1,
                'finalized_at' => date('Y-m-d H:i:s'),
                'finalized_by' => session()->get('id_user'),
            ]);
        }

        return redirect()->back()->with('success', 'Rapor siswa berhasil difinalisasi. Orang tua sudah dapat melihat rapor final.');
    }

    public function finalizeClass($id_kelas, $id_tahun_ajaran)
    {
        return $this->finalizeClassRapor((int) $id_kelas, (int) $id_tahun_ajaran);
    }

    private function finalizeClassRapor(int $idKelas, int $idTahunAjaran)
    {
        $raporModel = new RaporModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $siswaModel = new SiswaModel();

        $tahunAjaran = $tahunAjaranModel->find($idTahunAjaran);
        if (!$tahunAjaran || ($tahunAjaran['status_pengisian'] ?? null) !== 'Kunci') {
            return redirect()->back()->with('error', 'Finalisasi rapor hanya dapat dilakukan saat semester sudah dikunci.');
        }

        $issues = $raporModel->getFinalizationIssuesForClass($idKelas, $idTahunAjaran);
        if (!empty($issues)) {
            return redirect()->back()
                ->with('error', 'Finalisasi rapor ditolak karena data rapor kelas belum lengkap.')
                ->with('finalization_errors', $issues);
        }

        $students = $siswaModel->where('id_kelas', $idKelas)
            ->where('status', 'aktif')
            ->findAll();

        foreach ($students as $student) {
            $rapor = $raporModel->where('id_siswa', (int) $student['id_siswa'])
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->first();

            if ($rapor) {
                $raporModel->update((int) $rapor['id_rapor'], [
                    'is_finalized' => 1,
                    'finalized_at' => date('Y-m-d H:i:s'),
                    'finalized_by' => session()->get('id_user'),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Rapor kelas berhasil difinalisasi. Orang tua sudah dapat melihat nilai dan rapor anaknya.');
    }

    private function resolveAttendanceSheet(array $parsedWorkbook, array $kelas): ?array
    {
        $targetTingkat = (int) ($kelas['tingkat'] ?? 0);
        $targetNamaKelas = $this->normalizeStudentName((string) ($kelas['nama_kelas'] ?? ''));

        foreach ($parsedWorkbook as $sheet) {
            if ((int) ($sheet['tingkat'] ?? 0) === $targetTingkat && $targetTingkat > 0) {
                return $sheet;
            }

            $sheetName = $this->normalizeStudentName((string) ($sheet['nama_rombel'] ?? ''));
            if ($targetNamaKelas !== '' && $sheetName !== '' && str_contains($sheetName, $targetNamaKelas)) {
                return $sheet;
            }
        }

        return null;
    }

    private function findStudentForAttendanceImport(array $studentRow, int $idKelas, SiswaModel $siswaModel): ?array
    {
        $nis = trim((string) ($studentRow['nis'] ?? ''));
        if ($nis !== '') {
            $found = $siswaModel->where('id_kelas', $idKelas)->where('nis', $nis)->first();
            if ($found) {
                return $found;
            }
        }

        $nisn = trim((string) ($studentRow['nisn'] ?? ''));
        if ($nisn !== '') {
            $found = $siswaModel->where('id_kelas', $idKelas)->where('nisn', $nisn)->first();
            if ($found) {
                return $found;
            }
        }

        $targetName = $this->normalizeStudentName((string) ($studentRow['nama_siswa'] ?? ''));
        if ($targetName === '') {
            return null;
        }

        $candidates = $siswaModel->where('id_kelas', $idKelas)->findAll();
        foreach ($candidates as $candidate) {
            if ($this->normalizeStudentName((string) ($candidate['nama_siswa'] ?? '')) === $targetName) {
                return $candidate;
            }
        }

        return null;
    }

    private function normalizeStudentName(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        return $value;
    }
}
