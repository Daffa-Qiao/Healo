# Nutrition, Meal, Food Analysis, dan Healthy Menu

## UC-NUT-01 — Mengelola Profil dan Target Nutrisi

| Field | Specification |
|---|---|
| Use Case ID | UC-NUT-01 |
| Use Case Name | Mengelola Profil dan Target Nutrisi |
| Goal | Menyimpan preferensi nutrisi dan menetapkan target kalori aman secara otomatis atau manual. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin target relevan; safety membutuhkan floor/ceiling dan data kesehatan cukup. |
| Trigger | User membuka profil nutrisi atau mengirim perubahan/recalculate. |
| Preconditions | User terautentikasi; kalkulasi otomatis membutuhkan HealthProfile yang cukup. |
| Postconditions | Satu NutritionProfile milik user dibuat/diperbarui dengan target dan sumbernya. |
| Business Rules | Target dibatasi 1.200–4.500 kkal; kalkulasi memakai Mifflin-St Jeor, activity, dan goal adjustment; target manual tidak memerlukan input ulang profil kesehatan. |
| Data Requirements | Activity level, goal, diet preference, food preferences, daily calorie target, target source. |
| Authorization and Privacy Considerations | Owner dari auth context; food preferences terenkripsi; admin tidak bypass. |
| Related Backlog Items | RB-NUT-001 |
| Related Requirements | `NutritionProfileRequest`, `CalorieTargetService`, profile service/policy. |
| Related Test Cases | `NutritionProfileTest.php`; `CalorieTargetServiceTest.php`; `PhaseThreeApiTest.php`; `PhaseThreeAuthorizationTest.php` |
| Implementation Evidence | nutrition profile web/API controllers; services/model/migration/resource |
| Assumptions and Open Questions | Formula target adalah estimasi wellness dan bukan prescription diet klinis. |

### Main Success Scenario

1. User membuka profil nutrisi.
2. HEALO memuat profil nutrisi dan data kesehatan milik user.
3. User memilih kalkulasi otomatis atau memasukkan target manual serta preferensi.
4. HEALO menormalisasi dan memvalidasi input.
5. Untuk otomatis, CalorieTargetService menghitung target dalam batas aman.
6. HEALO meng-upsert NutritionProfile dan mengembalikan hasil.

### Alternative Flows

- A1 — User memilih target manual yang valid; kalkulasi kesehatan tidak dijalankan.
- A2 — Profil nutrisi belum ada; HEALO membuat satu record.

### Exception Flows

- E1 — HealthProfile tidak cukup untuk recalculate; HEALO menolak dengan pesan validasi.
- E2 — Target/preferensi di luar aturan; tidak disimpan.

## UC-NUT-02 — Melihat Ringkasan Nutrisi

| Field | Specification |
|---|---|
| Use Case ID | UC-NUT-02 |
| Use Case Name | Melihat Ringkasan Nutrisi |
| Goal | Membandingkan asupan harian/mingguan dengan target kalori dan makro. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin angka berlabel dan berdasarkan meal aktual; sistem harus menghindari total dari client. |
| Trigger | User membuka dashboard nutrisi atau endpoint today/summary. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | NutritionDailySummary tersinkron dan ringkasan ditampilkan. |
| Business Rules | Total berasal dari MealItem owner-scoped; hari tanpa data tetap dinyatakan kosong/zero sesuai service; periode weekly ditentukan server. |
| Data Requirements | Meal/MealItem, calories, protein_g, carbohydrates_g, fat_g, daily target, meal type/date. |
| Authorization and Privacy Considerations | Query dan summary hanya milik user; resource asing tidak diterima. |
| Related Backlog Items | RB-NUT-002 |
| Related Requirements | `NutritionSummaryService`, dashboard/API summary controllers. |
| Related Test Cases | `NutritionDashboardAndMenuTest.php`; `PhaseThreeApiTest.php` |
| Implementation Evidence | `NutritionDashboardController`; `NutritionSummaryController`; summary service/model/resource |
| Assumptions and Open Questions | Ringkasan adalah estimasi berdasarkan input/AI estimate user, bukan hasil laboratorium. |

### Main Success Scenario

1. User membuka ringkasan.
2. HEALO mengambil NutritionProfile, meal, dan item milik user.
3. Service menghitung/menyinkronkan total kalori dan makro harian.
4. Service membentuk tren dan statistik mingguan.
5. HEALO menampilkan target, consumed/remaining, distribusi meal type, dan tren dengan label satuan.

