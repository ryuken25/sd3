<?php

namespace App\Controllers\OrangTua;

use App\Controllers\BaseController;
use App\Libraries\RaporNarrativeService;
use App\Models\KelasModel;
use App\Models\KokurikulerTemaModel;
use App\Models\NilaiAkhirModel;
use App\Models\NilaiCapaianKompetensiModel;
use App\Models\RaporModel;
use App\Models\SiswaEkstrakurikulerModel;
use App\Models\SiswaKokurikulerDimensiModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
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
    // Konstanta identitas sekolah (Pek 1.1) - dipakai sebagai konstanta PDF
    private const KEPSEK_NAMA = 'Ni Wayan Kasrinayanti, S. Pd.';
    private const KEPSEK_NIP  = '198408132014062008';
    private const SEKOLAH     = 'SD NEGERI 3 MEKARSARI';

    // Mapping NIP guru per username (sumber: SD3_GuruSeeder)
    private const NIP_GURU = [
        'kasrinayanti'  => '198408132014062008',
        'nengahsarini'  => '196803301994032007',
        'bayukarsana'   => '198911082022211011',
        'raipitriani'   => '197710202021212001',
        'suarjana'      => '197407072023211004',
        'ariwidnya'     => '198508112022211007',
        'damayanti'     => '199008152022212006',
        'madhavi'       => '199308222024212027',
        'siskadewi'     => '',
        'desiwulandari' => '199312022025212013',
    ];

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

        $data = $this->loadRaporData($idSiswa, $idTa);
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
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
        // Custom footer: "Kelas X | NAMA | NIS    Halaman : N"
        $footer = sprintf('Kelas %s | %s | %s', $data['kelas']['nama_kelas'] ?? '-',
            $data['siswa']['nama_siswa'] ?? '-', $data['siswa']['nis'] ?? '-');
        $pdf->setPageMark();
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

    // ─── Data loader ──────────────────────────────────────────────────────────

    private function loadRaporData(int $idSiswa, int $idTa): array
    {
        $siswaModel = new SiswaModel();
        $kelasModel = new KelasModel();
        $taModel = new TahunAjaranModel();
        $raporModel = new RaporModel();
        $nilaiAkhirModel = new NilaiAkhirModel();
        $nilaiCpModel = new NilaiCapaianKompetensiModel();
        $temaModel = new KokurikulerTemaModel();
        $siswaEkskulModel = new SiswaEkstrakurikulerModel();
        $siswaKokoModel = new SiswaKokurikulerDimensiModel();

        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) return ['error' => 'Data siswa tidak ditemukan.'];

        $kelas = $kelasModel->find($siswa['id_kelas']);
        $ta    = $taModel->find($idTa);
        if (!$ta) return ['error' => 'Data tahun ajaran tidak ditemukan.'];

        $rapor = $raporModel->where('id_siswa', $idSiswa)
            ->where('id_tahun_ajaran', $idTa)
            ->first();

        // Wali kelas
        $waliKelas = null;
        if ($kelas && !empty($kelas['wali_kelas'])) {
            $waliKelas = \Config\Database::connect()->table('users')
                ->where('id_user', $kelas['wali_kelas'])->get()->getRowArray();
        }

        // Mapel + nilai_akhir + CP narasi
        $nilaiRows = $nilaiAkhirModel->select('nilai_akhir.*, mata_pelajaran.nama_mapel, mata_pelajaran.kode_mapel')
            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = nilai_akhir.id_mapel')
            ->where('nilai_akhir.id_siswa', $idSiswa)
            ->where('nilai_akhir.id_tahun_ajaran', $idTa)
            ->orderBy('mata_pelajaran.id_mapel', 'ASC')
            ->findAll();

        $narrative = new RaporNarrativeService();
        $mapelData = ['wajib' => [], 'pilihan' => []];
        foreach ($nilaiRows as $row) {
            $cpList = $nilaiCpModel->listWithDeskripsi((int) $row['id_nilai_akhir']);
            $row['capaian_narasi'] = $narrative->generateNarasiCP($cpList);
            // Bahasa Bali (kode BBALI) = pilihan; sisanya = wajib
            $isPilihan = strtoupper((string) ($row['kode_mapel'] ?? '')) === 'BBALI';
            $mapelData[$isPilihan ? 'pilihan' : 'wajib'][] = $row;
        }

        // Kokurikuler
        $tema = $kelas ? $temaModel->findForKelasTa((int) $kelas['id_kelas'], $idTa) : null;
        $kokoNarasi = '';
        if ($tema) {
            $dimensi = $siswaKokoModel->findForSiswaTema($idSiswa, (int) $tema['id_tema']);
            $kokoNarasi = $narrative->generateNarasiKokurikuler($tema['nama_tema'], $dimensi);
        }

        // Ekstrakurikuler
        $ekskul = $siswaEkskulModel->findForSiswaTa($idSiswa, $idTa);

        // Fase (dari tingkat kelas)
        $tingkat = (int) ($kelas['tingkat'] ?? 0);
        $fase = match (true) {
            $tingkat <= 2 => 'A',
            $tingkat <= 4 => 'B',
            default       => 'C',
        };

        return [
            'siswa'        => $siswa,
            'kelas'        => $kelas,
            'tahun_ajaran' => $ta,
            'rapor'        => $rapor,
            'wali_kelas'   => $waliKelas,
            'mapel'        => $mapelData,
            'fase'         => $fase,
            'koko_narasi'  => $kokoNarasi,
            'tema'         => $tema,
            'ekskul'       => $ekskul,
        ];
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
            ['Sekolah',    self::SEKOLAH,                     'Semester',     $semesterNum],
            ['Alamat',     $alamat,                           'Tahun Ajaran', $ta['tahun_ajaran'] ?? '-'],
        ];

        // Halaman 3 footer-style: kepalanya sama tapi ringkas tanpa "Fase"
        if ($compact) {
            $rows = [
                ['Nama Murid', $s['nama_siswa'] ?? '-',           'Kelas',        $k['nama_kelas'] ?? '-'],
                ['NIS/NISN',   ($s['nis'] ?? '-') . ' / ' . ($s['nisn'] ?? '-'), 'Semester', $semesterNum],
                ['Sekolah',    self::SEKOLAH,                     'Tahun Ajaran', $ta['tahun_ajaran'] ?? '-'],
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
        $narrative = new RaporNarrativeService();
        $tanggal = $narrative->tanggalIndonesia();

        $waliNama = $d['wali_kelas']['nama_lengkap'] ?? '-';
        $waliUsername = $d['wali_kelas']['username'] ?? '';
        $waliNip = self::NIP_GURU[$waliUsername] ?? '-';

        return '<br><br>'
             . '<table style="width:100%;font-size:10px;">'
             . '<tr>'
             . '<td width="33%"></td>'
             . '<td width="33%"></td>'
             . '<td width="33%" style="text-align:center;">Tabanan, ' . esc($tanggal) . '</td>'
             . '</tr>'
             . '<tr>'
             . '<td style="text-align:center;">Orang Tua Murid</td>'
             . '<td style="text-align:center;">Kepala Sekolah</td>'
             . '<td style="text-align:center;">Wali Kelas</td>'
             . '</tr>'
             . '<tr><td colspan="3" style="height:60px;"></td></tr>'
             . '<tr style="font-weight:bold;text-decoration:underline;">'
             . '<td style="text-align:center;">......................................</td>'
             . '<td style="text-align:center;">' . esc(self::KEPSEK_NAMA) . '</td>'
             . '<td style="text-align:center;">' . esc($waliNama) . '</td>'
             . '</tr>'
             . '<tr style="font-size:9px;">'
             . '<td></td>'
             . '<td style="text-align:center;">NIP. ' . esc(self::KEPSEK_NIP) . '</td>'
             . '<td style="text-align:center;">NIP. ' . esc($waliNip) . '</td>'
             . '</tr>'
             . '</table>';
    }
}
