<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\UserModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Import extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();

        $data = [
            'title' => 'Import Data Siswa',
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll()
        ];

        return view('admin/import/index', $data);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header columns
        $sheet->setCellValue('A1', 'NIS');
        $sheet->setCellValue('B1', 'NISN');
        $sheet->setCellValue('C1', 'Nama Siswa');
        $sheet->setCellValue('D1', 'Jenis Kelamin (L/P)');
        $sheet->setCellValue('E1', 'Tempat Lahir');
        $sheet->setCellValue('F1', 'Tanggal Lahir (YYYY-MM-DD)');
        $sheet->setCellValue('G1', 'Alamat');
        $sheet->setCellValue('H1', 'ID Kelas');
        $sheet->setCellValue('I1', 'Nama Ayah');
        $sheet->setCellValue('J1', 'Nama Ibu');
        $sheet->setCellValue('K1', 'No Telp Ortu');

        // Style header
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4CAF50');

        // Add sample data
        $sheet->setCellValue('A2', '2301001');
        $sheet->setCellValue('B2', '0012345678');
        $sheet->setCellValue('C2', 'Contoh Siswa');
        $sheet->setCellValue('D2', 'L');
        $sheet->setCellValue('E2', 'Jakarta');
        $sheet->setCellValue('F2', '2015-05-10');
        $sheet->setCellValue('G2', 'Jl. Contoh No. 123');
        $sheet->setCellValue('H2', '1');
        $sheet->setCellValue('I2', 'Nama Ayah Contoh');
        $sheet->setCellValue('J2', 'Nama Ibu Contoh');
        $sheet->setCellValue('K2', '081234567890');

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Template_Import_Siswa.xlsx';

        // Output to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function process()
    {
        $siswaModel = new SiswaModel();
        $userModel = new UserModel();
        $kelasModel = new KelasModel();
        $db = \Config\Database::connect();

        // Validate file upload
        $file = $this->request->getFile('excel_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ditemukan.');
        }

        $ext = $file->getClientExtension();
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->with('error', 'Format file harus Excel (xlsx/xls) atau CSV.');
        }

        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            $successCount = 0;
            $failedCount = 0;
            $skippedDuplicateCount = 0;
            $logs = [];

            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $nis = trim((string) $sheet->getCell('A' . $row)->getValue());
                $nisn = trim((string) $sheet->getCell('B' . $row)->getValue());
                $nama_siswa = trim((string) $sheet->getCell('C' . $row)->getValue());
                $jenis_kelamin = strtoupper(trim((string) $sheet->getCell('D' . $row)->getValue()));
                $tempat_lahir = trim((string) $sheet->getCell('E' . $row)->getValue());
                $tanggal_lahir = $sheet->getCell('F' . $row)->getValue();
                $alamat = trim((string) $sheet->getCell('G' . $row)->getValue());
                $id_kelas = trim((string) $sheet->getCell('H' . $row)->getValue());
                $nama_ayah = trim((string) $sheet->getCell('I' . $row)->getValue());
                $nama_ibu = trim((string) $sheet->getCell('J' . $row)->getValue());
                $no_telp_ortu = trim((string) $sheet->getCell('K' . $row)->getValue());

                // Skip empty rows
                if (empty($nis) && empty($nama_siswa)) {
                    continue;
                }

                // Validate required fields
                if (empty($nis) || empty($nama_siswa) || empty($id_kelas)) {
                    $logs[] = "Baris $row: Gagal - NIS, Nama Siswa, atau ID Kelas kosong.";
                    $failedCount++;
                    continue;
                }

                if (!in_array($jenis_kelamin, ['L', 'P'], true)) {
                    $logs[] = "Baris $row: Gagal - Jenis kelamin harus L atau P.";
                    $failedCount++;
                    continue;
                }

                if (!$kelasModel->find((int) $id_kelas)) {
                    $logs[] = "Baris $row: Gagal - ID Kelas '$id_kelas' tidak ditemukan.";
                    $failedCount++;
                    continue;
                }

                // Check for duplicate NIS
                if ($siswaModel->where('nis', $nis)->first()) {
                    $logs[] = "Baris $row: Dilewati - NIS '$nis' sudah ada di Data Siswa.";
                    $skippedDuplicateCount++;
                    continue;
                }

                // Format tanggal_lahir if it's a date object
                if ($tanggal_lahir instanceof \PhpOffice\PhpSpreadsheet\Shared\Date) {
                    $tanggal_lahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal_lahir)->format('Y-m-d');
                } elseif (is_numeric($tanggal_lahir)) {
                    $tanggal_lahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal_lahir)->format('Y-m-d');
                } elseif (is_string($tanggal_lahir) && !empty($tanggal_lahir)) {
                    // FIX BUG-11: Handle string date format (e.g., "1998-05-15")
                    try {
                        $tanggal_lahir = date('Y-m-d', strtotime($tanggal_lahir));
                    } catch (\Exception $e) {
                        $tanggal_lahir = null; // Set to null if conversion fails
                    }
                }

                $db->transStart();

                try {
                    $parentUsername = 'ortu_' . $nis;
                    $parentPassword = password_hash((string) $nis, PASSWORD_DEFAULT);
                    $existingParent = $userModel->where('username', $parentUsername)->first();

                    if ($existingParent) {
                        $id_user_ortu = $existingParent['id_user'];
                    } else {
                        $waliName = !empty($nama_ayah) ? $nama_ayah : (!empty($nama_ibu) ? $nama_ibu : 'Wali Siswa');
                        $userModel->insert([
                            'username' => $parentUsername,
                            'password' => $parentPassword,
                            'nama_lengkap' => $waliName,
                            'no_telp' => $no_telp_ortu,
                            'level' => 'orang_tua',
                            'status' => 'aktif',
                        ]);
                        $id_user_ortu = $userModel->getInsertID();
                    }

                    // Insert siswa
                    $siswaData = [
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'password' => password_hash((string) $nis, PASSWORD_DEFAULT),
                        'nama_siswa' => $nama_siswa,
                        'jenis_kelamin' => $jenis_kelamin,
                        'tempat_lahir' => $tempat_lahir,
                        'tanggal_lahir' => $tanggal_lahir,
                        'alamat' => $alamat,
                        'id_kelas' => (int) $id_kelas,
                        'id_user_ortu' => $id_user_ortu,
                        'nama_ayah' => $nama_ayah,
                        'nama_ibu' => $nama_ibu,
                        'no_telp_ortu' => $no_telp_ortu,
                        'status' => 'aktif'
                    ];

                    $siswaModel->insert($siswaData);

                    $db->transComplete();

                    if ($db->transStatus() === FALSE) {
                        $logs[] = "Baris $row: Gagal - Error database transaksi.";
                        $failedCount++;
                    } else {
                        $logs[] = "Baris $row: Berhasil - Siswa '$nama_siswa' dengan NIS '$nis' berhasil diimpor dan langsung masuk ke Data Siswa.";
                        $successCount++;
                    }

                } catch (\Exception $e) {
                    $db->transRollback();
                    $logs[] = "Baris $row: Gagal - " . $e->getMessage();
                    $failedCount++;
                }
            }

            // Prepare summary
            $summary = [
                'success' => $successCount,
                'failed' => $failedCount,
                'skipped_duplicate' => $skippedDuplicateCount,
                'total' => $highestRow - 1,
                'logs' => $logs
            ];

            session()->setFlashdata('import_result', $summary);
            return redirect()->to(base_url('admin/import'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error saat membaca file: ' . $e->getMessage());
        }
    }
}
