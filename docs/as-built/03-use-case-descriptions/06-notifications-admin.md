# Notification dan Administration

## UC-NOT-01 — Mengelola Pusat Notifikasi

| Field | Specification |
|---|---|
| Use Case ID | UC-NOT-01 |
| Use Case Name | Mengelola Pusat Notifikasi |
| Goal | Melihat notifikasi sendiri, unread count, dan mengubah read state. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada untuk durable notification center. |
| Stakeholders and Interests | User ingin pesan terorganisasi; privacy menuntut owner scoping. |
| Trigger | User membuka center/count atau memilih read/read-all. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | Daftar/count tampil atau read_at pada notification milik user diperbarui. |
| Business Rules | Daftar terpaginasikan; filter unread/type mengikuti controller; read-all hanya menyentuh user saat ini; durable record tidak dihapus. |
| Data Requirements | Notification type/title/body/data/action URL/available_at/delivered_at/read_at/reference key/date. |
| Authorization and Privacy Considerations | Owner query dan `UserNotificationPolicy`; payload API/realtime diminimalkan; admin tanpa bypass. |
| Related Backlog Items | RB-NOT-001 |
| Related Requirements | notification controller/service/policy/resource dan auth middleware. |
| Related Test Cases | `NotificationFeatureTest.php`; `NotificationServiceTest.php`; `PhaseSevenApiTest.php` |
| Implementation Evidence | notification web/API controllers; service/model/migration/policy/resource/views |
| Assumptions and Open Questions | Arsip/delete notification oleh User tidak ditemukan. |

### Main Success Scenario

1. User membuka notification center.
2. HEALO mengambil notification owner-scoped dan unread count.
3. HEALO menampilkan list terpaginasikan.
4. User memilih notification.
5. HEALO memeriksa ownership dan mengisi read_at.
6. HEALO memperbarui badge/count.

### Alternative Flows

- A1 — User memilih read-all; service menandai seluruh unread milik user.
- A2 — Tidak ada notification; empty state tampil.
- A3 — Legacy route `/features/notifications` digunakan; HEALO mengalihkan ke center yang sama.

### Exception Flows

- E1 — Notification user lain; policy menolak.
- E2 — Session/token invalid; middleware menolak.

## UC-NOT-02 — Mengelola Preferensi Notifikasi

| Field | Specification |
|---|---|
| Use Case ID | UC-NOT-02 |
| Use Case Name | Mengelola Preferensi Notifikasi |
| Goal | Mengontrol kategori aktif, reminder time, dan quiet hours. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Scheduler/queue membaca preferensi setelah disimpan. |
| Stakeholders and Interests | User ingin kontrol gangguan; sistem membutuhkan konfigurasi waktu valid. |
| Trigger | User membuka atau menyimpan notification settings. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | Satu NotificationPreference milik user tersedia dan berisi nilai terbaru. |
| Business Rules | Master enable dan kategori per type; reminder/quiet time format valid; quiet hours menunda active delivery; preference dibuat bila belum ada. |
| Data Requirements | Boolean global/kategori, quiet_hours_start/end, reminder_time. |
| Authorization and Privacy Considerations | Preference hanya milik user; API tidak menerima owner ID. |
| Related Backlog Items | RB-NOT-002 |
| Related Requirements | preference request/controller/service/model/resource. |
| Related Test Cases | `NotificationFeatureTest.php`; `NotificationServiceTest.php`; `PhaseSevenApiTest.php` |
| Implementation Evidence | preference web/API controllers; service/model/migration/request/resource/views |
| Assumptions and Open Questions | Zona waktu tunggal mengikuti `Asia/Bangkok`; per-user timezone tidak ditemukan. |

### Main Success Scenario

1. User membuka notification settings.
2. HEALO mengambil/membuat preference milik user.
3. User memilih master/category switches serta waktu.
4. HEALO memvalidasi format dan kombinasi waktu.
5. Service menyimpan preference.
6. HEALO mengembalikan settings terbaru.

### Alternative Flows

- A1 — User menonaktifkan kategori; NotificationService melewati pembuatan/delivery tipe itu.
- A2 — Quiet hours mencakup tengah malam; service menghitung available_at sesuai rule waktu.

### Exception Flows

- E1 — Format waktu invalid atau pasangan quiet hours tidak lengkap; request ditolak.

## UC-NOT-03 — Menerima Reminder dan Notifikasi

