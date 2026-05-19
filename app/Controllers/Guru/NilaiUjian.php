<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\MapelKelasModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiSiswaModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;

class NilaiUjian extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $data = [
            'title' => 'Input Nilai Ujian',
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel' => $mapelModel->getWithClasses(),
            'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll()
        ];

        return view('guru/nilai_ujian/index', $data);
    }

    /**
     * By Class: Input UTS/UAS for all students in a class, for one subject
     */
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

        $kelas = $kelasModel->find($id_kelas);
        $mapel = $mapelModel->find($id_mapel);
        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        if ($response = $this->rejectIfMapelNotInClass((int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        if ($response = $this->guardGradeWriteAccess($tahunAjaran, 'Semester sudah dikunci. Jika ada kesalahan nilai, ajukan request buka nilai kepada admin.', (int) $id_kelas, (int) $id_mapel)) {
            return $response;
        }

        $siswa = $siswaModel->where('id_kelas', $id_kelas)
            ->where('id_tahun_ajaran', $id_tahun_ajaran)
            ->where('status', 'aktif')
            ->findAll();

        // Fetch existing ujian grades
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
            'title' => 'Input Nilai Ujian (By Class)',
            'kelas' => $kelas,
            'mapel' => $mapel,
            'tahun_ajaran' => $tahunAjaran,
            'siswa' => $siswa,
            'nilai_existing' => $nilaiExisting,
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'id_tahun_ajaran' => $id_tahun_ajaran
        ];

        return view('guru/nilai_ujian/by_class', $data);
    }

    /**
     * Save UTS/UAS for all students
     */
    public function save()
    {
        $nilaiSiswaModel = new NilaiSiswaModel();
        $data = $this->request->getPost('nilai'); // array

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
                $nilai_uts = isset($item['nilai_uts']) && $item['nilai_uts'] !== '' ? $item['nilai_uts'] : null;
                $nilai_uas = isset($item['nilai_uas']) && $item['nilai_uas'] !== '' ? $item['nilai_uas'] : null;

                $siswaRow = (new SiswaModel())->find((int) $id_siswa);
                if (!$siswaRow || !$this->mapelBelongsToClass((int) $siswaRow['id_kelas'], (int) $id_mapel)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Simpan nilai ditolak karena mata pelajaran tidak sesuai kelas siswa.');
                }

                // Validate numeric values and ranges
                if ($nilai_uts !== null && (!is_numeric($nilai_uts) || $nilai_uts < 0 || $nilai_uts > 100)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Nilai UTS harus angka antara 0-100');
                }

                if ($nilai_uas !== null && (!is_numeric($nilai_uas) || $nilai_uas < 0 || $nilai_uas > 100)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Nilai UAS harus angka antara 0-100');
                }

                // Check if record exists
                $existing = $nilaiSiswaModel->where([
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran
                ])->first();

                if ($existing) {
                    $nilaiSiswaModel->update($existing['id_nilai_siswa'], [
                        'nilai_uts' => $nilai_uts,
                        'nilai_uas' => $nilai_uas
                    ]);
                } else {
                    $nilaiSiswaModel->insert([
                        'id_siswa' => $id_siswa,
                        'id_mapel' => $id_mapel,
                        'id_tahun_ajaran' => $id_tahun_ajaran,
                        'nilai_uts' => $nilai_uts,
                        'nilai_uas' => $nilai_uas
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', 'Transaction failed in NilaiUjian::save()');
                return redirect()->back()->with('error', 'Gagal menyimpan nilai ujian. Silakan coba lagi.');
            }

            return redirect()->back()->with('success', 'Nilai ujian berhasil disimpan!');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception in NilaiUjian::save(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
