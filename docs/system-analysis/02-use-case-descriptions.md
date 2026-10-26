# Use Case Description

## Aturan umum

- Untuk area privat, autentikasi adalah prasyarat. Route web kesehatan memakai middleware email terverifikasi; endpoint API privat saat ini memakai Sanctum dan throttle tanpa middleware `verified`.
- Setiap operasi pada record privat memeriksa kepemilikan melalui query owner-scoped dan/atau policy.
- Form Request melakukan normalisasi dan validasi input. Pada API, kegagalan menghasilkan respons JSON yang konsisten; pada web, pengguna dikembalikan ke form dengan error.
- Konten kesehatan sensitif disimpan dengan cast terenkripsi. Audit hanya menerima metadata aman, bukan isi kesehatan.
- Use case yang tersedia pada web dan API memiliki tujuan bisnis sama; perbedaannya hanya session/redirect versus token/JSON Resource.

## A. Akses, autentikasi, dan akun

### UC-01 — Melihat landing page

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengunjung |
| Pemicu | Membuka `/`. |
| Prasyarat | Tidak ada. |
| Alur utama | 1. Sistem menerima request publik. 2. Sistem merender halaman pengenalan HEALO dan tautan autentikasi. |
| Alternatif | Pengguna yang sudah login dapat melanjutkan ke dashboard melalui navigasi. |
| Pascakondisi | Tidak ada data domain yang berubah. |

### UC-02 — Mengubah bahasa

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengunjung atau Pengguna |
| Pemicu | Memilih locale pada UI. |
| Prasyarat | Locale termasuk `id` atau `en`. |
| Alur utama | 1. Sistem memvalidasi locale. 2. Locale disimpan pada session dan, bila login, preferensi user. 3. Sistem mengarahkan kembali dengan terjemahan terpilih. |
| Alternatif | Locale di luar daftar ditolak oleh validasi. |
| Pascakondisi | Preferensi bahasa aktif untuk request berikutnya. |

### UC-03 — Registrasi akun

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengunjung / Klien API |
| Pemicu | Mengirim formulir registrasi. |
| Prasyarat | Belum terautentikasi; email belum dipakai. |
| Alur utama | 1. Validasi nama, email, password dan konfirmasi. 2. Hash password dan buat `User` ber-role user. 3. Web membuat session; API membuat token. 4. Sistem memicu proses verifikasi email. |
| Alternatif | Email duplikat atau password tidak valid menyebabkan request ditolak tanpa membuat akun. |
| Pascakondisi | Akun aktif tercipta tetapi akses fitur terverifikasi menunggu verifikasi email. |

### UC-04 — Login dan logout

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna / Admin / Klien API |
| Pemicu | Mengirim kredensial atau memilih logout. |
| Prasyarat | Untuk login, akun ada dan tidak terhapus; untuk logout, aktor sudah login. |
| Alur utama | Login: validasi, cek rate limit dan kredensial, regenerasi session atau buat token, arahkan sesuai role. Logout: cabut token/session dan regenerasi CSRF/session state. |
| Alternatif | Kredensial salah menambah hit rate-limit; terlalu banyak percobaan ditunda; akun non-admin ditolak dari route admin. |
| Pascakondisi | Konteks autentikasi dibuat atau diakhiri. |

### UC-05 — Reset password

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengunjung |
| Pemicu | Meminta tautan reset atau mengirim password baru dengan token. |
| Prasyarat | Email valid; token reset belum kedaluwarsa saat dipakai. |
| Alur utama | 1. Pengguna meminta reset. 2. Sistem mengirim tautan. 3. Pengguna membuka tautan dan memasukkan password baru. 4. Sistem memvalidasi token lalu memperbarui hash password. |
| Alternatif | Email tidak dikenal, token invalid/kedaluwarsa, atau konfirmasi password tidak cocok menghasilkan pesan aman. |
| Pascakondisi | Password baru berlaku dan token reset tidak dapat dipakai ulang. |

