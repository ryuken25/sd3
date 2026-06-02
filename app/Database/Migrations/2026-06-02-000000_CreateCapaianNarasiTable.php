<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel narasi Capaian Kompetensi per (siswa, mapel, tahun ajaran).
 *
 * Sebelumnya narasi CP nyangkut di kolom nilai_akhir.narasi_cp, sehingga guru
 * baru bisa mengisi CP setelah Nilai Akhir dihitung. Tabel ini melepas keterikatan
 * itu: guru bisa menulis/menyimpan narasi CP kapan pun, lalu rapor membacanya.
 *
 * Unik per (id_siswa, id_mapel, id_tahun_ajaran) → satu narasi per kombinasi.
 */
class CreateCapaianNarasiTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('capaian_narasi')) {
            return;
        }

        $this->forge->addField([
            'id_capaian_narasi' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_mapel'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'narasi'            => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_capaian_narasi', true);
        $this->forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun_ajaran']);
        $this->forge->createTable('capaian_narasi');
    }

    public function down()
    {
        $this->forge->dropTable('capaian_narasi', true);
    }
}
