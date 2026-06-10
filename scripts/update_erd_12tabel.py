"""Update spec.json ERD ke 12 entitas + attribute lengkap dgn FORMAT library:
{"id": str, "name": str, "pk": str, "fks": [str], "attrs": [str]}
"""
import json

spec = json.load(open('spec.json', encoding='utf-8'))

entities = [
    {"id": "users", "name": "USERS",
     "pk": "id_user", "fks": [],
     "attrs": ["username", "password", "nama_lengkap", "role", "no_telp", "status"]},

    {"id": "tahun_ajaran", "name": "TAHUN_AJARAN",
     "pk": "id_tahun_ajaran", "fks": [],
     "attrs": ["tahun_ajaran", "semester", "status", "status_pengisian"]},

    {"id": "kelas", "name": "KELAS",
     "pk": "id_kelas", "fks": ["wali_kelas"],
     "attrs": ["nama_kelas", "tingkat"]},

    {"id": "mata_pelajaran", "name": "MATA_PELAJARAN",
     "pk": "id_mapel", "fks": [],
     "attrs": ["kode_mapel", "nama_mapel", "kelompok"]},

    {"id": "mapel_kelas", "name": "MAPEL_KELAS",
     "pk": "id_mapel_kelas",
     "fks": ["id_mapel", "id_kelas", "id_guru"],
     "attrs": []},

    {"id": "siswa", "name": "SISWA",
     "pk": "id_siswa",
     "fks": ["id_kelas", "id_tahun_ajaran"],
     "attrs": ["nis", "nisn", "nama_siswa", "jenis_kelamin", "password", "status"]},

    {"id": "kkm", "name": "KKM",
     "pk": "id_kkm",
     "fks": ["id_mapel", "id_kelas", "id_tahun_ajaran"],
     "attrs": ["nilai_kkm"]},

    {"id": "nilai", "name": "NILAI",
     "pk": "id_nilai",
     "fks": ["id_siswa", "id_mapel", "id_tahun_ajaran"],
     "attrs": ["nilai_tugas", "nilai_ulangan", "rata_rata_harian",
               "nilai_uts", "nilai_uas", "nilai_akhir", "nilai_huruf",
               "status_kelulusan", "narasi", "tindak_lanjut", "status_remedial"]},

    {"id": "rapor", "name": "RAPOR",
     "pk": "id_rapor",
     "fks": ["id_siswa", "id_tahun_ajaran"],
     "attrs": ["sakit", "izin", "alpa", "catatan_wali_kelas",
               "narasi_koko", "is_finalized", "status_kenaikan"]},

    {"id": "request_buka_nilai", "name": "REQUEST_BUKA_NILAI",
     "pk": "id_request",
     "fks": ["id_guru", "id_kelas", "id_mapel", "id_tahun_ajaran"],
     "attrs": ["alasan", "status", "akses_sampai"]},

    {"id": "master_referensi", "name": "MASTER_REFERENSI",
     "pk": "id_referensi",
     "fks": ["id_mapel", "id_kelas", "id_tahun_ajaran"],
     "attrs": ["jenis", "legacy_id",
               "nama_dimensi", "urutan",
               "nama", "deskripsi_default", "wajib",
               "nama_template", "isi_template", "kategori",
               "fase", "semester", "predikat", "deskripsi",
               "nama_tema", "narasi_pembuka", "aktif"]},

    {"id": "nilai_aktivitas", "name": "NILAI_AKTIVITAS",
     "pk": "id_aktivitas",
     "fks": ["id_siswa", "id_tahun_ajaran", "id_ekskul", "id_tema", "id_dimensi"],
     "attrs": ["jenis", "keterangan", "subdimensi", "level"]},
]