### Alternative Flows

- A1 — Profil belum ada; HEALO menampilkan ajakan mengatur target.
- A2 — Meal belum ada; HEALO menampilkan empty state tanpa angka fiktif.

### Exception Flows

- E1 — Token/session invalid; middleware menolak.

## UC-MEAL-01 — Mengelola Catatan Makan

| Field | Specification |
|---|---|
| Use Case ID | UC-MEAL-01 |
| Use Case Name | Mengelola Catatan Makan |
| Goal | Membuat, melihat, memperbarui, dan menghapus meal pada tanggal/jenis makan tertentu. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin pencatatan fleksibel; summary membutuhkan meal konsisten. |
| Trigger | User membuka daftar/form/detail atau mengirim create/update/delete. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | Meal owner-scoped berubah dan daily summary dihitung ulang. |
| Business Rules | Meal date tidak boleh masa depan; meal type harus enum; notes opsional dan terenkripsi. |
| Data Requirements | Meal date, meal type, notes, relasi items, total calories/macro. |
| Authorization and Privacy Considerations | Owner query/policy; admin tanpa bypass; client user ID diabaikan/tidak diterima. |
| Related Backlog Items | RB-MEAL-001 |
| Related Requirements | `MealRequest`, `MealService`, meal policy/resource. |
| Related Test Cases | `MealFeatureTest.php`; `PhaseThreeApiTest.php`; `PhaseThreeAuthorizationTest.php` |
| Implementation Evidence | meal web/API controllers; service/model/migration/policy/resource |
| Assumptions and Open Questions | Delete behavior mengikuti implementasi model/service; tidak ada sharing meal. |

### Main Success Scenario

1. User memilih create/update meal.
2. User memasukkan tanggal, meal type, dan notes.
3. HEALO memvalidasi dan menyimpan meal pada user.
4. HEALO menyinkronkan total serta daily summary.
5. HEALO menampilkan detail/resource meal.

### Alternative Flows

- A1 — User melihat index/detail tanpa mutasi.
- A2 — User menghapus meal; policy diperiksa dan summary hari terkait disinkronkan.

### Exception Flows

- E1 — Tanggal/type invalid; validation error.
- E2 — Meal bukan milik user; not found/forbidden.

## UC-MEAL-02 — Mengelola Item Makanan

| Field | Specification |
|---|---|
| Use Case ID | UC-MEAL-02 |
| Use Case Name | Mengelola Item Makanan |
| Goal | Menambah, mengoreksi, atau menghapus makanan beserta nilai nutrisinya pada meal. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User membutuhkan arti setiap angka; kalkulasi membutuhkan satuan dan range valid. |
| Trigger | User mengirim create/update/delete MealItem. |
| Preconditions | Meal/MealItem induk dimiliki user. |
| Postconditions | Item dan agregat Meal/NutritionDailySummary konsisten. |
| Business Rules | Nilai negatif/absurd ditolak; calories/protein/carbohydrates/fat tidak boleh menjadi angka tanpa label; source mencatat manual/AI estimate. |
| Data Requirements | Food name, serving size, serving unit, calories, protein_g, carbohydrates_g, fat_g, source, confidence opsional. |
| Authorization and Privacy Considerations | Kepemilikan diturunkan melalui Meal; policy melarang akses silang user. |
| Related Backlog Items | RB-MEAL-002 |
| Related Requirements | `MealItemRequest`, item service, totals/summary synchronization. |
| Related Test Cases | `MealFeatureTest.php`; `PhaseThreeApiTest.php`; `PhaseThreeAuthorizationTest.php` |
| Implementation Evidence | item web/API controllers; service/model/migration/policy/resource |
| Assumptions and Open Questions | Nilai manual/AI adalah input/estimasi user dan tidak diverifikasi terhadap database nutrisi eksternal. |

### Main Success Scenario

1. User membuka meal miliknya.
2. User mengisi nama makanan, porsi/satuan, kalori, protein, karbohidrat, dan lemak.
3. HEALO memvalidasi tiap field dan range.
4. Service menyimpan item.
5. Service menghitung ulang total meal dan summary harian.
6. HEALO menampilkan nilai dengan label dan satuan.

### Alternative Flows

- A1 — Nilai makro opsional tidak diisi; service menggunakan nilai default sesuai request/service.
- A2 — User mengubah atau menghapus item; agregat dihitung ulang.

