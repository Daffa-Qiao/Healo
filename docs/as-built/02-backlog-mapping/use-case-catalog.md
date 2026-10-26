# Katalog Use Case ID

Katalog ini menetapkan ID dan nama use case yang akan digunakan secara konsisten mulai Fase 3. Ini bukan use case description lengkap.

| Use Case ID | Use Case Name | Primary Actor | Related Feature Inventory | Channel | Status |
|---|---|---|---|---|---|
| UC-PUB-01 | Melihat Landing Page | User | FI-PUB-01 | Web | **CONFIRMED** |
| UC-AUTH-01 | Registrasi Akun | User | FI-AUTH-01 | Web, API | **CONFIRMED** |
| UC-AUTH-02 | Login | User, Admin | FI-AUTH-02 | Web, API | **CONFIRMED** |
| UC-AUTH-03 | Logout | User, Admin | FI-AUTH-03 | Web, API | **CONFIRMED** |
| UC-AUTH-04 | Verifikasi Email | User, Admin | FI-AUTH-04 | Web | **CONFIRMED** |
| UC-AUTH-05 | Reset Password | User, Admin | FI-AUTH-05 | Web | **CONFIRMED** |
| UC-AUTH-06 | Konfirmasi dan Ubah Password | User, Admin | FI-AUTH-06 | Web | **CONFIRMED** |
| UC-PREF-01 | Mengubah Bahasa Aplikasi | User, Admin | FI-PREF-01 | Web | **CONFIRMED** |
| UC-ACC-01 | Melihat dan Memperbarui Profil Akun | User, Admin | FI-AUTH-07, FI-ACC-01 | Web, API | **CONFIRMED** |
| UC-ACC-02 | Menghapus Akun Sendiri | User, Admin | FI-ACC-02 | Web | **CONFIRMED** |
| UC-DASH-01 | Melihat Dashboard Kesehatan | User | FI-DASH-01 | Web | **CONFIRMED** |
| UC-HPR-01 | Mengelola Profil Kesehatan | User | FI-HPR-01 | Web, API | **CONFIRMED** |
| UC-BMI-01 | Menghitung dan Mencatat BMI | User | FI-BMI-01 | Web, API | **CONFIRMED** |
| UC-BMI-02 | Mengelola Riwayat BMI | User | FI-BMI-02 | Web, API | **CONFIRMED** |
| UC-JRN-01 | Mengelola Jurnal Kesehatan | User | FI-JRN-01 | Web, API | **CONFIRMED** |
| UC-JRN-02 | Mengelola Entri Jurnal | User | FI-JRN-02 | Web, API | **CONFIRMED** |
| UC-JRN-03 | Melihat Dashboard dan Tren Jurnal | User | FI-JRN-03 | Web | **CONFIRMED** |
| UC-NUT-01 | Mengelola Profil dan Target Nutrisi | User | FI-NUT-01 | Web, API | **CONFIRMED** |
| UC-NUT-02 | Melihat Ringkasan Nutrisi | User | FI-NUT-02 | Web, API | **CONFIRMED** |
| UC-MEAL-01 | Mengelola Catatan Makan | User | FI-MEAL-01 | Web, API | **CONFIRMED** |
| UC-MEAL-02 | Mengelola Item Makanan | User | FI-MEAL-02 | Web, API | **CONFIRMED** |
| UC-FOOD-01 | Memindai Foto Makanan | User | FI-FOOD-01 | Web, API | **CONFIRMED** |
| UC-FOOD-02 | Meninjau dan Mengonfirmasi Hasil Analisis Makanan | User | FI-FOOD-02 | Web, API | **CONFIRMED** |
| UC-FOOD-03 | Mengelola Draft Analisis Makanan | User | FI-FOOD-03 | Web, API | **CONFIRMED** |
| UC-MENU-01 | Mencari dan Memfilter Menu Sehat | User | FI-MENU-01 | Web, API | **CONFIRMED** |
| UC-MENU-02 | Melihat Rekomendasi Menu Personal | User | FI-MENU-02 | Web | **CONFIRMED** |
| UC-AST-01 | Mengelola Percakapan Healbot | User | FI-AST-01 | Web, API | **CONFIRMED** |
| UC-AST-02 | Berkonsultasi dengan Healbot | User | FI-AST-02, FI-AST-03 | Web, API | **CONFIRMED** |
| UC-SYM-01 | Memeriksa Gejala | User | FI-SYM-01, FI-SYM-04 | Web, API | **CONFIRMED** |
| UC-SYM-02 | Mengelola Riwayat Pemeriksaan Gejala | User | FI-SYM-02 | Web, API | **CONFIRMED** |
| UC-SYM-03 | Menyimpan Pemeriksaan ke Jurnal | User | FI-SYM-03 | Web, API | **CONFIRMED** |
| UC-INS-01 | Melihat Ringkasan Health Insights | User | FI-INS-01 | Web, API | **CONFIRMED** |
| UC-INS-02 | Melihat Tren dan Ringkasan Mingguan | User | FI-INS-02 | Web, API | **CONFIRMED** |
| UC-INS-03 | Mendapatkan Personalized AI Insight | User | FI-INS-03 | Web, API | **CONFIRMED** |
| UC-GOAL-01 | Mengelola Health Goal | User | FI-GOAL-01 | Web, API | **CONFIRMED** |
| UC-GOAL-02 | Memantau Progress Health Goal | User | FI-GOAL-02 | Web, API | **CONFIRMED** |
| UC-PLAN-01 | Membuat dan Melihat Health Plan Harian | User | FI-PLAN-01 | Web, API | **CONFIRMED** |
| UC-PLAN-02 | Mengelola Penyelesaian Task Harian | User | FI-PLAN-02 | Web, API | **CONFIRMED** |
| UC-PLAN-03 | Melihat Personalisasi Daily Wellness | User | FI-PLAN-03 | Web | **CONFIRMED** |
| UC-NOT-01 | Mengelola Pusat Notifikasi | User | FI-NOT-01 | Web, API | **CONFIRMED** |
| UC-NOT-02 | Mengelola Preferensi Notifikasi | User | FI-NOT-02 | Web, API | **CONFIRMED** |
| UC-NOT-03 | Menerima Reminder dan Notifikasi | User | FI-NOT-03, FI-NOT-04 | Background, Realtime | **CONFIRMED** |
| UC-ADM-01 | Melihat Dashboard Admin | Admin | FI-ADM-01 | Web | **CONFIRMED** |

## Kontrol internal tanpa Use Case ID

| Feature Inventory | Alasan |
|---|---|
| FI-AUD-01 | Audit logging berjalan sebagai kontrol lintas use case dan belum memiliki interaksi/review oleh User atau Admin |
| FI-SYS-01 | Cleanup foto kedaluwarsa adalah maintenance terjadwal tanpa goal aktor |

Relasi `include` dan `extend` belum diputuskan pada Fase 2. Relasi tersebut hanya akan dipakai bila alur lengkap Fase 3 menunjukkan semantik UML yang tepat.

