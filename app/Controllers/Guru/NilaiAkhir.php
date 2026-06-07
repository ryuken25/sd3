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
            'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll()
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

        // Get all students in the class for the selected tahun ajaran.
        $siswa = $siswaModel->where('id_kelas', $id_kelas)
            ->where('id_tahun_ajaran', $id_tahun_ajaran)
            ->where('status', 'aktif')
            ->findAll();

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

                // Pek 6: tepat 75 = borderline (siswa di-katrol pasca remedial).
                $flagBorderline = (abs($nilaiAkhir - 75.0) < 0.01);

                // Pasca merge: nilai_akhir + remedial inline di baris `nilai`.
                // Naik di atas KKM → kolom remedial di-NULL (pengganti DELETE remedial).
                // Turun di bawah → status 'Belum' bila belum ada, biarkan kalau sudah
                // 'Sedang Proses'/'Selesai' (jaga progress guru).
                $nilaiTbl  = $db->table('nilai');
                $existing  = $nilaiTbl->getWhere([
                    'id_siswa'        => $s['id_siswa'],
                    'id_mapel'        => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                ])->getRowArray();

                $payload = [
                    'id_siswa'           => $s['id_siswa'],
                    'id_mapel'           => $id_mapel,
                    'id_tahun_ajaran'    => $id_tahun_ajaran,
                    'nilai_akhir'        => $nilaiAkhir,
                    'nilai_huruf'        => $scoreService->determineLetter($nilaiAkhir),
                    'status_kelulusan'   => $statusKelulusan,
                    'flag_borderline_75' => $flagBorderline ? 1 : 0,
                    'updated_at'         => date('Y-m-d H:i:s'),
                ];

                if ($statusKelulusan === 'Remedial') {
                    $currentStatus = $existing['status_remedial'] ?? null;
                    $payload['status_remedial'] = $currentStatus ?: 'Belum';
                } else {
                    $payload['status_remedial'] = null;
                    $payload['tindak_lanjut']   = null;
                }

                if ($existing) {
                    $nilaiTbl->where('id_nilai', (int) $existing['id_nilai'])->update($payload);
                } else {
                    $payload['created_at'] = $payload['updated_at'];
                    $nilaiTbl->insert($payload);
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

        // Pasca merge: kolom remedial (tindak_lanjut, status_remedial) inline di
        // tabel `nilai` → 1 LEFT JOIN cukup, hilangkan join ke tabel remedial.
        $siswa = $siswaModel->select('siswa.*, nilai.*')
            ->join(
                'nilai',
                'nilai.id_siswa = siswa.id_siswa
                 AND nilai.id_mapel = ' . (int) $id_mapel . '
                 AND nilai.id_tahun_ajaran = ' . (int) $id_tahun_ajaran,
                'left'
            )
            ->where('siswa.id_kelas', $id_kelas)
            ->where('siswa.id_tahun_ajaran', $id_tahun_ajaran)
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
                'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll(),
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

        // Query students who are Remedial (below KKM). Pasca merge: kolom remedial
        // (tindak_lanjut, status_remedial) sudah di tabel `nilai`.
        $siswaModel = new SiswaModel();
        $siswaRemedial = $siswaModel
            ->select('siswa.*, nilai.id_nilai, nilai.nilai_akhir, nilai.nilai_huruf, nilai.status_kelulusan, nilai.tindak_lanjut, nilai.status_remedial')
            ->join('nilai', 'nilai.id_siswa = siswa.id_siswa')
            ->where('siswa.id_kelas', $id_kelas)
            ->where('siswa.id_tahun_ajaran', $id_tahun_ajaran)
            ->where('siswa.status', 'aktif')
            ->where('nilai.id_mapel', $id_mapel)
            ->where('nilai.id_tahun_ajaran', $id_tahun_ajaran)
            ->where('nilai.status_kelulusan', 'Remedial')
            ->findAll();

        $data = [
            'title' => 'Rekap Remedial',
            'kelas_list' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel_list' => $mapelModel->getWithClasses(),
            'tahun_ajaran_list' => $tahunAjaranModel->where('aktif', 'aktif')->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll(),
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

    /**
     * Pek 6.4: simpan catatan_remedial untuk siswa borderline (nilai_akhir == 75).
     * POST body: array `catatan[id_nilai] => string`.
     * Validasi: kalau flag_borderline_75 = 1, catatan minimal 10 karakter.
     */
    public function saveCatatanBorderline()
    {
        $nilaiAkhirModel = new NilaiAkhirModel();
        $data = $this->request->getPost('catatan');

        if (!$data || !is_array($data)) {
            return redirect()->back()->with('error', 'Data catatan tidak valid.');
        }

        $idTahunAjaran = (int) $this->request->getPost('id_tahun_ajaran');
        $idKelas       = (int) $this->request->getPost('id_kelas');
        $idMapel       = (int) $this->request->getPost('id_mapel');

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAjaran = $idTahunAjaran ? $tahunAjaranModel->find($idTahunAjaran) : null;

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Ajukan request buka nilai.', $idKelas, $idMapel)) {
            return $response;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($data as $idNilai => $catatan) {
                $idNilai = (int) $idNilai;
                $row = $nilaiAkhirModel->find($idNilai);
                if (!$row) {
                    continue;
                }

                $isBorderline = ((int) ($row['flag_borderline_75'] ?? 0)) === 1;
                $catatan = trim((string) $catatan);

                if ($isBorderline && strlen($catatan) < 10) {
                    $db->transRollback();
                    return redirect()->back()->with('error',
                        'Catatan wajib diisi minimal 10 karakter untuk siswa dengan nilai akhir 75.');
                }

                $nilaiAkhirModel->update($idNilai, [
                    'catatan_remedial' => $catatan !== '' ? $catatan : null,
                ]);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal menyimpan catatan. Coba lagi.');
            }
            return redirect()->back()->with('success', 'Catatan borderline 75 tersimpan.');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception in saveCatatanBorderline: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
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
                // Pasca merge: key = id_nilai (kolom remedial inline di baris `nilai`).
                $id_nilai      = (int) ($item['id_nilai'] ?? 0);
                $tindak_lanjut = trim((string) ($item['tindak_lanjut'] ?? ''));

                if ($id_nilai <= 0) {
                    continue;
                }

                if (empty($tindak_lanjut)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Tindak lanjut remedial wajib diisi untuk semua siswa yang remedial!');
                }

                $remedialModel->setRemedial($id_nilai, $tindak_lanjut, 'Sedang Proses');
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