### Exception Flows

- E1 — Nilai negatif, serving nol, atau nilai melebihi batas; request ditolak.
- E2 — Meal/item bukan milik user; akses ditolak.

## UC-FOOD-01 — Memindai Foto Makanan

| Field | Specification |
|---|---|
| Use Case ID | UC-FOOD-01 |
| Use Case Name | Memindai Foto Makanan |
| Goal | Menghasilkan draft estimasi makanan/nutrisi dari foto tanpa langsung membuat meal. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | FoodAnalysisProvider aktif: Groq bila terkonfigurasi atau LocalDemo fallback. |
| Stakeholders and Interests | User ingin pencatatan cepat; privacy ingin storage privat; safety ingin hasil terstruktur dan review wajib. |
| Trigger | User mengunggah foto. |
| Preconditions | User terautentikasi; file memenuhi MIME/size; belum melewati limit 5 request/menit. |
| Postconditions | FoodAnalysis berstatus review dengan result/confidence, atau failed tanpa Meal; foto disimpan privat sementara hanya pada jalur berhasil. |
| Business Rules | Max upload mengikuti config (default 5 MB); hasil selalu draft; Meal tidak dibuat saat analyze; retention default 30 hari. |
| Data Requirements | File image, private path, status, result items, confidence, provider/model, timestamps/expiry. |
| Authorization and Privacy Considerations | Direktori per user, disk local/private, path/result encrypted, error tidak membuka respons provider. |
| Related Backlog Items | RB-FOOD-001 |
| Related Requirements | `FoodAnalysisRequest`, provider contract, strict schema, throttle, private storage. |
| Related Test Cases | `FoodAnalysisFeatureTest.php`; `GroqFoodAnalysisProviderTest.php`; `PhaseThreeApiTest.php` |
| Implementation Evidence | food analysis controllers/service; provider interface/adapters; config nutrition/services |
| Assumptions and Open Questions | Akurasi live provider tidak dijamin dan belum diuji dengan credential produksi. |

### Main Success Scenario

1. User memilih dan mengunggah foto.
2. HEALO memvalidasi file dan rate limit.
3. HEALO menyimpan file privat dan membuat FoodAnalysis status processing.
4. HEALO mengirim gambar ke provider aktif melalui contract.
5. Provider mengembalikan payload terstruktur.
6. HEALO menyimpan result, confidence, provider, analyzed_at, dan status review.
7. HEALO membawa User ke tahap review; belum ada Meal dibuat.

### Alternative Flows

- A1 — Groq tidak dikonfigurasi/API key kosong; binding memakai LocalDemo provider.
- A2 — Confidence di bawah threshold; HEALO menandai low-confidence agar review lebih jelas.

### Exception Flows

- E1 — File invalid/terlalu besar atau throttle terlampaui; file tidak diproses.
- E2 — Provider/schema gagal; HEALO menghapus file, menandai failed, mencatat warning aman, dan tidak membuat Meal.

## UC-FOOD-02 — Meninjau dan Mengonfirmasi Hasil Analisis Makanan

| Field | Specification |
|---|---|
| Use Case ID | UC-FOOD-02 |
| Use Case Name | Meninjau dan Mengonfirmasi Hasil Analisis Makanan |
| Goal | Memastikan User memahami dan mengoreksi hasil AI sebelum menyimpannya sebagai meal. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada pemanggilan AI baru pada konfirmasi. |
| Stakeholders and Interests | User membutuhkan kontrol; data nutrition membutuhkan label/range yang eksplisit. |
| Trigger | User membuka draft review lalu mengirim nilai final. |
| Preconditions | FoodAnalysis milik user, status review, belum confirmed. |
| Postconditions | Satu Meal beserta items dibuat secara transaction; analysis confirmed; foto privat dihapus. |
| Business Rules | 1–20 foods; tanggal tidak masa depan; setiap item wajib nama, serving size/unit, calories; makro optional namun tervalidasi; konfirmasi hanya sekali. |
| Data Requirements | Meal date/type/notes dan per-item: food name, serving size/unit, calories, protein_g, carbohydrates_g, fat_g. |
| Authorization and Privacy Considerations | Policy/owner query; image response private/no-store; corrected values tetap owner-scoped. |
| Related Backlog Items | RB-FOOD-002 |
| Related Requirements | `FoodAnalysisConfirmationRequest`, DB transaction, MealService createWithItems. |
| Related Test Cases | `FoodAnalysisFeatureTest.php`; `PhaseThreeApiTest.php`; `PhaseThreeAuthorizationTest.php` |
| Implementation Evidence | `FoodAnalysisController::show/confirm`; `FoodAnalysisService::confirm`; result view |
| Assumptions and Open Questions | User bertanggung jawab memeriksa estimasi; aplikasi tidak mempunyai database referensi eksternal untuk validasi kebenaran nutrisi. |

