# Healbot dan Symptom Checker

## UC-AST-01 — Mengelola Percakapan Healbot

| Field | Specification |
|---|---|
| Use Case ID | UC-AST-01 |
| Use Case Name | Mengelola Percakapan Healbot |
| Goal | Membuat, mendaftar, membuka, dan menghapus conversation milik sendiri. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada provider AI yang diperlukan untuk lifecycle conversation. |
| Stakeholders and Interests | User ingin riwayat privat; privacy menuntut enkripsi dan penghapusan relasi yang benar. |
| Trigger | User membuka Healbot atau memilih create/show/delete conversation. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | Conversation dibuat, ditampilkan, atau dihapus beserta messages terkait. |
| Business Rules | Title opsional menggunakan default; title terenkripsi; list/detail hanya milik user. |
| Data Requirements | Conversation ID, user ID, title, last_message_at, messages/assessments terkait. |
| Authorization and Privacy Considerations | Owner query dan `HealthConversationPolicy`; admin tanpa bypass; title/message encrypted. |
| Related Backlog Items | RB-AST-001 |
| Related Requirements | Conversation request, service, policy, resource. |
| Related Test Cases | `HealthAssistantFeatureTest.php`; `PhaseFourApiTest.php`; `PhaseFourAuthorizationTest.php`; `PhaseFourPrivacyTest.php` |
| Implementation Evidence | conversation web/API controllers; `HealthAssistantService`; models/migrations/policies/resources |
| Assumptions and Open Questions | Tidak ada sharing conversation atau recovery setelah delete. |

### Main Success Scenario

1. User membuka Healbot.
2. HEALO menampilkan daftar conversation milik user.
3. User membuat conversation dengan title opsional.
4. HEALO memvalidasi dan menyimpan conversation terenkripsi.
5. HEALO membuka conversation dan message history.

### Alternative Flows

- A1 — Title kosong; HEALO menggunakan nama conversation default.
- A2 — User memilih delete; policy diperiksa lalu conversation dan messages terkait dihapus.

### Exception Flows

- E1 — Title invalid; request ditolak.
- E2 — Conversation user lain/tidak ada; not found/forbidden.

## UC-AST-02 — Berkonsultasi dengan Healbot

| Field | Specification |
|---|---|
| Use Case ID | UC-AST-02 |
| Use Case Name | Berkonsultasi dengan Healbot |
| Goal | Mengirim pertanyaan dan menerima jawaban informasi kesehatan yang melewati safety boundary. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | HealthAIProvider aktif: Groq atau LocalDemo; provider bukan aktor use case. |
| Stakeholders and Interests | User ingin jawaban berguna; safety ingin emergency/diagnosis/dosis/injection ditangani deterministik. |
| Trigger | User mengirim pesan pada conversation. |
| Preconditions | Conversation milik user; pesan valid; limit health-ai (default 10/menit) tersedia. |
| Postconditions | Pesan User dan respons Assistant yang aman tersimpan terenkripsi; last_message_at diperbarui. |
| Business Rules | Rule lokal berjalan sebelum provider; history dibatasi config (default 12); provider context diminimalkan; output unsafe/failure diganti. |
| Data Requirements | Message content, recent history, minimized context, provider/model/confidence/safety metadata. |
| Authorization and Privacy Considerations | Conversation owner-only; content/metadata encrypted; identity/API key tidak dikirim ke frontend/context; audit tidak menyimpan content. |
| Related Backlog Items | RB-AST-002; RB-AST-003 |
| Related Requirements | `HealthMessageRequest`, context service, parser, `MedicalSafetyService`, provider contract, throttle. |
| Related Test Cases | `HealthAssistantFeatureTest.php`; `PhaseFourApiTest.php`; `PhaseFourAuthorizationTest.php`; `PhaseFourPrivacyTest.php`; `GroqHealthAIProviderTest.php` |
| Implementation Evidence | message controllers; `HealthAssistantService`; context/parser/safety/provider services; configs |
| Assumptions and Open Questions | Live provider quality/latency memerlukan validasi environment; Healbot bukan pengganti tenaga medis. |

### Main Success Scenario

