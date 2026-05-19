<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiCapaianKompetensiModel extends Model
{
    protected $table         = 'nilai_capaian_kompetensi';
    protected $primaryKey    = 'id_nilai_cp';
    protected $allowedFields = ['id_nilai_akhir', 'master_cp_id', 'deskripsi_custom', 'status'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findForNilaiAkhir(int $idNilaiAkhir): array
    {
        return $this->where('id_nilai_akhir', $idNilaiAkhir)->findAll();
    }

    /**
     * Untuk satu nilai_akhir, kembalikan list CP dengan deskripsi yang efektif
     * (deskripsi master kalau master_cp_id != null, atau deskripsi_custom).
     * Hasil siap dipakai oleh RaporNarrativeService.
     */
    public function listWithDeskripsi(int $idNilaiAkhir): array
    {
        $rows = $this->select('nilai_capaian_kompetensi.*, master_capaian_pembelajaran.deskripsi AS master_deskripsi')
            ->join('master_capaian_pembelajaran', 'master_capaian_pembelajaran.id_master_cp = nilai_capaian_kompetensi.master_cp_id', 'left')
            ->where('nilai_capaian_kompetensi.id_nilai_akhir', $idNilaiAkhir)
            ->orderBy('nilai_capaian_kompetensi.id_nilai_cp', 'ASC')
            ->findAll();

        foreach ($rows as &$r) {
            $r['deskripsi'] = $r['master_deskripsi'] ?: ($r['deskripsi_custom'] ?? '');
        }
        return $rows;
    }
}
