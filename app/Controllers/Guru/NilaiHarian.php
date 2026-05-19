<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Libraries\AcademicScoreService;
use App\Models\KelasModel;
use App\Models\MapelKelasModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiSiswaModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;

class NilaiHarian extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $data = [
            'title' => 'Input Nilai Harian',
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel' => $mapelModel->getWithClasses(),
            'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll()
        ];

        return view('guru/nilai_harian/index', $data);
    }

    // Mode By Class: Display all students in a class for one subject
    public function byClass()
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
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new TahunAjaranModel();

        // Get class, subject, and academic year info
        $kelas = $kelasModel->find($id_kelas);
        $mapel = $mapelModel->find($id_mapel);
        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        if ($response = $this->rejectIfMapelNotInClass((int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', (int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        // Get all students in this class for the selected tahun ajaran (multi-TA siswa rows would otherwise leak).
        $siswa = $siswaModel->where('id_kelas', $id_kelas)
            ->where('id_tahun_ajaran', $id_tahun_ajaran)
            ->where('status', 'aktif')
            ->findAll();

        // Get existing grades
        $nilaiExisting = [];
        foreach ($siswa as $s) {
            $nilai = $nilaiSiswaModel->where([
                'id_siswa' => $s['id_siswa'],
                'id_mapel' => $id_mapel,
                'id_tahun_ajaran' => $id_tahun_ajaran
            ])->first();
            $nilaiExisting[$s['id_siswa']] = $nilai;
        }

        $data = [
            'title' => 'Input Nilai Harian (Mode: By Class)',
            'kelas' => $kelas,
            'mapel' => $mapel,
            'tahun_ajaran' => $tahunAjaran,
            'siswa' => $siswa,
            'nilai_existing' => $nilaiExisting,
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran
        ];

        return view('guru/nilai_harian/by_class', $data);
    }

    // Mode By Student: Display all subjects for one student
    public function byStudent()
    {
        $id_siswa = $this->request->getGet('id_siswa');
        $id_kelas = $this->request->getGet('id_kelas');
        $id_tahun_ajaran = $this->request->getGet('id_tahun_ajaran');

        if (!$id_siswa || !$id_tahun_ajaran) {
            return redirect()->back()->with('error', 'Parameter tidak lengkap');
        }

        $siswaModel = new SiswaModel();
        $mapelModel = new MataPelajaranModel();
        $nilaiSiswaModel = new NilaiSiswaModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $siswa = $siswaModel->find($id_siswa);
        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', $siswa ? (int) $siswa['id_kelas'] : null, null)) {
            return $response;
        }

        // Get subjects assigned to the student's class
        $mapel = $mapelModel->getByClass((int) ($siswa['id_kelas'] ?? 0));

        // Get existing grades
        $nilaiExisting = [];
        foreach ($mapel as $m) {
            $nilai = $nilaiSiswaModel->where([
                'id_siswa' => $id_siswa,
                'id_mapel' => $m['id_mapel'],
                'id_tahun_ajaran' => $id_tahun_ajaran
            ])->first();
            $nilaiExisting[$m['id_mapel']] = $nilai;
        }

        $data = [
            'title' => 'Input Nilai Harian (Mode: By Student)',
            'siswa' => $siswa,
            'mapel' => $mapel,
            'tahun_ajaran' => $tahunAjaran,
            'nilai_existing' => $nilaiExisting,
            'id_siswa' => $id_siswa,
            'id_tahun_ajaran' => $id_tahun_ajaran
        ];

        return view('guru/nilai_harian/by_student', $data);
    }

    /**
     * API: Return JSON list of students for a given class (used by JS in By Student mode)
     */
    public function getSiswa()
    {
        $id_kelas = $this->request->getGet('id_kelas');
        $id_tahun_ajaran = $this->request->getGet('id_tahun_ajaran');
        if (!$id_kelas || !$id_tahun_ajaran) {
            return $this->response->setJSON([]);
        }

        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->where('id_kelas', $id_kelas)
            ->where('id_tahun_ajaran', $id_tahun_ajaran)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'ASC')
            ->findAll();

        return $this->response->setJSON($siswa);
    }

    // Save grades (for both modes)
    public function save()
    {
        $nilaiSiswaModel = new NilaiSiswaModel();
        $scoreService = new AcademicScoreService();
        $data = $this->request->getPost('nilai'); // Array of grades

        if (!$data || !is_array($data)) {
            return redirect()->back()->with('error', 'Data nilai tidak valid');
        }

        $firstItem = reset($data);
        $idTahunAjaran = $firstItem['id_tahun_ajaran'] ?? null;
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAjaran = $idTahunAjaran ? $tahunAjaranModel->find($idTahunAjaran) : null;

        $firstStudent = isset($firstItem['id_siswa']) ? (new SiswaModel())->find((int) $firstItem['id_siswa']) : null;
        $firstKelasId = $firstStudent ? (int) $firstStudent['id_kelas'] : null;
        $firstMapelId = isset($firstItem['id_mapel']) ? (int) $firstItem['id_mapel'] : null;

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', $firstKelasId, $firstMapelId)) {
            return $response;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
        foreach ($data as $item) {
                $id_siswa = $item['id_siswa'];
                $id_mapel = $item['id_mapel'];
                $id_tahun_ajaran = $item['id_tahun_ajaran'];
                $nilai_tugas = $item['nilai_tugas'] ?? null;
                $nilai_ulangan = $item['nilai_ulangan'] ?? null;

                $siswaRow = (new SiswaModel())->find((int) $id_siswa);
                if (!$siswaRow || !$this->mapelBelongsToClass((int) $siswaRow['id_kelas'], (int) $id_mapel)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Simpan nilai ditolak karena mata pelajaran tidak sesuai kelas siswa.');
                }

                // Validate numeric values and ranges
                if ($nilai_tugas !== null && (!is_numeric($nilai_tugas) || $nilai_tugas < 0 || $nilai_tugas > 100)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Nilai tugas harus angka antara 0-100');
                }

                if ($nilai_ulangan !== null && (!is_numeric($nilai_ulangan) || $nilai_ulangan < 0 || $nilai_ulangan > 100)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Nilai ulangan harus angka antara 0-100');
                }

                // Calculate average
                $rata_rata = $scoreService->calculateDailyAverage($nilai_tugas, $nilai_ulangan);

                $existing = $nilaiSiswaModel->where([
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran
                ])->first();

                if ($existing) {
                    $nilaiSiswaModel->update($existing['id_nilai_siswa'], [
                        'nilai_tugas' => $nilai_tugas,
                        'nilai_ulangan' => $nilai_ulangan,
                        'rata_rata_harian' => $rata_rata
                    ]);
                } else {
                    $nilaiSiswaModel->insert([
                        'id_siswa' => $id_siswa,
                        'id_mapel' => $id_mapel,
                        'id_tahun_ajaran' => $id_tahun_ajaran,
                        'nilai_tugas' => $nilai_tugas,
                        'nilai_ulangan' => $nilai_ulangan,
                        'rata_rata_harian' => $rata_rata
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                log_message('error', 'Transaction failed in NilaiHarian::save()');
                return redirect()->back()->with('error', 'Gagal menyimpan nilai. Silakan coba lagi.');
            }

            return redirect()->back()->with('success', 'Nilai berhasil disimpan');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception in NilaiHarian::save(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
