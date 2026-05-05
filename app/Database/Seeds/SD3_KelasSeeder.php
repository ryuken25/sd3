<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: Kelas (1–6) dengan mapping wali kelas dari file absensi
 *
 * Mapping wali kelas (dari baris "Wali Kelas: ..." di tiap sheet):
 *   Kelas 1  => Ni Wayan Damayanti          => username: damayanti
 *   Kelas 2  => Nengah Sarini               => username: nengahsarini
 *   Kelas 3  => Ni Wayan Rai Pitriani       => username: raipitriani
 *   Kelas 4  => I Wayan Bayu Karsana Putra  => username: bayukarsana
 *   Kelas 5  => Ni Luh Gede Madhavi Devi Dasi => username: madhavdevi
 *   Kelas 6  => I Gst. Ngurah Bgs. Ariwidnya  => username: ariwidnya
 */
class SD3_KelasSeeder extends Seeder
{
    public function run(): void
    {
        echo "▶ [3/9] Kelas ... ";

        $waliMap = [
            1 => 'damayanti',
            2 => 'nengahsarini',
            3 => 'raipitriani',
            4 => 'bayukarsana',
            5 => 'madhavdevi',
            6 => 'ariwidnya',
        ];

        $inserted = 0;
        $updated  = 0;

        for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
            $namaKelas = (string) $tingkat;
            $waliUsername = $waliMap[$tingkat];

            // Cari id_user wali kelas
            $waliUser = $this->db->table('users')
                ->where('username', $waliUsername)
                ->get()->getRow();

            $waliId = $waliUser ? $waliUser->id_user : null;

            $existing = $this->db->table('kelas')
                ->where('nama_kelas', $namaKelas)
                ->where('tingkat', $tingkat)
                ->get()->getRow();

            if (!$existing) {
                $this->db->table('kelas')->insert([
                    'nama_kelas'  => $namaKelas,
                    'tingkat'     => $tingkat,
                    'wali_kelas'  => $waliId,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            } else {
                $this->db->table('kelas')
                    ->where('nama_kelas', $namaKelas)
                    ->where('tingkat', $tingkat)
                    ->update([
                        'wali_kelas' => $waliId,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                $updated++;
            }
        }

        echo "✓ ($inserted inserted, $updated updated)\n";
    }
}
