<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Facade tipis ke `nilai_aktivitas` (jenis='ekskul') pasca konsolidasi 17 -> 14.
 * Class name + public method + allowedFields TETAP supaya RaporDataLoader &
 * WaliKelas controller tidak perlu refactor. SELECT mengalias id_aktivitas
 * AS id_siswa_ekskul agar caller yang baca PK tetap dapat kolom yang sama.
 */
class SiswaEkstrakurikulerModel extends Model
{
    protected $table         = 'nilai_aktivitas';
    protected $primaryKey    = 'id_aktivitas';
    protected $allowedFields = ['id_siswa', 'id_ekskul', 'id_tahun_ajaran', 'keterangan'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert  = ['injectJenis'];

    protected function injectJenis(array $data): array
    {
        $data['data']['jenis'] = 'ekskul';
        return $data;
    }

    /**
     * Daftar ekskul siswa untuk satu tahun ajaran, gabung nama dari master_referensi.
     * Return shape sama persis dengan versi lama: kolom siswa_ekstrakurikuler.* +
     * master_ekstrakurikuler.nama (alias 'nama').
     */
    public function findForSiswaTa(int $idSiswa, int $idTa): array
    {
        return $this->db->table('nilai_aktivitas na')
            ->select("na.id_aktivitas AS id_siswa_ekskul,
                      na.id_siswa, na.id_ekskul, na.id_tahun_ajaran, na.keterangan,
                      na.created_at, na.updated_at, mr.nama")
            ->join('master_referensi mr', "mr.id_referensi = na.id_ekskul AND mr.jenis = 'ekskul'", 'left')
            ->where('na.jenis', 'ekskul')
            ->where('na.id_siswa', $idSiswa)
            ->where('na.id_tahun_ajaran', $idTa)
            ->orderBy('na.id_ekskul', 'ASC')
            ->get()->getResultArray();
    }

    public function find($id = null)
    {
        if ($id === null) {
            return $this->findAll();
        }
        return $this->builder()
            ->where('jenis', 'ekskul')
            ->where('id_aktivitas', (int) $id)
            ->select('id_aktivitas AS id_siswa_ekskul, id_siswa, id_ekskul, id_tahun_ajaran, keterangan, created_at, updated_at')
            ->get()->getRowArray();
    }

    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $b = $this->builder()
            ->where('jenis', 'ekskul')
            ->select('id_aktivitas AS id_siswa_ekskul, id_siswa, id_ekskul, id_tahun_ajaran, keterangan, created_at, updated_at');
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
            ->where('id_aktivitas', (int) $id)
            ->update($data);
    }

    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            return $this->builder()
                ->where('jenis', 'ekskul')
                ->where('id_aktivitas', (int) $id)
                ->delete();
        }
        return parent::delete($id, $purge);
    }
}