| Field | Specification |
|---|---|
| Use Case ID | UC-NOT-03 |
| Use Case Name | Menerima Reminder dan Notifikasi |
| Goal | Menerima reminder relevan secara durable, sesuai preferensi, realtime bila tersedia. |
| Scope | HEALO Background dan Realtime, dengan hasil pada Web/API |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Scheduler, queue worker, Laravel Reverb/Echo; semuanya komponen pendukung. |
| Stakeholders and Interests | User ingin reminder tepat waktu tanpa duplikasi; operasi membutuhkan retry/idempotency. |
| Trigger | Scheduler memeriksa reminder time atau notification menjadi eligible setelah quiet hours. |
| Preconditions | User role user; preference mengizinkan tipe; scheduler/worker aktif untuk delivery otomatis. |
| Postconditions | Notification durable dibuat paling banyak sekali per deduplication key/date dan delivered/broadcast sesuai eligibility. |
| Business Rules | Pemeriksaan due tiap 15 menit; deferred delivery tiap menit; reference key mencegah duplikat; quiet hours menentukan available_at; private channel hanya owner. |
| Data Requirements | User activity (journal/meal/plan/goal), preference, notification payload/reference/date/availability/delivery, channel user ID. |
| Authorization and Privacy Considerations | Broadcast hanya `users.{userId}.notifications`; payload minimal; record tetap owner-scoped; Reverb offline tidak menghilangkan durable record. |
| Related Backlog Items | RB-NOT-003; RB-NOT-004 |
| Related Requirements | reminder/notification services, job/commands/scheduler, event/private channel. |
| Related Test Cases | `ReminderEngineTest.php`; `NotificationServiceTest.php`; `PhaseSevenBroadcastAndSeederTest.php` |
| Implementation Evidence | `ReminderService`; `SendWellnessReminderJob`; commands; `routes/console.php`; `NotificationCreated`; `routes/channels.php`; Echo client |
| Assumptions and Open Questions | Delivery production tergantung scheduler, queue worker, dan Reverb process; email/push mobile tidak ditemukan. |

### Main Success Scenario

1. Scheduler menjalankan pemeriksaan reminder.
2. HEALO memilih User yang reminder time-nya berada pada window 15 menit.
3. Queue job memeriksa data aktual User: jurnal, meal, task plan, goal, weekly insight, dan wellness.
4. NotificationService memeriksa master/category preference dan deduplication.
5. HEALO membuat durable notification.
6. Bila di luar quiet hours, HEALO menandai delivery dan mem-broadcast event ke private owner channel.
7. Client User memperbarui badge/dropdown dan menampilkan toast; notification juga tersedia di center/API.

### Alternative Flows

- A1 — Quiet hours aktif; notification disimpan dengan available_at dan tidak langsung broadcast.
- A2 — Deliver command kemudian menemukan notification due; delivery/broadcast dilakukan sekali.
- A3 — Reverb/client offline; notification tetap dapat dibaca dari center/API.
- A4 — Aktivitas sudah dicatat; reminder jurnal/meal terkait tidak dibuat.

### Exception Flows

- E1 — Retry mencoba reference yang sama; idempotency mencegah duplikasi.
- E2 — Kategori/global notification disabled; pembuatan dilewati.
- E3 — Scheduler/worker tidak berjalan; delivery otomatis tertunda dan membutuhkan validasi operasional.

## UC-ADM-01 — Melihat Dashboard Admin

| Field | Specification |
|---|---|
| Use Case ID | UC-ADM-01 |
| Use Case Name | Melihat Dashboard Admin |
| Goal | Melihat tiga statistik operasional agregat tanpa mengakses data kesehatan privat. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | Admin |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | Admin ingin monitoring dasar; User/privacy ingin tidak ada health-data bypass. |
| Trigger | Admin membuka `/admin`. |
| Preconditions | Session login, email verified, role admin. |
| Postconditions | Dashboard menampilkan total user, user baru 30 hari, dan audit event 24 jam; tidak ada data berubah. |
| Business Rules | User biasa dilarang; hanya role user dihitung sebagai total/new users; audit count adalah aggregate. |
| Data Requirements | Count User role user, created_at 30 hari, AuditLog created_at 24 jam. |
| Authorization and Privacy Considerations | Middleware `role:admin`; controller tidak mengambil isi health records; policies privat tetap tanpa admin bypass. |
| Related Backlog Items | RB-ADM-001 |
| Related Requirements | Admin route group dan `EnsureUserHasRole`. |
| Related Test Cases | `RoleAuthorizationTest.php`; `PhaseTwoAuthorizationTest.php`; `PhaseThreeAuthorizationTest.php`; `PhaseFourAuthorizationTest.php`; `PhaseFiveAuthorizationTest.php`; `PhaseSixAuthorizationTest.php` |
| Implementation Evidence | `routes/web.php`; `Admin/DashboardController`; admin dashboard view; role middleware |
| Assumptions and Open Questions | User management, menu management, AI telemetry, audit review, dan settings admin tetap PLANNED. |

### Main Success Scenario

1. Admin membuka `/admin`.
2. HEALO memverifikasi session, email, dan role admin.
3. Controller menghitung total User biasa.
4. Controller menghitung User baru dalam 30 hari.
5. Controller menghitung AuditLog dalam 24 jam.
6. HEALO menampilkan tiga statistik agregat.

### Alternative Flows

- A1 — Admin membuka `/dashboard`; HEALO mengalihkan ke `/admin`.
- A2 — Tidak ada data; dashboard menampilkan count nol.

### Exception Flows

- E1 — User biasa membuka `/admin`; middleware mengembalikan forbidden.
- E2 — Guest/unverified; middleware mengarahkan ke login/verification.

