# Public, Authentication, dan Account

## UC-PUB-01 — Melihat Landing Page

| Field | Specification |
|---|---|
| Use Case ID | UC-PUB-01 |
| Use Case Name | Melihat Landing Page |
| Goal | Memahami fungsi utama HEALO dan menemukan jalur registrasi/login. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | Pengunjung membutuhkan informasi ringkas; pemilik produk membutuhkan entry point yang tidak membuka data privat. |
| Trigger | User membuka `/`. |
| Preconditions | Tidak ada. |
| Postconditions | Halaman publik tampil; tidak ada data domain berubah. |
| Business Rules | Konten publik tidak boleh menampilkan record kesehatan atau secret. |
| Data Requirements | Copy landing page, locale aktif, dan status autentikasi untuk navigasi. |
| Authorization and Privacy Considerations | Route publik; komponen navigasi hanya menampilkan tindakan sesuai status login. |
| Related Backlog Items | RB-PUB-001 |
| Related Requirements | Public route dan Blade view harus tersedia; tidak ada requirement ID formal. |
| Related Test Cases | `HomepageTest.php` |
| Implementation Evidence | `routes/web.php`; `resources/views/welcome.blade.php` |
| Assumptions and Open Questions | Tidak ada gap implementasi; copy marketing tetap dapat berubah tanpa mengubah goal. |

### Main Success Scenario

1. User meminta halaman root.
2. HEALO menentukan locale dan status autentikasi.
3. HEALO merender informasi produk serta tautan tindakan yang sesuai.
4. User memilih registrasi, login, atau dashboard bila sudah terautentikasi.

### Alternative Flows

- A1 — User yang sudah login menggunakan tautan dashboard; HEALO meneruskan ke dashboard yang sesuai role.

### Exception Flows

- E1 — View gagal dirender; framework mengembalikan error server tanpa membuka konfigurasi sensitif pada production.

## UC-AUTH-01 — Registrasi Akun

| Field | Specification |
|---|---|
| Use Case ID | UC-AUTH-01 |
| Use Case Name | Registrasi Akun |
| Goal | Membuat akun User baru untuk mengakses HEALO. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User |
| Supporting Actors / External Systems | Mail/notification transport untuk verifikasi email, bila dikonfigurasi. |
| Stakeholders and Interests | User ingin akun aman; security menginginkan email unik dan password kuat. |
| Trigger | User mengirim form atau request registrasi API. |
| Preconditions | User belum login; email belum digunakan oleh record aktif. |
| Postconditions | User ber-role `user` dibuat; event Registered dipicu; session web atau token API tersedia. |
| Business Rules | Role default adalah user; password harus dikonfirmasi dan memenuhi aturan Laravel; API membutuhkan `device_name`; endpoint auth dibatasi 10 request/menit. |
| Data Requirements | Nama, email lowercase/valid/unik, password, konfirmasi password; locale/device name pada API. |
| Authorization and Privacy Considerations | Password di-hash dan tidak diserialisasi; token API hanya dikembalikan saat dibuat. |
| Related Backlog Items | RB-AUTH-001 |
| Related Requirements | Validation rules pada controller/request dan default role pada `User`. |
| Related Test Cases | `Auth/RegistrationTest.php`; `ApiAuthenticationTest.php` |
| Implementation Evidence | `RegisteredUserController`; `Api/V1/AuthController`; `RegisterRequest`; `User` |
| Assumptions and Open Questions | Keberhasilan pengiriman email tergantung mail transport runtime. |

### Main Success Scenario

1. User mengisi data registrasi.
2. HEALO memvalidasi nama, email, password, dan data channel.
3. HEALO membuat User dengan password ter-hash dan role user.
4. HEALO memicu event Registered.
5. Web membuat session dan mengarah ke dashboard; API membuat Sanctum token dan mengembalikan UserResource.

### Alternative Flows

- A1 — Locale API tidak diberikan; HEALO memakai `id`.
- A2 — User mendaftar melalui web; `device_name` tidak diperlukan.

### Exception Flows

