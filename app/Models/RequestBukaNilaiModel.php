<?php

namespace App\Models;

use CodeIgniter\Model;

class RequestBukaNilaiModel extends Model
{
    protected $table = 'request_buka_nilai';
    protected $primaryKey = 'id_request';
    protected $allowedFields = [
        'id_guru',
        'id_tahun_ajaran',
        'id_kelas',
        'id_mapel',
        'alasan',
        'status',
        'tanggal_akses',
        'approved_by',
        'catatan_admin'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all requests with guru and tahun ajaran information
     */
    public function getRequestsWithDetails()
    {
        return $this->select('request_buka_nilai.*, users.nama_lengkap as nama_guru, tahun_ajaran.tahun_ajaran, tahun_ajaran.semester, kelas.nama_kelas, mata_pelajaran.nama_mapel')
            ->join('users', 'users.id_user = request_buka_nilai.id_guru', 'left')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = request_buka_nilai.id_tahun_ajaran', 'left')
            ->join('kelas', 'kelas.id_kelas = request_buka_nilai.id_kelas', 'left')
            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = request_buka_nilai.id_mapel', 'left')
            ->orderBy('request_buka_nilai.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get pending requests
     */
    public function getPendingRequests()
    {
        return $this->select('request_buka_nilai.*, users.nama_lengkap as nama_guru, tahun_ajaran.tahun_ajaran, tahun_ajaran.semester, kelas.nama_kelas, mata_pelajaran.nama_mapel')
            ->join('users', 'users.id_user = request_buka_nilai.id_guru', 'left')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = request_buka_nilai.id_tahun_ajaran', 'left')
            ->join('kelas', 'kelas.id_kelas = request_buka_nilai.id_kelas', 'left')
            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = request_buka_nilai.id_mapel', 'left')
            ->where('request_buka_nilai.status', 'pending')
            ->orderBy('request_buka_nilai.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Check if guru has active approved access
     */
    public function hasActiveAccess($id_guru, $id_tahun_ajaran, ?int $idKelas = null, ?int $idMapel = null)
    {
        $today = date('Y-m-d');

        $builder = $this->where('id_guru', $id_guru)
            ->where('id_tahun_ajaran', $id_tahun_ajaran)
            ->where('status', 'disetujui')
            ->where('tanggal_akses >=', $today);

        if ($idKelas !== null && $idKelas > 0) {
            $builder->where('id_kelas', $idKelas);
        }

        if ($idMapel !== null && $idMapel > 0) {
            $builder->where('id_mapel', $idMapel);
        }

        return $builder->first();
    }
}
