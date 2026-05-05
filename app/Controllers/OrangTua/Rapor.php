<?php

namespace App\Controllers\OrangTua;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\RaporModel;
use App\Models\NilaiAkhirModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;
use TCPDF;

class Rapor extends BaseController
{
    /**
     * Generate and download PDF e-rapor
     */
    public function downloadPDF($id_siswa, $id_tahun_ajaran)
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

        $siswa = $siswaModel->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.id_kelas')
            ->where('siswa.id_siswa', $id_siswa)
            ->first();

        $tahunAjaran = $tahunAjaranModel->find($id_tahun_ajaran);

        if (!$tahunAjaran || $tahunAjaran['status_pengisian'] !== 'Kunci') {
            return redirect()->back()->with('error', 'Rapor belum tersedia. Semester masih dalam proses pengisian.');
        }

        // Get rapor data
        $rapor = $raporModel->getFinalizedReport((int) $id_siswa, (int) $id_tahun_ajaran);

        if (!$rapor) {
            return redirect()->back()->with('error', 'Rapor belum difinalisasi oleh admin/wali kelas.');
        }

        // Get all final grades grouped by kelompok
        $builder = $nilaiAkhirModel->select('nilai_akhir.*, mata_pelajaran.nama_mapel, mata_pelajaran.kelompok, kkm.nilai_kkm')
            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = nilai_akhir.id_mapel')
            ->join('mapel_kelas', 'mapel_kelas.id_mapel = nilai_akhir.id_mapel AND mapel_kelas.id_kelas = ' . (int) $siswa['id_kelas'])
            ->join('kkm', 'kkm.id_mapel = nilai_akhir.id_mapel AND kkm.id_kelas = ' . (int) $siswa['id_kelas'] . ' AND kkm.id_tahun_ajaran = ' . (int) $id_tahun_ajaran, 'left')
            ->where('nilai_akhir.id_siswa', $id_siswa)
            ->where('nilai_akhir.id_tahun_ajaran', $id_tahun_ajaran);

        $nilaiAkhir = $builder->orderBy('mata_pelajaran.kelompok', 'ASC')
            ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
            ->findAll();

