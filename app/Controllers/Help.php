<?php

namespace App\Controllers;

/**
 * Halaman bantuan/panduan penggunaan dalam aplikasi (Pek 2 — Megaprompt v2).
 * Dapat diakses semua role yang sudah login.
 */
class Help extends BaseController
{
    public function panduanRapor()
    {
        // Sidebar mengikuti role yang sedang login supaya navigasi tetap konsisten.
        $role = (string) session()->get('role');

        return view('help/panduan_rapor', [
            'title' => 'Panduan Penggunaan Rapor',
            'role'  => $role,
        ]);
    }
}
