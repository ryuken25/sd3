<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameKkmTahunAjaranId extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('kkm')) {
            return;
        }

        // Fresh install already uses `id_tahun_ajaran` from
        // [`CreateAllTables::up()`](app/Database/Migrations/2026-03-24-083252_CreateAllTables.php:9),
        // while older databases may still have `tahun_ajaran_id`.
        if ($this->db->fieldExists('tahun_ajaran_id', 'kkm') && !$this->db->fieldExists('id_tahun_ajaran', 'kkm')) {
            $this->db->query('ALTER TABLE `kkm` CHANGE `tahun_ajaran_id` `id_tahun_ajaran` INT(11) UNSIGNED NOT NULL');
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('kkm')) {
            return;
        }

        if ($this->db->fieldExists('id_tahun_ajaran', 'kkm') && !$this->db->fieldExists('tahun_ajaran_id', 'kkm')) {
            $this->db->query('ALTER TABLE `kkm` CHANGE `id_tahun_ajaran` `tahun_ajaran_id` INT(11) UNSIGNED NOT NULL');
        }
    }
}
