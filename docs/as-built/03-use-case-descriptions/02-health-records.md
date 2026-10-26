# Dashboard dan Personal Health Records

## UC-DASH-01 — Melihat Dashboard Kesehatan

| Field | Specification |
|---|---|
| Use Case ID | UC-DASH-01 |
| Use Case Name | Melihat Dashboard Kesehatan |
| Goal | Melihat snapshot kesehatan dan tindakan berikutnya dari data milik sendiri. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada; seluruh agregasi dilakukan komponen internal. |
| Stakeholders and Interests | User membutuhkan ringkasan aktual; privacy membutuhkan owner scoping dan empty state yang jujur. |
| Trigger | User membuka `/dashboard`. |
| Preconditions | Session login dan email terverifikasi; role user. |
| Postconditions | Dashboard tampil; tidak ada record kesehatan baru yang dibuat hanya karena halaman dibuka. |
| Business Rules | Admin dialihkan ke `/admin`; perbandingan mingguan memakai periode user saat ini/sebelumnya; missing data tidak boleh direka. |
| Data Requirements | Profil, BMI, jurnal, meal/summary, goal, plan, notifikasi, dan aggregate owner-scoped yang tersedia. |
| Authorization and Privacy Considerations | Query berdasarkan authenticated User; data user lain tidak boleh muncul. |
| Related Backlog Items | RB-DASH-001 |
| Related Requirements | Middleware `auth`, `verified`, reverse-proxy scheme handling, dan dashboard service. |
| Related Test Cases | `DashboardHealthTest.php`; `RoleAuthorizationTest.php` |
| Implementation Evidence | `DashboardController`; `DashboardHealthService`; `dashboard.blade.php` |
| Assumptions and Open Questions | Makna klinis kartu/score tetap informasi wellness, bukan diagnosis. |

### Main Success Scenario

1. User meminta dashboard.
2. HEALO memverifikasi session, email, dan role.
3. HEALO mengambil data kesehatan milik user dari setiap modul terkait.
4. HEALO menghitung snapshot dan perbandingan mingguan.
5. HEALO menampilkan kartu, tren, preview plan, dan quick actions.

### Alternative Flows

- A1 — Data sumber belum lengkap; HEALO menampilkan empty state/ajakan mengisi data.
- A2 — Data plan hari ini ada; HEALO menampilkan plan tanpa membuat plan baru.

### Exception Flows

- E1 — Admin membuka dashboard user; HEALO mengalihkan ke dashboard admin.
- E2 — User belum login/terverifikasi; middleware mengalihkan ke auth/verification.

## UC-HPR-01 — Mengelola Profil Kesehatan

| Field | Specification |
|---|---|
| Use Case ID | UC-HPR-01 |
| Use Case Name | Mengelola Profil Kesehatan |
| Goal | Menyimpan konteks kesehatan personal dan consent untuk kalkulasi/personalization. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin data akurat; privacy membutuhkan consent, enkripsi, dan kepemilikan ketat. |
| Trigger | User membuka profil atau mengirim perubahan. |
| Preconditions | Web: login dan email verified; API: token Sanctum valid. |
| Postconditions | Satu HealthProfile milik user dibuat atau diperbarui. |
| Business Rules | Consent wajib; tanggal lahir/pengukuran harus masuk rentang; satu profil per user; client tidak menentukan owner. |
| Data Requirements | Tanggal lahir, gender, tinggi, berat, activity level, health goal, conditions, allergies, consent timestamp/flag. |
| Authorization and Privacy Considerations | Conditions/allergies encrypted at rest; policy/owner query; admin tanpa bypass. |
| Related Backlog Items | RB-HPR-001 |
| Related Requirements | `HealthProfileRequest`, service upsert, policy, encrypted casts. |
| Related Test Cases | `HealthProfileTest.php`; `PhaseTwoApiTest.php`; `PhaseTwoAuthorizationTest.php` |
| Implementation Evidence | health profile web/API controllers; `HealthProfileService`; `HealthProfile`; migration/policy/resource |
| Assumptions and Open Questions | Consent legal text/version tidak dimodelkan sebagai entitas terpisah pada implementasi ini. |