### Main Success Scenario

1. HEALO memverifikasi owner dan menampilkan foto serta confidence.
2. HEALO menampilkan setiap nilai dengan label: nama makanan, porsi, satuan, kalori, protein, karbohidrat, dan lemak.
3. User memeriksa dan mengoreksi nilai.
4. User memilih tanggal dan meal type lalu mengonfirmasi.
5. HEALO membersihkan/menvalidasi seluruh field.
6. Dalam transaction, HEALO membuat Meal/MealItems bersumber AI estimate dan menyinkronkan summary.
7. HEALO menghapus foto, menandai analysis confirmed, mencatat audit, dan membuka Meal.

### Alternative Flows

- A1 — Makro kosong; HEALO menggunakan default nol sesuai service setelah validasi nullable.
- A2 — Confidence rendah; flow tetap dapat dilanjutkan setelah review eksplisit.

### Exception Flows

- E1 — Draft sudah confirmed/bukan status review; HEALO menolak duplikasi.
- E2 — Item/range invalid; HEALO kembali ke review dengan error berlabel.
- E3 — Record user lain; not found/forbidden.

## UC-FOOD-03 — Mengelola Draft Analisis Makanan

| Field | Specification |
|---|---|
| Use Case ID | UC-FOOD-03 |
| Use Case Name | Mengelola Draft Analisis Makanan |
| Goal | Melihat foto/draft privat dan membatalkan draft yang tidak ingin disimpan. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Private filesystem; scheduler untuk retention sebagai komponen internal. |
| Stakeholders and Interests | User ingin kontrol draft; privacy ingin akses privat dan retensi terbatas. |
| Trigger | User membuka draft/image atau memilih cancel/delete. |
| Preconditions | Draft milik user; image tersedia untuk view. |
| Postconditions | Draft tetap tersedia, atau file dan record dibatalkan/dihapus; cleanup dapat menghapus file expired. |
| Business Rules | Recent review drafts dibatasi lima pada create page; private image memakai `Cache-Control: private, no-store`; cleanup berjalan harian 02:30. |
| Data Requirements | Analysis ID/status/path/expiry dan file privat. |
| Authorization and Privacy Considerations | Policy dan owner query sebelum stream/delete; direct public URL tidak digunakan. |
| Related Backlog Items | RB-FOOD-003 |
| Related Requirements | Private image route, cancel service, retention scheduler. |
| Related Test Cases | `FoodAnalysisFeatureTest.php`; `PhaseThreeAuthorizationTest.php` |
| Implementation Evidence | `FoodAnalysisController::create/image/destroy`; service cancel/cleanup; `routes/console.php` |
| Assumptions and Open Questions | Retention production bergantung scheduler aktif. |

### Main Success Scenario

1. User membuka daftar draft atau detail draft.
2. HEALO mengambil hanya analysis milik user.
3. Bila foto diminta, HEALO memeriksa policy lalu men-stream file privat tanpa cache publik.
4. User memilih membatalkan draft.
5. HEALO mencatat audit, menghapus file, dan menghapus record analysis.
6. HEALO kembali ke halaman food analysis.

### Alternative Flows

- A1 — User tidak membatalkan; draft tetap tersedia sampai confirm/cancel/expiry.
- A2 — Scheduler menemukan foto expired; file dihapus dan path/expiry dikosongkan tanpa goal aktor baru.

### Exception Flows

- E1 — Image path kosong/file tidak ada; HEALO mengembalikan 404.
- E2 — Draft bukan milik user; akses ditolak.

## UC-MENU-01 — Mencari dan Memfilter Menu Sehat

