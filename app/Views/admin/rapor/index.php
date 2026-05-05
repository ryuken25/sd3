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
                                     data-id_tahun_ajaran="<?= esc($filter_ta ?? '') ?>"
                                     data-nama="<?= esc($r['nama_siswa']) ?>"
                                     data-id_rapor="<?= $r['id_rapor'] ?? '' ?>"
                                     data-is_finalized="<?= !empty($r['is_finalized']) ? '1' : '0' ?>"
                                     data-sakit="<?= $r['sakit'] ?? 0 ?>"
                                    data-izin="<?= $r['izin'] ?? 0 ?>"
                                    data-alpa="<?= $r['alpa'] ?? 0 ?>"
                                    data-catatan="<?= esc($r['catatan_wali_kelas'] ?? '') ?>"
                                     data-status="<?= $r['status_kenaikan'] ?? '' ?>">
                                    <i class="bi bi-file-earmark-text me-1"></i>Detail/Edit
                                 </button>
                                <?php if (!empty($r['is_finalized']) && !empty($r['id_rapor'])): ?>
                                    <form action="<?= base_url('admin/rapor/unfinalize/' . $r['id_rapor']) ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Batalkan finalisasi rapor ini? Orang tua tidak dapat melihat rapor final sampai difinalisasi ulang.')">
                                            <i class="bi bi-unlock me-1"></i>Batalkan Final
                                        </button>
                                    </form>
                                <?php elseif (!empty($r['is_complete'])): ?>
                                    <form action="<?= base_url('admin/rapor/finalize/' . $r['id_siswa'] . '/' . ($filter_ta ?? '')) ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success" <?= (isset($selected_tahun_ajaran) && $selected_tahun_ajaran['status_pengisian'] === 'Kunci') ? '' : 'disabled' ?> onclick="return confirm('Finalisasi rapor siswa ini?')">
                                            <i class="bi bi-shield-check me-1"></i>Finalisasi
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle align-self-center">Belum lengkap</span>
                                <?php endif; ?>
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
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="overwrite_finalized" value="1" id="overwriteFinalizedImport">
                        <label class="form-check-label" for="overwriteFinalizedImport">
                            Izinkan import menimpa rapor final dan batalkan finalisasi otomatis
                        </label>
                        <div class="form-text">Jika dicentang, absensi rapor final akan diperbarui dan statusnya berubah menjadi Draft.</div>
                    </div>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Import ini hanya mengisi rekap kehadiran pada rapor.</li>
                        <li>Tanpa opsi overwrite, rapor yang sudah final tidak akan diubah melalui import absensi.</li>
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
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Detail Rapor Siswa: <span id="modal-nama-siswa"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="raporForm" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_siswa" id="modal-id-siswa">
                <input type="hidden" name="id_tahun_ajaran" value="<?= $filter_ta ?? '' ?>">
                <div class="modal-body">
                    <div id="rapor-detail-loading" class="alert alert-info border-0 shadow-sm mb-3">
                        <i class="bi bi-arrow-repeat me-2"></i>Memuat detail isi rapor siswa...
                    </div>
                    <div id="rapor-detail-error" class="alert alert-danger border-0 shadow-sm mb-3 d-none"></div>
                    <div id="rapor-final-warning" class="alert alert-warning border-0 shadow-sm mb-3 d-none">
                        <div class="fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Rapor ini sudah final.</div>
                        <div class="small mb-0">Jika disimpan, status finalisasi akan otomatis dibatalkan dan orang tua tidak dapat melihat rapor final sampai difinalisasi ulang.</div>
                    </div>
                    <div id="rapor-detail-content" class="d-none">
                        <div class="alert alert-primary border-0 shadow-sm mb-3">
                            <div class="fw-bold"><i class="bi bi-eye me-2"></i>Preview Isi Lembar Rapor</div>
                            <div class="small mb-0">Bagian ini bersifat read-only untuk pengecekan isi rapor lengkap sebelum admin mengedit absensi, catatan wali kelas, atau status kenaikan.</div>
                        </div>
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="bi bi-person-vcard me-2"></i>Identitas Siswa</h6>
                                <div class="row g-3 small">
                                    <div class="col-md-4">
                                        <div class="text-muted">Nama</div>
                                        <div class="fw-semibold" id="detail-nama">-</div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-muted">NIS</div>
                                        <div class="fw-semibold" id="detail-nis">-</div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-muted">NISN</div>
                                        <div class="fw-semibold" id="detail-nisn">-</div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-muted">Kelas</div>
                                        <div class="fw-semibold" id="detail-kelas">-</div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-muted">Semester</div>
                                        <div class="fw-semibold" id="detail-semester">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted">Tahun Ajaran</div>
                                        <div class="fw-semibold" id="detail-tahun-ajaran">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted">Status Rapor</div>
                                        <div id="detail-status-rapor">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted">Kelengkapan</div>
                                        <div id="detail-kelengkapan">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="detail-rapor-message" class="alert alert-warning border-0 shadow-sm d-none"></div>
                        <div id="detail-nilai-message" class="alert alert-warning border-0 shadow-sm d-none"></div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white fw-bold"><i class="bi bi-journal-check me-2"></i>Nilai Mata Pelajaran</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 56px;">No</th>
                                            <th>Mata Pelajaran</th>
                                            <th class="text-center" style="width: 90px;">KKM</th>
                                            <th class="text-center" style="width: 110px;">Nilai</th>
                                            <th class="text-center" style="width: 90px;">Huruf</th>
                                            <th class="text-center" style="width: 150px;">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-nilai-body">
                                        <tr><td colspan="6" class="text-center text-muted py-3">Nilai belum tersedia</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white fw-bold"><i class="bi bi-calendar-check me-2"></i>Absensi</div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between border-bottom py-2"><span>Sakit</span><strong><span id="detail-sakit">0</span> hari</strong></div>
                                        <div class="d-flex justify-content-between border-bottom py-2"><span>Izin</span><strong><span id="detail-izin">0</span> hari</strong></div>
                                        <div class="d-flex justify-content-between py-2"><span>Alpa</span><strong><span id="detail-alpa">0</span> hari</strong></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white fw-bold"><i class="bi bi-chat-left-text me-2"></i>Catatan dan Status</div>
                                    <div class="card-body">
                                        <div class="small text-muted mb-1">Catatan Wali Kelas</div>
                                        <div class="border rounded bg-light p-3 mb-3" id="detail-catatan-preview">-</div>
                                        <div class="small text-muted mb-1">Status Kenaikan Kelas</div>
                                        <div id="detail-status-kenaikan-preview">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2"></i>Form Edit Data Rapor</h6>
                    <div class="small text-muted mb-2">Bagian ini tetap dapat diedit oleh admin tanpa mengubah data nilai mata pelajaran.</div>
                    <div class="fw-semibold mb-2"><i class="bi bi-calendar-check me-2"></i>Bagian Absensi</div>
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
                    </div>
                    <div class="fw-semibold mt-3 mb-2"><i class="bi bi-chat-left-text me-2"></i>Bagian Catatan dan Status</div>
                    <div class="row g-3">
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
const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const formatNumber = (value) => {
    if (value === null || value === undefined || value === '') {
        return '-';
    }
    const number = Number(value);
    return Number.isNaN(number) ? '-' : number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const setText = (id, value) => {
    document.getElementById(id).textContent = value || '-';
};

const setAlert = (id, message) => {
    const element = document.getElementById(id);
    if (message) {
        element.textContent = message;
        element.classList.remove('d-none');
    } else {
        element.textContent = '';
        element.classList.add('d-none');
    }
};

const renderDetailRapor = (payload) => {
    const data = payload.data || {};
    const siswa = data.siswa || {};
    const tahunAjaran = data.tahun_ajaran || {};
    const rapor = data.rapor || {};
    const summary = data.summary || {};
    const nilai = Array.isArray(data.nilai) ? data.nilai : [];

    setText('detail-nama', siswa.nama_siswa);
    setText('detail-nis', siswa.nis);
    setText('detail-nisn', siswa.nisn);
    setText('detail-kelas', siswa.nama_kelas);
    setText('detail-semester', tahunAjaran.semester ? `Semester ${tahunAjaran.semester}` : '-');
    setText('detail-tahun-ajaran', tahunAjaran.tahun_ajaran);

    document.getElementById('detail-status-rapor').innerHTML = rapor.is_finalized
        ? '<span class="badge bg-primary">Final</span>'
        : '<span class="badge bg-warning text-dark">Draft</span>';

    document.getElementById('detail-kelengkapan').innerHTML = summary.is_complete
        ? '<span class="badge bg-success">Lengkap</span>'
        : '<span class="badge bg-danger">Belum lengkap</span>';

    setText('detail-sakit', rapor.sakit ?? 0);
    setText('detail-izin', rapor.izin ?? 0);
    setText('detail-alpa', rapor.alpa ?? 0);
    setText('detail-catatan-preview', rapor.catatan_wali_kelas || 'Belum ada catatan wali kelas.');
    const statusKenaikan = rapor.status_kenaikan || 'Belum ditentukan';
    const statusClass = statusKenaikan === 'Naik' ? 'bg-success' : (statusKenaikan === 'Lulus' ? 'bg-info' : (statusKenaikan === 'Tidak Naik' ? 'bg-danger' : 'bg-secondary'));
    document.getElementById('detail-status-kenaikan-preview').innerHTML = `<span class="badge ${statusClass}">${escapeHtml(statusKenaikan)}</span>`;

    setAlert('detail-rapor-message', data.messages?.rapor || '');
    setAlert('detail-nilai-message', data.messages?.nilai || '');

    document.getElementById('modal-sakit').value = rapor.sakit ?? 0;
    document.getElementById('modal-izin').value = rapor.izin ?? 0;
    document.getElementById('modal-alpa').value = rapor.alpa ?? 0;
    document.getElementById('modal-catatan').value = rapor.catatan_wali_kelas || '';
    document.getElementById('modal-status').value = rapor.status_kenaikan || '';

    const nilaiBody = document.getElementById('detail-nilai-body');
    if (nilai.length === 0) {
        nilaiBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Nilai belum tersedia</td></tr>';
    } else {
        nilaiBody.innerHTML = nilai.map((row, index) => {
            const isTuntas = row.keterangan === 'Tuntas';
            const isRemedial = row.keterangan === 'Remedial';
            const badgeClass = isTuntas ? 'bg-success' : (isRemedial ? 'bg-danger' : 'bg-secondary');
            const remedialNote = row.status_remedial ? `<div class="small text-muted">Remedial: ${escapeHtml(row.status_remedial)}</div>` : '';

            return `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(row.nama_mapel)}</td>
                    <td class="text-center">${formatNumber(row.kkm)}</td>
                    <td class="text-center fw-semibold">${formatNumber(row.nilai_akhir)}</td>
                    <td class="text-center">${escapeHtml(row.nilai_huruf || '-')}</td>
                    <td class="text-center"><span class="badge ${badgeClass}">${escapeHtml(row.keterangan || '-')}</span>${remedialNote}</td>
                </tr>
            `;
        }).join('');
    }
};

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
    const isFinalized = btn.dataset.is_finalized === '1';
    const form = document.getElementById('raporForm');
    if (idRapor) {
        form.action = '<?= base_url('admin/rapor/update/') ?>' + idRapor;
    } else {
        form.action = '<?= base_url('admin/rapor/store') ?>';
    }

    document.getElementById('rapor-detail-loading').classList.remove('d-none');
    document.getElementById('rapor-detail-content').classList.add('d-none');
    document.getElementById('rapor-final-warning').classList.toggle('d-none', !isFinalized);
    setAlert('rapor-detail-error', '');

    const idSiswa = btn.dataset.id;
    const idTahunAjaran = btn.dataset.id_tahun_ajaran;
    fetch(`<?= base_url('admin/rapor/detail') ?>/${idSiswa}/${idTahunAjaran}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(async (response) => {
            const payload = await response.json().catch(() => ({}));
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Detail rapor gagal dimuat.');
            }
            return payload;
        })
        .then((payload) => {
            renderDetailRapor(payload);
            document.getElementById('rapor-detail-content').classList.remove('d-none');
        })
        .catch((error) => {
            setAlert('rapor-detail-error', error.message || 'Detail rapor gagal dimuat.');
        })
        .finally(() => {
            document.getElementById('rapor-detail-loading').classList.add('d-none');
        });
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
