<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Data Master</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-door-open me-2"></i> Kelas</a>
<a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
<a href="<?= base_url('admin/kkm') ?>"><i class="bi bi-bar-chart-line me-2"></i> KKM</a>
<a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
<a href="<?= base_url('admin/guru') ?>"><i class="bi bi-person-badge me-2"></i> Data Guru</a>
<div class="sidebar-heading mt-3">Alat & Laporan</div>
<a href="<?= base_url('admin/import') ?>"><i class="bi bi-upload me-2"></i> Import Data</a>
<a href="<?= base_url('admin/request-buka-nilai') ?>" class="active"><i class="bi bi-unlock me-2"></i> Permintaan Buka
    Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 text-pastel-primary fw-bold"><i class="bi bi-unlock me-2"></i>Manajemen Permintaan Buka Nilai
        </h4>
        <small class="text-muted">Daftar permintaan guru untuk membuka sementara nilai pada semester yang sudah
            dikunci</small>
    </div>
</div>

<?php $pendingCount = count(array_filter($requests ?? [], fn($r) => $r['status'] === 'pending')); ?>
<?php if ($pendingCount > 0): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>Ada <strong><?= $pendingCount ?> permintaan</strong> yang menunggu persetujuan Anda.</div>
    </div>
<?php endif; ?>

<div class="alert alert-info border-0 shadow-sm mb-4">
    <i class="bi bi-shield-lock me-2"></i>
    Request buka nilai adalah mekanisme audit: admin hanya membuka akses edit sementara untuk guru pemohon pada kelas, mata pelajaran, dan tahun ajaran yang diminta. Sistem tidak membuka seluruh semester secara global.
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Nama Guru</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Alasan Permintaan</th>
                        <th>Waktu Pengajuan</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php $no = 1;
                        foreach ($requests as $r): ?>
                            <tr>
                                <td class="ps-4"><?= $no++ ?></td>
                                <td><strong><?= esc($r['nama_guru']) ?></strong></td>
                                <td><?= esc($r['nama_kelas'] ?? '-') ?></td>
                                <td><?= esc($r['nama_mapel'] ?? '-') ?></td>
                                <td><?= esc($r['tahun_ajaran']) ?></td>
                                <td>Semester <?= esc($r['semester']) ?></td>
                                <td><?= esc($r['alasan']) ?></td>
                                <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small></td>
                                <td>
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu</span>
                                    <?php elseif ($r['status'] === 'disetujui'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Disetujui</span>
                                        <?php if ($r['tanggal_akses']): ?>
                                            <br><small class="text-muted d-block mt-1">Akses s/d:
                                                <?= date('d/m/Y', strtotime($r['tanggal_akses'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal"
                                            data-bs-target="#approveModal<?= $r['id_request'] ?>">
                                            <i class="bi bi-check-lg"></i> Setujui
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal<?= $r['id_request'] ?>">
                                            <i class="bi bi-x-lg"></i> Tolak
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal<?= $r['id_request'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-success text-white border-0">
                                            <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Setujui Permintaan
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('admin/request-buka-nilai/approve/' . $r['id_request']) ?>"
                                            method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <p>Anda akan menyetujui permintaan dari
                                                    <strong><?= esc($r['nama_guru']) ?></strong>.
                                                </p>
                                                <p class="mb-1"><strong>Kelas:</strong> <?= esc($r['nama_kelas'] ?? '-') ?></p>
                                                <p class="mb-1"><strong>Mata Pelajaran:</strong>
                                                    <?= esc($r['nama_mapel'] ?? '-') ?></p>
                                                <p class="text-muted small">Alasan: <?= esc($r['alasan']) ?></p>
                                                <p class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>Akses
                                                    akan diberikan selama <strong>1 hari</strong> dan dibatasi pada guru, kelas,
                                                    mata pelajaran, serta semester yang diajukan.</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Catatan Admin (Opsional)</label>
                                                    <textarea class="form-control" name="catatan_admin" rows="2"
                                                        placeholder="Tambahkan catatan jika perlu..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success"><i
                                                        class="bi bi-check-lg me-1"></i>Ya, Setujui</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?= $r['id_request'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white border-0">
                                            <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Tolak Permintaan</h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('admin/request-buka-nilai/reject/' . $r['id_request']) ?>"
                                            method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <p>Anda akan <strong>menolak</strong> permintaan dari
                                                    <strong><?= esc($r['nama_guru']) ?></strong>.
                                                </p>
                                                <div class="mb-3">
                                                    <label class="form-label">Alasan Penolakan <span
                                                            class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="catatan_admin" rows="3" required
                                                        placeholder="Jelaskan alasan penolakan..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Ya,
                                                    Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada permintaan buka nilai
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
