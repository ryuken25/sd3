<?php

namespace App\Controllers\OrangTua;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\NilaiAkhirModel;
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

        // Resolve TA aktif lebih dulu supaya list siswa dipersempit ke TA itu —
        // tanpa filter, satu anak yang ada di banyak TA akan muncul sebagai banyak card.
        $tahunAjaranAktif = $tahunAjaranModel->where('aktif', 'aktif')->first();
        $idTaAktif = $tahunAjaranAktif ? (int) $tahunAjaranAktif['id_tahun_ajaran'] : null;

        $siswaData = $siswaModel->findByParentUser((int) $id_user, $idTaAktif);

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
                    // Pasca merge: tabel `nilai` (bukan `nilai_akhir`).
                    $nilaiAkhir = $nilaiAkhirModel->select('nilai.*')
                        ->join('mapel_kelas', 'mapel_kelas.id_mapel = nilai.id_mapel AND mapel_kelas.id_kelas = ' . (int) $siswa['id_kelas'])
                        ->where('nilai.id_siswa', $siswa['id_siswa'])
                        ->where('nilai.id_tahun_ajaran', $tahunAjaranAktif['id_tahun_ajaran'])
                        ->where('nilai.nilai_akhir IS NOT NULL', null, false)
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

            // Pasca merge: remedial inline di baris `nilai`. Tidak perlu query terpisah —
            // baca status_remedial/tindak_lanjut langsung dari $nilaiAkhir.
            foreach ($mapel as $m) {
                $nilaiAkhir = $nilaiAkhirByMapel[$m['id_mapel']] ?? null;
                $kkm = $kkmByMapel[$m['id_mapel']] ?? null;
                $remedial = ($nilaiAkhir && ($nilaiAkhir['status_remedial'] ?? null) !== null)
                    ? [
                        'tindak_lanjut'   => $nilaiAkhir['tindak_lanjut'] ?? null,
                        'status_remedial' => $nilaiAkhir['status_remedial'] ?? null,
                    ]
                    : null;

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

    // View e-rapor online — layout match PDF referensi (Pek 9).
    public function viewRapor($id_siswa, $id_tahun_ajaran)
    {
        $session = session();
        $id_user = $session->get('id_user');

        $siswaModel = new SiswaModel();
        if (!$siswaModel->isOwnedByParent((int) $id_siswa, (int) $id_user)) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', 'Akses ditolak');
        }

        // Satu sumber data dengan PDF cetak (RaporDataLoader) supaya layout konsisten.
        $data = (new \App\Libraries\RaporDataLoader())->load((int) $id_siswa, (int) $id_tahun_ajaran);
        if (isset($data['error'])) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', $data['error']);
        }

        // Gate visibilitas e-rapor = rapor.is_finalized (Pek 9). status_pengisian
        // hanya mengunci INPUT nilai guru, bukan visibilitas rapor — jadi rapor
        // yang sudah difinalisasi tetap bisa dilihat walau semester masih 'Buka'.
        if (empty($data['rapor']) || (int) ($data['rapor']['is_finalized'] ?? 0) !== 1) {
            return redirect()->to(base_url('orangtua/grades/' . $id_siswa))
                ->with('info', 'Rapor belum difinalisasi oleh admin/wali kelas.');
        }

        $data['title'] = 'E-Rapor - ' . $data['siswa']['nama_siswa'];
        return view('orangtua/rapor/view', $data);
    }
}
