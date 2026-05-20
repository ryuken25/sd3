/**
 * Konstanta data test — selaras dengan SD3MekarsariSeeder + SD3_RaporIsiSeeder.
 *
 * Catatan: id_siswa bersifat per-(siswa × tahun ajaran). Nilai di bawah adalah
 * id_siswa untuk Tahun Ajaran AKTIF (2025/2026 Ganjil, id_tahun_ajaran = 5).
 */
export const TA_AKTIF_ID = 5;

/** Siswa contoh di TA aktif (kelas 3 & 6 — rapornya difinalisasi seeder). */
export const SISWA = {
  danendraKelas3: { idSiswa: 244, nis: '909', nama: 'DANENDRA ADI PRATAMA' },
};

/** Section yang WAJIB ada di layout rapor lengkap (admin detail & e-rapor). */
export const RAPOR_SECTIONS = [
  'LAPORAN HASIL BELAJAR',
  'Mata Pelajaran Wajib',
  'Mata Pelajaran Pilihan',
  'Kokurikuler',
  'Ekstrakurikuler',
  'Ketidakhadiran',
  'Catatan Wali Kelas',
  'Tanggapan Orang Tua/Wali Murid',
];

export const KEPSEK = {
  nama: 'Ni Wayan Kasrinayanti, S. Pd.',
  nip: '198408132014062008',
};