| Field | Specification |
|---|---|
| Use Case ID | UC-MENU-01 |
| Use Case Name | Mencari dan Memfilter Menu Sehat |
| Goal | Menemukan menu aktif sesuai kata kunci, meal type, diet, dan rentang kalori. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | User ingin katalog relevan; content owner ingin hanya menu aktif ditampilkan. |
| Trigger | User membuka katalog atau mengirim filter. |
| Preconditions | User terautentikasi; web email verified. |
| Postconditions | Hasil terfilter/terpaginasikan tampil; tidak ada data berubah. |
| Business Rules | Search max 100 karakter; meal/diet enum; kalori 0–5000 dan max ≥ min; vegetarian mencakup vegan menurut query service. |
| Data Requirements | Query, meal type, diet preference, min/max calories; HealthyMenu aktif dan atribut nutrisi/bahan. |
| Authorization and Privacy Considerations | Katalog bukan record kesehatan privat, tetapi endpoint tetap authenticated; filter tidak menerima user ID. |
| Related Backlog Items | RB-MENU-001 |
| Related Requirements | Controller validation dan `HealthyMenuService::search`. |
| Related Test Cases | `NutritionDashboardAndMenuTest.php`; `PhaseThreeApiTest.php` |
| Implementation Evidence | healthy menu web/API controllers; service/model/resource/seeder |
| Assumptions and Open Questions | Proses authoring menu tidak tersedia di UI admin. |

### Main Success Scenario

1. User membuka katalog.
2. User memasukkan search/filter.
3. HEALO memvalidasi filter.
4. Service mengambil hanya menu aktif yang cocok dan mengurutkannya.
5. HEALO menampilkan halaman/resource paginated dengan informasi menu.

### Alternative Flows

- A1 — Tanpa filter; semua menu aktif ditampilkan terurut.
- A2 — Tidak ada hasil; empty state ditampilkan.

### Exception Flows

- E1 — Enum/rentang kalori invalid; validation error.

## UC-MENU-02 — Melihat Rekomendasi Menu Personal

| Field | Specification |
|---|---|
| Use Case ID | UC-MENU-02 |
| Use Case Name | Melihat Rekomendasi Menu Personal |
| Goal | Melihat hingga tiga menu aktif yang mendekati target dan preferensi User. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada AI/external provider; rekomendasi rule-based lokal. |
| Stakeholders and Interests | User ingin relevansi; safety membutuhkan filter alergi dan tidak mengklaim suitability klinis. |
| Trigger | User membuka nutrition dashboard atau healthy menu page. |
| Preconditions | Login dan email verified; profil nutrisi/health profile meningkatkan personalisasi tetapi tidak wajib. |
| Postconditions | Rekomendasi ditampilkan; tidak ada data menu/user berubah. |
| Business Rules | Default target 2.000 bila profil tidak ada; desired share 30% meal atau 12% snack; range ±180 kalori; maksimal tiga; alergi dicocokkan case-insensitive terhadap teks ingredients. |
| Data Requirements | Daily target, diet preference, meal type, allergies, menu calories/ingredients/active. |
| Authorization and Privacy Considerations | Alergi didekripsi hanya untuk perhitungan server-side dan tidak dikirim ke provider eksternal. |
| Related Backlog Items | RB-MENU-002 |
| Related Requirements | `HealthyMenuService::recommendations`. |
| Related Test Cases | Coverage render halaman pada `NutritionDashboardAndMenuTest.php`; assertion personalisasi khusus belum ada. |
| Implementation Evidence | recommendation service; nutrition/menu controllers dan views |
| Assumptions and Open Questions | **NEEDS VALIDATION**: pencocokan alergi berbasis substring bukan evaluasi keamanan makanan klinis; perlu disclaimer/review product. |

### Main Success Scenario

1. User membuka halaman yang memuat rekomendasi.
2. HEALO membaca target kalori/diet preference milik user atau default.
3. HEALO menentukan desired calories berdasarkan meal type.
4. Service memfilter menu aktif menurut diet dan rentang kalori.
5. Service menolak menu yang ingredients-nya mengandung teks alergi.
6. HEALO mengurutkan berdasarkan kedekatan kalori dan menampilkan hingga tiga menu.

### Alternative Flows

- A1 — Profil tidak tersedia; HEALO memakai target default dan tanpa filter preferensi/allergies yang tidak ada.
- A2 — Tidak ada rekomendasi aman/cocok; list rekomendasi kosong.

### Exception Flows

- E1 — Data ingredients tidak lengkap; sistem hanya dapat menerapkan pencocokan yang tersedia dan tidak boleh menjamin bebas alergen.

