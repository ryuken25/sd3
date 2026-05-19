<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TahunAjaranModel;

class TahunAjaran extends BaseController
{
    public function index()
    {
        $taModel = new TahunAjaranModel();

        $data = [
            'title' => 'Tahun Ajaran',
            'ta' => $taModel->orderBy('tahun_ajaran', 'DESC')->orderBy('semester', 'DESC')->findAll()
        ];
        return view('admin/tahun_ajaran/index', $data);
    }

    public function store()
    {
        $taModel = new TahunAjaranModel();

        if (
            !$this->validate([
                'tahun_ajaran' => 'required|max_length[9]',
                'semester' => 'required|in_list[Ganjil,Genap]',
                'tanggal_mulai' => 'required|valid_date',
                'tanggal_selesai' => 'required|valid_date'
            ])
        ) {
            return redirect()->back()->with('error', 'Gagal menyimpan. Data tidak lengkap atau tidak valid.');
        }

        $tahunAjaran = trim((string) $this->request->getPost('tahun_ajaran'));
        if ($message = $this->validateTahunAjaranFormat($tahunAjaran)) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        if ($message = $this->validateDuplicateTahunSemester($taModel, $tahunAjaran, (string) $this->request->getPost('semester'))) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        if (
            $message = $this->validatePeriodeTanggal(
                (string) $this->request->getPost('tanggal_mulai'),
                (string) $this->request->getPost('tanggal_selesai')
            )
        ) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        $taData = [
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $this->request->getPost('semester'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'aktif' => 'nonaktif',
            'status_pengisian' => 'Buka'
        ];

        if ($taModel->insert($taData)) {
            return redirect()->to(base_url('admin/tahun-ajaran'))->with('success', 'Tahun Ajaran berhasil ditambahkan.');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data ke database.');
        }
    }

    public function update($id)
    {
        $taModel = new TahunAjaranModel();
        $ta = $taModel->find($id);

        if (!$ta) {
            return redirect()->to(base_url('admin/tahun-ajaran'))->with('error', 'Data Tahun Ajaran tidak ditemukan.');
        }

        if (
            !$this->validate([
                'tahun_ajaran' => 'required|max_length[9]',
                'semester' => 'required|in_list[Ganjil,Genap]',
                'tanggal_mulai' => 'required|valid_date',
                'tanggal_selesai' => 'required|valid_date'
            ])
        ) {
            return redirect()->back()->with('error', 'Gagal memperbarui. Data tidak lengkap atau tidak valid.')->withInput();
        }

        $tahunAjaran = trim((string) $this->request->getPost('tahun_ajaran'));
        if ($message = $this->validateTahunAjaranFormat($tahunAjaran)) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        if ($message = $this->validateDuplicateTahunSemester($taModel, $tahunAjaran, (string) $this->request->getPost('semester'), (int) $id)) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        if (
            $message = $this->validatePeriodeTanggal(
                (string) $this->request->getPost('tanggal_mulai'),
                (string) $this->request->getPost('tanggal_selesai')
            )
        ) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        $taModel->update($id, [
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $this->request->getPost('semester'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
        ]);

        return redirect()->to(base_url('admin/tahun-ajaran'))->with('success', 'Data Tahun Ajaran berhasil diperbarui.');
    }

    /**
     * Toggle aktif/nonaktif for a tahun_ajaran
     */
    public function setAktif($id)
    {
        $taModel = new TahunAjaranModel();
        $ta = $taModel->find($id);

        if (!$ta) {
            return redirect()->to(base_url('admin/tahun-ajaran'))->with('error', 'Data tidak ditemukan.');
        }

        // Deactivate all, then activate the selected one
        $taModel->where('aktif', 'aktif')->set('aktif', 'nonaktif')->update();
        $taModel->update($id, ['aktif' => 'aktif']);

        return redirect()->to(base_url('admin/tahun-ajaran'))->with('success', 'Tahun Ajaran berhasil diaktifkan.');
    }

    /**
     * Toggle status_pengisian (Buka/Kunci) for a tahun_ajaran
     */
    public function toggleKunci($id)
    {
        $taModel = new TahunAjaranModel();
        $ta = $taModel->find($id);

        if (!$ta) {
            return redirect()->to(base_url('admin/tahun-ajaran'))->with('error', 'Data tidak ditemukan.');
        }

        $newStatus = $ta['status_pengisian'] === 'Buka' ? 'Kunci' : 'Buka';
        $taModel->update($id, ['status_pengisian' => $newStatus]);

        $msg = $newStatus === 'Kunci'
            ? 'Semester berhasil dikunci. Nilai tidak dapat diubah.'
            : 'Semester dibuka kembali. Nilai dapat diubah.';

        return redirect()->to(base_url('admin/tahun-ajaran'))->with('success', $msg);
    }

    private function validateTahunAjaranFormat(string $tahunAjaran): ?string
    {
        if (!preg_match('/^[0-9]{4}\/[0-9]{4}$/', $tahunAjaran)) {
            return 'Format tahun ajaran harus seperti 2026/2027.';
        }

        [$tahunMulai, $tahunSelesai] = array_map('intval', explode('/', $tahunAjaran));
        if ($tahunSelesai !== $tahunMulai + 1) {
            return 'Tahun ajaran harus berurutan, misalnya 2026/2027.';
        }

        return null;
    }

    private function validatePeriodeTanggal(string $tanggalMulai, string $tanggalSelesai): ?string
    {
        $mulai = strtotime($tanggalMulai);
        $selesai = strtotime($tanggalSelesai);

        if ($mulai === false || $selesai === false) {
            return 'Tanggal mulai dan tanggal selesai harus diisi dengan benar.';
        }

        if ($mulai === $selesai) {
            return 'Tanggal mulai tidak boleh sama dengan tanggal selesai.';
        }

        if ($mulai > $selesai) {
            return 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.';
        }

        return null;
    }

    private function validateDuplicateTahunSemester(TahunAjaranModel $taModel, string $tahunAjaran, string $semester, ?int $ignoreId = null): ?string
    {
        $builder = $taModel->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester);

        if ($ignoreId !== null) {
            $builder->where('id_tahun_ajaran !=', $ignoreId);
        }

        if ($builder->first()) {
            return 'Tahun ajaran dan semester yang sama sudah ada.';
        }

        return null;
    }
}
