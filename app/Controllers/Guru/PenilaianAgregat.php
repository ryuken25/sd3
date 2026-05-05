<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Libraries\AcademicScoreService;
use App\Models\KelasModel;
use App\Models\KkmModel;
use App\Models\MapelKelasModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiAkhirModel;
use App\Models\NilaiSiswaModel;
use App\Models\RemedialModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;

class PenilaianAgregat extends BaseController
{
    /**
     * Filter page: select kelas, mapel, and tahun ajaran
     */
    public function index()
    {
        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $data = [
            'title' => 'Penilaian Agregat',
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel' => $mapelModel->getWithClasses(),
            'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->findAll()
        ];

        return view('guru/penilaian_agregat/index', $data);
    }

    /**
     * Unified input form: show all students with 4 score columns
     */
    public function input()
    {
        $id_kelas = $this->request->getGet('id_kelas');
        $id_mapel = $this->request->getGet('id_mapel');
        $id_tahun_ajaran = $this->request->getGet('id_tahun_ajaran');

        if (!$id_kelas || !$id_mapel || !$id_tahun_ajaran) {
            return redirect()->back()->with('error', 'Parameter tidak lengkap');
        }

        $siswaModel = new SiswaModel();
        $nilaiSiswaModel = new NilaiSiswaModel();
        $kelasModel = new KelasModel();
        $kkmModel = new KkmModel();
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $kelas = $kelasModel->find($id_kelas);
        $mapel = $mapelModel->find($id_mapel);
        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        if ($response = $this->rejectIfMapelNotInClass((int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', (int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        // Get active students in this class
        $siswa = $siswaModel->where('id_kelas', $id_kelas)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'ASC')
            ->findAll();

        $nilaiSiswaExisting = [];
        $remedialExisting = [];
        foreach ($siswa as $s) {
            $nilai = $nilaiSiswaModel->where([
                'id_siswa' => $s['id_siswa'],
                'id_mapel' => $id_mapel,
                'id_tahun_ajaran' => $id_tahun_ajaran
            ])->first();
            $nilaiSiswaExisting[$s['id_siswa']] = $nilai;

            $nilaiAkhir = (new NilaiAkhirModel())->where([
                'id_siswa' => $s['id_siswa'],
                'id_mapel' => $id_mapel,
                'id_tahun_ajaran' => $id_tahun_ajaran,
            ])->first();

            if ($nilaiAkhir) {
                $remedialExisting[$s['id_siswa']] = (new RemedialModel())
                    ->where('id_nilai_akhir', $nilaiAkhir['id_nilai_akhir'])
                    ->first();
            }
        }

        $kkm = $kkmModel->where([
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran,
        ])->first();

        $data = [
            'title' => 'Penilaian Agregat — Input Nilai',
            'kelas' => $kelas,
            'mapel' => $mapel,
            'tahun_ajaran' => $tahunAjaran,
            'siswa' => $siswa,
            'nilai_siswa_existing' => $nilaiSiswaExisting,
            'remedial_existing' => $remedialExisting,
            'kkm' => $kkm,
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran
        ];

        return view('guru/penilaian_agregat/input', $data);
    }

    /**
     * Save all 4 components to nilai_siswa and auto-sync remedial requirements.
     */
    public function save()
    {
        $data = $this->request->getPost('nilai');

        if (!$data || !is_array($data)) {
            return redirect()->back()->with('error', 'Data nilai tidak valid');
        }

        $firstItem = reset($data);
        $idTahunAjaran = $firstItem['id_tahun_ajaran'] ?? null;
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAjaran = $idTahunAjaran ? $tahunAjaranModel->find($idTahunAjaran) : null;

        $firstStudent = isset($firstItem['id_siswa']) ? (new SiswaModel())->find((int) $firstItem['id_siswa']) : null;
        $firstKelasId = $firstStudent ? (int) $firstStudent['id_kelas'] : (int) ($this->request->getPost('id_kelas') ?? 0);
        $firstMapelId = isset($firstItem['id_mapel']) ? (int) $firstItem['id_mapel'] : null;

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', $firstKelasId, $firstMapelId)) {
            return $response;
        }

        $nilaiSiswaModel = new NilaiSiswaModel();
        $nilaiAkhirModel = new NilaiAkhirModel();
        $remedialModel = new RemedialModel();
        $kkmModel = new KkmModel();
        $scoreService = new AcademicScoreService();
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($data as $item) {
                $id_siswa = $item['id_siswa'];
                $id_mapel = $item['id_mapel'];
                $id_tahun_ajaran = $item['id_tahun_ajaran'];

                // Extract and sanitize values
                $nilai_tugas = isset($item['nilai_tugas']) && $item['nilai_tugas'] !== '' ? $item['nilai_tugas'] : null;
                $nilai_ulangan = isset($item['nilai_ulangan']) && $item['nilai_ulangan'] !== '' ? $item['nilai_ulangan'] : null;
                $nilai_uts = isset($item['nilai_uts']) && $item['nilai_uts'] !== '' ? $item['nilai_uts'] : null;
                $nilai_uas = isset($item['nilai_uas']) && $item['nilai_uas'] !== '' ? $item['nilai_uas'] : null;
                $tindakLanjut = trim((string) ($item['tindak_lanjut'] ?? ''));

                $siswaRow = (new SiswaModel())->find((int) $id_siswa);
                if (!$siswaRow || !$this->mapelBelongsToClass((int) $siswaRow['id_kelas'], (int) $id_mapel)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Simpan nilai ditolak karena mata pelajaran tidak sesuai kelas siswa.');
                }

                // Validate ranges (0-100)
                foreach (['nilai_tugas' => $nilai_tugas, 'nilai_ulangan' => $nilai_ulangan, 'nilai_uts' => $nilai_uts, 'nilai_uas' => $nilai_uas] as $label => $val) {
                    if ($val !== null && (!is_numeric($val) || $val < 0 || $val > 100)) {
                        $db->transRollback();
                        return redirect()->back()->with('error', "Nilai $label harus angka antara 0-100");
                    }
                }

                $rata_rata = $scoreService->calculateDailyAverage($nilai_tugas, $nilai_ulangan);
                $nilaiProyeksi = $scoreService->calculateFinalScore($nilai_tugas, $nilai_ulangan, $nilai_uts, $nilai_uas);

                $kkm = $kkmModel->where([
                    'id_kelas' => $this->request->getPost('id_kelas'),
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                ])->first();
                $nilaiKkm = isset($kkm['nilai_kkm']) ? (float) $kkm['nilai_kkm'] : 70.0;

                if ($nilaiProyeksi < $nilaiKkm && $tindakLanjut === '') {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Tindak lanjut wajib diisi untuk setiap siswa dengan nilai proyeksi di bawah KKM.');
                }

                $existing = $nilaiSiswaModel->where([
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran
                ])->first();

                $payload = [
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                    'nilai_tugas' => $nilai_tugas,
                    'nilai_ulangan' => $nilai_ulangan,
                    'rata_rata_harian' => $rata_rata,
                    'nilai_uts' => $nilai_uts,
                    'nilai_uas' => $nilai_uas,
                ];

                if ($existing) {
                    $nilaiSiswaModel->update($existing['id_nilai_siswa'], $payload);
                } else {
                    $nilaiSiswaModel->insert($payload);
                }

                $existingNilaiAkhir = $nilaiAkhirModel->where([
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                ])->first();

                $nilaiAkhirPayload = [
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                    'nilai_akhir' => $nilaiProyeksi,
                    'nilai_huruf' => $scoreService->determineLetter($nilaiProyeksi),
                    'status_kelulusan' => $scoreService->determineStatus($nilaiProyeksi, $nilaiKkm),
                ];

                if ($existingNilaiAkhir) {
                    $nilaiAkhirModel->update($existingNilaiAkhir['id_nilai_akhir'], $nilaiAkhirPayload);
                    $nilaiAkhirId = (int) $existingNilaiAkhir['id_nilai_akhir'];
                } else {
                    $nilaiAkhirModel->insert($nilaiAkhirPayload);
                    $nilaiAkhirId = (int) $nilaiAkhirModel->getInsertID();
                }

                $existingRemedial = $remedialModel->where('id_nilai_akhir', $nilaiAkhirId)->first();

                if ($nilaiProyeksi < $nilaiKkm) {
                    $remedialPayload = [
                        'id_nilai_akhir' => $nilaiAkhirId,
                        'tindak_lanjut' => $tindakLanjut,
                        'status_remedial' => $tindakLanjut === '' ? 'Belum' : 'Sedang Proses',
                    ];

                    if ($existingRemedial) {
                        $remedialModel->update($existingRemedial['id_remedial'], $remedialPayload);
                    } else {
                        $remedialModel->insert($remedialPayload);
                    }
                } elseif ($existingRemedial) {
                    $remedialModel->delete($existingRemedial['id_remedial']);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', 'Transaction failed in PenilaianAgregat::save()');
                return redirect()->back()->with('error', 'Gagal menyimpan nilai. Silakan coba lagi.');
            }

            return redirect()->back()->with('success', 'Semua nilai berhasil disimpan dan remedial otomatis disinkronkan.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception in PenilaianAgregat::save(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
