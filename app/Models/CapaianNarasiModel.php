<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Narasi Capaian Kompetensi per (siswa, mapel, tahun ajaran).
 *
 * Pasca merge: narasi disimpan di kolom `nilai.narasi` (tabel `nilai`), bukan
 * tabel `capaian_narasi` terpisah. Decouple test "guru bisa isi narasi walau
 * nilai_akhir belum dihitung" tetap terjaga: baris `nilai` di-upsert berdasarkan
 * kunci natural (siswa, mapel, TA), sehingga narasi bisa dituliskan duluan.
 *
 * Class & API publik dipertahankan agar call-site di controller/library tidak berubah.
 */
class CapaianNarasiModel extends Model
{
    protected $table         = 'nilai';
    protected $primaryKey    = 'id_nilai';
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
        $rows = $this->select('id_siswa, narasi')
            ->whereIn('id_siswa', $idsSiswa)
            ->where('id_mapel', $idMapel)
            ->where('id_tahun_ajaran', $idTa)
            ->findAll();

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['id_siswa']] = (string) ($r['narasi'] ?? '');
        }
        return $map;
    }

    /** Narasi satu siswa untuk satu mapel + TA (dipakai rapor). '' jika belum ada. */
    public function narasiFor(int $idSiswa, int $idMapel, int $idTa): string
    {
        $row = $this->select('narasi')
            ->where('id_siswa', $idSiswa)
            ->where('id_mapel', $idMapel)
            ->where('id_tahun_ajaran', $idTa)
            ->first();

        return trim((string) ($row['narasi'] ?? ''));
    }

    /**
     * Simpan/timpa narasi untuk satu (siswa, mapel, TA). Narasi kosong → set NULL.
     * Baris baru di-INSERT bila belum ada — supaya guru bisa isi narasi tanpa
     * nilai_akhir lebih dulu.
     */
    public function upsertNarasi(int $idSiswa, int $idMapel, int $idTa, string $narasi): void
    {
        $existing = $this->select('id_nilai')
            ->where('id_siswa', $idSiswa)
            ->where('id_mapel', $idMapel)
            ->where('id_tahun_ajaran', $idTa)
            ->first();

        $narasi = trim($narasi);
        $payload = ['narasi' => $narasi !== '' ? $narasi : null];

        if ($existing) {
            $this->update($existing['id_nilai'], $payload);
            return;
        }

        $this->insert([
            'id_siswa'        => $idSiswa,
            'id_mapel'        => $idMapel,
            'id_tahun_ajaran' => $idTa,
            'narasi'          => $payload['narasi'],
        ]);
    }
}
