"""Update spec.json antarmuka to be 1:1 selaras with implementasi (29 entries)."""
import json
import sys

ANT = [
    {"title": "Halaman Login",
     "desc": "Halaman login bersama admin, guru, dan orang tua. Admin/guru pakai username; orang tua pakai NIS siswa.",
     "mockup": [
       {"type":"navbar","logo":True,"title":"SIM Nilai SDN 3 Mekarsari"},
       {"type":"form","title":"Login Sistem",
        "fields":[{"label":"Username / NIS"},{"label":"Password","type":"password"}],
        "submit":"Masuk"}
     ]},
    {"title": "Dashboard Admin",
     "desc": "Ringkasan KPI total siswa, guru, kelas, dan remedial belum selesai, plus shortcut ke modul utama dan status tahun ajaran.",
     "mockup": [
       {"type":"navbar","logo":True,"title":"SIM Nilai","menu":["Dashboard","Master","Rapor","Logout"]},
       {"type":"sidebar","items":["Dashboard","Tahun Ajaran","Kelas","Mata Pelajaran","KKM","Data Siswa","Data Guru","Import","Manajemen Rapor"]},
       {"type":"heading","text":"Dashboard Admin"},
       {"type":"cards","items":[
         {"title":"Total Siswa Aktif","value":"120"},
         {"title":"Total Guru","value":"9"},
         {"title":"Total Kelas","value":"6"},
         {"title":"Remedial Belum Selesai","value":"12"}
       ]},
       {"type":"buttons","items":["Tambah Siswa","Import Data","Manajemen Rapor"]}
     ]},
    {"title": "Manajemen Data Siswa",
     "desc": "Daftar siswa dengan filter kelas+tahun ajaran, aksi tambah/edit/hapus, dan tombol import massal.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Data Siswa","Data Guru","Kelas"]},
       {"type":"heading","text":"Manajemen Data Siswa"},
       {"type":"table","title":"Daftar Siswa",
        "columns":["No","NIS","Nama","Jenis Kelamin","Kelas","Aksi"],"dummy_rows":5},
       {"type":"buttons","items":["Tambah Siswa","Import Excel","Export Excel"]}
     ]},
    {"title": "Manajemen Data Guru",
     "desc": "Daftar guru dengan kolom username, nama lengkap, no telepon, status, dan aksi.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Data Siswa","Data Guru","Kelas"]},
       {"type":"heading","text":"Manajemen Data Guru"},
       {"type":"table","title":"Daftar Guru",
        "columns":["No","Username","Nama Lengkap","No Telepon","Status","Aksi"],"dummy_rows":5},
       {"type":"buttons","items":["Tambah Guru Baru"]}
     ]},
    {"title": "Manajemen Kelas",
     "desc": "Daftar kelas dengan tingkat dan wali kelas yang dipilih dari pengguna ber-role guru.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Kelas","Mata Pelajaran"]},
       {"type":"heading","text":"Manajemen Kelas"},
       {"type":"table","title":"Daftar Kelas",
        "columns":["No","Nama Kelas","Tingkat","Wali Kelas","Aksi"],"dummy_rows":6},
       {"type":"buttons","items":["Tambah Kelas"]}
     ]},
    {"title": "Mata Pelajaran",
     "desc": "Daftar mata pelajaran beserta kode, kelompok wajib/pilihan, kelas penerima, dan guru pengampu.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Mata Pelajaran","KKM"]},
       {"type":"heading","text":"Data Mata Pelajaran"},
       {"type":"form","title":"Filter",
        "fields":[{"label":"Kelas"}],"submit":"Tampilkan"},
       {"type":"table","title":"Daftar Mata Pelajaran",
        "columns":["No","Kode","Nama Mapel","Kelompok","Kelas","Guru","Aksi"],"dummy_rows":5},
       {"type":"buttons","items":["Tambah Mata Pelajaran Baru"]}
     ]},
    {"title": "Konfigurasi KKM (Awal)",
     "desc": "Halaman KKM dengan filter Tahun Ajaran + Kelas, default menampilkan tahun ajaran aktif.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","KKM","Mata Pelajaran"]},
       {"type":"heading","text":"Konfigurasi KKM"},
       {"type":"form","title":"Filter",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"}],"submit":"Tampilkan"},
       {"type":"text","text":"Pilih Tahun Ajaran dan Kelas lalu klik Tampilkan untuk melihat daftar KKM."}
     ]},
    {"title": "Konfigurasi KKM (Setelah Filter)",
     "desc": "Hasil KKM setelah filter diterapkan: daftar KKM per mata pelajaran dengan aksi edit/hapus.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","KKM","Mata Pelajaran"]},
       {"type":"heading","text":"Konfigurasi KKM - Kelas 3 (2025/2026)"},
       {"type":"table","title":"Daftar KKM",
        "columns":["No","Tahun Ajaran","Kelas","Mata Pelajaran","Nilai KKM","Aksi"],"dummy_rows":5},
       {"type":"buttons","items":["Atur KKM Baru","Reset Filter"]}
     ]},
    {"title": "Manajemen Tahun Ajaran",
     "desc": "Daftar tahun ajaran dengan urutan kolom Tahun Ajaran, Semester, Periode, Status Editor (Buka/Kunci), Status, dan aksi.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Tahun Ajaran","Kelas"]},
       {"type":"heading","text":"Manajemen Tahun Ajaran"},
       {"type":"table","title":"Daftar Tahun Ajaran",
        "columns":["No","Tahun Ajaran","Semester","Periode","Status Editor","Status","Aksi"],"dummy_rows":5},
       {"type":"buttons","items":["Tambah Baru","Set Aktif"]}
     ]},
    {"title": "Manajemen Rapor (Awal)",
     "desc": "Halaman manajemen rapor menampilkan filter Tahun Ajaran + Kelas dan placeholder sampai filter diterapkan.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Manajemen Rapor","Permintaan Buka Nilai"]},
       {"type":"heading","text":"Manajemen Rapor Siswa"},
       {"type":"form","title":"Filter Data Rapor",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"}],"submit":"Tampilkan"},
       {"type":"text","text":"Pilih Tahun Ajaran dan Kelas lalu klik Tampilkan untuk melihat data rapor."}
     ]},
    {"title": "Manajemen Rapor (Setelah Filter)",
     "desc": "Daftar rapor seluruh siswa pada kelas terpilih beserta ringkasan absensi, status remedial, status kelengkapan, dan tombol finalisasi.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Manajemen Rapor","Permintaan Buka Nilai"]},
       {"type":"heading","text":"Manajemen Rapor - Kelas 3 (2025/2026)"},
       {"type":"cards","items":[
         {"title":"Total Siswa","value":"13"},
         {"title":"Siswa Lengkap","value":"10"},
         {"title":"Belum Lengkap","value":"3"},
         {"title":"Sudah Final","value":"0"}
       ]},
       {"type":"table","title":"Daftar Rapor",
        "columns":["No","Nama Siswa","Mapel Belum","Ketidakhadiran","Catatan Wali","Remedial","Status","Aksi"],"dummy_rows":5},
       {"type":"buttons","items":["Import Absensi","Finalisasi Rapor Kelas"]}
     ]},
    {"title": "Detail Rapor Admin (Preview)",
     "desc": "Preview e-rapor lengkap dengan kop sekolah, identitas, tabel nilai dengan narasi capaian, kokurikuler, ekstrakurikuler, catatan wali kelas, dan tanda tangan.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"image","label":"Kop Sekolah","h":100},
       {"type":"heading","text":"Detail Rapor - Siswa A"},
       {"type":"table","title":"Identitas",
        "columns":["Label","Nilai"],
        "rows":[["Nama","Siswa A"],["NIS / NISN","123 / 1234567890"],["Kelas","3"],["Tahun Ajaran","2025/2026"]]},
       {"type":"table","title":"Mata Pelajaran",
        "columns":["No","Mata Pelajaran","Nilai Akhir","Capaian Kompetensi"],"dummy_rows":5},
       {"type":"buttons","items":["Cetak Halaman","Edit Absensi / Catatan","Kembali"]}
     ]},
    {"title": "Import Data Siswa",
     "desc": "Halaman import massal siswa dari template Excel: download template, upload file, validasi, lalu terapkan dengan otomatis membuat akun orang tua per NIS.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Import","Data Siswa"]},
       {"type":"heading","text":"Import Data Siswa (Massal)"},
       {"type":"text","text":"1. Pilih Tahun Ajaran tujuan; 2. Download template Excel; 3. Upload file isian."},
       {"type":"form","title":"Form Upload",
        "fields":[{"label":"Tahun Ajaran"},{"label":"File Excel"}],"submit":"Proses Import"},
       {"type":"buttons","items":["Download Template","Reset"]}
     ]},
    {"title": "Manajemen Permintaan Buka Nilai",
     "desc": "Daftar permintaan buka nilai dari guru: nama guru, kelas, mapel, alasan, waktu pengajuan, status, dan aksi setujui/tolak.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Permintaan Buka Nilai","Manajemen Rapor"]},
       {"type":"heading","text":"Manajemen Permintaan Buka Nilai"},
       {"type":"table","title":"Daftar Permintaan",
        "columns":["No","Nama Guru","Kelas","Mapel","Alasan","Waktu","Status","Aksi"],"dummy_rows":4},
       {"type":"buttons","items":["Setujui","Tolak"]}
     ]},
    {"title": "Dashboard Guru",
     "desc": "Dashboard guru menampilkan total nilai diinput, jumlah remedial belum ditangani, dan panduan alur input nilai.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai","menu":["Dashboard","Penilaian","Wali Kelas","Logout"]},
       {"type":"sidebar","items":["Dashboard","Penilaian Agregat","Capaian Kompetensi","Template Capaian","Nilai Akhir","Rekap Remedial","Anak Wali Kelas","Permintaan Buka Nilai"]},
       {"type":"heading","text":"Dashboard Guru"},
       {"type":"cards","items":[
         {"title":"Total Nilai Diinput","value":"120"},
         {"title":"Remedial Belum Ditangani","value":"5"}
       ]},
       {"type":"buttons","items":["Penilaian Agregat","Nilai Akhir","Permintaan Buka Nilai"]}
     ]},
    {"title": "Input Nilai Harian",
     "desc": "Form input nilai harian dua mode: by-class (vertikal seluruh siswa) dan by-student (horizontal seluruh mapel).",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Nilai Harian","Nilai UTS/UAS"]},
       {"type":"heading","text":"Input Nilai Harian"},
       {"type":"text","text":"Mode 1: By Class | Mode 2: By Student"},
       {"type":"form","title":"Filter",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"},{"label":"Mata Pelajaran"}],"submit":"Buka Form Input"}
     ]},
    {"title": "Input Nilai UTS/UAS",
     "desc": "Form input nilai UTS dan UAS per kelas + mata pelajaran, masing-masing berkontribusi 30% ke nilai akhir.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Nilai Harian","Nilai UTS/UAS"]},
       {"type":"heading","text":"Input Nilai UTS / UAS"},
       {"type":"form","title":"Filter Per Kelas",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"},{"label":"Mata Pelajaran"}],"submit":"Buka Form Input Nilai"},
       {"type":"text","text":"Informasi: UTS 30%, UAS 30%, Harian 40%. Pastikan nilai harian sudah diisi."}
     ]},
    {"title": "Penilaian Agregat (Filter)",
     "desc": "Halaman filter Penilaian Agregat untuk memilih tahun ajaran, kelas, mata pelajaran sebelum membuka form input.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Penilaian Agregat","Capaian Kompetensi","Nilai Akhir"]},
       {"type":"heading","text":"Penilaian Agregat"},
       {"type":"form","title":"Pilih Parameter",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"},{"label":"Mata Pelajaran"}],"submit":"Buka Form Input Agregat"}
     ]},
    {"title": "Penilaian Agregat (Form Input)",
     "desc": "Form input agregat dengan rumus 40-30-30 di atas, validasi 0-100, kolom tindak lanjut otomatis untuk siswa di bawah KKM.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Penilaian Agregat"]},
       {"type":"heading","text":"Penilaian Agregat - Input Nilai"},
       {"type":"text","text":"Rumus: Nilai Akhir = (Harian x 0.4) + (UTS x 0.3) + (UAS x 0.3)"},
       {"type":"table","title":"Tabel Nilai Siswa",
        "columns":["No","Nama Siswa","Tugas","Ulangan","UTS","UAS","Nilai Akhir","Tindak Lanjut"],"dummy_rows":5},
       {"type":"buttons","items":["Simpan Semua","Batal"]}
     ]},
    {"title": "Capaian Kompetensi (Filter)",
     "desc": "Halaman pemilihan parameter (tahun ajaran, kelas, mapel) sebelum input narasi capaian.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Capaian Kompetensi","Template Capaian"]},
       {"type":"heading","text":"Capaian Kompetensi"},
       {"type":"text","text":"Pilih Kelas, Mapel, dan TA. Narasi otomatis prefill dari template band predikat."},
       {"type":"form","title":"Filter",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"},{"label":"Mata Pelajaran"}],"submit":"Buka Form Input CP"}
     ]},
    {"title": "Template Capaian (Filter)",
     "desc": "Filter mata pelajaran + fase + semester untuk menampilkan 4 template narasi per band A/B/C/D.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Template Capaian","Capaian Kompetensi"]},
       {"type":"heading","text":"Kelola Template Capaian"},
       {"type":"text","text":"Siapkan 4 narasi template per (Mata Pelajaran, Fase, Semester)."},
       {"type":"form","title":"Pilih Mapel / Fase / Semester",
        "fields":[{"label":"Mata Pelajaran"},{"label":"Fase"},{"label":"Semester"}],"submit":"Tampilkan"}
     ]},
    {"title": "Template Capaian (Form Band)",
     "desc": "Form 4 narasi template per band A/B/C/D. Narasi inilah yang akan di-prefill di Capaian Kompetensi.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Template Capaian"]},
       {"type":"heading","text":"Kelola Template Capaian - Pendidikan Pancasila Fase B Ganjil"},
       {"type":"table","title":"Template per Band Predikat",
        "columns":["Band","Narasi Template"],
        "rows":[["A","Mencapai kompetensi dengan sangat baik..."],
                ["B","Mencapai kompetensi dengan baik..."],
                ["C","Mencapai kompetensi dengan cukup..."],
                ["D","Mulai berkembang..."]]},
       {"type":"buttons","items":["Simpan Template (4 Band)"]}
     ]},
    {"title": "Nilai Akhir (Filter)",
     "desc": "Halaman pemilihan parameter untuk menghitung dan meninjau nilai akhir per kelas + mapel + tahun ajaran.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Nilai Akhir","Rekap Remedial"]},
       {"type":"heading","text":"Proses Nilai Akhir & Remedial"},
       {"type":"text","text":"Pilih Kelas, Mapel, dan TA. Sistem menghitung otomatis nilai akhir dari komponen yang sudah diinput."},
       {"type":"form","title":"Pilih Parameter",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"},{"label":"Mata Pelajaran"}],"submit":"Hitung Nilai Akhir"}
     ]},
    {"title": "Rekap Remedial",
     "desc": "Daftar siswa nilainya di bawah KKM beserta status remedial dan kolom tindak lanjut.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Nilai Akhir","Rekap Remedial"]},
       {"type":"heading","text":"Rekap Remedial"},
       {"type":"form","title":"Filter",
        "fields":[{"label":"Tahun Ajaran"},{"label":"Kelas"},{"label":"Mata Pelajaran"}],"submit":"Tampilkan Rekap"},
       {"type":"table","title":"Daftar Siswa di Bawah KKM",
        "columns":["No","Nama Siswa","Nilai Akhir","Status","Tindak Lanjut","Aksi"],"dummy_rows":4}
     ]},
    {"title": "Anak Wali Kelas (Daftar)",
     "desc": "Daftar siswa di kelas wali. Kolom No, NIS, Nama, Jenis Kelamin, dan tombol Isi Rapor.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Anak Wali Kelas"]},
       {"type":"heading","text":"Anak Wali Kelas - Tingkat 3"},
       {"type":"table","title":"Daftar Siswa",
        "columns":["No","NIS","Nama Siswa","Jenis Kelamin","Aksi"],"dummy_rows":6},
       {"type":"buttons","items":["Isi Rapor"]}
     ]},
    {"title": "Anak Wali Kelas (Form Siswa)",
     "desc": "Form terintegrasi 4 tab: Catatan, Ketidakhadiran, Ekstrakurikuler, Kokurikuler P5.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Anak Wali Kelas"]},
       {"type":"heading","text":"Wali Kelas - Siswa A"},
       {"type":"text","text":"Tab: Catatan | Ketidakhadiran | Ekstrakurikuler | Kokurikuler P5"},
       {"type":"form","title":"Catatan Wali Kelas",
        "fields":[{"label":"Pilih Template (opsional)"},{"label":"Catatan untuk Siswa","type":"textarea"}],
        "submit":"Simpan Catatan"}
     ]},
    {"title": "Permintaan Buka Nilai (Guru)",
     "desc": "Form pengajuan permintaan buka nilai untuk semester yang sudah dikunci.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"SIM Nilai"},
       {"type":"sidebar","items":["Dashboard","Permintaan Buka Nilai"]},
       {"type":"heading","text":"Permintaan Buka Nilai"},
       {"type":"form","title":"Ajukan Permintaan Baru",
        "fields":[{"label":"Kelas"},{"label":"Mata Pelajaran"},{"label":"Tahun Ajaran"},{"label":"Alasan Permintaan","type":"textarea"}],
        "submit":"Kirim Permintaan"},
       {"type":"table","title":"Riwayat Permintaan",
        "columns":["No","Tahun Ajaran","Kelas","Mapel","Status","Catatan Admin"],"dummy_rows":3}
     ]},
    {"title": "Dashboard Orang Tua",
     "desc": "Kartu anak (NIS, NISN, jenis kelamin, status), ringkasan tuntas vs remedial, tombol akses e-rapor.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"E-Rapor","menu":["Dashboard","Logout"]},
       {"type":"sidebar","items":["Dashboard","Anak Saya"]},
       {"type":"heading","text":"Dashboard Orang Tua"},
       {"type":"cards","items":[
         {"title":"Total Anak","value":"1"},
         {"title":"Rapor Tersedia","value":"1"}
       ]},
       {"type":"table","title":"Daftar Anak",
        "columns":["No","Nama","Kelas","NIS","Status","Aksi"],"dummy_rows":1},
       {"type":"buttons","items":["Lihat Nilai dan Rapor"]}
     ]},
    {"title": "E-Rapor Orang Tua",
     "desc": "Tampilan e-rapor digital dengan kop sekolah identik PDF cetak; akses dibuka setelah rapor difinalisasi.",
     "mockup":[
       {"type":"navbar","logo":True,"title":"E-Rapor"},
       {"type":"image","label":"Kop Sekolah","h":100},
       {"type":"heading","text":"Laporan Hasil Belajar - Siswa A"},
       {"type":"table","title":"Identitas Siswa",
        "columns":["Label","Nilai"],
        "rows":[["Nama","Siswa A"],["NIS / NISN","123 / 1234567890"],["Kelas","3"],["Tahun Ajaran","2025/2026"]]},
       {"type":"table","title":"Mata Pelajaran",
        "columns":["No","Mata Pelajaran","Nilai Akhir","Capaian Kompetensi"],"dummy_rows":5},
       {"type":"text","text":"Catatan Wali Kelas, Ekstrakurikuler, Kokurikuler P5, Ketidakhadiran."},
       {"type":"buttons","items":["Cetak / Unduh PDF","Kembali"]}
     ]},
]

spec = json.load(open('spec.json'))
imp_titles = [i['title'] for i in spec['implementasi']]
ant_titles = [a['title'] for a in ANT]

print(f"antarmuka={len(ANT)}, implementasi={len(imp_titles)}")
mismatch = [(i, a, b) for i, (a, b) in enumerate(zip(ant_titles, imp_titles)) if a != b]
if mismatch:
    print("MISMATCH:")
    for i, a, b in mismatch:
        print(f"  [{i}] ant='{a}' vs imp='{b}'")
    sys.exit(1)
print("All 29 titles selaras 1:1 with implementasi.")

spec['antarmuka'] = ANT
json.dump(spec, open('spec.json', 'w'), indent=2, ensure_ascii=False)
print("Saved spec.json")
