# Kandidat Fitur dan Status Awal

Daftar ini adalah hasil triase Fase 0. Ini **belum** merupakan product backlog final atau daftar use case final. Fase 1 akan memecah kandidat menjadi capability dan workflow yang dapat ditelusuri.

| Domain / kandidat fitur | Status awal | Bukti implementasi utama | Bukti test utama | Catatan audit |
|---|---|---|---|---|
| Registrasi, login, logout, verifikasi email, reset/ubah password | **CONFIRMED** | `routes/auth.php`, controller auth, view auth | `tests/Feature/Auth/*`, `ApiAuthenticationTest.php` | Web dan API memiliki alur auth yang berbeda tetapi capability sama |
| Locale Indonesia/Inggris | **CONFIRMED** | `SetLocale`, `LocaleController`, `lang/` | `LocalizationTest.php` | Default lingkungan audit `id` |
| Profil akun dan hapus akun | **CONFIRMED** | `ProfileController`, view profile | `ProfileTest.php` | Hapus akun memerlukan password |
| Dashboard kesehatan user | **CONFIRMED** | `DashboardController`, `DashboardHealthService`, view dashboard | `DashboardHealthTest.php` | Menyajikan data owner-scoped dan weekly summary |
| Profil kesehatan dan consent | **CONFIRMED** | `HealthProfileController/Service`, model/request/policy | `HealthProfileTest.php`, `PhaseTwoApiTest.php` | Alergi/kondisi terenkripsi |
| Kalkulator dan riwayat BMI | **CONFIRMED** | `BmiController`, `BmiCalculator`, `BmiRecordService` | `BmiCalculatorTest.php`, `BmiFeatureTest.php` | Validasi ukuran dan tanggal tersedia |
| Jurnal kesehatan dan entry | **CONFIRMED** | controller/service/model jurnal | `HealthJournalTest.php` | Konten terenkripsi; metrik harian dipakai dashboard |
| Profil nutrisi dan target kalori | **CONFIRMED** | `NutritionProfileController/Service`, `CalorieTargetService` | `NutritionProfileTest.php`, `CalorieTargetServiceTest.php` | Target dibatasi rentang aman aplikasi |
| Catatan meal, item, total nutrisi, ringkasan harian/mingguan | **CONFIRMED** | controller/service/model nutrition | `MealFeatureTest.php`, `NutritionDashboardAndMenuTest.php` | Nutrien utama tersimpan pada meal item |
| Analisis foto makanan berbasis AI | **CONFIRMED** | `FoodAnalysisController/Service`, provider interface dan adapter | `FoodAnalysisFeatureTest.php`, `GroqFoodAnalysisProviderTest.php` | Hasil tetap draft sampai dikonfirmasi user; fallback lokal tersedia |
| Pencarian/filter menu sehat | **CONFIRMED** | `HealthyMenuController`, `HealthyMenuService::search` | `NutritionDashboardAndMenuTest.php` | Hanya menu aktif |
| Rekomendasi menu personal | **CONFIRMED** | `HealthyMenuService::recommendations` | cakupan halaman pada `NutritionDashboardAndMenuTest.php` | Berdasarkan target kalori, diet preference, meal type, dan filter alergi berbasis teks |
| Healbot / health assistant | **CONFIRMED** | controller/service conversation/message, provider AI | `HealthAssistantFeatureTest.php`, test fase 4 | Ada safety boundary, rate limit, dan fallback |
| AI symptom checker dan history | **CONFIRMED** | `SymptomCheckerController`, `SymptomAssessmentService` | `SymptomCheckerFeatureTest.php`, test fase 4 | Assessment dapat ephemeral atau disimpan |
| Simpan assessment ke jurnal | **CONFIRMED** | `AssessmentJournalService`, request/controller | `SymptomCheckerFeatureTest.php` | Membutuhkan konfirmasi eksplisit |
| Deteksi kondisi darurat/self-harm | **CONFIRMED** | `EmergencyDetectionService`, `MedicalSafetyService` | `EmergencyDetectionServiceTest.php`, `SymptomCheckerFeatureTest.php` | Rule lokal mengoverride provider AI |
| Health insights dan tren | **CONFIRMED** | service insight/dashboard dan controller | `HealthInsightsServiceTest.php`, test fase 5 | AI memakai aggregate/minimized context dan fallback |
| Health goals dan progress | **CONFIRMED** | controller/service/model goal | `HealthGoalFeatureTest.php`, `HealthGoalServiceTest.php` | CRUD dan perhitungan progress |
| Health plan, daily wellness, dan task | **CONFIRMED** | service/model/controller plan | test fitur/service/API fase 6 | Task derived tidak bisa dipalsukan selesai; task manual bisa dibuka kembali |
| Notification center, unread, read/read-all | **CONFIRMED** | notification controller/service/model | test notifikasi dan API fase 7 | Owner-scoped |
| Preferensi, quiet hours, reminder terjadwal | **CONFIRMED** | preference service, reminder service/job/commands/scheduler | `NotificationServiceTest.php`, `ReminderEngineTest.php` | Idempotent dan delivery dapat ditunda |
| Realtime notification | **CONFIRMED** | event, private channel, Reverb/Echo | `PhaseSevenBroadcastAndSeederTest.php` | Notifikasi durable tetap menjadi sumber utama |
| Audit log operasional | **CONFIRMED** | `AuditLogService`, listener event di provider | `AuditLogTest.php`, privacy test fase 4 | Tidak ditemukan halaman review audit admin |
| Dashboard admin agregat | **CONFIRMED** | `Admin/DashboardController`, view, `/admin` | `RoleAuthorizationTest.php` | Hanya statistik total/new user dan event audit 24 jam |
| Kelola lifecycle user oleh admin | **PLANNED** | disebut `docs/implementation-roadmap.md` | tidak ada | Route/controller/view/test admin terkait tidak ditemukan |
| Kelola makanan/menu oleh admin | **PLANNED** | disebut roadmap | tidak ada | Model menu ada, tetapi UI CRUD admin tidak ditemukan |
| Telemetri AI, error monitoring, review audit admin | **PLANNED** | disebut roadmap | tidak ada | Audit storage ada; review UI tidak ada |
| Pengaturan sistem admin | **PLANNED** | disebut inventaris route lama | tidak ada | Route/controller/model settings tidak ditemukan |
| PDF/report export dan ekspor data kesehatan | **PLANNED** / **NOT FOUND** | dependensi PDF dan roadmap | tidak ada | Dependensi bukan bukti workflow |
| Integrasi rumah sakit/EMR/EHR/BPJS/SATUSEHAT | **NOT FOUND** | pencarian lintas app/routes/config/database/resources/tests/docs | tidak ada | `docs/architecture.md` justru menyatakan produk standalone |
| Apple Health/Google Health Connect/wearable | **NOT FOUND** | pencarian lintas repository | tidak ada | Jangan dimasukkan ke use case as-built |

## Aktor dan sistem pendukung awal

- **User** — aktor utama untuk seluruh data kesehatan privat dan fitur wellness.
- **Admin** — aktor untuk dashboard administrasi agregat yang saat ini tersedia.
- **AI Provider** — sistem pendukung opsional untuk analisis; bukan aktor use case. Implementasi memilih Groq bila dikonfigurasi dan fallback lokal bila tidak.
- **Scheduler/queue/realtime infrastructure** — komponen internal platform; tidak otomatis dimodelkan sebagai aktor use case.

Sesuai arahan pemilik produk, aktor pada use case diagram dibatasi menjadi **User** dan **Admin**. AI Provider tetap dapat ditunjukkan sebagai boundary/komponen pendukung atau swimlane pada activity diagram AI, tetapi tidak sebagai aktor use case.

