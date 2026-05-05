<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SimplifyAcademicSchema extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($this->db->fieldExists('email', 'users')) {
            $this->forge->dropColumn('users', 'email');
        }

        if ($this->db->fieldExists('status', 'mata_pelajaran')) {
            $this->forge->dropColumn('mata_pelajaran', 'status');
        }

        if ($this->db->fieldExists('tahun_ajaran_id', 'kelas')) {
            try {
                $db->query('ALTER TABLE `kelas` DROP FOREIGN KEY `fk_kelas_tahun_ajaran`');
            } catch (\Throwable $e) {
                // Ignore when foreign key does not exist.
            }

            try {
                $db->query('ALTER TABLE `kelas` DROP INDEX `fk_kelas_tahun_ajaran`');
            } catch (\Throwable $e) {
                // Ignore when index does not exist.
            }

            $this->forge->dropColumn('kelas', 'tahun_ajaran_id');
        }
    }

    public function down()
    {
        if (!$this->db->fieldExists('email', 'users')) {
            $this->forge->addColumn('users', [
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'nama_lengkap',
                ],
            ]);
        }

        if (!$this->db->fieldExists('status', 'mata_pelajaran')) {
            $this->forge->addColumn('mata_pelajaran', [
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['aktif', 'nonaktif'],
                    'default' => 'aktif',
                    'after' => 'kelompok',
                ],
            ]);
        }

        if (!$this->db->fieldExists('tahun_ajaran_id', 'kelas')) {
            $this->forge->addColumn('kelas', [
                'tahun_ajaran_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'wali_kelas',
                ],
            ]);

            try {
                $this->db->query('ALTER TABLE `kelas` ADD CONSTRAINT `fk_kelas_tahun_ajaran` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE SET NULL ON UPDATE CASCADE');
            } catch (\Throwable $e) {
                // Ignore when constraint cannot be restored automatically.
            }
        }
    }
}
