# Inventaris Repository

## 1. Platform dan dependensi utama

| Area | Temuan | Status | Bukti utama |
|---|---|---|---|
| Backend | PHP 8.2+ dan Laravel 12 | **CONFIRMED** | `composer.json`, hasil `php artisan about` |
| Autentikasi API | Laravel Sanctum 4.3 | **CONFIRMED** | `composer.json`, `routes/api.php` |
| Realtime | Laravel Reverb, Echo, dan Pusher client | **CONFIRMED** | `composer.json`, `package.json`, `routes/channels.php`, `resources/js/echo.js` |
| Frontend | Blade, Vite, Tailwind CSS, Alpine.js | **CONFIRMED** | `package.json`, `vite.config.js`, `resources/` |
| Visualisasi | Chart.js dan DataTables | **CONFIRMED** | `package.json`, view terkait |
| PDF | `barryvdh/laravel-dompdf` terpasang | **CONFIRMED** sebagai dependensi; alur ekspor **NOT FOUND** | `composer.json`; pencarian penggunaan di `app/`, `routes/`, `resources/`, dan `tests/` |
| AI eksternal opsional | Groq melalui adapter kontrak; fallback lokal tersedia | **CONFIRMED** | `app/Contracts/`, `app/Providers/AppServiceProvider.php`, `app/Services/HealthAI/`, `app/Services/Nutrition/` |

## 2. Inventaris kode

| Artefak | Jumlah | Lokasi / catatan |
|---|---:|---|
| Model Eloquent | 20 | `app/Models/` |
| Controller | 54 | termasuk web, API v1, auth, dan 1 controller admin |
| Service | 38 | audit, BMI, health, AI, plan, insight, journal, notification, nutrition |
| Contract/interface | 2 | provider analisis makanan dan provider Health AI |
| Form Request | 24 | validasi web/API |
| API Resource | 20 | serialisasi respons API v1 |
| Policy | 16 | owner scoping data privat |
| Middleware aplikasi | 2 | role dan locale |
| Job | 1 | pengingat wellness |
| Event | 1 | notifikasi dibuat |
| Console Command | 2 | pembuatan pengingat dan delivery notifikasi |
| Migration | 26 | tabel framework dan domain |
| Seeder | 9 | akun development serta data demo per domain |
| Blade view | 63 | public, auth, user, admin, dan komponen |
| Feature test | 51 file | `tests/Feature/` |
| Unit test | 7 file | `tests/Unit/` |

## 3. Model domain aktual

Model yang ditemukan:

`User`, `HealthProfile`, `BmiRecord`, `HealthJournal`, `HealthJournalEntry`, `NutritionProfile`, `Meal`, `MealItem`, `NutritionDailySummary`, `FoodAnalysis`, `HealthyMenu`, `HealthConversation`, `HealthMessage`, `SymptomAssessment`, `HealthGoal`, `HealthPlan`, `HealthPlanTask`, `UserNotification`, `NotificationPreference`, dan `AuditLog`.

Relasi utama yang terkonfirmasi dari model:

- `User` memiliki satu health profile, nutrition profile, dan notification preference;
- `User` memiliki banyak BMI record, journal, meal, nutrition summary, food analysis, conversation, assessment, goal, health plan, dan notification;
- `HealthJournal` memiliki banyak entry;
- `Meal` memiliki banyak meal item;
- `HealthConversation` memiliki banyak message dan assessment;
- `HealthPlan` memiliki banyak task;
- `AuditLog` berelasi ke actor dan subject polymorphic.

Detail atribut, foreign key, multiplicity, cast, dan operasi belum dibakukan sebagai class diagram pada Fase 0; hal tersebut akan ditelusuri pada fase diagram kelas setelah use case disetujui.

## 4. Database dan data seed

Migration aktual membentuk:

