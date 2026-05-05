<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: Guru (dari papan data guru)
 *
 * Data OCR dari gambar guru.png:
 * 1. Ni Wayan Kasrinayanti, S.Pd  - NIP 198408132014062008 - P - Kepala Sekolah
 * 2. Nengah Sarini, S.Pd          - NIP 196803301994032007 - P - Guru Kelas II  (PNS)
 * 3. I Wayan Bayu Karsana P, S.Pd - NIP 198911082022211011 - L - Guru Kelas IV  (PPPK)
 * 4. Ni Wayan Rai Pitriani, S.Pd  - NIP 197710202021212001 - P - Guru Kelas III (PPPK)
 * 5. I Wayan Suarjana, S.Pd.H     - NIP 197407072023211004 - L - Guru Agama Hindu Kls 1-3 (PPPK)
 * 6. I G.N.B. Ariwidnya, S.Pd     - NIP 198508112022211007 - L - Guru Kelas VI  (PPPK)
 * 7. Ni Wayan Damayanti, S.Pd     - NIP 199008152022212006 - P - Guru Kelas I   (PPPK)
 * 8. Ni Luh Gede Madhavi Devi, D.S.Pd - NIP 199308222024212027 - P - Guru Kelas V (PPPK)
 * 9. Ni Gst Ayu Pt Siska D., S.Pd.H   - (Non-PNS) - P - Guru Agama Hindu / Bhs Bali Kls I-II
 *10. Ni Pt Ayu Desi Wulandari, S.Fil.H - NIP 199312022025212013 - P - Guru Agama Hindu Kls III & IV (PPPK)
 *
 * Username dibentuk dari nama depan+belakang lowercase tanpa spasi
 * Password default: password123
 * Level: guru (kecuali admin untuk operator)
 */
class SD3_GuruSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [2/9] Guru ... ";

        $defaultPass = password_hash('password123', PASSWORD_DEFAULT);

        $guruData = [
            [
                'username'     => 'kasrinayanti',
                'nama_lengkap' => 'Ni Wayan Kasrinayanti, S.Pd',
                'nip'          => '198408132014062008',
                'level'        => 'admin',
                'jabatan'      => 'Kepala Sekolah',
                'jk'           => 'P',
                'tmpt_lahir'   => 'Pupuan',
                'tgl_lahir'    => '1984-08-13',
            ],
            [
                'username'     => 'nengahsarini',
                'nama_lengkap' => 'Nengah Sarini, S.Pd',
                'nip'          => '196803301994032007',
                'level'        => 'guru',
                'jabatan'      => 'Guru Kelas II',
                'jk'           => 'P',
                'tmpt_lahir'   => 'Singaraja',
                'tgl_lahir'    => '1968-03-30',
            ],
            [
                'username'     => 'bayukarsana',
                'nama_lengkap' => 'I Wayan Bayu Karsana Putra, S.Pd',
                'nip'          => '198911082022211011',
                'level'        => 'guru',
                'jabatan'      => 'Guru Kelas IV',
                'jk'           => 'L',
                'tmpt_lahir'   => 'Tabanan',
                'tgl_lahir'    => '1989-11-08',
            ],
            [
                'username'     => 'raipitriani',
                'nama_lengkap' => 'Ni Wayan Rai Pitriani, S.Pd',
                'nip'          => '197710202021212001',
                'level'        => 'guru',
                'jabatan'      => 'Guru Kelas III',
                'jk'           => 'P',
                'tmpt_lahir'   => 'Baturiti',
                'tgl_lahir'    => '1977-10-20',
            ],
            [
                'username'     => 'suarjana',
                'nama_lengkap' => 'I Wayan Suarjana, S.Pd.H',
                'nip'          => '197407072023211004',
                'level'        => 'guru',
                'jabatan'      => 'Guru Agama Hindu Kelas 1-3',
                'jk'           => 'L',
                'tmpt_lahir'   => 'Pemeng',
                'tgl_lahir'    => '1974-07-07',
            ],
            [
                'username'     => 'ariwidnya',
                'nama_lengkap' => 'I Gst. Ngurah Bgs. Ariwidnya, S.Pd',
                'nip'          => '198508112022211007',
                'level'        => 'guru',
                'jabatan'      => 'Guru Kelas VI',
                'jk'           => 'L',
                'tmpt_lahir'   => 'Kukuh',
                'tgl_lahir'    => '1985-08-11',
            ],
            [
                'username'     => 'damayanti',
                'nama_lengkap' => 'Ni Wayan Damayanti, S.Pd',
                'nip'          => '199008152022212006',
                'level'        => 'guru',
                'jabatan'      => 'Guru Kelas I',
                'jk'           => 'P',
                'tmpt_lahir'   => 'Palian',
                'tgl_lahir'    => '1990-08-15',
            ],
            [
                'username'     => 'madhavdevi',
                'nama_lengkap' => 'Ni Luh Gede Madhavi Devi Dasi, S.Pd',
                'nip'          => '199308222024212027',
                'level'        => 'guru',
                'jabatan'      => 'Guru Kelas V',
                'jk'           => 'P',
                'tmpt_lahir'   => 'Denpasar',
                'tgl_lahir'    => '1993-08-22',
            ],
            [
                'username'     => 'siskadevi',
                'nama_lengkap' => 'Ni Gst Ayu Pt Siska Dewi, S.Pd.H',
                'nip'          => null, // Non-PNS, NIP tidak terbaca jelas
                'level'        => 'guru',
                'jabatan'      => 'Guru Agama Hindu & Bhs. Bali Kls I-II',
                'jk'           => 'P',
                'tmpt_lahir'   => 'Badung',
                'tgl_lahir'    => '1991-12-23',
            ],
            [
                'username'     => 'desiwulandari',
                'nama_lengkap' => 'Ni Pt Ayu Desi Wulandari, S.Fil.H',
                'nip'          => '199312022025212013',
                'level'        => 'guru',
                'jabatan'      => 'Guru Agama Hindu Kelas III & IV',
                'jk'           => 'P',
                'tmpt_lahir'   => 'Timpag',
                'tgl_lahir'    => '1993-12-02',
            ],
        ];

        $inserted = 0;
        $updated  = 0;

        foreach ($guruData as $g) {
            $existing = $this->db->table('users')
                ->where('username', $g['username'])
                ->get()->getRow();

            $record = [
                'username'     => $g['username'],
                'nama_lengkap' => $g['nama_lengkap'],
                'no_telp'      => null,
                'level'        => $g['level'],
                'status'       => 'aktif',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

            if (!$existing) {
                $record['password'] = $defaultPass;
                $this->db->table('users')->insert($record);
                $inserted++;
            } else {
                unset($record['created_at']);
                $this->db->table('users')
                    ->where('username', $g['username'])
                    ->update($record);
                $updated++;
            }
        }

        echo "✓ ($inserted inserted, $updated updated)\n";
        echo "   ⚠ CATATAN AMBIGUITAS:\n";
        echo "     - NIP Ni Gst Ayu Pt Siska D. tidak terbaca di gambar (Non-PNS, dikosongkan)\n";
        echo "     - Digit NIP baris 4 (Ni Wayan Rai Pitriani): '197710202021212001' — perlu verifikasi manual\n";
        echo "     - Ni Luh Gede Madhavi Devi: nama panjang, disingkat 'Dasi' dari file absensi\n";
    }
}
