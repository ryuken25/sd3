<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>" class="active"><i class="bi bi-list-check me-2"></i> Rekap
    Remedial</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<div class="sidebar-heading mt-3">Lainnya</div>
    <a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-4">
    <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-list-check me-2"></i>Rekap Remedial</h4>
    <p class="text-muted">Daftar siswa yang nilainya di bawah KKM beserta status remedial dan tindak lanjut.</p>
</div>

<!-- Filter Form -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3">Pilih Filter</h5>
        <form method="get" action="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_tahun_ajaran" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php
                        $taList = $siswa === null ? ($tahun_ajaran ?? []) : ($tahun_ajaran_list ?? []);
                        foreach ($taList as $ta): ?>
                            <option value="<?= $ta['id_tahun_ajaran'] ?>" <?= isset($selected_tahun) && $selected_tahun == $ta['id_tahun_ajaran'] ? 'selected' : '' ?>>
                                <?= esc($ta['tahun_ajaran']) ?> — Sem. <?= $ta['semester'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_kelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php
                        $kelasList = $siswa === null ? ($kelas ?? []) : ($kelas_list ?? []);
                        foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= isset($selected_kelas) && $selected_kelas == $k['id_kelas'] ? 'selected' : '' ?>>
                                <?= esc($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_mapel" required>
                        <option value="">-- Pilih Mapel --</option>
                        <?php
                        $mapelList = $siswa === null ? ($mapel ?? []) : ($mapel_list ?? []);
                        foreach ($mapelList as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" <?= isset($selected_mapel) && $selected_mapel == $m['id_mapel'] ? 'selected' : '' ?>>
                                <?= esc($m['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary bg-pastel-primary border-0 fw-semibold px-4">
                    <i class="bi bi-search me-1"></i> Tampilkan Rekap
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($siswa !== null): ?>
    <!-- Results -->
    <?php if ($kkm): ?>
        <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="bi bi-bar-chart-line me-2 fs-5"></i>
            <div>
                <strong>KKM <?= esc($mapel['nama_mapel']) ?> — <?= esc($kelas['nama_kelas']) ?>:</strong>
                <?= number_format($kkm['nilai_kkm'], 0) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">
                    Siswa Remedial
                    <?php if (!empty($siswa)): ?>
                        <span class="badge bg-danger ms-2"><?= count($siswa) ?> siswa</span>
                    <?php endif; ?>
                </h5>
            </div>

            <?php if (empty($siswa)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-emoji-smile fs-1 text-success d-block mb-2"></i>
                    <h6 class="fw-bold text-success">Tidak ada siswa remedial!</h6>
                    <p class="text-muted">Semua siswa sudah mencapai nilai KKM untuk mata pelajaran dan kelas ini.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center">KKM</th>
                                <th class="text-center">Selisih</th>
                                <th class="text-center">Huruf</th>
                                <th>Tindak Lanjut</th>
                                <th class="text-center">Status Remedial</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($siswa as $s): ?>
                                <tr class="table-danger">
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= esc($s['nis']) ?></strong></td>
                                    <td><?= esc($s['nama_siswa']) ?></td>
                                    <td class="text-center">
                                        <strong
                                            class="text-danger"><?= isset($s['nilai_akhir']) ? number_format($s['nilai_akhir'], 2) : '-' ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?= $kkm ? number_format($kkm['nilai_kkm'], 0) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (isset($s['nilai_akhir']) && $kkm): ?>
                                            <span
                                                class="text-danger fw-bold">-<?= number_format($kkm['nilai_kkm'] - $s['nilai_akhir'], 2) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($s['nilai_huruf']) ? '<strong>' . esc($s['nilai_huruf']) . '</strong>' : '-' ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['tindak_lanjut'])): ?>
                                            <small><?= esc($s['tindak_lanjut']) ?></small>
                                        <?php else: ?>
                                            <small class="text-danger fst-italic">Belum diisi</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $statusRemedial = $s['status_remedial'] ?? 'Belum';
                                        $badgeClass = match ($statusRemedial) {
                                            'Selesai' => 'bg-pastel-success',
                                            'Sedang Proses' => 'bg-pastel-warning',
                                            default => 'bg-pastel-danger',
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($statusRemedial) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 pt-3 border-top">
                    <a href="<?= base_url('guru/nilai-akhir/review?id_kelas=' . $selected_kelas . '&id_mapel=' . $selected_mapel . '&id_tahun_ajaran=' . $selected_tahun) ?>"
                        class="btn btn-outline-primary">
                        <i class="bi bi-pencil-square me-1"></i> Isi atau perbarui tindak lanjut pada halaman pemeriksaan
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
