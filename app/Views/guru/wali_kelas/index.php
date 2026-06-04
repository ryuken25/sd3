<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>" class="active"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-people-fill me-2"></i>Anak Wali Kelas</h4>
        <p class="text-muted mb-0">
            Pilih siswa untuk mengisi <strong>Catatan, Ekstrakurikuler, Kokurikuler P5, dan Ketidakhadiran</strong>.
        </p>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (!empty($message ?? '')): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i><?= esc($message) ?></div>
<?php endif; ?>

<?php if ($kelas): ?>
    <div class="alert alert-info d-flex align-items-center">
        <i class="bi bi-info-circle me-2 fs-5"></i>
        <div>
            Anda wali kelas dari <strong><?= esc($kelas['nama_kelas']) ?></strong>
            (Tingkat <?= esc($kelas['tingkat']) ?>). TA aktif:
            <strong><?= esc($ta['tahun_ajaran']) ?> Sem. <?= esc($ta['semester']) ?></strong>.
            Total siswa: <strong><?= count($siswa) ?></strong>.
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        Akun Anda tidak terdaftar sebagai wali kelas di tabel <code>kelas</code>. Hubungi admin untuk assignment.
    </div>
<?php endif; ?>

<?php if (!empty($siswa)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Kelamin</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($siswa as $s): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= esc($s['nis']) ?></strong></td>
                                <td><?= esc($s['nama_siswa']) ?></td>
                                <td><?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('guru/wali-kelas/siswa/' . $s['id_siswa']) ?>"
                                       class="btn btn-sm btn-primary bg-pastel-primary border-0">
                                        <i class="bi bi-pencil-square me-1"></i> Isi Rapor
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