        // Separate by kelompok
        $kelompokA = [];
        $kelompokB = [];
        foreach ($nilaiAkhir as $nilai) {
            if ($nilai['kelompok'] === 'A') {
                $kelompokA[] = $nilai;
            } else {
                $kelompokB[] = $nilai;
            }
        }

        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('SDN 3 Mekarsari');
        $pdf->SetAuthor('SDN 3 Mekarsari');
        $pdf->SetTitle('Rapor - ' . $siswa['nama_siswa']);
        $pdf->SetSubject('E-Rapor Siswa');

        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Build HTML content
        $html = $this->generateRaporHTML($siswa, $tahunAjaran, $rapor, $kelompokA, $kelompokB);

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF
        $filename = 'Rapor_' . $siswa['nama_siswa'] . '_' . $tahunAjaran['tahun_ajaran'] . '_' . $tahunAjaran['semester'] . '.pdf';
        $pdf->Output($filename, 'D'); // D = Download
    }

    /**
     * Generate HTML content for rapor
     */
    private function generateRaporHTML($siswa, $tahunAjaran, $rapor, $kelompokA, $kelompokB)
    {
        $renderRows = static function (array $rows): string {
            if (empty($rows)) {
                return '<tr><td colspan="6" class="empty-state">Belum ada data nilai untuk kelompok ini.</td></tr>';
            }

            $html = '';
            $no = 1;
            foreach ($rows as $nilai) {
                $statusColor = ($nilai['status_kelulusan'] ?? '') === 'Tuntas' ? '#137b4a' : '#b44133';
                $kkm = isset($nilai['nilai_kkm']) && $nilai['nilai_kkm'] !== null ? number_format((float) $nilai['nilai_kkm'], 0) : '-';
                $nilaiAkhir = isset($nilai['nilai_akhir']) ? number_format((float) $nilai['nilai_akhir'], 2) : '-';
                $html .= '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . esc((string) ($nilai['nama_mapel'] ?? '-')) . '</td>
                    <td class="text-center">' . $kkm . '</td>
                    <td class="text-center"><strong>' . $nilaiAkhir . '</strong></td>
                    <td class="text-center"><strong>' . esc((string) ($nilai['nilai_huruf'] ?? '-')) . '</strong></td>
                    <td class="text-center" style="color:' . $statusColor . '; font-weight:bold;">' . esc((string) ($nilai['status_kelulusan'] ?? '-')) . '</td>
                </tr>';
            }

            return $html;
        };

        $namaSiswa = esc((string) ($siswa['nama_siswa'] ?? '-'));
        $nis = esc((string) ($siswa['nis'] ?? '-'));
        $nisn = esc((string) ($siswa['nisn'] ?? '-'));
        $kelas = esc((string) ($siswa['nama_kelas'] ?? '-'));
        $tempatLahir = esc((string) ($siswa['tempat_lahir'] ?? '-'));
        $tanggalLahir = !empty($siswa['tanggal_lahir']) ? date('d-m-Y', strtotime($siswa['tanggal_lahir'])) : '-';
        $tahun = esc((string) ($tahunAjaran['tahun_ajaran'] ?? '-'));
        $semester = esc((string) ($tahunAjaran['semester'] ?? '-'));
        $statusKenaikan = esc((string) ($rapor['status_kenaikan'] ?? 'Belum Ditentukan'));
        $totalRows = count($kelompokA) + count($kelompokB);
        $catatanPlain = trim((string) ($rapor['catatan_wali_kelas'] ?? 'Tidak ada catatan dari wali kelas.'));
        if (mb_strlen($catatanPlain) > 600) {
            $catatanPlain = rtrim(mb_substr($catatanPlain, 0, 600)) . '…';
        }
        $catatan = nl2br(esc($catatanPlain));
        $notesPageBreak = ($totalRows > 18 || mb_strlen($catatanPlain) > 350) ? '<br pagebreak="true" />' : '';
        $signaturePageBreak = $totalRows > 26 ? '<br pagebreak="true" />' : '';

        $html = '
        <style>
            body { color: #24364b; }
            .document-title { text-align: center; margin-bottom: 10px; }
            .document-title h1 { font-size: 18px; margin: 0 0 4px; color: #1c4167; }
            .document-title p { font-size: 10px; margin: 0; color: #6a7d90; }
            .hero-box { border: 1px solid #d5e2ee; background: #f7fbff; padding: 12px 14px; border-radius: 8px; margin-bottom: 14px; }
            .header-table { width: 100%; margin-bottom: 12px; font-size: 10px; }
            .header-table td { padding: 4px 5px; vertical-align: top; }
            .nilai-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9.5px; }
            .nilai-table th { background-color: #234d73; color: #ffffff; padding: 8px 6px; border: 1px solid #d9e4ef; text-align: center; font-weight: bold; }
            .nilai-table td { padding: 7px 6px; border: 1px solid #d9e4ef; }
            .section-title { font-weight: bold; margin-top: 16px; margin-bottom: 6px; background-color: #eaf3fb; color: #234d73; padding: 7px 9px; border: 1px solid #d5e2ee; }
            .text-center { text-align: center; }
            .empty-state { text-align: center; color: #73859a; font-style: italic; padding: 10px; }
            .note-box { border: 1px solid #d5e2ee; background: #fcfdff; padding: 10px 12px; font-size: 10px; }
            .signature-table { width: 100%; margin-top: 32px; font-size: 10px; }
            .signature-table td { padding: 10px; vertical-align: top; }
            .footer-meta { margin-top: 16px; font-size: 8px; color: #7a8a9b; text-align: center; }
        </style>

        <div class="document-title">
            <h1>RAPOR SISWA</h1>
            <p>SDN 3 MEKARSARI</p>
            <p>Tahun Ajaran ' . $tahun . ' • Semester ' . $semester . '</p>
        </div>

        <div class="hero-box">
            Dokumen ini merupakan ringkasan hasil belajar siswa yang telah difinalisasi pada sistem akademik SDN 3 Mekarsari.
        </div>

        <table class="header-table">
            <tr>
                <td width="24%">Nama Siswa</td>
                <td width="3%">:</td>
                <td width="31%"><strong>' . $namaSiswa . '</strong></td>
                <td width="18%">Kelas</td>
                <td width="3%">:</td>
                <td width="21%">' . $kelas . '</td>
            </tr>
            <tr>
                <td>NIS / NISN</td>
                <td>:</td>
                <td>' . $nis . ' / ' . $nisn . '</td>
                <td>Semester</td>
                <td>:</td>
                <td>' . $semester . '</td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td>' . $tempatLahir . ', ' . esc($tanggalLahir) . '</td>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td>' . $tahun . '</td>
            </tr>
        </table>

        <div class="section-title">A. Kelompok Mata Pelajaran Nasional</div>
        <table class="nilai-table">
            <thead>
                <tr>
                    <th width="6%">No</th>
                    <th width="38%">Mata Pelajaran</th>
                    <th width="12%">KKM</th>
                    <th width="14%">Nilai</th>
                    <th width="10%">Huruf</th>
                    <th width="20%">Keterangan</th>
                </tr>
            </thead>
            <tbody>' . $renderRows($kelompokA) . '</tbody>
        </table>

        <div class="section-title">B. Kelompok Mata Pelajaran Muatan Lokal</div>
        <table class="nilai-table">
            <thead>
                <tr>
                    <th width="6%">No</th>
                    <th width="38%">Mata Pelajaran</th>
                    <th width="12%">KKM</th>
                    <th width="14%">Nilai</th>
                    <th width="10%">Huruf</th>
                    <th width="20%">Keterangan</th>
                </tr>
            </thead>
            <tbody>' . $renderRows($kelompokB) . '</tbody>
        </table>';

        if ($rapor) {
            $html .= '
            ' . $notesPageBreak . '
            <div class="section-title">C. Ketidakhadiran</div>
            <table class="header-table">
                <tr>
                    <td width="30%">Sakit</td>
                    <td width="2%">:</td>
                    <td width="68%">' . (int) ($rapor['sakit'] ?? 0) . ' hari</td>
                </tr>
                <tr>
                    <td>Izin</td>
                    <td>:</td>
                    <td>' . (int) ($rapor['izin'] ?? 0) . ' hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan (Alpa)</td>
                    <td>:</td>
                    <td>' . (int) ($rapor['alpa'] ?? 0) . ' hari</td>
                </tr>
            </table>

            <div class="section-title">D. Catatan Wali Kelas</div>
            <div class="note-box">' . $catatan . '</div>

            <div class="section-title">E. Keputusan</div>
            <div class="note-box"><strong>Berdasarkan hasil belajar pada semester ini, siswa ditetapkan: ' . $statusKenaikan . '.</strong></div>';
        }

        $html .= '
        ' . $signaturePageBreak . '
        <table class="signature-table">
            <tr>
                <td width="50%" class="text-center">
                    <p>Orang Tua / Wali,</p>
                    <br><br><br>
                    <p>_____________________</p>
                </td>
                <td width="50%" class="text-center">
                    <p>Wali Kelas,</p>
                    <br><br><br>
                    <p>_____________________</p>
                </td>
            </tr>
        </table>

        <div class="footer-meta">
            <p>Dokumen ini dihasilkan otomatis oleh Sistem Informasi Manajemen Nilai Siswa SDN 3 Mekarsari.</p>
            <p>Tanggal cetak: ' . date('d-m-Y H:i:s') . '</p>
        </div>';

        return $html;
    }
}
