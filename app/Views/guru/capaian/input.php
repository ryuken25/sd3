<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>" class="active"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-bookmark-check me-2"></i>Input Capaian Kompetensi</h4>
        <p class="text-muted mb-0 small">
            Kelas: <strong><?= esc($kelas['nama_kelas']) ?></strong> |
            Mapel: <strong><?= esc($mapel['nama_mapel']) ?></strong> |
            Fase: <strong><?= esc($fase) ?></strong> |
            TA: <strong><?= esc($tahun_ajaran['tahun_ajaran']) ?> Sem. <?= esc($tahun_ajaran['semester']) ?></strong>
        </p>
    </div>
    <a href="<?= base_url('guru/capaian-kompetensi') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<?= view('partials/info_banner', [
    'judul'   => 'Cara Mengisi Capaian Kompetensi',
    'langkah' => [
        'Tiap siswa punya <strong>satu kotak narasi yang selalu bisa diisi</strong> — tidak perlu menunggu Nilai Akhir dihitung.',
        'Isi singkat saja; teks final apa adanya itulah yang tampil di rapor.',
        'Bila Nilai Akhir sudah ada, tombol <strong>Ambil template</strong> mengisi saran narasi sesuai predikat (A/B/C/D).',
        'Atur narasi template per band di menu <strong>Template Capaian</strong>.',
        'Klik <strong>Simpan</strong> bila sudah sesuai.',
    ],
    'tips'    => 'Huruf predikat (A/B/C/D) hanya untuk memilih template — tidak ikut tampil di rapor.',
]) ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php $bandKosong = empty(array_filter($band_map ?? [])); ?>
<?php if ($bandKosong): ?>
    <div class="alert alert-warning">
        Template band untuk <strong><?= esc($mapel['nama_mapel']) ?></strong>, Fase <?= esc($fase) ?>,
        Semester <?= esc($tahun_ajaran['semester']) ?> belum diisi. Atur dulu di
        <a href="<?= base_url('guru/template-capaian?id_mapel=' . $id_mapel . '&fase=' . $fase . '&semester=' . $tahun_ajaran['semester']) ?>" class="alert-link">Template Capaian</a>,
        atau ketik manual di kotak teks.
    </div>
<?php endif; ?>

<form action="<?= base_url('guru/capaian-kompetensi/save') ?>" method="post" id="formCP">
    <?= csrf_field() ?>
    <input type="hidden" name="id_tahun_ajaran" value="<?= esc($id_tahun_ajaran) ?>">
    <input type="hidden" name="id_kelas" value="<?= esc($id_kelas) ?>">
    <input type="hidden" name="id_mapel" value="<?= esc($id_mapel) ?>">

    <?php foreach ($per_siswa as $idSiswa => $ps): ?>
        <?php
        $siswa = $ps['siswa'];
        $band  = (string) ($ps['band'] ?? '');
        $narasi = trim((string) ($ps['narasi'] ?? ''));
        $hasNilai = $ps['nilai_akhir'] !== null;
        ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-0 fw-bold"><?= esc($siswa['nama_siswa']) ?></h5>
                        <small class="text-muted">NIS: <?= esc($siswa['nis']) ?> | Nilai Akhir:
                            <strong><?= $hasNilai ? number_format($ps['nilai_akhir'], 2) : '—' ?></strong>
                            <?php if ($band !== ''): ?>
                                <span class="badge bg-secondary ms-1 no-print">Predikat: <?= esc($band) ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <textarea name="narasi[<?= (int) $idSiswa ?>]" rows="3" class="form-control cp-narasi"
                    data-band="<?= esc($band) ?>"
                    placeholder="Tulis capaian kompetensi siswa untuk mapel ini…"><?= esc($narasi) ?></textarea>
                <div class="mt-2 d-flex gap-2 align-items-center no-print">
                    <?php if ($band !== '' && trim((string) ($band_map[$band] ?? '')) !== ''): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-ambil-template"
                            data-target="narasi-<?= (int) $idSiswa ?>" data-band="<?= esc($band) ?>">
                            <i class="bi bi-arrow-repeat me-1"></i> Ambil template (<?= esc($band) ?>)
                        </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-bersihkan"
                        data-target="narasi-<?= (int) $idSiswa ?>">
                        <i class="bi bi-eraser me-1"></i> Bersihkan
                    </button>
                    <?php if (!$hasNilai): ?>
                        <small class="text-muted ms-auto"><i class="bi bi-info-circle me-1"></i>Nilai Akhir belum dihitung — narasi tetap bisa diisi & disimpan.</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary bg-pastel-primary border-0 fw-semibold px-4">
            <i class="bi bi-save me-1"></i> Simpan Semua Capaian
        </button>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Peta narasi template per band — sumber tombol "Ambil ulang dari template".
    const BAND_MAP = <?= json_encode($band_map ?? ['A' => '', 'B' => '', 'C' => '', 'D' => ''], JSON_UNESCAPED_UNICODE) ?>;

    // Beri id ke tiap textarea berdasar atribut name narasi[<id>].
    document.querySelectorAll('textarea.cp-narasi').forEach(function (ta) {
        const m = (ta.getAttribute('name') || '').match(/narasi\[(\d+)\]/);
        if (m) ta.id = 'narasi-' + m[1];
    });

    document.querySelectorAll('.btn-ambil-template').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ta = document.getElementById(btn.dataset.target);
            const band = btn.dataset.band || '';
            if (!ta) return;
            const teks = BAND_MAP[band] || '';
            if (teks === '') {
                alert('Template band ' + (band || '-') + ' belum diisi. Atur di menu Template Capaian.');
                return;
            }
            ta.value = teks;
        });
    });

    document.querySelectorAll('.btn-bersihkan').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ta = document.getElementById(btn.dataset.target);
            if (ta) ta.value = '';
        });
    });
</script>
<?= $this->endSection() ?>
