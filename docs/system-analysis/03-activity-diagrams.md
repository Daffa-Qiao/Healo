# Activity Diagram

Diagram berikut mencakup seluruh kelompok use case aktif. ID pada judul merujuk ke [deskripsi use case](02-use-case-descriptions.md).

## AD-01 — Akses, autentikasi, dan akun (UC-01–UC-09)

```mermaid
flowchart TD
    A([Mulai]) --> B[Buka HEALO]
    B --> C{Sudah punya akun?}
    C -- Tidak --> D[Isi registrasi]
    D --> E{Data valid dan email unik?}
    E -- Tidak --> D
    E -- Ya --> F[Buat user dan kirim verifikasi]
    C -- Ya --> G[Isi kredensial]
    F --> G
    G --> H{Rate limit dan kredensial valid?}
    H -- Tidak --> I[Tampilkan error / tunggu throttle]
    I --> G
    H -- Ya --> J[Buat session atau token]
    J --> K{Email terverifikasi?}
    K -- Tidak --> L[Tampilkan verifikasi / kirim ulang]
    L --> M{Tautan signed valid?}
    M -- Ya --> N[Tandai email terverifikasi]
    M -- Tidak --> L
    K -- Ya --> O[Masuk dashboard]
    N --> O
    O --> P{Aksi akun}
    P -- Ubah profil/password/locale --> Q[Validasi dan simpan]
    Q --> O
    P -- Hapus akun --> R[Konfirmasi password]
    R --> S{Password benar?}
    S -- Tidak --> O
    S -- Ya --> T[Soft-delete user dan invalidasi session]
    P -- Logout --> U[Cabut session/token]
    T --> V([Selesai])
    U --> V
```

## AD-02 — Profil kesehatan dan BMI (UC-10–UC-13)

```mermaid
flowchart TD
    A([Mulai]) --> B[Pengguna membuka dashboard]
    B --> C{Health profile tersedia?}
    C -- Tidak --> D[Tampilkan empty state dan ajakan isi profil]
    D --> E[Pengguna membuka form dan isi data serta consent]
    E --> F{Valid?}
    F -- Tidak --> E
    F -- Ya --> G[Upsert profil; enkripsi kondisi dan alergi]
    C -- Ya --> H[Bangun snapshot owner-scoped]
    G --> H
    H --> I[Tampilkan dashboard]
    I --> J{Catat BMI?}
    J -- Ya --> K[Isi tinggi, berat, waktu ukur]
    K --> L{Input dalam rentang?}
    L -- Tidak --> K
    L -- Ya --> M[Hitung BMI dan kategori di server]
    M --> N[Simpan BmiRecord]
    N --> O[Sinkronkan profil bila diperlukan]
    O --> P[Tampilkan histori dan grafik]
    P --> Q{Hapus record?}
    Q -- Ya --> R{Milik pengguna?}
    R -- Tidak --> S[Tolak akses]
    R -- Ya --> T[Hapus record dan tulis audit]
    Q -- Tidak --> U([Selesai])
    T --> U
    S --> U
    J -- Tidak --> U
```

## AD-03 — Jurnal kesehatan (UC-14–UC-16)

```mermaid
flowchart TD
    A([Mulai]) --> B[Buka daftar/dashboard jurnal]
    B --> C[Ambil jurnal milik user]
    C --> D[Terapkan search, filter, periode, pagination]
    D --> E[Hitung overview, history, mood, dan tren]
    E --> F{Aksi pengguna}
    F -- Buat jurnal --> G[Isi judul, tanggal, mood, metrik]
    F -- Edit jurnal --> H[Cek policy pemilik]
    H --> G
    G --> I{Valid?}
    I -- Tidak --> G
    I -- Ya --> J[Simpan jurnal]
    J --> K{Kelola entry?}
    K -- Tambah/Ubah --> L[Pilih tipe, isi, urutan]
    L --> M{Valid dan owner?}
    M -- Tidak --> L
    M -- Ya --> N[Enkripsi dan simpan entry]
    K -- Hapus entry --> O[Cek owner melalui jurnal]
    O --> P[Hapus entry]
    N --> Q[Tampilkan detail terbaru]
    P --> Q
    F -- Hapus jurnal --> R[Cek policy pemilik]
    R --> S[Soft-delete jurnal dan audit]
    F -- Lihat saja --> T([Selesai])
    Q --> T
    S --> T
```

