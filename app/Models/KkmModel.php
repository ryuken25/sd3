<?php

namespace App\Models;

use CodeIgniter\Model;

class KkmModel extends Model
{
    protected $table = 'kkm';
    protected $primaryKey = 'id_kkm';
    protected $allowedFields = ['id_mapel', 'id_kelas', 'id_tahun_ajaran', 'nilai_kkm'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
