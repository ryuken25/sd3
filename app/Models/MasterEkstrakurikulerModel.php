<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterEkstrakurikulerModel extends Model
{
    protected $table         = 'master_ekstrakurikuler';
    protected $primaryKey    = 'id_ekskul';
    protected $allowedFields = ['nama', 'deskripsi_default', 'aktif', 'wajib'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findActive(): array
    {
        // Ekskul wajib di urutan atas, lalu sisanya.
        return $this->where('aktif', 1)
            ->orderBy('wajib', 'DESC')
            ->orderBy('id_ekskul', 'ASC')
            ->findAll();
    }
}
