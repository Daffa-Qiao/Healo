# Insights, Goals, dan Health Plan

## UC-INS-01 — Melihat Ringkasan Health Insights

| Field | Specification |
|---|---|
| Use Case ID | UC-INS-01 |
| Use Case Name | Melihat Ringkasan Health Insights |
| Goal | Melihat overview lintas BMI, nutrition, journal, AI usage, dan goals untuk periode terpilih. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada untuk overview/rule insights. |
| Stakeholders and Interests | User ingin gambaran aktual; privacy membutuhkan aggregate owner-scoped. |
| Trigger | User membuka insights dengan periode 7, 30, atau 90 hari. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | Overview dan rule-based insights tampil; source records tidak berubah. |
| Business Rules | Periode lain dinormalisasi ke 7; averages hanya dari hari tercatat; empty data tidak direka; rule bersifat non-diagnostik. |
| Data Requirements | BMI, meals/items, journal/entries, nutrition target, AI counts, active/paused goals. |
| Authorization and Privacy Considerations | Semua query melalui relasi User; foreign user identifier diabaikan. |
| Related Backlog Items | RB-INS-001 |
| Related Requirements | `HealthInsightsRequest`, `HealthInsightsService::overview/ruleInsights`. |
| Related Test Cases | `HealthInsightsServiceTest.php`; `PhaseFiveApiTest.php`; `PhaseFiveAuthorizationTest.php` |
| Implementation Evidence | insights web/API controllers; service/context/resources |
| Assumptions and Open Questions | Rule threshold adalah aturan wellness aplikasi, bukan rekomendasi klinis. |

### Main Success Scenario

1. User memilih periode.
2. HEALO memvalidasi/menormalisasi periode.
3. Service mengambil data owner-scoped pada rentang tersebut.
4. Service menghitung BMI change, averages nutrisi, journal activity, AI usage, dan goal progress.
5. Rule engine menghasilkan insight cautious.
6. HEALO menampilkan/mengembalikan overview terstruktur.

### Alternative Flows

- A1 — Data sebagian tersedia; HEALO hanya menghitung indikator yang punya sumber.
- A2 — Tidak ada data; HEALO memberikan getting-started/empty guidance.

### Exception Flows

- E1 — Auth invalid; request ditolak.

## UC-INS-02 — Melihat Tren dan Ringkasan Mingguan

| Field | Specification |
|---|---|
| Use Case ID | UC-INS-02 |
| Use Case Name | Melihat Tren dan Ringkasan Mingguan |
| Goal | Meninjau seri waktu kesehatan dan struktur ringkasan tujuh hari. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Chart.js hanya untuk rendering UI. |
| Stakeholders and Interests | User ingin perubahan terlihat jelas; sistem harus mempertahankan tanggal/data kosong secara jujur. |
| Trigger | User membuka halaman insights atau endpoint trends/weekly. |
| Preconditions | User terautentikasi; periode valid/ternormalisasi. |
| Postconditions | Tren/ringkasan tampil tanpa mutasi domain. |
| Business Rules | Labels dibentuk per hari; mood dikonversi ke score 1–5 hanya untuk grafik; weekly selalu hari ini dan enam hari sebelumnya. |
| Data Requirements | BMI/weight dates, meal macro totals, journal frequency/mood, goals dan overview mingguan. |
| Authorization and Privacy Considerations | Seri hanya dari record User; tidak ada identifier asing dari client sebagai scope. |
| Related Backlog Items | RB-INS-002 |
| Related Requirements | `HealthInsightsService::trends/weekly`. |
| Related Test Cases | `HealthInsightsServiceTest.php`; `PhaseFiveApiTest.php`; `DashboardHealthTest.php` |
| Implementation Evidence | insights service/controllers/resource/view |
| Assumptions and Open Questions | Missing data tampil null/zero sesuai seri dan tidak diinterpolasi. |

### Main Success Scenario

1. User meminta tren/ringkasan.
2. HEALO mengambil record owner-scoped untuk periode.
3. Service membuat label tanggal serta seri BMI, weight, calories, macro, journal frequency, dan mood.
4. Service membentuk weekly structure dari overview tujuh hari.
5. HEALO menampilkan grafik atau JSON.

### Alternative Flows

- A1 — Hari tertentu tanpa data; seri mempertahankan hari dengan nilai kosong/zero sesuai metrik.

### Exception Flows

