<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>" class="active"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-4">
    <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-calculator me-2"></i>Hitung Nilai Akhir & Remedial</h4>
    <p class="text-muted">Hitung nilai akhir otomatis berdasarkan nilai harian dan ujian, serta kelola tindak lanjut
        remedial</p>
</div>

<div class="alert alert-info d-flex align-items-start">
    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
    <div>
        <strong>Alur Proses Nilai Akhir:</strong>
        <ol class="mb-0 mt-2">
            <li>Pilih <strong>Kelas, Mata Pelajaran, dan Tahun Ajaran</strong> yang akan dihitung</li>
            <li>Sistem akan menghitung nilai akhir otomatis berdasarkan nilai harian dan nilai ujian</li>
            <li>Periksa hasil perhitungan dan <strong>isi tindak lanjut remedial</strong> untuk siswa yang nilainya di
                bawah KKM</li>
            <li>Simpan tindak lanjut remedial sebelum finalisasi rapor</li>
        </ol>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4">Pilih Parameter Perhitungan</h5>
        <form action="<?= base_url('guru/nilai-akhir/calculate') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="id_kelas" class="form-label fw-semibold">Kelas <span
                            class="text-danger">*</span></label>
                    <select name="id_kelas" id="id_kelas" class="form-select kelas-mapel-filter" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="id_mapel" class="form-label fw-semibold">Mata Pelajaran <span
                            class="text-danger">*</span></label>
                    <select name="id_mapel" id="id_mapel" class="form-select mapel-by-kelas" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php foreach ($mapel as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" data-kelas="<?= esc($m['kelas_ids'] ?? '') ?>"><?= esc($m['nama_mapel']) ?> (<?= $m['kelompok'] ?>) - <?= esc($m['daftar_kelas'] ?? '-') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Mapel akan disaring sesuai kelas yang dipilih.</small>
                </div>

                <div class="col-md-4">
                    <label for="id_tahun_ajaran" class="form-label fw-semibold">Tahun Ajaran <span
                            class="text-danger">*</span></label>
                    <select name="id_tahun_ajaran" id="id_tahun_ajaran" class="form-select" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php foreach ($tahun_ajaran as $ta): ?>
                            <option value="<?= $ta['id_tahun_ajaran'] ?>">
                                <?= esc($ta['tahun_ajaran']) ?> - Semester <?= $ta['semester'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary bg-pastel-primary border-0 px-4 fw-semibold">
                    <i class="bi bi-calculator me-1"></i> Hitung Nilai Akhir
                </button>
                <small class="text-muted ms-3">
                    <i class="bi bi-lightning me-1"></i> Sistem akan memproses semua siswa dalam kelas yang dipilih
                </small>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mt-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-question-circle me-2"></i>Informasi Penting</h5>
        <ul class="mb-0">
            <li class="mb-2">Pastikan <strong>Penilaian Agregat</strong> (Tugas, Ulangan, UTS, UAS) sudah diinput
                sebelum menghitung nilai akhir</li>
            <li class="mb-2">Nilai akhir dihitung otomatis oleh sistem berdasarkan komponen nilai yang sudah diinput
            </li>
            <li class="mb-2">Siswa dengan nilai di bawah KKM akan otomatis masuk status <span
                    class="badge bg-pastel-danger">Remedial</span></li>
            <li class="mb-2">Wajib mengisi <strong>Tindak Lanjut Remedial</strong> untuk setiap siswa yang remedial
                sebelum rapor difinalkan</li>
            <li>Setelah hitung nilai akhir, Anda akan diarahkan ke halaman pemeriksaan untuk mengisi tindak lanjut
                remedial</li>
        </ul>
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
