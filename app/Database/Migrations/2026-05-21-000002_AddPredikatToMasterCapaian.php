<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Template CP per band predikat A/B/C/D. Baris seed lama (per-kalimat) tetap
 * dengan predikat = NULL dan diabaikan oleh alur band baru.
 */
class AddPredikatToMasterCapaian extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('predikat', 'master_capaian_pembelajaran')) {
            $this->forge->addColumn('master_capaian_pembelajaran', [
                'predikat' => [
                    'type'       => 'ENUM',
                    'constraint' => ['A', 'B', 'C', 'D'],
                    'null'       => true,
                    'after'      => 'semester',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('predikat', 'master_capaian_pembelajaran')) {
            $this->forge->dropColumn('master_capaian_pembelajaran', 'predikat');
        }
    }
}