## AD-04 — Profil, dashboard, meal, dan item nutrisi (UC-17–UC-20)

```mermaid
flowchart TD
    A([Mulai]) --> B[Buka modul nutrisi]
    B --> C{Nutrition profile tersedia?}
    C -- Tidak --> D[Isi aktivitas, goal, diet, preferensi]
    D --> E{Target manual atau kalkulasi?}
    E -- Kalkulasi --> F[Ambil data health profile]
    F --> G[Hitung target kalori berbatas aman]
    E -- Manual --> H[Validasi target 1200–4500]
    G --> I[Upsert NutritionProfile]
    H --> I
    C -- Ya --> J[Ambil meal dan item milik user]
    I --> J
    J --> K[Hitung today dan weekly summary]
    K --> L[Tampilkan dashboard nutrisi]
    L --> M{Aksi meal}
    M -- Buat/Ubah --> N[Isi tanggal, tipe, catatan]
    N --> O{Valid dan owner?}
    O -- Tidak --> N
    O -- Ya --> P[Simpan Meal]
    P --> Q{Kelola item?}
    Q -- Ya --> R[Isi nama, porsi, kalori, makro, sumber]
    R --> S{Valid?}
    S -- Tidak --> R
    S -- Ya --> T[Simpan/Ubah/Hapus MealItem]
    T --> U[Hitung ulang total meal dan daily summary]
    Q -- Tidak --> U
    M -- Hapus --> V[Cek policy lalu soft-delete meal]
    V --> U
    U --> W([Selesai])
    M -- Lihat saja --> W
```

## AD-05 — Analisis foto makanan dan menu sehat (UC-21–UC-23)

```mermaid
flowchart TD
    A([Mulai]) --> B{Pilih fitur}
    B -- Analisis foto --> C[Unggah gambar]
    C --> D{File valid dan rate limit tersedia?}
    D -- Tidak --> E[Tampilkan error]
    D -- Ya --> F[Simpan gambar privat dan buat draft]
    F --> G[Panggil FoodAnalysisProvider]
    G --> H{Provider berhasil?}
    H -- Tidak --> I[Tandai gagal / fallback aman]
    H -- Ya --> J[Enkripsi hasil dan simpan confidence]
    J --> K[Tampilkan image privat, hasil, warning]
    K --> L{Konfirmasi?}
    L -- Koreksi --> M[Edit nilai meal dan item]
    M --> K
    L -- Batal/Hapus --> N[Batalkan draft dan hapus sesuai retensi]
    L -- Ya --> O{Owner, status, dan input valid?}
    O -- Tidak --> K
    O -- Ya --> P[Buat Meal dan MealItem dari nilai final]
    P --> Q[Tandai analysis confirmed]
    B -- Menu sehat --> R[Masukkan pencarian/filter]
    R --> S[Filter menu aktif dan rekomendasi profil]
    S --> T[Tampilkan nutrien, bahan, cara, sumber]
    E --> U([Selesai])
    I --> U
    N --> U
    Q --> U
    T --> U
```

## AD-06 — Asisten kesehatan (UC-24–UC-25)

