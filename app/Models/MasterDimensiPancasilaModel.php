<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterDimensiPancasilaModel extends Model
{
    protected $table         = 'master_dimensi_pancasila';
    protected $primaryKey    = 'id_dimensi';
    protected $allowedFields = ['nama_dimensi', 'urutan'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findOrdered(): array
    {
        return $this->orderBy('urutan', 'ASC')->findAll();
    }
}
