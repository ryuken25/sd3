<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/nilai-harian') ?>"><i class="bi bi-journal-text me-2"></i> Nilai Harian</a>
<a href="<?= base_url('guru/nilai-ujian') ?>" class="active"><i class="bi bi-file-earmark-text me-2"></i> Nilai
    UTS/UAS</a>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1">
            <i class="bi bi-file-earmark-text me-2"></i>Input Nilai UTS / UAS — By Class
        </h4>
        <p class="text-muted mb-0">
            Kelas: <strong><?= esc($kelas['nama_kelas']) ?></strong> |
            Mapel: <strong><?= esc($mapel['nama_mapel']) ?></strong> |
            TA: <strong><?= esc($tahun_ajaran['tahun_ajaran']) ?> Sem <?= $tahun_ajaran['semester'] ?></strong>
        </p>
    </div>
    <a href="<?= base_url('guru/nilai-ujian') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($siswa)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                Tidak ada siswa aktif di kelas ini.
            </div>
        <?php else: ?>
            <form action="<?= base_url('guru/nilai-ujian/save') ?>" method="post" id="formNilaiUjian">
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>Nama Siswa</th>
                                <th style="width:160px" class="text-center">Nilai UTS (30%)</th>
                                <th style="width:160px" class="text-center">Nilai UAS (30%)</th>
                                <th style="width:80px" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($siswa as $s):
                                $nu = $nilai_existing[$s['id_siswa']] ?? null;
                                ?>
                                <tr id="row-<?= $s['id_siswa'] ?>">
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= esc($s['nama_siswa']) ?></strong>
                                        <br><small class="text-muted"><?= esc($s['nis']) ?></small>
                                        <input type="hidden" name="nilai[<?= $s['id_siswa'] ?>][id_siswa]"
                                            value="<?= $s['id_siswa'] ?>">
                                        <input type="hidden" name="nilai[<?= $s['id_siswa'] ?>][id_mapel]"
                                            value="<?= $id_mapel ?>">
                                        <input type="hidden" name="nilai[<?= $s['id_siswa'] ?>][id_tahun_ajaran]"
                                            value="<?= $id_tahun_ajaran ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-center"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_uts]"
                                            value="<?= $nu && $nu['nilai_uts'] !== null ? $nu['nilai_uts'] : '' ?>" min="0"
                                            max="100" step="0.5" placeholder="0-100">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-center"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_uas]"
                                            value="<?= $nu && $nu['nilai_uas'] !== null ? $nu['nilai_uas'] : '' ?>" min="0"
                                            max="100" step="0.5" placeholder="0-100">
                                    </td>
                                    <td class="text-center">
                                        <?php if ($nu): ?>
                                            <span class="badge bg-pastel-success">Tersimpan</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Kosong</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                    <a href="<?= base_url('guru/nilai-ujian') ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-warning border-0 fw-semibold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Semua Nilai UTS/UAS
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4 p-3 bg-light rounded-3 border">
    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2 text-info"></i>Informasi</h6>
    <ul class="mb-0 small text-muted">
        <li>Nilai UTS dan UAS masing-masing berkontribusi <strong>30%</strong> terhadap Nilai Akhir siswa.</li>
        <li>Nilai harian (Tugas &amp; Ulangan) berkontribusi <strong>40%</strong>.</li>
        <li>Pastikan semua nilai sudah diisi sebelum menghitung nilai akhir melalui menu <strong>Nilai Akhir &amp;
                Remedial</strong>.</li>
    </ul>
</div>
<?= $this->endSection() ?>