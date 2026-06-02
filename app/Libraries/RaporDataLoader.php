<?php

namespace App\Libraries;

use App\Models\CapaianNarasiModel;
use App\Models\KelasModel;
use App\Models\KokurikulerTemaModel;
use App\Models\NilaiAkhirModel;
use App\Models\NilaiCapaianKompetensiModel;
use App\Models\RaporModel;
use App\Models\SiswaEkstrakurikulerModel;
use App\Models\SiswaKokurikulerDimensiModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;

/**
 * Loader data rapor lengkap (siswa + kelas + mapel + CP narasi + kokurikuler +
 * ekskul + wali kelas + fase) — dipakai oleh PDF cetak (OrangTua\Rapor) dan
 * view online e-rapor (OrangTua\Dashboard::viewRapor) supaya satu sumber data.
 *
 * Pek 9 megaprompt: e-rapor online harus tampil persis seperti PDF, jadi data
 * shape-nya harus sama. Service ini bikin gak ada drift antara dua view.
 */
class RaporDataLoader
{
    /** Konstanta identitas sekolah — dipakai di PDF + view online. */
    public const KEPSEK_NAMA = 'Ni Wayan Kasrinayanti, S. Pd.';
    public const KEPSEK_NIP  = '198408132014062008';
    public const SEKOLAH     = 'SD NEGERI 3 MEKARSARI';

    /** Mapping NIP guru per username (sumber: SD3_GuruSeeder). */
    public const NIP_GURU = [
        'kasrinayanti'  => '198408132014062008',
        'nengahsarini'  => '196803301994032007',
        'bayukarsana'   => '198911082022211011',
        'raipitriani'   => '197710202021212001',
        'suarjana'      => '197407072023211004',
        'ariwidnya'     => '198508112022211007',
        'damayanti'     => '199008152022212006',
        'madhavi'       => '199308222024212027',
        'siskadewi'     => '',
        'desiwulandari' => '199312022025212013',
    ];

    /**
     * Return shape:
     *   ['error' => string]                                                — bila siswa/TA tidak ditemukan
     *   ['siswa', 'kelas', 'tahun_ajaran', 'rapor', 'wali_kelas',
     *    'mapel' => ['wajib' => [...], 'pilihan' => [...]],
     *    'fase', 'koko_narasi', 'tema', 'ekskul',
     *    'wali_nip', 'sekolah', 'kepsek_nama', 'kepsek_nip']
     */
    public function load(int $idSiswa, int $idTa): array
    {
        $siswaModel       = new SiswaModel();
        $kelasModel       = new KelasModel();
        $taModel          = new TahunAjaranModel();
        $raporModel       = new RaporModel();
        $nilaiAkhirModel  = new NilaiAkhirModel();
        $nilaiCpModel     = new NilaiCapaianKompetensiModel();
        $capaianNarasiModel = new CapaianNarasiModel();
        $temaModel        = new KokurikulerTemaModel();
        $siswaEkskulModel = new SiswaEkstrakurikulerModel();
        $siswaKokoModel   = new SiswaKokurikulerDimensiModel();

        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) {
            return ['error' => 'Data siswa tidak ditemukan.'];
        }

        $kelas = $kelasModel->find($siswa['id_kelas']);
        $ta    = $taModel->find($idTa);
        if (!$ta) {
            return ['error' => 'Data tahun ajaran tidak ditemukan.'];
        }

        $rapor = $raporModel->where('id_siswa', $idSiswa)
            ->where('id_tahun_ajaran', $idTa)
            ->first();

        // Wali kelas
        $waliKelas = null;
        if ($kelas && !empty($kelas['wali_kelas'])) {
            $waliKelas = \Config\Database::connect()->table('users')
                ->where('id_user', $kelas['wali_kelas'])->get()->getRowArray();
        }
        $waliUsername = (string) ($waliKelas['username'] ?? '');
        $waliNip      = self::NIP_GURU[$waliUsername] ?? '-';

