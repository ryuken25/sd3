<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Libraries\AcademicScoreService;
use App\Models\KelasModel;
use App\Models\KkmModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiSiswaModel;
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
            'tahun_ajaran' => $tahunAjaranModel->where('aktif', 'aktif')->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll()
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

        // Get active students in this class for the SELECTED tahun ajaran.
        // Tanpa filter id_tahun_ajaran, siswa lintas-TA dengan kelas yang sama akan bocor
        // (satu NIS bisa punya banyak baris siswa sejak constraint UNIQUE direlaksasi ke composite (nis, id_tahun_ajaran)).
        $siswa = $siswaModel->where('id_kelas', $id_kelas)
            ->where('id_tahun_ajaran', $id_tahun_ajaran)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'ASC')
            ->findAll();

        // Pasca merge: komponen + nilai_akhir + remedial sudah di satu baris `nilai`.
        // View masih pakai dua variabel terpisah (nilai_siswa_existing & remedial_existing)
        // supaya layout tidak berubah; isi keduanya dari row gabungan yang sama.
        $nilaiSiswaExisting = [];
        $remedialExisting   = [];
        foreach ($siswa as $s) {
            $row = $nilaiSiswaModel->findByKey((int) $s['id_siswa'], (int) $id_mapel, (int) $id_tahun_ajaran);
            $nilaiSiswaExisting[$s['id_siswa']] = $row;
            if ($row && ($row['status_remedial'] ?? null) !== null) {
                $remedialExisting[$s['id_siswa']] = [
                    'tindak_lanjut'   => $row['tindak_lanjut'] ?? null,
                    'status_remedial' => $row['status_remedial'] ?? null,
                ];
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

        // Pasca merge: cukup satu upsert ke tabel `nilai` per (siswa, mapel, TA).
        // Pakai DB builder langsung karena payload menggabungkan kolom yang
        // tersebar di beberapa Model facade (allowedFields-nya subset).
        $kkmModel        = new KkmModel();
        $scoreService    = new AcademicScoreService();
        $db = \Config\Database::connect();
        $nilaiTbl = $db->table('nilai');
        $db->transStart();

        try {
            foreach ($data as $item) {
                $id_siswa        = (int) $item['id_siswa'];
                $id_mapel        = (int) $item['id_mapel'];
                $id_tahun_ajaran = (int) $item['id_tahun_ajaran'];

                $nilai_tugas   = isset($item['nilai_tugas'])   && $item['nilai_tugas']   !== '' ? $item['nilai_tugas']   : null;
                $nilai_ulangan = isset($item['nilai_ulangan']) && $item['nilai_ulangan'] !== '' ? $item['nilai_ulangan'] : null;
                $nilai_uts     = isset($item['nilai_uts'])     && $item['nilai_uts']     !== '' ? $item['nilai_uts']     : null;
                $nilai_uas     = isset($item['nilai_uas'])     && $item['nilai_uas']     !== '' ? $item['nilai_uas']     : null;
                $tindakLanjut  = trim((string) ($item['tindak_lanjut'] ?? ''));

                $siswaRow = (new SiswaModel())->find($id_siswa);
                if (!$siswaRow || !$this->mapelBelongsToClass((int) $siswaRow['id_kelas'], $id_mapel)) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Simpan nilai ditolak karena mata pelajaran tidak sesuai kelas siswa.');
                }

                foreach (['nilai_tugas' => $nilai_tugas, 'nilai_ulangan' => $nilai_ulangan, 'nilai_uts' => $nilai_uts, 'nilai_uas' => $nilai_uas] as $label => $val) {
                    if ($val !== null && (!is_numeric($val) || $val < 0 || $val > 100)) {
                        $db->transRollback();
                        return redirect()->back()->with('error', "Nilai $label harus angka antara 0-100");
                    }
                }

                $rata_rata     = $scoreService->calculateDailyAverage($nilai_tugas, $nilai_ulangan);
                $nilaiProyeksi = $scoreService->calculateFinalScore($nilai_tugas, $nilai_ulangan, $nilai_uts, $nilai_uas);

                $kkm = $kkmModel->where([
                    'id_kelas'        => $this->request->getPost('id_kelas'),
                    'id_mapel'        => $id_mapel,
                    'id_tahun_ajaran' => $id_tahun_ajaran,
                ])->first();
                $nilaiKkm = isset($kkm['nilai_kkm']) ? (float) $kkm['nilai_kkm'] : 70.0;

                if ($nilaiProyeksi < $nilaiKkm && $tindakLanjut === '') {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Tindak lanjut wajib diisi untuk setiap siswa dengan nilai proyeksi di bawah KKM.');
                }

                // Satu payload gabungan: komponen + nilai_akhir + remedial.
                // <KKM → set tindak_lanjut + status_remedial ('Belum' kalau kosong, else 'Sedang Proses').
                // >=KKM → kedua kolom remedial NULL (pengganti DELETE FROM remedial).
                $payload = [
                    'id_siswa'         => $id_siswa,
                    'id_mapel'         => $id_mapel,
                    'id_tahun_ajaran'  => $id_tahun_ajaran,
                    'nilai_tugas'      => $nilai_tugas,
                    'nilai_ulangan'    => $nilai_ulangan,
                    'rata_rata_harian' => $rata_rata,
                    'nilai_uts'        => $nilai_uts,
                    'nilai_uas'        => $nilai_uas,
                    'nilai_akhir'      => $nilaiProyeksi,
                    'nilai_huruf'      => $scoreService->determineLetter($nilaiProyeksi),
                    'status_kelulusan' => $scoreService->determineStatus($nilaiProyeksi, $nilaiKkm),
                    'tindak_lanjut'    => $nilaiProyeksi < $nilaiKkm ? ($tindakLanjut !== '' ? $tindakLanjut : null) : null,
                    'status_remedial'  => $nilaiProyeksi < $nilaiKkm ? ($tindakLanjut !== '' ? 'Sedang Proses' : 'Belum') : null,
                    'updated_at'       => date('Y-m-d H:i:s'),
                ];

                $existing = $nilaiTbl
                    ->getWhere([
                        'id_siswa'        => $id_siswa,
                        'id_mapel'        => $id_mapel,
                        'id_tahun_ajaran' => $id_tahun_ajaran,
                    ])
                    ->getRowArray();

                if ($existing) {
                    $db->table('nilai')
                        ->where('id_nilai', (int) $existing['id_nilai'])
                        ->update($payload);
                } else {
                    $payload['created_at'] = $payload['updated_at'];
                    $db->table('nilai')->insert($payload);
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