```mermaid
flowchart TD
    A([Mulai]) --> B[Buka daftar percakapan]
    B --> C{Pilih aksi}
    C -- Buat --> D[Isi judul]
    D --> E[Validasi dan simpan judul terenkripsi]
    E --> F[Buka conversation]
    C -- Buka --> G{Conversation milik user?}
    G -- Tidak --> H[Tolak akses]
    G -- Ya --> F
    F --> I[Masukkan pesan]
    I --> J{Valid dan tidak ter-throttle?}
    J -- Tidak --> I
    J -- Ya --> K[Simpan pesan user terenkripsi]
    K --> L[Bangun konteks minimum]
    L --> M[Panggil HealthAIProvider]
    M --> N{Schema/provider berhasil?}
    N -- Tidak --> O[Buat respons fallback]
    N -- Ya --> P[Periksa emergency dan medical safety]
    P --> Q{Output aman?}
    Q -- Tidak --> O
    Q -- Ya --> R[Simpan respons asisten terenkripsi]
    O --> R
    R --> S[Tampilkan respons dan disclaimer]
    C -- Hapus --> T[Cek policy pemilik]
    T --> U[Hapus conversation dan audit]
    S --> V([Selesai])
    U --> V
    H --> V
```

## AD-07 — Symptom checker dan simpan ke jurnal (UC-26–UC-28)

```mermaid
flowchart TD
    A([Mulai]) --> B[Isi uraian gejala]
    B --> C{Input valid dan throttle tersedia?}
    C -- Tidak --> B
    C -- Ya --> D[Jalankan emergency detection lokal]
    D --> E{Red flag darurat?}
    E -- Ya --> F[Bentuk hasil emergency dan arahan bantuan]
    E -- Tidak --> G[Panggil provider dengan konteks minimum]
    G --> H{Output terstruktur dan aman?}
    H -- Tidak --> I[Gunakan fallback assessment]
    H -- Ya --> J[Bangun hasil assessment]
    F --> K[Tampilkan urgensi, red flag, rekomendasi, disclaimer]
    I --> K
    J --> K
    K --> L{Simpan history?}
    L -- Tidak --> M([Selesai])
    L -- Ya --> N[Enkripsi dan simpan SymptomAssessment]
    N --> O{Simpan ke jurnal?}
    O -- Tidak --> M
    O -- Ya --> P[Minta konfirmasi eksplisit]
    P --> Q{Diterima, owner benar, belum tersimpan?}
    Q -- Tidak --> R[Tolak atau gunakan tautan jurnal yang ada]
    Q -- Ya --> S[Buat HealthJournal dan entries]
    S --> T[Tautkan assessment; isi saved_at; audit]
    R --> M
    T --> M
```

## AD-08 — Insight dan health goal (UC-29–UC-31)

```mermaid
flowchart TD
    A([Mulai]) --> B[Pilih periode 7/30/90 hari]
    B --> C[Ambil data BMI, jurnal, meal, goal milik user]
    C --> D[Hitung overview, tren, weekly summary]
    D --> E[Hasilkan rule-based insights]
    E --> F{AI insight dikonfigurasi dan throttle tersedia?}
    F -- Tidak --> G[Gunakan insight lokal]
    F -- Ya --> H[Bentuk konteks agregat minimum]
    H --> I[Panggil provider]
    I --> J{Schema dan safety valid?}
    J -- Tidak --> G
    J -- Ya --> K[Cache dan gabungkan insight aman]
    G --> L[Tampilkan overview, chart, insight]
    K --> L
    L --> M{Kelola goal?}
    M -- Buat/Ubah --> N[Isi tipe, target, tanggal, status]
    N --> O{Rentang spesifik tipe valid?}
    O -- Tidak --> N
    O -- Ya --> P[Simpan goal owner-scoped]
    P --> Q[Hitung current value dari source record]
    Q --> R[Tampilkan progres live]
    M -- Hapus --> S[Cek owner, hapus, audit]
    M -- Tidak --> T([Selesai])
    R --> T
    S --> T
```

## AD-09 — Rencana kesehatan harian (UC-32–UC-33)

