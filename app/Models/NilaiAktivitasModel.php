<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Helper generik untuk akses tabel `nilai_aktivitas` (polymorphic) yang
 * menampung dua jenis penilaian non-akademik:
 *   - jenis='ekskul' : satu baris per (siswa, ekskul, TA) — pakai
 *     id_ekskul (soft ref ke master_referensi.id_referensi jenis='ekskul'),
 *     id_tahun_ajaran, keterangan.
 *   - jenis='koko'   : satu baris per (siswa, tema, dimensi) — pakai
 *     id_tema (Phase 1: kokurikuler_tema; Phase 2: master_referensi koko_tema),
 *     id_dimensi (soft ref master_referensi.id_referensi jenis='dimensi'),
 *     subdimensi, level.
 *
 * Facade SiswaEkstrakurikulerModel & SiswaKokurikulerDimensiModel tetap
 * dipakai oleh controller — class ini opsional.
 */
class NilaiAktivitasModel extends Model
{
    protected $table         = 'nilai_aktivitas';
    protected $primaryKey    = 'id_aktivitas';
    protected $allowedFields = [
        'jenis', 'id_siswa', 'id_tahun_ajaran',
        'id_ekskul', 'keterangan',
        'id_tema', 'id_dimensi', 'subdimensi', 'level',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
