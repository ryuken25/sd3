<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Facade tipis ke `nilai_aktivitas` (jenis='koko') pasca konsolidasi.
 * Class name + public method + allowedFields TETAP.
 */
class SiswaKokurikulerDimensiModel extends Model
{
    protected $table         = 'nilai_aktivitas';
    protected $primaryKey    = 'id_aktivitas';
    protected $allowedFields = ['id_siswa', 'id_tema', 'id_dimensi', 'subdimensi', 'level'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert  = ['injectJenis'];

    protected function injectJenis(array $data): array
    {
        $data['data']['jenis'] = 'koko';
        return $data;
    }

    /**
     * Daftar dimensi P5 siswa untuk satu tema (kelas+TA). Gabung nama_dimensi
     * dan urutan dari master_referensi (jenis='dimensi'). Return shape sama
     * persis dengan versi lama.
     */
    public function findForSiswaTema(int $idSiswa, int $idTema): array
    {
        return $this->db->table('nilai_aktivitas na')
            ->select("na.id_aktivitas AS id_siswa_koko,
                      na.id_siswa, na.id_tema, na.id_dimensi, na.subdimensi, na.level,
                      na.created_at, na.updated_at,
                      mr.nama_dimensi, mr.urutan")
            ->join('master_referensi mr', "mr.id_referensi = na.id_dimensi AND mr.jenis = 'dimensi'", 'left')
            ->where('na.jenis', 'koko')
            ->where('na.id_siswa', $idSiswa)
            ->where('na.id_tema', $idTema)
            ->orderBy('mr.urutan', 'ASC')
            ->get()->getResultArray();
    }

    public function find($id = null)
    {
        if ($id === null) {
            return $this->findAll();
        }
        return $this->builder()
            ->where('jenis', 'koko')
            ->where('id_aktivitas', (int) $id)
            ->select('id_aktivitas AS id_siswa_koko, id_siswa, id_tema, id_dimensi, subdimensi, level, created_at, updated_at')
            ->get()->getRowArray();
    }

    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $b = $this->builder()
            ->where('jenis', 'koko')
            ->select('id_aktivitas AS id_siswa_koko, id_siswa, id_tema, id_dimensi, subdimensi, level, created_at, updated_at');
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
            ->where('jenis', 'koko')
            ->where('id_aktivitas', (int) $id)
            ->update($data);
    }

    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            return $this->builder()
                ->where('jenis', 'koko')
                ->where('id_aktivitas', (int) $id)
                ->delete();
        }
        return parent::delete($id, $purge);
    }
}
