<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixSchemaInconsistencies extends Migration
{
    public function up()
    {
        // Historical migration kept for compatibility.
        // Redundant fields (users.email, mata_pelajaran.status, kelas.tahun_ajaran_id)
        // are no longer introduced in fresh installations.
    }

    public function down()
    {
        // No-op. Migration intentionally left empty to preserve numbering without
        // reintroducing redundant schema pieces on rollback.
    }
}
