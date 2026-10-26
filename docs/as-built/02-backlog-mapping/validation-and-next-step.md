# Validasi Fase 2 dan Rencana Fase 3

## 1. Hasil validasi mapping

| Pemeriksaan | Hasil |
|---|---|
| Reconstructed backlog ID | 49 ID, seluruhnya unik |
| Planned backlog ID | 6 ID, seluruhnya unik |
| Use Case ID | 43 ID, seluruhnya unik |
| Coverage Feature Inventory | 49 dari 49 `FI-*` ditemukan pada mapping |
| Referential integrity UC | 43 UC pada mapping sama persis dengan 43 UC pada katalog |
| Aktor use case | Hanya User dan Admin |
| Kontrol internal | Audit logging dan cleanup tidak dipaksa menjadi use case aktor |
| Planned vs implemented | Dipisahkan secara eksplisit |
| External integrations | Ditandai NOT FOUND dan tidak dibuat use case |
| Secret/PHI | Tidak disalin ke dokumen |

`git diff --check` tetap digunakan sebagai pemeriksaan format repository. Baseline implementasi tetap hasil Fase 0: **169 test lulus dengan 1.414 assertion**.

## 2. Keputusan pemetaan penting

1. `FI-AUTH-07` dan `FI-ACC-01` menggunakan `UC-ACC-01` karena pembacaan profil API adalah channel dari goal profil akun yang sama.
2. Safety assistant `FI-AST-03` menjadi bagian `UC-AST-02`, bukan use case mandiri.
3. Emergency override `FI-SYM-04` menjadi alur alternatif `UC-SYM-01`, bukan use case mandiri.
4. Reminder generation dan delivery `FI-NOT-03`/`FI-NOT-04` menggunakan `UC-NOT-03` karena goal user sama: menerima notifikasi sesuai preferensi.
5. Audit logging dan cleanup foto tetap backlog internal tanpa Use Case ID karena tidak mempunyai interaksi atau goal aktor User/Admin.
6. Fitur planned tidak diberi Use Case ID as-built agar tidak terlihat seolah telah tersedia.

## 3. Gap yang dibawa ke fase berikutnya

| Gap | Status | Perlakuan Fase 3 |
|---|---|---|
| Tidak ada backlog asli/product owner priority | **NEEDS VALIDATION** | Gunakan reconstructed backlog tanpa mengklaim prioritas bisnis asli |
| Rekomendasi menu personal memiliki coverage test parsial | **NEEDS VALIDATION** | Nyatakan bukti kode kuat tetapi test langsung belum spesifik |
| Live AI/provider production belum divalidasi | **NEEDS VALIDATION** | Dokumentasikan provider failure/fallback dan batas environment |
| Planned admin/reporting belum ada implementasi | **PLANNED** | Tidak dibuat use case description as-built kecuali sebagai gap |
| Workflow background tidak punya aktor langsung | **CONFIRMED** sebagai internal | Jelaskan sebagai trigger/system response pada UC terkait, bukan aktor baru |

## 4. Pertanyaan review

1. Apakah 43 Use Case ID pada katalog disetujui sebagai baseline untuk seluruh dokumen berikutnya?
2. Apakah fitur planned cukup dipertahankan pada mapping/gap tanpa dibuat use case description?
3. Apakah nama `Healbot` ingin dipertahankan, atau diseragamkan menjadi `Health Assistant` pada semua dokumen berikutnya?
4. Apakah istilah campuran seperti Health Goal, Health Plan, dan Health Insights mengikuti label UI saat ini, atau perlu diterjemahkan penuh pada dokumentasi?

Jika tidak ada koreksi, default Fase 3 adalah mempertahankan nama fitur sesuai UI/source saat ini dan menulis spesifikasi hanya untuk 43 use case **CONFIRMED**.

## 5. Rencana Fase 3 — Use Case Descriptions

Untuk setiap Use Case ID, Fase 3 akan memuat:

- Use Case ID dan Name;
- Goal, Scope, dan Level;
- Primary Actor serta supporting component yang relevan;
- Stakeholders and Interests;
- Trigger dan Preconditions;
- Main Success Scenario langkah demi langkah;
- Alternative dan Exception Flows;
- Postconditions;
- Business Rules dan Data Requirements;
- Authorization and Privacy Considerations;
- Related Backlog Items, Requirements, dan Test Cases;
- Implementation Evidence;
- Assumptions and Open Questions.

Urutan penyusunan akan mengikuti domain pada katalog. Tindakan User/Admin, respons HEALO, dan respons AI Provider akan dibedakan dengan jelas. Fase 3 berhenti untuk review sebelum diagram use case dibuat.

