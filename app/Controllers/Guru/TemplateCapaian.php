<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\MasterCapaianPembelajaranModel;
use App\Models\MataPelajaranModel;

/**
 * Kelola Template Capaian Pembelajaran berbasis BAND PREDIKAT.
 *
 * Untuk tiap (Mapel + Fase + Semester), guru menyiapkan 4 narasi template:
 * satu per band A/B/C/D. Saat input rapor, narasi band sesuai nilai_huruf siswa
 * otomatis di-prefill ke kotak teks (lihat Guru\CapaianKompetensi).
 *
 * Data referensi lintas periode → TIDAK dikunci guardGradeWriteAccess.
 */
class TemplateCapaian extends BaseController
{
    private const BANDS = ['A', 'B', 'C', 'D'];

    private function filterQuery(int $idMapel, string $fase, string $semester): string
    {
        return '?id_mapel=' . $idMapel . '&fase=' . urlencode($fase) . '&semester=' . urlencode($semester);
    }

    public function index()
    {
        $mapelModel = new MataPelajaranModel();
        $cpModel    = new MasterCapaianPembelajaranModel();

        $idMapel  = (int) ($this->request->getGet('id_mapel') ?? 0);
        $fase     = (string) ($this->request->getGet('fase') ?? '');
        $semester = (string) ($this->request->getGet('semester') ?? '');

        $bandMap = null;
        if ($idMapel > 0 && in_array($fase, ['A', 'B', 'C'], true) && in_array($semester, ['Ganjil', 'Genap'], true)) {
            $bandMap = $cpModel->getBandMap($idMapel, $fase, $semester);
        }

        return view('guru/capaian/template', [
            'title'    => 'Kelola Template Capaian',
            'mapel'    => $mapelModel->orderBy('nama_mapel', 'ASC')->findAll(),
            'band_map' => $bandMap,
            'f_mapel'  => $idMapel,
            'f_fase'   => $fase,
            'f_sem'    => $semester,
        ]);
    }

    /**
     * Upsert 4 band sekaligus. Band yang dikosongkan → baris dihapus.
     * POST: id_mapel, fase, semester, band[A], band[B], band[C], band[D].
     */
    public function saveBands()
    {
        $cpModel = new MasterCapaianPembelajaranModel();

        $idMapel  = (int) $this->request->getPost('id_mapel');
        $fase     = (string) $this->request->getPost('fase');
        $semester = (string) $this->request->getPost('semester');
        $bands    = $this->request->getPost('band') ?? [];

        $back = base_url('guru/template-capaian' . $this->filterQuery($idMapel, $fase, $semester));

        if ($idMapel <= 0 || !in_array($fase, ['A', 'B', 'C'], true) || !in_array($semester, ['Ganjil', 'Genap'], true)) {
            return redirect()->to(base_url('guru/template-capaian'))->with('error', 'Filter mapel/fase/semester tidak valid.');
        }
        if (!\is_array($bands)) {
            return redirect()->to($back)->with('error', 'Data band tidak valid.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach (self::BANDS as $p) {
            $teks = trim((string) ($bands[$p] ?? ''));
            $existing = $cpModel->findBand($idMapel, $fase, $semester, $p);

            if ($teks === '') {
                // Band dikosongkan → hapus baris band ini bila ada.
                if ($existing) {
                    $cpModel->delete((int) $existing['id_master_cp']);
                }
                continue;
            }

            if ($existing) {
                $cpModel->update((int) $existing['id_master_cp'], ['deskripsi' => $teks, 'aktif' => 1]);
            } else {
                $cpModel->insert([
                    'id_mapel'  => $idMapel,
                    'fase'      => $fase,
                    'semester'  => $semester,
                    'predikat'  => $p,
                    'deskripsi' => $teks,
                    'aktif'     => 1,
                ]);
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->to($back)->with('error', 'Gagal menyimpan template. Coba lagi.');
        }

        return redirect()->to($back)->with('success', 'Template capaian (4 band) berhasil disimpan.');
    }
}