1. User mengirim pesan ke conversation miliknya.
2. HEALO memvalidasi ownership, content, dan rate limit.
3. MedicalSafetyService memeriksa emergency, self-harm, diagnosis, dosis, dan prompt injection.
4. Bila tidak ada safety response lokal, HEALO membentuk minimized context dan recent history.
5. Provider menghasilkan payload; parser mengubahnya menjadi respons terstruktur.
6. MedicalSafetyService memeriksa kembali output provider.
7. Dalam transaction, HEALO menyimpan pesan User dan jawaban Assistant terenkripsi.
8. HEALO menampilkan jawaban beserta metadata aman/disclaimer yang relevan.

### Alternative Flows

- A1 — Input emergency/self-harm; HEALO langsung memakai emergency response lokal dan tidak membutuhkan provider.
- A2 — Permintaan diagnosis/dosis/injection; HEALO memberi boundary response lokal.
- A3 — Pesan pertama pada title default; title conversation diperbarui dari 70 karakter awal pesan.

### Exception Flows

- E1 — Provider/parser gagal; HEALO mencatat warning tanpa raw response dan menyimpan fallback aman.
- E2 — Output provider membuat diagnosis pasti; safety service menggantinya.
- E3 — Rate limit atau validation gagal; pesan tidak diproses.

## UC-SYM-01 — Memeriksa Gejala

| Field | Specification |
|---|---|
| Use Case ID | UC-SYM-01 |
| Use Case Name | Memeriksa Gejala |
| Goal | Mendapat hasil assessment terstruktur, cautious, dan aman dari deskripsi gejala. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | HealthAIProvider aktif bila tidak ada red flag lokal. |
| Stakeholders and Interests | User ingin arahan awal; safety ingin red flag dan referral diprioritaskan tanpa diagnosis. |
| Trigger | User mengirim deskripsi gejala serta pilihan save/tidak. |
| Preconditions | User terautentikasi; input memenuhi batas request; health-ai throttle tersedia. |
| Postconditions | Result ephemeral ditampilkan atau SymptomAssessment terenkripsi disimpan. |
| Business Rules | Emergency detection berjalan sebelum provider; schema/urgency wajib valid; referral gate aktif untuk urgency/confidence/specialty tertentu; save bersifat eksplisit. |
| Data Requirements | Input; symptoms/severity; possible categories; differential; red flags; urgency; recommendation; specialty/referral; confidence; provider/model. |
| Authorization and Privacy Considerations | Context owner-scoped/minimized; sensitive assessment fields encrypted; admin tanpa bypass; provider error tidak dibuka. |
| Related Backlog Items | RB-SYM-001; RB-SYM-004 |
| Related Requirements | `SymptomAssessmentRequest`, prompt/parser, emergency/safety services, provider contract. |
| Related Test Cases | `SymptomCheckerFeatureTest.php`; `EmergencyDetectionServiceTest.php`; `PhaseFourApiTest.php`; `PhaseFourAuthorizationTest.php`; `PhaseFourPrivacyTest.php`; `GroqHealthAIProviderTest.php` |
| Implementation Evidence | symptom web/API controllers; `SymptomAssessmentService`; prompt/context/parser/safety services; model/policy/resource |
| Assumptions and Open Questions | Phrase-based emergency rules tidak menggantikan layanan darurat atau evaluasi klinis. |

### Main Success Scenario

1. User mengisi deskripsi gejala dan opsi save.
2. HEALO membersihkan/menvalidasi input dan rate limit.
3. EmergencyDetectionService mencari red flags.
4. Bila aman, HEALO mengirim minimized context dan input ke provider.
5. Parser memvalidasi schema; referral gate menegakkan kebutuhan referral.
6. HEALO menampilkan summary, symptoms, categories/differential, red flags, urgency, specialty/referral, recommendation, confidence, dan disclaimer.
7. Bila save dipilih, HEALO menyimpan assessment terenkripsi pada user.

### Alternative Flows

- A1 — Save tidak dipilih; result hanya dikembalikan dan tidak masuk history.
- A2 — Red flag fisik/self-harm ditemukan; HEALO membuat emergency assessment lokal tanpa provider.
- A3 — Confidence rendah/urgency tinggi/specialty kritis; referral gate memastikan `referral_needed` benar.

### Exception Flows

- E1 — Provider timeout/error atau schema invalid; HEALO memakai validated fallback dengan urgency `soon`.
- E2 — Input invalid/throttle terlampaui; assessment tidak dibuat.

## UC-SYM-02 — Mengelola Riwayat Pemeriksaan Gejala