### UC-06 — Verifikasi email

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka tautan verifikasi atau meminta kirim ulang. |
| Prasyarat | Login; tautan memiliki signature valid untuk akun yang sama. |
| Alur utama | 1. Sistem memvalidasi ID, hash, dan signature. 2. `email_verified_at` diisi. 3. Pengguna dapat masuk ke area `verified`. |
| Alternatif | Tautan invalid ditolak; akun yang sudah terverifikasi langsung diarahkan; kirim ulang dibatasi throttle. |
| Pascakondisi | Status email terverifikasi. |

### UC-07 — Mengelola profil akun dan password

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Menyimpan perubahan profil atau password. |
| Prasyarat | Login. |
| Alur utama | 1. Sistem menampilkan profil. 2. Pengguna mengubah nama/email/locale atau password. 3. Sistem memvalidasi dan menyimpan. 4. Bila email berubah, status verifikasi direset. |
| Alternatif | Email duplikat, current password salah, atau password baru lemah ditolak. |
| Pascakondisi | Atribut akun berubah dan tercatat secara aman. |

### UC-08 — Menghapus akun

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Mengonfirmasi penghapusan akun. |
| Prasyarat | Login dan password saat ini benar. |
| Alur utama | 1. Sistem meminta konfirmasi kredensial. 2. Session dikeluarkan. 3. User di-soft-delete. 4. Session di-invalidasi dan token CSRF diperbarui. |
| Alternatif | Password salah membatalkan penghapusan. |
| Pascakondisi | Akun tidak dapat digunakan lewat alur normal; data mengikuti aturan relasi/retensi aplikasi. |

### UC-09 — Menggunakan autentikasi API

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Klien API |
| Pemicu | Register/login, memanggil endpoint privat, atau logout. |
| Prasyarat | Format JSON valid; endpoint privat membawa bearer token Sanctum. |
| Alur utama | 1. Klien memperoleh token. 2. Token dipakai pada endpoint `/api/v1`. 3. Sistem menerapkan auth dan throttle. 4. Logout mencabut token aktif. |
| Alternatif | Token hilang/invalid menghasilkan 401; akses record bukan milik sendiri menghasilkan 403/404 sesuai endpoint. |
| Pascakondisi | Sesi API terkontrol per token. |

## B. Dashboard dan kesehatan dasar

### UC-10 — Melihat dashboard kesehatan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka `/dashboard`. |
| Prasyarat | Login dan email terverifikasi; health profile meningkatkan kelengkapan pengalaman tetapi bukan syarat membuka dashboard. |
| Alur utama | 1. Sistem mengambil snapshot owner-scoped. 2. Sistem menghitung BMI, nutrisi, jurnal, goal, plan, weekly comparison, dan insight aman. 3. UI menampilkan kartu, grafik, preview, dan quick action. |
| Alternatif | Bila health profile atau data sumber belum ada, dashboard menampilkan empty state/ajakan mengisi data tanpa mengarang nilai. |
| Pascakondisi | Tidak ada mutasi selain sinkronisasi/generasi yang memang dipanggil service dashboard. |

### UC-11 — Mengelola profil kesehatan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka lalu menyimpan formulir health profile. |
| Prasyarat | Login dan email terverifikasi; persetujuan pemrosesan data diberikan. |
| Alur utama | 1. Input tinggi, berat, tanggal lahir, gender, level aktivitas, tujuan, kondisi, alergi, dan consent. 2. Sistem membersihkan/memvalidasi input. 3. Service membuat atau memperbarui satu profil milik user. 4. Field sensitif dienkripsi. |
| Alternatif | Consent/input wajib tidak valid ditolak; record user lain tidak dapat ditargetkan. |
| Pascakondisi | `HealthProfile` mutakhir dan dapat menjadi sumber kalkulasi BMI/nutrisi/plan. |

