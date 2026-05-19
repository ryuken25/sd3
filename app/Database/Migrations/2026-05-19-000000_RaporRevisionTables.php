<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Revisi rapor SDN 3 Mekarsari — Megaprompt Tier B/C.
 *
 * Tabel BARU:
 *   - master_capaian_pembelajaran      (Pek 3)
 *   - nilai_capaian_kompetensi          (Pek 3)
 *   - master_template_catatan           (Pek 4 — siswa catatan reuse rapor.catatan_wali_kelas)
 *   - master_ekstrakurikuler            (Pek 5.1)
 *   - siswa_ekstrakurikuler             (Pek 5.1)
 *   - master_dimensi_pancasila          (Pek 5.2)
 *   - kokurikuler_tema                  (Pek 5.2)
 *   - siswa_kokurikuler_dimensi         (Pek 5.2)
 *
 * ALTER nilai_akhir (Pek 6):
 *   - catatan_remedial TEXT NULL
 *   - flag_borderline_75 TINYINT(1) DEFAULT 0
 *
 * Catatan: tabel siswa_catatan_wali_kelas dan siswa_ketidakhadiran TIDAK dibuat
 * karena kolom rapor.catatan_wali_kelas + rapor.sakit/izin/alpa sudah ada.
 */
class RaporRevisionTables extends Migration
{
    public function up()
    {
        // ── 1. master_capaian_pembelajaran ────────────────────────────────
        $this->forge->addField([
            'id_master_cp'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_mapel'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'fase'            => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C']],
            'semester'        => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap']],
            'deskripsi'       => ['type' => 'TEXT'],
            'aktif'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_master_cp', true);
        $this->forge->addKey(['id_mapel', 'fase', 'semester']);
        $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->createTable('master_capaian_pembelajaran');

        // ── 2. nilai_capaian_kompetensi ────────────────────────────────────
        $this->forge->addField([
            'id_nilai_cp'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_nilai_akhir'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'master_cp_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'deskripsi_custom'   => ['type' => 'TEXT', 'null' => true],
            'status'             => ['type' => 'ENUM', 'constraint' => ['tercapai_sangat_baik', 'perlu_peningkatan']],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_nilai_cp', true);
        $this->forge->addKey('id_nilai_akhir');
        $this->forge->addKey('master_cp_id');
        $this->forge->addForeignKey('id_nilai_akhir', 'nilai_akhir', 'id_nilai_akhir', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('master_cp_id', 'master_capaian_pembelajaran', 'id_master_cp', 'SET NULL', 'CASCADE');
        $this->forge->createTable('nilai_capaian_kompetensi');

        // ── 3. master_template_catatan ─────────────────────────────────────
        $this->forge->addField([
            'id_template'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_template'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'isi_template'   => ['type' => 'TEXT'],
            'kategori'       => ['type' => 'ENUM', 'constraint' => ['positif', 'perlu_perbaikan', 'netral'], 'default' => 'netral'],
            'aktif'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_template', true);
        $this->forge->createTable('master_template_catatan');

        // ── 4. master_ekstrakurikuler ──────────────────────────────────────
        $this->forge->addField([
            'id_ekskul'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'deskripsi_default' => ['type' => 'TEXT', 'null' => true],
            'aktif'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_ekskul', true);
        $this->forge->createTable('master_ekstrakurikuler');

        // ── 5. siswa_ekstrakurikuler ───────────────────────────────────────
        $this->forge->addField([
            'id_siswa_ekskul'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_ekskul'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'keterangan'       => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa_ekskul', true);
        $this->forge->addUniqueKey(['id_siswa', 'id_ekskul', 'id_tahun_ajaran']);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_ekskul', 'master_ekstrakurikuler', 'id_ekskul', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa_ekstrakurikuler');

        // ── 6. master_dimensi_pancasila ────────────────────────────────────
        $this->forge->addField([
            'id_dimensi'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_dimensi' => ['type' => 'VARCHAR', 'constraint' => 150],
            'urutan'       => ['type' => 'INT', 'constraint' => 2, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_dimensi', true);
        $this->forge->createTable('master_dimensi_pancasila');

        // ── 7. kokurikuler_tema ────────────────────────────────────────────
        $this->forge->addField([
            'id_tema'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_tema'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_kelas'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'narasi_pembuka'  => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_tema', true);
        $this->forge->addUniqueKey(['id_tahun_ajaran', 'id_kelas']);
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kokurikuler_tema');

        // ── 8. siswa_kokurikuler_dimensi ───────────────────────────────────
        $this->forge->addField([
            'id_siswa_koko'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tema'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_dimensi'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'subdimensi'     => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'level'          => ['type' => 'ENUM', 'constraint' => ['berkembang', 'cakap', 'mahir', 'sangat_mahir'], 'default' => 'berkembang'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa_koko', true);
        $this->forge->addUniqueKey(['id_siswa', 'id_tema', 'id_dimensi']);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tema', 'kokurikuler_tema', 'id_tema', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_dimensi', 'master_dimensi_pancasila', 'id_dimensi', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa_kokurikuler_dimensi');

        // ── ALTER nilai_akhir: Pek 6 threshold-75 + catatan remedial ───────
        $this->forge->addColumn('nilai_akhir', [
            'catatan_remedial'    => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'status_kelulusan',
            ],
            'flag_borderline_75' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'catatan_remedial',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('nilai_akhir', ['catatan_remedial', 'flag_borderline_75']);
        $this->forge->dropTable('siswa_kokurikuler_dimensi', true);
        $this->forge->dropTable('kokurikuler_tema', true);
        $this->forge->dropTable('master_dimensi_pancasila', true);
        $this->forge->dropTable('siswa_ekstrakurikuler', true);
        $this->forge->dropTable('master_ekstrakurikuler', true);
        $this->forge->dropTable('master_template_catatan', true);
        $this->forge->dropTable('nilai_capaian_kompetensi', true);
        $this->forge->dropTable('master_capaian_pembelajaran', true);
    }
}
