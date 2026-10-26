# Pemetaan Product Backlog ke Use Case

## Legenda

- Prioritas: `Must` = fungsi inti/keamanan, `Should` = penting tetapi bukan prasyarat dasar, `Could` = peningkatan pengalaman.
- Status: `Implemented` = route dan implementasi tersedia; `Planned` = disebut di roadmap tetapi belum tersedia sebagai fitur penuh.
- Kanal: `Web`, `API`, `Web + API`, atau `Internal`.

## Backlog terimplementasi

| ID | Epic | Product backlog / user story | Kriteria penerimaan ringkas | Prioritas | Use case | Kanal | Status |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PB-01 | Akses | Sebagai user, saya ingin melihat landing page agar memahami HEALO. | Halaman publik dapat dibuka tanpa login. | Should | UC-01 | Web | Implemented |
| PB-02 | Akses | Sebagai pengguna, saya ingin memilih Bahasa Indonesia/Inggris. | Locale valid `id/en` disimpan dan tampilan mengikuti pilihan. | Should | UC-02 | Web | Implemented |
| PB-03 | Autentikasi | Sebagai user, saya ingin membuat akun. | Nama, email unik, dan password valid menghasilkan akun user. | Must | UC-03 | Web + API | Implemented |
| PB-04 | Autentikasi | Sebagai pengguna, saya ingin login dan logout dengan aman. | Kredensial diverifikasi, sesi/token dibuat atau dicabut, percobaan dibatasi. | Must | UC-04 | Web + API | Implemented |
| PB-05 | Autentikasi | Sebagai pengguna, saya ingin mereset password yang terlupa. | Tautan reset dikirim dan token valid dapat mengganti password. | Must | UC-05 | Web | Implemented |
| PB-06 | Autentikasi | Sebagai pengguna, saya ingin memverifikasi email. | Tautan bertanda tangan mengisi waktu verifikasi; pengiriman ulang dibatasi. | Must | UC-06 | Web | Implemented |
| PB-07 | Akun | Sebagai pengguna, saya ingin memperbarui nama, email, bahasa, dan password. | Input tervalidasi; perubahan email membatalkan verifikasi lama. | Must | UC-07 | Web | Implemented |
| PB-08 | Akun | Sebagai pengguna, saya ingin menghapus akun. | Password dikonfirmasi, sesi dibatalkan, akun di-soft-delete. | Must | UC-08 | Web | Implemented |
| PB-09 | API | Sebagai user yang memakai API, saya ingin autentikasi token dan membaca profil. | Register/login mengembalikan token; endpoint privat memakai Sanctum. | Must | UC-09 | API | Implemented |
| PB-10 | Dashboard | Sebagai pengguna, saya ingin ringkasan kesehatan pribadi. | Dashboard menampilkan snapshot, tren, goal, plan, insight, dan quick action dari data pemilik. | Must | UC-10 | Web | Implemented |
| PB-11 | Kesehatan | Sebagai pengguna, saya ingin menyimpan profil kesehatan dengan persetujuan. | Tinggi, berat, demografi, aktivitas, tujuan, kondisi/alergi tervalidasi; data sensitif terenkripsi. | Must | UC-11 | Web + API | Implemented |
| PB-12 | BMI | Sebagai pengguna, saya ingin menghitung dan menyimpan BMI. | BMI/kategori dihitung server-side, histori dan tren ditampilkan, profil dapat disinkronkan. | Must | UC-12 | Web + API | Implemented |
| PB-13 | BMI | Sebagai pengguna, saya ingin menghapus catatan BMI saya. | Hanya pemilik dapat menghapus record yang dipilih. | Should | UC-13 | Web + API | Implemented |
| PB-14 | Jurnal | Sebagai pengguna, saya ingin membuat, melihat, mengubah, dan menghapus jurnal kesehatan. | CRUD tervalidasi, tanggal tidak di masa depan, record owner-scoped dan mendukung soft delete. | Must | UC-14 | Web + API | Implemented |
| PB-15 | Jurnal | Sebagai pengguna, saya ingin mencatat detail/entri dalam jurnal. | Entri keluhan, gejala, catatan, dan tipe valid lain dapat ditambah/diubah/dihapus. | Must | UC-15 | Web + API | Implemented |
| PB-16 | Jurnal | Sebagai pengguna, saya ingin mencari, memfilter, dan melihat tren jurnal. | Daftar dapat difilter; dashboard jurnal menyajikan overview, histori, dan tren. | Should | UC-16 | Web | Implemented |
| PB-17 | Nutrisi | Sebagai pengguna, saya ingin mengatur profil nutrisi dan target kalori. | Preferensi tervalidasi; target manual dibatasi atau dihitung dari profil kesehatan. | Must | UC-17 | Web + API | Implemented |
| PB-18 | Nutrisi | Sebagai pengguna, saya ingin melihat ringkasan nutrisi harian/mingguan. | Kalori, sisa target, meal, makro, dan tren 7 hari berasal dari catatan aktual. | Must | UC-18 | Web + API | Implemented |
| PB-19 | Nutrisi | Sebagai pengguna, saya ingin CRUD catatan makan. | Meal bertanggal, bertipe, owner-scoped, dan totalnya disinkronkan. | Must | UC-19 | Web + API | Implemented |
| PB-20 | Nutrisi | Sebagai pengguna, saya ingin CRUD item makanan pada suatu meal. | Nutrien item tervalidasi dan perubahan menghitung ulang total meal/ringkasan. | Must | UC-20 | Web + API | Implemented |
| PB-21 | AI Makanan | Sebagai pengguna, saya ingin mengunggah foto makanan untuk dianalisis. | File privat tervalidasi, request dibatasi, provider menghasilkan draf dan confidence. | Should | UC-21 | Web + API | Implemented |
| PB-22 | AI Makanan | Sebagai pengguna, saya ingin meninjau dan mengoreksi hasil sebelum menyimpan. | Hasil tidak otomatis menjadi meal; konfirmasi eksplisit membuat meal dari nilai yang disetujui. | Must | UC-22 | Web + API | Implemented |
| PB-23 | Nutrisi | Sebagai pengguna, saya ingin mencari menu sehat. | Katalog aktif dapat dicari/difilter dan menampilkan sumber/atribusi. | Should | UC-23 | Web + API | Implemented |
| PB-24 | Asisten | Sebagai pengguna, saya ingin membuat dan menghapus percakapan kesehatan. | Percakapan owner-scoped; judul terenkripsi; penghapusan hanya oleh pemilik. | Should | UC-24 | Web + API | Implemented |
| PB-25 | Asisten | Sebagai pengguna, saya ingin berkonsultasi dengan asisten kesehatan. | Pesan tervalidasi/rate-limited, tersimpan terenkripsi, respons melewati safety guard/fallback. | Must | UC-25 | Web + API | Implemented |
| PB-26 | Gejala | Sebagai pengguna, saya ingin memeriksa gejala secara terstruktur. | Emergency detection berjalan dahulu; hasil berisi urgensi, red flag, ringkasan, dan rekomendasi aman. | Must | UC-26 | Web + API | Implemented |
| PB-27 | Gejala | Sebagai pengguna, saya ingin menyimpan, melihat, dan menghapus riwayat asesmen. | Penyimpanan bersifat opsional; histori dan detail hanya dapat diakses pemilik. | Should | UC-27 | Web + API | Implemented |
| PB-28 | Gejala/Jurnal | Sebagai pengguna, saya ingin menyalin asesmen ke jurnal setelah mengonfirmasi. | Konfirmasi wajib; jurnal dibuat/ditautkan sekali tanpa duplikasi. | Must | UC-28 | Web + API | Implemented |
| PB-29 | Insight | Sebagai pengguna, saya ingin melihat insight kesehatan personal yang aman. | Overview memakai data aktual; insight rule-based selalu tersedia dan AI opsional melewati safety check. | Should | UC-29 | Web + API | Implemented |
| PB-30 | Insight | Sebagai pengguna, saya ingin melihat tren 7/30/90 hari dan ringkasan mingguan. | BMI, berat, kalori, makro, frekuensi jurnal, dan mood dihitung dari record milik pengguna. | Should | UC-30 | Web + API | Implemented |
| PB-31 | Goal | Sebagai pengguna, saya ingin mengelola sasaran kesehatan. | CRUD owner-scoped; rentang target sesuai tipe; progres dihitung dari sumber aktual. | Must | UC-31 | Web + API | Implemented |
| PB-32 | Rencana | Sebagai pengguna, saya ingin rencana kesehatan harian yang personal. | Maksimum satu plan per tanggal; generate idempoten; task berasal dari kondisi/data aktual. | Must | UC-32 | Web + API | Implemented |
| PB-33 | Rencana | Sebagai pengguna, saya ingin menyelesaikan/membuka kembali task manual. | Hanya task manual sah yang dapat diubah; task data-derived disinkronkan sistem; progres dihitung server. | Must | UC-33 | Web + API | Implemented |
| PB-34 | Notifikasi | Sebagai pengguna, saya ingin membaca pusat notifikasi. | Daftar owner-scoped mendukung filter, pagination, unread count, read, dan read-all. | Must | UC-34 | Web + API | Implemented |
| PB-35 | Notifikasi | Sebagai pengguna, saya ingin mengatur kategori, waktu reminder, dan quiet hours. | Satu preferensi per user; waktu/kategori tervalidasi termasuk quiet hours lintas tengah malam. | Must | UC-35 | Web + API | Implemented |
| PB-36 | Reminder | Sebagai pengguna, saya ingin reminder berdasarkan data aktual. | Scheduler memeriksa jurnal, meal, task, goal, insight, wellness; notifikasi terdeduplikasi. | Should | UC-36 | Internal | Implemented |
| PB-37 | Notifikasi | Sebagai pengguna, saya ingin badge/toast real-time. | Event privat hanya memuat payload minimum; kegagalan real-time tidak menghilangkan notifikasi durable. | Could | UC-37 | Web + Internal | Implemented |
| PB-38 | Administrasi | Sebagai admin, saya ingin melihat statistik operasional agregat. | Route hanya role admin dan menampilkan total user, user baru 30 hari, event audit 24 jam. | Should | UC-38 | Web | Implemented |
| PB-39 | Audit | Sebagai admin, saya ingin aksi penting tercatat tanpa isi kesehatan. | Audit menyimpan aktor, aksi, subject, metadata aman, dan hash IP; tidak menyimpan konten sensitif. | Must | UC-39 | Internal | Implemented |
| PB-40 | Privasi | Sebagai pengguna, saya ingin data kesehatan hanya dapat diakses oleh saya. | Policy/owner query berlaku di web/API; admin tidak mendapat bypass; field sensitif dienkripsi. | Must | Semua UC privat | Web + API | Implemented |
| PB-41 | Keandalan | Sebagai pengguna, saya ingin fungsi inti tetap tersedia saat provider AI/Reverb gagal. | Provider memiliki fallback; plan/insight inti deterministik; notifikasi tetap tersimpan. | Must | UC-21, UC-25, UC-26, UC-29, UC-32, UC-37 | Semua | Implemented |

