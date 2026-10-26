# Validasi Fase 3 dan Rencana Fase 4

## 1. Hasil validasi use case descriptions

| Pemeriksaan | Hasil |
|---|---|
| Use case pada katalog Fase 2 | 43 |
| Use case yang memiliki description | 43 |
| Use Case ID unik | 43 |
| Selisih katalog vs description | 0 |
| Reconstructed backlog user-facing | 47 |
| Backlog user-facing yang direferensikan description | 47 |
| Selisih backlog mapping vs description | 0 |
| Elemen wajib per use case | 22 dari 22 ditemukan pada seluruh 43 spesifikasi |
| Primary actor | Hanya User dan Admin |
| Main/alternative/exception flow | Tersedia pada seluruh 43 use case |
| Evidence dan test reference | Tersedia pada seluruh 43 use case |
| Planned backlog dibuatkan description | Tidak; sesuai batas as-built |
| Internal control dibuat sebagai aktor/use case baru | Tidak |

Baseline verifikasi implementasi tetap **169 test lulus dengan 1.414 assertion** dari Fase 0. Fase 3 hanya menambah dokumentasi dan tidak mengubah aplikasi/database.

## 2. Konsistensi penting

- ID dan nama use case mengikuti `02-backlog-mapping/use-case-catalog.md` tanpa perubahan.
- `UC-AST-02` memuat safety diagnosis/dosis/prompt-injection/emergency sebagai subflow, bukan use case/aktor baru.
- `UC-SYM-01` menjalankan emergency detection sebelum provider dan memperlakukan save sebagai pilihan eksplisit.
- `UC-FOOD-01` hanya menghasilkan draft; `UC-FOOD-02` wajib menampilkan label nutrisi dan memerlukan koreksi/konfirmasi sebelum Meal dibuat.
- `UC-NOT-03` menggunakan User sebagai beneficiary/primary actor, sedangkan scheduler/queue/realtime tetap supporting components.
- `UC-ADM-01` hanya mencakup dashboard agregat; fitur admin planned tidak dimasukkan.
- Web dan API dijelaskan sebagai channel dari goal bisnis yang sama, dengan perbedaan session/redirect versus token/JSON.

## 3. Gap dan open questions

| Gap | Status | Dampak |
|---|---|---|
| Live Groq tidak diuji pada deployment produksi | **NEEDS VALIDATION** | Exception/fallback sudah didokumentasikan; SLA/latency tidak dapat diklaim |
| Mail delivery aktual tidak diaudit | **NEEDS VALIDATION** | Register/verification/reset code confirmed, delivery bergantung environment |
| Rekomendasi menu memakai allergen substring matching | **NEEDS VALIDATION** | Tidak boleh diklaim sebagai jaminan bebas alergen |
| Per-user timezone tidak ditemukan | **NOT FOUND** | Reminder mengikuti timezone aplikasi `Asia/Bangkok` |
| Purge permanen setelah soft-delete akun tidak ditemukan | **NEEDS VALIDATION** | UC-ACC-02 hanya mengklaim soft delete |
| Acceptance/UAT product owner belum tersedia | **NEEDS VALIDATION** | Status tetap confirmed-by-code/test, bukan accepted-by-UAT |
| Fitur admin/reporting dari roadmap | **PLANNED** | Tidak masuk description/diagram as-built confirmed |

## 4. Rencana Fase 4 — Use Case Diagrams

Fase 4 akan menghasilkan source PlantUML yang dapat diedit, dipisahkan per domain agar tidak padat:

1. overview system boundary dan dua aktor;
2. public/auth/account;
3. health records dan journal;
4. nutrition, meal, food analysis, dan healthy menu;
5. Healbot dan symptom checker;
6. insights, goals, dan health plan;
7. notification dan administration.

Aturan pemodelan:

- aktor hanya User dan Admin;
- AI Provider tidak digambar sebagai aktor;
- controller, service, database, scheduler, dan queue tidak dijadikan aktor;
- `include` hanya untuk behavior wajib/reusable yang benar-benar ditunjukkan alur;
- `extend` hanya untuk variasi opsional dengan extension point yang jelas;
- planned backlog tidak dimasukkan dalam system boundary as-built;
- setiap node use case memakai ID/nama dari katalog Fase 2.

Jika tool PlantUML tersedia, diagram akan dirender dan hasil render diverifikasi. Jika tidak tersedia, hanya source `.puml` yang akan diklaim selesai dan keterbatasan render dicatat secara eksplisit.

## 5. Pertanyaan review

1. Apakah 43 use case description ini disetujui sebagai baseline diagram?
2. Apakah diagram overview boleh menyederhanakan use case menjadi kelompok domain, sementara diagram domain menampilkan seluruh ID secara lengkap?
3. Apakah output visual yang diinginkan SVG, PNG, atau keduanya jika PlantUML tersedia?

Default yang direkomendasikan: overview ringkas, diagram domain lengkap, serta render SVG karena tetap tajam dan mudah ditautkan dari Markdown.