```mermaid
flowchart TD
    A([Mulai]) --> B[Buka health plan / pilih generate]
    B --> C[Cari plan milik user untuk tanggal]
    C --> D{Plan sudah ada?}
    D -- Tidak --> E[Buat satu HealthPlan]
    D -- Ya --> F[Gunakan plan yang sama]
    E --> G[Baca profil, nutrisi, meal, jurnal, BMI, goal]
    F --> G
    G --> H[Generator deterministik menyusun task terkontrol]
    H --> I[Upsert task tanpa duplikasi]
    I --> J[Sinkronkan task data-derived dari source record]
    J --> K[Hitung progres dan status server-side]
    K --> L{AI personalization tersedia?}
    L -- Ya --> M[Minta wording/motivasi agregat]
    M --> N{Output aman?}
    N -- Ya --> O[Gunakan personalisasi]
    N -- Tidak --> P[Gunakan wording lokal]
    L -- Tidak --> P
    O --> Q[Tampilkan pending/completed dan quick action]
    P --> Q
    Q --> R{Complete/uncomplete task?}
    R -- Tidak --> S([Selesai])
    R -- Ya --> T{Owner dan completion mode manual?}
    T -- Tidak --> U[Tolak; sinkronkan dari data aktual]
    T -- Ya --> V[Ubah status dan completed_at]
    U --> W[Hitung ulang progres plan]
    V --> W
    W --> S
```

## AD-10 — Notifikasi, reminder, dan real-time (UC-34–UC-37)

```mermaid
flowchart TD
    A([Scheduler berjalan]) --> B[Ambil user dalam chunk]
    B --> C[Ambil/buat NotificationPreference]
    C --> D{Master dan kategori aktif?}
    D -- Tidak --> E[Lewati kandidat]
    D -- Ya --> F[Periksa journal, meal, plan, goal, insight, wellness aktual]
    F --> G{Reminder diperlukan?}
    G -- Tidak --> E
    G -- Ya --> H[Bentuk deduplication key]
    H --> I{Sudah pernah dibuat?}
    I -- Ya --> E
    I -- Tidak --> J{Sedang quiet hours?}
    J -- Ya --> K[Set available_at setelah quiet hours]
    J -- Tidak --> L[Buat notifikasi tersedia sekarang]
    K --> M[Delivery command memindai notifikasi due]
    L --> M
    M --> N[Klaim delivery secara atomik]
    N --> O{Eligible dan belum delivered?}
    O -- Tidak --> P[Retry-safe selesai]
    O -- Ya --> Q[Isi delivered_at dan broadcast event privat]
    Q --> R{Client Reverb online?}
    R -- Ya --> S[Perbarui badge/dropdown dan tampilkan toast]
    R -- Tidak --> T[Notifikasi tetap di database]
    S --> U[Pengguna membuka notification center]
    T --> U
    U --> V[Filter/paginate/read/read-all]
    V --> W([Selesai])
    E --> W
    P --> W
```

## AD-11 — Dashboard admin dan audit (UC-38–UC-39)

```mermaid
flowchart TD
    A([Mulai]) --> B[Pengguna membuka /admin]
    B --> C{Login dan email verified?}
    C -- Tidak --> D[Arahkan ke autentikasi/verifikasi]
    C -- Ya --> E{Role admin?}
    E -- Tidak --> F[Tolak akses]
    E -- Ya --> G[Hitung total user biasa]
    G --> H[Hitung user baru 30 hari]
    H --> I[Hitung audit event 24 jam]
    I --> J[Tampilkan statistik agregat]
    J --> K([Selesai])
    D --> K
    F --> K

    L([Aksi sensitif sistem]) --> M[Pilih metadata aman]
    M --> N[Hash IP; catat actor/action/subject/user-agent]
    N --> O{Mengandung isi kesehatan/credential?}
    O -- Ya --> P[Buang field sensitif]
    O -- Tidak --> Q[Simpan AuditLog append-only]
    P --> Q
    Q --> R([Audit selesai])
```

