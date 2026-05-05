<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
    <div class="sidebar-heading">Menu Utama</div>
    <a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <div class="sidebar-heading mt-3">Master Data</div>
    <a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
    <a href="<?= base_url('admin/import') ?>"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Massal</a>
    <a href="<?= base_url('admin/guru') ?>"><i class="bi bi-person-badge me-2"></i> Data Guru</a>
    <a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-building me-2"></i> Kelas</a>
    <a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
    <div class="sidebar-heading mt-3">Akademik</div>
    <a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
    <a href="<?= base_url('admin/kkm') ?>"><i class="bi bi-sliders me-2"></i> KKM</a>
    <a href="<?= base_url('admin/rapor') ?>" class="active"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
    <div class="sidebar-heading mt-3">Audit</div>
    <a href="<?= base_url('admin/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-text me-2"></i>Manajemen Rapor Siswa</h4>
        <p class="text-muted mb-0">Isi dan periksa rapor siswa. Finalisasi menjadi pengecekan akhir sebelum orang tua dapat melihat rapor.</p>
    </div>
    <?php if (!empty($filter_ta) && !empty($filter_kelas)): ?>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importAttendanceModal">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Rekap Absensi
        </button>
    <?php endif; ?>
</div>

<?php if (isset($selected_tahun_ajaran) && $selected_tahun_ajaran): ?>
    <?php if ($selected_tahun_ajaran['status_pengisian'] !== 'Kunci'): ?>
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <i class="bi bi-lock me-2"></i>
            Finalisasi rapor akan aktif setelah semester dikunci pada menu Tahun Ajaran.
        </div>
    <?php else: ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-shield-check me-2"></i>
            Semester sudah dikunci. Finalisasi hanya berhasil jika seluruh siswa sudah memiliki nilai lengkap, absensi, catatan wali kelas, status kenaikan/kelulusan, dan tindak lanjut remedial bila ada.
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('finalization_errors')): ?>
    <div class="alert alert-warning mb-4">
        <h6 class="fw-bold mb-2"><i class="bi bi-list-check me-2"></i>Daftar kekurangan sebelum finalisasi</h6>
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('finalization_errors') as $issue): ?>
                <li><?= esc($issue) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-funnel me-2"></i>Filter Data Rapor</div>
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/rapor') ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tahun Ajaran</label>
                <select name="id_tahun_ajaran" class="form-select">
                    <option value="">-- Pilih Tahun Ajaran --</option>
                    <?php foreach ($tahun_ajaran as $ta): ?>
                    <option value="<?= $ta['id_tahun_ajaran'] ?>"
                        <?= (isset($filter_ta) && $filter_ta == $ta['id_tahun_ajaran']) ? 'selected' : '' ?>>
                        <?= esc($ta['tahun_ajaran']) ?> - Semester <?= esc($ta['semester']) ?>
                        <?= $ta['aktif'] === 'aktif' ? '(Aktif)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <select name="id_kelas" class="form-select">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k['id_kelas'] ?>"
                        <?= (isset($filter_kelas) && $filter_kelas == $k['id_kelas']) ? 'selected' : '' ?>>
                        <?= esc($k['nama_kelas']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($filter_ta) && !empty($filter_kelas)): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Gunakan template resmi yang telah disediakan sistem untuk mengisi rekap <strong>sakit</strong>, <strong>izin</strong>, dan <strong>alpa</strong>. Pastikan format kolom tidak diubah agar data dapat dibaca sistem.
    </div>
<?php endif; ?>

<?php if (!empty($rapor_data)): ?>
<?php if (!empty($summary)): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total Siswa</div><div class="fs-4 fw-bold"><?= esc($summary['total_siswa']) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Siswa Lengkap</div><div class="fs-4 fw-bold text-success"><?= esc($summary['siswa_lengkap']) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Belum Lengkap</div><div class="fs-4 fw-bold text-danger"><?= esc($summary['siswa_belum_lengkap']) ?></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Sudah Final</div><div class="fs-4 fw-bold text-primary"><?= esc($summary['siswa_final']) ?></div></div></div></div>
    </div>
