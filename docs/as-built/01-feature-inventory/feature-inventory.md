# Inventaris Fitur Terkonfirmasi

## Cara membaca

- **Actor/initiator** hanya memakai User atau Admin untuk interaksi bisnis. Tanda `—` menunjukkan proses internal otomatis, bukan aktor tambahan.
- **Channel** menunjukkan Web, API, atau Background. Keberadaan dua channel tidak otomatis membuat dua use case bisnis.
- Semua item pada tabel utama berstatus **CONFIRMED** karena memiliki implementasi yang dapat ditunjukkan. Kolom test menjelaskan tingkat verifikasi otomatisnya.

## A. Public, authentication, dan account

| Feature ID | Capability / workflow | Actor | Channel | Status | Implementation evidence | Test evidence | Observable result / notes |
|---|---|---|---|---|---|---|---|
| FI-PUB-01 | Melihat landing page publik | User | Web | **CONFIRMED** | `routes/web.php`, `resources/views/welcome.blade.php` | `HomepageTest.php` | Guest memperoleh halaman publik; user terautentikasi diarahkan ke dashboard |
| FI-AUTH-01 | Registrasi akun | User | Web, API | **CONFIRMED** | `RegisteredUserController`, `Api/V1/AuthController`, request/view auth | `RegistrationTest.php`, `ApiAuthenticationTest.php` | Akun user dibuat dan sesi/token dibentuk sesuai channel |
| FI-AUTH-02 | Login | User, Admin | Web, API | **CONFIRMED** | `AuthenticatedSessionController`, `Api/V1/AuthController` | `AuthenticationTest.php`, `ApiAuthenticationTest.php` | Kredensial valid menghasilkan session atau Sanctum token; kredensial invalid ditolak |
| FI-AUTH-03 | Logout | User, Admin | Web, API | **CONFIRMED** | route logout web/API dan controller auth | `AuthenticationTest.php`, `ApiAuthenticationTest.php` | Session diakhiri atau token aktif dicabut |
| FI-AUTH-04 | Verifikasi dan kirim ulang email verifikasi | User, Admin | Web | **CONFIRMED** | `VerifyEmailController`, `EmailVerification*Controller`, signed/throttled routes | `EmailVerificationTest.php` | Email ditandai terverifikasi hanya dengan signed URL valid |
| FI-AUTH-05 | Meminta dan menyelesaikan reset password | User, Admin | Web | **CONFIRMED** | `PasswordResetLinkController`, `NewPasswordController`, auth views | `PasswordResetTest.php` | Link reset dapat diminta dan password diganti menggunakan token valid |
| FI-AUTH-06 | Konfirmasi dan ubah password | User, Admin | Web | **CONFIRMED** | `ConfirmablePasswordController`, `PasswordController` | `PasswordConfirmationTest.php`, `PasswordUpdateTest.php` | Password lama diverifikasi sebelum tindakan sensitif/perubahan password |
| FI-AUTH-07 | Membaca profil autentikasi melalui token API | User, Admin | API | **CONFIRMED** | `GET api/v1/profile`, `UserResource`, Sanctum middleware | `ApiAuthenticationTest.php` | Pemilik token menerima representasi profilnya sendiri |
| FI-PREF-01 | Mengubah bahasa aplikasi | User, Admin | Web | **CONFIRMED** | `LocaleController`, `SetLocale`, file `lang/en` dan `lang/id` | `LocalizationTest.php` | Locale yang didukung disimpan; locale lain ditolak |
| FI-ACC-01 | Melihat dan memperbarui profil akun | User, Admin | Web | **CONFIRMED** | `ProfileController`, `ProfileUpdateRequest`, view profile | `ProfileTest.php` | Nama/email akun diperbarui dengan aturan verifikasi yang berlaku |
| FI-ACC-02 | Menghapus akun sendiri | User, Admin | Web | **CONFIRMED** | `ProfileController::destroy`, form penghapusan akun | `ProfileTest.php` | Password wajib benar; akun dan session aktif dihapus/diakhiri |

## B. Dashboard dan health records

