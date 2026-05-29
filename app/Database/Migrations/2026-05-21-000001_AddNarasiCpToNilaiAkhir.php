<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Kolom narasi capaian manual per nilai_akhir.
 * Guru menyimpan teks final (boleh hasil prefill band/template) verbatim.
 */
class AddNarasiCpToNilaiAkhir extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('narasi_cp', 'nilai_akhir')) {
            $this->forge->addColumn('nilai_akhir', [
                'narasi_cp' => [
                    'type'  => 'TEXT',
                    'null'  => true,
                    'after' => 'status_kelulusan',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('narasi_cp', 'nilai_akhir')) {
            $this->forge->dropColumn('nilai_akhir', 'narasi_cp');
        }
    }
}