<?php endif; ?>
<?php if (!empty($filter_ta) && !empty($filter_kelas)): ?>
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h6 class="fw-bold mb-1"><i class="bi bi-shield-check me-2"></i>Finalisasi Rapor Kelas</h6>
                <p class="text-muted mb-0 small">Klik tombol ini setelah semua isi rapor siswa di kelas terpilih sudah diperiksa dan lengkap.</p>
            </div>
            <form action="<?= base_url('admin/rapor/finalize-class/' . ($filter_kelas ?? '') . '/' . ($filter_ta ?? '')) ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success" <?= (isset($selected_tahun_ajaran) && $selected_tahun_ajaran['status_pengisian'] === 'Kunci') ? '' : 'disabled' ?>
                    onclick="return confirm('Finalisasi rapor seluruh siswa pada kelas ini? Pastikan semua data sudah lengkap dan benar.')">
                    <i class="bi bi-shield-check me-1"></i> Finalisasi Rapor Kelas
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-table me-2"></i>Data Rapor
        <span class="text-muted fw-normal ms-1">(<?= count($rapor_data) ?> siswa)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Siswa</th>
                        <th>NIS</th>
                        <th>Sakit</th>
                        <th>Izin</th>
                        <th>Alpa</th>
                        <th>Kelengkapan Nilai</th>
                        <th>Catatan Wali</th>
                        <th>Remedial</th>
                        <th>Status Kenaikan</th>
                        <th>Status Rapor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rapor_data as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($r['nama_siswa']) ?></strong></td>
                        <td><?= esc($r['nis']) ?></td>
                        <td><?= $r['sakit'] ?? 0 ?></td>
                        <td><?= $r['izin'] ?? 0 ?></td>
                        <td><?= $r['alpa'] ?? 0 ?></td>
                        <td>
                            <div class="small">Mapel berjalan: <strong><?= esc($r['jumlah_mapel_berjalan'] ?? 0) ?></strong></div>
                            <div class="small">Nilai akhir masuk: <strong><?= esc($r['jumlah_nilai_akhir'] ?? 0) ?></strong></div>
                            <?php if (!empty($r['is_complete'])): ?>
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mt-1">Lengkap</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle mt-1">Belum lengkap</span>
                                <?php if (!empty($r['issues'])): ?>
                                    <div class="small text-muted mt-1"><?= esc(implode('; ', array_slice($r['issues'], 0, 2))) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($r['catatan_wali_kelas'])): ?>
                                <?= esc(mb_strimwidth($r['catatan_wali_kelas'], 0, 48, '...')) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php $remedialBelumLengkap = (int) ($r['remedial_belum_lengkap'] ?? 0); ?>
                            <?php if ($remedialBelumLengkap > 0): ?>
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                    <?= $remedialBelumLengkap ?> belum lengkap
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Siap finalisasi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status_kenaikan']): ?>
                                <span class="badge bg-<?= $r['status_kenaikan'] === 'Naik' ? 'success' : ($r['status_kenaikan'] === 'Lulus' ? 'info' : 'danger') ?>">
                                    <?= esc($r['status_kenaikan']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Belum</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($r['is_finalized'])): ?>
                                <span class="badge bg-primary">Final</span>
                                <div class="small text-muted mt-1">
                                    <?= !empty($r['finalized_at']) ? date('d-m-Y H:i', strtotime($r['finalized_at'])) : '-' ?>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#editRaporModal"
                                    data-id="<?= $r['id_siswa'] ?>"
                                    data-nama="<?= esc($r['nama_siswa']) ?>"
                                    data-id_rapor="<?= $r['id_rapor'] ?? '' ?>"
                                    data-sakit="<?= $r['sakit'] ?? 0 ?>"
                                    data-izin="<?= $r['izin'] ?? 0 ?>"
                                    data-alpa="<?= $r['alpa'] ?? 0 ?>"
                                    data-catatan="<?= esc($r['catatan_wali_kelas'] ?? '') ?>"
                                    data-status="<?= $r['status_kenaikan'] ?? '' ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="<?= base_url('admin/rapor/finalize/' . $r['id_siswa'] . '/' . ($filter_ta ?? '')) ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-success" <?= (isset($selected_tahun_ajaran) && $selected_tahun_ajaran['status_pengisian'] === 'Kunci' && !empty($r['is_complete']) && empty($r['is_finalized'])) ? '' : 'disabled' ?> onclick="return confirm('Finalisasi rapor siswa ini?')">
                                        <i class="bi bi-shield-check"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($filter_ta) && !empty($filter_kelas)): ?>
