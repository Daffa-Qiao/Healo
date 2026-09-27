# HEALO Implementation Roadmap

## Routes and API surface

### Web routes

- Public: `/`, `/login`, `/register`, password reset and email verification.
- User: `/dashboard`, `/profile`, `/health-profile`, `/bmi`, `/journals`, `/food-scanner`, `/food-journal`, `/nutrition`, `/healthy-menu`, `/symptom-checker`, `/notifications`, `/settings`.
- Admin: `/admin`, `/admin/users`, `/admin/foods`, `/admin/menus`, `/admin/ai-usage`, `/admin/audit-logs`, `/admin/settings`.

### API v1

- `/api/v1/auth/*`, `/profile`, `/health-profile`.
- `/bmi-records`, `/journals`.
- `/foods`, `/food-scans`, `/food-logs`, `/nutrition`.
- `/menus`, `/menu-recommendations`.
- `/symptom-checks`, `/notifications`.

All private API endpoints use Sanctum. Mutating routes use Form Requests, policies, API Resources, throttling, and consistent JSON errors.

## Page and component inventory

Pages: welcome/auth, onboarding, user dashboard, health profile, BMI calculator/history, journal list/calendar/editor/detail, scanner/correction/confirmation, food journal, nutrition dashboard, healthy menu, symptom checker/history/result, notifications, profile/settings, PDF report selection, and separate admin pages.

Shared Blade components: app shell, desktop sidebar, mobile bottom navigation, top bar, cards, metric card, progress bar, chart panel, timeline, empty state, alert/disclaimer, form controls, modal/confirmation, loading/skeleton, toast, pagination, camera/upload panel, food-result editor, and accessible status badge.

## Delivery phases

### Phase 1 — Foundation

- Laravel 12 application, PostgreSQL-ready configuration, Blade/Tailwind/Alpine/Vite.
- Session authentication, Sanctum-ready API authentication, user/admin roles.
- Responsive app shell, navigation, HEALO brand tokens, Indonesian and English localization.
- Secure account/profile foundation, audit-event foundation, factories/seeders, and tests.

Exit: a user can register, sign in, see a role-appropriate dashboard shell, change locale/profile/password, and sign out; authorization tests pass.

### Phase 2 — Health

- Health profile with consent, BMI service/history/chart, personal journal CRUD/search/filter/timeline.
- Policies, encrypted sensitive fields, API resources, PDF-ready queries, and tests.

Exit: all health records are owner-scoped and calculations are reproducible server-side.

### Phase 3 — Nutrition

- Food catalogue, import/export, calorie target service, food journal, daily macro totals and charts.
- Internal food data is the source of truth for confirmed nutrition values.

### Phase 4 — AI

- Provider contracts/adapters, queued symptom analysis and food recognition, structured result DTOs, consent and correction flows, safe failure handling.

### Phase 5 — Healthy Menu

- Menu catalogue, ingredients and constraint-aware recommendation engine using calories, allergies, preferences, activity and goals.

### Phase 6 — Realtime

- Durable database notifications plus Reverb updates for queued AI work and user notifications.

### Phase 7 — Administration

- Aggregate dashboard, user lifecycle management, food/menu management, AI operational telemetry, error monitoring, and redacted audit review.

### Phase 8 — Quality and release

- Complete PHPUnit suites, accessibility audit, bilingual copy audit, rate-limit/security review, query profiling, queues/cache, image retention, PDF exports and deployment runbook.

## Model inventory

`User`, `HealthProfile`, `HealthJournal`, `HealthJournalEntry`, `BmiRecord`, `Food`, `FoodScan`, `FoodScanItem`, `FoodLog`, `NutritionRecord`, `Menu`, `MenuItem`, `MenuRecommendation`, `SymptomCheck`, `SymptomCheckResult`, `AiUsageLog`, and `AdminAuditLog`.

Framework notification, token, session, queue, and cache records remain framework-managed.

## Testing strategy

Each phase adds feature tests for happy paths, validation, unauthenticated access, cross-user ownership, empty state, localization, and API shape. Domain calculations receive unit tests. AI adapters are replaced with deterministic fakes in tests, including timeout and malformed-response cases. Upload tests cover MIME, extension, size, private storage, deletion and retention.

