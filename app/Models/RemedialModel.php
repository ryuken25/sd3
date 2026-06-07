<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Remedial sekarang inline di tabel `nilai` (kolom tindak_lanjut + status_remedial).
 * Model ini disisakan sebagai facade tipis supaya call-site lama (controller, view)
 * tidak meledak — semua mutasi sekarang via setRemedial()/clearRemedial() yang
 * UPDATE baris nilai existing (bukan insert/delete baris terpisah).
 */
class RemedialModel extends Model
{
    protected $table         = 'nilai';
    protected $primaryKey    = 'id_nilai';
    protected $allowedFields = ['tindak_lanjut', 'status_remedial'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Tandai baris nilai sebagai remedial dengan tindak_lanjut & status.
     * Tidak insert baris baru — wajib eksis dulu via PenilaianAgregat / NilaiAkhir.
     */
    public function setRemedial(int $idNilai, string $tindakLanjut, string $status = 'Sedang Proses'): bool
    {
        return (bool) $this->update($idNilai, [
            'tindak_lanjut'   => $tindakLanjut !== '' ? $tindakLanjut : null,
            'status_remedial' => $status,
        ]);
    }

    /** Hapus jejak remedial (set kedua kolom NULL). Dipakai saat nilai naik di atas KKM. */
    public function clearRemedial(int $idNilai): bool
    {
        return (bool) $this->update($idNilai, [
            'tindak_lanjut'   => null,
            'status_remedial' => null,
        ]);
    }
}
