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
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-clipboard-check me-2"></i>Pemeriksaan Nilai Akhir &
            Tindak Lanjut Remedial</h4>
        <p class="text-muted mb-0">
            <?= esc($kelas['nama_kelas']) ?> | <?= esc($mapel['nama_mapel']) ?>
        </p>
    </div>
    <a href="<?= base_url('guru/nilai-akhir') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($kkm): ?>
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bi bi-bar-chart-line me-2 fs-5"></i>
        <div><strong>KKM Mata Pelajaran ini:</strong> <?= number_format($kkm['nilai_kkm'], 0) ?></div>
    </div>
<?php endif; ?>

<?php
$remedialCount = 0;
$tuntasCount = 0;
foreach ($siswa as $s) {
    if (isset($s['status_kelulusan'])) {
        if ($s['status_kelulusan'] === 'Remedial') {
            $remedialCount++;
        } elseif ($s['status_kelulusan'] === 'Tuntas') {
            $tuntasCount++;
        }
    }
}
?>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #77DD77 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-1">Siswa Tuntas</h6>
                <h2 class="mb-0 fw-bold text-success"><?= $tuntasCount ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #FF6961 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-1">Siswa Remedial (Perlu Tindak Lanjut)</h6>
                <h2 class="mb-0 fw-bold text-danger"><?= $remedialCount ?></h2>
            </div>
        </div>
    </div>
</div>

<?php if ($remedialCount > 0): ?>
    <div class="alert alert-warning d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            <strong>Penting!</strong> Ada <strong><?= $remedialCount ?> siswa</strong> yang perlu tindak lanjut remedial.
            Silakan isi kolom "Tindak Lanjut" di bawah untuk setiap siswa yang remedial sebelum rapor difinalkan.
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4">Daftar Nilai Akhir Siswa</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center">Huruf</th>
                        <th class="text-center">Status</th>
                        <th>Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($siswa)): ?>
                        <?php $no = 1;
                        foreach ($siswa as $s): ?>
                            <tr
                                class="<?= isset($s['status_kelulusan']) && $s['status_kelulusan'] === 'Remedial' ? 'table-danger' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td><strong><?= esc($s['nis']) ?></strong></td>
                                <td><?= esc($s['nama_siswa']) ?></td>
                                <td class="text-center">
                                    <?php if (isset($s['nilai_akhir'])): ?>
                                        <strong
                                            class="<?= $s['status_kelulusan'] === 'Remedial' ? 'text-danger' : 'text-success' ?>">
                                            <?= number_format($s['nilai_akhir'], 2) ?>
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= isset($s['nilai_huruf']) ? '<strong>' . esc($s['nilai_huruf']) . '</strong>' : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($s['status_kelulusan'])): ?>
                                        <?php if ($s['status_kelulusan'] === 'Tuntas'): ?>
                                            <span class="badge bg-pastel-success"><i class="bi bi-check-circle me-1"></i>Tuntas</span>
                                        <?php else: ?>
                                            <span class="badge bg-pastel-danger"><i
                                                    class="bi bi-exclamation-triangle me-1"></i>Remedial</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($s['status_kelulusan']) && $s['status_kelulusan'] === 'Remedial'): ?>
                                        <small class="text-muted">
                                            <?= isset($s['tindak_lanjut']) && !empty($s['tindak_lanjut'])
                                                ? esc($s['tindak_lanjut'])
                                                : '<span class="text-danger fst-italic">Belum diisi</span>' ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Tidak ada data nilai akhir. Pastikan Anda sudah menghitung nilai akhir terlebih dahulu.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($remedialCount > 0): ?>
    <div class="card border-0 shadow-sm rounded-3 mt-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4 text-danger"><i class="bi bi-pencil-square me-2"></i>Isi Tindak Lanjut Remedial</h5>
            <p class="text-muted">Form ini digunakan untuk mengisi tindak lanjut remedial bagi siswa yang nilainya di bawah
                KKM.</p>

            <form id="formRemedial" action="<?= base_url('guru/nilai-akhir/save-remedial') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_tahun_ajaran" value="<?= esc($id_tahun_ajaran) ?>">
                <input type="hidden" name="id_kelas" value="<?= esc($id_kelas) ?>">
                <input type="hidden" name="id_mapel" value="<?= esc($id_mapel) ?>">
                <?php foreach ($siswa as $s): ?>
                    <?php // Pasca merge: id_nilai (PK tabel `nilai`) menggantikan id_remedial. ?>
                    <?php if (isset($s['status_kelulusan']) && $s['status_kelulusan'] === 'Remedial' && !empty($s['id_nilai'])): ?>
                        <div class="mb-3 p-3 border rounded bg-light">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-fill me-1"></i> <?= esc($s['nama_siswa']) ?>
                                <span class="badge bg-danger ms-2">Nilai: <?= number_format($s['nilai_akhir'], 2) ?></span>
                            </label>
                            <textarea name="remedial[<?= $s['id_nilai'] ?>][tindak_lanjut]"
                                class="form-control remedial-textarea" rows="2"
                                placeholder="Contoh: Mengerjakan ulang soal Bab 3, Latihan tambahan di rumah, Bimbingan khusus setiap Rabu..."
                                required><?= isset($s['tindak_lanjut']) ? esc($s['tindak_lanjut']) : '' ?></textarea>
                            <input type="hidden" name="remedial[<?= $s['id_nilai'] ?>][id_nilai]"
                                value="<?= $s['id_nilai'] ?>">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="mt-4">
                    <button type="submit" id="btnSimpanRemedial"
                        class="btn btn-primary bg-pastel-primary border-0 px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Semua Tindak Lanjut
                    </button>
                    <small id="warnRemedial" class="text-danger ms-3 d-none">
                        <i class="bi bi-exclamation-circle me-1"></i>Isi semua kolom tindak lanjut sebelum menyimpan.
                    </small>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php
