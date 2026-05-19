<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>" class="active"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-4">
    <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-bookmark-check me-2"></i>Capaian Kompetensi</h4>
    <p class="text-muted">Input Capaian Pembelajaran (CP) per siswa per mata pelajaran. Hasil otomatis dirangkai menjadi narasi rapor.</p>
</div>

<div class="alert alert-info d-flex align-items-start">
    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
    <div>
        <strong>Cara Kerja:</strong> pilih Kelas, Mapel, dan TA. Untuk tiap siswa, pilih status setiap CP:
        <em>Tercapai Sangat Baik</em>, <em>Perlu Peningkatan</em>, atau <em>Belum Dinilai</em>. Anda juga bisa
        tambah CP <em>custom</em> di luar daftar master. Narasi narasi akan muncul di rapor cetak & e-rapor.
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form method="get" action="<?= base_url('guru/capaian-kompetensi/input') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_tahun_ajaran" required>
                        <option value="">— Pilih TA —</option>
                        <?php foreach ($tahun_ajaran as $ta): ?>
                            <option value="<?= $ta['id_tahun_ajaran'] ?>">
                                <?= esc($ta['tahun_ajaran']) ?> — Sem. <?= esc($ta['semester']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                    <select class="form-select kelas-mapel-filter" name="id_kelas" required>
                        <option value="">— Pilih Kelas —</option>
                        <?php foreach ($kelas as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select class="form-select mapel-by-kelas" name="id_mapel" required>
                        <option value="">— Pilih Mapel —</option>
                        <?php foreach ($mapel as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" data-kelas="<?= esc($m['kelas_ids'] ?? '') ?>">
                                <?= esc($m['nama_mapel']) ?> (<?= esc($m['daftar_kelas'] ?? '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary bg-pastel-primary border-0 fw-semibold px-4">
                    <i class="bi bi-arrow-right-circle me-1"></i> Buka Form Input CP
                </button>
            </div>
        </form>
    </div>
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