relations = [
    # USERS
    {"src": "users", "src_card": "one", "dst": "kelas",
     "dst_card": "zeromany", "label": "menjadi_wali"},
    {"src": "users", "src_card": "one", "dst": "mapel_kelas",
     "dst_card": "many", "label": "mengajar"},
    {"src": "users", "src_card": "one", "dst": "request_buka_nilai",
     "dst_card": "many", "label": "mengajukan"},

    # TAHUN_AJARAN
    {"src": "tahun_ajaran", "src_card": "one", "dst": "siswa",
     "dst_card": "many", "label": "terdaftar_pada"},
    {"src": "tahun_ajaran", "src_card": "one", "dst": "kkm",
     "dst_card": "many", "label": "berlaku_pada"},
    {"src": "tahun_ajaran", "src_card": "one", "dst": "nilai",
     "dst_card": "many", "label": "dinilai_pada"},
    {"src": "tahun_ajaran", "src_card": "one", "dst": "rapor",
     "dst_card": "many", "label": "rapor_pada"},

    # KELAS
    {"src": "kelas", "src_card": "one", "dst": "siswa",
     "dst_card": "many", "label": "menampung"},
    {"src": "kelas", "src_card": "one", "dst": "mapel_kelas",
     "dst_card": "many", "label": "memiliki"},
    {"src": "kelas", "src_card": "one", "dst": "kkm",
     "dst_card": "many", "label": "target_KKM"},

    # MATA_PELAJARAN
    {"src": "mata_pelajaran", "src_card": "one", "dst": "mapel_kelas",
     "dst_card": "many", "label": "diajarkan_di"},
    {"src": "mata_pelajaran", "src_card": "one", "dst": "kkm",
     "dst_card": "many", "label": "berstandar"},
    {"src": "mata_pelajaran", "src_card": "one", "dst": "nilai",
     "dst_card": "many", "label": "dinilai"},

    # SISWA
    {"src": "siswa", "src_card": "one", "dst": "nilai",
     "dst_card": "many", "label": "mendapat"},
    {"src": "siswa", "src_card": "one", "dst": "rapor",
     "dst_card": "many", "label": "memiliki"},
    {"src": "siswa", "src_card": "one", "dst": "nilai_aktivitas",
     "dst_card": "many", "label": "mengikuti"},

    # MASTER_REFERENSI relasi
    {"src": "master_referensi", "src_card": "one", "dst": "nilai_aktivitas",
     "dst_card": "many", "label": "kategori_aktivitas"},
    {"src": "mata_pelajaran", "src_card": "one", "dst": "master_referensi",
     "dst_card": "many", "label": "punya_CP"},
    {"src": "kelas", "src_card": "one", "dst": "master_referensi",
     "dst_card": "many", "label": "tema_P5_per_kelas"},
    {"src": "tahun_ajaran", "src_card": "one", "dst": "master_referensi",
     "dst_card": "many", "label": "tema_P5_per_TA"},

    # NILAI_AKTIVITAS extras
    {"src": "tahun_ajaran", "src_card": "one", "dst": "nilai_aktivitas",
     "dst_card": "many", "label": "ekskul_pada_TA"},

    # REQUEST_BUKA_NILAI extras
    {"src": "tahun_ajaran", "src_card": "one", "dst": "request_buka_nilai",
     "dst_card": "many", "label": "request_pada_TA"},
    {"src": "kelas", "src_card": "one", "dst": "request_buka_nilai",
     "dst_card": "many", "label": "request_untuk_kelas"},
    {"src": "mata_pelajaran", "src_card": "one", "dst": "request_buka_nilai",
     "dst_card": "many", "label": "request_untuk_mapel"},
]

spec['erd'] = {
    "entities": entities,
    "relations": relations,
    "chen": True,
}

json.dump(spec, open('spec.json', 'w', encoding='utf-8'),
          indent=2, ensure_ascii=False)
print(f"ERD updated: {len(entities)} entities, {len(relations)} relations")
for e in entities:
    n_attr = 1 + len(e['fks']) + len(e['attrs'])  # PK + FKs + others
    print(f"  {e['id']:20} attr={n_attr:2}")