### UC-12 — Menghitung dan mencatat BMI

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Mengirim tinggi, berat, dan waktu pengukuran. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Validasi nilai fisik dan tanggal. 2. `BmiCalculator` menghitung BMI serta kategori. 3. `BmiRecordService` menyimpan record owner-scoped. 4. Riwayat/grafik diperbarui dan profil dapat diselaraskan. |
| Alternatif | Nilai di luar rentang atau tanggal invalid ditolak; kalkulasi tidak menerima BMI dari client. |
| Pascakondisi | Satu `BmiRecord` baru dengan hasil server-side. |

### UC-13 — Menghapus catatan BMI

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Memilih hapus pada item histori. |
| Prasyarat | Record ada dan dimiliki aktor. |
| Alur utama | 1. Sistem mencari record pada scope user. 2. Policy mengizinkan delete. 3. Record dihapus dan aksi diaudit. |
| Alternatif | Record user lain/tidak ada tidak dapat diakses. |
| Pascakondisi | Record hilang dari histori dan tren berikutnya. |

### UC-14 — Mengelola jurnal kesehatan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuat, membuka, mengubah, atau menghapus jurnal. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Pengguna mengisi judul, tanggal, mood, dan metrik harian. 2. Sistem memvalidasi tanggal/nilai. 3. Service menyimpan pada user. 4. View/API mengembalikan jurnal dengan entries. |
| Alternatif | Tanggal masa depan/nilai di luar rentang ditolak; update/delete milik pihak lain ditolak. |
| Pascakondisi | Jurnal dibuat/diperbarui atau di-soft-delete. |

### UC-15 — Mengelola entri jurnal

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Menambah, mengubah, atau menghapus entry dalam jurnal. |
| Prasyarat | Jurnal induk dimiliki pengguna. |
| Alur utama | 1. Pilih tipe dan isi. 2. Sistem membersihkan dan memvalidasi. 3. Service menyimpan content terenkripsi serta urutan. 4. Jurnal menampilkan entries terbaru. |
| Alternatif | Tipe/isi invalid ditolak; policy memeriksa pemilik melalui jurnal induk. |
| Pascakondisi | Detail jurnal berubah tanpa membuka data ke user lain. |

### UC-16 — Menelusuri dan melihat tren jurnal

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka daftar/dashboard jurnal dengan search/filter/periode. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Sistem menerapkan filter pada jurnal user. 2. Data dipaginasi. 3. Dashboard service menghitung overview, histori, mood, dan tren periode. 4. UI menampilkan hasil/empty state. |
| Alternatif | Filter kosong menampilkan daftar default; belum ada data menampilkan ajakan membuat jurnal. |
| Pascakondisi | Tidak ada data berubah. |

## C. Nutrisi dan makanan

### UC-17 — Mengelola profil nutrisi

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Menyimpan level aktivitas, goal, diet, preferensi, dan target. |
| Prasyarat | Login dan email terverifikasi; health profile diperlukan untuk kalkulasi otomatis penuh. |
| Alur utama | 1. Sistem membersihkan daftar preferensi. 2. Validasi enum dan target 1200–4500. 3. Bila recalculate, target dihitung dari profil kesehatan dalam batas aman. 4. Satu `NutritionProfile` di-upsert. |
| Alternatif | Data health profile tidak cukup menghasilkan pesan/target fallback; target manual di luar batas ditolak. |
| Pascakondisi | Profil nutrisi dan sumber target tersimpan. |

### UC-18 — Melihat dashboard/ringkasan nutrisi

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka `/nutrition` atau endpoint today/summary. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Sistem mengambil profil, meal, dan item pada scope user. 2. Service menghitung kalori/makro, sisa target, jumlah meal, dan tren tujuh hari. 3. Hasil dirender/diubah menjadi JSON Resource. |
| Alternatif | Tanpa profil/meal, sistem menampilkan empty state dan nilai aman. |
| Pascakondisi | Ringkasan dapat disinkronkan, tanpa menerima total buatan client. |

