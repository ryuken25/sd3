<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>" class="active"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$ekskulMap = [];
foreach ($ekskul_siswa as $e) { $ekskulMap[(int) $e['id_ekskul']] = $e; }
$kokoMap = [];
foreach ($koko_siswa as $k) { $kokoMap[(int) $k['id_dimensi']] = $k; }
$namaPanggilan = explode(' ', trim((string) $siswa['nama_siswa']))[0];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1">
            <i class="bi bi-clipboard-pulse me-2"></i><?= esc($siswa['nama_siswa']) ?>
        </h4>
        <p class="text-muted mb-0 small">
            NIS: <?= esc($siswa['nis']) ?> | Kelas: <?= esc($kelas['nama_kelas']) ?> |
            TA: <?= esc($tahun_ajaran['tahun_ajaran']) ?> Sem. <?= esc($tahun_ajaran['semester']) ?>
        </p>
    </div>
    <a href="<?= base_url('guru/wali-kelas') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?= view('partials/info_banner', [
    'judul'   => 'Cara Mengisi Data Wali Kelas (Catatan, Ketidakhadiran, Ekskul, Kokurikuler)',
    'langkah' => [
        '<strong>Catatan</strong>: pilih template (opsional) lalu edit. Placeholder {nama_panggilan} diganti otomatis. Wajib diisi minimal 10 karakter.',
        '<strong>Ketidakhadiran</strong>: isi jumlah hari Sakit / Izin / Tanpa Keterangan (default 0).',
        '<strong>Ekstrakurikuler</strong>: centang ekskul yang diikuti siswa, isi/teruskan keterangan default.',
        '<strong>Kokurikuler P5</strong>: untuk 7 dimensi Pancasila, isi subdimensi + level (Berkembang/Cakap/Mahir/Sangat Mahir).',
    ],
    'tips'    => 'Narasi kokurikuler disusun otomatis dari dimensi yang diisi.',
]) ?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-catatan">Catatan</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-ketidakhadiran">Ketidakhadiran</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-ekskul">Ekstrakurikuler</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-koko">Kokurikuler P5</a></li>
</ul>

