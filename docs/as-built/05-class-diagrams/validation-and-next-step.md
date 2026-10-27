# Validasi Fase 5 dan Rencana Fase 6

## Hasil validasi

| Pemeriksaan | Hasil |
|---|---|
| Model pada `app/Models` | 20 |
| Model unik pada domain overview | 20 |
| Model yang tidak tercakup | 0 |
| Service pada `app/Services` | 38 |
| Service unik pada application service map | 38 |
| Service yang tidak tercakup | 0 |
| Provider interface | 2 dari 2 tercakup |
| Job / event / command | 1 / 1 / 2 tercakup |
| Source PlantUML | 7 |
| Hasil render | 7 SVG dan 7 PNG |
| Marker syntax/runtime error pada hasil akhir | 0 |
| Pemeriksaan visual | 7 diagram terbaca dan memiliki boundary package yang jelas |

## Validasi multiplicity dan integritas

| Relasi/aturan | Bukti implementasi | Representasi |
|---|---|---|
| User–HealthProfile | `health_profiles.user_id` unique dan FK cascade | `1 — 0..1` |
| User–NutritionProfile | `nutrition_profiles.user_id` unique dan FK cascade | `1 — 0..1` |
| User–NotificationPreference | `notification_preferences.user_id` unique dan FK cascade | `1 — 0..1` |
| User–record owner-scoped lainnya | FK `user_id` non-null dan cascade | `1 — 0..*` |
| HealthJournal–HealthJournalEntry | FK non-null dan cascade | composition `1 — 0..*` |
| Meal–MealItem | FK non-null dan cascade | composition `1 — 0..*` |
| HealthConversation–HealthMessage | FK non-null dan cascade | composition `1 — 0..*` |
| HealthPlan–HealthPlanTask | FK non-null dan cascade | composition `1 — 0..*` |
| Assessment–Conversation | FK nullable dan `nullOnDelete` | conversation `0..1` per assessment |
| Assessment–Journal | FK nullable dan `nullOnDelete` | journal `0..1` per assessment |
| HealthPlan per hari | unique `user_id + plan_date` | maksimal satu plan/User/tanggal |
| Nutrition summary per hari | unique `user_id + summary_date` | maksimal satu summary/User/tanggal |
| Notification deduplication | unique `user_id + deduplication_key` | reminder idempoten per User/key |

## Validasi dependency

- `FoodAnalysisProviderInterface` direalisasikan oleh `GroqFoodAnalysisProvider` dan `LocalDemoFoodAnalysisProvider`.
- `HealthAIProviderInterface` direalisasikan oleh `GroqHealthAIProvider` dan `LocalDemoHealthAIProvider`.
- `AppServiceProvider` memilih Groq hanya ketika konfigurasi provider dan API key mendukungnya; kondisi lain menggunakan LocalDemo.
- `FoodAnalysisService` bergantung pada food provider dan `MealService`; tidak ada relasi database langsung antara draft analysis dan meal.
- `MedicalSafetyService` bergantung pada `EmergencyDetectionService` dan dipakai pada Healbot, symptom checker, insight AI, serta personalisasi plan.
- `NotificationService` menyimpan notification durable sebelum event realtime dibroadcast.
- `AssessmentJournalService` memakai `HealthJournalService`, sehingga penyimpanan assessment ke jurnal tetap mengikuti aturan jurnal yang sama.

## Batas dan gap

- Tidak ditemukan repository abstraction; ini dicatat sebagai kondisi as-built, bukan kekurangan yang otomatis harus diperbaiki.
- Target polymorphic `AuditLog::subject()` bergantung pada model yang diberikan saat runtime, sehingga tidak diberi multiplicity ke seluruh kemungkinan model.
- Diagram tidak menyalin secret, API key, raw prompt sensitif, password, token, atau data kesehatan pribadi.
- Controller dan policy telah diperiksa tetapi tidak digambar satu per satu karena tidak menambah relasi domain baru. Inventaris lengkapnya tetap tersedia pada Fase 0.

Tidak ada gap yang menghalangi Fase 6.

## Rencana Fase 6 — Activity Diagrams with Swimlanes

Setelah Fase 5 disetujui, workflow penting akan dibuat sebagai satu file PlantUML per workflow. Swimlane hanya memakai pihak/komponen yang benar-benar menjalankan aktivitas, seperti **User**, **Admin**, **HEALO Application**, dan **AI Service** bila relevan.

Prioritas workflow:

1. registrasi, login, verifikasi, reset password, dan pengelolaan akun;
2. health profile, BMI, jurnal, serta dashboard/tren;
3. profil nutrisi, meal/item, ringkasan nutrisi, dan rekomendasi menu;
4. scan makanan → review label nutrisi → koreksi → konfirmasi;
5. Healbot dan symptom checker, termasuk red flag, failure, dan fallback;
6. health insight, goal progress, plan generation, dan task completion;
7. reminder, quiet hours, durable notification, dan realtime broadcast;
8. dashboard Admin.

Setiap diagram akan memiliki initial node, decision dengan guard, alternative/error path, serta final node.

**Gate:** jangan mulai Fase 6 sebelum Fase 5 disetujui.

