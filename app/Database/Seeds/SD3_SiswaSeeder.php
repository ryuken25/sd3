<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: Siswa dari file absensi.xls (SpreadsheetML)
 *
 * SUMBER DATA (hasil parse absensi.xls):
 * ─────────────────────────────────────────────────────────────────────────────
 * Kelas 1 (Wali: Ni Wayan Damayanti) — 12 siswa
 * Kelas 2 (Wali: Nengah Sarini) — 13 siswa
 * Kelas 3 (Wali: Ni Wayan Rai Pitriani) — 13 siswa
 * Kelas 4 (Wali: I Wayan Bayu Karsana Putra) — 17 siswa
 * Kelas 5 (Wali: Ni Luh Gede Madhavi Devi Dasi) — 10 siswa
 * Kelas 6 (Wali: I Gst. Ngurah Bgs. Ariwidnya) — 8 siswa
 * TOTAL: 73 siswa
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Format NISN / NIS dari file: "3188335541 / 937"
 * Parsing: nisn = bagian sebelum " / ", nis = bagian sesudah " / "
 * Jika NIS kosong: set NULL
 */
class SD3_SiswaSeeder extends Seeder
{
    // ─── DATA SISWA HARDCODED dari parse absensi.xls ──────────────────────────
    private function getSiswaPerKelas(): array
    {
        return [
            // ── KELAS 1 (12 siswa) ─────────────────────────────────────────────
            1 => [
                ['nisn' => '3197018614', 'nis' => null, 'nama' => 'GEDE ANGGA KUSUMA', 'jk' => 'L'],
                ['nisn' => '3195741685', 'nis' => null, 'nama' => 'I KADEK ANGGA SAPUTRA', 'jk' => 'L'],
                ['nisn' => '3198059484', 'nis' => null, 'nama' => 'I KADEK BAGAS ADI PUTRA', 'jk' => 'L'],
                ['nisn' => '3196459226', 'nis' => null, 'nama' => 'I MADE ARTA WIRANATA', 'jk' => 'L'],
                ['nisn' => '3192905386', 'nis' => null, 'nama' => 'KADEK ARIF SUDARMAWAN', 'jk' => 'L'],
                ['nisn' => '3193268131', 'nis' => null, 'nama' => 'KADEK RESTU ADI SAPUTRA', 'jk' => 'L'],
                ['nisn' => '3198047011', 'nis' => null, 'nama' => 'KOMANG ANOM SATYA DARMA', 'jk' => 'L'],
                ['nisn' => '3197543046', 'nis' => null, 'nama' => 'NI KADEK DWIPAYANTI', 'jk' => 'P'],
                ['nisn' => '3190673090', 'nis' => null, 'nama' => 'NI KADEK NAIYA PUTRIANI', 'jk' => 'P'],
                ['nisn' => '3196557091', 'nis' => null, 'nama' => 'NI KOMANG AYU APRIYANTI', 'jk' => 'P'],
                ['nisn' => '3199154380', 'nis' => null, 'nama' => 'NI PUTU SINTYA DEWI', 'jk' => 'P'],
                ['nisn' => '3199047720', 'nis' => null, 'nama' => 'PUTU DHIKA JULIARTA', 'jk' => 'L'],
            ],

            // ── KELAS 2 (13 siswa) ─────────────────────────────────────────────
            2 => [
                ['nisn' => '3188697344', 'nis' => null, 'nama' => 'I GEDE AGUS ADI SAPUTRA', 'jk' => 'L'],
                ['nisn' => '3185441296', 'nis' => null, 'nama' => 'I KADEK AGUS SAPUTRA', 'jk' => 'L'],
                ['nisn' => '3188047699', 'nis' => null, 'nama' => 'I KADEK NANDA SUANDANA', 'jk' => 'L'],
                ['nisn' => '3186513278', 'nis' => null, 'nama' => 'I MADE DICKY PRIANA PUTRA', 'jk' => 'L'],
                ['nisn' => '3188335541', 'nis' => '937', 'nama' => 'I PUTU ALDI WAHYU SUPUTRA', 'jk' => 'L'],
                ['nisn' => '3188047577', 'nis' => null, 'nama' => 'KADEK ARYA SWACARA', 'jk' => 'L'],
                ['nisn' => '3187034856', 'nis' => null, 'nama' => 'NI KADEK ARTINI', 'jk' => 'P'],
                ['nisn' => '3185738440', 'nis' => null, 'nama' => 'NI KADEK ERNI YUSTINI', 'jk' => 'P'],
                ['nisn' => '3185416143', 'nis' => null, 'nama' => 'NI KOMANG DARMAWATI', 'jk' => 'P'],
                ['nisn' => '3187282481', 'nis' => null, 'nama' => 'NI KOMANG SUCI OKTARINI', 'jk' => 'P'],
                ['nisn' => '3181476826', 'nis' => null, 'nama' => 'NI PUTU AYU PURNAMI DEWI', 'jk' => 'P'],
                ['nisn' => '3175825039', 'nis' => null, 'nama' => 'NI PUTU CAHYA ANDIRA', 'jk' => 'P'],
                ['nisn' => '3176780887', 'nis' => null, 'nama' => 'NI PUTU INDAH LISTYA DEWI', 'jk' => 'P'],
            ],

            // ── KELAS 3 (13 siswa) ─────────────────────────────────────────────
            3 => [
                ['nisn' => '3179723720', 'nis' => '909', 'nama' => 'DANENDRA ADI PRATAMA', 'jk' => 'L'],
                ['nisn' => '3165858949', 'nis' => '910', 'nama' => 'I KADEK RAKA PRANAJAYA ISWARA', 'jk' => 'L'],
                ['nisn' => '3168276683', 'nis' => '911', 'nama' => 'I KADEK VICKY PERWIRA PUTRA', 'jk' => 'L'],
                ['nisn' => '3165933891', 'nis' => '912', 'nama' => 'I KOMANG BAGUS ADNYANA', 'jk' => 'L'],
                ['nisn' => '3164799282', 'nis' => '913', 'nama' => 'I KOMANG RAJA DIRGANTARA', 'jk' => 'L'],
                ['nisn' => '3169140836', 'nis' => '915', 'nama' => 'MADE AZKA PRASETYA PRAMITA', 'jk' => 'L'],
                ['nisn' => '3164868101', 'nis' => '917', 'nama' => 'NI KOMANG WINDI BUDE ANTARI', 'jk' => 'P'],
                ['nisn' => '3177870833', 'nis' => '918', 'nama' => 'NI LUH PUTU APRILIA TRISNA DEWI', 'jk' => 'P'],
                ['nisn' => '3169511989', 'nis' => '919', 'nama' => 'NI LUH PUTU CITRA LESTARI', 'jk' => 'P'],
                ['nisn' => '3166104084', 'nis' => '920', 'nama' => 'NI PUTU ADISTIA INDIRA KAMANIA', 'jk' => 'P'],
                ['nisn' => '3166098483', 'nis' => '921', 'nama' => 'NI PUTU ERLINA SEFTIANI', 'jk' => 'P'],
                ['nisn' => '3165507363', 'nis' => '922', 'nama' => 'NI PUTU INESYA JELITA PUTRI', 'jk' => 'P'],
                ['nisn' => '3168269769', 'nis' => '923', 'nama' => 'PUTU MONIKA SHRI HARTATI', 'jk' => 'P'],
            ],

            // ── KELAS 4 (17 siswa) ─────────────────────────────────────────────
            4 => [
                ['nisn' => '3152568737', 'nis' => null, 'nama' => 'I GEDE BRYANANDA PUTRA', 'jk' => 'L'],
                ['nisn' => '3149681712', 'nis' => null, 'nama' => 'I Kadek Agus Bintang', 'jk' => 'L'],
                ['nisn' => '3153915745', 'nis' => null, 'nama' => 'I KADEK MICKO DWI PUTRA', 'jk' => 'L'],
                ['nisn' => '3159376255', 'nis' => null, 'nama' => 'I KADEK SUMERDIANA PUTRA', 'jk' => 'L'],
                ['nisn' => '3152528311', 'nis' => null, 'nama' => 'I Kadek Widana Putra', 'jk' => 'L'],
                ['nisn' => '3159555040', 'nis' => null, 'nama' => 'I Komang Indra Pradita', 'jk' => 'L'],
                ['nisn' => '3157038910', 'nis' => null, 'nama' => 'I MADE APRI ADITYA PUTRA', 'jk' => 'L'],
                ['nisn' => '3152540640', 'nis' => null, 'nama' => 'I MADE ARSANA DIPUTRA', 'jk' => 'L'],
                ['nisn' => '3167005919', 'nis' => null, 'nama' => 'I Made Sunu Jayata', 'jk' => 'L'],
                ['nisn' => '3151156856', 'nis' => null, 'nama' => 'I Putu Hendra Pratama', 'jk' => 'L'],
                ['nisn' => '3154031863', 'nis' => null, 'nama' => 'I PUTU WIKA ADITYA PRATAMA', 'jk' => 'L'],
                ['nisn' => '3163111586', 'nis' => null, 'nama' => 'KETUT RANGGA ARIYA SUPUTRA', 'jk' => 'L'],
                ['nisn' => '3166288077', 'nis' => null, 'nama' => 'Ni Kadek Dinda Cahaya Putri', 'jk' => 'P'],
                ['nisn' => '3165653529', 'nis' => null, 'nama' => 'Ni Luh Komang Marta Miranda Putri', 'jk' => 'P'],
                ['nisn' => '3158394918', 'nis' => null, 'nama' => 'Ni Made Adelia Septiani', 'jk' => 'P'],
                ['nisn' => '3157512080', 'nis' => null, 'nama' => 'NI NYOMAN MIA ELVIYANTI', 'jk' => 'P'],
                ['nisn' => '3155151276', 'nis' => null, 'nama' => 'PUTU NANDA CAHYA YUNITHA', 'jk' => 'P'],
            ],

            // ── KELAS 5 (10 siswa) ─────────────────────────────────────────────
            5 => [
                ['nisn' => '3143924082', 'nis' => null, 'nama' => 'I KADEK EVAN ADITYA PUTRA', 'jk' => 'L'],
                ['nisn' => '3140306352', 'nis' => null, 'nama' => 'I KETUT KUMARA NATHA', 'jk' => 'L'],
                ['nisn' => '3147260938', 'nis' => null, 'nama' => 'I MADE JUNA ADINATA PUTRA', 'jk' => 'L'],
                ['nisn' => '3145838912', 'nis' => null, 'nama' => 'I Putu Arta Negara', 'jk' => 'L'],
                ['nisn' => '3143761419', 'nis' => null, 'nama' => 'I Putu Bayu Cakra Wedana', 'jk' => 'L'],
                ['nisn' => '3140106098', 'nis' => null, 'nama' => 'KADE ENDRA PRATAMA', 'jk' => 'L'],
                ['nisn' => '3140043650', 'nis' => null, 'nama' => 'Kadek Salya Oktaniarta', 'jk' => 'P'],
                ['nisn' => '3158875345', 'nis' => null, 'nama' => 'Ni komang Puspa Martiani', 'jk' => 'P'],
                ['nisn' => '3149799725', 'nis' => null, 'nama' => 'NI MADE HAYU SASMITA', 'jk' => 'P'],
                ['nisn' => '3141169385', 'nis' => null, 'nama' => 'NI MADE MIKA DIANDRA DEWI', 'jk' => 'P'],
            ],

            // ── KELAS 6 (8 siswa) ──────────────────────────────────────────────
            6 => [
                ['nisn' => '0134720766', 'nis' => '871', 'nama' => 'I GEDE RAMA', 'jk' => 'L'],
                ['nisn' => '0131450295', 'nis' => '870', 'nama' => 'I KOMANG IRWAN KUSUMA', 'jk' => 'L'],
                ['nisn' => '0135732527', 'nis' => '874', 'nama' => 'I KOMANG NANDA ADI PUTRA', 'jk' => 'L'],
                ['nisn' => '0139631212', 'nis' => '869', 'nama' => 'I Made Aditya Pramana', 'jk' => 'L'],
                ['nisn' => '3143709887', 'nis' => '876', 'nama' => 'I Made Dwi Pradnya Maha Putra', 'jk' => 'L'],
                ['nisn' => '0135220982', 'nis' => '877', 'nama' => 'KOMANG KIRANA CITRA DEWI', 'jk' => 'P'],
                ['nisn' => '0135636115', 'nis' => '875', 'nama' => 'NI KADEK PIANI NINGSIH', 'jk' => 'P'],
                ['nisn' => '0136410982', 'nis' => '873', 'nama' => 'NI LUH GEDE SINTA', 'jk' => 'P'],
            ],
        ];
    }

