<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Konsolidasi Phase 2: 14 -> 12 tabel.
 *
 * Fold master_capaian_pembelajaran + kokurikuler_tema ke master_referensi
 * (STI). Repoint nilai_aktivitas.id_tema dari kokurikuler_tema.id_tema lama ke
 * master_referensi.id_referensi baru lewat legacy_id.
 *
 * Resilient: master_capaian_pembelajaran kadang corrupt di engine MariaDB
 * ("Table doesn't exist in engine"). Backfill CP dibungkus try/catch supaya
 * tidak crash; tabel tetap akan di-drop di akhir.
 */
class FoldCpAndTemaIntoMasterRef extends Migration
{
    public function up(): void
    {
        $forge = $this->forge;
        $db    = $this->db;

        if (!$db->tableExists('master_referensi')) {
            return; // Phase 1 belum jalan
        }

        $db->transStart();

        // ----- 1. Backfill CP -> master_referensi (jenis='cp') -----------
        if ($db->tableExists('master_capaian_pembelajaran')) {
            try {
                $db->query("INSERT INTO master_referensi
                    (jenis, legacy_id, id_mapel, fase, semester, predikat, deskripsi, aktif, created_at, updated_at)
                    SELECT 'cp', id_master_cp, id_mapel, fase, semester, predikat, deskripsi, aktif, created_at, updated_at
                    FROM master_capaian_pembelajaran");
            } catch (\Throwable $e) {
                log_message('warning', 'Skip backfill master_capaian_pembelajaran: ' . $e->getMessage());
            }
        }

        // ----- 2. Backfill koko_tema -> master_referensi (jenis='koko_tema')
        if ($db->tableExists('kokurikuler_tema')) {
            $db->query("INSERT INTO master_referensi
                (jenis, legacy_id, nama_tema, id_kelas, id_tahun_ajaran, narasi_pembuka, created_at, updated_at)
                SELECT 'koko_tema', id_tema, nama_tema, id_kelas, id_tahun_ajaran, narasi_pembuka, created_at, updated_at
                FROM kokurikuler_tema");

            // ----- 3. Repoint nilai_aktivitas.id_tema ----------------------
            // id_tema lama -> id_referensi baru (via legacy_id JOIN).
            // id_referensi & legacy_id range tidak bertabrakan karena master_referensi
            // PK terus increment dari Phase 1 backfill.
            $db->query("UPDATE nilai_aktivitas na
                JOIN master_referensi mr
                  ON mr.jenis = 'koko_tema' AND mr.legacy_id = na.id_tema
                SET na.id_tema = mr.id_referensi
                WHERE na.jenis = 'koko'");
        }

        // ----- 4. DROP 2 tabel lama (FK-safe) ----------------------------
        $db->query('SET FOREIGN_KEY_CHECKS=0');
        if ($db->tableExists('master_capaian_pembelajaran')) {
            try {
                $forge->dropTable('master_capaian_pembelajaran', true);
            } catch (\Throwable $e) {
                // Engine corrupt → force drop via DROP TABLE
                $db->query('DROP TABLE IF EXISTS master_capaian_pembelajaran');
            }
        }
        if ($db->tableExists('kokurikuler_tema')) {
            $forge->dropTable('kokurikuler_tema', true);
        }
        $db->query('SET FOREIGN_KEY_CHECKS=1');

        $db->transComplete();
    }

    public function down(): void
    {
        $forge = $this->forge;
        $db    = $this->db;

        // 1. Recreate kokurikuler_tema (struktur lama)
        if (!$db->tableExists('kokurikuler_tema')) {
            $forge->addField([
                'id_tema'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'nama_tema'       => ['type' => 'VARCHAR', 'constraint' => 200],
                'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_kelas'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'narasi_pembuka'  => ['type' => 'TEXT', 'null' => true],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
                'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_tema', true);
            $forge->addUniqueKey(['id_tahun_ajaran', 'id_kelas']);
            try {
                $forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
                $forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
            } catch (\Throwable $e) {
            }
            $forge->createTable('kokurikuler_tema', true);
        }

        // 2. Recreate master_capaian_pembelajaran
        if (!$db->tableExists('master_capaian_pembelajaran')) {
            $forge->addField([
                'id_master_cp' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_mapel'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'fase'         => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C']],
                'semester'     => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap']],
                'deskripsi'    => ['type' => 'TEXT'],
                'aktif'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'predikat'     => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C', 'D'], 'null' => true],
                'created_at'   => ['type' => 'DATETIME', 'null' => true],
                'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_master_cp', true);
            $forge->addKey(['id_mapel', 'fase', 'semester']);
            try {
                $forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
            } catch (\Throwable $e) {
            }
            $forge->createTable('master_capaian_pembelajaran', true);
        }

        // 3. Hapus baris cp + koko_tema dari master_referensi
        if ($db->tableExists('master_referensi')) {
            $db->table('master_referensi')->whereIn('jenis', ['cp', 'koko_tema'])->delete();
        }
    }
}
