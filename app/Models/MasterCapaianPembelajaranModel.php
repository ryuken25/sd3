<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterCapaianPembelajaranModel extends Model
{
    protected $table         = 'master_capaian_pembelajaran';
    protected $primaryKey    = 'id_master_cp';
    protected $allowedFields = ['id_mapel', 'fase', 'semester', 'predikat', 'deskripsi', 'aktif'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Daftar kalimat lama (per-kalimat, predikat NULL) — masih dipakai sebagai
     * sumber fallback narasi auto (RaporNarrativeService::generateNarasiCP).
     */
    public function findForMapel(int $idMapel, string $fase, string $semester): array
    {
        return $this->where('id_mapel', $idMapel)
            ->where('fase', $fase)
            ->where('semester', $semester)
            ->where('aktif', 1)
            ->orderBy('id_master_cp', 'ASC')
            ->findAll();
    }

    /**
     * Peta narasi template per band predikat untuk satu (mapel, fase, semester).
     * Return: ['A'=>narasi, 'B'=>.., 'C'=>.., 'D'=>..] — band yang belum diisi = ''.
     * Hanya baris aktif & predikat tidak null.
     */
    public function getBandMap(int $idMapel, string $fase, string $semester): array
    {
        $map = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];

        $rows = $this->where('id_mapel', $idMapel)
            ->where('fase', $fase)
            ->where('semester', $semester)
            ->where('aktif', 1)
            ->where('predikat IS NOT NULL')
            ->findAll();

        foreach ($rows as $r) {
            $p = (string) ($r['predikat'] ?? '');
            if (isset($map[$p])) {
                $map[$p] = (string) $r['deskripsi'];
            }
        }
        return $map;
    }

    /**
     * Cari baris band tertentu (untuk upsert di saveBands).
     */
    public function findBand(int $idMapel, string $fase, string $semester, string $predikat): ?array
    {
        return $this->where('id_mapel', $idMapel)
            ->where('fase', $fase)
            ->where('semester', $semester)
            ->where('predikat', $predikat)
            ->first();
    }
}
