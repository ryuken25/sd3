<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Flag ekskul wajib (mis. Pramuka) — selalu tersimpan untuk tiap siswa.
 */
class AddWajibToMasterEkstrakurikuler extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('wajib', 'master_ekstrakurikuler')) {
            $this->forge->addColumn('master_ekstrakurikuler', [
                'wajib' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'aktif',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('wajib', 'master_ekstrakurikuler')) {
            $this->forge->dropColumn('master_ekstrakurikuler', 'wajib');
        }
    }
}
