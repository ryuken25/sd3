<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Narasi Capaian Kompetensi per (siswa, mapel, tahun ajaran).
 * Lepas dari nilai_akhir → guru bisa mengisi CP kapan pun.
 */
class CapaianNarasiModel extends Model
{
    protected $table         = 'capaian_narasi';
    protected $primaryKey    = 'id_capaian_narasi';
    protected $allowedFields = ['id_siswa', 'id_mapel', 'id_tahun_ajaran', 'narasi'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Peta narasi per id_siswa untuk satu mapel + TA (lookup cepat di view input).
     *
     * @param int[] $idsSiswa
     * @return array<int,string> [id_siswa => narasi]
     */
    public function mapForMapelTa(array $idsSiswa, int $idMapel, int $idTa): array
    {
        if ($idsSiswa === []) {
            return [];
        }
        $rows = $this->whereIn('id_siswa', $idsSiswa)
            ->where('id_mapel', $idMapel)
            ->where('id_tahun_ajaran', $idTa)
            ->findAll();

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['id_siswa']] = (string) ($r['narasi'] ?? '');
        }
        return $map;
    }

    /**
     * Narasi satu siswa untuk satu mapel + TA (dipakai rapor). '' jika belum ada.
     */
    public function narasiFor(int $idSiswa, int $idMapel, int $idTa): string
    {
        $row = $this->where('id_siswa', $idSiswa)
            ->where('id_mapel', $idMapel)
            ->where('id_tahun_ajaran', $idTa)
            ->first();

        return trim((string) ($row['narasi'] ?? ''));
    }

    /**
     * Simpan/timpa narasi untuk satu (siswa, mapel, TA). Narasi kosong → baris dihapus.
     */
    public function upsertNarasi(int $idSiswa, int $idMapel, int $idTa, string $narasi): void
    {
        $existing = $this->where('id_siswa', $idSiswa)
            ->where('id_mapel', $idMapel)
            ->where('id_tahun_ajaran', $idTa)
            ->first();

        $narasi = trim($narasi);

        if ($narasi === '') {
            if ($existing) {
                $this->delete($existing['id_capaian_narasi']);
            }
            return;
        }

        if ($existing) {
            $this->update($existing['id_capaian_narasi'], ['narasi' => $narasi]);
        } else {
            $this->insert([
                'id_siswa'        => $idSiswa,
                'id_mapel'        => $idMapel,
                'id_tahun_ajaran' => $idTa,
                'narasi'          => $narasi,
            ]);
        }
    }
}