### UC-19 — Mengelola meal

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuat, membuka, mengubah, atau menghapus meal. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Isi tanggal, tipe meal, dan catatan. 2. Validasi tanggal tidak melebihi hari ini. 3. Service menyimpan meal milik user. 4. Total/ringkasan nutrisi disinkronkan. |
| Alternatif | Data invalid ditolak; record pihak lain ditolak policy. |
| Pascakondisi | `Meal` dan summary terkait konsisten; delete menggunakan soft delete. |

### UC-20 — Mengelola item makanan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Menambah, mengubah, atau menghapus item pada meal. |
| Prasyarat | Meal induk dimiliki pengguna. |
| Alur utama | 1. Isi nama, porsi, satuan, kalori, makro, dan sumber. 2. Validasi. 3. Service menyimpan perubahan. 4. Total meal dan daily summary dihitung ulang. |
| Alternatif | Nilai negatif/invalid ditolak; policy menelusuri pemilik lewat meal. |
| Pascakondisi | Item dan agregat nutrisi konsisten. |

### UC-21 — Menganalisis foto makanan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna; Penyedia AI |
| Pemicu | Mengunggah foto ke food analysis. |
| Prasyarat | Login, email terverifikasi, file gambar valid, belum melewati rate limit. |
| Alur utama | 1. File disimpan pada storage privat. 2. `FoodAnalysis` berstatus proses dibuat. 3. Provider menganalisis. 4. Hasil/confidence disimpan terenkripsi sebagai draf. 5. Pengguna diarahkan ke review. |
| Alternatif | Provider gagal menghasilkan status gagal dan pesan aman; local demo memberi draf confidence rendah; file invalid/rate-limit ditolak. |
| Pascakondisi | Draf analisis ada; belum ada `Meal` yang dibuat. |

### UC-22 — Meninjau dan mengonfirmasi analisis makanan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka draf lalu menekan konfirmasi atau batal. |
| Prasyarat | Draf dimiliki user, belum dikonfirmasi/kedaluwarsa. |
| Alur utama | 1. Sistem menampilkan image privat, hasil, confidence, dan warning. 2. Pengguna mengoreksi meal/item. 3. Validasi konfirmasi. 4. Service membuat meal/item dari nilai final. 5. Draf ditandai confirmed dan gambar ditangani sesuai retensi. |
| Alternatif | Pengguna membatalkan/menghapus draf; status invalid atau draf user lain ditolak; confidence rendah tetap memerlukan review eksplisit. |
| Pascakondisi | Meal terkonfirmasi dibuat sekali, atau draf dibatalkan. |

### UC-23 — Menelusuri menu sehat

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka katalog dengan query/filter. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Sistem membaca search, tipe meal, dan preferensi diet. 2. `HealthyMenuService` memfilter menu aktif. 3. Sistem menampilkan nutrien, bahan, cara, sumber, atribusi, dan rekomendasi sesuai profil. |
| Alternatif | Tidak ada hasil menampilkan empty state; filter invalid dinormalisasi/ditolak. |
| Pascakondisi | Tidak ada data berubah. |

## D. Asisten kesehatan dan symptom checker

### UC-24 — Mengelola percakapan kesehatan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuat, membuka, atau menghapus conversation. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Pengguna membuat judul. 2. Service membuat conversation owner-scoped dengan judul terenkripsi. 3. Pengguna membuka message history atau menghapus conversation. |
| Alternatif | Judul invalid ditolak; akses/hapus conversation user lain ditolak. |
| Pascakondisi | Conversation dibuat, dibaca, atau dihapus beserta relasi terkait sesuai database. |