| Feature ID | Capability / workflow | Actor | Channel | Status | Implementation evidence | Test evidence | Observable result / notes |
|---|---|---|---|---|---|---|---|
| FI-DASH-01 | Melihat dashboard kesehatan personal | User | Web | **CONFIRMED** | `DashboardController`, `DashboardHealthService`, `dashboard.blade.php` | `DashboardHealthTest.php` | Menampilkan ringkasan owner-scoped dan perbandingan mingguan; admin dialihkan ke dashboard admin |
| FI-HPR-01 | Membuat/memperbarui profil kesehatan dan consent | User | Web, API | **CONFIRMED** | `HealthProfileController/Service`, request, model, policy/resource | `HealthProfileTest.php`, `PhaseTwoApiTest.php`, `PhaseTwoAuthorizationTest.php` | Pengukuran dan consent divalidasi; kondisi dan alergi terenkripsi |
| FI-BMI-01 | Menghitung dan menyimpan BMI | User | Web, API | **CONFIRMED** | `BmiController`, `BmiRecordController`, `BmiCalculator`, `BmiRecordService` | `BmiCalculatorTest.php`, `BmiFeatureTest.php`, `PhaseTwoApiTest.php` | BMI dibulatkan/diklasifikasi dan record disimpan; profil hanya diperbarui sesuai consent/keberadaan |
| FI-BMI-02 | Melihat dan menghapus riwayat BMI | User | Web, API | **CONFIRMED** | BMI controllers, `BmiRecordPolicy`, `BmiRecordResource` | `BmiFeatureTest.php`, test otorisasi fase 2 | Hanya record milik user yang dapat dilihat/dihapus |
| FI-JRN-01 | Membuat, mencari, melihat, mengubah, dan menghapus jurnal kesehatan | User | Web, API | **CONFIRMED** | `HealthJournalController/Service`, request, model, policy/resource | `HealthJournalTest.php`, `PhaseTwoApiTest.php` | Jurnal owner-scoped dikelola; filter pencarian/mood/bulan tersedia pada web |
| FI-JRN-02 | Menambah, mengubah, dan menghapus entry jurnal | User | Web, API | **CONFIRMED** | `HealthJournalEntryController/Service`, request, policy/resource | `HealthJournalTest.php`, `PhaseTwoApiTest.php` | Entry terhubung ke jurnal milik user dan kontennya terenkripsi |
| FI-JRN-03 | Melihat dashboard, tren, dan skor jurnal | User | Web | **CONFIRMED** | `HealthJournalDashboardService`, partial dashboard/overview/trends/history | `HealthJournalTest.php` | Ringkasan memakai metrik harian dan BMI milik user; empty/insufficient data ditangani |

## C. Nutrition, meal, food analysis, dan healthy menu

