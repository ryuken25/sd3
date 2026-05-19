<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KkmModel;
use App\Models\MataPelajaranModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;

class Kkm extends BaseController
{
    public function index()
    {
        $kkmModel = new KkmModel();
        $mapelModel = new MataPelajaranModel();
        $kelasModel = new KelasModel();
        $taModel = new TahunAjaranModel();
        $filterKelas = (int) ($this->request->getGet('id_kelas') ?? 0);
        $filterTa    = (int) ($this->request->getGet('id_tahun_ajaran') ?? 0);

        // Default: pakai TA aktif kalau filter TA tidak di-set,
        // supaya halaman tidak ramai dengan KKM lintas TA.
        if ($filterTa === 0) {
            $taAktif = $taModel->where('aktif', 'aktif')
                               ->orderBy('id_tahun_ajaran', 'DESC')
                               ->first();
            $filterTa = $taAktif ? (int) $taAktif['id_tahun_ajaran'] : 0;
        }

        $kkmBuilder = $kkmModel->select('kkm.*, mata_pelajaran.nama_mapel, mata_pelajaran.kode_mapel, kelas.nama_kelas, kelas.tingkat, tahun_ajaran.tahun_ajaran, tahun_ajaran.semester, tahun_ajaran.aktif')
                            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = kkm.id_mapel')
                            ->join('kelas', 'kelas.id_kelas = kkm.id_kelas')
                            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = kkm.id_tahun_ajaran', 'left')
                            ->orderBy('kelas.tingkat', 'ASC')
                            ->orderBy('kelas.nama_kelas', 'ASC')
                            ->orderBy('mata_pelajaran.nama_mapel', 'ASC');

        if ($filterKelas > 0) {
            $kkmBuilder->where('kkm.id_kelas', $filterKelas);
        }
        if ($filterTa > 0) {
            $kkmBuilder->where('kkm.id_tahun_ajaran', $filterTa);
        }

        $kkmList = $kkmBuilder->findAll();

        $data = [
            'title' => 'Konfigurasi KKM',
            'kkm'   => $kkmList,
            'mapel' => $mapelModel->getWithClasses(),
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'ta'    => $taModel->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll(),
            'filter_kelas' => $filterKelas,
            'filter_ta'    => $filterTa,
        ];
        return view('admin/kkm/index', $data);
    }

    public function store()
    {
        $kkmModel = new KkmModel();

        if (!$this->validate([
            'id_mapel'          => 'required',
            'id_kelas'          => 'required',
            'nilai_kkm'         => 'required|decimal',
            'id_tahun_ajaran'   => 'required'
        ])) {
            return redirect()->back()->with('error', 'Gagal menyimpan. Pastikan semua data termasuk format desimal KKM sudah benar.');
        }

        $kkmData = [
            'id_mapel'          => (int) $this->request->getPost('id_mapel'),
            'id_kelas'          => (int) $this->request->getPost('id_kelas'),
            'nilai_kkm'         => $this->request->getPost('nilai_kkm'),
            'id_tahun_ajaran'   => (int) $this->request->getPost('id_tahun_ajaran')
        ];

        if ($response = $this->rejectIfMapelNotInClass($kkmData['id_kelas'], $kkmData['id_mapel'])) {
            return $response;
        }

        // Cek duplicate manual (since unique constraint exists)
        $exists = $kkmModel->where('id_mapel', $kkmData['id_mapel'])
                           ->where('id_kelas', $kkmData['id_kelas'])
                           ->where('id_tahun_ajaran', $kkmData['id_tahun_ajaran'])
                           ->first();

        if ($exists) {
            $kkmModel->update($exists['id_kkm'], ['nilai_kkm' => $kkmData['nilai_kkm']]);
            return redirect()->to(base_url('admin/kkm'))->with('success', 'Nilai KKM berhasil diupdate (Data sudah ada sebelumnya).');
        } else {
            if ($kkmModel->insert($kkmData)) {
                return redirect()->to(base_url('admin/kkm'))->with('success', 'Konfigurasi KKM baru berhasil ditambahkan.');
            } else {
                return redirect()->back()->with('error', 'Gagal menyimpan data ke database.');
            }
        }
    }

    public function update($id)
    {
        $kkmModel = new KkmModel();
        $kkm = $kkmModel->find($id);

        if (!$kkm) {
            return redirect()->to(base_url('admin/kkm'))->with('error', 'Data KKM tidak ditemukan.');
        }

        if (!$this->validate([
            'nilai_kkm' => 'required|decimal'
        ])) {
            return redirect()->back()->with('error', 'Format nilai KKM tidak valid.');
        }

        $kkmData = [
            'id_mapel'          => (int) $this->request->getPost('id_mapel'),
            'id_kelas'          => (int) $this->request->getPost('id_kelas'),
            'nilai_kkm'         => $this->request->getPost('nilai_kkm'),
            'id_tahun_ajaran'   => (int) $this->request->getPost('id_tahun_ajaran')
        ];

        if ($response = $this->rejectIfMapelNotInClass($kkmData['id_kelas'], $kkmData['id_mapel'])) {
            return $response;
        }

        if ($kkmModel->update($id, $kkmData)) {
            return redirect()->to(base_url('admin/kkm'))->with('success', 'Data KKM berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui data KKM.');
        }
    }

    public function delete($id)
    {
        $kkmModel = new KkmModel();
        $kkm = $kkmModel->find($id);

        if (!$kkm) {
            return redirect()->to(base_url('admin/kkm'))->with('error', 'Data KKM tidak ditemukan.');
        }

        if ($kkmModel->delete($id)) {
            return redirect()->to(base_url('admin/kkm'))->with('success', 'Data KKM berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus data KKM.');
        }
    }
}
