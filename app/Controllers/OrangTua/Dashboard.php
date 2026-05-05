<?php

namespace App\Controllers\OrangTua;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\NilaiAkhirModel;
use App\Models\RemedialModel;
use App\Models\MataPelajaranModel;
use App\Models\TahunAjaranModel;
use App\Models\RaporModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        $id_user = $session->get('id_user');

        $siswaModel = new SiswaModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $siswaData = $siswaModel->findByParentUser((int) $id_user);

        // Get active academic year
        $tahunAjaranAktif = $tahunAjaranModel->where('aktif', 'aktif')->first();

        $nilaiAkhirModel = new NilaiAkhirModel();
        $raporModel = new RaporModel();
        $studentOverview = [];

        foreach ($siswaData as $siswa) {
            $ringkasan = [
                'total_mapel' => 0,
                'jumlah_tuntas' => 0,
                'jumlah_remedial' => 0,
                'rapor_tersedia' => false,
            ];

            if ($tahunAjaranAktif) {
                $rapor = $raporModel->getFinalizedReport((int) $siswa['id_siswa'], (int) $tahunAjaranAktif['id_tahun_ajaran']);
                $ringkasan['rapor_tersedia'] = !empty($rapor);

                if ($ringkasan['rapor_tersedia'] && ($tahunAjaranAktif['status_pengisian'] ?? null) === 'Kunci') {
                    $nilaiAkhir = $nilaiAkhirModel->select('nilai_akhir.*')
                        ->join('mapel_kelas', 'mapel_kelas.id_mapel = nilai_akhir.id_mapel AND mapel_kelas.id_kelas = ' . (int) $siswa['id_kelas'])
                        ->where('id_siswa', $siswa['id_siswa'])
                        ->where('id_tahun_ajaran', $tahunAjaranAktif['id_tahun_ajaran'])
                        ->findAll();

                    $ringkasan['total_mapel'] = count($nilaiAkhir);
                    foreach ($nilaiAkhir as $nilai) {
                        if (($nilai['status_kelulusan'] ?? null) === 'Tuntas') {
                            $ringkasan['jumlah_tuntas']++;
                        }

                        if (($nilai['status_kelulusan'] ?? null) === 'Remedial') {
                            $ringkasan['jumlah_remedial']++;
                        }
                    }
                }
            }

            $studentOverview[$siswa['id_siswa']] = $ringkasan;
        }

        $data = [
            'title' => 'Dashboard Orang Tua',
            'siswa_data' => $siswaData,
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
            'student_overview' => $studentOverview,
        ];

        return view('orangtua/dashboard/index', $data);
    }

    // View detailed grades for a student (card-based, color-coded)
    public function viewGrades($id_siswa)
    {
        $session = session();
        $id_user = $session->get('id_user');

        $siswaModel = new SiswaModel();
        $access = $siswaModel->isOwnedByParent((int) $id_siswa, (int) $id_user);

        if (!$access) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', 'Akses ditolak');
        }

        $nilaiAkhirModel = new NilaiAkhirModel();
        $remedialModel = new RemedialModel();
        $mapelModel = new MataPelajaranModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $kkmModel = new \App\Models\KkmModel();
        $raporModel = new RaporModel();

        $siswa = $siswaModel->find($id_siswa);
        $tahunAjaranAktif = $tahunAjaranModel->where('aktif', 'aktif')->first();

        // Validate academic year exists
        if (!$tahunAjaranAktif) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif. Hubungi administrator.');
        }

        // Validate student exists
        if (!$siswa) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', 'Data siswa tidak ditemukan');
        }

        $nilaiData = [];
        $rapor = $raporModel->where([
            'id_siswa' => $id_siswa,
            'id_tahun_ajaran' => $tahunAjaranAktif['id_tahun_ajaran'],
            'is_finalized' => 1,
        ])->first();

        $raporTersedia = ($tahunAjaranAktif['status_pengisian'] ?? null) === 'Kunci'
            && !empty($rapor);

        if ($raporTersedia) {
            // Get subjects assigned to student's class
            $mapel = $mapelModel->getByClass((int) $siswa['id_kelas']);

            // FIX BUG-06: Batch fetch all data before loop to avoid N+1 queries
            $allNilaiAkhir = $nilaiAkhirModel->where('id_siswa', $id_siswa)
                ->where('id_tahun_ajaran', $tahunAjaranAktif['id_tahun_ajaran'])
                ->findAll();
            $nilaiAkhirByMapel = array_column($allNilaiAkhir, null, 'id_mapel');

            $allKkm = $kkmModel->where('id_kelas', $siswa['id_kelas'])
                ->where('id_tahun_ajaran', $tahunAjaranAktif['id_tahun_ajaran'])
                ->findAll();
            $kkmByMapel = array_column($allKkm, null, 'id_mapel');

            $nilaiAkhirIds = array_column($allNilaiAkhir, 'id_nilai_akhir');
            $allRemedial = !empty($nilaiAkhirIds)
                ? $remedialModel->whereIn('id_nilai_akhir', $nilaiAkhirIds)->findAll()
                : [];
            $remedialByNilaiAkhir = array_column($allRemedial, null, 'id_nilai_akhir');

            foreach ($mapel as $m) {
                $nilaiAkhir = $nilaiAkhirByMapel[$m['id_mapel']] ?? null;
                $kkm = $kkmByMapel[$m['id_mapel']] ?? null;
                $remedial = $nilaiAkhir ? ($remedialByNilaiAkhir[$nilaiAkhir['id_nilai_akhir']] ?? null) : null;

                $nilaiData[] = [
                    'mapel' => $m,
                    'nilai_akhir' => $nilaiAkhir,
                    'kkm' => $kkm,
                    'remedial' => $remedial,
                    'status_color' => !$nilaiAkhir
                        ? 'secondary'
                        : ($nilaiAkhir['status_kelulusan'] === 'Tuntas' ? 'success' : 'danger')
                ];
            }
        }

        $data = [
            'title' => 'Nilai Siswa - ' . $siswa['nama_siswa'],
            'siswa' => $siswa,
            'tahun_ajaran' => $tahunAjaranAktif,
            'nilai_data' => $nilaiData,
            'rapor_tersedia' => $raporTersedia,
        ];

        return view('orangtua/grades/view', $data);
    }

    // View e-rapor (if semester is locked and rapor is finalized)
    public function viewRapor($id_siswa, $id_tahun_ajaran)
    {
        $session = session();
        $id_user = $session->get('id_user');

        $siswaModel = new SiswaModel();
        $access = $siswaModel->isOwnedByParent((int) $id_siswa, (int) $id_user);

        if (!$access) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', 'Akses ditolak');
        }

        $raporModel = new RaporModel();
        $nilaiAkhirModel = new NilaiAkhirModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $siswa = $siswaModel->find($id_siswa);
        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        // Validate data exists
        if (!$siswa) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', 'Data siswa tidak ditemukan');
        }

        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Data tahun ajaran tidak ditemukan');
        }

        // Check if semester is locked
        if ($tahunAjaran['status_pengisian'] !== 'Kunci') {
            // FIX BUG-04: Redirect to dashboard with clear message instead of back()
            return redirect()->to(base_url('orangtua/dashboard'))
                ->with('info', 'Rapor belum tersedia. Semester masih dalam proses pengisian nilai.');
        }

        $rapor = $raporModel->getFinalizedReport((int) $id_siswa, (int) $id_tahun_ajaran);

        if (!$rapor) {
            return redirect()->to(base_url('orangtua/grades/' . $id_siswa))
                ->with('info', 'Rapor belum difinalisasi oleh admin/wali kelas.');
        }

        // Get all final grades
        $nilaiAkhir = $nilaiAkhirModel->select('nilai_akhir.*, mata_pelajaran.nama_mapel, mata_pelajaran.kelompok, kkm.nilai_kkm')
            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = nilai_akhir.id_mapel')
            ->join('mapel_kelas', 'mapel_kelas.id_mapel = nilai_akhir.id_mapel AND mapel_kelas.id_kelas = ' . (int) $siswa['id_kelas'])
            ->join('kkm', 'kkm.id_mapel = nilai_akhir.id_mapel AND kkm.id_kelas = ' . (int) $siswa['id_kelas'] . ' AND kkm.id_tahun_ajaran = ' . (int) $id_tahun_ajaran, 'left')
            ->where('nilai_akhir.id_siswa', $id_siswa)
            ->where('nilai_akhir.id_tahun_ajaran', $id_tahun_ajaran)
            ->orderBy('mata_pelajaran.kelompok', 'ASC')
            ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
            ->findAll();

        $data = [
            'title' => 'E-Rapor - ' . $siswa['nama_siswa'],
            'siswa' => $siswa,
            'tahun_ajaran' => $tahunAjaran,
            'rapor' => $rapor,
            'nilai_akhir' => $nilaiAkhir
        ];

        return view('orangtua/rapor/view', $data);
    }
}
