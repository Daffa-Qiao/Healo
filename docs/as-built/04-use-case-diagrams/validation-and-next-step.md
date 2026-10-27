# Validasi Fase 4 dan Rencana Fase 5

## Ringkasan hasil

| Pemeriksaan | Hasil |
|---|---|
| Katalog Fase 2 | 43 ID unik |
| ID pada enam diagram domain | 43 ID unik |
| ID katalog yang tidak tergambar | 0 |
| ID diagram yang tidak ada di katalog | 0 |
| Aktor yang dideklarasikan | User, Admin |
| Aktor teknis/eksternal yang keliru | 0 |
| Source PlantUML berhasil diproses | 7 dari 7 |
| Hasil render | 7 SVG dan 7 PNG |
| Pemeriksaan visual | Seluruh diagram terbaca; Admin ditempatkan di sisi kanan pada diagram yang melibatkannya |

## Cakupan per domain

| Domain | Rentang use case | Jumlah |
|---|---|---:|
| Public, Authentication, Preferences & Account | UC-PUB, UC-AUTH, UC-PREF, UC-ACC | 10 |
| Dashboard & Personal Health Records | UC-DASH, UC-HPR, UC-BMI, UC-JRN | 7 |
| Nutrition, Meal, Food Analysis & Healthy Menu | UC-NUT, UC-MEAL, UC-FOOD, UC-MENU | 9 |
| Healbot & Symptom Checker | UC-AST, UC-SYM | 5 |
| Insights, Goals & Daily Wellness Plan | UC-INS, UC-GOAL, UC-PLAN | 8 |
| Notifications & Administration | UC-NOT, UC-ADM | 4 |
| **Total** |  | **43** |

## Relasi UML yang digunakan

| Relasi | Semantik as-built |
|---|---|
| UC-JRN-02 `extend` UC-JRN-01 | Pengelolaan detail entri terjadi ketika User memilih bagian entri dari jurnal. |
| UC-MEAL-02 `extend` UC-MEAL-01 | Pengelolaan item terjadi dalam konteks catatan makan yang dipilih. |
| UC-FOOD-02 `extend` UC-FOOD-01 | Review tersedia setelah proses scan menghasilkan draft yang siap ditinjau. |
| UC-FOOD-03 `extend` UC-FOOD-01 | Draft dapat dikelola setelah draft scan tersedia. |
| UC-FOOD-02 `include` UC-MEAL-01 | Konfirmasi yang sukses wajib membuat catatan makan terkonfirmasi. |
| UC-FOOD-02 `include` UC-MEAL-02 | Konfirmasi yang sukses wajib menyimpan item dan nilai nutrisi yang telah dikoreksi. |
| UC-SYM-03 `extend` UC-SYM-02 | Assessment dapat ditambahkan ke jurnal ketika assessment tersimpan dipilih. |
| UC-INS-03 `extend` UC-INS-01 | Personalized insight hanya dijalankan ketika User memintanya. |
| UC-GOAL-02 `extend` UC-GOAL-01 | Pemantauan progress dilakukan pada goal yang dipilih. |
| UC-PLAN-02 `extend` UC-PLAN-01 | Penyelesaian task dikelola ketika User memilih task manual. |
| UC-PLAN-03 `extend` UC-PLAN-01 | Personalisasi ditampilkan ketika plan harian tersedia. |

Relasi lain tidak dipaksakan. Autentikasi, validasi request, authorization, provider call, penyimpanan, dan pengiriman notifikasi dipertahankan sebagai langkah internal di dalam use case description bila tidak membentuk goal aktor tersendiri.

## Keputusan dan batasan

- Overview dipertahankan ringkas agar dapat berfungsi sebagai peta navigasi; diagram domain adalah sumber visual lengkap.
- Provider AI tidak dijadikan aktor. Interaksi provider akan terlihat sebagai swimlane komponen hanya pada activity diagram yang relevan di Fase 6.
- Fitur admin berstatus PLANNED/NOT FOUND tidak ditampilkan pada diagram as-built.
- Kontrol audit logging dan cleanup foto terjadwal tidak diberi Use Case ID karena tidak memiliki goal langsung bagi User/Admin pada implementasi yang diaudit.
- Tidak ada secret, token, prompt sensitif, atau data kesehatan pribadi yang dimasukkan ke artefak.

## Gap dan pertanyaan manusia

Tidak ada gap yang menghalangi diagram use case. Keputusan produk yang masih terbuka dari fase sebelumnya tetap dicatat di dokumen asal dan tidak diubah menjadi fitur baru pada diagram ini.

## Rencana Fase 5 — Class Diagrams

Setelah Fase 4 disetujui, Fase 5 akan:

1. merekonstruksi domain model dari class aktual dan migration/foreign key;
2. memisahkan diagram domain model dari application/service layer agar tetap terbaca;
3. mendokumentasikan attribute, operation, inheritance, interface realization, association, dan multiplicity hanya bila dapat dibuktikan;
4. membuat overview dan diagram per domain dalam PlantUML;
5. merender serta memvalidasi diagram terhadap source code dan schema.

**Gate:** jangan mulai Fase 5 sebelum Fase 4 disetujui.