$borderlineList = array_filter($siswa ?? [], static fn($s) => (int) ($s['flag_borderline_75'] ?? 0) === 1);
?>
<?php if (!empty($borderlineList)): ?>
    <div class="card border-0 shadow-sm rounded-3 mt-4" style="border-left: 4px solid #f0ad4e !important;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3 text-warning">
                <i class="bi bi-sticky me-2"></i>Catatan untuk Siswa dengan Nilai Akhir 75 (Borderline)
            </h5>
            <p class="text-muted small mb-4">
                Nilai akhir <strong>tepat 75</strong> umumnya hasil katrol pasca-remedial. Wajib diisi catatan
                (minimal 10 karakter) menjelaskan tindak lanjut atau konteks nilai borderline ini. Catatan ini
                <strong>tidak tampil</strong> di rapor cetak, hanya di dashboard orang tua sebagai "Catatan dari guru".
            </p>

            <form action="<?= base_url('guru/nilai-akhir/save-catatan-borderline') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_tahun_ajaran" value="<?= esc($id_tahun_ajaran) ?>">
                <input type="hidden" name="id_kelas" value="<?= esc($id_kelas) ?>">
                <input type="hidden" name="id_mapel" value="<?= esc($id_mapel) ?>">

                <?php foreach ($borderlineList as $b): ?>
                    <div class="mb-3 p-3 border rounded bg-light">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-person-fill me-1"></i> <?= esc($b['nama_siswa']) ?>
                            <span class="badge bg-warning text-dark ms-2">Nilai: 75</span>
                        </label>
                        <textarea name="catatan[<?= (int) ($b['id_nilai'] ?? 0) ?>]" rows="2"
                            class="form-control"
                            placeholder="Mis. Siswa berhasil setelah remedial Bab 3; perlu pendampingan operasi hitung bilangan cacah."
                            minlength="10" required><?= esc($b['catatan_remedial'] ?? '') ?></textarea>
                        <small class="text-muted">Minimal 10 karakter. Catatan hanya tampil online untuk orang tua.</small>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-warning fw-semibold">
                    <i class="bi bi-save me-1"></i> Simpan Catatan Borderline
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="alert alert-secondary mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Catatan:</strong> Tindak lanjut remedial wajib diisi untuk semua siswa remedial sebelum rapor dapat
    difinalisasi oleh admin atau wali kelas.
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const form = document.getElementById('formRemedial');
        if (!form) return;

        const textareas = form.querySelectorAll('.remedial-textarea');
        const btnSimpan = document.getElementById('btnSimpanRemedial');
        const warnEl = document.getElementById('warnRemedial');

        function checkAll() {
            let allFilled = true;
            textareas.forEach(function (ta) {
                if (ta.value.trim() === '') allFilled = false;
            });
            btnSimpan.disabled = !allFilled;
            if (!allFilled) {
                warnEl.classList.remove('d-none');
            } else {
                warnEl.classList.add('d-none');
            }
        }

        // Run on page load so button starts disabled when fields are empty
        checkAll();

        textareas.forEach(function (ta) {
            ta.addEventListener('input', checkAll);
        });
    })();
</script>
<?= $this->endSection() ?>