<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<?php $role = $role ?? session()->get('role'); ?>
<div class="sidebar-heading">Menu Utama</div>
<?php if ($role === 'admin'): ?>
    <a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a href="<?= base_url('admin/rapor') ?>"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
<?php elseif ($role === 'guru'): ?>
    <a href="<?= base_url('guru/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<?php else: ?>
    <a href="<?= base_url('orangtua/dashboard') ?>"><i class="bi bi-house me-2"></i> Dashboard</a>
<?php endif; ?>
<div class="sidebar-heading mt-3">Bantuan</div>
<a href="<?= base_url('help/panduan-rapor') ?>" class="active"><i class="bi bi-question-circle me-2"></i> Panduan
    Penggunaan</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-4">
    <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-question-circle me-2"></i>Panduan Penggunaan Rapor</h4>
    <p class="text-muted mb-0">Penjelasan lengkap cara mengisi rapor digital SDN 3 Mekarsari — untuk guru &amp; admin.</p>
</div>

<!-- Daftar isi -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-2"><i class="bi bi-list-ul me-2"></i>Daftar Isi</h6>
        <div class="row small">
            <div class="col-md-6">
                <a href="#cp" class="d-block py-1">1. Capaian Kompetensi</a>
                <a href="#kokurikuler" class="d-block py-1">2. Kokurikuler (P5)</a>
                <a href="#ekskul" class="d-block py-1">3. Ekstrakurikuler</a>
            </div>
            <div class="col-md-6">
                <a href="#absensi" class="d-block py-1">4. Ketidakhadiran</a>
                <a href="#catatan" class="d-block py-1">5. Catatan Wali Kelas</a>
                <a href="#glosarium" class="d-block py-1">6. Glosarium &amp; FAQ</a>
            </div>
        </div>
    </div>
</div>

<!-- 1. Capaian Kompetensi -->
<div class="card border-0 shadow-sm mb-4" id="cp">
    <div class="card-header bg-white fw-bold"><i class="bi bi-1-circle me-2"></i>Capaian Kompetensi</div>
    <div class="card-body">
        <p>Capaian Kompetensi (CP) adalah deskripsi penguasaan siswa per mata pelajaran yang otomatis disusun jadi
            narasi rapor.</p>
        <ol>
            <li>Buka menu <strong>Capaian Kompetensi</strong>, pilih siswa dan mata pelajaran.</li>
            <li>Untuk tiap Capaian Pembelajaran, pilih salah satu status:
                <ul>
                    <li><strong>Tercapai Sangat Baik</strong> — masuk kalimat <em>"Mencapai Kompetensi dengan sangat
                            baik dalam hal…"</em></li>
                    <li><strong>Perlu Peningkatan</strong> — masuk kalimat <em>"Perlu peningkatan dalam hal…"</em></li>
                    <li><strong>Belum Dinilai</strong> — default, tidak muncul di rapor.</li>
                </ul>
            </li>
            <li>Lihat <strong>Preview Narasi</strong> di bawah form — narasi tersusun otomatis.</li>
            <li>Bila ada CP yang belum terdaftar, klik <strong>+ Tambah CP Custom</strong>.</li>
            <li>Klik <strong>Simpan</strong>.</li>
        </ol>
        <div class="alert alert-info small mb-0"><i class="bi bi-lightbulb me-1"></i>Anda tidak perlu mengetik narasi
            manual — sistem menyusunnya dari status CP yang dipilih.</div>
    </div>
</div>

<!-- 2. Kokurikuler -->
<div class="card border-0 shadow-sm mb-4" id="kokurikuler">
    <div class="card-header bg-white fw-bold"><i class="bi bi-2-circle me-2"></i>Kokurikuler (Projek P5)</div>
    <div class="card-body">
        <p>Kokurikuler menilai 7 dimensi Profil Pelajar Pancasila melalui tema projek per kelas.</p>
        <ol>
            <li>Tema kokurikuler terpilih otomatis sesuai kelas (mis. Kelas 3 = <em>Gelanggang Ceria Nusantara</em>,
                Kelas 6 = <em>Celengan Impian</em>).</li>
            <li>Untuk tiap dari 7 dimensi, isi <strong>Subdimensi</strong> (aspek yang diukur) dan <strong>Level</strong>
                (Berkembang / Cakap / Mahir / Sangat Mahir).</li>
            <li>Sistem menyusun narasi kokurikuler otomatis untuk rapor.</li>
        </ol>
    </div>
