# Fase 1 — Feature Inventory

Tanggal penyusunan: **26 Oktober 2026**  
Dasar audit: commit `e531d710e26cd6e7ffbf6e911dc1f2b6498ec47e` beserta kondisi working tree yang telah dicatat pada Fase 0.

## Tujuan

Fase ini mengubah kandidat fitur Fase 0 menjadi daftar capability dan workflow yang dapat diamati. Setiap item mempunyai ID inventaris, aktor/initiator, channel, status, bukti implementasi, bukti test, dan hasil yang dapat diamati.

ID `FI-*` adalah **Feature Inventory ID**, bukan Backlog ID atau Use Case ID. ID backlog dan use case baru akan ditentukan pada fase masing-masing agar tidak mencampur artefak.

## Artefak

1. [Inventaris fitur terkonfirmasi](feature-inventory.md)
2. [Batas workflow dan channel](workflow-boundaries.md)
3. [Gap, pertanyaan, dan rencana Fase 2](validation-and-next-step.md)

## Ringkasan

- **49 capability/workflow CONFIRMED** diinventarisasi.
- Aktor use case tetap hanya **User** dan **Admin**.
- AI Provider, scheduler, queue, database, dan realtime infrastructure dicatat sebagai komponen pendukung, bukan aktor use case.
- Web dan API diperlakukan sebagai channel berbeda dari capability bisnis yang sama, kecuali lifecycle token API yang memang spesifik channel.
- Enam kelompok fitur lama tetap **PLANNED** dan dua kelompok integrasi eksternal tetap **NOT FOUND**.
- Fitur dengan implementasi jelas tetapi coverage test langsung belum spesifik diberi catatan coverage parsial; status implementasinya tidak dinaikkan melebihi bukti kode.

## Gerbang fase

Fase 1 selesai setelah artefak ini direview. Backlog mapping, use case specification, dan diagram belum dibuat pada fase ini.

