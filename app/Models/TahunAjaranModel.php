<?php

namespace App\Models;

use CodeIgniter\Model;

class TahunAjaranModel extends Model
{
    protected $table = 'tahun_ajaran';
    protected $primaryKey = 'id_tahun_ajaran';
    protected $allowedFields = [
        'tahun_ajaran', 'semester', 'aktif', 'status_pengisian',
        'tanggal_mulai', 'tanggal_selesai'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
