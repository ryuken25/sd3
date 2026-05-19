<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AttendanceWorkbookParser;
use App\Models\RaporModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\KelasModel;
use App\Services\AcademicPeriodService;

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
                ->where('siswa.status', 'aktif')
                ->where('siswa.id_tahun_ajaran', $filterTaId);

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

        $activePeriodId = null;
        try {
            $activePeriodId = (new AcademicPeriodService())->getActivePeriod()['id_tahun_ajaran'];
        } catch (\RuntimeException $e) {
            // tidak ada periode aktif — biarkan null
        }

        $data = [
            'title'                 => 'Manajemen Rapor',
            'rapor_data'            => $rapor_data,
            'siswa'                 => $siswaModel->where('status', 'aktif')->findAll(),
            'tahun_ajaran'          => $tahunAjaranModel->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll(),
            'kelas'                 => $kelasModel->findAll(),
            'filter_ta'             => $filter_ta,
            'filter_kelas'          => $filter_kelas,
            'selected_tahun_ajaran' => $selectedTahunAjaran,
            'summary'               => $summary,
            'active_period_id'      => $activePeriodId,
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
                ? 'Rapor berhasil diperbarui dan finalisasi dibatalkan. Silakan finalisasi ulang setelah data benar.'
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
            ? 'Rapor berhasil diperbarui dan finalisasi dibatalkan. Silakan finalisasi ulang setelah data benar.'
            : 'Rapor berhasil diperbarui.';

        return redirect()->back()->with('success', $message);
    }

    public function unfinalize($id_rapor)
    {
        $idRapor = (int) $id_rapor;
        if ($idRapor <= 0) {
            return redirect()->back()->with('error', 'ID rapor tidak valid.');
        }

        $raporModel = new RaporModel();
        $rapor = $raporModel->find($idRapor);

        if (!$rapor) {
            return redirect()->back()->with('error', 'Data rapor tidak ditemukan.');
        }

        if (empty($rapor['is_finalized'])) {
            return redirect()->back()->with('success', 'Rapor sudah berstatus Draft dan dapat diedit.');
        }

        $raporModel->update($idRapor, [
            'is_finalized' => 0,
            'finalized_at' => null,
            'finalized_by' => null,
        ]);

        return redirect()->back()->with('success', 'Finalisasi rapor berhasil dibatalkan. Rapor dapat diedit kembali.');
    }

    public function detail($id_siswa, $id_tahun_ajaran)
    {
        $idSiswa = (int) $id_siswa;
        $idTahunAjaran = (int) $id_tahun_ajaran;

        if ($idSiswa <= 0 || $idTahunAjaran <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Parameter siswa atau tahun ajaran tidak valid.',
            ]);
        }

        $siswaModel = new SiswaModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $raporModel = new RaporModel();
        $db = \Config\Database::connect();

        $siswa = $siswaModel->select('siswa.*, kelas.nama_kelas, kelas.tingkat')
            ->join('kelas', 'kelas.id_kelas = siswa.id_kelas', 'left')
            ->where('siswa.id_siswa', $idSiswa)
            ->first();

        if (!$siswa) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ]);
        }

        $tahunAjaran = $tahunAjaranModel->find($idTahunAjaran);
        if (!$tahunAjaran) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data tahun ajaran tidak ditemukan.',
            ]);
        }

        $rapor = $raporModel->where('id_siswa', $idSiswa)
            ->where('id_tahun_ajaran', $idTahunAjaran)
            ->first();

        $nilaiRows = $db->table('mata_pelajaran')
            ->select('mata_pelajaran.id_mapel, mata_pelajaran.nama_mapel, mata_pelajaran.kelompok, nilai_akhir.id_nilai_akhir, nilai_akhir.nilai_akhir, nilai_akhir.nilai_huruf, nilai_akhir.status_kelulusan, kkm.nilai_kkm, remedial.id_remedial, remedial.status_remedial, remedial.tindak_lanjut')
            ->join('mapel_kelas', 'mapel_kelas.id_mapel = mata_pelajaran.id_mapel AND mapel_kelas.id_kelas = ' . (int) $siswa['id_kelas'], 'inner')
            ->join('nilai_akhir', 'nilai_akhir.id_mapel = mata_pelajaran.id_mapel AND nilai_akhir.id_siswa = ' . $idSiswa . ' AND nilai_akhir.id_tahun_ajaran = ' . $idTahunAjaran, 'left')
            ->join('kkm', 'kkm.id_mapel = mata_pelajaran.id_mapel AND kkm.id_kelas = ' . (int) $siswa['id_kelas'] . ' AND kkm.id_tahun_ajaran = ' . $idTahunAjaran, 'left')
            ->join('remedial', 'remedial.id_nilai_akhir = nilai_akhir.id_nilai_akhir', 'left')
            ->orderBy('mata_pelajaran.kelompok', 'ASC')
            ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($nilaiRows)) {
            $nilaiRows = $db->table('mata_pelajaran')
                ->select('mata_pelajaran.id_mapel, mata_pelajaran.nama_mapel, mata_pelajaran.kelompok, nilai_akhir.id_nilai_akhir, nilai_akhir.nilai_akhir, nilai_akhir.nilai_huruf, nilai_akhir.status_kelulusan, kkm.nilai_kkm, remedial.id_remedial, remedial.status_remedial, remedial.tindak_lanjut')
                ->join('nilai_akhir', 'nilai_akhir.id_mapel = mata_pelajaran.id_mapel AND nilai_akhir.id_siswa = ' . $idSiswa . ' AND nilai_akhir.id_tahun_ajaran = ' . $idTahunAjaran, 'left')
                ->join('kkm', 'kkm.id_mapel = mata_pelajaran.id_mapel AND kkm.id_kelas = ' . (int) $siswa['id_kelas'] . ' AND kkm.id_tahun_ajaran = ' . $idTahunAjaran, 'left')
                ->join('remedial', 'remedial.id_nilai_akhir = nilai_akhir.id_nilai_akhir', 'left')
                ->where('nilai_akhir.id_siswa', $idSiswa)
                ->where('nilai_akhir.id_tahun_ajaran', $idTahunAjaran)
                ->orderBy('mata_pelajaran.kelompok', 'ASC')
                ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
                ->get()
                ->getResultArray();
        }

        $hasAnyNilai = false;
        $nilai = array_map(static function (array $row) use (&$hasAnyNilai): array {
            $statusKelulusan = trim((string) ($row['status_kelulusan'] ?? ''));
            $hasNilai = $row['nilai_akhir'] !== null && $row['nilai_akhir'] !== '';
            if ($hasNilai) {
                $hasAnyNilai = true;
            }

            return [
                'id_mapel' => (int) $row['id_mapel'],
                'nama_mapel' => (string) ($row['nama_mapel'] ?? '-'),
                'kelompok' => (string) ($row['kelompok'] ?? '-'),
                'kkm' => $row['nilai_kkm'] !== null ? (float) $row['nilai_kkm'] : null,
                'nilai_akhir' => $hasNilai ? (float) $row['nilai_akhir'] : null,
                'nilai_huruf' => $row['nilai_huruf'] !== null && $row['nilai_huruf'] !== '' ? (string) $row['nilai_huruf'] : null,
                'keterangan' => $statusKelulusan !== '' ? $statusKelulusan : 'Nilai belum tersedia',
                'status_remedial' => $row['status_remedial'] ?? null,
                'tindak_lanjut' => $row['tindak_lanjut'] ?? null,
            ];
        }, $nilaiRows);

        $status = $raporModel->getFinalizationStatusForStudent($idSiswa, (int) $siswa['id_kelas'], $idTahunAjaran, $rapor ?: null);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Detail rapor berhasil dimuat.',
            'data' => [
                'siswa' => [
                    'id_siswa' => $idSiswa,
                    'nama_siswa' => $siswa['nama_siswa'] ?? '-',
                    'nis' => $siswa['nis'] ?? '-',
                    'nisn' => $siswa['nisn'] ?? '-',
                    'id_kelas' => (int) ($siswa['id_kelas'] ?? 0),
                    'nama_kelas' => $siswa['nama_kelas'] ?? '-',
                ],
                'tahun_ajaran' => [
                    'id_tahun_ajaran' => $idTahunAjaran,
                    'tahun_ajaran' => $tahunAjaran['tahun_ajaran'] ?? '-',
                    'semester' => $tahunAjaran['semester'] ?? '-',
                    'status_pengisian' => $tahunAjaran['status_pengisian'] ?? '-',
                ],
                'rapor' => [
                    'id_rapor' => $rapor['id_rapor'] ?? null,
                    'sakit' => (int) ($rapor['sakit'] ?? 0),
                    'izin' => (int) ($rapor['izin'] ?? 0),
                    'alpa' => (int) ($rapor['alpa'] ?? 0),
                    'catatan_wali_kelas' => $rapor['catatan_wali_kelas'] ?? '',
                    'status_kenaikan' => $rapor['status_kenaikan'] ?? '',
                    'is_finalized' => !empty($rapor['is_finalized']),
                    'exists' => !empty($rapor),
                ],
                'nilai' => $nilai,
                'summary' => $status,
                'messages' => [
                    'rapor' => $rapor ? null : 'Draft rapor belum dibuat. Form edit akan membuat draft rapor saat disimpan.',
                    'nilai' => empty($nilai) || !$hasAnyNilai ? 'Nilai belum tersedia.' : null,
                ],
            ],
        ]);
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
        $overwriteFinalized = (bool) $this->request->getPost('overwrite_finalized');
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
        $overwrittenFinalized = [];

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
                    if (!empty($rapor['is_finalized']) && !$overwriteFinalized) {
                        $locked[] = $siswa['nama_siswa'];
                        continue;
                    }

                    if (!empty($rapor['is_finalized']) && $overwriteFinalized) {
                        $overwrittenFinalized[] = $siswa['nama_siswa'];
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
            if (!empty($overwrittenFinalized)) {
                $message .= ' Rapor final yang ditimpa dan dibatalkan finalisasinya: ' . implode(', ', array_slice($overwrittenFinalized, 0, 5));
                if (count($overwrittenFinalized) > 5) {
                    $message .= ' dan ' . (count($overwrittenFinalized) - 5) . ' lainnya';
                }
                $message .= '. Silakan finalisasi ulang setelah data benar.';
            }

            if (!empty($unmatched)) {
                $message .= ' Siswa tidak cocok: ' . implode(', ', array_slice($unmatched, 0, 5));
                if (count($unmatched) > 5) {
                    $message .= ' dan ' . (count($unmatched) - 5) . ' lainnya';
                }
                $message .= '.';
            }

            if (!empty($locked)) {
                $message .= ' Beberapa rapor tidak diubah karena sudah final. Batalkan finalisasi terlebih dahulu atau centang opsi overwrite saat import. Rapor final yang dilewati: ' . implode(', ', array_slice($locked, 0, 5));
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
