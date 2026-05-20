<?php

namespace App\Controllers\OrangTua;

use App\Controllers\BaseController;
use App\Libraries\RaporDataLoader;
use App\Models\SiswaModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * PDF rapor generator (Dompdf) — match Rapor_Kelas3.pdf / Rapor_Kelas6.pdf.
 *
 * Render HTML via shared partial rapor/_full_layout (lewat wrapper
 * rapor/cetak.php) dengan showBadgeOnline=false — layout identik dengan
 * e-rapor online & detail admin, hanya badge "Catatan dari guru" tidak ikut.
 */
class Rapor extends BaseController
{
    public function downloadPDF($idSiswa, $idTahunAjaran)
    {
        $idSiswa = (int) $idSiswa;
        $idTa    = (int) $idTahunAjaran;
        $idUser  = (int) session()->get('id_user');

        $siswaModel = new SiswaModel();
        if (!$siswaModel->isOwnedByParent($idSiswa, $idUser)) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', 'Akses ditolak.');
        }

        $data = (new RaporDataLoader())->load($idSiswa, $idTa);
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        // Pek 9: PDF hanya tersedia kalau rapor sudah difinalisasi.
        if (empty($data['rapor']) || (int) ($data['rapor']['is_finalized'] ?? 0) !== 1) {
            return redirect()->to(base_url('orangtua/dashboard'))
                ->with('error', 'Rapor belum difinalisasi. Tunggu wali kelas/admin memfinalkan.');
        }

        $html = view('rapor/cetak', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Times-Roman');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = sprintf(
            'Rapor_%s_%s_%s.pdf',
            preg_replace('/[^A-Za-z0-9]+/', '_', (string) $data['siswa']['nama_siswa']),
            str_replace('/', '-', (string) $data['tahun_ajaran']['tahun_ajaran']),
            (string) $data['tahun_ajaran']['semester']
        );

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }
}
