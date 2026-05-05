<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';
    protected $allowedFields = [
        'nis',
        'nisn',
        'password',
        'nama_siswa',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'id_kelas',
        'id_user_ortu',
        'nama_ayah',
        'nama_ibu',
        'no_telp_ortu',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByParentUser(int $idUser): array
    {
        return $this->where('id_user_ortu', $idUser)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'ASC')
            ->findAll();
    }

    public function isOwnedByParent(int $idSiswa, int $idUser): bool
    {
        return $this->where('id_siswa', $idSiswa)
            ->where('id_user_ortu', $idUser)
            ->countAllResults() > 0;
    }
}
