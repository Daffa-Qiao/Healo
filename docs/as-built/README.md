# Dokumentasi As-Built HEALO

Dokumentasi ini direkonstruksi dari implementasi aktual HEALO. Setiap klaim harus dapat ditelusuri ke source code, skema, konfigurasi aman, atau pengujian. Dokumen lama diperlakukan sebagai referensi, bukan sebagai bukti implementasi.

## Status pengerjaan

| Fase | Status | Artefak |
|---|---|---|
| 0 — Audit | Disetujui | [`00-audit/`](00-audit/) |
| 1 — Feature Inventory | Disetujui | [`01-feature-inventory/`](01-feature-inventory/) |
| 2 — Backlog Mapping | Disetujui | [`02-backlog-mapping/`](02-backlog-mapping/) |
| 3 — Use Case Descriptions | Disetujui | [`03-use-case-descriptions/`](03-use-case-descriptions/) |
| 4 — Use Case Diagrams | Disetujui | [`04-use-case-diagrams/`](04-use-case-diagrams/) |
| 5 — Class Diagrams | Disetujui | [`05-class-diagrams/`](05-class-diagrams/) |
| 6 — Activity Diagrams | Selesai, menunggu persetujuan | [`06-activity-diagrams/`](06-activity-diagrams/) |
| 7 — Cross-Document Validation | Belum dimulai | Menunggu persetujuan Fase 6 |

## Konvensi status

- **CONFIRMED**: didukung implementasi dan/atau pengujian yang dapat ditunjukkan.
- **INFERRED**: disimpulkan dari struktur kode, tetapi belum dikonfirmasi penuh.
- **PLANNED**: tertulis dalam dokumen rencana, tetapi implementasinya belum terkonfirmasi.
- **NOT FOUND**: tidak ditemukan setelah sumber yang relevan diperiksa.
- **NEEDS VALIDATION**: memerlukan keputusan pemilik produk atau pengujian manual tambahan.

## Batas dokumentasi

- Aktor bisnis awal yang ditemukan adalah **User** dan **Admin**.
- Provider AI adalah sistem pendukung, bukan aktor manusia dan bukan pengganti diagnosis medis.
- Tidak ada secret, token, API key, password, atau data kesehatan pribadi yang disalin ke dokumentasi.
- Perubahan aplikasi yang sudah ada tidak diubah oleh pekerjaan audit ini.

