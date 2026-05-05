<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiHarianModel extends Model
{
    protected $table = 'nilai_siswa';
    protected $primaryKey = 'id_nilai_siswa';
    protected $allowedFields = [
        'id_siswa',
        'id_mapel',
        'id_tahun_ajaran',
        'nilai_tugas',
        'nilai_ulangan',
        'rata_rata_harian'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
