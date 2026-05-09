<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;
use App\Models\UserModel;

class Siswa extends BaseController
{
    public function index()
    {
        $siswaModel = new SiswaModel();
        $kelasModel = new KelasModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $filterKelas = (int) ($this->request->getGet('id_kelas') ?? 0);
        $requestedTahunAjaran = $this->request->getGet('id_tahun_ajaran');
        $activeTahunAjaran = $tahunAjaranModel->where('aktif', 'aktif')->orderBy('id_tahun_ajaran', 'DESC')->first();
        $filterTahunAjaran = $requestedTahunAjaran !== null && $requestedTahunAjaran !== ''
            ? (int) $requestedTahunAjaran
            : (int) ($activeTahunAjaran['id_tahun_ajaran'] ?? 0);

        $builder = $siswaModel->select('siswa.*, kelas.nama_kelas, tahun_ajaran.tahun_ajaran, tahun_ajaran.semester')
            ->join('kelas', 'kelas.id_kelas = siswa.id_kelas', 'left')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = siswa.id_tahun_ajaran', 'left')
            ->orderBy('kelas.tingkat', 'ASC')
            ->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC');

        if ($filterTahunAjaran > 0) {
            $builder->where('siswa.id_tahun_ajaran', $filterTahunAjaran);
        }

        if ($filterKelas > 0) {
            $builder->where('siswa.id_kelas', $filterKelas);
        }

        $siswa = $builder->findAll();

        $data = [
            'title' => 'Data Siswa',
            'siswa' => $siswa,
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'tahun_ajaran' => $tahunAjaranModel->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'ASC')->findAll(),
            'filter_kelas' => $filterKelas,
            'filter_ta' => $filterTahunAjaran,
            'active_tahun_ajaran' => $activeTahunAjaran,
            'jumlah_siswa_tampil' => count($siswa),
        ];
        return view('admin/siswa/index', $data);
    }

    public function update($id)
    {
        $siswaModel = new SiswaModel();

        $siswa = $siswaModel->find($id);
        if (!$siswa) {
            return redirect()->to(base_url('admin/siswa'))->with('error', 'Data siswa tidak ditemukan.');
        }

        if (
            !$this->validate([
                'nis' => "required|is_unique[siswa.nis,id_siswa,{$id}]",
                'nama_siswa' => 'required',
                'id_kelas' => 'required',
                'id_tahun_ajaran' => 'required|integer',
            ])
        ) {
            return redirect()->back()->with('error', 'Gagal update. NIS mungkin sudah digunakan atau form tidak lengkap.')->withInput();
        }

        $siswaModel->update($id, [
            'nis' => $this->request->getPost('nis'),
            'nisn' => $this->request->getPost('nisn'),
            'nama_siswa' => $this->request->getPost('nama_siswa'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'alamat' => $this->request->getPost('alamat'),
            'id_kelas' => $this->request->getPost('id_kelas'),
            'id_tahun_ajaran' => $this->request->getPost('id_tahun_ajaran'),
            'nama_ayah' => $this->request->getPost('nama_ayah'),
            'nama_ibu' => $this->request->getPost('nama_ibu'),
            'no_telp_ortu' => $this->request->getPost('no_telp_ortu'),
            'status' => $this->request->getPost('status') ?? 'aktif',
        ]);

        return redirect()->to($this->studentRedirectUrl((int) $this->request->getPost('id_kelas'), (int) $this->request->getPost('id_tahun_ajaran')))->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function delete($id)
    {
        $siswaModel = new SiswaModel();
        $userModel = new UserModel();
        $db = \Config\Database::connect();

        $siswa = $siswaModel->find($id);
        if (!$siswa) {
            return redirect()->to(base_url('admin/siswa'))->with('error', 'Data siswa tidak ditemukan.');
        }

        $db->transStart();

        $id_user_ortu = isset($siswa['id_user_ortu']) ? (int) $siswa['id_user_ortu'] : 0;
        if ($id_user_ortu > 0) {
            $otherLinks = $siswaModel->where('id_user_ortu', $id_user_ortu)
                ->where('id_siswa !=', $id)
                ->countAllResults();

            if ($otherLinks === 0) {
                $userModel->delete($id_user_ortu);
            }
        }

        $siswaModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->to(base_url('admin/siswa'))->with('error', 'Gagal menghapus data siswa.');
        }

        return redirect()->to($this->studentRedirectUrl((int) ($siswa['id_kelas'] ?? 0), (int) ($siswa['id_tahun_ajaran'] ?? 0)))->with('success', 'Data siswa & akun orang tua berhasil dihapus.');
    }

    public function store()
    {
        $siswaModel = new SiswaModel();
        $userModel = new UserModel();
        $db = \Config\Database::connect();

        $db->transStart();

        try {
            // Validate basic inputs
            if (
                !$this->validate([
                    'nis' => 'required|is_unique[siswa.nis]',
                    'nama_siswa' => 'required',
                    'id_kelas' => 'required',
                    'id_tahun_ajaran' => 'required|integer',
                ])
            ) {
                return redirect()->back()->with('error', 'Gagal menyimpan. NIS mungkin sudah terdaftar atau form tidak lengkap.')->withInput();
            }

            $nis = $this->request->getPost('nis');

            // Save Siswa — default password is bcrypt(NIS)
            $siswaData = [
                'nis' => $nis,
                'nisn' => $this->request->getPost('nisn'),
                'password' => password_hash($nis, PASSWORD_DEFAULT),
                'nama_siswa' => $this->request->getPost('nama_siswa'),
                'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
                'tempat_lahir' => $this->request->getPost('tempat_lahir'),
                'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
                'alamat' => $this->request->getPost('alamat'),
                'id_kelas' => $this->request->getPost('id_kelas'),
                'id_tahun_ajaran' => $this->request->getPost('id_tahun_ajaran'),
                'id_user_ortu' => null,
                'nama_ayah' => $this->request->getPost('nama_ayah'),
                'nama_ibu' => $this->request->getPost('nama_ibu'),
                'no_telp_ortu' => $this->request->getPost('no_telp_ortu'),
                'status' => 'aktif'
            ];

            $siswaModel->insert($siswaData);
            $id_siswa = $siswaModel->getInsertID();

            // Auto-generate Orang Tua (Parent) logic based on NIS
            $parentUsername = 'ortu_' . $nis;
            $parentPassword = password_hash($nis, PASSWORD_DEFAULT);

            // Check if parent user already exists (might happen if sibling)
            $existingParent = $userModel->where('username', $parentUsername)->first();

            $id_user_ortu = null;

            if ($existingParent) {
                $id_user_ortu = $existingParent['id_user'];
            } else {
                $waliName = !empty($siswaData['nama_ayah']) ? $siswaData['nama_ayah'] : (!empty($siswaData['nama_ibu']) ? $siswaData['nama_ibu'] : 'Wali Siswa');
                $userModel->insert([
                    'username' => $parentUsername,
                    'password' => $parentPassword,
                    'nama_lengkap' => $waliName,
                    'no_telp' => $siswaData['no_telp_ortu'],
                    'level' => 'orang_tua',
                    'status' => 'aktif'
                ]);
                $id_user_ortu = $userModel->getInsertID();
            }

            $siswaModel->update($id_siswa, [
                'id_user_ortu' => $id_user_ortu,
            ]);

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                log_message('error', 'Transaction failed in Admin\Siswa::store()');
                return redirect()->back()->with('error', 'Gagal menyimpan ke database. Silakan coba lagi.');
            }

            return redirect()->to($this->studentRedirectUrl((int) $siswaData['id_kelas'], (int) $siswaData['id_tahun_ajaran']))->with('success', 'Data Siswa & Akun Orang Tua berhasil dibuat. Password login NIS: ' . $nis);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception in Admin\Siswa::store(): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Reset student login password (NIS-based login).
     * POST admin/siswa/reset-password/{id}
     */
    public function resetPassword($id)
    {
        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->find($id);

        if (!$siswa) {
            return redirect()->to(base_url('admin/siswa'))->with('error', 'Data siswa tidak ditemukan.');
        }

        $newPassword = $this->request->getPost('new_password');

        // If no password provided, reset to NIS (default)
        if (empty($newPassword)) {
            $newPassword = $siswa['nis'];
        }

        $siswaModel->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        $displayPass = ($newPassword === $siswa['nis']) ? 'NIS (' . $siswa['nis'] . ')' : '(password baru)';
        return redirect()->to($this->studentRedirectUrl((int) ($siswa['id_kelas'] ?? 0), (int) ($siswa['id_tahun_ajaran'] ?? 0)))->with('success', 'Password siswa ' . $siswa['nama_siswa'] . ' berhasil direset ke ' . $displayPass . '.');
    }

    private function studentRedirectUrl(int $idKelas = 0, int $idTahunAjaran = 0): string
    {
        $params = [];

        if ($idTahunAjaran > 0) {
            $params['id_tahun_ajaran'] = $idTahunAjaran;
        }

        if ($idKelas > 0) {
            $params['id_kelas'] = $idKelas;
        }

        return base_url('admin/siswa' . (!empty($params) ? '?' . http_build_query($params) : ''));
    }
}
