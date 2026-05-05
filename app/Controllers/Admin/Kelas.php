<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\UserModel;

class Kelas extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();
        $userModel = new UserModel();

        $kelas = $kelasModel->select('kelas.*, users.nama_lengkap as wali_kelas_nama')
            ->join('users', 'users.id_user = kelas.wali_kelas', 'left')
            ->findAll();

        $data = [
            'title' => 'Data Kelas',
            'kelas' => $kelas,
            'guru' => $userModel->where('level', 'guru')->findAll(),
        ];
        return view('admin/kelas/index', $data);
    }

    public function store()
    {
        $kelasModel = new KelasModel();

        if (
            !$this->validate([
                'nama_kelas' => 'required',
                'tingkat' => 'required|in_list[1,2,3,4,5,6]',
            ])
        ) {
            return redirect()->back()->with('error', 'Gagal menyimpan. Data tidak lengkap atau format tingkat salah.');
        }

        $wali_kelas = $this->request->getPost('wali_kelas');

        $kelasData = [
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'tingkat' => $this->request->getPost('tingkat'),
            'wali_kelas' => empty($wali_kelas) ? null : $wali_kelas,
        ];

        if ($kelasModel->insert($kelasData)) {
            return redirect()->to(base_url('admin/kelas'))->with('success', 'Data Kelas & Wali Kelas berhasil ditambahkan.');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data ke database.');
        }
    }

    public function update($id)
    {
        $kelasModel = new KelasModel();
        $kelas = $kelasModel->find($id);

        if (!$kelas) {
            return redirect()->to(base_url('admin/kelas'))->with('error', 'Data kelas tidak ditemukan.');
        }

        if (
            !$this->validate([
                'nama_kelas' => 'required',
                'tingkat' => 'required|in_list[1,2,3,4,5,6]',
            ])
        ) {
            return redirect()->back()->with('error', 'Gagal mengupdate. Data tidak lengkap atau format tingkat salah.');
        }

        $wali_kelas = $this->request->getPost('wali_kelas');

        $kelasData = [
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'tingkat' => $this->request->getPost('tingkat'),
            'wali_kelas' => empty($wali_kelas) ? null : $wali_kelas,
        ];

        if ($kelasModel->update($id, $kelasData)) {
            return redirect()->to(base_url('admin/kelas'))->with('success', 'Data Kelas berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function delete($id)
    {
        $kelasModel = new KelasModel();
        $kelas = $kelasModel->find($id);

        if (!$kelas) {
            return redirect()->to(base_url('admin/kelas'))->with('error', 'Data kelas tidak ditemukan.');
        }

        if ($kelasModel->delete($id)) {
            return redirect()->to(base_url('admin/kelas'))->with('success', 'Data Kelas berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }
}
