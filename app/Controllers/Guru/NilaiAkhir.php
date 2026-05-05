<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Libraries\AcademicScoreService;
use App\Models\NilaiAkhirModel;
use App\Models\NilaiSiswaModel;
use App\Models\RemedialModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\MataPelajaranModel;
use App\Models\KkmModel;
use App\Models\TahunAjaranModel;

class NilaiAkhir extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new \App\Models\TahunAjaranModel();

        $data = [
            'title' => 'Proses Nilai Akhir',
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel' => $mapelModel->getWithClasses(),
            'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->findAll()
        ];

        return view('guru/nilai_akhir/index', $data);
    }

    // Calculate final grades from nilai_siswa without depending on stored procedure
    public function calculate()
    {
        $id_kelas = $this->request->getPost('id_kelas');
        $id_mapel = $this->request->getPost('id_mapel');
        $id_tahun_ajaran = $this->request->getPost('id_tahun_ajaran');

        if (!$id_kelas || !$id_mapel || !$id_tahun_ajaran) {
            return redirect()->back()->with('error', 'Parameter tidak lengkap');
        }

        $siswaModel = new SiswaModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $nilaiSiswaModel = new NilaiSiswaModel();
        $nilaiAkhirModel = new NilaiAkhirModel();
        $remedialModel = new RemedialModel();
        $kkmModel = new KkmModel();
        $scoreService = new AcademicScoreService();
        $db = \Config\Database::connect();
        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        if ($response = $this->rejectIfMapelNotInClass((int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', (int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        // Get all students in the class
        $siswa = $siswaModel->where('id_kelas', $id_kelas)->where('status', 'aktif')->findAll();

        if (empty($siswa)) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas ini');
        }

        $successCount = 0;
        $failCount = 0;
        $errorMessages = [];

        $kkm = $kkmModel->where([
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran,
        ])->first();
        $nilaiKkm = isset($kkm['nilai_kkm']) ? (float) $kkm['nilai_kkm'] : 70.0;

        foreach ($siswa as $s) {
            try {
                $nilaiSiswa = $nilaiSiswaModel->where([
                    'id_siswa' => $s['id_siswa'],
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                ])->first();

                if (!$nilaiSiswa) {
                    $failCount++;
                    $errorMessages[] = "Siswa {$s['nama_siswa']}: data nilai_siswa belum tersedia";
                    continue;
                }

                $nilaiAkhir = $scoreService->calculateFinalScore(
                    $nilaiSiswa['nilai_tugas'] ?? null,
                    $nilaiSiswa['nilai_ulangan'] ?? null,
                    $nilaiSiswa['nilai_uts'] ?? null,
                    $nilaiSiswa['nilai_uas'] ?? null
                );
                $statusKelulusan = $scoreService->determineStatus($nilaiAkhir, $nilaiKkm);

                $payload = [
                    'id_siswa' => $s['id_siswa'],
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                    'nilai_akhir' => $nilaiAkhir,
                    'nilai_huruf' => $scoreService->determineLetter($nilaiAkhir),
                    'status_kelulusan' => $statusKelulusan,
                ];

                $existingNilaiAkhir = $nilaiAkhirModel->where([
                    'id_siswa' => $s['id_siswa'],
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                ])->first();

                if ($existingNilaiAkhir) {
                    $nilaiAkhirModel->update($existingNilaiAkhir['id_nilai_akhir'], $payload);
                    $nilaiAkhirId = (int) $existingNilaiAkhir['id_nilai_akhir'];
                } else {
                    $nilaiAkhirModel->insert($payload);
                    $nilaiAkhirId = (int) $nilaiAkhirModel->getInsertID();
                }

                $existingRemedial = $remedialModel->where('id_nilai_akhir', $nilaiAkhirId)->first();

                if ($statusKelulusan === 'Remedial') {
                    if ($existingRemedial) {
                        $remedialModel->update($existingRemedial['id_remedial'], [
                            'status_remedial' => $existingRemedial['status_remedial'] ?: 'Belum',
                        ]);
                    } else {
                        $remedialModel->insert([
                            'id_nilai_akhir' => $nilaiAkhirId,
                            'status_remedial' => 'Belum',
                            'tindak_lanjut' => null,
                        ]);
                    }
                } elseif ($existingRemedial) {
                    $remedialModel->delete($existingRemedial['id_remedial']);
                }

                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
                $errorMessages[] = "Siswa {$s['nama_siswa']}: " . $e->getMessage();
                log_message('error', "Failed to calculate nilai akhir for siswa {$s['id_siswa']}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $message = "Berhasil menghitung $successCount nilai akhir.";
            if ($failCount > 0) {
                $message .= " ($failCount gagal)";
            }
            $message .= " Silakan periksa dan lengkapi tindak lanjut remedial.";

            return redirect()->to(base_url('guru/nilai-akhir/review?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel . '&id_tahun_ajaran=' . $id_tahun_ajaran))
                ->with('success', $message);
        } else {
            $errorDetail = !empty($errorMessages) ? ' Error: ' . implode(', ', array_slice($errorMessages, 0, 3)) : '';
            return redirect()->back()->with('error', 'Gagal menghitung nilai akhir.' . $errorDetail);
        }
    }

    // Review final grades and remedial cases
    public function review()
    {
        $id_kelas = $this->request->getGet('id_kelas');
        $id_mapel = $this->request->getGet('id_mapel');
        $id_tahun_ajaran = $this->request->getGet('id_tahun_ajaran');

        if (!$id_kelas || !$id_mapel || !$id_tahun_ajaran) {
            return redirect()->back()->with('error', 'Parameter tidak lengkap');
        }

        $siswaModel = new SiswaModel();
        $nilaiAkhirModel = new NilaiAkhirModel();
        $remedialModel = new RemedialModel();
        $kkmModel = new KkmModel();
        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();

        // Get class, subject info
        $kelas = $kelasModel->find($id_kelas);
        $mapel = $mapelModel->find($id_mapel);

        // Get KKM
        $kkm = $kkmModel->where([
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran
        ])->first();

        // Get all students with their final grades
        // FIX BUG-02: Move id_mapel and id_tahun_ajaran to ON clause for proper LEFT JOIN
        $siswa = $siswaModel->select('siswa.*, nilai_akhir.*, remedial.*')
            ->join(
                'nilai_akhir',
                'nilai_akhir.id_siswa = siswa.id_siswa
                 AND nilai_akhir.id_mapel = ' . (int) $id_mapel . '
                 AND nilai_akhir.id_tahun_ajaran = ' . (int) $id_tahun_ajaran,
                'left'
            )
            ->join('remedial', 'remedial.id_nilai_akhir = nilai_akhir.id_nilai_akhir', 'left')
            ->where('siswa.id_kelas', $id_kelas)
            ->where('siswa.status', 'aktif')
            ->findAll();

        $data = [
            'title' => 'Review Nilai Akhir & Remedial',
            'kelas' => $kelas,
            'mapel' => $mapel,
            'kkm' => $kkm,
            'siswa' => $siswa,
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran
        ];

        return view('guru/nilai_akhir/review', $data);
    }

    /**
     * Rekap Remedial: Show all students below KKM with their remedial status
     */
    public function rekapRemedial()
    {
        $id_kelas = $this->request->getGet('id_kelas');
        $id_mapel = $this->request->getGet('id_mapel');
        $id_tahun_ajaran = $this->request->getGet('id_tahun_ajaran');

        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new \App\Models\TahunAjaranModel();
        $kkmModel = new KkmModel();

        // If no filter provided yet, show the filter form
        if (!$id_kelas || !$id_mapel || !$id_tahun_ajaran) {
            $data = [
                'title' => 'Rekap Remedial',
                'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
                'mapel' => $mapelModel->getWithClasses(),
                'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->findAll(),
                'siswa' => null,
                'kkm' => null,
                'selected_kelas' => null,
                'selected_mapel' => null,
            ];
            return view('guru/nilai_akhir/rekap_remedial', $data);
        }

        $kelas = $kelasModel->find($id_kelas);
        $mapel = $mapelModel->find($id_mapel);
        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        // Get KKM for this combination
        $kkm = $kkmModel->where([
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran
        ])->first();

        // Query students who are Remedial (below KKM)
        $siswaModel = new SiswaModel();
        $siswaRemedial = $siswaModel
            ->select('siswa.*, nilai_akhir.nilai_akhir, nilai_akhir.nilai_huruf, nilai_akhir.status_kelulusan, remedial.id_remedial, remedial.tindak_lanjut, remedial.status_remedial')
            ->join('nilai_akhir', 'nilai_akhir.id_siswa = siswa.id_siswa')
            ->join('remedial', 'remedial.id_nilai_akhir = nilai_akhir.id_nilai_akhir', 'left')
            ->where('siswa.id_kelas', $id_kelas)
            ->where('siswa.status', 'aktif')
            ->where('nilai_akhir.id_mapel', $id_mapel)
            ->where('nilai_akhir.id_tahun_ajaran', $id_tahun_ajaran)
            ->where('nilai_akhir.status_kelulusan', 'Remedial')
            ->findAll();

        $data = [
            'title' => 'Rekap Remedial',
            'kelas_list' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel_list' => $mapelModel->getWithClasses(),
            'tahun_ajaran_list' => $tahunAjaranModel->where('aktif', 'aktif')->findAll(),
            'kelas' => $kelas,
            'mapel' => $mapel,
            'tahun_ajaran' => $tahunAjaran,
            'kkm' => $kkm,
            'siswa' => $siswaRemedial,
            'selected_kelas' => $id_kelas,
            'selected_mapel' => $id_mapel,
            'selected_tahun' => $id_tahun_ajaran,
        ];

        return view('guru/nilai_akhir/rekap_remedial', $data);
    }

    // Save remedial actions (mandatory for students below KKM)
    public function saveRemedial()
    {
        $remedialModel = new RemedialModel();
        $data = $this->request->getPost('remedial'); // Array of remedial actions

        if (!$data || !is_array($data)) {
            return redirect()->back()->with('error', 'Data remedial tidak valid');
        }

        $idTahunAjaran = (int) $this->request->getPost('id_tahun_ajaran');
        $idKelas = (int) $this->request->getPost('id_kelas');
        $idMapel = (int) $this->request->getPost('id_mapel');
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAjaran = $idTahunAjaran ? $tahunAjaranModel->find($idTahunAjaran) : null;

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', $idKelas, $idMapel)) {
            return $response;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($data as $item) {
                $id_remedial = $item['id_remedial'];
                $tindak_lanjut = trim((string) ($item['tindak_lanjut'] ?? ''));

                // Validate: tindak_lanjut is mandatory
                if (empty($tindak_lanjut)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Tindak lanjut remedial wajib diisi untuk semua siswa yang remedial!');
                }

                $remedialModel->update($id_remedial, [
                    'tindak_lanjut' => $tindak_lanjut,
                    'status_remedial' => 'Sedang Proses'
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                log_message('error', 'Transaction failed in NilaiAkhir::saveRemedial()');
                return redirect()->back()->with('error', 'Gagal menyimpan tindak lanjut remedial. Silakan coba lagi.');
            }

            return redirect()->back()->with('success', 'Tindak lanjut remedial berhasil disimpan. Rapor dapat diproses.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception in NilaiAkhir::saveRemedial(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
