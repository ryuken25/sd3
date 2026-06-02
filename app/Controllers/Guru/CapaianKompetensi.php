<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\MasterCapaianPembelajaranModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiAkhirModel;
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

        // Peta narasi template per band predikat (A/B/C/D) untuk mapel+fase+semester ini.
        $bandMap = $masterCpModel->getBandMap($idMapel, $fase, $ta['semester']);

        // Ambil semua nilai_akhir kelas ini dalam satu query (hindari N+1 di loop),
        // di-index per id_siswa untuk lookup cepat.
        $naBySiswa = [];
        $siswaIds  = array_column($siswa, 'id_siswa');
        if ($siswaIds !== []) {
            $naRows = $nilaiAkhirModel->whereIn('id_siswa', $siswaIds)
                ->where('id_mapel', $idMapel)
                ->where('id_tahun_ajaran', $idTa)
                ->findAll();
            foreach ($naRows as $row) {
                $naBySiswa[$row['id_siswa']] = $row;
            }
        }

        // Untuk tiap siswa: id_nilai_akhir, narasi_cp existing, band dari nilai_huruf.
        $perSiswa = [];
        foreach ($siswa as $s) {
            $na = $naBySiswa[$s['id_siswa']] ?? null;

            // Mapping huruf → band: A→A, B→B, C→C, D→D, E→D, lainnya kosong.
            $huruf = strtoupper((string) ($na['nilai_huruf'] ?? ''));
            $band  = match ($huruf) {
                'A' => 'A', 'B' => 'B', 'C' => 'C', 'D', 'E' => 'D',
                default => '',
            };

            $perSiswa[$s['id_siswa']] = [
                'siswa'          => $s,
                'id_nilai_akhir' => $na['id_nilai_akhir'] ?? null,
                'nilai_akhir'    => $na['nilai_akhir'] ?? null,
                'band'           => $band,
                'narasi_cp'      => $na['narasi_cp'] ?? '',
            ];
        }

        return view('guru/capaian/input', [
            'title'           => 'Capaian Kompetensi — Input',
            'kelas'           => $kelas,
            'mapel'           => $mapel,
            'tahun_ajaran'    => $ta,
            'fase'            => $fase,
            'band_map'        => $bandMap,
            'per_siswa'       => $perSiswa,
            'id_kelas'        => $idKelas,
            'id_mapel'        => $idMapel,
            'id_tahun_ajaran' => $idTa,
        ]);
    }

    /**
     * Simpan narasi capaian manual per siswa verbatim ke nilai_akhir.narasi_cp.
     * POST body: narasi[id_nilai_akhir] = "teks final" (boleh hasil prefill band, sudah diedit).
     */
    public function save()
    {
        $narasi  = $this->request->getPost('narasi');
        $idTa    = (int) $this->request->getPost('id_tahun_ajaran');
        $idKelas = (int) $this->request->getPost('id_kelas');
        $idMapel = (int) $this->request->getPost('id_mapel');

        if (!$narasi || !\is_array($narasi)) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }

        $taModel = new TahunAjaranModel();
        $ta = $taModel->find($idTa);
        if ($response = $this->guardGradeWriteAccess($ta,
            'Semester sudah dikunci. Ajukan request buka nilai ke admin.', $idKelas, $idMapel)) {
            return $response;
        }

        $nilaiAkhirModel = new NilaiAkhirModel();
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($narasi as $idNilaiAkhir => $teks) {
                $idNilaiAkhir = (int) $idNilaiAkhir;
                if ($idNilaiAkhir <= 0) {
                    continue;
                }
                $teks = trim((string) $teks);
                $nilaiAkhirModel->update($idNilaiAkhir, ['narasi_cp' => $teks !== '' ? $teks : null]);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal menyimpan narasi capaian. Coba lagi.');
            }

            return redirect()->back()->with('success', 'Capaian Kompetensi berhasil disimpan.');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception CP save: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
