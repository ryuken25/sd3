<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiAkhirModel extends Model
{
    protected $table         = 'nilai';
    protected $primaryKey    = 'id_nilai';
    protected $allowedFields = [
        'id_siswa',
        'id_mapel',
        'id_tahun_ajaran',
        'nilai_akhir',
        'nilai_huruf',
        'status_kelulusan',
        'catatan_remedial',
        'flag_borderline_75',
        'narasi',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
