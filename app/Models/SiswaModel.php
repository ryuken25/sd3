<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';
    protected $allowedFields = [
        'nis',
        'nisn',
        'password',
        'nama_siswa',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'id_kelas',
        'id_tahun_ajaran',
        'id_user_ortu',
        'nama_ayah',
        'nama_ibu',
        'no_telp_ortu',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Ambil baris siswa milik akun orang tua tertentu.
     *
     * Karena siswa disimpan satu baris per Tahun Ajaran (composite UNIQUE
     * (nis, id_tahun_ajaran)), method ini wajib menerima id TA aktif —
     * tanpa filter, satu anak yang ada di banyak TA akan muncul sebagai
     * banyak baris di dashboard orang tua.
     *
     * Bila $idTahunAjaran null (mis. tidak ada TA aktif), fallback ke
     * dedup-per-NIS: ambil baris TA terbaru untuk tiap NIS — supaya UI
     * tetap menampilkan satu card per anak unik.
     */
    public function findByParentUser(int $idUser, ?int $idTahunAjaran = null): array
    {
        if ($idTahunAjaran !== null) {
            return $this->where('id_user_ortu', $idUser)
                ->where('status', 'aktif')
                ->where('id_tahun_ajaran', $idTahunAjaran)
                ->orderBy('nama_siswa', 'ASC')
                ->findAll();
        }

        $all = $this->where('id_user_ortu', $idUser)
            ->where('status', 'aktif')
            ->orderBy('id_tahun_ajaran', 'DESC')
            ->findAll();

        $byNis = [];
        foreach ($all as $row) {
            $byNis[$row['nis']] = $byNis[$row['nis']] ?? $row;
        }
        $result = array_values($byNis);
        usort($result, static fn($a, $b) => strcmp((string) $a['nama_siswa'], (string) $b['nama_siswa']));
        return $result;
    }

    public function isOwnedByParent(int $idSiswa, int $idUser): bool
    {
        return $this->where('id_siswa', $idSiswa)
            ->where('id_user_ortu', $idUser)
            ->countAllResults() > 0;
    }
}
