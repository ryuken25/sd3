<?php

namespace App\Models;

use CodeIgniter\Model;

class RemedialModel extends Model
{
    protected $table = 'remedial';
    protected $primaryKey = 'id_remedial';
    protected $allowedFields = [
        'id_nilai_akhir', 'tindak_lanjut', 'status_remedial'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
