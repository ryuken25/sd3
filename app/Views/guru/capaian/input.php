<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/capaian-kompetensi') ?>" class="active"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-bookmark-check me-2"></i>Input Capaian Kompetensi</h4>
        <p class="text-muted mb-0 small">
            Kelas: <strong><?= esc($kelas['nama_kelas']) ?></strong> |
            Mapel: <strong><?= esc($mapel['nama_mapel']) ?></strong> |
            Fase: <strong><?= esc($fase) ?></strong> |
            TA: <strong><?= esc($tahun_ajaran['tahun_ajaran']) ?> Sem. <?= esc($tahun_ajaran['semester']) ?></strong>
        </p>
    </div>
    <a href="<?= base_url('guru/capaian-kompetensi') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (empty($master_cp)): ?>
    <div class="alert alert-warning">
        Belum ada master CP untuk mapel <strong><?= esc($mapel['nama_mapel']) ?></strong>, Fase <?= esc($fase) ?>,
        Semester <?= esc($tahun_ajaran['semester']) ?>. Hubungi admin untuk seed atau gunakan CP custom.
    </div>
<?php endif; ?>

<form action="<?= base_url('guru/capaian-kompetensi/save') ?>" method="post" id="formCP">
    <?= csrf_field() ?>
    <input type="hidden" name="id_tahun_ajaran" value="<?= esc($id_tahun_ajaran) ?>">
    <input type="hidden" name="id_kelas" value="<?= esc($id_kelas) ?>">
    <input type="hidden" name="id_mapel" value="<?= esc($id_mapel) ?>">

    <?php foreach ($per_siswa as $idSiswa => $ps): ?>
        <?php
        $siswa = $ps['siswa'];
        $idNa  = (int) ($ps['id_nilai_akhir'] ?? 0);
        $byMaster = $ps['by_master'];
        $custom = $ps['custom'];
        ?>
        <div class="card border-0 shadow-sm mb-3 cp-card" data-siswa="<?= $idSiswa ?>">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-0 fw-bold"><?= esc($siswa['nama_siswa']) ?></h5>
                        <small class="text-muted">NIS: <?= esc($siswa['nis']) ?> | Nilai Akhir:
                            <strong><?= $ps['nilai_akhir'] !== null ? number_format($ps['nilai_akhir'], 2) : '—' ?></strong>
                        </small>
                    </div>
                </div>

                <?php if ($idNa === 0): ?>
                    <div class="alert alert-warning small mb-0">
                        Siswa ini belum punya <code>nilai_akhir</code>. Hitung nilai akhir dulu di menu Nilai Akhir.
                    </div>
                <?php else: ?>
                    <p class="text-muted small">Pilih status untuk setiap Capaian Pembelajaran:</p>

                    <?php foreach ($master_cp as $cp): ?>
                        <?php $existing = $byMaster[(int) $cp['id_master_cp']] ?? null;
                              $sel = $existing['status'] ?? 'belum'; ?>
                        <div class="p-3 border rounded mb-2 cp-row" data-deskripsi="<?= esc($cp['deskripsi']) ?>">
                            <div class="small mb-2"><?= esc($cp['deskripsi']) ?></div>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input cp-status" type="radio"
                                        name="cp[<?= $idNa ?>][master][<?= $cp['id_master_cp'] ?>]" value="belum"
                                        id="cp-<?= $idNa ?>-<?= $cp['id_master_cp'] ?>-b" <?= $sel === 'belum' ? 'checked' : '' ?>>
                                    <label class="form-check-label small text-muted" for="cp-<?= $idNa ?>-<?= $cp['id_master_cp'] ?>-b">Belum Dinilai</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input cp-status" type="radio"
                                        name="cp[<?= $idNa ?>][master][<?= $cp['id_master_cp'] ?>]" value="tercapai_sangat_baik"
                                        id="cp-<?= $idNa ?>-<?= $cp['id_master_cp'] ?>-t" <?= $sel === 'tercapai_sangat_baik' ? 'checked' : '' ?>>
                                    <label class="form-check-label small text-success" for="cp-<?= $idNa ?>-<?= $cp['id_master_cp'] ?>-t">Tercapai Sangat Baik</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input cp-status" type="radio"
                                        name="cp[<?= $idNa ?>][master][<?= $cp['id_master_cp'] ?>]" value="perlu_peningkatan"
                                        id="cp-<?= $idNa ?>-<?= $cp['id_master_cp'] ?>-p" <?= $sel === 'perlu_peningkatan' ? 'checked' : '' ?>>
                                    <label class="form-check-label small text-warning" for="cp-<?= $idNa ?>-<?= $cp['id_master_cp'] ?>-p">Perlu Peningkatan</label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Custom CP -->
                    <div class="cp-custom-list mt-3">
                        <?php foreach ($custom as $i => $c): ?>
                            <div class="p-3 border rounded mb-2 cp-row cp-custom-row">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control form-control-sm cp-custom-desc"
                                            name="cp[<?= $idNa ?>][custom][<?= $i ?>][deskripsi]"
                                            value="<?= esc($c['deskripsi_custom']) ?>" placeholder="Deskripsi CP custom">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm cp-status"
                                            name="cp[<?= $idNa ?>][custom][<?= $i ?>][status]">
                                            <option value="tercapai_sangat_baik" <?= $c['status'] === 'tercapai_sangat_baik' ? 'selected' : '' ?>>Tercapai SB</option>
                                            <option value="perlu_peningkatan" <?= $c['status'] === 'perlu_peningkatan' ? 'selected' : '' ?>>Perlu Peningkatan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-cp"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-cp" data-siswa="<?= $idSiswa ?>" data-id-na="<?= $idNa ?>">
                        <i class="bi bi-plus-circle me-1"></i> Tambah CP Custom
                    </button>

                    <!-- Preview narasi -->
                    <div class="mt-3 p-3 bg-light rounded small narasi-preview">
                        <strong>Preview Narasi:</strong>
                        <div class="narasi-output text-muted fst-italic">—</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary bg-pastel-primary border-0 fw-semibold px-4">
            <i class="bi bi-save me-1"></i> Simpan Semua CP
        </button>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    function updateNarasi(card) {
        const tercapai = [];
        const perlu = [];
        card.querySelectorAll('.cp-row').forEach(function (row) {
            const isCustom = row.classList.contains('cp-custom-row');
            let deskripsi, status;
            if (isCustom) {
                deskripsi = (row.querySelector('.cp-custom-desc')?.value || '').trim();
                status    = row.querySelector('select.cp-status')?.value;
            } else {
                deskripsi = row.dataset.deskripsi || '';
                const chk = row.querySelector('input[type="radio"]:checked');
                status = chk ? chk.value : 'belum';
            }
            if (!deskripsi) return;
            if (status === 'tercapai_sangat_baik') tercapai.push(deskripsi);
            else if (status === 'perlu_peningkatan') perlu.push(deskripsi);
        });
        let parts = [];
        if (tercapai.length) parts.push('Mencapai Kompetensi dengan sangat baik dalam hal ' + tercapai.join(', ') + '.');
        if (perlu.length)    parts.push('Perlu peningkatan dalam hal ' + perlu.join(', ') + '.');
        card.querySelector('.narasi-output').textContent = parts.length ? parts.join(' ') : '—';
    }

    document.querySelectorAll('.cp-card').forEach(function (card) {
        card.addEventListener('change', function () { updateNarasi(card); });
        card.addEventListener('input', function () { updateNarasi(card); });
        updateNarasi(card);
    });

    document.querySelectorAll('.btn-add-cp').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const card = btn.closest('.cp-card');
            const list = card.querySelector('.cp-custom-list');
            const idNa = btn.dataset.idNa;
            const idx  = list.children.length + 1000; // unique
            const html = `
                <div class="p-3 border rounded mb-2 cp-row cp-custom-row">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8">
                            <input type="text" class="form-control form-control-sm cp-custom-desc"
                                name="cp[${idNa}][custom][${idx}][deskripsi]" placeholder="Deskripsi CP custom">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm cp-status"
                                name="cp[${idNa}][custom][${idx}][status]">
                                <option value="tercapai_sangat_baik">Tercapai SB</option>
                                <option value="perlu_peningkatan">Perlu Peningkatan</option>
                            </select>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-cp"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>`;
            list.insertAdjacentHTML('beforeend', html);
            updateNarasi(card);
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-cp')) {
            const row = e.target.closest('.cp-row');
            const card = row.closest('.cp-card');
            row.remove();
            updateNarasi(card);
        }
    });
})();
</script>
<?= $this->endSection() ?>
