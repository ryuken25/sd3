<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\MapelKelasModel;
use App\Models\MataPelajaranModel;

class Mapel extends BaseController
{
    public function index()
    {
        $mapelModel = new MataPelajaranModel();
        $kelasModel = new KelasModel();
        $mapelKelasModel = new MapelKelasModel();
        $filterKelas = (int) ($this->request->getGet('id_kelas') ?? 0);

        $mapel = $mapelModel->getWithClasses($filterKelas > 0 ? $filterKelas : null);
        $assignedRows = $mapelKelasModel->findAll();
        $assignedByMapel = [];
        foreach ($assignedRows as $row) {
            $assignedByMapel[(int) $row['id_mapel']][] = (int) $row['id_kelas'];
        }

        $data = [
            'title' => 'Data Mata Pelajaran',
            'mapel' => $mapel,
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'filter_kelas' => $filterKelas,
            'assigned_by_mapel' => $assignedByMapel,
        ];
        return view('admin/mapel/index', $data);
    }

    public function store()
    {
        $mapelModel = new MataPelajaranModel();
        $mapelKelasModel = new MapelKelasModel();
        $kelasIds = $this->request->getPost('id_kelas');
        $kelasIds = is_array($kelasIds) ? $kelasIds : [$kelasIds];

        if (!$this->validate([
            'kode_mapel' => 'required',
            'nama_mapel' => 'required',
            'kelompok' => 'required|in_list[A,B]',
            'id_kelas' => 'required',
        ])) {
            return redirect()->back()->with('error', 'Gagal menyimpan. Kelas belum dipilih atau data belum lengkap.')->withInput();
        }

        if ($message = $this->validateDuplicateMapelInClasses($this->request->getPost('kode_mapel'), $this->request->getPost('nama_mapel'), $kelasIds)) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        $mapelData = [
            'kode_mapel' => $this->request->getPost('kode_mapel'),
            'nama_mapel' => $this->request->getPost('nama_mapel'),
            'kelompok' => $this->request->getPost('kelompok'),
        ];

        if ($mapelModel->insert($mapelData)) {
            $idMapel = (int) $mapelModel->getInsertID();
            $mapelKelasModel->syncMapelClasses($idMapel, $kelasIds);
            return redirect()->to(base_url('admin/mapel'))->with('success', 'Mata Pelajaran berhasil ditambahkan dan sudah dikelompokkan berdasarkan kelas.');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data ke database.');
        }
    }

    public function update($id)
    {
        $mapelModel = new MataPelajaranModel();
        $mapelKelasModel = new MapelKelasModel();
        $mapel = $mapelModel->find($id);
        $kelasIds = $this->request->getPost('id_kelas');
        $kelasIds = is_array($kelasIds) ? $kelasIds : [$kelasIds];

        if (!$mapel) {
            return redirect()->to(base_url('admin/mapel'))->with('error', 'Data Mata Pelajaran tidak ditemukan.');
        }

        if (!$this->validate([
            'kode_mapel' => 'required',
            'nama_mapel' => 'required',
            'kelompok' => 'required|in_list[A,B]',
            'id_kelas' => 'required',
        ])) {
            return redirect()->back()->with('error', 'Gagal memperbarui. Kelas belum dipilih atau data belum lengkap.')->withInput();
        }

        if ($message = $this->validateDuplicateMapelInClasses($this->request->getPost('kode_mapel'), $this->request->getPost('nama_mapel'), $kelasIds, (int) $id)) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        $mapelData = [
            'kode_mapel' => $this->request->getPost('kode_mapel'),
            'nama_mapel' => $this->request->getPost('nama_mapel'),
            'kelompok' => $this->request->getPost('kelompok'),
        ];

        if ($mapelModel->update($id, $mapelData)) {
            $mapelKelasModel->syncMapelClasses((int) $id, $kelasIds);
            return redirect()->to(base_url('admin/mapel'))->with('success', 'Data Mata Pelajaran dan pembagian kelas berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function delete($id)
    {
        $mapelModel = new MataPelajaranModel();
        $mapel = $mapelModel->find($id);

        if (!$mapel) {
            return redirect()->to(base_url('admin/mapel'))->with('error', 'Data Mata Pelajaran tidak ditemukan.');
        }

        if ($mapelModel->delete($id)) {
            return redirect()->to(base_url('admin/mapel'))->with('success', 'Data Mata Pelajaran berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }

    private function validateDuplicateMapelInClasses($kodeMapel, $namaMapel, array $kelasIds, ?int $ignoreId = null): ?string
    {
        $kodeMapel = trim((string) $kodeMapel);
        $namaMapel = trim((string) $namaMapel);
        $kelasIds = array_values(array_unique(array_filter(array_map('intval', $kelasIds))));

        if (empty($kelasIds)) {
            return 'Kelas wajib dipilih sebelum menyimpan mata pelajaran.';
        }

        $db = \Config\Database::connect();
        $builder = $db->table('mata_pelajaran')
            ->select('mata_pelajaran.nama_mapel, mata_pelajaran.kode_mapel, kelas.nama_kelas')
            ->join('mapel_kelas', 'mapel_kelas.id_mapel = mata_pelajaran.id_mapel')
            ->join('kelas', 'kelas.id_kelas = mapel_kelas.id_kelas')
            ->groupStart()
            ->where('LOWER(mata_pelajaran.kode_mapel)', strtolower($kodeMapel))
            ->orWhere('LOWER(mata_pelajaran.nama_mapel)', strtolower($namaMapel))
            ->groupEnd()
            ->whereIn('mapel_kelas.id_kelas', $kelasIds);

        if ($ignoreId !== null) {
            $builder->where('mata_pelajaran.id_mapel !=', $ignoreId);
        }

        $duplicate = $builder->get()->getRowArray();
        if ($duplicate) {
            return 'Mata pelajaran/kode yang sama sudah berjalan pada kelas ' . $duplicate['nama_kelas'] . '. Duplikasi dalam kelas yang sama tidak diizinkan.';
        }

        return null;
    }
}
