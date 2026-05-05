<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HardenRaporAndRemedialConstraints extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $db->query('DELETE r1 FROM remedial r1 INNER JOIN remedial r2 ON r1.id_nilai_akhir = r2.id_nilai_akhir AND r1.id_remedial < r2.id_remedial');

        try {
            $db->query('ALTER TABLE `remedial` ADD UNIQUE KEY `unique_remedial_nilai_akhir` (`id_nilai_akhir`)');
        } catch (\Throwable $e) {
            // Key already exists.
        }

        $columns = [];
        if (!$this->db->fieldExists('is_finalized', 'rapor')) {
            $columns['is_finalized'] = [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'status_kenaikan',
            ];
        }
        if (!$this->db->fieldExists('finalized_at', 'rapor')) {
            $columns['finalized_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'is_finalized',
            ];
        }
        if (!$this->db->fieldExists('finalized_by', 'rapor')) {
            $columns['finalized_by'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'finalized_at',
            ];
        }

        if (!empty($columns)) {
            $this->forge->addColumn('rapor', $columns);
        }

        $db->query('DELETE r1 FROM rapor r1 INNER JOIN rapor r2 ON r1.id_siswa = r2.id_siswa AND r1.id_tahun_ajaran = r2.id_tahun_ajaran AND r1.id_rapor < r2.id_rapor');

        try {
            $db->query('ALTER TABLE `rapor` ADD UNIQUE KEY `unique_rapor_siswa_tahun` (`id_siswa`, `id_tahun_ajaran`)');
        } catch (\Throwable $e) {
            // Key already exists.
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        try {
            $db->query('ALTER TABLE `remedial` DROP INDEX `unique_remedial_nilai_akhir`');
        } catch (\Throwable $e) {
            // Ignore when key does not exist.
        }

        try {
            $db->query('ALTER TABLE `rapor` DROP INDEX `unique_rapor_siswa_tahun`');
        } catch (\Throwable $e) {
            // Ignore when key does not exist.
        }

        foreach (['finalized_by', 'finalized_at', 'is_finalized'] as $column) {
            if ($this->db->fieldExists($column, 'rapor')) {
                $this->forge->dropColumn('rapor', $column);
            }
        }
    }
}
