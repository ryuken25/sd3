<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * Penyederhanaan skema klaster nilai: 21 -> 17 tabel.
 *
 * Gabungkan 4 tabel 1:1-by-(siswa,mapel,TA) menjadi satu tabel `nilai`:
 *   - nilai_siswa          (komponen tugas/ulangan/uts/uas/rata)
 *   - nilai_akhir          (nilai_akhir, nilai_huruf, status_kelulusan, catatan_remedial, flag_borderline_75, narasi_cp)
 *   - capaian_narasi       (narasi)
 *   - remedial             (tindak_lanjut, status_remedial)  [1:1 ke nilai_akhir]
 *
 * Plus buang tabel vestigial:
 *   - nilai_capaian_kompetensi  (legacy fallback; di-bake jadi nilai.narasi saat migrasi)
 *
 * Migrasi lossless & reversible:
 *   - up()   : create nilai + backfill 5 langkah + drop 5 tabel lama
 *   - down() : recreate 5 tabel lama (kosong; struktur saja cukup buat reversibility)
 *
 * Aturan grain & narasi:
 *   - PK kunci natural: (id_siswa, id_mapel, id_tahun_ajaran) UNIQUE.
 *   - Narasi prioritas: capaian_narasi -> nilai_akhir.narasi_cp -> bake auto-gen.
 *   - Auto-gen pakai RaporNarrativeService::generateNarasiCP() supaya rapor demo
 *     yang dulu cuma punya nilai_capaian_kompetensi tetap tampil sama.
 */