### UC-25 — Mengirim pesan ke asisten kesehatan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna; Penyedia AI |
| Pemicu | Mengirim pesan pada conversation. |
| Prasyarat | Conversation dimiliki user; input valid; belum melewati throttle. |
| Alur utama | 1. Pesan dibersihkan dan disimpan terenkripsi. 2. Sistem membentuk konteks minimum. 3. Provider diminta merespons. 4. Parser/safety service menolak diagnosis, resep, dosis, injection, atau output tidak aman. 5. Respons aman/fallback disimpan dan ditampilkan. |
| Alternatif | Pesan darurat menghasilkan arahan keselamatan; provider/schema gagal memakai fallback; input berbahaya diblok/diganti. |
| Pascakondisi | Pasangan pesan user/asisten tersimpan; `last_message_at` diperbarui. |

### UC-26 — Memeriksa gejala

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna; Penyedia AI |
| Pemicu | Mengirim uraian gejala. |
| Prasyarat | Login, input 5–batas konfigurasi karakter, throttle tersedia. |
| Alur utama | 1. Input dibersihkan. 2. Emergency detection deterministik berjalan sebelum provider. 3. Jika aman, provider memberi hasil terstruktur. 4. Schema dan medical safety divalidasi. 5. Sistem menampilkan summary, urgency, red flags, kategori, rekomendasi, confidence, dan batasan medis. |
| Alternatif | Red flag memicu hasil emergency tanpa menunggu provider; provider gagal memakai fallback; penyimpanan hanya dilakukan bila opsi save dipilih. |
| Pascakondisi | Hasil sementara ditampilkan atau `SymptomAssessment` terenkripsi disimpan. |

### UC-27 — Mengelola riwayat asesmen gejala

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka history/detail atau memilih hapus. |
| Prasyarat | Assessment sebelumnya disimpan dan dimiliki user. |
| Alur utama | 1. Sistem mengambil assessment user. 2. Hasil didekripsi untuk pemilik dan ditampilkan. 3. Bila hapus, policy dicek lalu record dihapus dan aksi diaudit. |
| Alternatif | Assessment sementara/tidak disimpan tidak muncul; record user lain tidak dapat diakses. |
| Pascakondisi | Histori tetap atau assessment terpilih dihapus. |

### UC-28 — Menyimpan asesmen ke jurnal

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Menekan simpan ke jurnal dan memberi konfirmasi eksplisit. |
| Prasyarat | Assessment tersimpan, dimiliki user, belum ditautkan ke jurnal. |
| Alur utama | 1. Sistem memerlukan `confirm=accepted`. 2. `AssessmentJournalService` membentuk jurnal/entries dari data assessment. 3. Assessment ditautkan ke jurnal dan waktu simpan dicatat. 4. Audit aman dibuat. |
| Alternatif | Tanpa konfirmasi ditolak; pemanggilan ulang tidak membuat duplikat; assessment user lain ditolak. |
| Pascakondisi | Jurnal owner-scoped tercipta dan dapat diedit pengguna. |

## E. Insight, goal, dan rencana kesehatan

### UC-29 — Melihat insight kesehatan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna; Penyedia AI opsional |
| Pemicu | Membuka halaman/endpoint insights. |
| Prasyarat | Login, email terverifikasi, periode valid (7/30/90), throttle AI tersedia bila dipakai. |
| Alur utama | 1. Service menghitung overview aktual. 2. Rule engine menghasilkan insight non-diagnostik. 3. Konteks agregat minimum dapat dikirim ke provider. 4. Output terstruktur divalidasi/safety-checked dan di-cache per user. 5. UI menampilkan insight aman. |
| Alternatif | Data kurang menghasilkan empty guidance; provider gagal memakai rule-based fallback; output medis tidak aman diganti. |
| Pascakondisi | Tidak mengubah source record; cache aman dapat diperbarui. |