<div class="tab-content">

    <!-- TAB: Catatan Wali Kelas -->
    <div class="tab-pane fade show active" id="tab-catatan">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2"></i>Catatan Wali Kelas</h5>
                <form action="<?= base_url('guru/wali-kelas/save-catatan') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_siswa" value="<?= $siswa['id_siswa'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Pilih Template (opsional)</label>
                        <select id="selectTemplate" class="form-select">
                            <option value="">— Tulis Sendiri —</option>
                            <?php foreach ($master_template as $t): ?>
                                <option value="<?= esc($t['isi_template']) ?>"
                                    data-kategori="<?= esc($t['kategori']) ?>">
                                    <?= esc($t['nama_template']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            Placeholder <code>{nama_panggilan}</code> akan diganti dengan
                            <strong><?= esc($namaPanggilan) ?></strong>.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan untuk Siswa <span class="text-danger">*</span></label>
                        <textarea id="catatanText" name="catatan_wali_kelas" class="form-control" rows="5"
                            minlength="10" required><?= esc($rapor['catatan_wali_kelas'] ?? '') ?></textarea>
                        <small class="text-muted">Minimal 10 karakter.</small>
                    </div>

                    <button class="btn btn-primary bg-pastel-primary border-0 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Catatan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB: Ketidakhadiran -->
    <div class="tab-pane fade" id="tab-ketidakhadiran">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-calendar-x me-2"></i>Ketidakhadiran</h5>
                <form action="<?= base_url('guru/wali-kelas/save-ketidakhadiran') ?>" method="post" class="row g-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_siswa" value="<?= $siswa['id_siswa'] ?>">

                    <div class="col-md-4">
                        <label class="form-label">Sakit (hari)</label>
                        <input type="number" name="sakit" class="form-control" min="0" max="200"
                               value="<?= esc($rapor['sakit'] ?? 0) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Izin (hari)</label>
                        <input type="number" name="izin" class="form-control" min="0" max="200"
                               value="<?= esc($rapor['izin'] ?? 0) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanpa Keterangan (hari)</label>
                        <input type="number" name="alpa" class="form-control" min="0" max="200"
                               value="<?= esc($rapor['alpa'] ?? 0) ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary bg-pastel-primary border-0 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Ketidakhadiran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB: Ekstrakurikuler -->
    <div class="tab-pane fade" id="tab-ekskul">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-stars me-2"></i>Ekstrakurikuler</h5>
                <form action="<?= base_url('guru/wali-kelas/save-ekskul') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_siswa" value="<?= $siswa['id_siswa'] ?>">

                    <p class="text-muted small">Centang ekstrakurikuler yang siswa ikuti, sesuaikan keterangannya bila perlu.
                        Ekskul bertanda <span class="badge bg-danger">Wajib</span> selalu diikuti semua siswa.</p>

                    <?php foreach ($master_ekskul as $me): ?>
                        <?php
                        $existing = $ekskulMap[(int) $me['id_ekskul']] ?? null;
                        $isWajib  = (int) ($me['wajib'] ?? 0) === 1;
                        $checked  = $isWajib || $existing;
                        $ket      = $existing['keterangan'] ?? $me['deskripsi_default'];
                        ?>
                        <div class="mb-3 p-3 border rounded">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="ekskul[<?= $me['id_ekskul'] ?>][aktif]" value="1"
                                    id="ekskul-<?= $me['id_ekskul'] ?>"
                                    <?= $checked ? 'checked' : '' ?> <?= $isWajib ? 'disabled' : '' ?>>
                                <label class="form-check-label fw-semibold" for="ekskul-<?= $me['id_ekskul'] ?>">
                                    <?= esc($me['nama']) ?>
                                    <?php if ($isWajib): ?><span class="badge bg-danger ms-1">Wajib</span><?php endif; ?>
                                </label>
                                <?php if ($isWajib): ?>
                                    <!-- checkbox disabled tidak ikut submit → hidden input agar tetap terkirim -->
                                    <input type="hidden" name="ekskul[<?= $me['id_ekskul'] ?>][aktif]" value="1">
                                <?php endif; ?>
                            </div>
                            <textarea name="ekskul[<?= $me['id_ekskul'] ?>][keterangan]" class="form-control mt-2"
                                rows="2" placeholder="<?= esc($me['deskripsi_default']) ?>"><?= esc($ket) ?></textarea>
                        </div>
                    <?php endforeach; ?>

                    <button class="btn btn-primary bg-pastel-primary border-0 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Ekstrakurikuler
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB: Kokurikuler P5 -->
    <div class="tab-pane fade" id="tab-koko">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-award me-2"></i>Kokurikuler — Profil Pelajar Pancasila</h5>
                <?php if (!$tema): ?>
                    <div class="alert alert-warning">Tema kokurikuler untuk kelas ini di TA aktif belum di-seed. Hubungi admin.</div>
                <?php else: ?>
                    <p class="mb-2">
                        Tema: <strong><?= esc($tema['nama_tema']) ?></strong>
                    </p>
                    <form action="<?= base_url('guru/wali-kelas/save-kokurikuler') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_siswa" value="<?= $siswa['id_siswa'] ?>">
                        <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">

                        <?php foreach ($master_dimensi as $d): ?>
                            <?php $existing = $kokoMap[(int) $d['id_dimensi']] ?? null; ?>
                            <div class="mb-3 p-3 border rounded">
                                <label class="form-label fw-semibold text-capitalize">
                                    <?= esc($d['nama_dimensi']) ?>
                                </label>
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <input type="text" name="dimensi[<?= $d['id_dimensi'] ?>][subdimensi]"
                                            class="form-control" placeholder="Subdimensi (mis. hubungan dengan sesama manusia)"
                                            value="<?= esc($existing['subdimensi'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <?php $lvl = $existing['level'] ?? 'berkembang'; ?>
                                        <select name="dimensi[<?= $d['id_dimensi'] ?>][level]" class="form-select">
                                            <option value="berkembang"   <?= $lvl === 'berkembang'   ? 'selected' : '' ?>>berkembang</option>
                                            <option value="cakap"        <?= $lvl === 'cakap'        ? 'selected' : '' ?>>cakap</option>
                                            <option value="mahir"        <?= $lvl === 'mahir'        ? 'selected' : '' ?>>mahir</option>
                                            <option value="sangat_mahir" <?= $lvl === 'sangat_mahir' ? 'selected' : '' ?>>sangat mahir</option>
                                        </select>
                                    </div>
                                </div>
                                <small class="text-muted">Kosongkan subdimensi untuk menghapus dimensi ini dari rapor.</small>
                            </div>
                        <?php endforeach; ?>

                        <hr class="my-3">
                        <label class="form-label fw-semibold"><i class="bi bi-card-text me-1"></i>Narasi Kokurikuler (untuk rapor)</label>
                        <?php
                        $kokoNarasiExisting = trim((string) ($rapor['narasi_koko'] ?? ''));
                        $kokoPrefill = $kokoNarasiExisting !== '' ? $kokoNarasiExisting : (string) ($koko_draft ?? '');
                        ?>
                        <textarea name="narasi_koko" id="narasiKoko" rows="5" class="form-control"
                            placeholder="Narasi kokurikuler yang tampil di rapor..."><?= esc($kokoPrefill) ?></textarea>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAmbilKoko">
                                <i class="bi bi-arrow-repeat me-1"></i> Ambil ulang dari template (otomatis)
                            </button>
                            <small class="text-muted align-self-center">Kosongkan untuk pakai narasi otomatis dari dimensi di atas.</small>
                        </div>

                        <div class="mt-3">
                            <button class="btn btn-primary bg-pastel-primary border-0 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Kokurikuler
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('selectTemplate')?.addEventListener('change', function () {
        const txt = (this.value || '').replace(/\{nama_panggilan\}/g, <?= json_encode($namaPanggilan) ?>);
        if (txt) document.getElementById('catatanText').value = txt;
    });

    // Koko: ambil ulang narasi otomatis dari dimensi (draft auto-generate).
    document.getElementById('btnAmbilKoko')?.addEventListener('click', function () {
        const draft = <?= json_encode($koko_draft ?? '', JSON_UNESCAPED_UNICODE) ?>;
        const ta = document.getElementById('narasiKoko');
        if (!ta) return;
        if (!draft) { alert('Belum ada dimensi kokurikuler yang diisi untuk dijadikan narasi otomatis.'); return; }
        ta.value = draft;
    });
</script>
<?= $this->endSection() ?>