### Main Success Scenario

1. User membuka form/endpoint profil.
2. HEALO memuat profil milik user atau state kosong.
3. User mengirim data kesehatan dan consent.
4. HEALO menormalisasi serta memvalidasi nilai.
5. HEALO melakukan create/update profil milik user dan mengenkripsi field sensitif.
6. HEALO mengembalikan view/redirect atau resource profil.

### Alternative Flows

- A1 — Profil belum ada; service membuat satu record baru.
- A2 — Profil sudah ada; service memperbarui record yang sama.

### Exception Flows

- E1 — Consent tidak diberikan atau nilai tidak mungkin; HEALO menolak tanpa menyimpan.
- E2 — Request mencoba record user lain; owner scoping/policy menolak.

## UC-BMI-01 — Menghitung dan Mencatat BMI

| Field | Specification |
|---|---|
| Use Case ID | UC-BMI-01 |
| Use Case Name | Menghitung dan Mencatat BMI |
| Goal | Menghitung BMI server-side dan menyimpan pengukuran. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin hasil konsisten; aplikasi harus menolak nilai mustahil dan hasil buatan client. |
| Trigger | User mengirim tinggi, berat, dan tanggal ukur. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | BmiRecord owner-scoped tersimpan dengan BMI/kategori hasil kalkulasi server. |
| Business Rules | Berat/tinggi harus positif dan dalam rentang request; measured_at tidak boleh masa depan; BMI dibulatkan dan diklasifikasi service. |
| Data Requirements | Weight kg, height cm, measured_at; hasil BMI/category dihitung. |
| Authorization and Privacy Considerations | Owner berasal dari auth context; record privat; update profil hanya bila profil/consent mengizinkan. |
| Related Backlog Items | RB-BMI-001 |
| Related Requirements | `BmiStoreRequest`, `BmiCalculator`, `BmiRecordService`. |
| Related Test Cases | `BmiCalculatorTest.php`; `BmiFeatureTest.php`; `PhaseTwoApiTest.php` |
| Implementation Evidence | BMI web/API controllers; calculator/service/model/resource |
| Assumptions and Open Questions | Kategori BMI mengikuti rule aplikasi dan bukan penilaian klinis individual. |

### Main Success Scenario

1. User memasukkan pengukuran.
2. HEALO memvalidasi berat, tinggi, dan waktu.
3. BmiCalculator menghitung dan membulatkan BMI serta kategori.
4. BmiRecordService menyimpan record pada User.
5. Bila profil yang dapat diperbarui tersedia, service menyelaraskan pengukuran sesuai rule.
6. HEALO menampilkan record/riwayat terbaru.

### Alternative Flows

- A1 — HealthProfile tidak ada atau consent tidak memungkinkan; BMI tetap dicatat tanpa membuat profil otomatis.

### Exception Flows

- E1 — Nilai non-positif/tidak masuk rentang atau tanggal masa depan; validation error.
- E2 — Client mengirim hasil BMI sendiri; nilai tersebut diabaikan/tidak menjadi source of truth.

## UC-BMI-02 — Mengelola Riwayat BMI

