"""Update spec.json: data_stores 17 -> 12 tabel + remap flows + bolak-balik per pair."""
import json

spec = json.load(open('spec.json'))

# === Replace data_stores with 12-tabel set ===
spec['data_stores'] = [
    {"id": "d_user",        "code": "D1",  "name": "users"},
    {"id": "d_ta",          "code": "D2",  "name": "tahun_ajaran"},
    {"id": "d_kelas",       "code": "D3",  "name": "kelas"},
    {"id": "d_mapel",       "code": "D4",  "name": "mata_pelajaran"},
    {"id": "d_mapel_kelas", "code": "D5",  "name": "mapel_kelas"},
    {"id": "d_siswa",       "code": "D6",  "name": "siswa"},
    {"id": "d_kkm",         "code": "D7",  "name": "kkm"},
    {"id": "d_nilai",       "code": "D8",  "name": "nilai"},
    {"id": "d_rapor",       "code": "D9",  "name": "rapor"},
    {"id": "d_request",     "code": "D10", "name": "request_buka_nilai"},
    {"id": "d_master_ref",  "code": "D11", "name": "master_referensi"},
    {"id": "d_aktivitas",   "code": "D12", "name": "nilai_aktivitas"},
]

# === ID remap untuk flows ===
remap = {
    "d_master_cp":      "d_master_ref",
    "d_master_ekskul":  "d_master_ref",
    "d_master_catatan": "d_master_ref",
    "d_master_dimensi": "d_master_ref",
    "d_tema_koko":      "d_master_ref",
    "d_siswa_ekskul":   "d_aktivitas",
    "d_siswa_koko":     "d_aktivitas",
}

# === Label remap (data_master_X / info_master_X -> data_master_referensi / info_master_referensi)
# Untuk konsistensi semantik DFD: flow ke STI table pakai label generik.
label_remap = {
    "data_master_ekskul":      "data_master_referensi",
    "data_master_catatan":     "data_master_referensi",
    "data_master_dimensi":     "data_master_referensi",
    "data_master_cp":          "data_master_referensi",
    "data_master":             "data_master_referensi",
    "data_tema_koko":          "data_master_referensi",
    "data_template_capaian":   "data_master_referensi",
    "info_master_ekskul":      "info_master_referensi",
    "info_master_catatan":     "info_master_referensi",
    "info_master_dimensi":     "info_master_referensi",
    "info_master_cp":          "info_master_referensi",
    "info_master":             "info_master_referensi",
    "info_tema_koko":          "info_master_referensi",
    "info_template_capaian":   "info_master_referensi",
    "info_template_catatan":   "info_master_referensi",
    "info_template_band":      "info_master_referensi",
    "info_predikat":           "info_master_referensi",
    "data_siswa_ekskul":       "data_aktivitas",
    "data_siswa_koko":         "data_aktivitas",
    "info_siswa_ekskul":       "info_aktivitas",
    "info_siswa_koko":         "info_aktivitas",
}

def remap_flow(f):
    """Remap src/dst dan optionally label."""
    f = dict(f)
    f['src'] = remap.get(f['src'], f['src'])
    f['dst'] = remap.get(f['dst'], f['dst'])
    if f.get('label') in label_remap:
        f['label'] = label_remap[f['label']]
    return f

def dedupe(flows):
    """Hapus flow duplikat persis (src,dst,label)."""
    seen = set()
    out = []
    for f in flows:
        key = (f['src'], f['dst'], f['label'])
        if key not in seen:
            seen.add(key)
            out.append(f)
    return out

# Apply remap
spec['context']['flows'] = dedupe([remap_flow(f) for f in spec['context']['flows']])
spec['level0']['flows']  = dedupe([remap_flow(f) for f in spec['level0']['flows']])
for lvl1 in spec.get('level1', []):
    lvl1['flows'] = dedupe([remap_flow(f) for f in lvl1['flows']])

# === Bolak-balik per (proc, store) ===
new_stores_ids = {s['id'] for s in spec['data_stores']}
procs0 = {p['id'] for p in spec['level0']['processes']}
store_name = {s['id']: s['name'] for s in spec['data_stores']}

def bidirectionalize(flows, procs, stores):
    pairs = {}
    for f in flows:
        s, d = f['src'], f['dst']
        if s in procs and d in stores:
            pairs.setdefault((s, d), {'w': False, 'r': False})['w'] = True
        elif s in stores and d in procs:
            pairs.setdefault((d, s), {'w': False, 'r': False})['r'] = True
    added = 0
    for (p, st), dirs in list(pairs.items()):
        sname = stores[st] if isinstance(stores, dict) else store_name[st]
        if not dirs['w']:
            flows.append({'src': p, 'dst': st, 'label': f'data_{sname}'})
            added += 1
        if not dirs['r']:
            flows.append({'src': st, 'dst': p, 'label': f'info_{sname}'})
            added += 1
    return added

