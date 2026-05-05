<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorSchemaToElevenTables extends Migration
{
    public function up(): void
    {
        $db = \Config\Database::connect();

        $tables = $db->listTables();

        if ($this->db->fieldExists('id_user_ortu', 'siswa') === false) {
            $this->forge->addColumn('siswa', [
                'id_user_ortu' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'id_kelas',
                ],
            ]);
        }

        if (!in_array('nilai_siswa', $tables, true)) {
            $this->forge->addField([
                'id_nilai_siswa' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'id_siswa' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'id_mapel' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'id_tahun_ajaran' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'nilai_tugas' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'nilai_ulangan' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'rata_rata_harian' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'nilai_uts' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'nilai_uas' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_nilai_siswa', true);
            $this->forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun_ajaran'], 'unique_nilai_siswa');
            $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
            $this->forge->createTable('nilai_siswa');
        }

        if (in_array('wali_siswa', $tables, true)) {
            $waliRows = $db->table('wali_siswa')
                ->orderBy('id_wali_siswa', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($waliRows as $wali) {
                $db->table('siswa')
                    ->where('id_siswa', (int) $wali['id_siswa'])
                    ->update(['id_user_ortu' => (int) $wali['id_user']]);
            }
        }

        if (in_array('nilai_harian', $tables, true)) {
            $db->query(
                'INSERT INTO nilai_siswa (id_siswa, id_mapel, id_tahun_ajaran, nilai_tugas, nilai_ulangan, rata_rata_harian, created_at, updated_at)
                 SELECT id_siswa, id_mapel, id_tahun_ajaran, nilai_tugas, nilai_ulangan, rata_rata_harian, created_at, updated_at
                 FROM nilai_harian
                 ON DUPLICATE KEY UPDATE
                    nilai_tugas = VALUES(nilai_tugas),
                    nilai_ulangan = VALUES(nilai_ulangan),
                    rata_rata_harian = VALUES(rata_rata_harian),
                    updated_at = VALUES(updated_at)'
            );
        }

        if (in_array('nilai_ujian', $tables, true)) {
            $db->query(
                'INSERT INTO nilai_siswa (id_siswa, id_mapel, id_tahun_ajaran, nilai_uts, nilai_uas, created_at, updated_at)
                 SELECT id_siswa, id_mapel, id_tahun_ajaran, nilai_uts, nilai_uas, created_at, updated_at
                 FROM nilai_ujian
                 ON DUPLICATE KEY UPDATE
                    nilai_uts = VALUES(nilai_uts),
                    nilai_uas = VALUES(nilai_uas),
                    updated_at = VALUES(updated_at)'
            );
        }

        foreach (['wali_siswa', 'nilai_harian', 'nilai_ujian', 'log_aktivitas'] as $legacyTable) {
            if (in_array($legacyTable, $tables, true) || in_array($legacyTable, $db->listTables(), true)) {
                $this->forge->dropTable($legacyTable, true);
            }
        }
    }

    public function down(): void
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();

        if (!in_array('wali_siswa', $tables, true)) {
            $this->forge->addField([
                'id_wali_siswa' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'id_user' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'id_siswa' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'hubungan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'wali',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_wali_siswa', true);
            $this->forge->addForeignKey('id_user', 'users', 'id_user', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            $this->forge->createTable('wali_siswa');
        }

        if (!in_array('nilai_harian', $tables, true)) {
            $this->forge->addField([
                'id_nilai_harian' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'nilai_tugas' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'nilai_ulangan' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'rata_rata_harian' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id_nilai_harian', true);
            $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
            $this->forge->createTable('nilai_harian');
        }

        if (!in_array('nilai_ujian', $tables, true)) {
            $this->forge->addField([
                'id_nilai_ujian' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'nilai_uts' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'nilai_uas' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id_nilai_ujian', true);
            $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
            $this->forge->createTable('nilai_ujian');
        }

        if (in_array('nilai_siswa', $db->listTables(), true)) {
            $nilaiRows = $db->table('nilai_siswa')->get()->getResultArray();

            foreach ($nilaiRows as $row) {
                $db->table('nilai_harian')->insert([
                    'id_siswa' => $row['id_siswa'],
                    'id_mapel' => $row['id_mapel'],
                    'id_tahun_ajaran' => $row['id_tahun_ajaran'],
                    'nilai_tugas' => $row['nilai_tugas'],
                    'nilai_ulangan' => $row['nilai_ulangan'],
                    'rata_rata_harian' => $row['rata_rata_harian'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]);

                $db->table('nilai_ujian')->insert([
                    'id_siswa' => $row['id_siswa'],
                    'id_mapel' => $row['id_mapel'],
                    'id_tahun_ajaran' => $row['id_tahun_ajaran'],
                    'nilai_uts' => $row['nilai_uts'],
                    'nilai_uas' => $row['nilai_uas'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]);
            }

            $this->forge->dropTable('nilai_siswa', true);
        }

        if ($this->db->fieldExists('id_user_ortu', 'siswa')) {
            $siswaRows = $db->table('siswa')->where('id_user_ortu IS NOT NULL', null, false)->get()->getResultArray();
            foreach ($siswaRows as $siswa) {
                $db->table('wali_siswa')->insert([
                    'id_user' => $siswa['id_user_ortu'],
                    'id_siswa' => $siswa['id_siswa'],
                    'hubungan' => 'wali',
                    'created_at' => $siswa['created_at'] ?? null,
                    'updated_at' => $siswa['updated_at'] ?? null,
                ]);
            }

            $this->forge->dropColumn('siswa', 'id_user_ortu');
        }
    }
}
