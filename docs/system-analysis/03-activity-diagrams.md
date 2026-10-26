# Activity Diagram Swimlane

Seluruh activity diagram menggunakan swimlane. Hanya User dan Admin yang diperlakukan sebagai aktor. Lane Sistem HEALO menunjukkan tanggung jawab aplikasi, database, scheduler, queue, provider, dan layanan internal; lane tersebut bukan aktor tambahan.

Versi poster visual bergaya infografik tersedia dalam format [PNG](activity-diagram-poster.png) dan [HTML yang dapat diedit](activity-diagram-poster.html).

![Poster Activity Diagram HEALO](activity-diagram-poster.png)

ID pada judul merujuk ke [deskripsi use case](02-use-case-descriptions.md).

## AD-01 — Akses, autentikasi, dan akun (UC-01–UC-09)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Buka HEALO]
        U3{Sudah punya akun?}
        U4[Isi registrasi]
        U5[Isi kredensial]
        U6[Buka tautan verifikasi]
        U7[Pilih aksi akun]
        U8[Ubah profil, password, atau locale]
        U9[Konfirmasi hapus akun]
        U10[Pilih logout]
        U11([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1{Data registrasi valid?}
        S2[Buat user dan kirim verifikasi]
        S3{Kredensial dan throttle valid?}
        S4[Buat session atau token]
        S5{Email web sudah terverifikasi?}
        S6[Validasi signed URL]
        S7[Tandai email terverifikasi]
        S8[Validasi dan simpan perubahan]
        S9{Password konfirmasi benar?}
        S10[Soft-delete user dan invalidasi session]
        S11[Cabut session atau token]
        S12[Tampilkan error]
    end

    U1 --> U2 --> U3
    U3 -- Tidak --> U4 --> S1
    S1 -- Tidak --> S12 --> U4
    S1 -- Ya --> S2 --> U5
    U3 -- Ya --> U5
    U5 --> S3
    S3 -- Tidak --> S12 --> U5
    S3 -- Ya --> S4 --> S5
    S5 -- Tidak --> U6 --> S6
    S6 -- Valid --> S7 --> U7
    S6 -- Tidak valid --> S12 --> U6
    S5 -- Ya --> U7
    U7 -- Ubah akun --> U8 --> S8 --> U7
    U7 -- Hapus akun --> U9 --> S9
    S9 -- Tidak --> S12 --> U7
    S9 -- Ya --> S10 --> U11
    U7 -- Logout --> U10 --> S11 --> U11
```

## AD-02 — Dashboard, profil kesehatan, dan BMI (UC-10–UC-13)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Buka dashboard]
        U3[Buka form profil]
        U4[Isi data kesehatan dan consent]
        U5[Isi tinggi, berat, dan waktu ukur]
        U6[Pilih hapus record BMI]
        U7([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1[Bangun snapshot owner-scoped]
        S2{Health profile tersedia?}
        S3[Tampilkan empty state dan ajakan]
        S4{Data profil valid?}
        S5[Upsert profil dan enkripsi field sensitif]
        S6[Tampilkan dashboard]
        S7{Input BMI valid?}
        S8[Hitung BMI dan kategori server-side]
        S9[Simpan record dan sinkronkan profil]
        S10[Tampilkan histori dan grafik]
        S11{Record milik user?}
        S12[Hapus record dan tulis audit]
        S13[Tolak akses atau tampilkan error]
    end

    U1 --> U2 --> S1 --> S2
    S2 -- Tidak --> S3 --> U3 --> U4 --> S4
    S4 -- Tidak --> S13 --> U4
    S4 -- Ya --> S5 --> S1
    S2 -- Ya --> S6 --> U5
    U5 --> S7
    S7 -- Tidak --> S13 --> U5
    S7 -- Ya --> S8 --> S9 --> S10
    S10 --> U6 --> S11
    S11 -- Tidak --> S13 --> U7
    S11 -- Ya --> S12 --> U7
```

## AD-03 — Jurnal kesehatan (UC-14–UC-16)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Buka daftar atau dashboard jurnal]
        U3[Pilih buat, edit, lihat, atau hapus]
        U4[Isi jurnal dan metrik harian]
        U5[Pilih tambah, ubah, atau hapus entry]
        U6[Isi tipe, konten, dan urutan]
        U7([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1[Ambil jurnal milik user]
        S2[Terapkan search, filter, periode, pagination]
        S3[Hitung overview dan tren]
        S4{Owner dan input jurnal valid?}
        S5[Simpan atau soft-delete jurnal]
        S6{Owner dan entry valid?}
        S7[Enkripsi lalu simpan atau hapus entry]
        S8[Tampilkan hasil terbaru]
        S9[Tampilkan error atau tolak akses]
    end

    U1 --> U2 --> S1 --> S2 --> S3 --> U3
    U3 -- Buat atau edit --> U4 --> S4
    U3 -- Hapus --> S4
    S4 -- Tidak --> S9 --> U3
    S4 -- Ya --> S5 --> U5
    U5 -- Tambah atau ubah --> U6 --> S6
    U5 -- Hapus --> S6
    U5 -- Tidak ada --> S8 --> U7
    S6 -- Tidak --> S9 --> U5
    S6 -- Ya --> S7 --> S8 --> U7
    U3 -- Lihat --> S8 --> U7
```

## AD-04 — Profil nutrisi, meal, dan item (UC-17–UC-20)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Buka modul nutrisi]
        U3[Isi profil nutrisi]
        U4[Pilih target manual atau kalkulasi]
        U5[Pilih aksi meal]
        U6[Isi tanggal, tipe, dan catatan]
        U7[Isi item makanan dan nutrien]
        U8([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1{Profil nutrisi tersedia?}
        S2[Validasi preferensi dan target]
        S3[Hitung target dari health profile]
        S4[Upsert NutritionProfile]
        S5[Ambil meal dan item milik user]
        S6[Hitung ringkasan harian dan mingguan]
        S7{Meal valid dan owner benar?}
        S8[Simpan atau soft-delete meal]
        S9{Item valid dan owner benar?}
        S10[Simpan, ubah, atau hapus item]
        S11[Hitung ulang total dan daily summary]
        S12[Tampilkan error]
    end

    U1 --> U2 --> S1
    S1 -- Tidak --> U3 --> U4
    U4 -- Manual --> S2
    U4 -- Kalkulasi --> S3
    S2 --> S4
    S3 --> S4
    S4 --> S5
    S1 -- Ya --> S5
    S5 --> S6 --> U5
    U5 -- Buat atau edit --> U6 --> S7
    U5 -- Hapus --> S7
    S7 -- Tidak --> S12 --> U5
    S7 -- Ya --> S8 --> U7
    U7 --> S9
    S9 -- Tidak --> S12 --> U7
    S9 -- Ya --> S10 --> S11 --> U8
    U5 -- Lihat saja --> U8
```

## AD-05 — Analisis foto makanan dan menu sehat (UC-21–UC-23)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Pilih analisis foto atau menu sehat]
        U3[Unggah gambar]
        U4[Review dan koreksi hasil]
        U5[Pilih konfirmasi atau batal]
        U6[Masukkan pencarian dan filter menu]
        U7([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1{File valid dan throttle tersedia?}
        S2[Simpan gambar privat dan buat draft]
        S3[Analisis melalui provider internal]
        S4{Provider berhasil?}
        S5[Enkripsi hasil dan confidence]
        S6[Tandai gagal atau gunakan fallback aman]
        S7{Draft owner, status, dan input valid?}
        S8[Buat Meal dan MealItem dari nilai final]
        S9[Tandai confirmed]
        S10[Batalkan draft sesuai retensi]
        S11[Filter menu aktif dan rekomendasi profil]
        S12[Tampilkan menu, nutrien, dan sumber]
        S13[Tampilkan error]
    end

    U1 --> U2
    U2 -- Analisis foto --> U3 --> S1
    S1 -- Tidak --> S13 --> U3
    S1 -- Ya --> S2 --> S3 --> S4
    S4 -- Tidak --> S6 --> U7
    S4 -- Ya --> S5 --> U4 --> U5
    U5 -- Konfirmasi --> S7
    S7 -- Tidak --> S13 --> U4
    S7 -- Ya --> S8 --> S9 --> U7
    U5 -- Batal --> S10 --> U7
    U2 -- Menu sehat --> U6 --> S11 --> S12 --> U7
```

## AD-06 — Asisten kesehatan (UC-24–UC-25)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Buka percakapan]
        U3[Buat atau pilih conversation]
        U4[Masukkan pesan]
        U5[Pilih hapus conversation]
        U6([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1{Conversation milik user?}
        S2[Simpan judul atau pesan terenkripsi]
        S3{Input valid dan tidak ter-throttle?}
        S4[Bangun konteks minimum]
        S5[Minta respons melalui provider internal]
        S6{Schema dan medical safety valid?}
        S7[Gunakan fallback aman]
        S8[Simpan dan tampilkan respons]
        S9[Hapus conversation dan tulis audit]
        S10[Tolak akses atau tampilkan error]
    end

    U1 --> U2 --> U3 --> S1
    S1 -- Tidak --> S10 --> U6
    S1 -- Ya --> S2 --> U4 --> S3
    S3 -- Tidak --> S10 --> U4
    S3 -- Ya --> S2 --> S4 --> S5 --> S6
    S6 -- Tidak --> S7 --> S8 --> U5
    S6 -- Ya --> S8 --> U5
    U5 -- Ya --> S1
    S1 -- Hapus valid --> S9 --> U6
    U5 -- Tidak --> U6
```

## AD-07 — Symptom checker dan simpan ke jurnal (UC-26–UC-28)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Isi uraian gejala]
        U3[Pilih simpan history]
        U4[Pilih simpan ke jurnal]
        U5[Berikan konfirmasi eksplisit]
        U6([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1{Input valid dan throttle tersedia?}
        S2[Jalankan emergency detection lokal]
        S3{Ada red flag darurat?}
        S4[Bentuk hasil emergency]
        S5[Analisis melalui provider internal]
        S6{Output terstruktur dan aman?}
        S7[Gunakan fallback assessment]
        S8[Tampilkan urgensi, red flag, dan rekomendasi]
        S9[Enkripsi dan simpan SymptomAssessment]
        S10{Owner benar dan belum tersimpan ke jurnal?}
        S11[Buat jurnal dan entries]
        S12[Tautkan assessment dan tulis audit]
        S13[Tolak atau gunakan jurnal yang sudah ada]
        S14[Tampilkan error]
    end

    U1 --> U2 --> S1
    S1 -- Tidak --> S14 --> U2
    S1 -- Ya --> S2 --> S3
    S3 -- Ya --> S4 --> S8
    S3 -- Tidak --> S5 --> S6
    S6 -- Tidak --> S7 --> S8
    S6 -- Ya --> S8
    S8 --> U3
    U3 -- Tidak --> U6
    U3 -- Ya --> S9 --> U4
    U4 -- Tidak --> U6
    U4 -- Ya --> U5 --> S10
    S10 -- Tidak --> S13 --> U6
    S10 -- Ya --> S11 --> S12 --> U6
```

## AD-08 — Insight dan health goal (UC-29–UC-31)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Pilih periode 7, 30, atau 90 hari]
        U3[Lihat overview, grafik, dan insight]
        U4[Pilih buat, ubah, atau hapus goal]
        U5[Isi target, tanggal, dan status]
        U6([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1[Ambil data owner-scoped]
        S2[Hitung overview, tren, dan weekly summary]
        S3[Hasilkan rule-based insight]
        S4{AI internal tersedia dan output aman?}
        S5[Gabungkan insight aman]
        S6[Gunakan insight lokal]
        S7{Goal owner dan rentang target valid?}
        S8[Simpan, ubah, atau hapus goal]
        S9[Hitung progress dari source record]
        S10[Tampilkan error]
    end

    U1 --> U2 --> S1 --> S2 --> S3 --> S4
    S4 -- Ya --> S5 --> U3
    S4 -- Tidak --> S6 --> U3
    U3 --> U4
    U4 -- Buat atau ubah --> U5 --> S7
    U4 -- Hapus --> S7
    U4 -- Tidak --> U6
    S7 -- Tidak --> S10 --> U4
    S7 -- Ya --> S8 --> S9 --> U6
```

## AD-09 — Rencana kesehatan harian (UC-32–UC-33)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Buka atau generate health plan]
        U3[Lihat task pending dan completed]
        U4[Pilih complete atau uncomplete]
        U5([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1[Cari plan user untuk tanggal]
        S2{Plan sudah ada?}
        S3[Buat satu HealthPlan]
        S4[Baca profil, nutrisi, meal, jurnal, BMI, goal]
        S5[Generate dan upsert task tanpa duplikasi]
        S6[Sinkronkan task data-derived]
        S7[Hitung progress dan status server-side]
        S8[Tambahkan wording aman atau fallback lokal]
        S9{Owner dan completion mode manual?}
        S10[Ubah status serta completed_at]
        S11[Tolak perubahan manual dan sinkronkan source]
    end

    U1 --> U2 --> S1 --> S2
    S2 -- Tidak --> S3 --> S4
    S2 -- Ya --> S4
    S4 --> S5 --> S6 --> S7 --> S8 --> U3
    U3 --> U4
    U4 -- Tidak ada --> U5
    U4 -- Ada --> S9
    S9 -- Ya --> S10 --> S7
    S9 -- Tidak --> S11 --> S7
```

## AD-10 — Notifikasi, reminder, dan real-time (UC-34–UC-37)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1([Mulai])
        U2[Atur kategori, reminder time, dan quiet hours]
        U3[Terima badge atau toast]
        U4[Buka notification center]
        U5[Filter, baca, atau read-all]
        U6([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1[Validasi dan simpan preferensi]
        S2[Scheduler memproses user per chunk]
        S3{Master dan kategori aktif?}
        S4[Periksa data aktual]
        S5{Reminder diperlukan dan belum terduplikasi?}
        S6{Sedang quiet hours?}
        S7[Set available_at setelah quiet hours]
        S8[Buat notifikasi tersedia sekarang]
        S9[Delivery command mengklaim secara atomik]
        S10[Simpan delivered_at dan broadcast privat]
        S11{Client real-time terhubung?}
        S12[Notifikasi tetap durable di database]
        S13[Perbarui read state dan unread count]
    end

    U1 --> U2 --> S1 --> S2 --> S3
    S3 -- Tidak --> U6
    S3 -- Ya --> S4 --> S5
    S5 -- Tidak --> U6
    S5 -- Ya --> S6
    S6 -- Ya --> S7 --> S9
    S6 -- Tidak --> S8 --> S9
    S9 --> S10 --> S11
    S11 -- Ya --> U3 --> U4
    S11 -- Tidak --> S12 --> U4
    U4 --> U5 --> S13 --> U6
```

## AD-11 — Dashboard admin dan audit (UC-38–UC-39)

```mermaid
flowchart LR
    subgraph U["User"]
        direction TB
        U1[Melakukan aksi aplikasi]
    end

    subgraph A["Admin"]
        direction TB
        A1([Mulai])
        A2[Buka dashboard admin]
        A3[Lihat statistik agregat]
        A4([Selesai])
    end

    subgraph S["Sistem HEALO"]
        direction TB
        S1{Login, verified, dan role admin?}
        S2[Hitung total user, user baru, dan audit 24 jam]
        S3[Tolak atau arahkan ke autentikasi]
        S4[Pilih metadata audit yang aman]
        S5[Hash IP dan buang field sensitif]
        S6[Simpan AuditLog append-only]
    end

    A1 --> A2 --> S1
    S1 -- Tidak --> S3 --> A4
    S1 -- Ya --> S2 --> A3 --> A4
    U1 --> S4
    A2 -. aksi admin .-> S4
    S4 --> S5 --> S6
```
