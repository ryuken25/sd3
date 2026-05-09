<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $allowedFields = ['username', 'password', 'nama_lengkap', 'no_telp', 'level', 'status'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveTeachers(): array
    {
        return $this->where('level', 'guru')
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap', 'ASC')
            ->findAll();
    }
}
