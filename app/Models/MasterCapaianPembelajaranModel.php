<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterCapaianPembelajaranModel extends Model
{
    protected $table         = 'master_capaian_pembelajaran';
    protected $primaryKey    = 'id_master_cp';
    protected $allowedFields = ['id_mapel', 'fase', 'semester', 'deskripsi', 'aktif'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findForMapel(int $idMapel, string $fase, string $semester): array
    {
        return $this->where('id_mapel', $idMapel)
            ->where('fase', $fase)
            ->where('semester', $semester)
            ->where('aktif', 1)
            ->orderBy('id_master_cp', 'ASC')
            ->findAll();
    }
}