    public function run(): void
    {
        echo "▶ [5/9] Siswa ... ";

        $siswaPerKelas = $this->getSiswaPerKelas();
        $inserted = 0;
        $updated = 0;
        $counter = []; // counter per kelas untuk validasi

        foreach ($siswaPerKelas as $tingkat => $siswaList) {
            // Cari id_kelas
            $kelas = $this->db->table('kelas')
                ->where('tingkat', $tingkat)
                ->get()->getRow();

            if (!$kelas) {
                echo "\n   ⚠ Kelas $tingkat tidak ditemukan, skip!\n";
                continue;
            }

            $kelasId = $kelas->id_kelas;
            $counter[$tingkat] = 0;

            foreach ($siswaList as $s) {
                $nisn = $s['nisn'];
                $nis = $s['nis']; // bisa null
                $nama = $s['nama'];
                $jk = $s['jk'];

                // Gunakan NIS sebagai primary key pencarian jika ada, fallback ke NISN
                $existing = null;

                if ($nisn) {
                    $existing = $this->db->table('siswa')
                        ->where('nisn', $nisn)
                        ->get()->getRow();
                }

                if (!$existing && $nis) {
                    $existing = $this->db->table('siswa')
                        ->where('nis', $nis)
                        ->get()->getRow();
                }

                $record = [
                    'nisn' => $nisn ?: null,
                    'nis' => $nis ?: ('SIS' . str_pad($tingkat, 1) . str_pad($inserted + $updated + 1, 3, '0', STR_PAD_LEFT)),
                    'nama_siswa' => $nama,
                    'jenis_kelamin' => $jk,
                    'id_kelas' => $kelasId,
                    'password' => password_hash((string) ($nis ?: $nisn ?: '12345'), PASSWORD_DEFAULT),
                    'status' => 'aktif',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $ortuUsername = 'ortu_' . $record['nis'];
                $existingOrtu = $this->db->table('users')
                    ->where('username', $ortuUsername)
                    ->get()->getRow();

                if (!$existingOrtu) {
                    $this->db->table('users')->insert([
                        'username' => $ortuUsername,
                        'password' => password_hash((string) $record['nis'], PASSWORD_DEFAULT),
                        'nama_lengkap' => 'Orang Tua ' . $nama,
                        'no_telp' => null,
                        'level' => 'orang_tua',
                        'status' => 'aktif',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $ortuId = (int) $this->db->insertID();
                } else {
                    $ortuId = (int) $existingOrtu->id_user;
                }

                $record['id_user_ortu'] = $ortuId;

                if (!$existing) {
                    $record['created_at'] = date('Y-m-d H:i:s');
                    $this->db->table('siswa')->insert($record);
                    $inserted++;
                } else {
                    $this->db->table('siswa')
                        ->where('id_siswa', $existing->id_siswa)
                        ->update($record);
                    $updated++;
                }

                $counter[$tingkat]++;
            }
        }

        echo "✓ ($inserted inserted, $updated updated)\n";

        // Validasi jumlah per kelas
        $expected = [1 => 12, 2 => 13, 3 => 13, 4 => 17, 5 => 10, 6 => 8];
        foreach ($expected as $k => $exp) {
            $actual = $counter[$k] ?? 0;
            $status = ($actual === $exp) ? '✓' : '✗';
            echo "   Kelas $k: $actual/$exp siswa $status\n";
        }
    }
}
