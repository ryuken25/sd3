<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\NilaiAkhirModel;
use App\Models\RemedialModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        $id_guru = $session->get('id_user');

        // Get statistics for the dashboard
        $nilaiAkhirModel = new NilaiAkhirModel();
        $remedialModel = new RemedialModel();

        // Get active tahun_ajaran
        $db = \Config\Database::connect();
        $tahunAjaranAktif = $db->table('tahun_ajaran')
            ->where('aktif', 'aktif')
            ->get()
            ->getRowArray();

        $data = [
            'title' => 'Dashboard Guru',
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
            'total_nilai_input' => 0,
            'total_remedial' => 0
        ];

        if ($tahunAjaranAktif) {
            // Count total grades entered (you can expand this based on guru's classes)
            $data['total_nilai_input'] = $nilaiAkhirModel
                ->where('id_tahun_ajaran', $tahunAjaranAktif['id_tahun_ajaran'])
                ->countAllResults();

            // Count remedial cases
            $data['total_remedial'] = $remedialModel
                ->join('nilai_akhir', 'nilai_akhir.id_nilai_akhir = remedial.id_nilai_akhir')
                ->where('nilai_akhir.id_tahun_ajaran', $tahunAjaranAktif['id_tahun_ajaran'])
                ->where('remedial.status_remedial', 'Belum')
                ->countAllResults();
        }

        return view('guru/dashboard/index', $data);
    }
}

