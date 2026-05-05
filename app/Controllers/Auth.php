<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SiswaModel;

class Auth extends BaseController
{
    public function index()
    {
        // Redirect if already logged in
        if (session()->get('logged_in')) {
            return $this->redirectByRole(session()->get('role'));
        }

        return view('auth/login');
    }

    public function process()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        // Validate inputs
        if (
            !$this->validate([
                'username' => 'required|min_length[3]',
                'password' => 'required|min_length[3]'
            ])
        ) {
            return redirect()->back()->with('error', 'Username dan password harus diisi dengan benar');
        }

        // --- Path 1: NIS-based login for Orang Tua ---
        // If the input looks like a NIS (numeric), try siswa table first
        if (ctype_digit($username)) {
            $siswaModel = new SiswaModel();
            $siswa = $siswaModel->where('nis', $username)->where('status', 'aktif')->first();

            if ($siswa && !empty($siswa['password'])) {
                [$passwordValid, $needsUpgrade] = $this->verifyPasswordAgainstHash($password, $siswa['password']);

                if ($passwordValid) {
                    if ($needsUpgrade) {
                        $siswaModel->update($siswa['id_siswa'], [
                            'password' => password_hash($password, PASSWORD_DEFAULT),
                        ]);
                    }

                    if (!empty($siswa['id_user_ortu'])) {
                        $userModel = new UserModel();
                        $user = $userModel->find((int) $siswa['id_user_ortu']);

                        if ($user && $user['status'] === 'aktif') {
                            $linkedSiswaRows = $siswaModel->select('id_siswa')
                                ->where('id_user_ortu', $user['id_user'])
                                ->where('status', 'aktif')
                                ->findAll();
                            $linkedSiswaIds = array_map(static fn(array $row): int => (int) $row['id_siswa'], $linkedSiswaRows);

                            session()->set([
                                'id_user' => $user['id_user'],
                                'username' => $user['username'],
                                'nama_lengkap' => $user['nama_lengkap'] ?: ('Orang Tua ' . $siswa['nama_siswa']),
                                'role' => 'orang_tua',
                                'id_siswa' => $siswa['id_siswa'],
                                'id_siswa_aktif' => $siswa['id_siswa'],
                                'linked_siswa_ids' => $linkedSiswaIds,
                                'logged_in' => true
                            ]);

                            return redirect()->to(base_url('orangtua/dashboard'));
                        }
                    }
                }
            }
        }

        // --- Path 2: Standard username/password login (admin, guru, orang_tua legacy) ---
        $users = new UserModel();
        $user = $users->where('username', $username)->first();

        if ($user) {
            [$passwordValid, $needsUpgrade] = $this->verifyPasswordAgainstHash($password, $user['password']);

            if ($passwordValid && $needsUpgrade) {
                $users->update($user['id_user'], [
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ]);
            }

            if ($passwordValid) {
                if ($user['status'] == 'nonaktif') {
                    return redirect()->back()->with('error', 'Akun tidak aktif. Hubungi admin.');
                }

                $sessionData = [
                    'id_user' => $user['id_user'],
                    'username' => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'role' => $user['level'],
                    'logged_in' => true
                ];

                if ($user['level'] === 'orang_tua') {
                    $siswaModel = new SiswaModel();
                    $linkedSiswaRows = $siswaModel->select('id_siswa')
                        ->where('id_user_ortu', $user['id_user'])
                        ->where('status', 'aktif')
                        ->findAll();
                    $linkedSiswaIds = array_map(static fn(array $row): int => (int) $row['id_siswa'], $linkedSiswaRows);

                    if (empty($linkedSiswaIds)) {
                        return redirect()->back()->with('error', 'Akun orang tua belum terhubung dengan data siswa.');
                    }

                    $sessionData['linked_siswa_ids'] = $linkedSiswaIds;
                    $sessionData['id_siswa'] = (int) $linkedSiswaIds[0];
                    $sessionData['id_siswa_aktif'] = (int) $linkedSiswaIds[0];
                }

                session()->set($sessionData);

                return $this->redirectByRole($user['level']);
            }
        }

        return redirect()->back()->with('error', 'Username/NIS atau Password salah');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }

    private function redirectByRole($role)
    {
        if ($role === 'admin') {
            return redirect()->to(base_url('admin/dashboard'));
        } elseif ($role === 'guru') {
            return redirect()->to(base_url('guru/dashboard'));
        } elseif ($role === 'orang_tua') {
            return redirect()->to(base_url('orangtua/dashboard'));
        }
        return redirect()->to(base_url('/'));
    }

    private function verifyPasswordAgainstHash(string $plainPassword, ?string $storedHash): array
    {
        if (empty($storedHash)) {
            return [false, false];
        }

        $isOldHash = strlen($storedHash) === 32 && ctype_xdigit($storedHash);
        if ($isOldHash) {
            return [md5($plainPassword) === $storedHash, true];
        }

        return [password_verify($plainPassword, $storedHash), false];
    }
}
