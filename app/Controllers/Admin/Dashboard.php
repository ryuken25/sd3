<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\UserModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;
use App\Models\RemedialModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $siswaModel       = new SiswaModel();
        $userModel        = new UserModel();
        $kelasModel       = new KelasModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $remedialModel    = new RemedialModel();

        $tahunAjaranAktif = $tahunAjaranModel->where('aktif', 'aktif')->first();

        $totalSiswa   = $siswaModel->where('status', 'aktif')->countAllResults();
        $totalGuru    = $userModel->where('level', 'guru')->where('status', 'aktif')->countAllResults();
        $totalKelas   = $kelasModel->countAllResults();

        $totalRemedial = 0;
        if ($tahunAjaranAktif) {
            // Pasca merge: status_remedial inline di tabel `nilai`.
            $totalRemedial = $remedialModel
                ->where('id_tahun_ajaran', $tahunAjaranAktif['id_tahun_ajaran'])
                ->where('status_remedial', 'Belum')
                ->countAllResults();
        }

        $data = [
            'title'              => 'Dashboard Admin',
            'total_siswa'        => $totalSiswa,
            'total_guru'         => $totalGuru,
            'total_kelas'        => $totalKelas,
            'total_remedial'     => $totalRemedial,
            'tahun_ajaran_aktif' => $tahunAjaranAktif
        ];

        return view('admin/dashboard/index', $data);
    }
}
