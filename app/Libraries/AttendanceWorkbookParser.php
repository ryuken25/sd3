<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceWorkbookParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $result = [];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow();
            $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
            $metaText = trim((string) $worksheet->getCell('A4')->getFormattedValue());
            $meta = $this->extractMeta($metaText);

            $students = [];
            for ($row = 10; $row <= $highestRow; $row++) {
                $marker = strtoupper(trim((string) $worksheet->getCellByColumnAndRow(1, $row)->getFormattedValue()));
                if (in_array($marker, ['LAKI-LAKI', 'PEREMPUAN', 'JUMLAH'], true)) {
                    break;
                }

                $name = trim((string) $worksheet->getCellByColumnAndRow(3, $row)->getFormattedValue());
                if ($name === '') {
                    continue;
                }

                $attendanceCells = [];
                for ($column = 5; $column <= $highestColumnIndex; $column++) {
                    $attendanceCells[] = (string) $worksheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
                }

                $identity = $this->parseIdentity((string) $worksheet->getCellByColumnAndRow(2, $row)->getFormattedValue());
                $counts = $this->countAttendance($attendanceCells);

                $students[] = [
                    'nomor_urut' => trim((string) $worksheet->getCellByColumnAndRow(1, $row)->getFormattedValue()),
                    'nisn' => $identity['nisn'],
                    'nis' => $identity['nis'],
                    'nama_siswa' => $name,
                    'jenis_kelamin' => trim((string) $worksheet->getCellByColumnAndRow(4, $row)->getFormattedValue()),
                    'sakit' => $counts['sakit'],
                    'izin' => $counts['izin'],
                    'alpa' => $counts['alpa'],
                ];
            }

            $result[] = [
                'sheet_name' => $worksheet->getTitle(),
                'nama_rombel' => $meta['nama_rombel'],
                'tingkat' => $meta['tingkat'],
                'semester' => $meta['semester'],
                'wali_kelas' => $meta['wali_kelas'],
                'students' => $students,
            ];
        }

        return $result;
    }

    /**
     * @return array{nama_rombel:string, tingkat:int, semester:string, wali_kelas:string}
     */
    private function extractMeta(string $metaText): array
    {
        $namaRombel = '';
        $semester = '';
        $waliKelas = '';
        $tingkat = 0;

        if (preg_match('/Nama\s+Rombel:\s*(.+?)\s*-\s*Semester/i', $metaText, $matches)) {
            $namaRombel = trim($matches[1]);
        }

        if (preg_match('/Semester\s+([^\-]+)/i', $metaText, $matches)) {
            $semester = trim($matches[1]);
        }

        if (preg_match('/Wali\s+Kelas:\s*(.+)$/i', $metaText, $matches)) {
            $waliKelas = trim($matches[1]);
        }

        if (preg_match('/Kelas\s+(\d+)/i', $namaRombel !== '' ? $namaRombel : $metaText, $matches)) {
            $tingkat = (int) $matches[1];
        }

        return [
            'nama_rombel' => $namaRombel,
            'tingkat' => $tingkat,
            'semester' => $semester,
            'wali_kelas' => $waliKelas,
        ];
    }

    /**
     * @return array{nisn:string, nis:string}
     */
    private function parseIdentity(string $raw): array
    {
        $parts = array_map(static fn(string $value): string => trim($value), explode('/', $raw, 2));

        return [
            'nisn' => $parts[0] ?? '',
            'nis' => $parts[1] ?? '',
        ];
    }

    /**
     * @param array<int, string> $cells
     * @return array{sakit:int, izin:int, alpa:int}
     */
    private function countAttendance(array $cells): array
    {
        $summary = [
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
        ];

        foreach ($cells as $value) {
            $normalized = strtoupper(trim($value));
            if ($normalized === '') {
                continue;
            }

            if (in_array($normalized, ['S', 'SAKIT'], true)) {
                $summary['sakit']++;
                continue;
            }

            if (in_array($normalized, ['I', 'IZIN'], true)) {
                $summary['izin']++;
                continue;
            }

            if (in_array($normalized, ['A', 'ALPA'], true)) {
                $summary['alpa']++;
            }
        }

        return $summary;
    }
}
