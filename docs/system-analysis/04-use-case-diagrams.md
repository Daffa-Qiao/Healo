# Use Case Diagram

Mermaid belum memiliki notasi native UML use case. Karena itu, aktor digambar sebagai node persegi dan use case sebagai node oval/stadium; relasi `include` dan `extend` diberi label eksplisit.

Catatan autentikasi: route web kesehatan menggunakan `auth + verified`, sedangkan endpoint API v1 privat pada implementasi saat ini menggunakan `auth:sanctum + throttle`.

## UCD-01 — Gambaran umum sistem

```mermaid
flowchart LR
    Guest[👤 Pengunjung]
    User[👤 Pengguna]
    Admin[👤 Admin]
    AI[🤖 Penyedia AI]
    Worker[⚙️ Scheduler / Queue Worker]
    Live[📡 Reverb / Client]

    subgraph HEALO[HEALO]
        Access([Akses dan autentikasi<br/>UC-01–UC-09])
        Health([Dashboard dan kesehatan<br/>UC-10–UC-16])
        Nutrition([Nutrisi dan makanan<br/>UC-17–UC-23])
        Assistant([Asisten dan gejala<br/>UC-24–UC-28])
        Wellness([Insight, goal, plan<br/>UC-29–UC-33])
        Notify([Notifikasi dan reminder<br/>UC-34–UC-37])
        AdminDash([Dashboard admin<br/>UC-38])
        Audit([Audit aman<br/>UC-39])
    end

    Guest --> Access
    User --> Access
    User --> Health
    User --> Nutrition
    User --> Assistant
    User --> Wellness
    User --> Notify
    Admin --> AdminDash
    AI --> Nutrition
    AI --> Assistant
    AI --> Wellness
    Worker --> Notify
    Live --> Notify
    Health -. include .-> Audit
    Nutrition -. include .-> Audit
    Assistant -. include .-> Audit
    Wellness -. include .-> Audit
```

## UCD-02 — Akses, kesehatan dasar, dan jurnal

```mermaid
flowchart LR
    Guest[👤 Pengunjung]
    User[👤 Pengguna]
    Api[💻 Klien API]

    subgraph HEALO[HEALO — Core & Health]
        UC01([UC-01 Landing page])
        UC02([UC-02 Ubah bahasa])
        UC03([UC-03 Registrasi])
        UC04([UC-04 Login / logout])
        UC05([UC-05 Reset password])
        UC06([UC-06 Verifikasi email])
        UC07([UC-07 Kelola profil akun])
        UC08([UC-08 Hapus akun])
        UC09([UC-09 Autentikasi API])
        UC10([UC-10 Dashboard kesehatan])
        UC11([UC-11 Profil kesehatan])
        UC12([UC-12 Catat BMI])
        UC13([UC-13 Hapus BMI])
        UC14([UC-14 CRUD jurnal])
        UC15([UC-15 CRUD entry jurnal])
        UC16([UC-16 Cari/filter/tren jurnal])
        UC39([UC-39 Audit aman])
    end

    Guest --> UC01
    Guest --> UC02
    Guest --> UC03
    Guest --> UC04
    Guest --> UC05
    User --> UC02
    User --> UC04
    User --> UC06
    User --> UC07
    User --> UC08
    User --> UC10
    User --> UC11
    User --> UC12
    User --> UC13
    User --> UC14
    User --> UC15
    User --> UC16
    Api --> UC03
    Api --> UC04
    Api --> UC09
    UC10 -. uses .-> UC11
    UC14 -. include .-> UC15
    UC16 -. reads .-> UC14
    UC12 -. include .-> UC39
    UC13 -. include .-> UC39
    UC14 -. include .-> UC39
```

## UCD-03 — Nutrisi dan analisis makanan

