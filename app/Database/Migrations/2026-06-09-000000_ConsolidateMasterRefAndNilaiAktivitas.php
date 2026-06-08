<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Konsolidasi schema Phase 1: 17 -> 14 tabel.
 *
 * Gabung:
 *   - master_dimensi_pancasila + master_ekstrakurikuler + master_template_catatan
 *     -> master_referensi (Single-Table-Inheritance via kolom `jenis`)
 *   - siswa_ekstrakurikuler + siswa_kokurikuler_dimensi
 *     -> nilai_aktivitas (polymorphic via kolom `jenis`)
 *
 * ENUM `jenis` di master_referensi sudah include 'cp' & 'koko_tema' supaya Phase 2
 * tidak perlu ALTER kolom.
 *
 * Lossless: backfill via JOIN ke legacy_id supaya FK transaksi siswa (id_ekskul,
 * id_dimensi) di-repoint ke id_referensi BARU. Reversible: down() recreate 5
 * tabel lama (kosong; data restore via seeder ulang).
 */
class ConsolidateMasterRefAndNilaiAktivitas extends Migration
{
    public function up(): void
    {
        $forge = $this->forge;
        $db    = $this->db;

        if ($db->tableExists('master_referensi') || $db->tableExists('nilai_aktivitas')) {
            return; // Sudah pernah jalan
        }

        // ----- 1. CREATE master_referensi --------------------------------
        $forge->addField([
            'id_referensi'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'jenis'             => ['type' => 'ENUM', 'constraint' => ['dimensi', 'ekskul', 'template', 'cp', 'koko_tema']],
            'legacy_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],

            // dimensi
            'nama_dimensi'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'urutan'            => ['type' => 'INT',     'constraint' => 2,   'null' => true],

            // ekskul
            'nama'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'deskripsi_default' => ['type' => 'TEXT',                          'null' => true],
            'wajib'             => ['type' => 'TINYINT', 'constraint' => 1,   'null' => true],

            // template catatan
            'nama_template'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'isi_template'      => ['type' => 'TEXT',                          'null' => true],
            'kategori'          => ['type' => 'ENUM', 'constraint' => ['positif', 'perlu_perbaikan', 'netral'], 'null' => true],

            // capaian pembelajaran
            'id_mapel'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'fase'              => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C'], 'null' => true],
            'semester'          => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap'], 'null' => true],
            'predikat'          => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C', 'D'], 'null' => true],
            'deskripsi'         => ['type' => 'TEXT', 'null' => true],

            // kokurikuler tema
            'nama_tema'         => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'id_kelas'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_tahun_ajaran'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'narasi_pembuka'    => ['type' => 'TEXT', 'null' => true],

            // status (dipakai ekskul/template/cp)
            'aktif'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],

            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id_referensi', true);
        $forge->addKey('jenis');
        $forge->addKey(['jenis', 'legacy_id']);
        $forge->createTable('master_referensi', true);

        // ----- 2. CREATE nilai_aktivitas ---------------------------------
        $forge->addField([
            'id_aktivitas'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'jenis'             => ['type' => 'ENUM', 'constraint' => ['ekskul', 'koko']],
            'id_siswa'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],

            // ekskul
            'id_ekskul'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'keterangan'        => ['type' => 'TEXT', 'null' => true],

            // koko
            'id_tema'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_dimensi'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'subdimensi'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'level'             => ['type' => 'ENUM', 'constraint' => ['berkembang', 'cakap', 'mahir', 'sangat_mahir'], 'null' => true],

            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id_aktivitas', true);
        $forge->addKey(['jenis', 'id_siswa']);
        $forge->addKey('id_siswa');
        $forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $forge->createTable('nilai_aktivitas', true);

        // Skip backfill kalau tabel sumber tidak ada (fresh install)
        if (!$db->tableExists('master_dimensi_pancasila')
            && !$db->tableExists('master_ekstrakurikuler')
            && !$db->tableExists('master_template_catatan')) {
            return;
        }

        $db->transStart();

        // ----- 3. Backfill master_referensi ------------------------------
        if ($db->tableExists('master_dimensi_pancasila')) {
            $db->query("INSERT INTO master_referensi
                (jenis, legacy_id, nama_dimensi, urutan, created_at, updated_at)
                SELECT 'dimensi', id_dimensi, nama_dimensi, urutan, created_at, updated_at
                FROM master_dimensi_pancasila");
        }
        if ($db->tableExists('master_ekstrakurikuler')) {
            $db->query("INSERT INTO master_referensi
                (jenis, legacy_id, nama, deskripsi_default, aktif, wajib, created_at, updated_at)
                SELECT 'ekskul', id_ekskul, nama, deskripsi_default, aktif, COALESCE(wajib,0), created_at, updated_at
                FROM master_ekstrakurikuler");
        }
        if ($db->tableExists('master_template_catatan')) {
            $db->query("INSERT INTO master_referensi
                (jenis, legacy_id, nama_template, isi_template, kategori, aktif, created_at, updated_at)
                SELECT 'template', id_template, nama_template, isi_template, kategori, aktif, created_at, updated_at
                FROM master_template_catatan");
        }

        // ----- 4. Backfill nilai_aktivitas (repoint ID via legacy_id) ---
        if ($db->tableExists('siswa_ekstrakurikuler')) {
            $db->query("INSERT INTO nilai_aktivitas
                (jenis, id_siswa, id_tahun_ajaran, id_ekskul, keterangan, created_at, updated_at)
                SELECT 'ekskul', se.id_siswa, se.id_tahun_ajaran,
                       mr.id_referensi, se.keterangan, se.created_at, se.updated_at
                FROM siswa_ekstrakurikuler se
                JOIN master_referensi mr ON mr.jenis='ekskul' AND mr.legacy_id = se.id_ekskul");
        }
        if ($db->tableExists('siswa_kokurikuler_dimensi')) {
            // Phase 1: id_tema masih nunjuk ke kokurikuler_tema (belum dibuang)
            $db->query("INSERT INTO nilai_aktivitas
                (jenis, id_siswa, id_tema, id_dimensi, subdimensi, level, created_at, updated_at)
                SELECT 'koko', sk.id_siswa, sk.id_tema,
                       mr.id_referensi, sk.subdimensi, sk.level, sk.created_at, sk.updated_at
                FROM siswa_kokurikuler_dimensi sk
                JOIN master_referensi mr ON mr.jenis='dimensi' AND mr.legacy_id = sk.id_dimensi");
        }

        // ----- 5. DROP 5 tabel lama (urutan FK-safe: anak dulu) ---------
        // FOREIGN_KEY_CHECKS off untuk safety — FK ke siswa_* sudah hilang otomatis
        // saat tabel anaknya drop.
        $db->query('SET FOREIGN_KEY_CHECKS=0');
        if ($db->tableExists('siswa_ekstrakurikuler')) {
            $forge->dropTable('siswa_ekstrakurikuler', true);
        }
        if ($db->tableExists('siswa_kokurikuler_dimensi')) {
            $forge->dropTable('siswa_kokurikuler_dimensi', true);
        }
        if ($db->tableExists('master_template_catatan')) {
            $forge->dropTable('master_template_catatan', true);
        }
        if ($db->tableExists('master_ekstrakurikuler')) {
            $forge->dropTable('master_ekstrakurikuler', true);
        }
        if ($db->tableExists('master_dimensi_pancasila')) {
            $forge->dropTable('master_dimensi_pancasila', true);
        }
        $db->query('SET FOREIGN_KEY_CHECKS=1');

        $db->transComplete();
    }

    public function down(): void
    {
        $forge = $this->forge;
        $db    = $this->db;

        // Recreate 5 tabel lama (struktur saja — data re-seedable via SD3MekarsariSeeder).
        if (!$db->tableExists('master_dimensi_pancasila')) {
            $forge->addField([
                'id_dimensi'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'nama_dimensi' => ['type' => 'VARCHAR', 'constraint' => 150],
                'urutan'       => ['type' => 'INT', 'constraint' => 2, 'default' => 0],
                'created_at'   => ['type' => 'DATETIME', 'null' => true],
                'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_dimensi', true);
            $forge->createTable('master_dimensi_pancasila', true);
        }

        if (!$db->tableExists('master_ekstrakurikuler')) {
            $forge->addField([
                'id_ekskul'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'nama'              => ['type' => 'VARCHAR', 'constraint' => 100],
                'deskripsi_default' => ['type' => 'TEXT', 'null' => true],
                'aktif'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'wajib'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_ekskul', true);
            $forge->createTable('master_ekstrakurikuler', true);
        }

        if (!$db->tableExists('master_template_catatan')) {
            $forge->addField([
                'id_template'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'nama_template' => ['type' => 'VARCHAR', 'constraint' => 100],
                'isi_template'  => ['type' => 'TEXT'],
                'kategori'      => ['type' => 'ENUM', 'constraint' => ['positif', 'perlu_perbaikan', 'netral'], 'default' => 'netral'],
                'aktif'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
                'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_template', true);
            $forge->createTable('master_template_catatan', true);
        }

        if (!$db->tableExists('siswa_ekstrakurikuler')) {
            $forge->addField([
                'id_siswa_ekskul'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_ekskul'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_tahun_ajaran'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'keterangan'       => ['type' => 'TEXT', 'null' => true],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_siswa_ekskul', true);
            $forge->addUniqueKey(['id_siswa', 'id_ekskul', 'id_tahun_ajaran']);
            $forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            // FK ke master_ekstrakurikuler & tahun_ajaran best-effort
            try {
                $forge->addForeignKey('id_ekskul', 'master_ekstrakurikuler', 'id_ekskul', 'CASCADE', 'CASCADE');
                $forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
            } catch (\Throwable $e) {
            }
            $forge->createTable('siswa_ekstrakurikuler', true);
        }

        if (!$db->tableExists('siswa_kokurikuler_dimensi')) {
            $forge->addField([
                'id_siswa_koko' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_tema'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_dimensi'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'subdimensi'    => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
                'level'         => ['type' => 'ENUM', 'constraint' => ['berkembang', 'cakap', 'mahir', 'sangat_mahir'], 'default' => 'berkembang'],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
                'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_siswa_koko', true);
            $forge->addUniqueKey(['id_siswa', 'id_tema', 'id_dimensi']);
            $forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            try {
                $forge->addForeignKey('id_tema', 'kokurikuler_tema', 'id_tema', 'CASCADE', 'CASCADE');
                $forge->addForeignKey('id_dimensi', 'master_dimensi_pancasila', 'id_dimensi', 'CASCADE', 'CASCADE');
            } catch (\Throwable $e) {
            }
            $forge->createTable('siswa_kokurikuler_dimensi', true);
        }

        if ($db->tableExists('nilai_aktivitas')) {
            $forge->dropTable('nilai_aktivitas', true);
        }
        if ($db->tableExists('master_referensi')) {
            $forge->dropTable('master_referensi', true);
        }
    }
}
