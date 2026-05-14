<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiSiswaModel;
use App\Models\SiswaModel;
use App\Services\AcademicPeriodService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportNilai extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();

        $period      = null;
        $periodError = null;
        try {
            $period = (new AcademicPeriodService())->getActivePeriod();
        } catch (\RuntimeException $e) {
            $periodError = $e->getMessage();
        }

        return view('admin/import_nilai/index', [
            'title'       => 'Import Nilai Siswa',
            'kelas'       => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'period'      => $period,
            'periodError' => $periodError,
        ]);
    }

    /**
     * Generate template Excel untuk satu kelas, prefill siswa + mapel,
     * kunci kolom periode, sisipkan hash di Z1.
     */
    public function downloadTemplate(int $kelasId)
    {
        $kelasModel = new KelasModel();
        $siswaModel = new SiswaModel();
        $mapelModel = new MataPelajaranModel();

        $kelas = $kelasModel->find($kelasId);
        if (!$kelas) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        try {
            $service = new AcademicPeriodService();
            $period  = $service->getActivePeriod();
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $siswaList = $siswaModel->where('id_kelas', $kelasId)->where('status', 'aktif')
            ->orderBy('nama_siswa', 'ASC')->findAll();
        $mapelList = $mapelModel->getByClass($kelasId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Nilai');

        // --- Header ---
        $headers = [
            'A' => 'NIS',
            'B' => 'Nama Siswa',
            'C' => 'Kode Mapel',
            'D' => 'Nama Mapel',
            'E' => 'Nilai Tugas (0-100)',
            'F' => 'Nilai Ulangan (0-100)',
            'G' => 'Nilai UTS (0-100)',
            'H' => 'Nilai UAS (0-100)',
            'I' => 'Tahun Ajaran',
            'J' => 'Semester',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '1', $label);
        }

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1976D2');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- Prefill baris data ---
        $row = 2;
        foreach ($siswaList as $siswa) {
            foreach ($mapelList as $mapel) {
                $sheet->setCellValue('A' . $row, $siswa['nis']);
                $sheet->setCellValue('B' . $row, $siswa['nama_siswa']);
                $sheet->setCellValue('C' . $row, $mapel['kode_mapel']);
                $sheet->setCellValue('D' . $row, $mapel['nama_mapel']);
                // E-H kosong — diisi user
                $sheet->setCellValue('I' . $row, $period['tahun_ajaran']);
                $sheet->setCellValue('J' . $row, $period['semester']);
                $row++;
            }
        }

        // --- Proteksi kolom periode (I & J) + kolom identitas (A-D) ---
        $sheet->getProtection()->setSheet(true)->setPassword('sd3mk');
        $lockStyle = ['protection' => ['locked' => Protection::PROTECTION_PROTECTED]];
        $editStyle = ['protection' => ['locked' => Protection::PROTECTION_UNPROTECTED]];

        if ($row > 2) {
            $sheet->getStyle('A2:D' . ($row - 1))->applyFromArray($lockStyle);
            $sheet->getStyle('E2:H' . ($row - 1))->applyFromArray($editStyle);
            $sheet->getStyle('I2:J' . ($row - 1))->applyFromArray($lockStyle);

            // Warnai kolom periode agar user tahu dikunci
            $sheet->getStyle('I2:J' . ($row - 1))->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF9C4');
        }

        // --- Auto-size ---
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Hidden metadata di Z1 untuk validasi saat upload ---
        $hash = $service->makeTemplateHash($period['id_tahun_ajaran'], $period['semester']);
        $sheet->setCellValue('Z1', $period['id_tahun_ajaran'] . ':' . $period['semester'] . ':' . $hash);
        $sheet->getColumnDimension('Z')->setVisible(false);

        // --- Sheet petunjuk ---
        $help = $spreadsheet->createSheet();
        $help->setTitle('Petunjuk');
        $helpRows = [
            ['Petunjuk Pengisian Template Nilai'],
            [],
            ['1. Jangan mengubah atau menghapus kolom A (NIS), B (Nama), C (Kode Mapel), D (Nama Mapel).'],
            ['2. Isi nilai pada kolom E (Tugas), F (Ulangan), G (UTS), H (UAS) — rentang 0–100.'],
            ['3. Kolom I (Tahun Ajaran) dan J (Semester) dikunci dan tidak boleh diubah.'],
            ['4. Template ini hanya berlaku untuk periode: ' . $period['label']],
            ['5. Jika periode sudah berganti, download template baru dari aplikasi.'],
            ['6. Baris yang nilai tugasnya kosong akan dilewati (skipped).'],
            ['7. Upload file ini melalui menu Import Nilai → Upload File Excel.'],
        ];
        foreach ($helpRows as $i => $helpRow) {
            $help->setCellValue('A' . ($i + 1), $helpRow[0] ?? '');
        }
        $help->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $help->getColumnDimension('A')->setWidth(80);

        // --- Output ---
        $filename = 'Template_Nilai_Kelas' . $kelas['nama_kelas'] . '_' . str_replace('/', '-', $period['tahun_ajaran']) . '_' . $period['semester'] . '.xlsx';

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }

    /**
     * Proses upload file Excel nilai.
     */
    public function upload()
    {
        $service = new AcademicPeriodService();

        try {
            $period = $service->getActivePeriod();
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        if (!$this->validate([
            'file' => 'uploaded[file]|max_size[file,5120]|ext_in[file,xlsx,xls]',
        ])) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        $sheet = $spreadsheet->getSheetByName('Data Nilai') ?? $spreadsheet->getActiveSheet();

        // Validasi template hash
        $meta = (string) $sheet->getCell('Z1')->getValue();
        if (!$service->validateTemplateHash($meta, $period)) {
            $parts = $meta ? explode(':', $meta, 3) : [];
            $templateInfo = (count($parts) >= 2)
                ? "Template dibuat untuk {$parts[0]} - Semester {$parts[1]}."
                : 'Template tidak dikenal.';

            return redirect()->back()->with('error',
                "{$templateInfo} Periode aktif sekarang: {$period['label']}. "
                . 'Silakan download template terbaru.'
            );
        }

        $nilaiModel = new NilaiSiswaModel();
        $siswaModel = new SiswaModel();
        $mapelModel = new MataPelajaranModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $success = 0;
        $failed  = [];
        $skipped = 0;

        try {
            $rows     = $sheet->toArray(null, true, true, true);
            $dataRows = \array_slice($rows, 1); // buang header baris 1

            foreach ($dataRows as $idx => $row) {
                $rowNum = $idx + 2;

                $nis       = trim((string) ($row['A'] ?? ''));
                $kodeMapel = trim((string) ($row['C'] ?? ''));
                $nilaiTugas    = $row['E'] ?? null;
                $nilaiUlangan  = $row['F'] ?? null;
                $nilaiUts      = $row['G'] ?? null;
                $nilaiUas      = $row['H'] ?? null;

                // Skip baris kosong
                if ($nis === '' && $kodeMapel === '') {
                    $skipped++;
                    continue;
                }

                // Validasi wajib
                if ($nis === '') {
                    $failed[] = "Baris {$rowNum}: NIS kosong.";
                    continue;
                }
                if ($kodeMapel === '') {
                    $failed[] = "Baris {$rowNum}: Kode Mapel kosong.";
                    continue;
                }

                // Validasi range nilai
                foreach (['E' => $nilaiTugas, 'F' => $nilaiUlangan, 'G' => $nilaiUts, 'H' => $nilaiUas] as $col => $val) {
                    if ($val !== null && $val !== '') {
                        $intVal = (int) $val;
                        if ($intVal < 0 || $intVal > 100) {
                            $failed[] = "Baris {$rowNum}: Nilai di kolom {$col} harus 0–100 (ditemukan: {$val}).";
                            continue 2;
                        }
                    }
                }

                // Resolve siswa
                $siswa = $siswaModel->where('nis', $nis)->first();
                if (!$siswa) {
                    $failed[] = "Baris {$rowNum}: NIS '{$nis}' tidak ditemukan.";
                    continue;
                }

                // Resolve mapel
                $mapel = $mapelModel->where('kode_mapel', $kodeMapel)->first();
                if (!$mapel) {
                    $failed[] = "Baris {$rowNum}: Kode Mapel '{$kodeMapel}' tidak ditemukan.";
                    continue;
                }

                $payload = [
                    'id_siswa'        => (int) $siswa['id_siswa'],
                    'id_mapel'        => (int) $mapel['id_mapel'],
                    'id_tahun_ajaran' => $period['id_tahun_ajaran'],
                ];

                if ($nilaiTugas !== null && $nilaiTugas !== '')   $payload['nilai_tugas']    = (int) $nilaiTugas;
                if ($nilaiUlangan !== null && $nilaiUlangan !== '') $payload['nilai_ulangan'] = (int) $nilaiUlangan;
                if ($nilaiUts !== null && $nilaiUts !== '')        $payload['nilai_uts']      = (int) $nilaiUts;
                if ($nilaiUas !== null && $nilaiUas !== '')        $payload['nilai_uas']      = (int) $nilaiUas;

                // Hitung rata_rata_harian kalau keduanya ada
                if (isset($payload['nilai_tugas']) && isset($payload['nilai_ulangan'])) {
                    $payload['rata_rata_harian'] = round(($payload['nilai_tugas'] + $payload['nilai_ulangan']) / 2, 2);
                }

                $nilaiModel->upsertByKey($payload);
                $success++;
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Import gagal — transaksi database di-rollback.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }

        session()->setFlashdata('import_nilai_report', [
            'success' => $success,
            'failed'  => $failed,
            'skipped' => $skipped,
            'period'  => $period['label'],
        ]);

        return redirect()->to(base_url('admin/import-nilai'))
            ->with('success', "Import selesai: {$success} berhasil, " . \count($failed) . " gagal, {$skipped} dilewati. Tujuan: {$period['label']}.");
    }
}