| Field | Specification |
|---|---|
| Use Case ID | UC-BMI-02 |
| Use Case Name | Mengelola Riwayat BMI |
| Goal | Melihat detail/riwayat dan menghapus record BMI milik sendiri. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin histori terkontrol; privacy menuntut isolasi antar-user. |
| Trigger | User membuka daftar/detail atau memilih delete. |
| Preconditions | User terautentikasi; record untuk detail/delete ada pada scope user. |
| Postconditions | Riwayat ditampilkan atau record terpilih dihapus. |
| Business Rules | API menyediakan index/show/destroy; web menampilkan riwayat dan destroy; record asing tidak dapat ditemukan melalui owner query. |
| Data Requirements | BmiRecord: weight, height, BMI, category, measured_at. |
| Authorization and Privacy Considerations | Policy dan owner scoping; admin tidak memiliki bypass. |
| Related Backlog Items | RB-BMI-002 |
| Related Requirements | `BmiRecordPolicy` dan resource owner-scoped. |
| Related Test Cases | `BmiFeatureTest.php`; `PhaseTwoAuthorizationTest.php` |
| Implementation Evidence | BMI controllers; `BmiRecordPolicy`; `BmiRecordResource` |
| Assumptions and Open Questions | Update record BMI tidak tersedia; koreksi dilakukan dengan hapus/catat ulang. |

### Main Success Scenario

1. User meminta riwayat/detail.
2. HEALO mengambil hanya BmiRecord milik user.
3. HEALO menampilkan record terurut/terpilih.
4. Bila user memilih delete, HEALO memeriksa policy dan menghapus record.
5. HEALO mengembalikan daftar/resource yang mutakhir.

### Alternative Flows

- A1 — Riwayat kosong; HEALO menampilkan empty state.

### Exception Flows

- E1 — ID record milik user lain/tidak ada; HEALO mengembalikan not found/forbidden sesuai channel.

## UC-JRN-01 — Mengelola Jurnal Kesehatan

| Field | Specification |
|---|---|
| Use Case ID | UC-JRN-01 |
| Use Case Name | Mengelola Jurnal Kesehatan |
| Goal | Membuat, mencari, melihat, memperbarui, dan menghapus jurnal harian. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin catatan privat; insights membutuhkan data aktual tervalidasi. |
| Trigger | User membuka daftar/detail/form atau mengirim create/update/delete. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | Jurnal owner-scoped dibuat, diperbarui, ditampilkan, atau dihapus. |
| Business Rules | Tanggal dan metrik divalidasi; web mendukung filter search/mood/month; mutasi penting diaudit. |
| Data Requirements | Title, journal_date, mood, weight, blood pressure, sleep, water, exercise, serta entries terkait. |
| Authorization and Privacy Considerations | Owner query/policy; field sensitif memakai encrypted cast sesuai model; admin tanpa bypass. |
| Related Backlog Items | RB-JRN-001 |
| Related Requirements | `HealthJournalRequest`, journal service/policy, audit metadata aman. |
| Related Test Cases | `HealthJournalTest.php`; `PhaseTwoApiTest.php`; `PhaseTwoAuthorizationTest.php` |
| Implementation Evidence | `HealthJournalController` web/API; services/models/migrations/policy/resource |
| Assumptions and Open Questions | Skor wellness yang diturunkan dari jurnal bukan diagnosis klinis. |

### Main Success Scenario

1. User memilih membuat atau mengubah jurnal.
2. HEALO menampilkan data milik user/form.
3. User mengirim tanggal, mood, metrik, dan judul.
4. HEALO memvalidasi lalu service menyimpan pada user.
5. HEALO mencatat audit mutasi tanpa konten kesehatan.
6. HEALO menampilkan jurnal beserta entries atau resource.

### Alternative Flows

- A1 — User hanya mencari/filter; HEALO memaginasi jurnal tanpa mutasi.
- A2 — User memilih delete; policy diperiksa, audit dibuat, lalu record dihapus.

### Exception Flows

- E1 — Input/tanggal invalid; HEALO mengembalikan validation errors.
- E2 — Jurnal bukan milik user; query/policy menolak.

## UC-JRN-02 — Mengelola Entri Jurnal

