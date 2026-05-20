<?php
/**
 * Shared partial layout rapor — dipakai bersama oleh:
 *   - app/Views/orangtua/rapor/view.php   (e-rapor orang tua, showBadgeOnline=true)
 *   - app/Views/admin/rapor/detail.php    (detail admin, showBadgeOnline=true)
 *   - app/Views/rapor/cetak.php           (PDF Dompdf, showBadgeOnline=false)
 *
 * Render 8 section sesuai PDF referensi (Rapor_Kelas3.pdf / Rapor_Kelas6.pdf).
 *
 * Variabel context (dari App\Libraries\RaporDataLoader::load()):
 *   siswa, kelas, tahun_ajaran, rapor, wali_kelas, wali_nip, mapel{wajib,pilihan},
 *   fase, koko_narasi, tema, ekskul, sekolah, kepsek_nama, kepsek_nip, tanggal_indo
 * Plus flag:
 *   showBadgeOnline (bool) — badge "Catatan dari guru" hanya online; PDF = false.
 */

$semesterNum = strtolower((string) ($tahun_ajaran['semester'] ?? '')) === 'genap' ? '2' : '1';
$showBadge   = $showBadgeOnline ?? true;

/**
 * Render baris tabel mata pelajaran. Badge "Catatan dari guru" hanya tampil
 * bila $showBadge true (online) untuk nilai flag_borderline_75 = 1.
 */