<div class="modal fade" id="importAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Rekap Absensi ke Rapor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/rapor/import-attendance') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id_tahun_ajaran" value="<?= esc($filter_ta ?? '') ?>">
                <input type="hidden" name="id_kelas" value="<?= esc($filter_kelas ?? '') ?>">
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <div class="small text-muted mb-1">Mode import</div>
                        <div class="fw-semibold">Sinkronisasi rekap kehadiran bulanan ke draft rapor kelas yang sedang difilter</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Workbook Absensi <span class="text-danger">*</span></label>
                        <input type="file" name="attendance_file" class="form-control" accept=".xls,.xlsx" required>
                        <div class="form-text">Gunakan workbook absensi resmi sekolah. Sistem membaca sheet kelas dan menghitung kode <strong>S</strong>, <strong>I</strong>, dan <strong>A</strong> per siswa. Sel kosong dianggap hadir / belum diisi.</div>
                    </div>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Import ini hanya mengisi rekap kehadiran pada rapor.</li>
                        <li>Rapor yang sudah final tidak akan diubah melalui import absensi.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import Absensi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Rapor Modal -->
<div class="modal fade" id="editRaporModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Form Isi Rapor: <span id="modal-nama-siswa"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="raporForm" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_siswa" id="modal-id-siswa">
                <input type="hidden" name="id_tahun_ajaran" value="<?= $filter_ta ?? '' ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Sakit (hari)</label>
                            <input type="number" name="sakit" id="modal-sakit" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Izin (hari)</label>
                            <input type="number" name="izin" id="modal-izin" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alpa (hari)</label>
                            <input type="number" name="alpa" id="modal-alpa" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan Wali Kelas</label>
                            <textarea name="catatan_wali_kelas" id="modal-catatan" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status Kenaikan Kelas <span class="text-danger">*</span></label>
                            <select name="status_kenaikan" id="modal-status" class="form-select" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Naik">Naik Kelas</option>
                                <option value="Tidak Naik">Tidak Naik Kelas</option>
                                <option value="Lulus">Lulus</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Rapor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editRaporModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modal-nama-siswa').textContent = btn.dataset.nama;
    document.getElementById('modal-id-siswa').value = btn.dataset.id;
    document.getElementById('modal-sakit').value = btn.dataset.sakit;
    document.getElementById('modal-izin').value = btn.dataset.izin;
    document.getElementById('modal-alpa').value = btn.dataset.alpa;
    document.getElementById('modal-catatan').value = btn.dataset.catatan;
    document.getElementById('modal-status').value = btn.dataset.status;

    const idRapor = btn.dataset.id_rapor;
    const form = document.getElementById('raporForm');
    if (idRapor) {
        form.action = '<?= base_url('admin/rapor/update/') ?>' + idRapor;
    } else {
        form.action = '<?= base_url('admin/rapor/store') ?>';
    }
});
</script>

<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-file-earmark-text display-4 mb-3"></i>
        <p>Pilih Tahun Ajaran dan Kelas lalu klik <strong>Tampilkan</strong> untuk melihat data rapor.</p>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
