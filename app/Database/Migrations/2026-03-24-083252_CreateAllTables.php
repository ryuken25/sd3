<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAllTables extends Migration
{
    public function up()
    {
        // 1. Table: users
        $this->forge->addField([
            'id_user' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nama_lengkap' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'no_telp' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'level' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'guru', 'orang_tua'],
                'default' => 'guru',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default' => 'aktif',
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
        $this->forge->addKey('id_user', true);
        $this->forge->createTable('users');

        // 2. Table: tahun_ajaran
        $this->forge->addField([
            'id_tahun_ajaran' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tahun_ajaran' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'semester' => [
                'type' => 'ENUM',
                'constraint' => ['Ganjil', 'Genap'],
            ],
            'aktif' => [
                'type' => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default' => 'nonaktif',
            ],
            'status_pengisian' => [
                'type' => 'ENUM',
                'constraint' => ['Buka', 'Kunci'],
                'default' => 'Buka',
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
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
        $this->forge->addKey('id_tahun_ajaran', true);
        $this->forge->createTable('tahun_ajaran');

        // 3. Table: kelas
        $this->forge->addField([
            'id_kelas' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_kelas' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'tingkat' => [
                'type' => 'INT',
                'constraint' => 1,
            ],
            'wali_kelas' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addKey('id_kelas', true);
        $this->forge->addForeignKey('wali_kelas', 'users', 'id_user', 'SET NULL', 'CASCADE');
        $this->forge->createTable('kelas');

        // 4. Table: mata_pelajaran
        $this->forge->addField([
            'id_mapel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'kode_mapel' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'nama_mapel' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'kelompok' => [
                'type' => 'ENUM',
                'constraint' => ['A', 'B'],
                'default' => 'A',
                'comment' => 'A=Nasional, B=Muatan Lokal',
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
        $this->forge->addKey('id_mapel', true);
        $this->forge->createTable('mata_pelajaran');

        // 5. Table: siswa
        $this->forge->addField([
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nis' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'nisn' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'nama_siswa' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'jenis_kelamin' => [
                'type' => 'ENUM',
                'constraint' => ['L', 'P'],
            ],
            'tempat_lahir' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'id_kelas' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nama_ayah' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'nama_ibu' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'no_telp_ortu' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['aktif', 'lulus', 'pindah', 'keluar'],
                'default' => 'aktif',
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
        $this->forge->addKey('id_siswa', true);
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa');

        // 6. Table: kkm
        $this->forge->addField([
            'id_kkm' => [
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
            'id_tahun_ajaran' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nilai_kkm' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
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
        $this->forge->addKey('id_kkm', true);
        $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kkm');

        // 7. Table: wali_siswa
        $this->forge->addField([
            'id_wali_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'hubungan' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'wali',
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
        $this->forge->addKey('id_wali_siswa', true);
        $this->forge->addForeignKey('id_user', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wali_siswa');

        // 8. Table: nilai_harian
        $this->forge->addField([
            'id_nilai_harian' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_mapel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_tahun_ajaran' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nilai_tugas' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'nilai_ulangan' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'rata_rata_harian' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
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
        $this->forge->addKey('id_nilai_harian', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->createTable('nilai_harian');

        // 9. Table: nilai_ujian
        $this->forge->addField([
            'id_nilai_ujian' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_mapel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_tahun_ajaran' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nilai_uts' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'nilai_uas' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
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
        $this->forge->addKey('id_nilai_ujian', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->createTable('nilai_ujian');

        // 10. Table: nilai_akhir
        $this->forge->addField([
            'id_nilai_akhir' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_mapel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_tahun_ajaran' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nilai_akhir' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'nilai_huruf' => [
                'type' => 'CHAR',
                'constraint' => 1,
            ],
            'status_kelulusan' => [
                'type' => 'ENUM',
                'constraint' => ['Tuntas', 'Remedial'],
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
        $this->forge->addKey('id_nilai_akhir', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        // Add unique constraint for stored procedure's ON DUPLICATE KEY UPDATE
        $this->forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun_ajaran'], 'unique_nilai_akhir');
        $this->forge->createTable('nilai_akhir');

        // 11. Table: remedial
        $this->forge->addField([
            'id_remedial' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_nilai_akhir' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tindak_lanjut' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status_remedial' => [
                'type' => 'ENUM',
                'constraint' => ['Belum', 'Sedang Proses', 'Selesai'],
                'default' => 'Belum',
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
        $this->forge->addKey('id_remedial', true);
        $this->forge->addUniqueKey('id_nilai_akhir', 'unique_remedial_nilai_akhir');
        $this->forge->addForeignKey('id_nilai_akhir', 'nilai_akhir', 'id_nilai_akhir', 'CASCADE', 'CASCADE');
        $this->forge->createTable('remedial');

        // 12. Table: rapor
        $this->forge->addField([
            'id_rapor' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_tahun_ajaran' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'sakit' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 0,
            ],
            'izin' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 0,
            ],
            'alpa' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 0,
            ],
            'catatan_wali_kelas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status_kenaikan' => [
                'type' => 'ENUM',
                'constraint' => ['Naik', 'Tidak Naik', 'Lulus'],
                'null' => true,
            ],
            'is_finalized' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'finalized_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'finalized_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addKey('id_rapor', true);
        $this->forge->addUniqueKey(['id_siswa', 'id_tahun_ajaran'], 'unique_rapor_siswa_tahun');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rapor');

        // 13. Table: log_aktivitas (Audit Trail)
        $this->forge->addField([
            'id_log' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'aksi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'tabel' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'id_record' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
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

        // 14. Table: request_buka_nilai (Approval System)
        $this->forge->addField([
            'id_request' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_guru' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_tahun_ajaran' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'alasan' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default' => 'pending',
            ],
            'tanggal_akses' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'catatan_admin' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('id_request', true);
        $this->forge->addForeignKey('id_guru', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id_user', 'SET NULL', 'CASCADE');
        $this->forge->createTable('request_buka_nilai');
    }

    public function down()
    {
        $this->forge->dropTable('request_buka_nilai', true);
        $this->forge->dropTable('log_aktivitas', true);
        $this->forge->dropTable('rapor', true);
        $this->forge->dropTable('remedial', true);
        $this->forge->dropTable('nilai_akhir', true);
        $this->forge->dropTable('nilai_ujian', true);
        $this->forge->dropTable('nilai_harian', true);
        $this->forge->dropTable('wali_siswa', true);
        $this->forge->dropTable('kkm', true);
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('mata_pelajaran', true);
        $this->forge->dropTable('kelas', true);
        $this->forge->dropTable('tahun_ajaran', true);
        $this->forge->dropTable('users', true);
    }
}
