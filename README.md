# Pack Generator BAB IV — Diagram DFD/ERD + Dokumen Word (Universal)

Pack ini membuat **BAB IV skripsi** (sistem informasi) yang rapi secara otomatis:
Diagram Konteks (input/output **selaras 1:1**), DFD Level 0, DFD Level 1 per
proses, **ERD crow's foot + notasi Chen**, **wireframe lite** untuk 4.4,
**screenshot asli** untuk 4.5, **Black Box** untuk 4.6, **SUS otomatis** untuk 4.7 — semuanya
dirangkum dalam **satu `BAB_IV.docx`** dengan heading auto-number + caption SEQ.

Dirancang untuk dipakai di **Claude Code**: tempel megaprompt, biarkan agen
membaca kode project-mu, mengisi `spec.json`, menyiapkan screenshot, lalu
menjalankan generator.

## Isi pack

```
README.md                              ← file ini
MEGAPROMPT_BAB4_DIAGRAM_UNIVERSAL.md   ← tempel ini ke Claude Code (di root project)
spec.schema.md                         ← dokumentasi tiap field spec.json
spec.example.json                      ← contoh terisi penuh (ChelisNet)
contoh/                                ← OUTPUT NYATA siap-lihat (BabIvAssets + BAB_IV.docx)
skills/
  bab4-diagrams/                       ← skill: library + generator
    SKILL.md
    build_babiv_assets.py              ← orchestrator: spec.json → BabIvAssets/
    babiv_docx.py                      ← generator Word (heading styles + SEQ, 4.1-4.7)
    capture_screenshots.py             ← tangkap screenshot 4.5 via Playwright
    verify_diagrams.py                 ← cek keseimbangan tiap PNG (anti "kanan atas kosong")
    requirements.txt
    spec.example.json   data.xlsx      ← contoh + contoh data SUS
    babiv_diagrams/                    ← engine layout + render
      model.py drawio.py graphml.py render.py __init__.py
      gvlayout.py                      ← layout via Graphviz (posisi + waypoint)
      sus.py                           ← hitung System Usability Scale (4.7)
      mockup.py                        ← wireframe lite (4.4) via Playwright
    vendor/viewer-static.min.js        ← draw.io viewer offline (tanpa internet)
```

## Cara pakai cepat (manual)

```bash
# OPSIONAL: graphviz (DFD level sedikit lebih rapi). Linux: sudo apt-get install -y graphviz | Windows: winget install graphviz
pip install python-docx playwright openpyxl pillow --break-system-packages
python -m playwright install chromium

cd skills/bab4-diagrams
python build_babiv_assets.py spec.example.json --out ./out
# hasil: ./out/BabIvAssets/  (context, dfd 0, dfd 1.X, erd, mockup 4.4, BAB_IV.docx)
```

## Cara pakai di Claude Code

1. Buka project skripsi-mu (mis. project CodeIgniter 4) di Claude Code.
2. Letakkan folder `skills/bab4-diagrams/` di project (atau di `.claude/skills/`).
3. Untuk 4.7: ekspor jawaban kuesioner SUS jadi `data.xlsx` dan taruh di project.
4. Tempel isi `MEGAPROMPT_BAB4_DIAGRAM_UNIVERSAL.md` sebagai instruksi.
5. Agen akan: audit kode → isi `spec.json` → render wireframe 4.4 → tangkap
   screenshot 4.5 → black box 4.6 → SUS 4.7 → jalankan generator → verify_diagrams.py.
6. Buka `BAB_IV.docx`, tekan **Ctrl+A → F9** untuk update semua nomor.

## Prinsip desain (kenapa hasilnya rapi)

- Kamu/agen hanya mendeklarasikan **logika** (proses, entitas, aliran, mockup).
  Posisi node dihitung **Graphviz** (minim persilangan, tak menembus kotak) → DFD
  **ortogonal tanpa label tumpang-tindih**, ERD **crow's foot** + **Chen**.
- Render pakai **engine draw.io asli** (dibundel offline) → PNG bisa dilihat &
  diverifikasi sebelum dipakai.
- 4.4 wireframe lite (LOGO teks, kotak gambar bersilang X, data dummy) digenerate
  otomatis; 4.5 screenshot asli (capture_screenshots.py); 4.6 black box per fitur; 4.7 SUS dari `data.xlsx`.
- Dokumen Word pakai **heading styles + field SEQ** → penomoran heading & caption
  tidak pernah bolong (memperbaiki bug "Gambar 4.19 → 4.22" versi lama), dan
  penomoran list (1/2/3 + a/b/c) menjorok benar (tidak meluber keluar halaman).

## Aturan penting

- Jangan menulis koordinat XML atau nomor gambar/tabel dengan tangan.
- Label aliran: input `data_xxx`, output `info_xxx` (snake_case).
- Dekomposisi **semua** proses utama ke Level 1 (folder `dfd 1.1, 1.2, …`).
- Maksimal **8 proses per level** (aturan DFD 7±2).
- **Tiap node (eksternal/proses/store) wajib punya aliran masuk DAN keluar** (divalidasi otomatis).
- **graphviz OPSIONAL** — ERD Chen & semua diagram tetap jalan tanpa graphviz (pakai layout bawaan).
- ERD pakai kata kunci kardinalitas (`one`/`many`/`mandone`/`zeromany`).
- 4.4 = wireframe (`mockup`), 4.5 = screenshot asli (`image`), 4.6 = black box (`pengujian.blackbox`), 4.7 = SUS (`sus.xlsx`).
- Semua bagian (4.1–4.7) masuk **satu** `BAB_IV.docx`. Sel tabel rata kiri (tanpa tab).

Detail lengkap: lihat `MEGAPROMPT_BAB4_DIAGRAM_UNIVERSAL.md`, `spec.schema.md`,
dan `skills/bab4-diagrams/SKILL.md`.