### UC-30 — Melihat tren dan ringkasan mingguan

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Memilih periode atau endpoint trends/weekly. |
| Prasyarat | Login; periode termasuk 7, 30, atau 90 hari. |
| Alur utama | 1. Sistem mengambil BMI, jurnal, meal, dan goal milik user. 2. Menghitung seri berat/BMI, kalori, makro, frekuensi jurnal, mood, dan perbandingan mingguan. 3. Hasil ditampilkan sebagai grafik/ringkasan. |
| Alternatif | Gap data ditampilkan sebagai data kosong, bukan dibuat-buat. |
| Pascakondisi | Tidak ada mutasi domain. |

### UC-31 — Mengelola health goal

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuat, melihat daftar, mengubah, atau menghapus goal. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Pilih tipe, target, tanggal, status. 2. Sistem memvalidasi rentang spesifik tipe. 3. Goal owner-scoped disimpan. 4. Progress service membaca nilai aktual dari BMI/meal/jurnal dan menampilkan progres. |
| Alternatif | Target/tanggal invalid ditolak; update tidak dapat mengganti owner/type secara bebas; record user lain ditolak. |
| Pascakondisi | Goal berubah, sedangkan current progress tetap diturunkan dari source record. |

### UC-32 — Melihat atau menghasilkan rencana kesehatan harian

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna; Penyedia AI opsional |
| Pemicu | Membuka health plan/today atau menekan generate. |
| Prasyarat | Login dan email terverifikasi; tanggal valid; user memiliki data minimum untuk personalisasi terbaik. |
| Alur utama | 1. Sistem mencari satu plan user/tanggal. 2. Generator deterministik membaca profil, nutrisi, meal, jurnal, BMI, dan goal. 3. Task terkontrol dibuat/disinkronkan tanpa duplikasi. 4. Progress dihitung server. 5. Provider opsional hanya memperhalus wording/motivasi yang aman. |
| Alternatif | Plan yang sudah ada disinkronkan idempoten; provider gagal tidak mengganggu task inti; client dilarang mengirim progress/owner/status. |
| Pascakondisi | Satu `HealthPlan` per user/tanggal dengan task dan progress konsisten. |

### UC-33 — Menyelesaikan atau membuka kembali task rencana

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Menekan complete/uncomplete. |
| Prasyarat | Task berada pada plan milik user dan completion mode mengizinkan aksi manual. |
| Alur utama | 1. Form Request/policy memverifikasi owner dan melarang field terhitung. 2. Service mengubah status/timestamp task manual. 3. Source-derived tasks disinkronkan. 4. Progress dan status plan dihitung ulang. |
| Alternatif | Task meal/journal data-derived tidak dapat dipalsukan manual; aksi pada status yang tidak cocok ditolak/idempoten sesuai service. |
| Pascakondisi | Task dan progress plan mencerminkan keadaan terbaru. |

## F. Notifikasi, administrasi, dan audit

### UC-34 — Mengelola pusat notifikasi

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Membuka daftar/unread count atau menandai read/read-all. |
| Prasyarat | Login dan email terverifikasi; notifikasi dimiliki user. |
| Alur utama | 1. Sistem memfilter tipe/unread dan memaginasi. 2. Pengguna membuka item. 3. Sistem mengisi `read_at` untuk item atau semua item user. 4. Badge dihitung ulang. |
| Alternatif | Notifikasi user lain ditolak; daftar kosong menampilkan empty state. |
| Pascakondisi | Read state berubah tanpa menghapus notifikasi durable. |

### UC-35 — Mengatur preferensi notifikasi

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna |
| Pemicu | Menyimpan switch kategori, reminder time, dan quiet hours. |
| Prasyarat | Login dan email terverifikasi. |
| Alur utama | 1. Service mengambil/membuat satu preference row. 2. Pengguna memilih master/category switches dan waktu. 3. Sistem memvalidasi pasangan quiet hours dan format `H:i`. 4. Preference di-update. |
| Alternatif | Hanya salah satu batas quiet hours atau format waktu invalid ditolak; field yang tidak dikirim dipertahankan sesuai service. |
| Pascakondisi | Reminder berikutnya mengikuti preferensi baru. |