</div>

<!-- 3. Ekstrakurikuler -->
<div class="card border-0 shadow-sm mb-4" id="ekskul">
    <div class="card-header bg-white fw-bold"><i class="bi bi-3-circle me-2"></i>Ekstrakurikuler</div>
    <div class="card-body">
        <ol>
            <li>Pilih ekstrakurikuler yang diikuti siswa (Pramuka, Majejahitan, Yoga, Menari).</li>
            <li>Isi keterangan capaian tiap ekskul — sistem sudah memberi teks default, boleh diedit.</li>
            <li>Boleh dikosongkan bila siswa tidak mengikuti ekskul tertentu.</li>
        </ol>
    </div>
</div>

<!-- 4. Ketidakhadiran -->
<div class="card border-0 shadow-sm mb-4" id="absensi">
    <div class="card-header bg-white fw-bold"><i class="bi bi-4-circle me-2"></i>Ketidakhadiran</div>
    <div class="card-body">
        <p>Isi jumlah hari per kategori (default 0 bila tidak ada):</p>
        <ul>
            <li><strong>Sakit</strong> — tidak hadir karena sakit, dengan keterangan.</li>
            <li><strong>Izin</strong> — tidak hadir karena keperluan lain (mis. acara keluarga).</li>
            <li><strong>Tanpa Keterangan</strong> — alpha, tidak ada keterangan.</li>
        </ul>
    </div>
</div>

<!-- 5. Catatan Wali Kelas -->
<div class="card border-0 shadow-sm mb-4" id="catatan">
    <div class="card-header bg-white fw-bold"><i class="bi bi-5-circle me-2"></i>Catatan Wali Kelas</div>
    <div class="card-body">
        <ol>
            <li>Pilih template catatan (opsional) — isi otomatis termuat ke kotak teks.</li>
            <li>Placeholder <code>{nama_panggilan}</code> diganti otomatis dengan nama panggilan siswa.</li>
            <li>Edit teks sesuai kebutuhan, atau tulis dari nol.</li>
            <li>Catatan wajib diisi (minimal 10 karakter).</li>
        </ol>
    </div>
</div>

<!-- 6. Glosarium & FAQ -->
<div class="card border-0 shadow-sm mb-4" id="glosarium">
    <div class="card-header bg-white fw-bold"><i class="bi bi-6-circle me-2"></i>Glosarium &amp; FAQ</div>
    <div class="card-body">
        <h6 class="fw-bold">Glosarium</h6>
        <dl class="row small">
            <dt class="col-sm-3">Fase</dt>
            <dd class="col-sm-9">Tahap kurikulum: Fase A (Kelas 1–2), Fase B (Kelas 3–4), Fase C (Kelas 5–6).</dd>
            <dt class="col-sm-3">KKM</dt>
            <dd class="col-sm-9">Kriteria Ketuntasan Minimal — nilai minimum agar siswa dinyatakan tuntas.</dd>
            <dt class="col-sm-3">P5</dt>
            <dd class="col-sm-9">Projek Penguatan Profil Pelajar Pancasila — kegiatan kokurikuler 7 dimensi.</dd>
            <dt class="col-sm-3">CP</dt>
            <dd class="col-sm-9">Capaian Pembelajaran — kompetensi yang diharapkan dikuasai siswa.</dd>
        </dl>
        <hr>
        <h6 class="fw-bold">FAQ</h6>
        <p class="small mb-1"><strong>T: Kenapa nilai 75 minta catatan?</strong><br>
            J: Nilai 75 adalah hasil remedial standar. Catatan menjelaskan ke orang tua bahwa nilai borderline ini hasil
            remedial — catatan ini hanya tampil di e-rapor online, tidak di PDF cetak.</p>
        <p class="small mb-1"><strong>T: Kapan orang tua bisa melihat rapor?</strong><br>
            J: Setelah admin/wali kelas memfinalisasi rapor siswa tersebut.</p>
        <p class="small mb-0"><strong>T: Apakah narasi rapor diketik manual?</strong><br>
            J: Tidak. Narasi Capaian Kompetensi &amp; Kokurikuler disusun otomatis dari data yang diisi.</p>
    </div>
</div>
<?= $this->endSection() ?>
