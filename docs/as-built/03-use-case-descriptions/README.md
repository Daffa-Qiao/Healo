# Fase 3 — Use Case Descriptions

Dokumen fase ini berisi spesifikasi untuk seluruh **43 use case CONFIRMED** pada katalog Fase 2.

## Daftar dokumen

1. [Public, Authentication, dan Account](01-public-auth-account.md) — 10 use case
2. [Dashboard dan Personal Health Records](02-health-records.md) — 7 use case
3. [Nutrition, Meal, Food Analysis, dan Healthy Menu](03-nutrition-food.md) — 9 use case
4. [Healbot dan Symptom Checker](04-health-ai.md) — 5 use case
5. [Insights, Goals, dan Health Plan](05-insights-goals-plan.md) — 8 use case
6. [Notification dan Administration](06-notifications-admin.md) — 4 use case
7. [Validasi dan rencana Fase 4](validation-and-next-step.md)

## Konvensi

- Scope adalah aplikasi HEALO standalone.
- Level seluruh use case adalah **user goal**, kecuali subflow teknis yang tetap dijelaskan di dalam use case induk.
- Aktor use case hanya **User** dan **Admin**.
- AI Provider, mail transport, scheduler, queue, Reverb, storage, dan database adalah komponen/sistem pendukung, bukan aktor use case.
- Untuk area privat web, prasyarat umum adalah session terautentikasi dan email terverifikasi. API privat menggunakan Sanctum; route API tidak menambahkan middleware `verified`.
- Data privat selalu dibatasi ke owner melalui query dan/atau policy. Role Admin tidak memberi bypass implisit.
- `Related Requirements` menunjuk aturan implementasi yang dapat dibuktikan. Repository tidak memuat requirement ID formal terpisah.
- Alur web mengembalikan view/redirect dan validation errors; alur API mengembalikan JSON Resource/status code. Goal bisnisnya tetap sama.

## Batas medis

Fitur AI memberikan informasi dan estimasi wellness. Implementasi menolak diagnosis pasti, resep, dosis obat, prompt injection, serta output tidak aman. Red flag emergency/self-harm diprioritaskan melalui rule lokal dan kegagalan provider menggunakan fallback aman.

