<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaEkstrakurikulerModel extends Model
{
    protected $table         = 'siswa_ekstrakurikuler';
    protected $primaryKey    = 'id_siswa_ekskul';
    protected $allowedFields = ['id_siswa', 'id_ekskul', 'id_tahun_ajaran', 'keterangan'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findForSiswaTa(int $idSiswa, int $idTa): array
    {
        return $this->select('siswa_ekstrakurikuler.*, master_ekstrakurikuler.nama')
            ->join('master_ekstrakurikuler', 'master_ekstrakurikuler.id_ekskul = siswa_ekstrakurikuler.id_ekskul')
            ->where('siswa_ekstrakurikuler.id_siswa', $idSiswa)
            ->where('siswa_ekstrakurikuler.id_tahun_ajaran', $idTa)
            ->orderBy('master_ekstrakurikuler.id_ekskul', 'ASC')
            ->findAll();
    }
}