### UC-36 — Memproses reminder terjadwal

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Scheduler/Queue Worker |
| Pemicu | Jadwal aplikasi menjalankan command reminder/delivery. |
| Prasyarat | Worker/scheduler aktif; preference user mengizinkan tipe dan waktu. |
| Alur utama | 1. Command memproses user dalam chunk. 2. `ReminderService` mengecek data aktual untuk jurnal, meal, plan, goal, weekly insight, dan wellness. 3. `NotificationService` membuat record dengan deduplication key. 4. Job mengirim saat di luar quiet hours. 5. `delivered_at` diisi atomik. |
| Alternatif | Duplikat ditolak constraint; quiet hours menunda `available_at`; retry job aman; kategori nonaktif dilewati. |
| Pascakondisi | Notifikasi durable tercipta/dikirim maksimum sekali untuk kunci deduplikasi. |

### UC-37 — Menerima notifikasi real-time

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Pengguna; Reverb/Client |
| Pemicu | Notifikasi eligible selesai dikirim. |
| Prasyarat | Channel privat terotorisasi dan client Echo terkoneksi. |
| Alur utama | 1. Sistem broadcast `NotificationCreated` dengan payload minimum. 2. Client privat menerima event. 3. Badge/dropdown diperbarui dan toast non-blocking muncul. |
| Alternatif | Reverb/client offline: event live tidak terlihat, tetapi record tetap tersedia di pusat notifikasi dan unread API. |
| Pascakondisi | UI dapat diperbarui; durability tidak bergantung pada broadcast. |

### UC-38 — Melihat dashboard admin agregat

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Admin |
| Pemicu | Membuka `/admin`. |
| Prasyarat | Login, email terverifikasi, role admin. |
| Alur utama | 1. Middleware memeriksa role. 2. Sistem menghitung total user biasa, user baru 30 hari, dan audit event 24 jam. 3. Dashboard menampilkan angka agregat. |
| Alternatif | User biasa mendapat 403/redirect sesuai middleware; admin tetap tidak dapat membuka data kesehatan privat lewat policy. |
| Pascakondisi | Tidak ada data berubah. |

### UC-39 — Mencatat audit event aman

| Elemen | Deskripsi |
| --- | --- |
| Aktor | Sistem |
| Pemicu | Aksi penting controller/service yang diaudit. |
| Prasyarat | Request/action context tersedia. |
| Alur utama | 1. Sistem menentukan aktor, aksi, subject, dan metadata whitelist. 2. Alamat IP di-hash. 3. User agent dan waktu dicatat. 4. Isi kesehatan, token, dan credential tidak dimasukkan. |
| Alternatif | Aktor dapat null untuk aksi sistem; subject polymorphic dapat sudah terhapus tetapi jejak tetap berguna. |
| Pascakondisi | `AuditLog` append-only tercipta tanpa `updated_at`. |

## Relasi include/extend utama

| Use case asal | Relasi | Use case tujuan | Makna |
| --- | --- | --- | --- |
| UC-12, UC-14, UC-19, UC-21, UC-24, UC-27, UC-31 | `include` | UC-39 | Mutasi sensitif tertentu menghasilkan audit aman. |
| UC-21 | `include` | UC-22 | Analisis selalu berakhir pada tahap review; pembuatan meal butuh konfirmasi. |
| UC-26 | `extend` | UC-27 | Hasil asesmen hanya menjadi histori jika pengguna memilih menyimpan. |
| UC-27 | `extend` | UC-28 | Assessment tersimpan dapat ditambahkan ke jurnal dengan konfirmasi. |
| UC-29 | `include` | UC-30 | Insight memakai overview/trend agregat sebagai sumber. |
| UC-32 | `include` | UC-33 | Plan berisi task, tetapi perubahan manual hanya untuk task yang diizinkan. |
| UC-36 | `include` | UC-37 | Reminder eligible dapat dibroadcast setelah tersimpan; broadcast bersifat tambahan. |

