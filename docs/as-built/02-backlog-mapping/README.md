# Fase 2 — Product Backlog to Use Case Mapping

Tanggal penyusunan: **26 Oktober 2026**

## Dasar rekonstruksi

Backlog asli dengan ID formal tidak ditemukan di repository. Karena itu seluruh item implemented menggunakan ID berawalan `RB-` dan diberi label **RECONSTRUCTED**. Item yang hanya berasal dari roadmap memakai awalan `PB-` dan status **PLANNED**.

## Artefak

1. [Product Backlog to Use Case Mapping](product-backlog-to-use-case-mapping.md)
2. [Katalog Use Case ID](use-case-catalog.md)
3. [Validasi dan rencana Fase 3](validation-and-next-step.md)

## Ringkasan pemetaan

- 49 item backlog reconstructed berstatus **CONFIRMED**.
- 47 item capability user-facing dipetakan ke **43 Use Case ID** yang unik.
- Dua item confirmed merupakan kontrol/proses internal lintas fitur dan sengaja tidak dijadikan use case aktor.
- Enam item backlog lama dipertahankan sebagai **PLANNED** tanpa Use Case ID as-built.
- Dua kelompok integrasi eksternal tetap **NOT FOUND** dan diletakkan sebagai exclusion audit.
- Aktor use case hanya **User** dan **Admin**.

## Aturan ID

- `RB-<EPIC>-NNN`: reconstructed backlog item.
- `PB-<EPIC>-NNN`: planned backlog item dari dokumen lama.
- `UC-<DOMAIN>-NN`: use case stabil untuk fase deskripsi dan diagram.
- `FI-<DOMAIN>-NN`: referensi kembali ke Feature Inventory Fase 1.

## Gerbang fase

Fase 2 berhenti setelah mapping dan katalog ID direview. Use case description belum ditulis pada fase ini.

