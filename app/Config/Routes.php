<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->post('auth/process', 'Auth::process');
$routes->get('logout', 'Auth::logout');

// Admin Routes
$routes->group('admin', ['filter' => ['auth', 'role:admin']], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // Tahun Ajaran
    $routes->get('tahun-ajaran', 'Admin\TahunAjaran::index');
    $routes->post('tahun-ajaran/store', 'Admin\TahunAjaran::store');
    $routes->post('tahun-ajaran/update/(:num)', 'Admin\TahunAjaran::update/$1');
    $routes->post('tahun-ajaran/set-aktif/(:num)', 'Admin\TahunAjaran::setAktif/$1');
    $routes->post('tahun-ajaran/toggle-kunci/(:num)', 'Admin\TahunAjaran::toggleKunci/$1');

    // KKM
    $routes->get('kkm', 'Admin\Kkm::index');
    $routes->post('kkm/store', 'Admin\Kkm::store');
    $routes->post('kkm/update/(:num)', 'Admin\Kkm::update/$1');
    $routes->post('kkm/delete/(:num)', 'Admin\Kkm::delete/$1');

    // Kelas
    $routes->get('kelas', 'Admin\Kelas::index');
    $routes->post('kelas/store', 'Admin\Kelas::store');
    $routes->post('kelas/update/(:num)', 'Admin\Kelas::update/$1');
    $routes->post('kelas/delete/(:num)', 'Admin\Kelas::delete/$1');

    // Siswa
    $routes->get('siswa', 'Admin\Siswa::index');
    $routes->post('siswa/store', 'Admin\Siswa::store');
    $routes->post('siswa/update/(:num)', 'Admin\Siswa::update/$1');
    $routes->post('siswa/delete/(:num)', 'Admin\Siswa::delete/$1');
    $routes->post('siswa/reset-password/(:num)', 'Admin\Siswa::resetPassword/$1');

    // Guru
    $routes->get('guru', 'Admin\Guru::index');
    $routes->post('guru/store', 'Admin\Guru::store');
    $routes->post('guru/update/(:num)', 'Admin\Guru::update/$1');
    $routes->post('guru/delete/(:num)', 'Admin\Guru::delete/$1');

    // Mapel
    $routes->get('mapel', 'Admin\Mapel::index');
    $routes->post('mapel/store', 'Admin\Mapel::store');
    $routes->post('mapel/update/(:num)', 'Admin\Mapel::update/$1');
    $routes->post('mapel/delete/(:num)', 'Admin\Mapel::delete/$1');

    // Import/Export — Siswa
    $routes->get('import', 'Admin\Import::index');
    $routes->post('import/process', 'Admin\Import::process');
    $routes->get('import/template', 'Admin\Import::downloadTemplate');

    // Import Nilai
    $routes->get('import-nilai', 'Admin\ImportNilai::index');
    $routes->get('import-nilai/template/(:num)', 'Admin\ImportNilai::downloadTemplate/$1');
    $routes->post('import-nilai/upload', 'Admin\ImportNilai::upload');

    // Request Buka Nilai (Approval System)
    $routes->get('request-buka-nilai', 'Admin\RequestBukaNilai::index');
    $routes->post('request-buka-nilai/approve/(:num)', 'Admin\RequestBukaNilai::approve/$1');
    $routes->post('request-buka-nilai/reject/(:num)', 'Admin\RequestBukaNilai::reject/$1');

    // Rapor Management
    $routes->get('rapor', 'Admin\Rapor::index');
    $routes->get('rapor/detail/(:num)/(:num)', 'Admin\Rapor::detail/$1/$2');
    $routes->post('rapor/store', 'Admin\Rapor::store');
    $routes->post('rapor/update/(:num)', 'Admin\Rapor::update/$1');
    $routes->post('rapor/unfinalize/(:num)', 'Admin\Rapor::unfinalize/$1');
    $routes->post('rapor/import-attendance', 'Admin\Rapor::importAttendance');
    $routes->post('rapor/finalize-class/(:num)/(:num)', 'Admin\Rapor::finalizeClass/$1/$2');
    $routes->post('rapor/finalize/(:num)/(:num)', 'Admin\Rapor::finalize/$1/$2');
});

