# Analisis Sistem HEALO

Dokumen ini memetakan implementasi aplikasi HEALO per 26 Oktober 2026. Sumber analisis utama adalah route web/API, controller, Form Request, policy, service, model, migration, test, dan `docs/implementation-roadmap.md`.

## Paket dokumen

1. [Pemetaan product backlog ke use case](01-product-backlog-use-case-map.md)
2. [Deskripsi use case](02-use-case-descriptions.md)
3. [Activity diagram](03-activity-diagrams.md)
4. [Use case diagram](04-use-case-diagrams.md)
5. [Class diagram](05-class-diagrams.md)

## Batasan dan konvensi

- Cakupan utama adalah fitur yang benar-benar memiliki route/implementasi saat ini, bukan seluruh ide pada roadmap.
- Fitur administrasi fase 8 selain dashboard agregat serta ekspor PDF fase 9 ditandai **planned** dan tidak digambar sebagai kemampuan aktif.
- Aktor `Pengguna` berarti akun terautentikasi. Sebagian besar area kesehatan juga mensyaratkan email terverifikasi.
- API v1 bukan use case bisnis terpisah apabila perilakunya sama dengan web; kolom kanal menunjukkan apakah use case tersedia melalui Web, API, atau keduanya.
- Semua record kesehatan pribadi bersifat owner-scoped. Peran admin tidak memperoleh bypass otomatis ke data kesehatan pribadi.
- Diagram menggunakan Mermaid agar dapat dirender oleh GitHub, GitLab, editor Markdown modern, atau Mermaid Live Editor.

## Ringkasan aktor

| Aktor | Tanggung jawab/interaksi |
| --- | --- |
| Pengunjung | Melihat landing page, memilih bahasa, mendaftar, login, dan memulihkan kata sandi. |
| Pengguna | Mengelola profil dan seluruh data kesehatan miliknya. |
| Admin | Melihat dashboard agregat yang dilindungi role; tidak dapat membaca record kesehatan pribadi melalui policy. |
| Penyedia AI | Menghasilkan respons asisten, analisis gejala, insight, personalisasi rencana, atau draf analisis makanan melalui abstraksi provider. |
| Scheduler/Queue Worker | Menilai kebutuhan reminder, membuat notifikasi terdeduplikasi, dan mengirimkannya sesuai quiet hours. |
| Reverb/Client | Menyampaikan event notifikasi privat secara real-time; pusat notifikasi tetap berfungsi bila kanal real-time gagal. |

## Traceability singkat

Alur keterlacakan dokumentasi adalah:

```mermaid
flowchart LR
    PB[Product Backlog] --> UC[Use Case]
    UC --> UCD[Use Case Diagram]
    UC --> UCT[Use Case Description]
    UCT --> AD[Activity Diagram]
    UC --> CD[Class Diagram]
    CD --> IMP[Model / Service / Controller]
```