- tabel framework: users, password reset tokens, sessions, cache, jobs, failed jobs, job batches, dan personal access tokens;
- audit: audit logs;
- health: health profiles, BMI records, health journals, dan journal entries;
- nutrition: nutrition profiles, meals, meal items, daily summaries, food analyses, dan healthy menus;
- AI: health conversations, health messages, dan symptom assessments;
- wellness: health goals, health plans, dan health plan tasks;
- notification: notifications dan notification preferences.

Seeder development menyediakan akun dan data demo. Nilai kredensial seed tidak disalin ke dokumen ini. Seeder bukan bukti bahwa data tersebut tersedia di produksi.

## 5. Route dan execution surface

Hasil `php artisan route:list --json --except-vendor` memuat **149 route**, yang dikelompokkan secara audit menjadi:

| Kelompok | Jumlah | Proteksi utama |
|---|---:|---|
| Admin | 1 | session auth dan role admin |
| API v1 | 64 | Sanctum dan throttle 60/menit; endpoint AI lebih ketat |
| Auth web | 14 | guest/auth, signed, dan throttle sesuai alur |
| Web aplikasi | 70 | mayoritas auth dan verified |

Surface terkonfirmasi mencakup autentikasi, dashboard, profil kesehatan, BMI, jurnal, nutrisi, meal, analisis foto makanan, menu sehat, assistant, symptom checker, insight, goal, health plan, notifikasi, preferensi notifikasi, dan dashboard admin agregat.

Proses terjadwal di `routes/console.php`:

- pembersihan gambar analisis makanan kedaluwarsa setiap hari pukul 02:30;
- pemeriksaan pengingat setiap 15 menit;
- delivery notifikasi tertunda setiap menit.

Channel realtime privat `users.{userId}.notifications` memeriksa kepemilikan user.

## 6. Authorization, privacy, dan safety

| Kontrol | Temuan awal | Status |
|---|---|---|
| Role | role `user` dan `admin`; dashboard admin dilindungi middleware role | **CONFIRMED** |
| Owner scoping | private record dilindungi policy/query milik user | **CONFIRMED** oleh policy dan test lintas pengguna |
| Admin bypass | admin tidak otomatis dapat membaca data kesehatan privat user | **CONFIRMED** oleh test otorisasi |
| Enkripsi | sejumlah field profil, jurnal, chat, assessment, plan metadata, meal notes, goal metadata, dan hasil analisis memakai encrypted cast | **CONFIRMED** |
| Audit | login/logout/registrasi dan sejumlah aksi dicatat dengan metadata rendah sensitivitas; IP di-hash | **CONFIRMED** |
| Upload privat | foto makanan disimpan privat, memiliki batas ukuran/tipe, retensi, dan cleanup | **CONFIRMED** |
| AI safety | red-flag emergency/self-harm, pembatasan diagnosis/dosis, validasi schema, dan fallback | **CONFIRMED** |
| Context minimization | konteks AI dipilih dan diperkecil berdasarkan topik/aggregate | **CONFIRMED** oleh test |
| Data export | alur ekspor data pengguna/PDF | **NOT FOUND** |
| Account deletion | penghapusan akun dengan konfirmasi password | **CONFIRMED** |

## 7. Dokumen lama yang ditemukan

| Dokumen | Cara dipakai dalam audit |
|---|---|
| `README.md` | Ringkasan implementasi; diverifikasi ulang terhadap kode/test |
| `docs/architecture.md` | Referensi tujuan arsitektur; bukan bukti tunggal |
| `docs/database-erd.md` | Referensi desain; entitas tanpa migration/model tidak dianggap aktual |
| `docs/implementation-roadmap.md` | Sumber fitur yang pernah direncanakan; status diperiksa ulang |
| `docs/system-analysis/*` | Artefak analisis sebelumnya; akan dibandingkan, bukan disalin sebagai as-built |

Dokumen lama menyebut beberapa route admin, reporting, dan entitas data yang tidak ditemukan pada implementasi aktual. Discrepancy tersebut dicatat sebagai **PLANNED** atau **NOT FOUND**, bukan **CONFIRMED**.