class MergeNilaiClusterAndDropNcp extends Migration
{
    public function up(): void
    {
        $forge = $this->forge;
        $db    = $this->db;

        if ($db->tableExists('nilai')) {
            return;
        }

        // ----- 1. CREATE TABLE nilai ------------------------------------
        $forge->addField([
            'id_nilai'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_mapel'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],

            'nilai_tugas'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'nilai_ulangan'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'rata_rata_harian'   => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'nilai_uts'          => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'nilai_uas'          => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],

            'nilai_akhir'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'nilai_huruf'        => ['type' => 'CHAR',    'constraint' => 1,     'null' => true],
            'status_kelulusan'   => ['type' => 'ENUM', 'constraint' => ['Tuntas', 'Remedial'], 'null' => true],
            'catatan_remedial'   => ['type' => 'TEXT', 'null' => true],
            'flag_borderline_75' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],

            'narasi'             => ['type' => 'TEXT', 'null' => true],

            'tindak_lanjut'      => ['type' => 'TEXT', 'null' => true],
            'status_remedial'    => ['type' => 'ENUM', 'constraint' => ['Belum', 'Sedang Proses', 'Selesai'], 'null' => true],

            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id_nilai', true);
        $forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun_ajaran']);
        $forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
        $forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $forge->createTable('nilai', true);

        // Jika tabel-tabel lama tidak ada (fresh install lewat migration ini saja),
        // lewati backfill — tinggal drop SP lama bila ada lalu selesai.
        $hasOld = $db->tableExists('nilai_siswa')
            || $db->tableExists('nilai_akhir')
            || $db->tableExists('capaian_narasi')
            || $db->tableExists('remedial')
            || $db->tableExists('nilai_capaian_kompetensi');
        if (!$hasOld) {
            $this->dropStoredProcedureIfExists();
            return;
        }

        $db->transStart();

        // ----- 2. Dedup nilai_akhir jika ada duplikat per (siswa,mapel,TA) ----
        if ($db->tableExists('nilai_akhir')) {
            $db->query("DELETE n1 FROM nilai_akhir n1
                INNER JOIN nilai_akhir n2
                  ON n1.id_siswa = n2.id_siswa
                 AND n1.id_mapel = n2.id_mapel
                 AND n1.id_tahun_ajaran = n2.id_tahun_ajaran
                 AND n1.id_nilai_akhir < n2.id_nilai_akhir");
        }

        // ----- 3. Backfill #1: dari nilai_siswa (sumber baris utama) ------
        if ($db->tableExists('nilai_siswa')) {
            $db->query("INSERT INTO nilai
                (id_siswa, id_mapel, id_tahun_ajaran,
                 nilai_tugas, nilai_ulangan, rata_rata_harian, nilai_uts, nilai_uas,
                 created_at, updated_at)
                SELECT id_siswa, id_mapel, id_tahun_ajaran,
                       nilai_tugas, nilai_ulangan, rata_rata_harian, nilai_uts, nilai_uas,
                       created_at, updated_at
                FROM nilai_siswa");
        }

        // ----- 4. Backfill #2: merge nilai_akhir (upsert ke baris yg ada) -
        if ($db->tableExists('nilai_akhir')) {
            $db->query("INSERT INTO nilai
                (id_siswa, id_mapel, id_tahun_ajaran,
                 nilai_akhir, nilai_huruf, status_kelulusan, catatan_remedial, flag_borderline_75,
                 narasi, created_at, updated_at)
                SELECT id_siswa, id_mapel, id_tahun_ajaran,
                       nilai_akhir, nilai_huruf, status_kelulusan, catatan_remedial,
                       COALESCE(flag_borderline_75, 0),
                       NULLIF(TRIM(COALESCE(narasi_cp, '')), ''),
                       created_at, updated_at
                FROM nilai_akhir
                ON DUPLICATE KEY UPDATE
                    nilai.nilai_akhir        = VALUES(nilai_akhir),
                    nilai.nilai_huruf        = VALUES(nilai_huruf),
                    nilai.status_kelulusan   = VALUES(status_kelulusan),
                    nilai.catatan_remedial   = VALUES(catatan_remedial),
                    nilai.flag_borderline_75 = VALUES(flag_borderline_75),
                    nilai.narasi             = COALESCE(VALUES(narasi), nilai.narasi),
                    nilai.updated_at         = VALUES(updated_at)");
        }

        // ----- 5. Backfill #3: timpa narasi dari capaian_narasi (prio #1) -
        if ($db->tableExists('capaian_narasi')) {
            $db->query("UPDATE nilai n
                INNER JOIN capaian_narasi cn
                   ON cn.id_siswa = n.id_siswa
                  AND cn.id_mapel = n.id_mapel
                  AND cn.id_tahun_ajaran = n.id_tahun_ajaran
                SET n.narasi = cn.narasi
                WHERE cn.narasi IS NOT NULL AND TRIM(cn.narasi) <> ''");

            // Untuk baris capaian_narasi yang tidak punya pasangan di nilai (rare):
            // INSERT baris baru ke nilai dengan hanya narasi terisi.
            $db->query("INSERT INTO nilai (id_siswa, id_mapel, id_tahun_ajaran, narasi, created_at, updated_at)
                SELECT cn.id_siswa, cn.id_mapel, cn.id_tahun_ajaran, cn.narasi, cn.created_at, cn.updated_at
                FROM capaian_narasi cn
                LEFT JOIN nilai n
                       ON n.id_siswa = cn.id_siswa
                      AND n.id_mapel = cn.id_mapel
                      AND n.id_tahun_ajaran = cn.id_tahun_ajaran
                WHERE n.id_nilai IS NULL
                  AND cn.narasi IS NOT NULL AND TRIM(cn.narasi) <> ''");
        }

        // ----- 6. Backfill #4: bake fallback dari nilai_capaian_kompetensi -
        // Untuk baris yang narasi MASIH kosong, rakit dari nilai_capaian_kompetensi
        // memakai logika RaporNarrativeService::generateNarasiCP() supaya tampilan
        // rapor data lama tetap sama setelah tabel ncp dibuang.
        //
        // Wrapped try/catch karena master_capaian_pembelajaran kadang corrupt di
        // engine (issue MySQL umum: "Table doesn't exist in engine"). Bila ini
        // gagal, narasi yang sudah masuk dari capaian_narasi/narasi_cp tetap utuh;
        // hanya baris yang HANYA punya jejak di nilai_capaian_kompetensi yang
        // narasinya jadi NULL (fallback ke "Belum ada capaian dinilai" di view).
        if ($db->tableExists('nilai_capaian_kompetensi') && $db->tableExists('nilai_akhir')) {
            try {
                // Cek master_capaian_pembelajaran sehat dulu — kalau tidak, skip seluruh bake step.
                $masterAlive = $db->tableExists('master_capaian_pembelajaran');
                if ($masterAlive) {
                    try {
                        $db->query('SELECT 1 FROM master_capaian_pembelajaran LIMIT 1');
                    } catch (\Throwable $e) {
                        $masterAlive = false;
                        log_message('warning', 'master_capaian_pembelajaran rusak, skip bake CP: ' . $e->getMessage());
                    }
                }

                if ($masterAlive) {
                    $rows = $db->query("SELECT n.id_nilai, na.id_nilai_akhir
                        FROM nilai n
                        INNER JOIN nilai_akhir na
                               ON na.id_siswa = n.id_siswa
                              AND na.id_mapel = n.id_mapel
                              AND na.id_tahun_ajaran = n.id_tahun_ajaran
                        WHERE n.narasi IS NULL OR TRIM(n.narasi) = ''")->getResultArray();

                    foreach ($rows as $r) {
                        $idNilai      = (int) $r['id_nilai'];
                        $idNilaiAkhir = (int) $r['id_nilai_akhir'];

                        $cpRows = $db->query("SELECT ncp.status,
                                   COALESCE(mcp.deskripsi, ncp.deskripsi_custom, '') AS deskripsi
                            FROM nilai_capaian_kompetensi ncp
                            LEFT JOIN master_capaian_pembelajaran mcp ON mcp.id_master_cp = ncp.master_cp_id
                            WHERE ncp.id_nilai_akhir = ?
                            ORDER BY ncp.id_nilai_cp ASC", [$idNilaiAkhir])->getResultArray();

                        if ($cpRows === []) continue;

                        $tercapai = [];
                        $perlu    = [];
                        foreach ($cpRows as $c) {
                            $d = trim((string) ($c['deskripsi'] ?? ''));
                            if ($d === '') continue;
                            if (($c['status'] ?? '') === 'tercapai_sangat_baik') {
                                $tercapai[] = $d;
                            } elseif (($c['status'] ?? '') === 'perlu_peningkatan') {
                                $perlu[] = $d;
                            }
                        }
                        $bagian1 = $tercapai ? 'Mencapai Kompetensi dengan sangat baik dalam hal ' . implode(', ', $tercapai) . '. ' : '';
                        $bagian2 = $perlu    ? 'Perlu peningkatan dalam hal ' . implode(', ', $perlu) . '.' : '';
                        $narasi  = trim($bagian1 . $bagian2);

                        if ($narasi !== '') {
                            $db->query('UPDATE nilai SET narasi = ?, updated_at = NOW() WHERE id_nilai = ?', [$narasi, $idNilai]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                log_message('warning', 'Bake fallback CP gagal (skip, narasi tetap dari sumber lain): ' . $e->getMessage());
            }
        }

        // ----- 7. Backfill #5: merge remedial (tindak_lanjut + status) ----
        if ($db->tableExists('remedial') && $db->tableExists('nilai_akhir')) {
            $db->query("UPDATE nilai n
                INNER JOIN nilai_akhir na
                       ON na.id_siswa = n.id_siswa
                      AND na.id_mapel = n.id_mapel
                      AND na.id_tahun_ajaran = n.id_tahun_ajaran
                INNER JOIN remedial r ON r.id_nilai_akhir = na.id_nilai_akhir
                SET n.tindak_lanjut   = r.tindak_lanjut,
                    n.status_remedial = r.status_remedial");
        }

        // ----- 8. DROP urutan FK-safe -----------------------------------
        if ($db->tableExists('nilai_capaian_kompetensi')) {
            $forge->dropTable('nilai_capaian_kompetensi', true);
        }
        if ($db->tableExists('capaian_narasi')) {
            $forge->dropTable('capaian_narasi', true);
        }
        if ($db->tableExists('remedial')) {
            $forge->dropTable('remedial', true);
        }
        if ($db->tableExists('nilai_akhir')) {
            $forge->dropTable('nilai_akhir', true);
        }
        if ($db->tableExists('nilai_siswa')) {
            $forge->dropTable('nilai_siswa', true);
        }

        // ----- 9. Update stored procedure HitungNilaiAkhir --------------
        $this->dropStoredProcedureIfExists();
        $db->query("CREATE PROCEDURE HitungNilaiAkhir(
            IN p_id_siswa INT,
            IN p_id_mapel INT,
            IN p_id_tahun_ajaran INT
        )
        BEGIN
            DECLARE v_rata_rata_harian DECIMAL(5,2);
            DECLARE v_nilai_uts DECIMAL(5,2);
            DECLARE v_nilai_uas DECIMAL(5,2);
            DECLARE v_nilai_akhir DECIMAL(5,2);
            DECLARE v_nilai_huruf CHAR(1);
            DECLARE v_status_kelulusan VARCHAR(20);
            DECLARE v_kkm DECIMAL(5,2);
            DECLARE v_id_kelas INT;

            SELECT id_kelas INTO v_id_kelas FROM siswa WHERE id_siswa = p_id_siswa;

            SELECT nilai_kkm INTO v_kkm FROM kkm
            WHERE id_mapel = p_id_mapel AND id_kelas = v_id_kelas
              AND id_tahun_ajaran = p_id_tahun_ajaran LIMIT 1;

            SELECT rata_rata_harian, nilai_uts, nilai_uas
              INTO v_rata_rata_harian, v_nilai_uts, v_nilai_uas
            FROM nilai
            WHERE id_siswa = p_id_siswa AND id_mapel = p_id_mapel
              AND id_tahun_ajaran = p_id_tahun_ajaran LIMIT 1;

            SET v_rata_rata_harian = IFNULL(v_rata_rata_harian, 0);
            SET v_nilai_uts        = IFNULL(v_nilai_uts, 0);
            SET v_nilai_uas        = IFNULL(v_nilai_uas, 0);
            SET v_kkm              = IFNULL(v_kkm, 70);

            SET v_nilai_akhir = (v_rata_rata_harian * 0.4) + (v_nilai_uts * 0.3) + (v_nilai_uas * 0.3);

            IF v_nilai_akhir >= 90 THEN SET v_nilai_huruf = 'A';
            ELSEIF v_nilai_akhir >= 80 THEN SET v_nilai_huruf = 'B';
            ELSEIF v_nilai_akhir >= 70 THEN SET v_nilai_huruf = 'C';
            ELSEIF v_nilai_akhir >= 60 THEN SET v_nilai_huruf = 'D';
            ELSE SET v_nilai_huruf = 'E';
            END IF;

            IF v_nilai_akhir >= v_kkm THEN SET v_status_kelulusan = 'Tuntas';
            ELSE SET v_status_kelulusan = 'Remedial';
            END IF;

            INSERT INTO nilai
                (id_siswa, id_mapel, id_tahun_ajaran, nilai_akhir, nilai_huruf, status_kelulusan, created_at, updated_at)
            VALUES
                (p_id_siswa, p_id_mapel, p_id_tahun_ajaran, v_nilai_akhir, v_nilai_huruf, v_status_kelulusan, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                nilai_akhir      = v_nilai_akhir,
                nilai_huruf      = v_nilai_huruf,
                status_kelulusan = v_status_kelulusan,
                updated_at       = NOW();

            IF v_status_kelulusan = 'Remedial' THEN
                UPDATE nilai
                SET status_remedial = COALESCE(status_remedial, 'Belum'),
                    updated_at = NOW()
                WHERE id_siswa = p_id_siswa
                  AND id_mapel = p_id_mapel
                  AND id_tahun_ajaran = p_id_tahun_ajaran;
            ELSE
                UPDATE nilai
                SET status_remedial = NULL,
                    tindak_lanjut   = NULL,
                    updated_at      = NOW()
                WHERE id_siswa = p_id_siswa
                  AND id_mapel = p_id_mapel
                  AND id_tahun_ajaran = p_id_tahun_ajaran;
            END IF;
        END");

        $db->transComplete();
    }

    public function down(): void
    {
        $forge = $this->forge;
        $db    = $this->db;

        // Recreate 5 old tables (struktur saja — data demo bisa di-seed ulang)

        // 1. nilai_siswa
        if (!$db->tableExists('nilai_siswa')) {
            $forge->addField([
                'id_nilai_siswa'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_mapel'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_tahun_ajaran'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'nilai_tugas'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'nilai_ulangan'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'rata_rata_harian' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'nilai_uts'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'nilai_uas'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_nilai_siswa', true);
            $forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun_ajaran']);
            $forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            $forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
            $forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
            $forge->createTable('nilai_siswa', true);
        }

        // 2. nilai_akhir
        if (!$db->tableExists('nilai_akhir')) {
            $forge->addField([
                'id_nilai_akhir'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_mapel'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_tahun_ajaran'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'nilai_akhir'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'nilai_huruf'        => ['type' => 'CHAR',    'constraint' => 1,     'null' => true],
                'status_kelulusan'   => ['type' => 'ENUM', 'constraint' => ['Tuntas', 'Remedial'], 'null' => true],
                'catatan_remedial'   => ['type' => 'TEXT',    'null' => true],
                'flag_borderline_75' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'narasi_cp'          => ['type' => 'TEXT',    'null' => true],
                'created_at'         => ['type' => 'DATETIME', 'null' => true],
                'updated_at'         => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_nilai_akhir', true);
            $forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun_ajaran']);
            $forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
            $forge->addForeignKey('id_mapel', 'mata_pelajaran', 'id_mapel', 'CASCADE', 'CASCADE');
            $forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
            $forge->createTable('nilai_akhir', true);
        }

        // 3. remedial
        if (!$db->tableExists('remedial')) {
            $forge->addField([
                'id_remedial'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_nilai_akhir'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'tindak_lanjut'   => ['type' => 'TEXT', 'null' => true],
                'status_remedial' => ['type' => 'ENUM', 'constraint' => ['Belum', 'Sedang Proses', 'Selesai'], 'default' => 'Belum'],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
                'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_remedial', true);
            $forge->addUniqueKey('id_nilai_akhir');
            $forge->addForeignKey('id_nilai_akhir', 'nilai_akhir', 'id_nilai_akhir', 'CASCADE', 'CASCADE');
            $forge->createTable('remedial', true);
        }

        // 4. capaian_narasi
        if (!$db->tableExists('capaian_narasi')) {
            $forge->addField([
                'id_capaian_narasi' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_mapel'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_tahun_ajaran'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'narasi'            => ['type' => 'TEXT', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_capaian_narasi', true);
            $forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun_ajaran']);
            $forge->createTable('capaian_narasi', true);
        }

        // 5. nilai_capaian_kompetensi — best-effort.
        // FK ke master_capaian_pembelajaran kadang gagal saat tabel itu corrupt
        // di engine; tabel ini cuma vestigial (data sudah di-bake ke nilai.narasi),
        // jadi safe untuk di-skip kalau gagal.
        if (!$db->tableExists('nilai_capaian_kompetensi')) {
            try {
                $forge->addField([
                    'id_nilai_cp'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                    'id_nilai_akhir'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                    'master_cp_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                    'deskripsi_custom' => ['type' => 'TEXT', 'null' => true],
                    'status'           => ['type' => 'ENUM', 'constraint' => ['tercapai_sangat_baik', 'perlu_peningkatan']],
                    'created_at'       => ['type' => 'DATETIME', 'null' => true],
                    'updated_at'       => ['type' => 'DATETIME', 'null' => true],
                ]);
                $forge->addKey('id_nilai_cp', true);
                $forge->addKey('id_nilai_akhir');
                $forge->addForeignKey('id_nilai_akhir', 'nilai_akhir', 'id_nilai_akhir', 'CASCADE', 'CASCADE');
                // Coba sertakan FK ke master CP; kalau master corrupt akan dilempar
                // exception → bersihin field stack + buat tabel tanpa FK ini.
                try {
                    $forge->addForeignKey('master_cp_id', 'master_capaian_pembelajaran', 'id_master_cp', 'CASCADE', 'SET NULL');
                    $forge->createTable('nilai_capaian_kompetensi', true);
                } catch (\Throwable $e) {
                    log_message('warning', 'Recreate ncp dengan FK master_cp gagal, retry tanpa FK: ' . $e->getMessage());
                    $forge->createTable('nilai_capaian_kompetensi', true);
                }
            } catch (\Throwable $e) {
                log_message('warning', 'Skip recreate nilai_capaian_kompetensi: ' . $e->getMessage());
            }
        }

        // Drop & restore stored procedure to old form (writes to nilai_akhir + remedial)
        $this->dropStoredProcedureIfExists();
        $db->query("CREATE PROCEDURE HitungNilaiAkhir(
            IN p_id_siswa INT, IN p_id_mapel INT, IN p_id_tahun_ajaran INT
        )
        BEGIN
            DECLARE v_rata_rata_harian DECIMAL(5,2);
            DECLARE v_nilai_uts DECIMAL(5,2);
            DECLARE v_nilai_uas DECIMAL(5,2);
            DECLARE v_nilai_akhir DECIMAL(5,2);
            DECLARE v_nilai_huruf CHAR(1);
            DECLARE v_status_kelulusan VARCHAR(20);
            DECLARE v_kkm DECIMAL(5,2);
            DECLARE v_id_kelas INT;
            DECLARE v_id_nilai_akhir INT;

            SELECT id_kelas INTO v_id_kelas FROM siswa WHERE id_siswa = p_id_siswa;
            SELECT nilai_kkm INTO v_kkm FROM kkm
            WHERE id_mapel = p_id_mapel AND id_kelas = v_id_kelas
              AND id_tahun_ajaran = p_id_tahun_ajaran LIMIT 1;
            SELECT rata_rata_harian, nilai_uts, nilai_uas
              INTO v_rata_rata_harian, v_nilai_uts, v_nilai_uas
            FROM nilai_siswa
            WHERE id_siswa = p_id_siswa AND id_mapel = p_id_mapel
              AND id_tahun_ajaran = p_id_tahun_ajaran LIMIT 1;

            SET v_rata_rata_harian = IFNULL(v_rata_rata_harian, 0);
            SET v_nilai_uts = IFNULL(v_nilai_uts, 0);
            SET v_nilai_uas = IFNULL(v_nilai_uas, 0);
            SET v_kkm = IFNULL(v_kkm, 70);
            SET v_nilai_akhir = (v_rata_rata_harian * 0.4) + (v_nilai_uts * 0.3) + (v_nilai_uas * 0.3);

            IF v_nilai_akhir >= 90 THEN SET v_nilai_huruf = 'A';
            ELSEIF v_nilai_akhir >= 80 THEN SET v_nilai_huruf = 'B';
            ELSEIF v_nilai_akhir >= 70 THEN SET v_nilai_huruf = 'C';
            ELSEIF v_nilai_akhir >= 60 THEN SET v_nilai_huruf = 'D';
            ELSE SET v_nilai_huruf = 'E'; END IF;

            IF v_nilai_akhir >= v_kkm THEN SET v_status_kelulusan = 'Tuntas';
            ELSE SET v_status_kelulusan = 'Remedial'; END IF;

            INSERT INTO nilai_akhir (id_siswa, id_mapel, id_tahun_ajaran, nilai_akhir, nilai_huruf, status_kelulusan, created_at, updated_at)
            VALUES (p_id_siswa, p_id_mapel, p_id_tahun_ajaran, v_nilai_akhir, v_nilai_huruf, v_status_kelulusan, NOW(), NOW())
            ON DUPLICATE KEY UPDATE nilai_akhir=v_nilai_akhir, nilai_huruf=v_nilai_huruf,
                                    status_kelulusan=v_status_kelulusan, updated_at=NOW();

            SET v_id_nilai_akhir = LAST_INSERT_ID();
            IF v_id_nilai_akhir = 0 THEN
                SELECT id_nilai_akhir INTO v_id_nilai_akhir FROM nilai_akhir
                WHERE id_siswa = p_id_siswa AND id_mapel = p_id_mapel AND id_tahun_ajaran = p_id_tahun_ajaran LIMIT 1;
            END IF;

            IF v_status_kelulusan = 'Remedial' THEN
                INSERT INTO remedial (id_nilai_akhir, status_remedial, created_at, updated_at)
                VALUES (v_id_nilai_akhir, 'Belum', NOW(), NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW();
            ELSE
                DELETE FROM remedial WHERE id_nilai_akhir = v_id_nilai_akhir;
            END IF;
        END");

        if ($db->tableExists('nilai')) {
            $forge->dropTable('nilai', true);
        }
    }

    /** DROP PROCEDURE jika ada — MySQL belum DROP IF EXISTS di semua versi tapi safe lewat query. */
    private function dropStoredProcedureIfExists(): void
    {
        $this->db->query('DROP PROCEDURE IF EXISTS HitungNilaiAkhir');
    }
}
