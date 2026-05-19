<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\KokurikulerTemaModel;
use App\Models\MasterDimensiPancasilaModel;
use App\Models\MasterEkstrakurikulerModel;
use App\Models\MasterTemplateCatatanModel;
use App\Models\RaporModel;
use App\Models\SiswaEkstrakurikulerModel;
use App\Models\SiswaKokurikulerDimensiModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;

/**
 * Halaman wali kelas untuk mengisi 4 bagian rapor:
 *   - Catatan Wali Kelas        (Pek 4)  → rapor.catatan_wali_kelas
 *   - Ekstrakurikuler           (Pek 5.1) → siswa_ekstrakurikuler
 *   - Kokurikuler P5            (Pek 5.2) → siswa_kokurikuler_dimensi
 *   - Ketidakhadiran            (Pek 8)  → rapor.sakit/izin/alpa
 *
 * Hanya guru yang menjadi wali_kelas di tabel kelas yang bisa akses
 * siswa-siswa kelas tsb. Filter selalu pakai TA aktif.
 */
class WaliKelas extends BaseController
{
    /**
     * Landing: list siswa di kelas yang diampu guru sebagai wali_kelas.
     */
    public function index()
    {
        $idUser = (int) session()->get('id_user');
        $taModel = new TahunAjaranModel();
        $taAktif = $taModel->where('aktif', 'aktif')->first();

        if (!$taAktif) {
            return view('guru/wali_kelas/index', [
                'title'   => 'Wali Kelas',
                'ta'      => null,
                'kelas'   => null,
                'siswa'   => [],
                'message' => 'Tidak ada Tahun Ajaran aktif.',
            ]);
        }

        $kelasModel = new KelasModel();
        $kelas = $kelasModel->where('wali_kelas', $idUser)->first();

        $siswa = [];
        if ($kelas) {
            $siswaModel = new SiswaModel();
            $siswa = $siswaModel->where('id_kelas', $kelas['id_kelas'])
                ->where('id_tahun_ajaran', $taAktif['id_tahun_ajaran'])
                ->where('status', 'aktif')
                ->orderBy('nama_siswa', 'ASC')
                ->findAll();
        }

        return view('guru/wali_kelas/index', [
            'title' => 'Wali Kelas',
            'ta'    => $taAktif,
            'kelas' => $kelas,
            'siswa' => $siswa,
        ]);
    }

    /**
     * Form per siswa: 4 section dalam satu halaman.
     */
    public function siswa(int $idSiswa)
    {
        $idUser = (int) session()->get('id_user');
        $siswaModel = new SiswaModel();
        $kelasModel = new KelasModel();
        $taModel = new TahunAjaranModel();
        $raporModel = new RaporModel();
        $masterEkskulModel = new MasterEkstrakurikulerModel();
        $masterDimensiModel = new MasterDimensiPancasilaModel();
        $masterTemplateModel = new MasterTemplateCatatanModel();
        $temaModel = new KokurikulerTemaModel();
        $siswaEkskulModel = new SiswaEkstrakurikulerModel();
        $siswaKokoModel = new SiswaKokurikulerDimensiModel();

        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) {
            return redirect()->to(base_url('guru/wali-kelas'))->with('error', 'Siswa tidak ditemukan.');
        }

        // Guard: hanya wali kelas yang berwenang
        $kelas = $kelasModel->find($siswa['id_kelas']);
        if (!$kelas || (int) $kelas['wali_kelas'] !== $idUser) {
            return redirect()->to(base_url('guru/wali-kelas'))->with('error', 'Anda bukan wali kelas siswa ini.');
        }

        $idTa = (int) $siswa['id_tahun_ajaran'];
        $ta = $taModel->find($idTa);