```mermaid
flowchart LR
    User[👤 Pengguna]
    Api[💻 Klien API]
    FoodAI[🤖 Food Analysis Provider]

    subgraph HEALO[HEALO — Nutrition]
        UC17([UC-17 Profil nutrisi])
        UC18([UC-18 Ringkasan nutrisi])
        UC19([UC-19 CRUD meal])
        UC20([UC-20 CRUD meal item])
        UC21([UC-21 Analisis foto])
        UC22([UC-22 Review & konfirmasi])
        UC23([UC-23 Menu sehat])
        UC39([UC-39 Audit aman])
    end

    User --> UC17
    User --> UC18
    User --> UC19
    User --> UC20
    User --> UC21
    User --> UC22
    User --> UC23
    Api --> UC17
    Api --> UC18
    Api --> UC19
    Api --> UC20
    Api --> UC21
    Api --> UC22
    Api --> UC23
    FoodAI --> UC21
    UC18 -. reads .-> UC17
    UC18 -. reads .-> UC19
    UC19 -. include .-> UC20
    UC21 -. include .-> UC22
    UC22 -. create .-> UC19
    UC17 -. personalize .-> UC23
    UC19 -. include .-> UC39
    UC21 -. include .-> UC39
```

## UCD-04 — AI health, insight, goal, dan health plan

```mermaid
flowchart LR
    User[👤 Pengguna]
    Api[💻 Klien API]
    AI[🤖 Health AI Provider]

    subgraph HEALO[HEALO — AI & Wellness]
        UC24([UC-24 Kelola conversation])
        UC25([UC-25 Kirim pesan asisten])
        UC26([UC-26 Periksa gejala])
        UC27([UC-27 Riwayat assessment])
        UC28([UC-28 Simpan assessment ke jurnal])
        UC29([UC-29 Insight personal])
        UC30([UC-30 Tren & weekly summary])
        UC31([UC-31 Health goal])
        UC32([UC-32 Health plan harian])
        UC33([UC-33 Complete/reopen task])
        UC14([UC-14 CRUD jurnal])
        UC39([UC-39 Audit aman])
    end

    User --> UC24
    User --> UC25
    User --> UC26
    User --> UC27
    User --> UC28
    User --> UC29
    User --> UC30
    User --> UC31
    User --> UC32
    User --> UC33
    Api --> UC24
    Api --> UC25
    Api --> UC26
    Api --> UC27
    Api --> UC28
    Api --> UC29
    Api --> UC30
    Api --> UC31
    Api --> UC32
    Api --> UC33
    AI --> UC25
    AI --> UC26
    AI --> UC29
    AI --> UC32
    UC24 -. include .-> UC25
    UC26 -. extend: save .-> UC27
    UC27 -. extend: confirm .-> UC28
    UC28 -. create .-> UC14
    UC29 -. include .-> UC30
    UC31 -. informs .-> UC29
    UC31 -. informs .-> UC32
    UC32 -. include .-> UC33
    UC24 -. include .-> UC39
    UC27 -. include .-> UC39
    UC31 -. include .-> UC39
```

## UCD-05 — Notifikasi, reminder, dan administrasi

```mermaid
flowchart LR
    User[👤 Pengguna]
    Admin[👤 Admin]
    Worker[⚙️ Scheduler / Queue Worker]
    Live[📡 Reverb / Client]
    System[⚙️ Sistem]

    subgraph HEALO[HEALO — Operations]
        UC34([UC-34 Pusat notifikasi])
        UC35([UC-35 Preferensi notifikasi])
        UC36([UC-36 Proses reminder])
        UC37([UC-37 Notifikasi real-time])
        UC38([UC-38 Dashboard admin agregat])
        UC39([UC-39 Audit aman])
    end

    User --> UC34
    User --> UC35
    User --> UC37
    Worker --> UC36
    Live --> UC37
    Admin --> UC38
    System --> UC39
    UC36 -. reads .-> UC35
    UC36 -. create .-> UC34
    UC36 -. extend: broadcast .-> UC37
    UC38 -. reads aggregate .-> UC39
```

## UCD-06 — Batas fitur planned

Node berikut sengaja tidak dihubungkan ke sistem aktif karena route/surface penuh belum diimplementasikan.

```mermaid
flowchart LR
    Admin[👤 Admin]

    subgraph Planned[Roadmap — Planned]
        P01([UC-P01 Kelola lifecycle user])
        P02([UC-P02 Kelola food/menu])
        P03([UC-P03 Telemetri AI & error])
        P04([UC-P04 Review audit log])
        P05([UC-P05 Ekspor PDF])
    end

    Admin -. planned .-> P01
    Admin -. planned .-> P02
    Admin -. planned .-> P03
    Admin -. planned .-> P04
    Admin -. planned .-> P05
```