| Feature ID | Capability / workflow | Actor | Channel | Status | Implementation evidence | Test evidence | Observable result / notes |
|---|---|---|---|---|---|---|---|
| FI-NUT-01 | Menghitung atau mengatur profil dan target kalori | User | Web, API | **CONFIRMED** | `NutritionProfileController/Service`, `CalorieTargetService`, request/policy/resource | `NutritionProfileTest.php`, `CalorieTargetServiceTest.php`, `PhaseThreeApiTest.php` | Target dapat dihitung dari profil kesehatan atau diatur manual dalam batas aman |
| FI-NUT-02 | Melihat dashboard serta ringkasan nutrisi harian/mingguan | User | Web, API | **CONFIRMED** | `NutritionDashboardController`, `NutritionSummaryController/Service` | `NutritionDashboardAndMenuTest.php`, `PhaseThreeApiTest.php` | Total kalori, makro, target, tren, dan meal aktual disajikan owner-scoped |
| FI-MEAL-01 | Membuat, melihat, mengubah, dan menghapus meal | User | Web, API | **CONFIRMED** | `MealController/Service`, request, model, policy/resource | `MealFeatureTest.php`, `PhaseThreeApiTest.php`, authorization fase 3 | Meal dikaitkan ke tanggal/jenis makan milik user |
| FI-MEAL-02 | Menambah, mengubah, dan menghapus item makanan | User | Web, API | **CONFIRMED** | `MealItemController/Service`, request, policy/resource | `MealFeatureTest.php`, `PhaseThreeApiTest.php` | Nama, porsi, satuan, kalori, protein, karbohidrat, dan lemak divalidasi; total meal/summary disinkronkan |
| FI-FOOD-01 | Mengunggah foto dan meminta estimasi makanan AI | User | Web, API | **CONFIRMED** | `FoodAnalysisController/Service`, `FoodAnalysisProviderInterface`, adapter Groq/local | `FoodAnalysisFeatureTest.php`, `GroqFoodAnalysisProviderTest.php`, `PhaseThreeApiTest.php` | Foto privat dianalisis menjadi draft terstruktur; endpoint dibatasi rate limit |
| FI-FOOD-02 | Meninjau, mengoreksi, dan mengonfirmasi hasil estimasi | User | Web, API | **CONFIRMED** | `FoodAnalysisConfirmationRequest`, `FoodAnalysisService::confirm`, view hasil | `FoodAnalysisFeatureTest.php`, `PhaseThreeApiTest.php` | Setiap angka memiliki field nutrisi; koreksi tervalidasi membentuk meal/item hanya setelah konfirmasi eksplisit |
| FI-FOOD-03 | Melihat foto privat, membatalkan draft, dan retensi gambar | User | Web, API, Background | **CONFIRMED** | private image route, `FoodAnalysisPolicy`, cancel/cleanup service, scheduler | `FoodAnalysisFeatureTest.php`, authorization fase 3 | Foto hanya dapat dibaca pemilik, dihapus saat confirm/cancel/failure, atau dibersihkan setelah kedaluwarsa |
| FI-MENU-01 | Mencari dan memfilter katalog menu sehat | User | Web, API | **CONFIRMED** | `HealthyMenuController/Service::search`, model/resource | `NutritionDashboardAndMenuTest.php`, `PhaseThreeApiTest.php` | Menu aktif dapat dicari dan difilter berdasarkan meal type, diet, dan kalori |
| FI-MENU-02 | Melihat rekomendasi menu personal | User | Web | **CONFIRMED** | `HealthyMenuService::recommendations`, dashboard nutrisi dan view menu | Coverage halaman parsial pada `NutritionDashboardAndMenuTest.php` | Rekomendasi memakai target kalori, preferensi diet, meal type, dan penyaringan alergi berbasis teks; perlu assertion khusus di masa depan |

## D. AI assistant dan symptom checker

| Feature ID | Capability / workflow | Actor | Channel | Status | Implementation evidence | Test evidence | Observable result / notes |
|---|---|---|---|---|---|---|---|
| FI-AST-01 | Membuat, mendaftar, melihat, dan menghapus percakapan assistant | User | Web, API | **CONFIRMED** | conversation controllers/service/model/policy/resource | `HealthAssistantFeatureTest.php`, API/authorization fase 4 | Percakapan dan message owner-scoped; penghapusan conversation menghapus messages terkait |
| FI-AST-02 | Mengirim pesan dan menerima jawaban assistant | User | Web, API | **CONFIRMED** | `HealthAssistantMessageController`, `HealthAssistantService`, provider interface | `HealthAssistantFeatureTest.php`, `PhaseFourApiTest.php` | Input divalidasi dan dibatasi; jawaban provider melewati safety guard/fallback sebelum disimpan |
| FI-AST-03 | Menangani diagnosis, dosis, prompt injection, dan red flag pada chat | User | Web, API | **CONFIRMED** | `MedicalSafetyService`, `EmergencyDetectionService` | `HealthAssistantFeatureTest.php`, `EmergencyDetectionServiceTest.php` | Rule lokal mengganti respons yang berbahaya dan tidak bergantung pada keputusan provider |
| FI-SYM-01 | Menjalankan symptom assessment ephemeral atau tersimpan | User | Web, API | **CONFIRMED** | symptom controllers, `SymptomAssessmentService`, prompt/parser/provider | `SymptomCheckerFeatureTest.php`, `PhaseFourApiTest.php` | Input menghasilkan struktur gejala, kategori kemungkinan, urgency, rekomendasi, confidence, dan disclaimer; penyimpanan opsional |
| FI-SYM-02 | Melihat history/detail dan menghapus assessment | User | Web, API | **CONFIRMED** | symptom controllers, model, policy/resource | `SymptomCheckerFeatureTest.php`, authorization fase 4 | Hanya assessment tersimpan milik user yang tersedia dalam history/detail |
| FI-SYM-03 | Menyimpan hasil assessment ke jurnal | User | Web, API | **CONFIRMED** | `AssessmentJournalService`, `SaveAssessmentToJournalRequest` | `SymptomCheckerFeatureTest.php` | Membutuhkan konfirmasi eksplisit dan mencegah penyimpanan diam-diam |
| FI-SYM-04 | Emergency/self-harm safety override pada assessment | User | Web, API | **CONFIRMED** | `EmergencyDetectionService`, `MedicalSafetyService::emergencyAssessment` | `SymptomCheckerFeatureTest.php`, `EmergencyDetectionServiceTest.php` | Red flag fisik/self-harm menghasilkan urgency emergency dan rekomendasi aman sebelum pemanggilan provider |

