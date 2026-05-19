<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/nilai-harian') ?>" class="active"><i class="bi bi-journal-text me-2"></i> Nilai Harian</a>
<a href="<?= base_url('guru/nilai-ujian') ?>"><i class="bi bi-file-earmark-text me-2"></i> Nilai UTS/UAS</a>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-person-lines-fill me-2"></i>Input Nilai Harian — By
            Student</h4>
        <p class="text-muted mb-0">Siswa: <strong><?= esc($siswa['nama_siswa']) ?></strong> | NIS:
            <?= esc($siswa['nis']) ?></p>
    </div>
    <a href="<?= base_url('guru/nilai-harian') ?>" class="btn btn-outline-secondary btn-sm"><i
            class="bi bi-arrow-left me-1"></i> Kembali</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="<?= base_url('guru/nilai-harian/save') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="mode" value="byStudent">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelompok</th>
                            <th style="width:150px">Nilai Tugas</th>
                            <th style="width:160px">Nilai Ulangan</th>
                            <th style="width:110px">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($mapel as $m):
                            $nh = $nilai_existing[$m['id_mapel']] ?? null;
                            $rata = $nh ? number_format(($nh['nilai_tugas'] + $nh['nilai_ulangan']) / 2, 1) : null;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= esc($m['nama_mapel']) ?></strong>
                                    <input type="hidden" name="nilai[<?= $m['id_mapel'] ?>][id_siswa]"
                                        value="<?= $id_siswa ?>">
                                    <input type="hidden" name="nilai[<?= $m['id_mapel'] ?>][id_mapel]"
                                        value="<?= $m['id_mapel'] ?>">
                                    <input type="hidden" name="nilai[<?= $m['id_mapel'] ?>][id_tahun_ajaran]"
                                        value="<?= $id_tahun_ajaran ?>">
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= esc($m['kelompok']) ?></span></td>
                                <td>
                                    <input type="number" class="form-control form-control-sm nilai-input"
                                        name="nilai[<?= $m['id_mapel'] ?>][nilai_tugas]"
                                        value="<?= $nh ? $nh['nilai_tugas'] : '' ?>" min="0" max="100" step="0.5"
                                        data-mapel="<?= $m['id_mapel'] ?>" data-type="tugas" placeholder="0-100">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm nilai-input"
                                        name="nilai[<?= $m['id_mapel'] ?>][nilai_ulangan]"
                                        value="<?= $nh ? $nh['nilai_ulangan'] : '' ?>" min="0" max="100" step="0.5"
                                        data-mapel="<?= $m['id_mapel'] ?>" data-type="ulangan" placeholder="0-100">
                                </td>
                                <td><span id="rata-<?= $m['id_mapel'] ?>" class="fw-bold"><?= $rata ?? '—' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                <a href="<?= base_url('guru/nilai-harian') ?>" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-success border-0 fw-semibold px-4">
                    <i class="bi bi-save me-1"></i> Simpan Semua
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.querySelectorAll('.nilai-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const mapelId = this.dataset.mapel;
            const tugasInput = document.querySelector(`input[data-mapel="${mapelId}"][data-type="tugas"]`);
            const ulanganInput = document.querySelector(`input[data-mapel="${mapelId}"][data-type="ulangan"]`);
            const rataSpan = document.getElementById(`rata-${mapelId}`);
            const tugas = parseFloat(tugasInput.value) || null;
            const ulangan = parseFloat(ulanganInput.value) || null;
            if (tugas !== null && ulangan !== null) {
                rataSpan.textContent = ((tugas + ulangan) / 2).toFixed(1);
                rataSpan.className = 'fw-bold text-success';
            } else {
                rataSpan.textContent = '—';
                rataSpan.className = 'fw-bold text-muted';
            }
        });
    });
</script>
<?= $this->endSection() ?>