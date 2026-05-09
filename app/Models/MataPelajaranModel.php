<?php

namespace App\Models;

use CodeIgniter\Model;

class MataPelajaranModel extends Model
{
    protected $table = 'mata_pelajaran';
    protected $primaryKey = 'id_mapel';
    protected $allowedFields = ['kode_mapel', 'nama_mapel', 'kelompok'];
    protected $useTimestamps = false; // FIX BUG-09: Table doesn't have updated_at column

    public function getWithClasses(?int $idKelas = null): array
    {
        $builder = $this->select("mata_pelajaran.*, GROUP_CONCAT(kelas.nama_kelas ORDER BY kelas.tingkat ASC, kelas.nama_kelas ASC SEPARATOR ', ') AS daftar_kelas, GROUP_CONCAT(kelas.id_kelas ORDER BY kelas.tingkat ASC, kelas.nama_kelas ASC SEPARATOR ',') AS kelas_ids, GROUP_CONCAT(CONCAT(kelas.nama_kelas, ': ', COALESCE(users.nama_lengkap, 'Belum diatur')) ORDER BY kelas.tingkat ASC, kelas.nama_kelas ASC SEPARATOR '||') AS daftar_pengampu, MIN(kelas.tingkat) AS tingkat_urut", false)
            ->join('mapel_kelas', 'mapel_kelas.id_mapel = mata_pelajaran.id_mapel', 'left')
            ->join('kelas', 'kelas.id_kelas = mapel_kelas.id_kelas', 'left')
            ->join('users', 'users.id_user = mapel_kelas.id_guru', 'left')
            ->groupBy('mata_pelajaran.id_mapel')
            ->orderBy('tingkat_urut', 'ASC')
            ->orderBy('mata_pelajaran.kelompok', 'ASC')
            ->orderBy('mata_pelajaran.nama_mapel', 'ASC');

        if ($idKelas !== null && $idKelas > 0) {
            $builder->where('mapel_kelas.id_kelas', $idKelas);
        }

        return $builder->findAll();
    }

    public function getByClass(int $idKelas): array
    {
        return $this->select('mata_pelajaran.*')
            ->join('mapel_kelas', 'mapel_kelas.id_mapel = mata_pelajaran.id_mapel')
            ->where('mapel_kelas.id_kelas', $idKelas)
            ->orderBy('mata_pelajaran.kelompok', 'ASC')
            ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
            ->findAll();
    }

    public function isAssignedToClass(int $idMapel, int $idKelas): bool
    {
        if ($idMapel <= 0 || $idKelas <= 0) {
            return false;
        }

        return $this->db->table('mapel_kelas')
            ->where('id_mapel', $idMapel)
            ->where('id_kelas', $idKelas)
            ->countAllResults() > 0;
    }
}
