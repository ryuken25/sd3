<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Narasi kokurikuler manual per rapor. Wali kelas boleh edit; bila kosong
 * rapor fallback ke narasi auto-generate (RaporNarrativeService).
 */
class AddNarasiKokoToRapor extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('narasi_koko', 'rapor')) {
            $this->forge->addColumn('rapor', [
                'narasi_koko' => [
                    'type'  => 'TEXT',
                    'null'  => true,
                    'after' => 'catatan_wali_kelas',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('narasi_koko', 'rapor')) {
            $this->forge->dropColumn('rapor', 'narasi_koko');
        }
    }
}