## E. Insights, goal, dan daily wellness plan

| Feature ID | Capability / workflow | Actor | Channel | Status | Implementation evidence | Test evidence | Observable result / notes |
|---|---|---|---|---|---|---|---|
| FI-INS-01 | Melihat overview dan rule-based health insights | User | Web, API | **CONFIRMED** | `HealthInsightsController/Service::overview`, context dari BMI/meal/journal/AI/goal | `HealthInsightsServiceTest.php`, `PhaseFiveApiTest.php` | Periode 7/30/90 hari dinormalisasi dan data hanya berasal dari owner |
| FI-INS-02 | Melihat tren dan ringkasan mingguan | User | Web, API | **CONFIRMED** | `HealthInsightsService::trends/weekly`, Chart.js view | `HealthInsightsServiceTest.php`, `PhaseFiveApiTest.php` | Seri BMI, berat, nutrisi, frekuensi jurnal, mood, dan weekly structure disajikan |
| FI-INS-03 | Mendapat personalized AI insight yang aman | User | Web, API | **CONFIRMED** | `HealthInsightsService::personalized`, context minimization, parser/safety/cache | test service, AI privacy, provider, dan API fase 5 | Hanya aggregate dikirim; hasil unsafe/failure diganti fallback lokal; rate limit per user |
| FI-GOAL-01 | Membuat, melihat, mengubah, dan menghapus health goal | User | Web, API | **CONFIRMED** | `HealthGoalController/Service`, requests, model/policy/resource | `HealthGoalFeatureTest.php`, API/authorization fase 5 | Goal tervalidasi dan selalu owner-scoped |
| FI-GOAL-02 | Menghitung dan melihat progress goal | User | Web, API | **CONFIRMED** | `HealthGoalService::progress`, sumber BMI/frequency | `HealthGoalServiceTest.php`, `PhaseFiveApiTest.php` | Progress dihitung server-side, dibatasi rentang aman, dan dapat menentukan status selesai |
| FI-PLAN-01 | Membuat dan melihat health plan hari ini | User | Web, API | **CONFIRMED** | `HealthPlanController/Service/Generator`, model/resource | `HealthPlanFeatureTest.php`, service/API fase 6 | Satu plan per tanggal; generate API menerima hari ini saja dan mencegah duplikasi |
| FI-PLAN-02 | Menyelesaikan/membuka kembali task manual dan sinkronisasi task data-derived | User | Web, API | **CONFIRMED** | `HealthPlanTaskController`, progress service, task requests/policy | `HealthPlanServiceTest.php`, API/authorization fase 6 | Hanya task manual dapat diubah langsung; task data-derived mengikuti meal/journal aktual |
| FI-PLAN-03 | Mendapat personalisasi AI untuk daily wellness plan | User | Web | **CONFIRMED** | `HealthPlanPersonalizationService`, provider dan medical safety | `PhaseSixAIPrivacyTest.php`, `GroqHealthAIProviderTest.php` | Provider menerima aggregate tanpa identitas/raw journal; failure/unsafe output memakai fallback |

## F. Notification, automation, audit, dan admin