| Field | Specification |
|---|---|
| Use Case ID | UC-JRN-02 |
| Use Case Name | Mengelola Entri Jurnal |
| Goal | Menambah, memperbarui, atau menghapus detail di dalam jurnal. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin detail terstruktur dan privat; sistem membutuhkan urutan/type yang valid. |
| Trigger | User mengirim create/update/delete entry. |
| Preconditions | User terautentikasi; jurnal/entry induk dimiliki user. |
| Postconditions | HealthJournalEntry berubah sesuai aksi. |
| Business Rules | Type harus enum yang didukung; content wajib/terbatas; owner diturunkan melalui journal; konten terenkripsi. |
| Data Requirements | Journal ID, entry type, content, urutan/metadata yang diizinkan. |
| Authorization and Privacy Considerations | Policy menelusuri kepemilikan journal; raw content tidak masuk audit. |
| Related Backlog Items | RB-JRN-002 |
| Related Requirements | `HealthJournalEntryRequest`, entry service/policy, encrypted content cast. |
| Related Test Cases | `HealthJournalTest.php`; `PhaseTwoApiTest.php`; `PhaseTwoAuthorizationTest.php` |
| Implementation Evidence | entry controllers/services/model/migration/policy/resource |
| Assumptions and Open Questions | Tidak ada sharing/collaboration entry. |

### Main Success Scenario

1. User membuka jurnal miliknya.
2. User memilih tipe dan memasukkan content entry.
3. HEALO memvalidasi journal owner, tipe, dan content.
4. Service menyimpan entry terenkripsi.
5. HEALO menampilkan jurnal/resource dengan entries terbaru.

### Alternative Flows

- A1 — User mengubah entry; HEALO mengupdate record yang sama.
- A2 — User menghapus entry; HEALO memeriksa policy lalu menghapusnya.

### Exception Flows

- E1 — Tipe/content invalid; request ditolak.
- E2 — Journal/entry user lain; not found/forbidden.

## UC-JRN-03 — Melihat Dashboard dan Tren Jurnal

| Field | Specification |
|---|---|
| Use Case ID | UC-JRN-03 |
| Use Case Name | Melihat Dashboard dan Tren Jurnal |
| Goal | Meninjau histori, metrik, skor, highlight, dan tren dari data jurnal/BMI sendiri. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Chart.js sebagai library UI, bukan sistem eksternal bisnis. |
| Stakeholders and Interests | User ingin tren mudah dipahami; sistem harus menunjukkan insufficient data dengan jujur. |
| Trigger | User membuka `/health-journals` atau menerapkan filter. |
| Preconditions | Login dan email verified. |
| Postconditions | Dashboard jurnal dan daftar terfilter tampil; tidak ada data domain berubah. |
| Business Rules | Periode dashboard 7–30 hari; score hanya dihitung dari highlight yang memiliki data; filter q/mood/month divalidasi. |
| Data Requirements | Jurnal, metrik harian, mood, BMI record, profil tinggi/usia untuk rule tertentu. |
| Authorization and Privacy Considerations | Seluruh query owner-scoped; grafik tidak memuat user lain. |
| Related Backlog Items | RB-JRN-003 |
| Related Requirements | `HealthJournalDashboardService` dan validation pada index controller. |
| Related Test Cases | `HealthJournalTest.php` |
| Implementation Evidence | `HealthJournalController::index`; dashboard service; journal dashboard partials |
| Assumptions and Open Questions | Threshold score adalah rule aplikasi dan perlu disclaimer UX yang konsisten. |

### Main Success Scenario

1. User membuka dashboard jurnal.
2. HEALO memvalidasi filter.
3. HEALO mengambil jurnal, metrik, dan BMI milik user dalam periode.
4. Service menghitung latest values, averages, score, highlights, action items, dan chart series.
5. HEALO menampilkan overview, tren, history, dan daftar jurnal terpaginasikan.

### Alternative Flows

- A1 — Filter diterapkan; hanya jurnal yang cocok tampil, sementara aggregate tetap memakai rule service yang ditentukan.
- A2 — Data tidak cukup; HEALO menampilkan score/summary not-ready dan tindakan pencatatan.

### Exception Flows

- E1 — Filter format invalid; validation error.

