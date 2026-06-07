<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiSiswaModel extends Model
{
    protected $table         = 'nilai';
    protected $primaryKey    = 'id_nilai';
    protected $allowedFields = [
        'id_siswa',
        'id_mapel',
        'id_tahun_ajaran',
        'nilai_tugas',
        'nilai_ulangan',
        'rata_rata_harian',
        'nilai_uts',
        'nilai_uas',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByKey(int $idSiswa, int $idMapel, int $idTahunAjaran): ?array
    {
        return $this->where([
            'id_siswa'        => $idSiswa,
            'id_mapel'        => $idMapel,
            'id_tahun_ajaran' => $idTahunAjaran,
        ])->first();
    }

    public function upsertByKey(array $payload): bool
    {
        $existing = $this->findByKey(
            (int) $payload['id_siswa'],
            (int) $payload['id_mapel'],
            (int) $payload['id_tahun_ajaran'],
        );

        if ($existing) {
            return (bool) $this->update($existing['id_nilai'], $payload);
        }
        return (bool) $this->insert($payload);
    }
}
