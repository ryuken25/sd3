<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Facade tipis ke tabel `master_referensi` (jenis='dimensi') pasca konsolidasi
 * 17 -> 14 tabel. Class name, public method, dan allowedFields TETAP SAMA
 * supaya call-site tidak berubah. SELECT mengaliaskan id_referensi AS
 * id_dimensi agar konsumen yang baca $row['id_dimensi'] tetap jalan.
 */
class MasterDimensiPancasilaModel extends Model
{
    protected $table         = 'master_referensi';
    protected $primaryKey    = 'id_referensi';
    protected $allowedFields = ['nama_dimensi', 'urutan'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Auto-inject jenis pada setiap insert. */
    protected $beforeInsert  = ['injectJenis'];

    protected function injectJenis(array $data): array
    {
        $data['data']['jenis'] = 'dimensi';
        return $data;
    }

    private function baseSelect(): string
    {
        return 'id_referensi AS id_dimensi, nama_dimensi, urutan, created_at, updated_at';
    }

    public function findOrdered(): array
    {
        return $this->builder()
            ->where('jenis', 'dimensi')
            ->select($this->baseSelect())
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    public function find($id = null)
    {
        if ($id === null) {
            return $this->findAll();
        }
        return $this->builder()
            ->where('jenis', 'dimensi')
            ->where('id_referensi', (int) $id)
            ->select($this->baseSelect())
            ->get()->getRowArray();
    }

    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $b = $this->builder()
            ->where('jenis', 'dimensi')
            ->select($this->baseSelect())
            ->orderBy('urutan', 'ASC');
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
            ->where('jenis', 'dimensi')
            ->where('id_referensi', (int) $id)
            ->update($data);
    }

    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            return $this->builder()
                ->where('jenis', 'dimensi')
                ->where('id_referensi', (int) $id)
                ->delete();
        }
        return parent::delete($id, $purge);
    }
}