        // Pastikan rapor row ada untuk siswa+TA ini
        $rapor = $raporModel->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $idTa)->first();
        if (!$rapor) {
            $raporModel->insert([
                'id_siswa'           => $idSiswa,
                'id_tahun_ajaran'    => $idTa,
                'sakit'              => 0,
                'izin'               => 0,
                'alpa'               => 0,
                'catatan_wali_kelas' => null,
                'status_kenaikan'    => 'Naik',
                'is_finalized'       => 0,
            ]);
            $rapor = $raporModel->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $idTa)->first();
        }

        $tema = $temaModel->findForKelasTa((int) $siswa['id_kelas'], $idTa);
        $kokoExisting = $tema ? $siswaKokoModel->findForSiswaTema($idSiswa, (int) $tema['id_tema']) : [];

        return view('guru/wali_kelas/siswa', [
            'title'           => 'Wali Kelas — ' . $siswa['nama_siswa'],
            'siswa'           => $siswa,
            'kelas'           => $kelas,
            'tahun_ajaran'    => $ta,
            'rapor'           => $rapor,
            'master_ekskul'   => $masterEkskulModel->findActive(),
            'master_dimensi'  => $masterDimensiModel->findOrdered(),
            'master_template' => $masterTemplateModel->findActive(),
            'tema'            => $tema,
            'ekskul_siswa'    => $siswaEkskulModel->findForSiswaTa($idSiswa, $idTa),
            'koko_siswa'      => $kokoExisting,
        ]);
    }

    public function saveCatatan()
    {
        $idSiswa = (int) $this->request->getPost('id_siswa');
        $catatan = trim((string) $this->request->getPost('catatan_wali_kelas'));

        if ($idSiswa <= 0) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }
        if (strlen($catatan) < 10) {
            return redirect()->back()->with('error', 'Catatan wali kelas minimal 10 karakter.');
        }

        if ($response = $this->guardOwnership($idSiswa)) {
            return $response;
        }

        $siswaModel = new SiswaModel();
        $raporModel = new RaporModel();
        $siswa = $siswaModel->find($idSiswa);
        $idTa  = (int) $siswa['id_tahun_ajaran'];

        $rapor = $raporModel->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $idTa)->first();
        $raporModel->update($rapor['id_rapor'], ['catatan_wali_kelas' => $catatan]);

        return redirect()->to(base_url('guru/wali-kelas/siswa/' . $idSiswa))
            ->with('success', 'Catatan wali kelas tersimpan.');
    }

    public function saveKetidakhadiran()
    {
        $idSiswa = (int) $this->request->getPost('id_siswa');
        $sakit = max(0, (int) $this->request->getPost('sakit'));
        $izin  = max(0, (int) $this->request->getPost('izin'));
        $alpa  = max(0, (int) $this->request->getPost('alpa'));

        if ($idSiswa <= 0) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }
        if ($response = $this->guardOwnership($idSiswa)) {
            return $response;
        }

        $siswaModel = new SiswaModel();
        $raporModel = new RaporModel();
        $siswa = $siswaModel->find($idSiswa);
        $idTa  = (int) $siswa['id_tahun_ajaran'];

        $rapor = $raporModel->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $idTa)->first();
        $raporModel->update($rapor['id_rapor'], [
            'sakit' => $sakit,
            'izin'  => $izin,
            'alpa'  => $alpa,
        ]);

        return redirect()->to(base_url('guru/wali-kelas/siswa/' . $idSiswa))
            ->with('success', 'Data ketidakhadiran tersimpan.');
    }

    public function saveEkskul()
    {
        $idSiswa = (int) $this->request->getPost('id_siswa');
        $ekskul  = $this->request->getPost('ekskul') ?? [];

        if ($idSiswa <= 0 || !\is_array($ekskul)) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }
        if ($response = $this->guardOwnership($idSiswa)) {
            return $response;
        }

        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->find($idSiswa);
        $idTa  = (int) $siswa['id_tahun_ajaran'];

        $siswaEkskulModel = new SiswaEkstrakurikulerModel();

        // Hapus assignment lama untuk (siswa, TA), insert ulang dari form
        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('siswa_ekstrakurikuler')
            ->where('id_siswa', $idSiswa)
            ->where('id_tahun_ajaran', $idTa)
            ->delete();

        foreach ($ekskul as $idEkskul => $row) {
            $idEkskul = (int) $idEkskul;
            $aktif = !empty($row['aktif']);
            if (!$aktif) {
                continue;
            }
            $ket = trim((string) ($row['keterangan'] ?? ''));
            $siswaEkskulModel->insert([
                'id_siswa'        => $idSiswa,
                'id_ekskul'       => $idEkskul,
                'id_tahun_ajaran' => $idTa,
                'keterangan'      => $ket !== '' ? $ket : null,
            ]);
        }

        $db->transComplete();

        return redirect()->to(base_url('guru/wali-kelas/siswa/' . $idSiswa))
            ->with('success', 'Ekstrakurikuler siswa tersimpan.');
    }

    public function saveKokurikuler()
    {
        $idSiswa = (int) $this->request->getPost('id_siswa');
        $idTema  = (int) $this->request->getPost('id_tema');
        $dimensi = $this->request->getPost('dimensi') ?? [];

        if ($idSiswa <= 0 || $idTema <= 0 || !\is_array($dimensi)) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }
        if ($response = $this->guardOwnership($idSiswa)) {
            return $response;
        }

        $kokoModel = new SiswaKokurikulerDimensiModel();
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($dimensi as $idDimensi => $row) {
            $idDimensi = (int) $idDimensi;
            $sub   = trim((string) ($row['subdimensi'] ?? ''));
            $level = (string) ($row['level'] ?? 'berkembang');
            if ($sub === '') {
                // Hapus row kalau guru kosongkan subdimensi
                $kokoModel->where('id_siswa', $idSiswa)
                    ->where('id_tema', $idTema)
                    ->where('id_dimensi', $idDimensi)
                    ->delete();
                continue;
            }

            $existing = $kokoModel->where('id_siswa', $idSiswa)
                ->where('id_tema', $idTema)
                ->where('id_dimensi', $idDimensi)
                ->first();

            $payload = [
                'id_siswa'   => $idSiswa,
                'id_tema'    => $idTema,
                'id_dimensi' => $idDimensi,
                'subdimensi' => $sub,
                'level'      => $level,
            ];

            if ($existing) {
                $kokoModel->update($existing['id_siswa_koko'], $payload);
            } else {
                $kokoModel->insert($payload);
            }
        }

        $db->transComplete();

        return redirect()->to(base_url('guru/wali-kelas/siswa/' . $idSiswa))
            ->with('success', 'Kokurikuler P5 tersimpan.');
    }

    /**
     * Pastikan guru yang login adalah wali kelas siswa ini.
     */
    private function guardOwnership(int $idSiswa)
    {
        $idUser = (int) session()->get('id_user');
        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) {
            return redirect()->to(base_url('guru/wali-kelas'))->with('error', 'Siswa tidak ditemukan.');
        }

        $kelasModel = new KelasModel();
        $kelas = $kelasModel->find($siswa['id_kelas']);
        if (!$kelas || (int) $kelas['wali_kelas'] !== $idUser) {
            return redirect()->to(base_url('guru/wali-kelas'))->with('error', 'Anda bukan wali kelas siswa ini.');
        }

        return null;
    }
}
