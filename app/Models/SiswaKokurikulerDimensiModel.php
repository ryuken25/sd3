<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaKokurikulerDimensiModel extends Model
{
    protected $table         = 'siswa_kokurikuler_dimensi';
    protected $primaryKey    = 'id_siswa_koko';
    protected $allowedFields = ['id_siswa', 'id_tema', 'id_dimensi', 'subdimensi', 'level'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findForSiswaTema(int $idSiswa, int $idTema): array
    {
        return $this->select('siswa_kokurikuler_dimensi.*, master_dimensi_pancasila.nama_dimensi, master_dimensi_pancasila.urutan')
            ->join('master_dimensi_pancasila', 'master_dimensi_pancasila.id_dimensi = siswa_kokurikuler_dimensi.id_dimensi')
            ->where('siswa_kokurikuler_dimensi.id_siswa', $idSiswa)
            ->where('siswa_kokurikuler_dimensi.id_tema', $idTema)
            ->orderBy('master_dimensi_pancasila.urutan', 'ASC')
            ->findAll();
    }
}
