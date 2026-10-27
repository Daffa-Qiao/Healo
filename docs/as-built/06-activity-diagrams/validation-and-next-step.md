# Validasi Fase 6 dan Rencana Fase 7

## Hasil validasi struktur

| Pemeriksaan | Hasil |
|---|---|
| Activity diagram source | 21 PlantUML |
| Hasil render | 21 SVG dan 21 PNG |
| Diagram dengan initial node | 21 dari 21 |
| Diagram dengan final node | 21 dari 21 |
| Diagram dengan decision/guard | 21 dari 21 |
| Diagram dengan swimlane | 21 dari 21 |
| Marker syntax/runtime error | 0 |
| Use case katalog yang dipetakan | 43 dari 43 |
| Use case tanpa workflow | 0 |

## Validasi tanggung jawab lane

| Lane | Tanggung jawab yang diperbolehkan |
|---|---|
| User | Input, pilihan, koreksi, konfirmasi, dan melihat hasil |
| Admin | Login Admin dan melihat dashboard agregat |
| HEALO Application | Validation, authorization, safety, perhitungan, orchestration, parsing, dan response |
| Database | Query dan persistence sesuai model/migration aktual |
| Mail Transport | Pengiriman tautan verifikasi/reset |
| Food Analysis Provider | Estimasi makanan dan nilai nutrisi terstruktur |
| Health AI Provider | Respons Healbot, symptom assessment, insight, dan personalisasi plan |
| Scheduler / Queue / Realtime | Penjadwalan reminder, queue job, delivery claim, dan private broadcast |

Lane komponen bukan aktor tambahan pada use case diagram. Aktor bisnis tetap hanya **User** dan **Admin**.

## Pemeriksaan workflow berisiko tinggi

- Food analysis tidak membuat meal ketika scan. Draft harus masuk status review, seluruh nilai memiliki label, User dapat mengoreksi, dan Meal baru dibuat setelah konfirmasi valid.
- Healbot memeriksa emergency, self-harm, diagnosis, dosis, serta prompt injection sebelum provider dan memeriksa output sesudah provider.
- Symptom checker menjalankan red-flag detection sebelum provider dan menggunakan fallback aman jika provider/schema gagal.
- Assessment hanya dipindahkan ke jurnal setelah konfirmasi eksplisit dan operasi idempoten bila journal link sudah ada.
- Notification disimpan durable sebelum broadcast; quiet hours menggeser `available_at` dan kegagalan realtime tidak menghilangkan record.
- Admin dashboard hanya membaca aggregate dan tidak memiliki jalur ke konten kesehatan privat.

## Gap dan batasan

- Kualitas/latency provider live tetap membutuhkan validasi environment bercredential; diagram hanya menggambarkan contract dan fallback yang ada.
- Pengiriman mail, scheduler, queue worker, dan Reverb di production bergantung pada proses infrastruktur aktif.
- Diagram tidak mengekspos secret, raw provider response, password, token, atau data kesehatan pribadi.

Tidak ada gap yang menghalangi validasi silang akhir.

## Rencana Fase 7 — Cross-Document Validation

Setelah Fase 6 disetujui, Fase 7 akan memeriksa:

1. konsistensi 43 Use Case ID di backlog mapping, description, use case diagram, dan activity matrix;
2. konsistensi nama dan aktor User/Admin;
3. keterlacakan workflow ke controller, service, model, migration, policy, dan test;
4. kesesuaian entity/class dengan activity persistence;
5. relasi `include`/`extend`, guard, exception path, serta AI safety boundary;
6. daftar gap final, status CONFIRMED/INFERRED/PLANNED/NOT FOUND/NEEDS VALIDATION;
7. indeks final seluruh lima deliverable.

**Gate:** jangan mulai Fase 7 sebelum Fase 6 disetujui.

