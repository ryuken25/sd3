<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/nilai-harian') ?>" class="active"><i class="bi bi-journal-text me-2"></i> Nilai Harian</a>
<a href="<?= base_url('guru/nilai-ujian') ?>"><i class="bi bi-file-earmark-text me-2"></i> Nilai UTS/UAS</a>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-journal-text me-2"></i>Input Nilai Harian</h4>
<p class="text-muted mb-4">Pilih mode input nilai yang sesuai kebutuhan Anda.</p>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--pastel-blue) !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-pastel-primary d-flex justify-content-center align-items-center me-3"
                        style="width:50px;height:50px;">
                        <i class="bi bi-table fs-4" style="color:#1a5276"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Mode 1: By Class</h5>
                        <small class="text-muted">Input vertikal seluruh siswa dalam 1 pelajaran</small>
                    </div>
                </div>
                <p class="text-muted small">Pilih kelas dan mata pelajaran, lalu isi nilai untuk semua siswa. Cocok
                    digunakan setelah selesai memeriksa hasil ulangan satu mapel.</p>
                <form method="get" action="<?= base_url('guru/nilai-harian/by-class') ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_tahun_ajaran" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            <?php foreach ($tahun_ajaran as $ta): ?>
                                <option value="<?= $ta['id_tahun_ajaran'] ?>"><?= esc($ta['tahun_ajaran']) ?> - Sem
                                    <?= $ta['semester'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select class="form-select kelas-mapel-filter" name="id_kelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelas as $k): ?>
                                <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select mapel-by-kelas" name="id_mapel" required>
                            <option value="">-- Pilih Mapel --</option>
                            <?php foreach ($mapel as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>" data-kelas="<?= esc($m['kelas_ids'] ?? '') ?>"><?= esc($m['nama_mapel']) ?> (<?= esc($m['daftar_kelas'] ?? '-') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Mapel akan disaring sesuai kelas yang dipilih.</small>
                    </div>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 w-100 fw-semibold">
                        <i class="bi bi-arrow-right-circle me-1"></i> Buka Form Input
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--pastel-green) !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-pastel-success d-flex justify-content-center align-items-center me-3"
                        style="width:50px;height:50px;">
                        <i class="bi bi-person-lines-fill fs-4" style="color:#145a14"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Mode 2: By Student</h5>
                        <small class="text-muted">Input horizontal semua mapel untuk 1 siswa</small>
                    </div>
                </div>
                <p class="text-muted small">Pilih siswa, lalu isi nilai di semua mata pelajarannya sekaligus. Cocok
                    untuk menyalin dari buku rapor bayangan per anak.</p>
                <form method="get" action="<?= base_url('guru/nilai-harian/by-student') ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_tahun_ajaran" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            <?php foreach ($tahun_ajaran as $ta): ?>
                                <option value="<?= $ta['id_tahun_ajaran'] ?>"><?= esc($ta['tahun_ajaran']) ?> - Sem
                                    <?= $ta['semester'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_kelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelas as $k): ?>
                                <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_siswa" required>
                            <option value="">-- Pilih Siswa --</option>
                        </select>
                        <small class="text-muted">*Pilih kelas terlebih dahulu</small>
                    </div>
                    <button type="submit" class="btn btn-success border-0 w-100 fw-semibold">
                        <i class="bi bi-arrow-right-circle me-1"></i> Buka Form Input
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-load siswa when kelas changes (for By Student mode)
    document.querySelectorAll('select[name="id_kelas"]').forEach(function (select) {
        select.addEventListener('change', function () {
            const form = this.closest('form');
            const mapelSelect = form.querySelector('select[name="id_mapel"]');
            if (mapelSelect) {
                const selectedKelas = this.value;
                Array.from(mapelSelect.options).forEach(function (option) {
                    if (!option.value) return;
                    const kelasList = (option.dataset.kelas || '').split(',').filter(Boolean);
                    option.hidden = selectedKelas && !kelasList.includes(selectedKelas);
                });
                if (mapelSelect.selectedOptions[0] && mapelSelect.selectedOptions[0].hidden) {
                    mapelSelect.value = '';
                }
            }
            const siswaSelect = form.querySelector('select[name="id_siswa"]');
            if (!siswaSelect) return;
            const id_kelas = this.value;
            const taSelect = form.querySelector('select[name="id_tahun_ajaran"]');
            const id_tahun_ajaran = taSelect ? taSelect.value : '';
            if (!id_kelas) { siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>'; return; }
            // Simple approach: show loading, then make a quick fetch
            siswaSelect.innerHTML = '<option value="">Memuat...</option>';
            fetch('<?= base_url('guru/nilai-harian/get-siswa') ?>?id_kelas=' + encodeURIComponent(id_kelas) + '&id_tahun_ajaran=' + encodeURIComponent(id_tahun_ajaran))
                .then(r => r.json())
                .then(data => {
                    siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
                    data.forEach(s => {
                        siswaSelect.innerHTML += `<option value="${s.id_siswa}">${s.nama_siswa}</option>`;
                    });
                }).catch(() => { siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>'; });
        });
        select.dispatchEvent(new Event('change'));
    });

    // Re-load siswa list when TA changes (By Student mode only — kelas re-dispatches the fetch)
    document.querySelectorAll('select[name="id_tahun_ajaran"]').forEach(function (taSelect) {
        taSelect.addEventListener('change', function () {
            const form = this.closest('form');
            const kelasSelect = form.querySelector('select[name="id_kelas"]');
            if (kelasSelect && kelasSelect.value) {
                kelasSelect.dispatchEvent(new Event('change'));
            }
        });
    });
</script>
<?= $this->endSection() ?>
