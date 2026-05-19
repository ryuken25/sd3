<?php

namespace App\Libraries;

/**
 * Helper untuk men-generate narasi rapor:
 *   - Narasi Capaian Kompetensi (Pek 3): "Mencapai Kompetensi dengan sangat baik
 *     dalam hal X, Y. Perlu peningkatan dalam hal Z, W."
 *   - Narasi Kokurikuler P5 (Pek 5.2): paragraf intro + per dimensi.
 */
class RaporNarrativeService
{
    /**
     * @param array $capaianList list of ['deskripsi' => string, 'status' => 'tercapai_sangat_baik'|'perlu_peningkatan']
     */
    public function generateNarasiCP(array $capaianList): string
    {
        $tercapai = [];
        $perlu    = [];
        foreach ($capaianList as $c) {
            $d = trim((string) ($c['deskripsi'] ?? ''));
            if ($d === '') {
                continue;
            }
            if (($c['status'] ?? '') === 'tercapai_sangat_baik') {
                $tercapai[] = $d;
            } elseif (($c['status'] ?? '') === 'perlu_peningkatan') {
                $perlu[] = $d;
            }
        }

        $bagian1 = !empty($tercapai)
            ? 'Mencapai Kompetensi dengan sangat baik dalam hal ' . implode(', ', $tercapai) . '. '
            : '';
        $bagian2 = !empty($perlu)
            ? 'Perlu peningkatan dalam hal ' . implode(', ', $perlu) . '.'
            : '';

        return trim($bagian1 . $bagian2);
    }

    /**
     * @param string $namaTema       Mis. "Kreasi dan Permainan Tradisional: Gelanggang Ceria Nusantara"
     * @param array  $dimensiList    list of ['nama_dimensi' => string, 'subdimensi' => string, 'level' => string]
     */
    public function generateNarasiKokurikuler(string $namaTema, array $dimensiList): string
    {
        $intro = "Pada semester ini, ananda menunjukkan capaian yang cukup baik dalam penguatan profil lulusan, "
               . "yang ditunjukkan melalui kegiatan kokurikuler {$namaTema}.\n";

        $body = '';
        foreach ($dimensiList as $d) {
            $nama       = trim((string) ($d['nama_dimensi'] ?? ''));
            $sub        = trim((string) ($d['subdimensi'] ?? ''));
            $levelRaw   = (string) ($d['level'] ?? 'berkembang');
            $level      = $this->humanizeLevel($levelRaw);
            if ($nama === '' || $sub === '') {
                continue;
            }
            $body .= "Pada dimensi {$nama}, ananda {$level} dalam subdimensi {$sub}.\n";
        }

        return $intro . $body;
    }

    private function humanizeLevel(string $level): string
    {
        return match ($level) {
            'sangat_mahir' => 'sangat mahir',
            'mahir'        => 'mahir',
            'cakap'        => 'cakap',
            default        => 'berkembang',
        };
    }

    /**
     * Format tanggal Indonesia: "20 Desember 2025"
     */
    public function tanggalIndonesia(?string $isoDate = null): string
    {
        $ts = $isoDate ? strtotime($isoDate) : time();
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}
