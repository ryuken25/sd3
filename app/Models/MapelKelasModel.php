<?php

namespace App\Models;

use CodeIgniter\Model;

class MapelKelasModel extends Model
{
    protected $table = 'mapel_kelas';
    protected $primaryKey = 'id_mapel_kelas';
    protected $allowedFields = ['id_mapel', 'id_kelas', 'id_guru'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function assignIfMissing(int $idMapel, int $idKelas, ?int $idGuru = null): void
    {
        $exists = $this->where('id_mapel', $idMapel)
            ->where('id_kelas', $idKelas)
            ->first();

        if (!$exists) {
            $this->insert([
                'id_mapel' => $idMapel,
                'id_kelas' => $idKelas,
                'id_guru' => $idGuru,
            ]);
            return;
        }

        $this->update((int) $exists[$this->primaryKey], [
            'id_guru' => $idGuru,
        ]);
    }

    public function getAssignmentsByMapel(?int $idMapel = null, ?int $idKelas = null): array
    {
        $builder = $this->select('mapel_kelas.*, kelas.nama_kelas, kelas.tingkat, users.nama_lengkap AS nama_guru')
            ->join('kelas', 'kelas.id_kelas = mapel_kelas.id_kelas', 'left')
            ->join('users', 'users.id_user = mapel_kelas.id_guru', 'left')
            ->orderBy('kelas.tingkat', 'ASC')
            ->orderBy('kelas.nama_kelas', 'ASC');

        if ($idMapel !== null && $idMapel > 0) {
            $builder->where('mapel_kelas.id_mapel', $idMapel);
        }

        if ($idKelas !== null && $idKelas > 0) {
            $builder->where('mapel_kelas.id_kelas', $idKelas);
        }

        $rows = $builder->findAll();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['id_mapel']][] = $row;
        }

        return $grouped;
    }

    public function syncMapelClasses(int $idMapel, array $kelasIds, array $guruByKelas = []): void
    {
        $kelasIds = array_values(array_unique(array_filter(array_map('intval', $kelasIds))));

        if (empty($kelasIds)) {
            $this->where('id_mapel', $idMapel)->delete();
            return;
        }

        $this->where('id_mapel', $idMapel)
            ->whereNotIn('id_kelas', $kelasIds)
            ->delete();

        foreach ($kelasIds as $idKelas) {
            $idGuru = isset($guruByKelas[$idKelas]) && (int) $guruByKelas[$idKelas] > 0
                ? (int) $guruByKelas[$idKelas]
                : null;

            $this->assignIfMissing($idMapel, $idKelas, $idGuru);
        }
    }
}
