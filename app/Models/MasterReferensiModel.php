<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Helper generik untuk akses tabel `master_referensi` (STI). Pasca konsolidasi
 * 17 -> 12 tabel, tabel ini menampung 3-5 jenis master (dimensi P5,
 * ekstrakurikuler, template catatan wali, capaian pembelajaran, kokurikuler
 * tema) dengan diskriminator kolom `jenis`.
 *
 * Facade per-jenis (MasterDimensiPancasilaModel, MasterEkstrakurikulerModel,
 * dst) tetap dipakai oleh controller/loader supaya signature tidak berubah.
 * Class ini opsional — dipakai bila perlu akses cross-jenis.
 */
class MasterReferensiModel extends Model
{
    protected $table         = 'master_referensi';
    protected $primaryKey    = 'id_referensi';
    protected $allowedFields = [
        'jenis', 'legacy_id',
        'nama_dimensi', 'urutan',
        'nama', 'deskripsi_default', 'wajib',
        'nama_template', 'isi_template', 'kategori',
        'id_mapel', 'fase', 'semester', 'predikat', 'deskripsi',
        'nama_tema', 'id_kelas', 'id_tahun_ajaran', 'narasi_pembuka',
        'aktif',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Cari id_referensi (PK baru) dari (jenis, legacy_id lama). */
    public function findByLegacy(string $jenis, int $legacyId): ?array
    {
        return $this->where('jenis', $jenis)
            ->where('legacy_id', $legacyId)
            ->first();
    }
}