$renderMapelRows = static function (array $rows, bool $showBadge): string {
    if (empty($rows)) {
        return '<tr><td colspan="4" style="text-align:center;font-style:italic;color:#888;">Belum ada data.</td></tr>';
    }
    $html = '';
    $no = 1;
    foreach ($rows as $r) {
        $nilai  = isset($r['nilai_akhir']) ? number_format((float) $r['nilai_akhir'], 0) : '-';
        $narasi = trim((string) ($r['capaian_narasi'] ?? ''));
        $narasiHtml = $narasi !== ''
            ? nl2br(esc($narasi))
            : '<span style="color:#888;font-style:italic;">Belum ada capaian dinilai.</span>';

        $badge = '';
        if ($showBadge
            && (int) ($r['flag_borderline_75'] ?? 0) === 1
            && trim((string) ($r['catatan_remedial'] ?? '')) !== ''
        ) {
            $badge = '<details class="rapor-online-only" style="display:block;margin-top:6px;">'
                . '<summary class="rapor-badge-catatan">&#128221; Catatan dari guru</summary>'
                . '<div class="rapor-badge-isi">' . nl2br(esc((string) $r['catatan_remedial'])) . '</div>'
                . '</details>';
        }

        $html .= '<tr>'
            . '<td style="text-align:center;">' . $no++ . '</td>'
            . '<td>' . esc((string) ($r['nama_mapel'] ?? '-')) . '</td>'
            . '<td style="text-align:center;font-weight:bold;">' . $nilai . $badge . '</td>'
            . '<td>' . $narasiHtml . '</td>'
            . '</tr>';
    }
    return $html;
};
?>
<div class="rapor-container">

    <!-- ═══ 1. HEADER IDENTITAS SISWA ═══ -->
    <table class="rapor-header-tbl">
        <tr>
            <td class="lbl">Nama Murid</td><td class="sep">:</td>
            <td><strong><?= esc($siswa['nama_siswa'] ?? '-') ?></strong></td>
            <td class="lbl">Kelas</td><td class="sep">:</td>
            <td><?= esc($kelas['nama_kelas'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="lbl">NIS/NISN</td><td class="sep">:</td>
            <td><?= esc($siswa['nis'] ?? '-') ?> / <?= esc($siswa['nisn'] ?? '-') ?></td>
            <td class="lbl">Fase</td><td class="sep">:</td>
            <td><?= esc($fase ?? '-') ?></td>
        </tr>
        <tr>
            <td class="lbl">Sekolah</td><td class="sep">:</td>
            <td><?= esc($sekolah ?? 'SD NEGERI 3 MEKARSARI') ?></td>
            <td class="lbl">Semester</td><td class="sep">:</td>
            <td><?= esc($semesterNum) ?></td>
        </tr>
        <tr>
            <td class="lbl">Alamat</td><td class="sep">:</td>
            <td><?= esc($siswa['alamat'] ?? '-') ?></td>
            <td class="lbl">Tahun Ajaran</td><td class="sep">:</td>
            <td><?= esc($tahun_ajaran['tahun_ajaran'] ?? '-') ?></td>
        </tr>
    </table>

    <h2 class="rapor-title">LAPORAN HASIL BELAJAR</h2>

    <!-- ═══ 2 & 3. MATA PELAJARAN WAJIB + PILIHAN ═══ -->
    <table class="rapor-tbl">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:28%;">Mata Pelajaran</th>
                <th style="width:11%;">Nilai Akhir</th>
                <th style="width:56%;">Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            <tr class="rapor-row-group"><td colspan="4">Mata Pelajaran Wajib</td></tr>
            <?= $renderMapelRows($mapel['wajib'] ?? [], $showBadge) ?>
            <tr class="rapor-row-group"><td colspan="4">Mata Pelajaran Pilihan</td></tr>
            <?= $renderMapelRows($mapel['pilihan'] ?? [], $showBadge) ?>
        </tbody>
    </table>

    <!-- ═══ 4. KOKURIKULER ═══ -->
    <div class="rapor-section-head">Kokurikuler</div>
    <div class="rapor-box">
        <?php $koko = trim((string) ($koko_narasi ?? '')); ?>
        <?= $koko !== ''
            ? nl2br(esc($koko))
            : '<span style="color:#888;font-style:italic;">Belum ada capaian kokurikuler dinilai.</span>' ?>
    </div>

    <!-- ═══ 5. EKSTRAKURIKULER ═══ -->
    <table class="rapor-tbl">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:28%;">Ekstrakurikuler</th>
                <th style="width:67%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ekskul)): ?>
                <tr><td colspan="3" style="text-align:center;font-style:italic;color:#888;">Belum ada ekstrakurikuler.</td></tr>
            <?php else: ?>
                <?php $no = 1;
                foreach ($ekskul as $e): ?>
                    <tr>
                        <td style="text-align:center;"><?= $no++ ?></td>
                        <td><?= esc($e['nama'] ?? '-') ?></td>
                        <td><?= esc($e['keterangan'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ═══ 6 & 7. KETIDAKHADIRAN + CATATAN WALI KELAS ═══ -->
    <table class="rapor-2col">
        <tr>
            <td style="width:42%;vertical-align:top;padding-right:8px;">
                <div class="rapor-section-head">Ketidakhadiran</div>
                <table class="rapor-tbl">
                    <tr><td>Sakit</td><td style="width:12%;text-align:center;">:</td><td style="width:32%;"><?= (int) ($rapor['sakit'] ?? 0) ?> hari</td></tr>
                    <tr><td>Izin</td><td style="text-align:center;">:</td><td><?= (int) ($rapor['izin'] ?? 0) ?> hari</td></tr>
                    <tr><td>Tanpa Keterangan</td><td style="text-align:center;">:</td><td><?= (int) ($rapor['alpa'] ?? 0) ?> hari</td></tr>
                </table>
            </td>
            <td style="width:58%;vertical-align:top;padding-left:8px;">
                <div class="rapor-section-head">Catatan Wali Kelas</div>
                <div class="rapor-box" style="min-height:96px;">
                    <?php $catatanWali = trim((string) ($rapor['catatan_wali_kelas'] ?? '')); ?>
                    <?= $catatanWali !== ''
                        ? nl2br(esc($catatanWali))
                        : '<span style="color:#888;font-style:italic;">Belum ada catatan dari wali kelas.</span>' ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- ═══ 8. TANGGAPAN ORANG TUA + TANDA TANGAN ═══ -->
    <div class="rapor-section-head">Tanggapan Orang Tua/Wali Murid</div>
    <div class="rapor-box" style="min-height:72px;">&nbsp;</div>

    <table class="rapor-ttd">
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>Tabanan, <?= esc($tanggal_indo ?? '') ?></td>
        </tr>
        <tr>
            <td>Orang Tua Murid</td>
            <td>Kepala Sekolah</td>
            <td>Wali Kelas</td>
        </tr>
        <tr class="ttd-space"><td colspan="3"></td></tr>
        <tr class="ttd-name">
            <td>......................................</td>
            <td><?= esc($kepsek_nama ?? '-') ?></td>
            <td><?= esc($wali_kelas['nama_lengkap'] ?? '-') ?></td>
        </tr>
        <tr class="ttd-nip">
            <td></td>
            <td>NIP. <?= esc($kepsek_nip ?? '-') ?></td>
            <td>NIP. <?= esc($wali_nip ?? '-') ?></td>
        </tr>
    </table>

</div>
