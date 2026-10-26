# Verifikasi, Batasan, Gap, dan Rencana Fase 1

## 1. Metode verifikasi yang dilakukan

| Metode | Hasil | Kegunaan |
|---|---|---|
| Inventaris filesystem dengan `rg --files` | Berhasil | Menghitung dan mengelompokkan artefak repository |
| Inspeksi route dengan `php artisan route:list --json --except-vendor` | 149 route | Memastikan endpoint terdaftar, middleware, controller, dan HTTP method |
| Inspeksi kode | Berhasil | Memeriksa controller, service, policy, model, contract, job, event, command, konfigurasi, view, dan frontend |
| Inspeksi migration | 26 migration | Memastikan tabel/kolom berasal dari skema aktual, bukan ERD lama |
| Inspeksi test | 51 feature test dan 7 unit test | Memetakan klaim ke coverage yang tersedia |
| Eksekusi `php artisan test` | **169 passed, 1.414 assertions** | Memverifikasi happy path, validasi, otorisasi, privacy, fallback, API, queue, broadcast, dan wiring yang dicakup test |
| Pencarian integrasi eksternal | Tidak ditemukan implementasi | Mencegah asumsi rumah sakit/EMR/EHR/BPJS/SATUSEHAT/wearable |
| Pencarian PDF/export | Hanya dependensi DomPDF ditemukan | Memisahkan dependensi dari workflow yang benar-benar tersedia |
| Perbandingan dokumen lama dengan kode | Ditemukan beberapa discrepancy | Mencegah roadmap/ERD lama dianggap sebagai as-built |

## 2. Batasan audit

1. Audit otomatis dijalankan di environment `local`, bukan deployment produksi.
2. Test menggunakan provider fake/local untuk skenario deterministik. Koneksi live Groq, kuota, latency, dan perilaku model produksi belum diuji end-to-end.
3. UI belum diuji manual lintas browser, viewport, keyboard navigation, atau screen reader dalam Fase 0.
4. Scheduler, worker queue, dan Reverb diperiksa melalui kode/test; proses daemon produksi dan konfigurasi supervisor belum divalidasi.
5. Konfigurasi database/SMTP/storage produksi, backup, retention operasional, dan observability deployment berada di luar bukti repository yang aman.
6. Tidak ada wawancara product owner pada Fase 0, sehingga business goal dan prioritas backlog belum dianggap final.
7. Lulusnya test tidak membuktikan seluruh kombinasi alur, copy UI, atau integrasi eksternal live.
8. Working tree telah memiliki perubahan pada file nutrisi/food analysis sebelum penyusunan audit. Perubahan tersebut tidak diubah atau dibatalkan; test dijalankan pada kondisi itu.

## 3. Gap dan risiko dokumentasi

| Gap | Status | Dampak ke fase berikutnya |
|---|---|---|
| Backlog asli dengan ID/story formal tidak ditemukan | **NEEDS VALIDATION** | Bila tidak tersedia, Fase 2 harus memakai backlog **RECONSTRUCTED** |
| Roadmap menyebut route/fitur admin yang tidak ada pada route aktual | **PLANNED** | Hanya dashboard admin agregat yang boleh masuk as-built confirmed |
| ERD lama memuat entitas yang tidak mempunyai migration/model aktual | **PLANNED** / **NOT FOUND** | Class diagram harus bersumber dari model dan migration aktual |
| Paket DomPDF terpasang tanpa controller/route/test export | **NOT FOUND** | Jangan membuat use case export sebagai fitur selesai |
| Acceptance criteria dan business priority formal tidak tersimpan dalam repo | **NEEDS VALIDATION** | Perlu direkonstruksi atau diberikan product owner |
| Bukti UAT/manual test belum tersedia | **NEEDS VALIDATION** | Status implementasi dapat confirmed-by-automation, bukan accepted-by-user |
| Deployment topology produksi belum didokumentasikan | **NEEDS VALIDATION** | Tidak menghalangi use case, tetapi membatasi klaim operasional |
| Redaksi disclaimer AI pada seluruh layar belum diaudit manual | **NEEDS VALIDATION** | Safety rule didukung kode/test; konsistensi UX perlu review manual |

## 4. Pertanyaan untuk keputusan manusia

1. Apakah ada backlog asli di luar repository yang memiliki ID, prioritas, dan user story? Jika tidak, backlog pada Fase 2 akan diberi label **RECONSTRUCTED**.
2. Apakah item Fase 8–9 pada `docs/implementation-roadmap.md` masih aktif sebagai rencana, atau sudah dibatalkan?
3. Apakah API v1 perlu didokumentasikan sebagai use case terpisah, atau sebagai channel alternatif dari use case bisnis yang sama?
4. Apakah diperlukan sesi validasi manual UI/live AI sebelum status akhir dokumen dinyatakan siap UAT?

## 5. Rencana Fase 1 — Feature Inventory

Jika Fase 0 disetujui, Fase 1 akan:

1. memecah setiap kandidat fitur menjadi capability dan workflow yang dapat diamati;
2. memberi ID inventaris fitur sementara dan status `CONFIRMED`, `INFERRED`, `PLANNED`, `NOT FOUND`, atau `NEEDS VALIDATION`;
3. menautkan setiap fitur ke route, controller, service, model/migration, UI/API, policy, dan test;
4. membedakan workflow web, API, background job, dan AI boundary tanpa menggandakan business capability;
5. mencatat actor, trigger, input, output, data owner, authorization, privacy, dan error/fallback;
6. menyusun traceability matrix awal sebagai dasar Fase 2;
7. menyajikan gap dan pertanyaan baru untuk persetujuan sebelum masuk Fase 2.

Fase 1 tidak akan mengubah source code, database, migration, atau business logic.