- E1 — Request period tidak didukung; service memakai periode default, bukan menjalankan rentang bebas.

## UC-INS-03 — Mendapatkan Personalized AI Insight

| Field | Specification |
|---|---|
| Use Case ID | UC-INS-03 |
| Use Case Name | Mendapatkan Personalized AI Insight |
| Goal | Mendapat insight personal dari aggregate minimal dengan safety/fallback. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | HealthAIProvider Groq/LocalDemo. |
| Stakeholders and Interests | User ingin insight relevan; privacy ingin tanpa identity/raw journal; safety ingin output non-diagnostik. |
| Trigger | Halaman/endpoint insights meminta personalized result. |
| Preconditions | User terautentikasi; health-ai rate limit tersedia. |
| Postconditions | Hasil AI tervalidasi atau fallback lokal dikembalikan dan dapat di-cache 15 menit. |
| Business Rules | Context hanya aggregate; cache key per user/period/locale/provider/model/context; diagnosis/dosis/secret output ditolak. |
| Data Requirements | Minimized overview context, provider payload, summary, insights, recommendations, disclaimer, provider/model/fallback flag. |
| Authorization and Privacy Considerations | Tidak mengirim nama/email/user ID/raw journal/API key; cache dipisah per user. |
| Related Backlog Items | RB-INS-003 |
| Related Requirements | context service, parser, provider contract, medical safety, throttle/cache. |
| Related Test Cases | `HealthInsightsServiceTest.php`; `HealthInsightsSafetyTest.php`; `PhaseFiveAIPrivacyTest.php`; `PhaseFiveApiTest.php`; `GroqHealthAIProviderTest.php` |
| Implementation Evidence | `HealthInsightsService::personalized`; context/parser/safety/provider classes; configs |
| Assumptions and Open Questions | Live Groq belum divalidasi end-to-end di production. |

### Main Success Scenario

1. HEALO menghitung overview User.
2. Context service mengubah overview menjadi aggregate minimal.
3. HEALO mencari cache per user/context.
4. Bila cache miss, provider menghasilkan payload terstruktur.
5. Parser dan MedicalSafetyService memvalidasi output.
6. HEALO menyimpan cache dan menampilkan summary/recommendations/disclaimer.

### Alternative Flows

- A1 — Cache hit; HEALO mengembalikan hasil tervalidasi tanpa provider call baru.
- A2 — Provider lokal aktif; deterministic result dipakai.

### Exception Flows

- E1 — Timeout/error/schema invalid/unsafe output; HEALO mencatat warning aman dan memakai rule-based fallback.
- E2 — Rate limit; request ditolak sementara.

## UC-GOAL-01 — Mengelola Health Goal

| Field | Specification |
|---|---|
| Use Case ID | UC-GOAL-01 |
| Use Case Name | Mengelola Health Goal |
| Goal | Membuat, melihat, memperbarui, dan menghapus target kesehatan personal. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin target jelas; sistem membutuhkan type/range/date valid dan owner tetap. |
| Trigger | User membuka goals atau mengirim create/update/delete. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | HealthGoal owner-scoped dibuat, diubah, ditampilkan, atau dihapus. |
| Business Rules | Type/status enum; target/current/range dan tanggal divalidasi; client user ID diabaikan; owner/type tertentu tidak bebas diubah. |
| Data Requirements | Goal type, target value/unit, start/target date, status, metadata. |
| Authorization and Privacy Considerations | Policy/owner query; metadata encrypted; admin tanpa bypass. |
| Related Backlog Items | RB-GOAL-001 |
| Related Requirements | goal create/update requests, service, policy/resource. |
| Related Test Cases | `HealthGoalFeatureTest.php`; `PhaseFiveApiTest.php`; `PhaseFiveAuthorizationTest.php` |
| Implementation Evidence | goal web/API controllers; service/model/migration/policy/resource |
| Assumptions and Open Questions | Tidak ada social/shared goals. |

### Main Success Scenario

1. User membuka daftar/form goal.
2. User memilih type, target, unit, tanggal, dan status.
3. HEALO memvalidasi rule per type.
4. HEALO menyimpan goal pada authenticated User.
5. HEALO menampilkan goal beserta progress terhitung.

### Alternative Flows

- A1 — User mengubah goal yang dimiliki; HEALO menyimpan field yang diizinkan.
- A2 — User menghapus goal; policy diperiksa lalu record dihapus.

### Exception Flows

