<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>" class="active"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-4">
    <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-card-list me-2"></i>Kelola Template Capaian</h4>
    <p class="text-muted mb-0">Siapkan 4 narasi template per Mata Pelajaran + Fase + Semester — satu untuk
        tiap band predikat A/B/C/D. Saat input rapor, narasi sesuai nilai siswa otomatis di-isi ke kotak teks
        (boleh diedit). Huruf predikat tidak tampil di rapor.</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-funnel me-2"></i>Pilih Mapel / Fase / Semester</div>
    <div class="card-body">
        <form method="get" action="<?= base_url('guru/template-capaian') ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Mata Pelajaran</label>
                <select name="id_mapel" class="form-select" required>
                    <option value="">— Pilih Mapel —</option>
                    <?php foreach ($mapel as $m): ?>
                        <option value="<?= $m['id_mapel'] ?>" <?= (int) $f_mapel === (int) $m['id_mapel'] ? 'selected' : '' ?>>
                            <?= esc($m['nama_mapel']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fase</label>
                <select name="fase" class="form-select" required>
                    <?php foreach (['A' => 'A (Kelas 1-2)', 'B' => 'B (Kelas 3-4)', 'C' => 'C (Kelas 5-6)'] as $v => $lbl): ?>
                        <option value="<?= $v ?>" <?= $f_fase === $v ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select" required>
                    <?php foreach (['Ganjil', 'Genap'] as $s): ?>
                        <option value="<?= $s ?>" <?= $f_sem === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary bg-pastel-primary border-0 w-100">
                    <i class="bi bi-search me-1"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (is_array($band_map)): ?>
    <?php
    $bandLabel = ['A' => 'Band A (Nilai ≥ 90)', 'B' => 'Band B (Nilai ≥ 80)', 'C' => 'Band C (Nilai ≥ 70)', 'D' => 'Band D (Nilai ≥ 60)'];
    $bandColor = ['A' => 'success', 'B' => 'primary', 'C' => 'warning', 'D' => 'danger'];
    ?>
    <form method="post" action="<?= base_url('guru/template-capaian/save-bands') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id_mapel" value="<?= esc($f_mapel) ?>">
        <input type="hidden" name="fase" value="<?= esc($f_fase) ?>">
        <input type="hidden" name="semester" value="<?= esc($f_sem) ?>">

        <?php foreach (['A', 'B', 'C', 'D'] as $p): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">
                    <span class="badge bg-<?= $bandColor[$p] ?> me-2"><?= $p ?></span><?= $bandLabel[$p] ?>
                </div>
                <div class="card-body">
                    <textarea name="band[<?= $p ?>]" rows="3" class="form-control"
                        placeholder="Narasi capaian untuk siswa berpredikat <?= $p ?> ..."><?= esc($band_map[$p] ?? '') ?></textarea>
                    <small class="text-muted">Kosongkan untuk menghapus template band ini.</small>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary bg-pastel-primary border-0 fw-semibold px-4">
                <i class="bi bi-save me-1"></i> Simpan Template (4 Band)
            </button>
        </div>
    </form>
<?php else: ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Pilih Mata Pelajaran, Fase, dan Semester lalu klik <strong>Tampilkan</strong>.</div>
<?php endif; ?>
<?= $this->endSection() ?>