- E1 — Email duplikat, format invalid, atau password tidak memenuhi aturan; HEALO menolak tanpa membuat akun.
- E2 — Rate limit API terlampaui; request ditolak sementara.

## UC-AUTH-02 — Login

| Field | Specification |
|---|---|
| Use Case ID | UC-AUTH-02 |
| Use Case Name | Login |
| Goal | Membentuk konteks autentikasi sesuai akun dan channel. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Sanctum untuk token API. |
| Stakeholders and Interests | Aktor ingin akses; security ingin kredensial dan rate limit diverifikasi. |
| Trigger | Aktor mengirim email/password. |
| Preconditions | Akun ada dan tidak terhapus; aktor belum memiliki session yang digunakan untuk alur login web. |
| Postconditions | Web memiliki session baru atau API memiliki token baru; login dicatat pada audit. |
| Business Rules | Email dinormalisasi lowercase; kredensial harus cocok; API auth dibatasi 10 request/menit; dashboard ditentukan oleh role. |
| Data Requirements | Email, password; `device_name` untuk API. |
| Authorization and Privacy Considerations | Password tidak pernah dicatat; audit menyimpan IP hash, bukan raw IP. |
| Related Backlog Items | RB-AUTH-002 |
| Related Requirements | Session regeneration, Sanctum abilities berdasarkan role, dan role routing. |
| Related Test Cases | `Auth/AuthenticationTest.php`; `ApiAuthenticationTest.php`; `AuditLogTest.php`; `RoleAuthorizationTest.php` |
| Implementation Evidence | `AuthenticatedSessionController`; `Api/V1/AuthController`; login requests; `AuditLogService` |
| Assumptions and Open Questions | Account lockout permanen tidak ditemukan; throttling menjadi kontrol percobaan berulang. |

### Main Success Scenario

1. Aktor mengirim kredensial.
2. HEALO memvalidasi format dan memeriksa kredensial.
3. HEALO meregenerasi session web atau membuat token API.
4. HEALO mencatat audit login tanpa menyimpan password/raw IP.
5. User diarahkan ke dashboard user; Admin diarahkan ke dashboard admin, atau API menerima token dan profil.

### Alternative Flows

- A1 — Aktor menggunakan API; HEALO tidak membuat session browser.
- A2 — Admin membuka `/dashboard`; HEALO mengalihkan ke `/admin`.

### Exception Flows

- E1 — Kredensial salah; HEALO menolak dan tidak membuat session/token.
- E2 — Rate limit terlampaui; percobaan ditunda.

## UC-AUTH-03 — Logout

| Field | Specification |
|---|---|
| Use Case ID | UC-AUTH-03 |
| Use Case Name | Logout |
| Goal | Mengakhiri session atau token aktif. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Sanctum untuk token API. |
| Stakeholders and Interests | Aktor ingin mencegah penggunaan akses lanjutan; security menginginkan invalidasi yang benar. |
| Trigger | Aktor memilih logout atau memanggil endpoint logout API. |
| Preconditions | Aktor terautentikasi pada channel terkait. |
| Postconditions | Session web invalid atau current access token dihapus; audit logout tercatat. |
| Business Rules | API hanya mencabut token yang sedang digunakan; web meregenerasi CSRF token. |
| Data Requirements | Session atau bearer token aktif. |
| Authorization and Privacy Considerations | Endpoint wajib auth; respons tidak mengembalikan token. |
| Related Backlog Items | RB-AUTH-003 |
| Related Requirements | Auth middleware dan mekanisme logout masing-masing channel. |
| Related Test Cases | `Auth/AuthenticationTest.php`; `ApiAuthenticationTest.php` |
| Implementation Evidence | session/API auth controllers; `routes/auth.php`; `routes/api.php` |
| Assumptions and Open Questions | Logout API tidak mencabut seluruh token perangkat lain. |

### Main Success Scenario

1. Aktor meminta logout.
2. HEALO mencatat audit logout.
3. Web mengeluarkan user, menginvalidasi session, dan meregenerasi token; API menghapus current access token.
4. HEALO mengembalikan redirect atau pesan sukses.

### Alternative Flows

- A1 — Aktor memiliki token API lain; token tersebut tetap berlaku.

