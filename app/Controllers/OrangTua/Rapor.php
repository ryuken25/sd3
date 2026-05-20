<?php

namespace App\Controllers\OrangTua;

use App\Controllers\BaseController;
use App\Libraries\RaporDataLoader;
use App\Models\SiswaModel;
use TCPDF;

/**
 * PDF rapor generator — match Rapor_Kelas3.pdf / Rapor_Kelas6.pdf reference.
 *
 * Layout 3 halaman:
 *   Hal 1: header siswa + tabel Mata Pelajaran Wajib
 *   Hal 2: tabel Mata Pelajaran Pilihan + Kokurikuler narasi + Ekstrakurikuler
 *          + Ketidakhadiran + Catatan Wali Kelas + Tanggapan Orang Tua
 *   Hal 3: Tanda tangan (Orang Tua / Kepala Sekolah / Wali Kelas) dengan NIP
 *
 * CATATAN: catatan_remedial dari nilai_akhir TIDAK ditampilkan di PDF.
 */
class Rapor extends BaseController
{
    public function downloadPDF($idSiswa, $idTahunAjaran)
    {
        $idSiswa = (int) $idSiswa;
        $idTa    = (int) $idTahunAjaran;

        $session = session();
        $idUser  = (int) $session->get('id_user');

        $siswaModel = new SiswaModel();
        if (!$siswaModel->isOwnedByParent($idSiswa, $idUser)) {
            return redirect()->to(base_url('orangtua/dashboard'))->with('error', 'Akses ditolak.');
        }

        $data = (new RaporDataLoader())->load($idSiswa, $idTa);
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        // Pek 9: rapor download hanya tersedia kalau rapor sudah difinalisasi.
        // Gate ini selaras dengan tombol "Download PDF" di e-rapor view yang
        // hanya muncul kalau is_finalized = 1.
        if (empty($data['rapor']) || (int) ($data['rapor']['is_finalized'] ?? 0) !== 1) {
            return redirect()->to(base_url('orangtua/dashboard'))
                ->with('error', 'Rapor belum difinalisasi. Tunggu wali kelas/admin memfinalkan.');
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SDN 3 Mekarsari');
        $pdf->SetAuthor('SDN 3 Mekarsari');
        $pdf->SetTitle('Rapor - ' . $data['siswa']['nama_siswa']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterFont(['courier', 'I', 8]);
        $pdf->setFooterData([0, 0, 0], [255, 255, 255]);
        $pdf->setFooterMargin(10);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 18);

        // Halaman 1: header + mapel wajib
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($this->renderHeader($data), true, false, true, false, '');
        $pdf->writeHTML('<h3 style="text-align:center;margin:6px 0;">LAPORAN HASIL BELAJAR</h3>', true, false, true, false, '');
        $pdf->writeHTML($this->renderTabelMapel($data, 'wajib'), true, false, true, false, '');

        // Halaman 2: mapel pilihan + kokurikuler + ekskul + ketidakhadiran + catatan
        $pdf->AddPage();
        $pdf->writeHTML($this->renderHeader($data), true, false, true, false, '');
        $pdf->writeHTML($this->renderTabelMapel($data, 'pilihan'), true, false, true, false, '');
        $pdf->writeHTML($this->renderKokurikuler($data), true, false, true, false, '');
        $pdf->writeHTML($this->renderEkstrakurikuler($data), true, false, true, false, '');
        $pdf->writeHTML($this->renderKetidakhadiranCatatan($data), true, false, true, false, '');
        $pdf->writeHTML($this->renderTanggapan(), true, false, true, false, '');

        // Halaman 3: tanda tangan
        $pdf->AddPage();
        $pdf->writeHTML($this->renderHeader($data, true), true, false, true, false, '');
        $pdf->writeHTML($this->renderTandaTangan($data), true, false, true, false, '');

        $filename = sprintf('Rapor_%s_%s_%s.pdf',
            preg_replace('/[^A-Za-z0-9]+/', '_', $data['siswa']['nama_siswa']),
            $data['tahun_ajaran']['tahun_ajaran'],
            $data['tahun_ajaran']['semester']);
        $pdf->Output(str_replace('/', '_', $filename), 'D');
    }

    // ─── Renderers ────────────────────────────────────────────────────────────

    private function renderHeader(array $d, bool $compact = false): string
    {
        $s = $d['siswa'];
        $k = $d['kelas'];
        $ta = $d['tahun_ajaran'];
        $alamat = esc((string) ($s['alamat'] ?? '-'));
        $semesterNum = strtolower((string) ($ta['semester'] ?? '')) === 'genap' ? '2' : '1';

        $rows = [
            ['Nama Murid', $s['nama_siswa'] ?? '-',           'Kelas',        $k['nama_kelas'] ?? '-'],
            ['NIS/NISN',   ($s['nis'] ?? '-') . ' / ' . ($s['nisn'] ?? '-'), 'Fase', $d['fase']],
            ['Sekolah',    $d['sekolah'],                     'Semester',     $semesterNum],
            ['Alamat',     $alamat,                           'Tahun Ajaran', $ta['tahun_ajaran'] ?? '-'],
        ];

        // Halaman 3 footer-style: kepalanya sama tapi ringkas tanpa "Fase"
        if ($compact) {
            $rows = [
                ['Nama Murid', $s['nama_siswa'] ?? '-',           'Kelas',        $k['nama_kelas'] ?? '-'],
                ['NIS/NISN',   ($s['nis'] ?? '-') . ' / ' . ($s['nisn'] ?? '-'), 'Semester', $semesterNum],
                ['Sekolah',    $d['sekolah'],                     'Tahun Ajaran', $ta['tahun_ajaran'] ?? '-'],
                ['Alamat',     $alamat,                           '',             ''],
            ];
        }

        $h = '<table cellpadding="3" style="font-size:10px;width:100%;">';
        foreach ($rows as $r) {
            $h .= '<tr>'
                . '<td width="22%">' . esc($r[0]) . '</td>'
                . '<td width="2%">:</td>'
                . '<td width="38%">' . esc($r[1]) . '</td>'
                . '<td width="15%">' . esc($r[2]) . '</td>'
                . '<td width="2%">' . ($r[2] ? ':' : '') . '</td>'
                . '<td width="21%">' . esc($r[3]) . '</td>'
                . '</tr>';
        }
        $h .= '</table>';
        $h .= '<hr style="border-top:1px solid #999;margin:6px 0;">';
        return $h;
    }

    private function renderTabelMapel(array $d, string $kelompok): string
    {
        $rows = $d['mapel'][$kelompok] ?? [];
        $heading = $kelompok === 'wajib' ? 'Mata Pelajaran Wajib' : 'Mata Pelajaran Pilihan';

        $h = '<table border="0.5" cellpadding="4" style="width:100%;font-size:9.5px;border-collapse:collapse;">';
        $h .= '<thead><tr style="background-color:#e8eef5;font-weight:bold;text-align:center;">'
            . '<th width="5%">No</th>'
            . '<th width="28%">Mata Pelajaran</th>'
            . '<th width="10%">Nilai Akhir</th>'
            . '<th width="57%">Capaian Kompetensi</th>'
            . '</tr></thead>';
        $h .= '<tbody>';
        $h .= '<tr><td colspan="4" style="background-color:#f7f7f7;font-weight:bold;font-size:9.5px;">' . esc($heading) . '</td></tr>';

        if (empty($rows)) {
            $h .= '<tr><td colspan="4" style="text-align:center;color:#888;font-style:italic;">Tidak ada data.</td></tr>';
        } else {
            $no = 1;
            foreach ($rows as $r) {
                $nilai = isset($r['nilai_akhir']) ? number_format((float) $r['nilai_akhir'], 0) : '-';
                $narasi = $r['capaian_narasi'] ?: '<span style="color:#aaa;font-style:italic;">Belum ada capaian dinilai.</span>';
                $h .= '<tr>'
                    . '<td style="text-align:center;">' . $no++ . '</td>'
                    . '<td>' . esc((string) ($r['nama_mapel'] ?? '-')) . '</td>'
                    . '<td style="text-align:center;font-weight:bold;">' . $nilai . '</td>'
                    . '<td>' . $narasi . '</td>'
                    . '</tr>';
            }
        }

        $h .= '</tbody></table>';
        return $h;
    }

    private function renderKokurikuler(array $d): string
    {
        $narasi = trim((string) ($d['koko_narasi'] ?? ''));
        if ($narasi === '') {
            $narasi = '<span style="color:#aaa;font-style:italic;">Belum ada capaian kokurikuler dinilai.</span>';
        } else {
            $narasi = nl2br(esc($narasi));
        }
        return '<br>'
             . '<table border="0.5" cellpadding="4" style="width:100%;font-size:9.5px;">'
             . '<tr><td style="background-color:#e8eef5;text-align:center;font-weight:bold;">Kokurikuler</td></tr>'
             . '<tr><td style="font-size:9.5px;">' . $narasi . '</td></tr>'
             . '</table>';
    }

    private function renderEkstrakurikuler(array $d): string
    {
        $rows = $d['ekskul'] ?? [];
        $h = '<br><table border="0.5" cellpadding="4" style="width:100%;font-size:9.5px;border-collapse:collapse;">';
        $h .= '<thead><tr style="background-color:#e8eef5;font-weight:bold;text-align:center;">'
            . '<th width="5%">No</th>'
            . '<th width="25%">Ekstrakurikuler</th>'
            . '<th width="70%">Keterangan</th>'
            . '</tr></thead><tbody>';
        if (empty($rows)) {
            $h .= '<tr><td colspan="3" style="text-align:center;color:#888;font-style:italic;">Belum ada ekstrakurikuler.</td></tr>';
        } else {
            $no = 1;
            foreach ($rows as $r) {
                $h .= '<tr>'
                    . '<td style="text-align:center;">' . $no++ . '</td>'
                    . '<td>' . esc((string) ($r['nama'] ?? '-')) . '</td>'
                    . '<td>' . esc((string) ($r['keterangan'] ?? '-')) . '</td>'
                    . '</tr>';
            }
        }
        $h .= '</tbody></table>';
        return $h;
    }

    private function renderKetidakhadiranCatatan(array $d): string
    {
        $rapor = $d['rapor'] ?? [];
        $sakit = (int) ($rapor['sakit'] ?? 0);
        $izin  = (int) ($rapor['izin'] ?? 0);
        $alpa  = (int) ($rapor['alpa'] ?? 0);
        $catatan = esc((string) ($rapor['catatan_wali_kelas'] ?? ''));

        $absen = '<table border="0.5" cellpadding="4" style="width:100%;font-size:9.5px;">'
               . '<tr><td colspan="3" style="background-color:#e8eef5;text-align:center;font-weight:bold;">Ketidakhadiran</td></tr>'
               . '<tr><td width="50%">Sakit</td><td width="5%">:</td><td>' . $sakit . ' hari</td></tr>'
               . '<tr><td>Izin</td><td>:</td><td>' . $izin . ' hari</td></tr>'
               . '<tr><td>Tanpa Keterangan</td><td>:</td><td>' . $alpa . ' hari</td></tr>'
               . '</table>';

        $cat = '<table border="0.5" cellpadding="4" style="width:100%;font-size:9.5px;height:100%;">'
             . '<tr><td style="background-color:#e8eef5;text-align:center;font-weight:bold;">Catatan Wali Kelas</td></tr>'
             . '<tr><td style="font-size:9.5px;">' . ($catatan !== '' ? $catatan : '<span style="color:#aaa;font-style:italic;">Belum ada catatan.</span>') . '</td></tr>'
             . '</table>';

        return '<br><table cellpadding="3" style="width:100%;"><tr>'
             . '<td width="40%" valign="top">' . $absen . '</td>'
             . '<td width="60%" valign="top">' . $cat . '</td>'
             . '</tr></table>';
    }

    private function renderTanggapan(): string
    {
        return '<br><table border="0.5" cellpadding="6" style="width:100%;font-size:9.5px;">'
             . '<tr><td style="background-color:#e8eef5;text-align:center;font-weight:bold;">Tanggapan Orang Tua/Wali Murid</td></tr>'
             . '<tr><td style="height:60px;"></td></tr>'
             . '</table>';
    }

    private function renderTandaTangan(array $d): string
    {
        $waliNama = $d['wali_kelas']['nama_lengkap'] ?? '-';

        return '<br><br>'
             . '<table style="width:100%;font-size:10px;">'
             . '<tr>'
             . '<td width="33%"></td>'
             . '<td width="33%"></td>'
             . '<td width="33%" style="text-align:center;">Tabanan, ' . esc($d['tanggal_indo']) . '</td>'
             . '</tr>'
             . '<tr>'
             . '<td style="text-align:center;">Orang Tua Murid</td>'
             . '<td style="text-align:center;">Kepala Sekolah</td>'
             . '<td style="text-align:center;">Wali Kelas</td>'
             . '</tr>'
             . '<tr><td colspan="3" style="height:60px;"></td></tr>'
             . '<tr style="font-weight:bold;text-decoration:underline;">'
             . '<td style="text-align:center;">......................................</td>'
             . '<td style="text-align:center;">' . esc($d['kepsek_nama']) . '</td>'
             . '<td style="text-align:center;">' . esc($waliNama) . '</td>'
             . '</tr>'
             . '<tr style="font-size:9px;">'
             . '<td></td>'
             . '<td style="text-align:center;">NIP. ' . esc($d['kepsek_nip']) . '</td>'
             . '<td style="text-align:center;">NIP. ' . esc($d['wali_nip']) . '</td>'
             . '</tr>'
             . '</table>';
    }
}
