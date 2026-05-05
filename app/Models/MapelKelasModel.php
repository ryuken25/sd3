<?php

namespace App\Models;

use CodeIgniter\Model;

class MapelKelasModel extends Model
{
    protected $table = 'mapel_kelas';
    protected $primaryKey = 'id_mapel_kelas';
    protected $allowedFields = ['id_mapel', 'id_kelas'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function assignIfMissing(int $idMapel, int $idKelas): void
    {
        $exists = $this->where('id_mapel', $idMapel)
            ->where('id_kelas', $idKelas)
            ->first();

        if (!$exists) {
            $this->insert([
                'id_mapel' => $idMapel,
                'id_kelas' => $idKelas,
            ]);
        }
    }

    public function syncMapelClasses(int $idMapel, array $kelasIds): void
    {
        $kelasIds = array_values(array_unique(array_filter(array_map('intval', $kelasIds))));

        $this->where('id_mapel', $idMapel)->delete();

        foreach ($kelasIds as $idKelas) {
            $this->insert([
                'id_mapel' => $idMapel,
                'id_kelas' => $idKelas,
            ]);
        }
    }
}
