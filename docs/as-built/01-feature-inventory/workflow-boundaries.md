# Batas Workflow dan Channel

Dokumen ini mencegah route, controller, provider, database, atau job salah dimodelkan sebagai aktor/use case tersendiri pada fase berikutnya.

## 1. Aktor yang disetujui

| Aktor | Batas interaksi |
|---|---|
| **User** | Mengelola akun dan seluruh data kesehatan/wellness miliknya sendiri; memakai fitur AI sebagai informasi pendukung |
| **Admin** | Mengakses dashboard admin agregat; tidak memperoleh bypass ke data kesehatan privat user |

AI Provider, scheduler, queue worker, Reverb, database, dan email infrastructure adalah sistem/komponen pendukung. Komponen tersebut boleh muncul sebagai boundary atau swimlane pada activity diagram jika menjalankan langkah yang relevan, tetapi bukan aktor use case.

## 2. Hubungan channel dengan capability

| Domain | Web | API v1 | Background / realtime | Keputusan pemodelan awal |
|---|---|---|---|---|
| Auth | Session, email verification, password lifecycle | Register/login/logout token, own profile | — | Capability auth sama; token lifecycle dicatat sebagai variasi channel |
| Health profile/BMI/journal | UI Blade penuh | Resource JSON | — | Satu kelompok use case bisnis dengan alternatif interface |
| Nutrition/meal | UI Blade penuh | Resource JSON | Refresh summary dilakukan service | Tidak menggandakan use case hanya karena endpoint API |
| Food analysis | Upload/review/confirm dan private image | Upload/show/confirm/delete draft | Cleanup gambar kedaluwarsa | Workflow user dipisah dari maintenance internal |
| Healthy menu | Search/filter/recommendation | Search/filter katalog | — | Recommendation personal hanya terbukti pada web saat ini |
| Assistant/symptom | Conversation dan assessment UI | Conversation/assessment JSON | Provider AI opsional | Provider adalah supporting system, bukan aktor |
| Insights/goals | Dashboard dan CRUD | Overview/trends/weekly dan CRUD | Cache AI insight | Overview/trend/personalized dapat menjadi subflow berbeda |
| Health plan | View/generate/complete | List/today/generate/complete | Personalisasi provider opsional | Generate plan berbeda dari update task |
| Notification | Center/preference | Center/count/preference | Scheduler, queue, quiet-hour delivery, broadcast | Workflow user dan workflow otomatis dipisahkan |
| Admin | Dashboard agregat | Tidak ada | Query aggregate | Jangan menambahkan CRUD admin yang hanya ada di roadmap |

## 3. Batas workflow AI

### 3.1 Analisis foto makanan

1. User memilih foto.
2. HEALO memvalidasi tipe/ukuran upload dan rate limit.
3. HEALO menyimpan foto pada storage privat.
4. Food Analysis Provider menganalisis gambar melalui contract yang aktif.
5. HEALO memvalidasi hasil terstruktur dan menyimpan **draft review**, bukan meal.
6. User melihat label nama makanan, porsi/satuan, kalori, protein, karbohidrat, dan lemak; seluruh nilai dapat dikoreksi.
7. Setelah konfirmasi eksplisit, HEALO membuat meal/items dan menghapus foto privat.
8. Bila provider gagal, HEALO membersihkan file, menandai failure, tidak membuat meal, dan menampilkan error aman.

Konsekuensi: upload/analyze dan review/confirm merupakan dua tahap tanggung jawab yang jelas dan layak dipertahankan pada activity diagram.

### 3.2 Health assistant

1. User membuat/memilih conversation dan mengirim pesan.
2. HEALO memvalidasi input, ownership, dan rate limit.
3. Safety service memeriksa emergency, self-harm, permintaan diagnosis/dosis, dan prompt injection.
4. Bila rule lokal memutuskan respons aman, provider tidak menjadi sumber keputusan akhir.
5. Untuk input lain, provider menerima konteks yang diminimalkan.
6. HEALO memeriksa respons provider dan menggantinya dengan fallback bila gagal atau unsafe.
7. Pesan user dan jawaban aman disimpan terenkripsi dalam conversation milik user.

### 3.3 Symptom checker

1. User memasukkan deskripsi gejala dan memilih apakah hasil akan disimpan.
2. HEALO memvalidasi input dan menjalankan red-flag detection sebelum provider.
3. Emergency/self-harm menghasilkan assessment emergency lokal.
4. Selain itu, provider menghasilkan payload yang wajib lolos parser/schema.
5. Payload invalid/failure menghasilkan fallback tervalidasi.
6. Hasil dapat tetap ephemeral atau disimpan sesuai pilihan user.
7. Penyimpanan ke jurnal membutuhkan konfirmasi terpisah.

### 3.4 Personalized insights dan wellness plan

1. HEALO menghitung data owner-scoped dan membentuk aggregate minimal.
2. Provider menerima aggregate tanpa identitas dan tanpa raw journal content.
3. Parser dan medical safety memeriksa output.
4. Output unsafe, invalid, timeout, atau error diganti dengan fallback lokal.
5. Hasil dipresentasikan sebagai informasi wellness, bukan diagnosis atau resep.

## 4. Batas privacy dan authorization

- Semua route domain health pada web membutuhkan `auth` dan `verified`.
- Semua API privat membutuhkan `auth:sanctum` dan throttle global.
- Query owner-scoped dan policy digunakan bersama; resource asing menghasilkan forbidden/not found sesuai endpoint.
- Admin tidak memiliki bypass implisit ke health profile, BMI, journal, nutrition, AI record, goal, plan, atau notification milik user.
- Field sensitif tertentu menggunakan encrypted cast; audit log dilarang menerima konten kesehatan.
- Foto analisis makanan di-stream melalui route privat dengan cache `private, no-store`.
- Groq hanya aktif bila provider dikonfigurasi dan API key tersedia; fallback lokal menjadi jalur valid.

## 5. Workflow otomatis yang bukan use case aktor baru

| Workflow | Trigger | Tanggung jawab | Bukti |
|---|---|---|---|
| Cleanup foto | Scheduler harian 02:30 | Menghapus file privat yang melewati retention | `routes/console.php`, `FoodAnalysisService` |
| Queue reminder | Scheduler tiap 15 menit | Memilih user yang reminder time-nya jatuh tempo dan dispatch job | `SendRemindersCommand`, `ReminderService` |
| Generate reminder | Queue job | Membentuk reminder dari meal, jurnal, plan, goal, insight, dan wellness aktual | `SendWellnessReminderJob`, `ReminderService` |
| Deliver deferred notification | Scheduler tiap menit | Mengirim notifikasi yang tertahan quiet hours tepat satu kali | `DeliverNotificationsCommand`, `NotificationService` |
| Broadcast notification | Event setelah create/due | Mengirim payload minimal ke private owner channel | `NotificationCreated`, `routes/channels.php` |
| Audit event | Auth event/controller action | Menyimpan metadata operasional rendah sensitivitas | `AuditLogService`, `AppServiceProvider` |

## 6. Hal yang sengaja tidak dijadikan capability terkonfirmasi

- Route redirect legacy `/features/notifications` bukan fitur bisnis baru; route tersebut hanya mengarahkan ke notification center.
- Keberadaan dependensi DomPDF bukan bukti fitur ekspor.
- Healthy menu seed bukan bukti CRUD admin.
- Tabel audit bukan bukti halaman review admin.
- Nama route pada roadmap bukan bukti route aktual.
- Provider AI bukan bukti diagnosis medis atau integrasi sistem kesehatan eksternal.