## Backlog roadmap yang belum menjadi use case aktif

| ID | Epic | Backlog roadmap | Use case target | Status/bukti batasan |
| --- | --- | --- | --- | --- |
| PB-P01 | Administrasi | Manajemen lifecycle pengguna | UC-P01 | Planned; tidak ada route admin users aktif. |
| PB-P02 | Administrasi | Manajemen food/menu oleh admin | UC-P02 | Planned; katalog ada, tetapi route admin foods/menus belum aktif. |
| PB-P03 | Administrasi | Telemetri penggunaan AI dan monitoring error | UC-P03 | Planned; tidak ada surface administrasi aktif. |
| PB-P04 | Administrasi | Review audit log teredaksi | UC-P04 | Planned; audit disimpan, tetapi halaman review belum aktif. |
| PB-P05 | Pelaporan | Ekspor laporan/PDF | UC-P05 | Planned; query disebut PDF-ready, tetapi route ekspor belum tersedia. |

## Matriks use case ke komponen utama

| Kelompok use case | Controller/surface utama | Service/model dominan |
| --- | --- | --- |
| UC-01–UC-09 | Auth controllers, `ProfileController`, `LocaleController`, API `AuthController` | `User`, Sanctum, session auth |
| UC-10–UC-16 | `DashboardController`, `HealthProfileController`, `BmiController`, journal controllers | `DashboardHealthService`, `HealthProfile`, `BmiRecord`, `HealthJournal`, `HealthJournalEntry` |
| UC-17–UC-23 | Nutrition, meal, food-analysis, menu controllers | Nutrition services, `NutritionProfile`, `Meal`, `MealItem`, `FoodAnalysis`, `HealthyMenu` |
| UC-24–UC-28 | Assistant and symptom-checker controllers | Health AI services, `HealthConversation`, `HealthMessage`, `SymptomAssessment` |
| UC-29–UC-33 | Insight, goal, and health-plan controllers | Insight/goal/plan services, `HealthGoal`, `HealthPlan`, `HealthPlanTask` |
| UC-34–UC-37 | Notification controllers, command/job/event | Notification services, `UserNotification`, `NotificationPreference` |
| UC-38–UC-39 | Admin dashboard and cross-cutting audit calls | `User`, `AuditLog`, `AuditLogService` |

