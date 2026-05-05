<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Hapus tabel log_aktivitas karena fitur tidak diperlukan.
 */
class DropLogAktivitas extends Migration
{
    public function up(): void
    {
        $this->forge->dropTable('log_aktivitas', true);
    }

    public function down(): void
    {
        $this->forge->addField([
            'id_log' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'aksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'tabel' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'id_record' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_log', true);
        $this->forge->addForeignKey('id_user', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('log_aktivitas');
    }
}
