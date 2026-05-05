<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\MataPelajaranModel;
use App\Models\RequestBukaNilaiModel;
use App\Models\TahunAjaranModel;

class RequestBukaNilai extends BaseController
{
    public function index()
    {
        $session = session();
        $id_guru = $session->get('id_user');

        $requestModel = new RequestBukaNilaiModel();

        $myRequests = $requestModel->select('request_buka_nilai.*, tahun_ajaran.tahun_ajaran, tahun_ajaran.semester, kelas.nama_kelas, mata_pelajaran.nama_mapel')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = request_buka_nilai.id_tahun_ajaran', 'left')
            ->join('kelas', 'kelas.id_kelas = request_buka_nilai.id_kelas', 'left')
            ->join('mata_pelajaran', 'mata_pelajaran.id_mapel = request_buka_nilai.id_mapel', 'left')
            ->where('request_buka_nilai.id_guru', $id_guru)
            ->orderBy('request_buka_nilai.created_at', 'DESC')
            ->findAll();

        $tahunAjaranModel = new TahunAjaranModel();
        $kelasModel = new KelasModel();
        $mapelModel = new MataPelajaranModel();

        $data = [
            'title' => 'Permintaan Buka Nilai',
            'my_requests' => $myRequests,
            'tahun_ajaran' => $tahunAjaranModel->orderBy('id_tahun_ajaran', 'DESC')->findAll(),
            'kelas' => $kelasModel->orderBy('tingkat', 'ASC')->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel' => $mapelModel->getWithClasses(),
        ];

        return view('guru/request_buka_nilai/index', $data);
    }

    public function store()
    {
        $session = session();
        $id_guru = $session->get('id_user');

        if (
            !$this->validate([
                'id_tahun_ajaran' => 'required|integer',
                'id_kelas' => 'required|integer',
                'id_mapel' => 'required|integer',
                'alasan' => 'required|min_length[10]'
            ])
        ) {
            return redirect()->back()->with('error', 'Data tidak lengkap. Kelas, mata pelajaran, semester, dan alasan wajib diisi. Alasan minimal 10 karakter.')->withInput();
        }

        $requestModel = new RequestBukaNilaiModel();
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAjaran = $tahunAjaranModel->find($this->request->getPost('id_tahun_ajaran'));

        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Tahun ajaran tidak ditemukan.')->withInput();
        }

        if (($tahunAjaran['status_pengisian'] ?? null) !== 'Kunci' && ($tahunAjaran['aktif'] ?? null) === 'aktif') {
            return redirect()->back()->with('error', 'Permintaan buka nilai hanya untuk semester/tahun ajaran yang sudah dikunci, nonaktif, atau sudah tidak berjalan.')->withInput();
        }

        if ($response = $this->rejectIfMapelNotInClass((int) $this->request->getPost('id_kelas'), (int) $this->request->getPost('id_mapel'))) {
            return $response->withInput();
        }

        // Check if there's already a pending request
        $existing = $requestModel->where([
            'id_guru' => $id_guru,
            'id_tahun_ajaran' => $this->request->getPost('id_tahun_ajaran'),
            'id_kelas' => $this->request->getPost('id_kelas'),
            'id_mapel' => $this->request->getPost('id_mapel'),
            'status' => 'pending'
        ])->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Sudah ada permintaan yang sedang menunggu persetujuan untuk kelas, mata pelajaran, dan semester ini.');
        }

        $requestModel->insert([
            'id_guru' => $id_guru,
            'id_tahun_ajaran' => $this->request->getPost('id_tahun_ajaran'),
            'id_kelas' => $this->request->getPost('id_kelas'),
            'id_mapel' => $this->request->getPost('id_mapel'),
            'alasan' => $this->request->getPost('alasan'),
            'status' => 'pending'
        ]);

        return redirect()->to(base_url('guru/request-buka-nilai'))->with('success', 'Permintaan berhasil dikirim. Tunggu persetujuan admin.');
    }
}
