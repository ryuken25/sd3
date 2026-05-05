<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';
    protected $allowedFields = ['nama_kelas', 'tingkat', 'wali_kelas'];
    protected $useTimestamps = false; // FIX BUG-09: Table doesn't have updated_at column
}
