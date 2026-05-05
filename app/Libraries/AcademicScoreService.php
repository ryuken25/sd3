<?php

namespace App\Libraries;

class AcademicScoreService
{
    public function normalizeScore($value): ?float
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    public function calculateDailyAverage($nilaiTugas, $nilaiUlangan): ?float
    {
        $tugas = $this->normalizeScore($nilaiTugas);
        $ulangan = $this->normalizeScore($nilaiUlangan);

        if ($tugas === null || $ulangan === null) {
            return null;
        }

        return round(($tugas + $ulangan) / 2, 2);
    }

    public function calculateFinalScore($nilaiTugas, $nilaiUlangan, $nilaiUts, $nilaiUas): float
    {
        $rataRataHarian = $this->calculateDailyAverage($nilaiTugas, $nilaiUlangan) ?? 0.0;
        $uts = $this->normalizeScore($nilaiUts) ?? 0.0;
        $uas = $this->normalizeScore($nilaiUas) ?? 0.0;

        return round(($rataRataHarian * 0.40) + ($uts * 0.30) + ($uas * 0.30), 2);
    }

    public function determineLetter(float $nilaiAkhir): string
    {
        if ($nilaiAkhir >= 90) {
            return 'A';
        }
        if ($nilaiAkhir >= 80) {
            return 'B';
        }
        if ($nilaiAkhir >= 70) {
            return 'C';
        }
        if ($nilaiAkhir >= 60) {
            return 'D';
        }

        return 'E';
    }

    public function determineStatus(float $nilaiAkhir, float $kkm): string
    {
        return $nilaiAkhir >= $kkm ? 'Tuntas' : 'Remedial';
    }
}
