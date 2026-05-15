<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Relax siswa.nis dari UNIQUE single-column jadi UNIQUE composite (nis, id_tahun_ajaran).
 *
 * Sebelum: 1 NIS = 1 baris siswa di seluruh sistem.
 * Sesudah: 1 NIS = 1 baris siswa per Tahun Ajaran (siswa naik kelas = baris baru per TA).
 *
 * Idempotent — cek INFORMATION_SCHEMA sebelum drop/create.
 */
class RelaxSiswaNisUniqueForMultiPeriod extends Migration
{
    public function up()
    {
        // Drop unique single-column kalau ada (exclude composite yang akan kita buat)
        $row = $this->db->query("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'siswa'
              AND COLUMN_NAME  = 'nis'
              AND NON_UNIQUE   = 0
              AND INDEX_NAME  != 'uniq_siswa_nis_ta'
            GROUP BY INDEX_NAME
            HAVING COUNT(*) = 1
        ")->getRowArray();

        if ($row) {
            $indexName = $row['INDEX_NAME'];
            $this->db->query("ALTER TABLE siswa DROP INDEX `{$indexName}`");
        }

        // Tambahkan composite unique kalau belum ada
        $exists = $this->db->query("
            SELECT 1
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'siswa'
              AND INDEX_NAME   = 'uniq_siswa_nis_ta'
            LIMIT 1
        ")->getRowArray();

        if (!$exists) {
            $this->db->query("ALTER TABLE siswa ADD CONSTRAINT uniq_siswa_nis_ta UNIQUE (nis, id_tahun_ajaran)");
        }
    }

    public function down()
    {
        $exists = $this->db->query("
            SELECT 1
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'siswa'
              AND INDEX_NAME   = 'uniq_siswa_nis_ta'
            LIMIT 1
        ")->getRowArray();

        if ($exists) {
            $this->db->query("ALTER TABLE siswa DROP INDEX uniq_siswa_nis_ta");
        }

        $this->db->query("ALTER TABLE siswa ADD UNIQUE (nis)");
    }
}
