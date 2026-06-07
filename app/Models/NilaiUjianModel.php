<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiUjianModel extends Model
{
    protected $table         = 'nilai';
    protected $primaryKey    = 'id_nilai';
    protected $allowedFields = [
        'id_siswa',
        'id_mapel',
        'id_tahun_ajaran',
        'nilai_uts',
        'nilai_uas',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
