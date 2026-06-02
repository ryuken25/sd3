<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/nilai-harian') ?>"><i class="bi bi-journal-text me-2"></i> Nilai Harian</a>
<a href="<?= base_url('guru/nilai-ujian') ?>" class="active"><i class="bi bi-file-earmark-text me-2"></i> Nilai
    UTS/UAS</a>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-text me-2"></i>Input Nilai UTS / UAS</h4>
<p class="text-muted mb-4">Pilih kelas dan mata pelajaran untuk mulai menginput nilai ujian siswa.</p>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-4">
            <div class="rounded-circle bg-pastel-warning d-flex justify-content-center align-items-center me-3"
                style="width:50px;height:50px;">
                <i class="bi bi-file-earmark-text fs-4" style="color:#7d4e00"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">Input Nilai Ujian Per Kelas</h5>
                <small class="text-muted">Pilih kelas dan mata pelajaran, lalu isi nilai UTS dan UAS seluruh
                    siswa.</small>
            </div>
        </div>

        <form method="get" action="<?= base_url('guru/nilai-ujian/by-class') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_tahun_ajaran" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php foreach ($tahun_ajaran as $ta): ?>
                            <option value="<?= $ta['id_tahun_ajaran'] ?>"><?= esc($ta['tahun_ajaran']) ?> — Sem.
                                <?= $ta['semester'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                    <select class="form-select kelas-mapel-filter" name="id_kelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select class="form-select mapel-by-kelas" name="id_mapel" required>
                        <option value="">-- Pilih Mapel --</option>
                        <?php foreach ($mapel as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" data-kelas="<?= esc($m['kelas_ids'] ?? '') ?>"><?= esc($m['nama_mapel']) ?> (<?= esc($m['daftar_kelas'] ?? '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Mapel akan disaring sesuai kelas yang dipilih.</small>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-warning border-0 fw-semibold px-4">
                    <i class="bi bi-arrow-right-circle me-1"></i> Buka Form Input Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mt-4 p-3 bg-light rounded-3 border">
    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2 text-info"></i>Informasi</h6>
    <ul class="mb-0 small text-muted">
        <li>Nilai UTS dan UAS berkontribusi masing-masing <strong>30%</strong> terhadap Nilai Akhir.</li>
        <li>Nilai harian berkontribusi <strong>40%</strong>. Pastikan nilai harian sudah diisi sebelum menghitung nilai
            akhir.</li>
        <li>Jika semester sudah <strong>dikunci</strong>, input nilai tidak dapat dilakukan. Ajukan permintaan buka
            nilai ke admin.</li>
    </ul>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('form').forEach(function (form) {
    const kelasSelect = form.querySelector('select[name="id_kelas"]');
    const mapelSelect = form.querySelector('select[name="id_mapel"]');
    if (!kelasSelect || !mapelSelect) return;
    const sync = function () {
        const selectedKelas = kelasSelect.value;
        Array.from(mapelSelect.options).forEach(function (option) {
            if (!option.value) return;
            const kelasList = (option.dataset.kelas || '').split(',').filter(Boolean);
            option.hidden = selectedKelas && !kelasList.includes(selectedKelas);
        });
        if (mapelSelect.selectedOptions[0] && mapelSelect.selectedOptions[0].hidden) mapelSelect.value = '';
    };
    kelasSelect.addEventListener('change', sync);
    sync();
});
</script>
<?= $this->endSection() ?>
