<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed: assign guru pengampu (mapel_kelas.id_guru) per kelas.
 *
 * Idempotent — hanya mengisi row mapel_kelas yang id_guru-nya masih NULL,
 * agar assignment yang sudah dibuat manual via admin/mapel tidak ditimpa.
 *
 * Default mapping: ikut wali kelas tiap tingkat. Exception khusus untuk
 * mapel AGAMA (Pendidikan Agama Hindu) dan BBALI (Bahasa Bali), yang
 * diampu oleh guru spesialis.
 */
class SD3_MapelKelasGuruSeeder extends Seeder
{
    private array $mapelPerKelas = [
        1 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        2 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        3 => ['Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Seni Rupa', 'Bahasa Inggris', 'Pendidikan Pancasila'],
        4 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        5 => ['Matematika', 'Bahasa Indonesia', 'Pendidikan Pancasila', 'IPAS', 'PJOK', 'Pendidikan Agama Hindu dan Budi Pekerti', 'Bahasa Bali', 'Seni Suara', 'Bahasa Inggris'],
        6 => ['Matematika', 'Tematik Terpadu', 'Pendidikan Agama Hindu dan Budi Pekerti', 'PJOK', 'Bahasa Bali', 'Bahasa Inggris'],
    ];

    public function run(): void
    {
        echo "▶ MapelKelasGuru ... \n";

        // PHASE 1: Create mapel_kelas rows if they don't exist yet
        $this->populateMapelKelas();

        $waliPerTingkat = [
            1 => ['Ni Wayan Damayanti, S.Pd'],
            2 => ['Nengah Sarini, S.Pd'],
            3 => ['Ni Wayan Rai Pitriani, S.Pd'],
            4 => ['I Wayan Bayu Karsana Putra, S.Pd'],
            5 => ['Ni Luh Gede Madhavi Devi Dasi, S.Pd', 'Ni Luh Gede Madhavi Devi, D.S.Pd'],
            6 => ['I Gst. Ngurah Bgs. Ariwidnya, S.Pd'],
        ];

        $agamaPerTingkat = [
            1 => ['I Wayan Suarjana, S.Pd.H'],
            2 => ['I Wayan Suarjana, S.Pd.H'],
            3 => ['I Wayan Suarjana, S.Pd.H'],
            4 => ['Ni Pt Ayu Desi Wulandari, S.Fil.H'],
            5 => ['Ni Pt Ayu Desi Wulandari, S.Fil.H'],
            6 => ['Ni Pt Ayu Desi Wulandari, S.Fil.H'],
        ];

        $bbaliPerTingkatKhusus = [
            1 => ['Ni Gst Ayu Pt Siska Dewi, S.Pd.H', 'Ni Gst Ayu Pt Siska D., S.Pd.H'],
            2 => ['Ni Gst Ayu Pt Siska Dewi, S.Pd.H', 'Ni Gst Ayu Pt Siska D., S.Pd.H'],
        ];

        $rows = $this->db->table('mapel_kelas mk')
            ->select('mk.id_mapel_kelas, mk.id_mapel, mk.id_kelas, mk.id_guru, m.kode_mapel, m.nama_mapel, k.tingkat, k.nama_kelas')
            ->join('mata_pelajaran m', 'm.id_mapel = mk.id_mapel')
            ->join('kelas k', 'k.id_kelas = mk.id_kelas')
            ->orderBy('k.tingkat', 'ASC')
            ->orderBy('m.kode_mapel', 'ASC')
            ->get()->getResultArray();

        $updated   = 0;
        $skipped   = 0;
        $unmatched = 0;
        $alreadyHasGuru = 0;

        foreach ($rows as $row) {
            $idMapelKelas = (int) $row['id_mapel_kelas'];
            $kode         = (string) $row['kode_mapel'];
            $tingkat      = (int) $row['tingkat'];
            $namaMapel    = (string) $row['nama_mapel'];
            $namaKelas    = (string) $row['nama_kelas'];

            if (!empty($row['id_guru'])) {
                $alreadyHasGuru++;
                echo "  • Skip [{$namaMapel} / Kelas {$namaKelas}]: sudah punya id_guru\n";
                continue;
            }

            $candidates = $this->pickCandidates($kode, $tingkat, $waliPerTingkat, $agamaPerTingkat, $bbaliPerTingkatKhusus);
            $idGuru = $this->resolveGuruId($candidates);

            // Untuk BBALI kelas 1/2: kalau guru spesialis tidak ditemukan,
            // fallback ke wali kelas.
            if ($idGuru === null && $kode === 'BBALI' && in_array($tingkat, [1, 2], true)) {
                $idGuru = $this->resolveGuruId($waliPerTingkat[$tingkat] ?? []);
            }

            if ($idGuru === null) {
                $unmatched++;
                echo "  ⚠ Unmatched [{$namaMapel} / Kelas {$namaKelas}]: tidak ada guru kandidat\n";
                continue;
            }

            $this->db->table('mapel_kelas')
                ->where('id_mapel_kelas', $idMapelKelas)
                ->where('id_guru', null)
                ->update([
                    'id_guru'    => $idGuru,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if ($this->db->affectedRows() > 0) {
                $updated++;
                echo "  ✓ Set [{$namaMapel} / Kelas {$namaKelas}] -> id_user={$idGuru}\n";
            } else {
                $skipped++;
            }
        }

        echo "\n  Ringkasan MapelKelasGuru:\n";
        echo "    - Updated         : {$updated}\n";
        echo "    - Skipped (race)  : {$skipped}\n";
        echo "    - Sudah ada guru  : {$alreadyHasGuru}\n";
        echo "    - Tidak ketemu    : {$unmatched}\n";
        echo "  ✓ MapelKelasGuru selesai.\n";
    }

    private function populateMapelKelas(): void
    {
        $mapelRows = $this->db->table('mata_pelajaran')->get()->getResultArray();
        $mapelByNama = [];
        foreach ($mapelRows as $m) {
            $mapelByNama[$m['nama_mapel']] = (int) $m['id_mapel'];
        }

        $created = 0;
        for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
            $kelas = $this->db->table('kelas')->where('tingkat', $tingkat)->get()->getRow();
            if (!$kelas) {
                continue;
            }

            foreach ($this->mapelPerKelas[$tingkat] ?? [] as $namaMapel) {
                $mapelId = $mapelByNama[$namaMapel] ?? null;
                if (!$mapelId) {
                    continue;
                }

                $exists = $this->db->table('mapel_kelas')
                    ->where('id_mapel', $mapelId)
                    ->where('id_kelas', $kelas->id_kelas)
                    ->get()->getRow();

                if (!$exists) {
                    $this->db->table('mapel_kelas')->insert([
                        'id_mapel'   => $mapelId,
                        'id_kelas'   => $kelas->id_kelas,
                        'id_guru'    => null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $created++;
                }
            }
        }

        echo "  → $created mapel_kelas rows created\n";
    }

    private function pickCandidates(string $kode, int $tingkat, array $wali, array $agama, array $bbaliKhusus): array
    {
        if ($kode === 'AGAMA') {
            return $agama[$tingkat] ?? [];
        }

        if ($kode === 'BBALI' && isset($bbaliKhusus[$tingkat])) {
            return $bbaliKhusus[$tingkat];
        }

        return $wali[$tingkat] ?? [];
    }

    private function resolveGuruId(array $candidates): ?int
    {
        foreach ($candidates as $namaLengkap) {
            $row = $this->db->table('users')
                ->where('level', 'guru')
                ->where('nama_lengkap', $namaLengkap)
                ->get()->getRow();
            if ($row) {
                return (int) $row->id_user;
            }
        }

        // Fallback LIKE — biar tidak gampang patah karena beda tanda baca.
        foreach ($candidates as $namaLengkap) {
            $pattern = $this->buildLikePattern($namaLengkap);
            if ($pattern === '') {
                continue;
            }
            $row = $this->db->table('users')
                ->where('level', 'guru')
                ->like('nama_lengkap', $pattern, 'both', null, true)
                ->get()->getRow();
            if ($row) {
                return (int) $row->id_user;
            }
        }

        return null;
    }

    /**
     * Ambil token paling unik dari nama untuk dijadikan LIKE pattern.
     * Mis. "I Gst. Ngurah Bgs. Ariwidnya, S.Pd" -> "Ariwidnya".
     */
    private function buildLikePattern(string $namaLengkap): string
    {
        $clean = preg_replace('/[.,]/', ' ', $namaLengkap);
        $parts = preg_split('/\s+/', trim((string) $clean));
        $blacklist = ['I', 'Ni', 'Wayan', 'Nengah', 'Made', 'Nyoman', 'Ketut', 'Gst', 'Ngurah', 'Bgs', 'Pt', 'Luh', 'Gede', 'Ayu', 'Gusti', 'Putu', 'Dewa', 'Ida', 'Bagus', 'Ny', 'S', 'Pd', 'H', 'Fil'];
        $candidates = [];
        foreach ($parts as $p) {
            if ($p === '' || in_array($p, $blacklist, true)) {
                continue;
            }
            $candidates[] = $p;
        }
        if (empty($candidates)) {
            return '';
        }

        usort($candidates, static fn($a, $b) => strlen($b) <=> strlen($a));
        return $candidates[0];
    }
}