// Guru Routes
$routes->group('guru', ['filter' => ['auth', 'role:guru']], static function ($routes) {
    $routes->get('dashboard', 'Guru\Dashboard::index');

    // Nilai Harian
    $routes->get('nilai-harian', 'Guru\NilaiHarian::index');
    $routes->get('nilai-harian/by-class', 'Guru\NilaiHarian::byClass');
    $routes->get('nilai-harian/by-student', 'Guru\NilaiHarian::byStudent');
    $routes->get('nilai-harian/get-siswa', 'Guru\NilaiHarian::getSiswa');
    $routes->post('nilai-harian/save', 'Guru\NilaiHarian::save');

    // Nilai Ujian
    $routes->get('nilai-ujian', 'Guru\NilaiUjian::index');
    $routes->get('nilai-ujian/by-class', 'Guru\NilaiUjian::byClass');
    $routes->post('nilai-ujian/save', 'Guru\NilaiUjian::save');

    // Penilaian Agregat (Unified Grade Input)
    $routes->get('penilaian-agregat', 'Guru\PenilaianAgregat::index');
    $routes->get('penilaian-agregat/input', 'Guru\PenilaianAgregat::input');
    $routes->post('penilaian-agregat/save', 'Guru\PenilaianAgregat::save');

    // Nilai Akhir & Remedial
    $routes->get('nilai-akhir', 'Guru\NilaiAkhir::index');
    $routes->post('nilai-akhir/calculate', 'Guru\NilaiAkhir::calculate');
    $routes->get('nilai-akhir/review', 'Guru\NilaiAkhir::review');
    $routes->get('nilai-akhir/rekap-remedial', 'Guru\NilaiAkhir::rekapRemedial');
    $routes->post('nilai-akhir/save-remedial', 'Guru\NilaiAkhir::saveRemedial');
    $routes->post('nilai-akhir/save-catatan-borderline', 'Guru\NilaiAkhir::saveCatatanBorderline');

    // Wali Kelas (rapor sections: catatan, ekskul, koko, ketidakhadiran)
    $routes->get('wali-kelas', 'Guru\WaliKelas::index');
    $routes->get('wali-kelas/siswa/(:num)', 'Guru\WaliKelas::siswa/$1');
    $routes->post('wali-kelas/save-catatan', 'Guru\WaliKelas::saveCatatan');
    $routes->post('wali-kelas/save-ekskul', 'Guru\WaliKelas::saveEkskul');
    $routes->post('wali-kelas/save-kokurikuler', 'Guru\WaliKelas::saveKokurikuler');
    $routes->post('wali-kelas/save-ketidakhadiran', 'Guru\WaliKelas::saveKetidakhadiran');

    // Capaian Kompetensi (input CP per siswa per mapel)
    $routes->get('capaian-kompetensi', 'Guru\CapaianKompetensi::index');
    $routes->get('capaian-kompetensi/input', 'Guru\CapaianKompetensi::input');
    $routes->post('capaian-kompetensi/save', 'Guru\CapaianKompetensi::save');

    // Request Buka Nilai
    $routes->get('request-buka-nilai', 'Guru\RequestBukaNilai::index');
    $routes->post('request-buka-nilai/store', 'Guru\RequestBukaNilai::store');
});

// Orang Tua Routes
$routes->group('orangtua', ['filter' => ['auth', 'role:orang_tua']], static function ($routes) {
    $routes->get('dashboard', 'OrangTua\Dashboard::index');
    $routes->get('grades/(:num)', 'OrangTua\Dashboard::viewGrades/$1');
    $routes->get('rapor/(:num)/(:num)', 'OrangTua\Dashboard::viewRapor/$1/$2');
    $routes->get('rapor/download/(:num)/(:num)', 'OrangTua\Rapor::downloadPDF/$1/$2');
});
