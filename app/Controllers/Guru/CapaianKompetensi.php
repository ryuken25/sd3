<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Libraries\RaporNarrativeService;
use App\Models\KelasModel;
use App\Models\MapelKelasModel;
use App\Models\MasterCapaianPembelajaranModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiAkhirModel;
use App\Models\NilaiCapaianKompetensiModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;

/**
 * Input Capaian Kompetensi (Pek 3 - Megaprompt revisi).
 *
 * Workflow:
 *   1. Index: pilih kelas + mapel + TA
 *   2. Input: tampilkan list siswa di kelas tsb, per siswa show CP master untuk
 *      fase+semester mapel itu, status radio (tercapai_sangat_baik / perlu_peningkatan / belum)
 *   3. Save: upsert ke nilai_capaian_kompetensi per (nilai_akhir, master_cp_id)
 *
 * Catatan: CP tersimpan di-link ke `nilai_akhir`, jadi nilai_akhir untuk siswa+mapel+TA
 * harus sudah ada (dari Penilaian Agregat → Hitung Nilai Akhir).
 */
class CapaianKompetensi extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();
        $taModel = new TahunAjaranModel();

        return view('guru/capaian/index', [
            'title'        => 'Capaian Kompetensi',
            'kelas'        => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel'        => $mapelModel->getWithClasses(),
            'tahun_ajaran' => $taModel->where('aktif', 'aktif')
                ->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll(),
        ]);
    }

    public function input()
    {
        $idKelas = (int) $this->request->getGet('id_kelas');
        $idMapel = (int) $this->request->getGet('id_mapel');
        $idTa    = (int) $this->request->getGet('id_tahun_ajaran');

        if (!$idKelas || !$idMapel || !$idTa) {
            return redirect()->to(base_url('guru/capaian-kompetensi'))->with('error', 'Parameter tidak lengkap.');
        }

        if ($response = $this->rejectIfMapelNotInClass($idKelas, $idMapel)) {
            return $response;
        }

        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();
        $taModel = new TahunAjaranModel();
        $siswaModel = new SiswaModel();
        $masterCpModel = new MasterCapaianPembelajaranModel();
        $nilaiAkhirModel = new NilaiAkhirModel();
        $nilaiCpModel = new NilaiCapaianKompetensiModel();

        $kelas = $kelasModel->find($idKelas);
        $mapel = $mapelModel->find($idMapel);
        $ta    = $taModel->find($idTa);
        if (!$kelas || !$mapel || !$ta) {
            return redirect()->back()->with('error', 'Data master tidak ditemukan.');
        }

        if ($response = $this->guardGradeWriteAccess($ta,
            'Semester sudah dikunci. Ajukan request buka nilai ke admin.', $idKelas, $idMapel)) {
            return $response;
        }

        // Fase ditentukan dari tingkat kelas: 1-2=A, 3-4=B, 5-6=C
        $fase = match (true) {
            (int) $kelas['tingkat'] <= 2 => 'A',
            (int) $kelas['tingkat'] <= 4 => 'B',
            default                       => 'C',
        };

        $siswa = $siswaModel->where('id_kelas', $idKelas)
            ->where('id_tahun_ajaran', $idTa)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'ASC')
            ->findAll();

        $masterCp = $masterCpModel->findForMapel($idMapel, $fase, $ta['semester']);

        // Untuk tiap siswa: ambil id_nilai_akhir + existing CP entries (master + custom)
        $perSiswa = [];
        foreach ($siswa as $s) {
            $na = $nilaiAkhirModel->where('id_siswa', $s['id_siswa'])
                ->where('id_mapel', $idMapel)
                ->where('id_tahun_ajaran', $idTa)
                ->first();

            $existing = $na ? $nilaiCpModel->findForNilaiAkhir((int) $na['id_nilai_akhir']) : [];
            $byMaster = [];
            $custom   = [];
            foreach ($existing as $e) {
                if (!empty($e['master_cp_id'])) {
                    $byMaster[(int) $e['master_cp_id']] = $e;
                } else {
                    $custom[] = $e;
                }
            }

            $perSiswa[$s['id_siswa']] = [
                'siswa'          => $s,
                'id_nilai_akhir' => $na['id_nilai_akhir'] ?? null,
                'nilai_akhir'    => $na['nilai_akhir'] ?? null,
                'by_master'      => $byMaster,
                'custom'         => $custom,
            ];
        }

        return view('guru/capaian/input', [
            'title'           => 'Capaian Kompetensi — Input',
            'kelas'           => $kelas,
            'mapel'           => $mapel,
            'tahun_ajaran'    => $ta,
            'fase'            => $fase,
            'master_cp'       => $masterCp,
            'per_siswa'       => $perSiswa,
            'id_kelas'        => $idKelas,
            'id_mapel'        => $idMapel,
            'id_tahun_ajaran' => $idTa,
        ]);
    }

    /**
     * POST body:
     *   cp[id_nilai_akhir][master][id_master_cp] = 'tercapai_sangat_baik'|'perlu_peningkatan'|'belum'
     *   cp[id_nilai_akhir][custom][] = ['deskripsi' => ..., 'status' => ...]
     */
    public function save()
    {
        $data = $this->request->getPost('cp');
        $idTa    = (int) $this->request->getPost('id_tahun_ajaran');
        $idKelas = (int) $this->request->getPost('id_kelas');
        $idMapel = (int) $this->request->getPost('id_mapel');

        if (!$data || !\is_array($data)) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }

        $taModel = new TahunAjaranModel();
        $ta = $taModel->find($idTa);
        if ($response = $this->guardGradeWriteAccess($ta,
            'Semester sudah dikunci. Ajukan request buka nilai ke admin.', $idKelas, $idMapel)) {
            return $response;
        }

        $nilaiCpModel = new NilaiCapaianKompetensiModel();
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($data as $idNilaiAkhir => $entries) {
                $idNilaiAkhir = (int) $idNilaiAkhir;
                if ($idNilaiAkhir <= 0) continue;

                // Reset existing rows untuk nilai_akhir ini, insert ulang
                $nilaiCpModel->where('id_nilai_akhir', $idNilaiAkhir)->delete();

                // Master CP entries
                foreach (($entries['master'] ?? []) as $idMasterCp => $status) {
                    $idMasterCp = (int) $idMasterCp;
                    if ($status !== 'tercapai_sangat_baik' && $status !== 'perlu_peningkatan') {
                        continue; // skip "belum"
                    }
                    $nilaiCpModel->insert([
                        'id_nilai_akhir'   => $idNilaiAkhir,
                        'master_cp_id'     => $idMasterCp,
                        'deskripsi_custom' => null,
                        'status'           => $status,
                    ]);
                }

                // Custom CP entries
                foreach (($entries['custom'] ?? []) as $cust) {
                    $desc   = trim((string) ($cust['deskripsi'] ?? ''));
                    $status = (string) ($cust['status'] ?? '');
                    if ($desc === '' || ($status !== 'tercapai_sangat_baik' && $status !== 'perlu_peningkatan')) {
                        continue;
                    }
                    $nilaiCpModel->insert([
                        'id_nilai_akhir'   => $idNilaiAkhir,
                        'master_cp_id'     => null,
                        'deskripsi_custom' => $desc,
                        'status'           => $status,
                    ]);
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal menyimpan CP. Coba lagi.');
            }

            return redirect()->back()->with('success', 'Capaian Kompetensi berhasil disimpan.');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception CP save: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
