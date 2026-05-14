<?php

namespace App\Services;

use App\Models\TahunAjaranModel;

class AcademicPeriodService
{
    protected TahunAjaranModel $model;

    public function __construct()
    {
        $this->model = new TahunAjaranModel();
    }

    /**
     * Return periode yang sedang aktif.
     * Lempar RuntimeException kalau tidak ada — jangan return default silently.
     */
    public function getActivePeriod(): array
    {
        $row = $this->model->where('aktif', 'aktif')->first();

        if (!$row) {
            throw new \RuntimeException(
                'Tidak ada Tahun Ajaran aktif. Admin wajib mengaktifkan tahun ajaran terlebih dahulu di menu Tahun Ajaran.'
            );
        }

        return [
            'id_tahun_ajaran' => (int) $row['id_tahun_ajaran'],
            'tahun_ajaran'    => $row['tahun_ajaran'],
            'semester'        => $row['semester'],
            'status_pengisian' => $row['status_pengisian'],
            'label'           => $row['tahun_ajaran'] . ' - Semester ' . $row['semester'],
        ];
    }

    /**
     * Cek apakah id_tahun_ajaran tertentu adalah periode aktif.
     */
    public function isActive(int $idTahunAjaran): bool
    {
        $row = $this->model->find($idTahunAjaran);
        return $row && $row['aktif'] === 'aktif';
    }

    /**
     * Generate hash untuk validasi template Excel.
     */
    public function makeTemplateHash(int $idTahunAjaran, string $semester): string
    {
        return hash('sha256', $idTahunAjaran . ':' . $semester . ':' . env('encryption.key', 'sd3mekarsari'));
    }

    /**
     * Validasi metadata Z1 dari template Excel.
     * Format cell Z1: "id_tahun_ajaran:semester:hash"
     */
    public function validateTemplateHash(?string $meta, array $period): bool
    {
        if (empty($meta)) {
            return false;
        }

        $parts = explode(':', $meta, 3);
        if (count($parts) !== 3) {
            return false;
        }

        [$idStr, $semester, $hash] = $parts;
        $idFromMeta = (int) $idStr;

        if ($idFromMeta !== $period['id_tahun_ajaran'] || $semester !== $period['semester']) {
            return false;
        }

        $expected = $this->makeTemplateHash($idFromMeta, $semester);
        return hash_equals($expected, $hash);
    }
}