### Exception Flows

- E1 — Session/token tidak valid; middleware menolak request sebagai unauthenticated.

## UC-AUTH-04 — Verifikasi Email

| Field | Specification |
|---|---|
| Use Case ID | UC-AUTH-04 |
| Use Case Name | Verifikasi Email |
| Goal | Membuktikan kepemilikan email sebelum memakai area web ber-middleware `verified`. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Mail/notification transport. |
| Stakeholders and Interests | Aktor ingin akses penuh; security ingin signed link terikat ke akun. |
| Trigger | Aktor membuka signed URL atau meminta kirim ulang. |
| Preconditions | Aktor login; URL cocok dengan ID/hash akun untuk proses verifikasi. |
| Postconditions | `email_verified_at` terisi atau notifikasi verifikasi dikirim ulang. |
| Business Rules | URL harus signed; verify/resend dibatasi 6 request/menit. |
| Data Requirements | User ID, hash email, signature, timestamp; alamat email akun. |
| Authorization and Privacy Considerations | Link hanya memverifikasi akun yang sedang login dan tidak membuka email pihak lain. |
| Related Backlog Items | RB-AUTH-004 |
| Related Requirements | Laravel MustVerifyEmail, signed middleware, dan throttle. |
| Related Test Cases | `Auth/EmailVerificationTest.php` |
| Implementation Evidence | `VerifyEmailController`; verification prompt/notification controllers; auth routes |
| Assumptions and Open Questions | Delivery email aktual memerlukan konfigurasi mail environment. |

### Main Success Scenario

1. Aktor menerima dan membuka signed URL.
2. HEALO memvalidasi signature, ID, dan hash email.
3. HEALO menandai email terverifikasi dan memicu event verified.
4. HEALO mengarahkan aktor ke area aplikasi.

### Alternative Flows

- A1 — Aktor belum menerima email; aktor meminta kirim ulang dan HEALO mengirim notification baru.
- A2 — Email sudah terverifikasi; HEALO langsung mengarahkan tanpa mengubah data.

### Exception Flows

- E1 — Signature/hash invalid; HEALO menolak verifikasi.
- E2 — Throttle terlampaui; pengiriman/verifikasi ditunda.

## UC-AUTH-05 — Reset Password

| Field | Specification |
|---|---|
| Use Case ID | UC-AUTH-05 |
| Use Case Name | Reset Password |
| Goal | Memulihkan akses akun dengan token reset. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Password broker dan mail transport. |
| Stakeholders and Interests | Aktor membutuhkan recovery; security ingin respons aman dan token sekali pakai. |
| Trigger | Aktor meminta link reset lalu mengirim password baru. |
| Preconditions | Email berformat valid; token reset valid saat digunakan. |
| Postconditions | Password ter-hash diperbarui dan token reset tidak dapat digunakan ulang. |
| Business Rules | Password baru harus memenuhi defaults dan confirmation; broker mengelola token/expiry. |
| Data Requirements | Email, token reset, password baru, confirmation. |
| Authorization and Privacy Considerations | Respons permintaan tidak boleh membeberkan detail akun; password/token tidak dicatat pada audit. |
| Related Backlog Items | RB-AUTH-005 |
| Related Requirements | Password broker Laravel dan validation rules. |
| Related Test Cases | `Auth/PasswordResetTest.php` |
| Implementation Evidence | `PasswordResetLinkController`; `NewPasswordController`; reset views/routes |
| Assumptions and Open Questions | Keberhasilan email bergantung pada mail transport. |

### Main Success Scenario

1. Aktor mengirim email pada forgot-password.
2. HEALO meminta password broker membuat dan mengirim link.
3. Aktor membuka link dan mengirim token serta password baru.
4. HEALO memvalidasi token dan password.
5. HEALO memperbarui password hash dan menyelesaikan token reset.

### Alternative Flows

- A1 — Aktor meminta ulang link; broker membuat alur reset baru sesuai throttling/expiry bawaan.

### Exception Flows

- E1 — Token invalid/kedaluwarsa atau confirmation tidak cocok; password tidak berubah.
- E2 — Mail transport gagal; request tidak menghasilkan delivery yang dapat dipastikan oleh repository.

