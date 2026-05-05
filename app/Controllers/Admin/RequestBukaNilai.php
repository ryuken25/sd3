<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RequestBukaNilaiModel;
use App\Models\TahunAjaranModel;

class RequestBukaNilai extends BaseController
{
    public function index()
    {
        $requestModel = new RequestBukaNilaiModel();

        $data = [
            'title' => 'Manajemen Permintaan Buka Nilai',
            'requests' => $requestModel->getRequestsWithDetails()
        ];

        return view('admin/request_buka_nilai/index', $data);
    }

    public function approve($id_request)
    {
        $requestModel = new RequestBukaNilaiModel();
        $session = session();
        $admin_id = $session->get('id_user');

        $request = $requestModel->find($id_request);

        if (!$request) {
            return redirect()->back()->with('error', 'Permintaan tidak ditemukan.');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan sudah diproses sebelumnya.');
        }

        $tahunAjaran = (new TahunAjaranModel())->find((int) $request['id_tahun_ajaran']);
        if (!$tahunAjaran || (($tahunAjaran['status_pengisian'] ?? null) !== 'Kunci' && ($tahunAjaran['aktif'] ?? null) === 'aktif')) {
            return redirect()->back()->with('error', 'Permintaan hanya boleh disetujui untuk semester yang terkunci, nonaktif, atau sudah tidak berjalan.');
        }

        if (!$this->mapelBelongsToClass((int) ($request['id_kelas'] ?? 0), (int) ($request['id_mapel'] ?? 0))) {
            return redirect()->back()->with('error', 'Permintaan tidak valid karena mapel tidak sesuai kelas.');
        }

        // Grant 1-day scoped access for the requesting teacher, class, subject, and year only.
        $tanggal_akses = date('Y-m-d', strtotime('+1 day'));

        $requestModel->update($id_request, [
            'status' => 'disetujui',
            'approved_by' => $admin_id,
            'tanggal_akses' => $tanggal_akses,
            'catatan_admin' => $this->request->getPost('catatan_admin')
        ]);

        return redirect()->back()->with('success', 'Permintaan disetujui. Akses edit nilai diberikan terbatas sampai ' . date('d/m/Y', strtotime($tanggal_akses)) . '.');
    }

    public function reject($id_request)
    {
        $requestModel = new RequestBukaNilaiModel();
        $session = session();
        $admin_id = $session->get('id_user');

        $request = $requestModel->find($id_request);

        if (!$request) {
            return redirect()->back()->with('error', 'Permintaan tidak ditemukan.');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan sudah diproses sebelumnya.');
        }

        $requestModel->update($id_request, [
            'status' => 'ditolak',
            'approved_by' => $admin_id,
            'catatan_admin' => $this->request->getPost('catatan_admin')
        ]);

        return redirect()->back()->with('success', 'Permintaan ditolak. Nilai tetap terkunci.');
    }
}
