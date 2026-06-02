<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>" class="active"><i class="bi bi-unlock me-2"></i> Permintaan Buka
    Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-unlock me-2"></i>Permintaan Buka Nilai</h4>
        <p class="text-muted mb-0">Ajukan permintaan ke admin untuk membuka sementara nilai pada semester yang sudah
            dikunci.</p>
    </div>
    <button type="button" class="btn btn-primary bg-pastel-primary border-0 shadow-sm" data-bs-toggle="modal"
        data-bs-target="#tambahRequestModal">
        <i class="bi bi-plus-circle me-1"></i> Ajukan Permintaan Baru
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Tahun Ajaran / Semester</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Alasan Permintaan</th>
                        <th>Tanggal Ajuan</th>
                        <th class="text-center">Status</th>
                        <th>Catatan Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_requests)): ?>
                        <?php $no = 1;
                        foreach ($my_requests as $r): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= esc($r['tahun_ajaran']) ?> (Sem
                                        <?= esc($r['semester']) ?>)</span>
                                </td>
                                <td><?= esc($r['nama_kelas'] ?? '-') ?></td>
                                <td><?= esc($r['nama_mapel'] ?? '-') ?></td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width:250px;"
                                        title="<?= esc($r['alasan']) ?>">
                                        <?= esc($r['alasan']) ?>
                                    </span>
                                </td>
                                <td><small class="text-muted"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></small></td>
                                <td class="text-center">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i
                                                class="bi bi-hourglass-split me-1"></i>Menunggu</span>
                                    <?php elseif ($r['status'] === 'disetujui'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Disetujui</span>
                                        <?php if (!empty($r['tanggal_akses'])): ?>
                                            <br><small class="text-muted">Berlaku sampai
                                                <?= date('d/m/Y', strtotime($r['tanggal_akses'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['catatan_admin'])): ?>
                                        <span class="text-muted small fst-italic"><?= esc($r['catatan_admin']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada permintaan yang diajukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mt-4">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2 text-info"></i>Informasi</h6>
        <ul class="mb-0 small text-muted">
            <li>Permintaan hanya dapat diajukan untuk semester yang berstatus <strong>Kunci</strong>, nonaktif, atau sudah tidak berjalan.</li>
            <li>Pilih kelas dan mata pelajaran agar akses yang dibuka hanya sesuai kebutuhan perbaikan.</li>
            <li>Hanya satu permintaan menunggu untuk kombinasi kelas, mata pelajaran, dan semester yang sama.</li>
            <li>Admin akan meninjau dan memberikan keputusan. Pantau status di tabel di atas.</li>
            <li>Jika disetujui, akses edit nilai dibuka sementara dan terbatas untuk guru pemohon, kelas, mapel, dan tahun ajaran yang diajukan.</li>
        </ul>
    </div>
</div>

<!-- Modal Tambah Request -->
<div class="modal fade" id="tambahRequestModal" tabindex="-1" aria-labelledby="tambahRequestLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                <h5 class="modal-title fw-bold" id="tambahRequestLabel">Ajukan Permintaan Buka Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('guru/request-buka-nilai/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-start border-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div>
                            <strong>Perhatian!</strong> Permintaan ini akan dikirim ke admin untuk diperiksa.
                            Alasan wajib jelas, misalnya ada kesalahan input nilai siswa.
                        </div>
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
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_mapel" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($mapel as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>" data-kelas="<?= esc($m['kelas_ids'] ?? '') ?>"><?= esc($m['nama_mapel']) ?> (<?= esc($m['daftar_kelas'] ?? '-') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Dropdown mapel akan mengikuti kelas agar akses tidak terbuka lintas kelas.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_tahun_ajaran" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            <?php foreach ($tahun_ajaran as $ta): ?>
                                <option value="<?= $ta['id_tahun_ajaran'] ?>"><?= esc($ta['tahun_ajaran']) ?> — Semester
                                    <?= esc($ta['semester']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Permintaan <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" name="alasan" rows="4" required minlength="10"
                            placeholder="Contoh: Ada kesalahan input nilai siswa pada semester sebelumnya."></textarea>
                        <div class="form-text">Minimal 10 karakter. Jelaskan data mana yang perlu diperbaiki.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 shadow-sm">
                        <i class="bi bi-send me-1"></i> Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>
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