## UC-AUTH-06 — Konfirmasi dan Ubah Password

| Field | Specification |
|---|---|
| Use Case ID | UC-AUTH-06 |
| Use Case Name | Konfirmasi dan Ubah Password |
| Goal | Mengonfirmasi identitas untuk tindakan sensitif dan mengganti password. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | Aktor ingin mengubah kredensial; security membutuhkan current password dan session auth. |
| Trigger | Aktor membuka konfirmasi atau mengirim form perubahan password. |
| Preconditions | Aktor login. |
| Postconditions | Status konfirmasi tersimpan pada session atau password baru berlaku. |
| Business Rules | Current password harus benar; password baru memenuhi defaults dan confirmation. |
| Data Requirements | Current password, password baru, confirmation. |
| Authorization and Privacy Considerations | Password tidak ditampilkan/disimpan sebagai plaintext dan tidak masuk log. |
| Related Backlog Items | RB-AUTH-006 |
| Related Requirements | `current_password` validation dan password hashing cast. |
| Related Test Cases | `Auth/PasswordConfirmationTest.php`; `Auth/PasswordUpdateTest.php` |
| Implementation Evidence | `ConfirmablePasswordController`; `PasswordController`; auth routes/views |
| Assumptions and Open Questions | Rotasi/revokasi seluruh token setelah perubahan password tidak ditemukan. |

### Main Success Scenario

1. Aktor mengirim current password untuk konfirmasi/perubahan.
2. HEALO membandingkan dengan password hash akun.
3. Untuk konfirmasi, HEALO menandai session telah dikonfirmasi.
4. Untuk perubahan, HEALO memvalidasi password baru dan menyimpan hash baru.
5. HEALO mengembalikan status berhasil.

### Alternative Flows

- A1 — Aktor hanya mengonfirmasi password tanpa menggantinya.

### Exception Flows

- E1 — Current password salah atau password baru lemah/tidak cocok; tidak ada perubahan.

## UC-PREF-01 — Mengubah Bahasa Aplikasi

| Field | Specification |
|---|---|
| Use Case ID | UC-PREF-01 |
| Use Case Name | Mengubah Bahasa Aplikasi |
| Goal | Menggunakan UI dalam Bahasa Indonesia atau Inggris. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | Aktor membutuhkan bahasa pilihan; produk membutuhkan locale yang konsisten. |
| Trigger | Aktor memilih locale. |
| Preconditions | Session browser tersedia; login tidak wajib. |
| Postconditions | Locale disimpan pada session dan, bila login, pada User. |
| Business Rules | Hanya `id` dan `en` diterima. |
| Data Requirements | Nilai locale. |
| Authorization and Privacy Considerations | Guest hanya mengubah session sendiri; user login hanya memperbarui preferensinya. |
| Related Backlog Items | RB-PREF-001 |
| Related Requirements | Validation `Rule::in` dan middleware `SetLocale`. |
| Related Test Cases | `LocalizationTest.php` |
| Implementation Evidence | `LocaleController`; `SetLocale`; `lang/id`; `lang/en` |
| Assumptions and Open Questions | Kelengkapan seluruh copy bilingual masih memerlukan audit manual. |

### Main Success Scenario

1. Aktor memilih bahasa.
2. HEALO memvalidasi nilai locale.
3. HEALO menyimpan locale pada session.
4. Bila aktor login, HEALO memperbarui locale akun.
5. HEALO kembali ke halaman sebelumnya dengan terjemahan terpilih.

### Alternative Flows

- A1 — Guest memilih bahasa; hanya session yang berubah.

### Exception Flows

- E1 — Locale tidak didukung; validation error dikembalikan.

## UC-ACC-01 — Melihat dan Memperbarui Profil Akun

