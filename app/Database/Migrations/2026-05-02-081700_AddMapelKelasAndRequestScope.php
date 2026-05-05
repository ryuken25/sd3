<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMapelKelasAndRequestScope extends Migration
{
    public function up(): void
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();

        if (!in_array('mapel_kelas', $tables, true)) {
            $this->forge->addField([
                'id_mapel_kelas' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'id_mapel' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'id_kelas' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_mapel_kelas', true);
            $this->forge->addUniqueKey(['id_mapel', 'id_kelas'], 'unique_mapel_kelas');
            $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
            $this->forge->createTable('mapel_kelas');
        }

        $this->seedMapelKelasFromExistingData($db);

        if (in_array('request_buka_nilai', $db->listTables(), true)) {
            if (!$this->db->fieldExists('id_kelas', 'request_buka_nilai')) {
                $this->forge->addColumn('request_buka_nilai', [
                    'id_kelas' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                        'after' => 'id_tahun_ajaran',
                    ],
                ]);
            }

            if (!$this->db->fieldExists('id_mapel', 'request_buka_nilai')) {
                $this->forge->addColumn('request_buka_nilai', [
                    'id_mapel' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                        'after' => 'id_kelas',
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('id_mapel', 'request_buka_nilai')) {
            $this->forge->dropColumn('request_buka_nilai', 'id_mapel');
        }

        if ($this->db->fieldExists('id_kelas', 'request_buka_nilai')) {
            $this->forge->dropColumn('request_buka_nilai', 'id_kelas');
        }

        $this->forge->dropTable('mapel_kelas', true);
    }

    private function seedMapelKelasFromExistingData($db): void
    {
        $tables = $db->listTables();
        if (!in_array('mapel_kelas', $tables, true)) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        if (in_array('kkm', $tables, true)) {
            $relations = $db->table('kkm')
                ->select('DISTINCT id_mapel, id_kelas', false)
                ->where('id_mapel IS NOT NULL', null, false)
                ->where('id_kelas IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

            foreach ($relations as $relation) {
                $this->insertMapelKelasIfMissing($db, (int) $relation['id_mapel'], (int) $relation['id_kelas'], $now);
            }
        }

        $hasRelations = $db->table('mapel_kelas')->countAllResults() > 0;
        if ($hasRelations || !in_array('mata_pelajaran', $tables, true) || !in_array('kelas', $tables, true)) {
            return;
        }

        $mapelRows = $db->table('mata_pelajaran')->select('id_mapel')->get()->getResultArray();
        $kelasRows = $db->table('kelas')->select('id_kelas')->get()->getResultArray();

        foreach ($kelasRows as $kelas) {
            foreach ($mapelRows as $mapel) {
                $this->insertMapelKelasIfMissing($db, (int) $mapel['id_mapel'], (int) $kelas['id_kelas'], $now);
            }
        }
    }

    private function insertMapelKelasIfMissing($db, int $idMapel, int $idKelas, string $now): void
    {
        if ($idMapel <= 0 || $idKelas <= 0) {
            return;
        }

        $exists = $db->table('mapel_kelas')
            ->where('id_mapel', $idMapel)
            ->where('id_kelas', $idKelas)
            ->get()
            ->getRowArray();

        if ($exists) {
            return;
        }

        $db->table('mapel_kelas')->insert([
            'id_mapel' => $idMapel,
            'id_kelas' => $idKelas,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
