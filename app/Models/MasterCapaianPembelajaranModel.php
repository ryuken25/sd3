<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Facade tipis ke `master_referensi` (jenis='cp') pasca konsolidasi Phase 2.
 * Class name + 3 public method (findForMapel, getBandMap, findBand) + allowedFields
 * TETAP SAMA supaya call-site di controller Guru\CapaianKompetensi /
 * TemplateCapaian / seeder tidak berubah.
 */
class MasterCapaianPembelajaranModel extends Model
{
    protected $table         = 'master_referensi';
    protected $primaryKey    = 'id_referensi';
    protected $allowedFields = ['id_mapel', 'fase', 'semester', 'predikat', 'deskripsi', 'aktif'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert  = ['injectJenis'];

    protected function injectJenis(array $data): array
    {
        $data['data']['jenis'] = 'cp';
        return $data;
    }

    private function baseSelect(): string
    {
        return 'id_referensi AS id_master_cp, id_mapel, fase, semester, predikat, deskripsi, aktif, created_at, updated_at';
    }

    /** Daftar CP per mapel/fase/semester (predikat boleh NULL untuk legacy per-kalimat). */
    public function findForMapel(int $idMapel, string $fase, string $semester): array
    {
        return $this->builder()
            ->where('jenis', 'cp')
            ->where('id_mapel', $idMapel)
            ->where('fase', $fase)
            ->where('semester', $semester)
            ->where('aktif', 1)
            ->select($this->baseSelect())
            ->orderBy('id_referensi', 'ASC')
            ->get()->getResultArray();
    }

    /** Peta narasi template per band predikat A/B/C/D. */
    public function getBandMap(int $idMapel, string $fase, string $semester): array
    {
        $map = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];

        $rows = $this->builder()
            ->where('jenis', 'cp')
            ->where('id_mapel', $idMapel)
            ->where('fase', $fase)
            ->where('semester', $semester)
            ->where('aktif', 1)
            ->where('predikat IS NOT NULL', null, false)
            ->select($this->baseSelect())
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $p = (string) ($r['predikat'] ?? '');
            if (isset($map[$p])) {
                $map[$p] = (string) $r['deskripsi'];
            }
        }
        return $map;
    }

    /** Cari baris band tertentu (untuk upsert di saveBands). */
    public function findBand(int $idMapel, string $fase, string $semester, string $predikat): ?array
    {
        return $this->builder()
            ->where('jenis', 'cp')
            ->where('id_mapel', $idMapel)
            ->where('fase', $fase)
            ->where('semester', $semester)
            ->where('predikat', $predikat)
            ->select($this->baseSelect())
            ->get()->getRowArray();
    }

    public function find($id = null)
    {
        if ($id === null) {
            return $this->findAll();
        }
        return $this->builder()
            ->where('jenis', 'cp')
            ->where('id_referensi', (int) $id)
            ->select($this->baseSelect())
            ->get()->getRowArray();
    }

    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $b = $this->builder()
            ->where('jenis', 'cp')
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
            ->where('jenis', 'cp')
            ->where('id_referensi', (int) $id)
            ->update($data);
    }

    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            return $this->builder()
                ->where('jenis', 'cp')
                ->where('id_referensi', (int) $id)
                ->delete();
        }
        return parent::delete($id, $purge);
    }
}
