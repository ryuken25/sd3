<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterTemplateCatatanModel extends Model
{
    protected $table         = 'master_template_catatan';
    protected $primaryKey    = 'id_template';
    protected $allowedFields = ['nama_template', 'isi_template', 'kategori', 'aktif'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findActive(): array
    {
        return $this->where('aktif', 1)->orderBy('id_template', 'ASC')->findAll();
    }
}
