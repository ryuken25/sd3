<?php

namespace App\Models;

use CodeIgniter\Model;

class KokurikulerTemaModel extends Model
{
    protected $table         = 'kokurikuler_tema';
    protected $primaryKey    = 'id_tema';
    protected $allowedFields = ['nama_tema', 'id_tahun_ajaran', 'id_kelas', 'narasi_pembuka'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findForKelasTa(int $idKelas, int $idTa): ?array
    {
        return $this->where('id_kelas', $idKelas)->where('id_tahun_ajaran', $idTa)->first();
    }
}
