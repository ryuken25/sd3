<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Guru extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();

        // Fetch only guru
        $guru = $userModel->where('level', 'guru')->findAll();

        $data = [
            'title' => 'Data Guru',
            'guru'  => $guru
        ];
        return view('admin/guru/index', $data);
    }

    public function store()
    {
        $userModel = new UserModel();

        if (!$this->validate([
            'username'     => 'required|is_unique[users.username]',
            'nama_lengkap' => 'required',
            'password'     => 'required'
        ])) {
            return redirect()->back()->with('error', 'Gagal menyimpan. Username sudah digunakan atau data tidak lengkap.');
        }

        $userData = [
            'username'     => $this->request->getPost('username'),
            'password'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'no_telp'      => $this->request->getPost('no_telp'),
            'level'        => 'guru',
            'status'       => 'aktif'
        ];

        if ($userModel->insert($userData)) {
            return redirect()->to(base_url('admin/guru'))->with('success', 'Data Guru berhasil ditambahkan.');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data ke database.');
        }
    }

    public function update($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user || $user['level'] !== 'guru') {
            return redirect()->to(base_url('admin/guru'))->with('error', 'Data Guru tidak ditemukan.');
        }

        if (!$this->validate([
            'username'     => 'required|is_unique[users.username,id_user,' . $id . ']',
            'nama_lengkap' => 'required',
        ])) {
            return redirect()->back()->with('error', 'Gagal mengupdate. Username sudah digunakan atau data tidak lengkap.');
        }

        $userData = [
            'username'     => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'no_telp'      => $this->request->getPost('no_telp'),
            'status'       => $this->request->getPost('status') ?? 'aktif'
        ];

        // Only update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            // FIX BUG-10: Validate minimum password length
            if (strlen($password) < 6) {
                return redirect()->back()->with('error', 'Password minimal 6 karakter.');
            }
            $userData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($userModel->update($id, $userData)) {
            return redirect()->to(base_url('admin/guru'))->with('success', 'Data Guru berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function delete($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user || $user['level'] !== 'guru') {
            return redirect()->to(base_url('admin/guru'))->with('error', 'Data Guru tidak ditemukan.');
        }

        if ($userModel->delete($id)) {
            return redirect()->to(base_url('admin/guru'))->with('success', 'Data Guru berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }
}
