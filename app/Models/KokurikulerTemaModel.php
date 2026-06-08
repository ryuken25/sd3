<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Facade tipis ke `master_referensi` (jenis='koko_tema') pasca konsolidasi
 * Phase 2. Class name + findForKelasTa() signature TETAP supaya
 * RaporDataLoader & WaliKelas controller tidak berubah.
 */
class KokurikulerTemaModel extends Model
{
    protected $table         = 'master_referensi';
    protected $primaryKey    = 'id_referensi';
    protected $allowedFields = ['nama_tema', 'id_tahun_ajaran', 'id_kelas', 'narasi_pembuka'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert  = ['injectJenis'];

    protected function injectJenis(array $data): array
    {
        $data['data']['jenis'] = 'koko_tema';
        return $data;
    }

    private function baseSelect(): string
    {
        return 'id_referensi AS id_tema, nama_tema, id_tahun_ajaran, id_kelas, narasi_pembuka, created_at, updated_at';
    }

    public function findForKelasTa(int $idKelas, int $idTa): ?array
    {
        return $this->builder()
            ->where('jenis', 'koko_tema')
            ->where('id_kelas', $idKelas)
            ->where('id_tahun_ajaran', $idTa)
            ->select($this->baseSelect())
            ->get()->getRowArray();
    }

    public function find($id = null)
    {
        if ($id === null) {
            return $this->findAll();
        }
        return $this->builder()
            ->where('jenis', 'koko_tema')
            ->where('id_referensi', (int) $id)
            ->select($this->baseSelect())
            ->get()->getRowArray();
    }

    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $b = $this->builder()
            ->where('jenis', 'koko_tema')
            ->select($this->baseSelect());
        if ($limit !== null && $limit > 0) {
            $b->limit($limit, $offset);
        }
        return $b->get()->getResultArray();
    }

    public function update($id = null, $data = null): bool
    {
        if ($id === null) {
            return parent::update($id, $data);
        }
        if (is_array($data)) {
            unset($data['jenis']);
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return $this->builder()
            ->where('jenis', 'koko_tema')
            ->where('id_referensi', (int) $id)
            ->update($data);
    }

    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            return $this->builder()
                ->where('jenis', 'koko_tema')
                ->where('id_referensi', (int) $id)
                ->delete();
        }
        return parent::delete($id, $purge);
    }
}
