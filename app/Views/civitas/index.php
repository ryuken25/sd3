<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: var(--pastel-text);">Civitas Akademika</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none" style="color: #d97706;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Civitas</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4 p-4">
            <h4 class="mb-3 fw-bold" style="color: var(--pastel-text);">Daftar Guru Pengajar</h4>
            <p class="text-muted">Informasi terkait guru kelas dan mata pelajaran dapat dilihat di bawah ini.</p>
            
            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead style="background-color: var(--pastel-secondary); color: var(--pastel-text);">
                        <tr>
                            <th scope="col" class="py-3 border-0 rounded-start">NIP / NUPTK</th>
                            <th scope="col" class="py-3 border-0">Nama Guru</th>
                            <th scope="col" class="py-3 border-0">Jabatan / Guru Kelas</th>
                            <th scope="col" class="py-3 border-0">Status</th>
                            <th scope="col" class="py-3 border-0 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>198005152005012001</td>
                            <td class="fw-bold">Ni Wayan Kasrinayanti, S.Pd.</td>
                            <td>Guru Kelas 5A</td>
                            <td><span class="badge bg-success rounded-pill">Aktif</span></td>
                            <td><button class="btn btn-sm btn-pastel"><i class="fa-solid fa-envelope"></i> Hubungi</button></td>
                        </tr>
                        <tr>
                            <td>198508202010011002</td>
                            <td class="fw-bold">I Made Wijaya, S.Pd.SD.</td>
                            <td>Guru Mapel PJOK</td>
                            <td><span class="badge bg-success rounded-pill">Aktif</span></td>
                            <td><button class="btn btn-sm btn-pastel"><i class="fa-solid fa-envelope"></i> Hubungi</button></td>
                        </tr>
                        <tr>
                            <td>199011102015012003</td>
                            <td class="fw-bold">Ni Komang Ayu, S.Ag.</td>
                            <td>Guru Agama Hindu</td>
                            <td><span class="badge bg-success rounded-pill">Aktif</span></td>
                            <td><button class="btn btn-sm btn-pastel"><i class="fa-solid fa-envelope"></i> Hubungi</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                <nav aria-label="Page navigation">
                  <ul class="pagination pagination-sm">
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#" style="background-color: var(--pastel-primary); border-color: var(--pastel-primary-dark); color: var(--pastel-text);">1</a></li>
                    <li class="page-item"><a class="page-link" href="#" style="color: var(--pastel-text);">2</a></li>
                    <li class="page-item"><a class="page-link" href="#" style="color: var(--pastel-text);">Next</a></li>
                  </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
