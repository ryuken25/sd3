<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;
use App\Models\UserModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Import extends BaseController
{
    public function index()
    {
        $kelasModel       = new KelasModel();
        $tahunAjaranModel = new TahunAjaranModel();

        $activeTahunAjaran = $tahunAjaranModel
            ->where('aktif', 'aktif')
            ->orderBy('id_tahun_ajaran', 'DESC')
            ->first();

        $data = [
            'title'               => 'Import Data Siswa',
            'kelas'               => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'tahun_ajaran'        => $tahunAjaranModel
                ->orderBy('tahun_ajaran', 'DESC')
                ->orderBy('semester', 'ASC')
                ->findAll(),
            'active_tahun_ajaran' => $activeTahunAjaran,
        ];

        return view('admin/import/index', $data);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

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

        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4CAF50');

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

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Template_Import_Siswa.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function process()
    {
        $siswaModel       = new SiswaModel();
        $userModel        = new UserModel();
        $kelasModel       = new KelasModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $db               = \Config\Database::connect();

        // Tahun Ajaran wajib dipilih
        $idTahunAjaran = (int) $this->request->getPost('id_tahun_ajaran');
        if ($idTahunAjaran <= 0) {
            return redirect()->back()->with('error', 'Tahun Ajaran wajib dipilih sebelum import.');
        }

        $tahunAjaran = $tahunAjaranModel->find($idTahunAjaran);
        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Tahun Ajaran tidak ditemukan.');
        }

        // Tolak import ke TA yang dikunci
        if (isset($tahunAjaran['status_pengisian']) && $tahunAjaran['status_pengisian'] === 'Kunci') {
            return redirect()->back()->with(
                'error',
                'Tahun Ajaran "' . $tahunAjaran['tahun_ajaran'] . ' ' . $tahunAjaran['semester'] . '" '
                . 'berstatus Kunci. Buka pengisian dulu di menu Tahun Ajaran sebelum import.'
            );
        }

        $file = $this->request->getFile('excel_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ditemukan.');
        }

        $ext = $file->getClientExtension();
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->with('error', 'Format file harus Excel (xlsx/xls) atau CSV.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet       = $spreadsheet->getActiveSheet();
            $highestRow  = $sheet->getHighestRow();

            $successCount          = 0;
            $failedCount           = 0;
            $skippedDuplicateCount = 0;
            $logs                  = [];

            for ($row = 2; $row <= $highestRow; $row++) {
                $nis           = trim((string) $sheet->getCell('A' . $row)->getValue());
                $nisn          = trim((string) $sheet->getCell('B' . $row)->getValue());
                $nama_siswa    = trim((string) $sheet->getCell('C' . $row)->getValue());
                $jenis_kelamin = strtoupper(trim((string) $sheet->getCell('D' . $row)->getValue()));
                $tempat_lahir  = trim((string) $sheet->getCell('E' . $row)->getValue());
                $tanggal_lahir = $sheet->getCell('F' . $row)->getValue();
                $alamat        = trim((string) $sheet->getCell('G' . $row)->getValue());
                $id_kelas      = trim((string) $sheet->getCell('H' . $row)->getValue());
                $nama_ayah     = trim((string) $sheet->getCell('I' . $row)->getValue());
                $nama_ibu      = trim((string) $sheet->getCell('J' . $row)->getValue());
                $no_telp_ortu  = trim((string) $sheet->getCell('K' . $row)->getValue());

                if (empty($nis) && empty($nama_siswa)) {
                    continue;
                }

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

                // Cek duplikat per kombinasi (NIS, TA) — bukan global NIS
                $existingSameTa = $siswaModel
                    ->where('nis', $nis)
                    ->where('id_tahun_ajaran', $idTahunAjaran)
                    ->first();

                if ($existingSameTa) {
                    $logs[] = "Baris $row: Dilewati - NIS '$nis' sudah ada untuk TA ini.";
                    $skippedDuplicateCount++;
                    continue;
                }

                // Normalize tanggal_lahir
                if (\is_numeric($tanggal_lahir)) {
                    $tanggal_lahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $tanggal_lahir)->format('Y-m-d');
                } elseif (\is_string($tanggal_lahir) && $tanggal_lahir !== '') {
                    $ts = strtotime($tanggal_lahir);
                    $tanggal_lahir = $ts !== false ? date('Y-m-d', $ts) : null;
                } else {
                    $tanggal_lahir = null;
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
                            'username'     => $parentUsername,
                            'password'     => $parentPassword,
                            'nama_lengkap' => $waliName,
                            'no_telp'      => $no_telp_ortu,
                            'level'        => 'orang_tua',
                            'status'       => 'aktif',
                        ]);
                        $id_user_ortu = $userModel->getInsertID();
                    }

                    $siswaData = [
                        'nis'             => $nis,
                        'nisn'            => $nisn,
                        'password'        => password_hash((string) $nis, PASSWORD_DEFAULT),
                        'nama_siswa'      => $nama_siswa,
                        'jenis_kelamin'   => $jenis_kelamin,
                        'tempat_lahir'    => $tempat_lahir,
                        'tanggal_lahir'   => $tanggal_lahir,
                        'alamat'          => $alamat,
                        'id_kelas'        => (int) $id_kelas,
                        'id_tahun_ajaran' => $idTahunAjaran,
                        'id_user_ortu'    => $id_user_ortu,
                        'nama_ayah'       => $nama_ayah,
                        'nama_ibu'        => $nama_ibu,
                        'no_telp_ortu'    => $no_telp_ortu,
                        'status'          => 'aktif',
                    ];

                    $siswaModel->insert($siswaData);

                    $db->transComplete();

                    if ($db->transStatus() === false) {
                        $logs[] = "Baris $row: Gagal - Error database transaksi.";
                        $failedCount++;
                    } else {
                        $logs[] = "Baris $row: Berhasil - '$nama_siswa' (NIS $nis) masuk TA "
                            . $tahunAjaran['tahun_ajaran'] . ' ' . $tahunAjaran['semester'] . '.';
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $db->transRollback();
                    $logs[] = "Baris $row: Gagal - " . $e->getMessage();
                    $failedCount++;
                }
            }

            $summary = [
                'success'           => $successCount,
                'failed'            => $failedCount,
                'skipped_duplicate' => $skippedDuplicateCount,
                'total'             => $highestRow - 1,
                'logs'              => $logs,
                'tahun_ajaran'      => $tahunAjaran['tahun_ajaran'] . ' ' . $tahunAjaran['semester'],
            ];

            session()->setFlashdata('import_result', $summary);
            return redirect()->to(base_url('admin/import'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error saat membaca file: ' . $e->getMessage());
        }
    }
}
