<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGuruToMapelKelasAndTahunAjaranToSiswa extends Migration
{
    public function up(): void
    {
        $db = \Config\Database::connect();

        if ($this->db->tableExists('mapel_kelas')) {
            if (!$this->db->fieldExists('id_guru', 'mapel_kelas')) {
                $this->forge->addColumn('mapel_kelas', [
                    'id_guru' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                        'after' => 'id_kelas',
                    ],
                ]);
            }

            $this->addForeignKeyIfMissing('mapel_kelas', 'fk_mapel_kelas_guru', 'id_guru', 'users', 'id_user', 'SET NULL', 'CASCADE');
        }

        if ($this->db->tableExists('siswa')) {
            if (!$this->db->fieldExists('id_tahun_ajaran', 'siswa')) {
                $this->forge->addColumn('siswa', [
                    'id_tahun_ajaran' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                        'after' => 'id_kelas',
                    ],
                ]);

                $activeYear = $db->table('tahun_ajaran')
                    ->select('id_tahun_ajaran')
                    ->where('aktif', 'aktif')
                    ->orderBy('id_tahun_ajaran', 'DESC')
                    ->get()
                    ->getRowArray();

                if ($activeYear) {
                    $db->table('siswa')
                        ->where('id_tahun_ajaran IS NULL', null, false)
                        ->update(['id_tahun_ajaran' => (int) $activeYear['id_tahun_ajaran']]);
                }
            }

            $this->addForeignKeyIfMissing('siswa', 'fk_siswa_tahun_ajaran', 'id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'SET NULL', 'CASCADE');
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('siswa') && $this->db->fieldExists('id_tahun_ajaran', 'siswa')) {
            $this->dropForeignKeyIfExists('siswa', 'fk_siswa_tahun_ajaran');
            $this->forge->dropColumn('siswa', 'id_tahun_ajaran');
        }

        if ($this->db->tableExists('mapel_kelas') && $this->db->fieldExists('id_guru', 'mapel_kelas')) {
            $this->dropForeignKeyIfExists('mapel_kelas', 'fk_mapel_kelas_guru');
            $this->forge->dropColumn('mapel_kelas', 'id_guru');
        }
    }

    private function addForeignKeyIfMissing(string $table, string $constraintName, string $field, string $foreignTable, string $foreignField, string $onDelete, string $onUpdate): void
    {
        if ($this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        try {
            $this->db->query(sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s ON UPDATE %s',
                $table,
                $constraintName,
                $field,
                $foreignTable,
                $foreignField,
                $onDelete,
                $onUpdate
            ));
        } catch (\Throwable $e) {
            log_message('warning', 'Unable to add foreign key {constraint}: {message}', [
                'constraint' => $constraintName,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function dropForeignKeyIfExists(string $table, string $constraintName): void
    {
        if (!$this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        try {
            $this->db->query(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraintName));
        } catch (\Throwable $e) {
            log_message('warning', 'Unable to drop foreign key {constraint}: {message}', [
                'constraint' => $constraintName,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = $this->db->getDatabase();
        $row = $this->db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
            [$database, $table, $constraintName]
        )->getRowArray();

        return !empty($row);
    }
}
