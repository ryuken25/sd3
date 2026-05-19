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
        <h4 class="fw-bold text-pastel-primary mb-1">
            <i class="bi bi-table me-2"></i>Input Nilai Harian — By Class
        </h4>
        <p class="text-muted mb-0">
            Kelas: <strong><?= esc($kelas['nama_kelas']) ?></strong> |
            Mapel: <strong><?= esc($mapel['nama_mapel']) ?></strong> |
            TA: <strong><?= esc($tahun_ajaran['tahun_ajaran']) ?> Sem <?= $tahun_ajaran['semester'] ?></strong>
        </p>
    </div>
    <a href="<?= base_url('guru/nilai-harian') ?>" class="btn btn-outline-secondary btn-sm">
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
            <form action="<?= base_url('guru/nilai-harian/save') ?>" method="post" id="formNilai">
                <?= csrf_field() ?>
                <input type="hidden" name="mode" value="byClass">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>Nama Siswa</th>
                                <th style="width:150px">Nilai Tugas</th>
                                <th style="width:160px">Nilai Ulangan Harian</th>
                                <th style="width:110px">Rata-rata</th>
                                <th style="width:80px">Status</th>
                            </tr>
                        </thead>
                        <tbody id="nilaiTable">
                            <?php $no = 1;
                            foreach ($siswa as $s):
                                $nh = $nilai_existing[$s['id_siswa']] ?? null;
                                $rata = $nh ? (($nh['nilai_tugas'] + $nh['nilai_ulangan']) / 2) : null;
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
                                        <input type="number" class="form-control form-control-sm nilai-input"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_tugas]"
                                            value="<?= $nh ? $nh['nilai_tugas'] : '' ?>" min="0" max="100" step="0.5"
                                            data-siswa="<?= $s['id_siswa'] ?>" data-type="tugas" placeholder="0-100">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm nilai-input"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_ulangan]"
                                            value="<?= $nh ? $nh['nilai_ulangan'] : '' ?>" min="0" max="100" step="0.5"
                                            data-siswa="<?= $s['id_siswa'] ?>" data-type="ulangan" placeholder="0-100">
                                    </td>
                                    <td>
                                        <span id="rata-<?= $s['id_siswa'] ?>" class="fw-bold fs-6">
                                            <?= $rata !== null ? number_format($rata, 1) : '—' ?>
                                        </span>
                                    </td>
                                    <td id="status-<?= $s['id_siswa'] ?>">
                                        <?php if ($nh): ?>
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
                    <a href="<?= base_url('guru/nilai-harian') ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 fw-semibold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Semua Nilai
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-calculate average on input
    document.querySelectorAll('.nilai-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const siswaId = this.dataset.siswa;
            const tugasInput = document.querySelector(`input[data-siswa="${siswaId}"][data-type="tugas"]`);
            const ulanganInput = document.querySelector(`input[data-siswa="${siswaId}"][data-type="ulangan"]`);
            const rataSpan = document.getElementById(`rata-${siswaId}`);

            const tugas = parseFloat(tugasInput.value) || null;
            const ulangan = parseFloat(ulanganInput.value) || null;

            if (tugas !== null && ulangan !== null) {
                const rata = ((tugas + ulangan) / 2).toFixed(1);
                rataSpan.textContent = rata;
                rataSpan.className = 'fw-bold fs-6 text-success';
            } else {
                rataSpan.textContent = '—';
                rataSpan.className = 'fw-bold fs-6 text-muted';
            }
        });
    });
</script>
<?= $this->endSection() ?>