| Field | Specification |
|---|---|
| Use Case ID | UC-ACC-01 |
| Use Case Name | Melihat dan Memperbarui Profil Akun |
| Goal | Membaca identitas akun dan menjaga nama/email tetap benar. |
| Scope | HEALO Web dan API v1 |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Sanctum untuk identitas API. |
| Stakeholders and Interests | Aktor ingin profil akurat; security ingin hanya akun sendiri yang terlihat/berubah. |
| Trigger | Aktor membuka profil atau mengirim perubahan. |
| Preconditions | Aktor terautentikasi. |
| Postconditions | Profil ditampilkan atau nama/email tersimpan; perubahan email mereset verifikasi. |
| Business Rules | Email harus valid dan unik; API saat ini menyediakan read-only own profile. |
| Data Requirements | User ID dari auth context, nama, email, locale/role untuk resource yang diizinkan. |
| Authorization and Privacy Considerations | Tidak menerima `user_id` dari client; password/remember token tersembunyi dari serialisasi. |
| Related Backlog Items | RB-AUTH-007; RB-ACC-001 |
| Related Requirements | Auth middleware, `ProfileUpdateRequest`, dan `UserResource`. |
| Related Test Cases | `ProfileTest.php`; `ApiAuthenticationTest.php` |
| Implementation Evidence | `ProfileController`; `ProfileUpdateRequest`; `GET api/v1/profile`; `UserResource` |
| Assumptions and Open Questions | Update profil melalui API tidak ditemukan. |

### Main Success Scenario

1. Aktor membuka profil.
2. HEALO mengambil User dari session/token, bukan ID client.
3. HEALO menampilkan/menserialisasi data yang diizinkan.
4. Pada web, aktor mengubah nama/email.
5. HEALO memvalidasi, menyimpan, mereset verifikasi bila email berubah, dan mencatat audit update.

### Alternative Flows

- A1 — API hanya membaca profil sendiri dan mengembalikan UserResource.
- A2 — Email tidak berubah; status verifikasi dipertahankan.

### Exception Flows

- E1 — Email invalid/duplikat; perubahan ditolak.
- E2 — Token/session invalid; request ditolak sebagai unauthenticated.

## UC-ACC-02 — Menghapus Akun Sendiri

| Field | Specification |
|---|---|
| Use Case ID | UC-ACC-02 |
| Use Case Name | Menghapus Akun Sendiri |
| Goal | Menonaktifkan akun melalui soft delete setelah konfirmasi kredensial. |
| Scope | HEALO Web |
| Level | User goal |
| Primary Actor | User, Admin |
| Supporting Actors / External Systems | Tidak ada. |
| Stakeholders and Interests | Aktor membutuhkan kontrol penghapusan; security ingin mencegah penghapusan tidak sah dan mempertahankan audit minimum. |
| Trigger | Aktor mengirim form penghapusan akun. |
| Preconditions | Aktor login dan mengetahui current password. |
| Postconditions | Audit dibuat, User di-soft-delete, session diinvalidasi, dan aktor kembali ke landing page. |
| Business Rules | Current password wajib benar; operasi menggunakan `SoftDeletes`; ini bukan workflow ekspor data. |
| Data Requirements | Current password dan User dari session. |
| Authorization and Privacy Considerations | Hanya akun sendiri; password tidak disimpan; audit dibuat sebelum soft delete. |
| Related Backlog Items | RB-ACC-002 |
| Related Requirements | `current_password` validation, session invalidation, `User::SoftDeletes`. |
| Related Test Cases | `ProfileTest.php` |
| Implementation Evidence | `ProfileController::destroy`; profile delete form; `User` model |
| Assumptions and Open Questions | Kebijakan purge permanen/cascade retention setelah soft delete belum didokumentasikan dan memerlukan keputusan produk. |

### Main Success Scenario

1. Aktor membuka bagian penghapusan akun.
2. Aktor memasukkan current password dan mengonfirmasi.
3. HEALO memvalidasi password.
4. HEALO mencatat audit `account.deleted`.
5. HEALO logout, melakukan soft delete User, menginvalidasi session, dan meregenerasi CSRF token.
6. HEALO mengarahkan ke landing page.

### Alternative Flows

- A1 — Aktor membatalkan sebelum submit; tidak ada data berubah.

### Exception Flows

- E1 — Password salah/kosong; penghapusan ditolak dan akun tetap aktif.