- E1 — Range/date/type invalid; request ditolak.
- E2 — Goal milik user lain; not found/forbidden.

## UC-GOAL-02 — Memantau Progress Health Goal

| Field | Specification |
|---|---|
| Use Case ID | UC-GOAL-02 |
| Use Case Name | Memantau Progress Health Goal |
| Goal | Melihat kemajuan target dari source record aktual. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin progress yang tidak dapat dipalsukan client; sistem membutuhkan formula konsisten. |
| Trigger | User membuka goal/dashboard/insights. |
| Preconditions | Goal dimiliki user; source data mungkin tersedia atau belum. |
| Postconditions | Progress structure/percentage ditampilkan; source records tidak berubah. |
| Business Rules | Weight goal memakai BMI/weight source; frequency goal dapat diturunkan dari record; percentage di-clamp; completed dapat diturunkan sesuai rule. |
| Data Requirements | Goal definition, latest/relevant BMI, meal/journal activity, periode dan target. |
| Authorization and Privacy Considerations | Goal dan source owner-scoped; progress input client tidak dipercaya. |
| Related Backlog Items | RB-GOAL-002 |
| Related Requirements | `HealthGoalService::progress`. |
| Related Test Cases | `HealthGoalServiceTest.php`; `PhaseFiveApiTest.php` |
| Implementation Evidence | goal service; dashboard/insights/goal controllers/views/resources |
| Assumptions and Open Questions | Formula per goal type mengikuti implementation saat ini dan perlu dijelaskan sebagai rule aplikasi. |

### Main Success Scenario

1. User membuka tampilan yang memuat progress.
2. HEALO mengambil goal dan source records milik user.
3. Service menghitung current value, percentage, dan completion state.
4. Service membatasi percentage pada range aman.
5. HEALO menampilkan progress.

### Alternative Flows

- A1 — Source data belum ada; progress menampilkan state awal/zero sesuai service.

### Exception Flows

- E1 — Goal/source bukan milik user; request ditolak oleh scope/policy.

## UC-PLAN-01 — Membuat dan Melihat Health Plan Harian

| Field | Specification |
|---|---|
| Use Case ID | UC-PLAN-01 |
| Use Case Name | Membuat dan Melihat Health Plan Harian |
| Goal | Memiliki satu rencana wellness harian yang diturunkan dari data aktual. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada untuk task generation inti. |
| Stakeholders and Interests | User ingin aktivitas praktis; sistem membutuhkan generation idempotent dan progress server-side. |
| Trigger | User membuka plan/today atau menekan generate. |
| Preconditions | User terautentikasi; generate menerima tanggal sesuai request rules (API today only). |
| Postconditions | Satu HealthPlan per user/date dengan task tersinkron dan progress konsisten, atau empty state bila hanya view tanpa generate. |
| Business Rules | Prevent daily duplicates; task identity type/source reference; client tidak boleh mengirim owner/progress/status terhitung. |
| Data Requirements | Plan date/status/progress/generated_at; profil, nutrition, meal, journal, BMI, goal; task definitions. |
| Authorization and Privacy Considerations | Plan/task owner-scoped; admin tanpa bypass; context personalization terpisah/minimal. |
| Related Backlog Items | RB-PLAN-001 |
| Related Requirements | generate request, plan service/generator/progress service, policy/resource. |
| Related Test Cases | `HealthPlanFeatureTest.php`; `HealthPlanServiceTest.php`; `PhaseSixApiTest.php`; `PhaseSixAuthorizationTest.php` |
| Implementation Evidence | plan web/API controllers; services/models/migrations/policies/resources |
| Assumptions and Open Questions | Plan adalah wellness plan, bukan treatment plan klinis. |

### Main Success Scenario

1. User meminta generate plan hari ini.
2. HEALO memvalidasi tanggal dan auth context.
3. Service mencari plan user/date atau membuatnya.
4. Generator membaca data aktual dan menghasilkan task definitions.
5. Service membuat/memperbarui task secara idempotent.
6. Progress service menyinkronkan data-derived tasks dan menghitung progress.
7. HEALO menampilkan plan, metrics, dan context pendukung.

### Alternative Flows

- A1 — User hanya membuka halaman dan plan belum ada; web menampilkan empty state tanpa auto-generate.
- A2 — Plan sudah ada; generate menyinkronkan, bukan membuat duplikat.

### Exception Flows