| Feature ID | Capability / workflow | Actor | Channel | Status | Implementation evidence | Test evidence | Observable result / notes |
|---|---|---|---|---|---|---|---|
| FI-NOT-01 | Melihat pusat notifikasi, unread count, serta menandai read/read-all | User | Web, API | **CONFIRMED** | notification controllers/service/model/policy/resource | `NotificationFeatureTest.php`, `PhaseSevenApiTest.php` | Daftar terpaginasikan dan seluruh perubahan dibatasi ke pemilik |
| FI-NOT-02 | Mengelola preferensi kategori, quiet hours, dan reminder time | User | Web, API | **CONFIRMED** | preference controllers/service/request/model/resource | `NotificationFeatureTest.php`, `PhaseSevenApiTest.php` | Waktu tervalidasi; kategori nonaktif mencegah pembuatan/delivery terkait |
| FI-NOT-03 | Menghasilkan reminder dari data harian aktual | — | Background | **CONFIRMED** | `ReminderService`, `SendWellnessReminderJob`, `SendRemindersCommand`, scheduler | `ReminderEngineTest.php`, `NotificationServiceTest.php` | Reminder jurnal, meal, plan, goal, insight, dan wellness bersifat idempotent |
| FI-NOT-04 | Mengirim notifikasi langsung atau setelah quiet hours | — | Background, Realtime | **CONFIRMED** | `NotificationService`, `DeliverNotificationsCommand`, `NotificationCreated`, private channel | `PhaseSevenBroadcastAndSeederTest.php`, `NotificationServiceTest.php` | Notifikasi durable disimpan; broadcast hanya ke channel pemilik dan delivery tertunda dikirim sekali |
| FI-ADM-01 | Melihat dashboard admin agregat | Admin | Web | **CONFIRMED** | `/admin`, role middleware, `Admin/DashboardController`, admin view | `RoleAuthorizationTest.php` | Menampilkan total user, user baru 30 hari, dan jumlah audit event 24 jam; tidak membuka data kesehatan privat |
| FI-AUD-01 | Mencatat audit event berisiko rendah | — | Internal | **CONFIRMED** | `AuditLogService`, auth event listener, pemanggilan audit pada controller | `AuditLogTest.php`, `PhaseFourPrivacyTest.php` | Metadata dibatasi, IP di-hash, dan konten pesan kesehatan tidak disimpan |
| FI-SYS-01 | Membersihkan foto analisis makanan kedaluwarsa | — | Background | **CONFIRMED** | `FoodAnalysisService::deleteExpiredImages`, scheduled command 02:30 | `FoodAnalysisFeatureTest.php` | File privat dihapus dan reference image dikosongkan tanpa menghapus hasil analisis |

## G. Fitur terencana atau tidak ditemukan

Item berikut bukan bagian dari 47 capability terkonfirmasi dan tidak boleh dipetakan sebagai use case selesai.

| Inventory ID | Item | Status | Evidence / alasan |
|---|---|---|---|
| PI-ADM-01 | Lifecycle management akun user oleh admin | **PLANNED** | Disebut roadmap; route/controller/view/test tidak ditemukan |
| PI-ADM-02 | CRUD food dan healthy menu oleh admin | **PLANNED** | Disebut roadmap; hanya katalog user dan seeder tersedia |
| PI-ADM-03 | Telemetri operasional AI dan error monitoring admin | **PLANNED** | Disebut roadmap; dashboard/route/model telemetry tidak ditemukan |
| PI-ADM-04 | Halaman review audit log teredaksi | **PLANNED** | Penyimpanan audit ada, UI/endpoint review tidak ada |
| PI-ADM-05 | Pengaturan sistem oleh admin | **PLANNED** | Disebut dokumentasi lama; implementasi tidak ditemukan |
| PI-REL-01 | PDF/report export dan ekspor data kesehatan | **PLANNED**; implementasi **NOT FOUND** | DomPDF terpasang dan roadmap menyebut ekspor, tetapi tidak ada route/controller/view/test workflow |
| NF-EXT-01 | Integrasi rumah sakit, EMR/EHR, BPJS, atau SATUSEHAT | **NOT FOUND** | Tidak ditemukan pada source/config/schema/test; arsitektur menyatakan standalone |
| NF-EXT-02 | Integrasi Apple Health, Google Health Connect, atau wearable | **NOT FOUND** | Tidak ditemukan pada source/config/schema/test |