| Field | Specification |
|---|---|
| Use Case ID | UC-SYM-02 |
| Use Case Name | Mengelola Riwayat Pemeriksaan Gejala |
| Goal | Melihat daftar/detail dan menghapus assessment tersimpan milik sendiri. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin riwayat privat; privacy membutuhkan owner isolation. |
| Trigger | User membuka history/detail atau memilih delete. |
| Preconditions | User terautentikasi; assessment pernah disimpan untuk detail/delete. |
| Postconditions | History/detail tampil atau assessment terpilih dihapus. |
| Business Rules | Assessment ephemeral tidak muncul; history terpaginasikan sesuai controller; mutasi diaudit aman. |
| Data Requirements | Seluruh result assessment, created_at, journal link/saved timestamp. |
| Authorization and Privacy Considerations | Owner query/policy; encrypted fields didekripsi hanya untuk owner; admin tanpa bypass. |
| Related Backlog Items | RB-SYM-002 |
| Related Requirements | `SymptomAssessmentPolicy` dan resource owner-scoped. |
| Related Test Cases | `SymptomCheckerFeatureTest.php`; `PhaseFourAuthorizationTest.php`; `PhaseFourPrivacyTest.php` |
| Implementation Evidence | symptom history/show/destroy controllers; model/policy/resource |
| Assumptions and Open Questions | Tidak ada restore assessment setelah delete. |

### Main Success Scenario

1. User membuka history.
2. HEALO mengambil assessment tersimpan milik user.
3. User memilih satu assessment.
4. HEALO memeriksa owner/policy dan menampilkan detail terdekripsi.
5. Bila user memilih delete, HEALO mencatat audit aman dan menghapus assessment.

### Alternative Flows

- A1 — History kosong; HEALO menampilkan empty state.
- A2 — User hanya melihat tanpa menghapus; tidak ada mutasi.

### Exception Flows

- E1 — Assessment milik user lain/tidak ada; not found/forbidden.

## UC-SYM-03 — Menyimpan Pemeriksaan ke Jurnal

| Field | Specification |
|---|---|
| Use Case ID | UC-SYM-03 |
| Use Case Name | Menyimpan Pemeriksaan ke Jurnal |
| Goal | Menyalin assessment tersimpan ke jurnal hanya setelah konfirmasi eksplisit. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin reuse data; privacy ingin mencegah pemindahan diam-diam/duplikasi. |
| Trigger | User memilih save to journal dan mengirim confirmation. |
| Preconditions | Assessment tersimpan, dimiliki user, dan belum/atau sudah memiliki journal link. |
| Postconditions | HealthJournal dibuat dan assessment ditautkan, atau journal yang sudah ada dikembalikan idempoten. |
| Business Rules | `confirm` wajib accepted; operasi transaction; tanggal journal hari ini; pemanggilan ulang tidak membuat duplikat. |
| Data Requirements | Assessment input, symptoms, summary, recommendation, optional title; journal link dan saved timestamp. |
| Authorization and Privacy Considerations | Owner/policy; content tetap terenkripsi dalam journal/entry; audit tidak menyimpan isi. |
| Related Backlog Items | RB-SYM-003 |
| Related Requirements | `SaveAssessmentToJournalRequest`, `AssessmentJournalService`, journal service. |
| Related Test Cases | `SymptomCheckerFeatureTest.php`; `PhaseFourAuthorizationTest.php` |
| Implementation Evidence | symptom controllers `saveToJournal`; assessment journal service; model relations |
| Assumptions and Open Questions | User dapat mengedit jurnal hasil konversi melalui workflow jurnal biasa. |

### Main Success Scenario

1. User membuka assessment tersimpan.
2. User memilih save to journal dan memberi konfirmasi.
3. HEALO memvalidasi confirmation dan ownership.
4. Dalam transaction, service membuat HealthJournal dari complaint/symptoms/summary/recommendation.
5. HEALO mengisi health_journal_id dan saved_to_journal_at pada assessment.
6. HEALO mencatat audit aman dan membuka jurnal.

### Alternative Flows

- A1 — Assessment sudah memiliki journal; service mengembalikan journal yang sama tanpa duplikasi.
- A2 — User memberi title; service memakai title tersebut setelah validation.

### Exception Flows

- E1 — Confirmation tidak diberikan; request ditolak.
- E2 — Assessment bukan milik user; akses ditolak.