        // Mapel + nilai_akhir + narasi capaian kompetensi
        $nilaiRows = $nilaiAkhirModel
            ->select('nilai_akhir.*, mata_pelajaran.nama_mapel, mata_pelajaran.kode_mapel, kkm.nilai_kkm')
            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = nilai_akhir.id_mapel')
            ->join('kkm', 'kkm.id_mapel = nilai_akhir.id_mapel AND kkm.id_kelas = ' . (int) $siswa['id_kelas'] . ' AND kkm.id_tahun_ajaran = ' . $idTa, 'left')
            ->where('nilai_akhir.id_siswa', $idSiswa)
            ->where('nilai_akhir.id_tahun_ajaran', $idTa)
            ->orderBy('mata_pelajaran.id_mapel', 'ASC')
            ->findAll();

        $narrative = new RaporNarrativeService();
        $mapelData = ['wajib' => [], 'pilihan' => []];
        foreach ($nilaiRows as $row) {
            // Prioritas narasi CP:
            //   1) capaian_narasi (tabel baru, diisi guru, lepas dari nilai_akhir)
            //   2) nilai_akhir.narasi_cp (data lama sebelum decouple)
            //   3) auto-rakit dari nilai_capaian_kompetensi lama
            $manual = $capaianNarasiModel->narasiFor($idSiswa, (int) $row['id_mapel'], $idTa);
            if ($manual === '') {
                $manual = trim((string) ($row['narasi_cp'] ?? ''));
            }
            $row['capaian_narasi'] = $manual !== ''
                ? $manual
                : $narrative->generateNarasiCP($nilaiCpModel->listWithDeskripsi((int) $row['id_nilai_akhir']));
            // Bahasa Bali (kode BBALI) = mapel pilihan; sisanya = wajib
            $isPilihan = strtoupper((string) ($row['kode_mapel'] ?? '')) === 'BBALI';
            $mapelData[$isPilihan ? 'pilihan' : 'wajib'][] = $row;
        }

        // Kokurikuler: pakai narasi manual (rapor.narasi_koko) bila ada; kalau
        // kosong fallback ke auto-gen dari tema + dimensi P5.
        $tema = $kelas ? $temaModel->findForKelasTa((int) $kelas['id_kelas'], $idTa) : null;
        $kokoNarasi = '';
        $manualKoko = trim((string) ($rapor['narasi_koko'] ?? ''));
        if ($manualKoko !== '') {
            $kokoNarasi = $rapor['narasi_koko'];
        } elseif ($tema) {
            $dimensi = $siswaKokoModel->findForSiswaTema($idSiswa, (int) $tema['id_tema']);
            $kokoNarasi = $narrative->generateNarasiKokurikuler($tema['nama_tema'], $dimensi);
        }

        // Ekstrakurikuler
        $ekskul = $siswaEkskulModel->findForSiswaTa($idSiswa, $idTa);

        // Fase (dari tingkat kelas) — Pek 7
        $tingkat = (int) ($kelas['tingkat'] ?? 0);
        $fase    = match (true) {
            $tingkat <= 2 => 'A',
            $tingkat <= 4 => 'B',
            default       => 'C',
        };

        return [
            'siswa'        => $siswa,
            'kelas'        => $kelas,
            'tahun_ajaran' => $ta,
            'rapor'        => $rapor,
            'wali_kelas'   => $waliKelas,
            'wali_nip'     => $waliNip,
            'mapel'        => $mapelData,
            'fase'         => $fase,
            'koko_narasi'  => $kokoNarasi,
            'tema'         => $tema,
            'ekskul'       => $ekskul,
            'sekolah'      => self::SEKOLAH,
            'kepsek_nama'  => self::KEPSEK_NAMA,
            'kepsek_nip'   => self::KEPSEK_NIP,
            'tanggal_indo' => (new RaporNarrativeService())->tanggalIndonesia(),
        ];
    }
}
