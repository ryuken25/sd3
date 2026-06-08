<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Facade tipis ke `master_referensi` (jenis='ekskul') pasca konsolidasi.
 * Class name + public method + allowedFields TETAP. SELECT mengalias
 * id_referensi AS id_ekskul agar caller yang baca $row['id_ekskul'] tetap jalan.
 */
class MasterEkstrakurikulerModel extends Model
{
    protected $table         = 'master_referensi';
    protected $primaryKey    = 'id_referensi';
    protected $allowedFields = ['nama', 'deskripsi_default', 'aktif', 'wajib'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert  = ['injectJenis'];

    protected function injectJenis(array $data): array
    {
        $data['data']['jenis'] = 'ekskul';
        return $data;
    }

    private function baseSelect(): string
    {
        return 'id_referensi AS id_ekskul, nama, deskripsi_default, aktif, wajib, created_at, updated_at';
    }

    public function findActive(): array
    {
        // Ekskul wajib di urutan atas, lalu sisanya.
        return $this->builder()
            ->where('jenis', 'ekskul')
            ->where('aktif', 1)
            ->select($this->baseSelect())
            ->orderBy('wajib', 'DESC')
            ->orderBy('id_referensi', 'ASC')
            ->get()->getResultArray();
    }

    public function find($id = null)
    {
        if ($id === null) {
            return $this->findAll();
        }
        return $this->builder()
            ->where('jenis', 'ekskul')
            ->where('id_referensi', (int) $id)
            ->select($this->baseSelect())
            ->get()->getRowArray();
    }

    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $b = $this->builder()
            ->where('jenis', 'ekskul')
            ->select($this->baseSelect())
            ->orderBy('id_referensi', 'ASC');
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
            ->where('jenis', 'ekskul')
            ->where('id_referensi', (int) $id)
            ->update($data);
    }

    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            return $this->builder()
                ->where('jenis', 'ekskul')
                ->where('id_referensi', (int) $id)
                ->delete();
        }
        return parent::delete($id, $purge);
    }
}