- E1 — API generate meminta tanggal selain today atau field terhitung; validation error.
- E2 — Plan user lain; scope/policy menolak.

## UC-PLAN-02 — Mengelola Penyelesaian Task Harian

| Field | Specification |
|---|---|
| Use Case ID | UC-PLAN-02 |
| Use Case Name | Mengelola Penyelesaian Task Harian |
| Goal | Menandai task manual selesai/belum selesai tanpa memalsukan task data-derived. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin progress responsif; integrity membutuhkan server menentukan completion. |
| Trigger | User menekan complete atau uncomplete. |
| Preconditions | Task berada pada plan milik user; completion mode manual. |
| Postconditions | Status/timestamp task manual dan progress/status plan dihitung ulang. |
| Business Rules | Data-derived task tidak dapat diubah manual; request tidak menerima progress/owner; percentage dihitung server. |
| Data Requirements | Task ID, completion mode/status/completed_at, plan tasks dan progress. |
| Authorization and Privacy Considerations | Policy menelusuri plan owner; admin tanpa bypass. |
| Related Backlog Items | RB-PLAN-002 |
| Related Requirements | complete/uncomplete requests, plan task controller/service/progress policy. |
| Related Test Cases | `HealthPlanServiceTest.php`; `HealthPlanFeatureTest.php`; `PhaseSixApiTest.php`; `PhaseSixAuthorizationTest.php` |
| Implementation Evidence | task web/API controllers; plan/progress services; task model/policy/resource |
| Assumptions and Open Questions | Tidak ada bulk-complete endpoint. |

### Main Success Scenario

1. User memilih task manual miliknya.
2. HEALO memvalidasi ownership dan melarang field terhitung.
3. Service memastikan completion mode manual.
4. Service mengubah status dan completed_at.
5. Progress service menghitung ulang plan.
6. HEALO mengembalikan plan/progress terbaru.

### Alternative Flows

- A1 — User memilih uncomplete; status kembali pending dan completed_at null.
- A2 — Source data berubah; synchronize memperbarui data-derived task tanpa aksi manual.

### Exception Flows

- E1 — Task data-derived dicoba complete; validation error.
- E2 — Task user lain; forbidden/not found.

## UC-PLAN-03 — Melihat Personalisasi Daily Wellness

| Field | Specification |
|---|---|
| Use Case ID | UC-PLAN-03 |
| Use Case Name | Melihat Personalisasi Daily Wellness |
| Goal | Melihat summary, suggestion, dan motivation aman untuk plan yang ada. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | HealthAIProvider Groq/LocalDemo. |
| Stakeholders and Interests | User ingin motivasi kontekstual; privacy ingin aggregate minimal; safety menolak diagnosis/resep. |
| Trigger | User membuka Health Plan yang sudah ada. |
| Preconditions | Login, email verified, dan plan hari ini tersedia. |
| Postconditions | Personalisasi tervalidasi/fallback tampil; task inti tidak diubah provider. |
| Business Rules | Provider hanya memengaruhi wording/motivation; data identity/raw journal tidak dikirim; unsafe/invalid/failure fallback. |
| Data Requirements | Aggregate plan/progress/nutrition/BMI/goals/journal presence; personalization result dan disclaimer. |
| Authorization and Privacy Considerations | Aggregate owner-only; tidak ada identity/API key/raw journal content pada provider context. |
| Related Backlog Items | RB-PLAN-003 |
| Related Requirements | `HealthPlanPersonalizationService`, provider contract, medical safety. |
| Related Test Cases | `PhaseSixAIPrivacyTest.php`; `GroqHealthAIProviderTest.php` |
| Implementation Evidence | `HealthPlanController::index`; personalization/context/provider/safety services |
| Assumptions and Open Questions | Channel API belum menyediakan personalization result terpisah saat audit. |

### Main Success Scenario

1. User membuka plan yang tersedia.
2. HEALO menghitung metrics dan aggregate context minimal.
3. Provider menghasilkan personalization payload.
4. HEALO memvalidasi schema dan medical safety.
5. HEALO menampilkan summary, suggestion, motivation, dan disclaimer.

### Alternative Flows

- A1 — Provider lokal aktif; deterministic personalization dipakai.
- A2 — Plan belum ada; personalisasi tidak dipanggil dan empty state tampil.

### Exception Flows

- E1 — Provider gagal/invalid/unsafe; HEALO memakai fallback lokal tanpa raw exception.