n0 = bidirectionalize(spec['level0']['flows'], procs0, new_stores_ids)
print(f"Level 0: +{n0} reverse flows, total {len(spec['level0']['flows'])}")

for lvl1 in spec.get('level1', []):
    sub_procs = {p['id'] for p in lvl1['processes']}
    n = bidirectionalize(lvl1['flows'], sub_procs, new_stores_ids)
    if n:
        print(f"Level 1 ({lvl1['parent_no']}): +{n}, total {len(lvl1['flows'])}")

# Update struktur_tabel kalau tidak include master_referensi + nilai_aktivitas
existing_table_names = {t['nama'] for t in spec.get('struktur_tabel', [])}
add_tables = []
if 'master_referensi' not in existing_table_names:
    add_tables.append({
        "nama": "master_referensi",
        "header": ["Field", "Tipe", "Keterangan"],
        "rows": [
            ["id_referensi", "INT UNSIGNED", "Primary Key, Auto Increment"],
            ["jenis", "ENUM(dimensi, ekskul, template, cp, koko_tema)", "Diskriminator subtype"],
            ["legacy_id", "INT UNSIGNED", "PK lama (sebelum konsolidasi) — repoint anchor"],
            ["nama_dimensi", "VARCHAR(150)", "Khusus dimensi P5"],
            ["urutan", "INT(2)", "Urutan tampilan dimensi"],
            ["nama", "VARCHAR(100)", "Khusus ekstrakurikuler"],
            ["deskripsi_default", "TEXT", "Khusus ekstrakurikuler"],
            ["wajib", "TINYINT(1)", "1 = wajib (Pramuka)"],
            ["nama_template", "VARCHAR(100)", "Khusus template catatan"],
            ["isi_template", "TEXT", "Isi template + placeholder {nama_panggilan}"],
            ["kategori", "ENUM('positif','perlu_perbaikan','netral')", "Khusus template"],
            ["id_mapel", "INT UNSIGNED", "Khusus CP"],
            ["fase", "ENUM('A','B','C')", "Khusus CP"],
            ["semester", "ENUM('Ganjil','Genap')", "Khusus CP / koko_tema"],
            ["predikat", "ENUM('A','B','C','D')", "Khusus CP band template"],
            ["deskripsi", "TEXT", "Khusus CP narasi"],
            ["nama_tema", "VARCHAR(200)", "Khusus kokurikuler_tema"],
            ["id_kelas", "INT UNSIGNED", "Khusus kokurikuler_tema"],
            ["id_tahun_ajaran", "INT UNSIGNED", "Khusus kokurikuler_tema"],
            ["narasi_pembuka", "TEXT", "Khusus kokurikuler_tema"],
            ["aktif", "TINYINT(1)", "Status keaktifan baris"]
        ],
        "widths_cm": [4.5, 5.0, 6.0]
    })
if 'nilai_aktivitas' not in existing_table_names:
    add_tables.append({
        "nama": "nilai_aktivitas",
        "header": ["Field", "Tipe", "Keterangan"],
        "rows": [
            ["id_aktivitas", "INT UNSIGNED", "Primary Key, Auto Increment"],
            ["jenis", "ENUM('ekskul','koko')", "Diskriminator polymorphic"],
            ["id_siswa", "INT UNSIGNED", "Foreign Key ke siswa (CASCADE)"],
            ["id_tahun_ajaran", "INT UNSIGNED", "Khusus ekskul"],
            ["id_ekskul", "INT UNSIGNED", "Soft ref ke master_referensi (ekskul)"],
            ["keterangan", "TEXT", "Khusus ekskul"],
            ["id_tema", "INT UNSIGNED", "Soft ref ke master_referensi (koko_tema)"],
            ["id_dimensi", "INT UNSIGNED", "Soft ref ke master_referensi (dimensi)"],
            ["subdimensi", "VARCHAR(200)", "Khusus koko"],
            ["level", "ENUM('berkembang','cakap','mahir','sangat_mahir')", "Khusus koko"]
        ],
        "widths_cm": [4.5, 5.0, 6.0]
    })
spec.setdefault('struktur_tabel', [])
spec['struktur_tabel'].extend(add_tables)

# Update ERD entities: drop 7 lama, tambah 2 baru
new_erd_entities = []
drop_ids = {'master_cp', 'master_ekskul', 'master_catatan', 'master_dimensi',
            'kokurikuler_tema', 'siswa_ekskul', 'siswa_koko'}
for e in spec.get('erd', {}).get('entities', []):
    if e['id'] not in drop_ids:
        new_erd_entities.append(e)

if spec.get('erd'):
    spec['erd']['entities'] = new_erd_entities

json.dump(spec, open('spec.json', 'w'), indent=2, ensure_ascii=False)
print(f"\nFinal: data_stores={len(spec['data_stores'])}, struktur_tabel={len(spec['struktur_tabel'])}")
print(f"L0 flows={len(spec['level0']['flows'])}")
