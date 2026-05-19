<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>" class="active"><i class="bi bi-files me-2"></i> Penilaian
    Agregat</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1">
            <i class="bi bi-files me-2"></i>Penilaian Agregat — Input Nilai
        </h4>
        <p class="text-muted mb-0">
            Kelas: <strong><?= esc($kelas['nama_kelas']) ?></strong> |
            Mapel: <strong><?= esc($mapel['nama_mapel']) ?></strong> |
            TA: <strong><?= esc($tahun_ajaran['tahun_ajaran']) ?> Sem <?= $tahun_ajaran['semester'] ?></strong>
        </p>
    </div>
    <a href="<?= base_url('guru/penilaian-agregat') ?>" class="btn btn-outline-secondary btn-sm">
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
            <form action="<?= base_url('guru/penilaian-agregat/save') ?>" method="post" id="formAgregat">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kelas" value="<?= esc($id_kelas) ?>">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>Nama Siswa</th>
                                <th class="text-center" style="width:120px">Tugas</th>
                                <th class="text-center" style="width:120px">Ulangan</th>
                                <th class="text-center" style="width:90px">Rata-rata</th>
                                <th class="text-center" style="width:120px">UTS</th>
                                <th class="text-center" style="width:120px">UAS</th>
                                <th class="text-center" style="width:110px">Proyeksi</th>
                                <th style="width:260px">Tindak Lanjut</th>
                                <th class="text-center" style="width:120px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($siswa as $s):
                                $nilai = $nilai_siswa_existing[$s['id_siswa']] ?? null;
                                $remedial = $remedial_existing[$s['id_siswa']] ?? null;
                                $rata = ($nilai && $nilai['nilai_tugas'] !== null && $nilai['nilai_ulangan'] !== null)
                                    ? (($nilai['nilai_tugas'] + $nilai['nilai_ulangan']) / 2)
                                    : null;
                                $proyeksi = ($rata !== null && $nilai && $nilai['nilai_uts'] !== null && $nilai['nilai_uas'] !== null)
                                    ? (($rata * 0.40) + ($nilai['nilai_uts'] * 0.30) + ($nilai['nilai_uas'] * 0.30))
                                    : null;
                                $nilaiKkm = isset($kkm['nilai_kkm']) ? (float) $kkm['nilai_kkm'] : 70.0;
                                $isBelowKkm = $proyeksi !== null && $proyeksi < $nilaiKkm;
                                $hasData = ($nilai !== null);
                                ?>
                                <tr id="row-<?= $s['id_siswa'] ?>" class="<?= $isBelowKkm ? 'table-warning' : '' ?>">
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
                                    <!-- Tugas -->
                                    <td>
                                        <input type="number" class="form-control form-control-sm nilai-input"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_tugas]"
                                            value="<?= $nilai ? $nilai['nilai_tugas'] : '' ?>" min="0" max="100" step="0.5"
                                            data-siswa="<?= $s['id_siswa'] ?>" data-type="tugas" placeholder="0-100">
                                    </td>
                                    <!-- Ulangan -->
                                    <td>
                                        <input type="number" class="form-control form-control-sm nilai-input"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_ulangan]"
                                            value="<?= $nilai ? $nilai['nilai_ulangan'] : '' ?>" min="0" max="100" step="0.5"
                                            data-siswa="<?= $s['id_siswa'] ?>" data-type="ulangan" placeholder="0-100">
                                    </td>
                                    <!-- Rata-rata (auto-calculated) -->
                                    <td class="text-center">
                                        <span id="rata-<?= $s['id_siswa'] ?>" class="fw-bold fs-6">
                                            <?= $rata !== null ? number_format($rata, 1) : '—' ?>
                                        </span>
                                    </td>
                                    <!-- UTS -->
                                    <td>
                                        <input type="number" class="form-control form-control-sm nilai-input"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_uts]"
                                            value="<?= $nilai ? $nilai['nilai_uts'] : '' ?>" min="0" max="100" step="0.5"
                                            data-siswa="<?= $s['id_siswa'] ?>" data-type="uts" placeholder="0-100">
                                    </td>
                                    <!-- UAS -->
                                    <td>
                                        <input type="number" class="form-control form-control-sm nilai-input"
                                            name="nilai[<?= $s['id_siswa'] ?>][nilai_uas]"
                                            value="<?= $nilai ? $nilai['nilai_uas'] : '' ?>" min="0" max="100" step="0.5"
                                            data-siswa="<?= $s['id_siswa'] ?>" data-type="uas" placeholder="0-100">
                                    </td>
                                    <td class="text-center">
                                        <span id="proyeksi-<?= $s['id_siswa'] ?>"
                                            class="fw-bold fs-6 <?= $isBelowKkm ? 'text-danger' : 'text-primary' ?>">
                                            <?= $proyeksi !== null ? number_format($proyeksi, 2) : '—' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <textarea
                                            class="form-control form-control-sm tindak-lanjut-input <?= $isBelowKkm ? '' : 'd-none' ?>"
                                            name="nilai[<?= $s['id_siswa'] ?>][tindak_lanjut]"
                                            id="tindak-lanjut-<?= $s['id_siswa'] ?>" rows="2" data-siswa="<?= $s['id_siswa'] ?>"
                                            placeholder="Wajib diisi jika nilai proyeksi di bawah KKM"><?= esc($remedial['tindak_lanjut'] ?? '') ?></textarea>
                                        <small id="kkm-note-<?= $s['id_siswa'] ?>"
                                            class="text-danger <?= $isBelowKkm ? '' : 'd-none' ?>">Nilai proyeksi di bawah KKM
                                            <?= number_format($nilaiKkm, 0) ?>.</small>
                                    </td>
                                    <!-- Status -->
                                    <td class="text-center" id="status-<?= $s['id_siswa'] ?>">
                                        <?php if ($isBelowKkm): ?>
                                            <span class="badge bg-pastel-danger">Perlu Tindak Lanjut</span>
                                        <?php elseif ($hasData): ?>
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

                <div class="alert alert-info small mt-3">
                    <strong>KKM aktif:</strong>
                    <?= isset($kkm['nilai_kkm']) ? number_format((float) $kkm['nilai_kkm'], 0) : '70 (default)' ?>.
                    Rata-Rata Harian = (Tugas + Ulangan) / 2. Nilai Proyeksi = (Rata-Rata Harian × 40%) + (UTS × 30%) + (UAS
                    × 30%).
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                    <a href="<?= base_url('guru/penilaian-agregat') ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 fw-semibold px-4"
                        id="submitAgregat">
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
    const nilaiKkm = <?= isset($kkm['nilai_kkm']) ? (float) $kkm['nilai_kkm'] : 70 ?>;
    const submitButton = document.getElementById('submitAgregat');

    if (submitButton) {

        function parseScore(selector) {
            const element = document.querySelector(selector);
            if (!element || element.value === '') {
                return null;
            }

            const value = parseFloat(element.value);
            return Number.isFinite(value) ? value : null;
        }

        function updateStudentRow(siswaId) {
            const tugas = parseScore(`input[data-siswa="${siswaId}"][data-type="tugas"]`);
            const ulangan = parseScore(`input[data-siswa="${siswaId}"][data-type="ulangan"]`);
            const uts = parseScore(`input[data-siswa="${siswaId}"][data-type="uts"]`);
            const uas = parseScore(`input[data-siswa="${siswaId}"][data-type="uas"]`);

            const rataSpan = document.getElementById(`rata-${siswaId}`);
            const proyeksiSpan = document.getElementById(`proyeksi-${siswaId}`);
            const tindakLanjut = document.getElementById(`tindak-lanjut-${siswaId}`);
            const kkmNote = document.getElementById(`kkm-note-${siswaId}`);
            const statusCell = document.getElementById(`status-${siswaId}`);
            const row = document.getElementById(`row-${siswaId}`);

            const rata = (tugas !== null && ulangan !== null) ? ((tugas + ulangan) / 2) : null;
            const proyeksi = (rata !== null ? rata : 0) * 0.40 + (uts !== null ? uts : 0) * 0.30 + (uas !== null ? uas : 0) * 0.30;
            const hasProjection = rata !== null || uts !== null || uas !== null;
            const belowKkm = hasProjection && proyeksi < nilaiKkm;

            rataSpan.textContent = rata !== null ? rata.toFixed(2) : '—';
            rataSpan.className = `fw-bold fs-6 ${rata !== null ? 'text-success' : 'text-muted'}`;

            proyeksiSpan.textContent = hasProjection ? proyeksi.toFixed(2) : '—';
            proyeksiSpan.className = `fw-bold fs-6 ${belowKkm ? 'text-danger' : 'text-primary'}`;

            tindakLanjut.classList.toggle('d-none', !belowKkm);
            tindakLanjut.required = belowKkm;
            kkmNote.classList.toggle('d-none', !belowKkm);
            row.classList.toggle('table-warning', belowKkm);

            if (belowKkm) {
                statusCell.innerHTML = '<span class="badge bg-pastel-danger">Perlu Tindak Lanjut</span>';
            } else if (hasProjection) {
                statusCell.innerHTML = '<span class="badge bg-pastel-success">Siap Disimpan</span>';
            } else {
                statusCell.innerHTML = '<span class="badge bg-secondary">Kosong</span>';
            }
        }

        function validateForm() {
            let invalid = false;
            document.querySelectorAll('.tindak-lanjut-input').forEach((textarea) => {
                const siswaId = textarea.dataset.siswa;
                const isVisible = !textarea.classList.contains('d-none');
                if (isVisible && textarea.value.trim() === '') {
                    invalid = true;
                    document.getElementById(`row-${siswaId}`).classList.add('table-warning');
                }
            });

            submitButton.disabled = invalid;
        }

        document.querySelectorAll('.nilai-input, .tindak-lanjut-input').forEach((input) => {
            input.addEventListener('input', function () {
                const siswaId = this.dataset.siswa;
                updateStudentRow(siswaId);
                validateForm();
            });
        });

        document.querySelectorAll('.nilai-input').forEach((input) => updateStudentRow(input.dataset.siswa));
        validateForm();
    }
</script>
<?= $this->endSection() ?>