<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordToSiswa extends Migration
{
    public function up()
    {
        $this->forge->addColumn('siswa', [
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'nis',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('siswa', 'password');
    }
}
