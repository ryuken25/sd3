<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiAkhirModel extends Model
{
    protected $table = 'nilai_akhir';
    protected $primaryKey = 'id_nilai_akhir';
    protected $allowedFields = [
        'id_siswa', 'id_mapel', 'id_tahun_ajaran',
        'nilai_akhir', 'nilai_huruf', 'status_kelulusan'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
