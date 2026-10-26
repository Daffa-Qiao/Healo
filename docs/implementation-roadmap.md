# HEALO Implementation Roadmap

## Delivery status

- Phase 1 — Core Platform: **DONE**
- Phase 2 — Health Foundation: **DONE**
- Phase 3 — Nutrition: **DONE**
- Phase 4 — AI Assistant: **DONE**
- Phase 5 — Health Insights: **DONE**
- Phase 6 — Health Plan & Wellness: **DONE**
- Phase 7 — Notification & Wellness Reminder: **DONE**

## Routes and API surface

### Web routes

- Public: `/`, `/login`, `/register`, password reset and email verification.
- User (implemented): `/dashboard`, `/profile`, `/health-profile`, `/bmi`, `/health-journals`, `/nutrition`, `/nutrition/profile`, `/meals`, `/food-analysis`, `/healthy-menus`, `/health-assistant`, `/symptom-checker`, `/health-insights`, `/health-goals`, `/health-plan`.
- User (implemented): `/notifications`, `/settings/notifications`.
- Admin: `/admin`, `/admin/users`, `/admin/foods`, `/admin/menus`, `/admin/ai-usage`, `/admin/audit-logs`, `/admin/settings`.

### API v1

- Implemented: `/api/v1/auth/*`, `/profile`, `/health-profile`, `/bmi-records`, `/health-journals`, `/health-journal-entries`.
- Implemented: `/nutrition/profile`, `/nutrition/today`, `/nutrition/summary`, `/meals`, `/meal-items`, `/nutrition/food-analysis`, `/nutrition/menus`.
- Implemented: `/health-assistant/conversations`, `/health-assistant/conversations/{conversation}/messages`, `/symptom-checker/assess`, `/symptom-checker/history`.
- Implemented: `/health-insights`, `/health-insights/trends`, `/health-insights/weekly`, `/health-goals`.
- Implemented: `/health-plan`, `/health-plan/today`, `/health-plan/generate`, and manual task completion/reopening endpoints.
- Implemented: `/notifications`, `/notifications/unread-count`, notification read/read-all, and `/notification-preferences`.

All private API endpoints use Sanctum. Mutating routes use Form Requests, policies, API Resources, throttling, and consistent JSON errors.

## Page and component inventory

Implemented pages include welcome/auth, user dashboard, health profile, BMI calculator/history, health journal, nutrition profile/dashboard, meal journal, food-photo review/confirmation, healthy-menu catalogue, health assistant, symptom checker/history/result, health insights/goals, daily health plan, notification center/preferences, profile/settings, and separate admin foundations. PDF reporting remains planned.

Shared Blade components: app shell, desktop sidebar, mobile bottom navigation, top bar, cards, metric card, progress bar, chart panel, timeline, empty state, alert/disclaimer, form controls, modal/confirmation, loading/skeleton, toast, pagination, camera/upload panel, food-result editor, and accessible status badge.

## Delivery phases

### Phase 1 — Foundation

- Laravel 12 application, PostgreSQL-ready configuration, Blade/Tailwind/Alpine/Vite.
- Session authentication, Sanctum-ready API authentication, user/admin roles.
- Responsive app shell, navigation, HEALO brand tokens, Indonesian and English localization.
- Secure account/profile foundation, audit-event foundation, factories/seeders, and tests.

Exit: a user can register, sign in, see a role-appropriate dashboard shell, change locale/profile/password, and sign out; authorization tests pass.

### Phase 2 — Health (implemented)

- Health profile with consent, BMI service/history/chart, personal journal CRUD/search/filter/timeline.
- Policies, encrypted sensitive fields, API resources, PDF-ready queries, and tests.

Exit: all health records are owner-scoped and calculations are reproducible server-side.

### Phase 3 — Nutrition (implemented)

- Nutrition profile, bounded calorie target service, Meal/MealItem CRUD, daily and weekly summaries, and charts.
- Private food-photo draft analysis with provider abstraction, strict throttling, user correction, and explicit confirmation.
- Internal healthy-menu samples with source attribution, search, filters, and preference-aware suggestions.
- Internal user-confirmed values are the source of truth; provider output is never persisted as a Meal automatically.

### Phase 4 — AI (implemented)

- Owner-scoped encrypted conversations/messages, optional saved symptom assessments, history deletion, and explicit save-to-journal confirmation.
- Provider abstraction with deterministic `local_demo` and configurable Groq implementations, strict schema parsing, safe fallback, and minimized context.
- Rule-based emergency detection runs before provider output; diagnosis, prescription, dosage, prompt-injection, and unsafe provider responses are blocked or replaced.
- Synchronous processing is intentional while the provider remains local; Reverb/queue complexity is deferred until a production adapter needs it.
- Phase 4 verification covers provider success/failure, invalid schema, emergency and self-harm detection, diagnosis/dosage/prompt-injection boundaries, encryption, rate limiting, Sanctum API shape, and owner/admin isolation.

### Phase 5 — Health Insights & Personalization (implemented)

- Actual-data overview plus 7/30/90-day BMI, weight, calorie, macro, journal-frequency, and mood trends.
- Rule-based non-diagnostic insights, locally calculated weekly summaries, and Chart.js visualizations.
- Owner-scoped health goals for weight, BMI, daily calories, journal frequency, and nutrition logging; progress is derived from source records rather than persisted as stale state.
- Groq `openai/gpt-oss-20b` support with server-side credentials, strict structured output, timeout/retry, context minimization, per-user cache keys, rate limiting, safe logs, and local fallback.
- Phase 5 Groq context contains aggregates only and is never used for weekly-summary calculation.
- Phase 5 verification brings the complete suite to 120 passing tests and 596 assertions.

### Phase 6 — Health Plan & Daily Wellness (implemented)

- One owner-scoped plan per user/date with controlled task types and indexed plan/task state.
- Deterministic task generation from nutrition profile/goals, meal state, journal state, BMI history, and active health goals.
- Data-derived meal and journal tasks cannot be faked complete; manual review/wellness tasks support complete and reopen actions.
- Progress, completion timestamps, and plan status are recalculated by the backend after every synchronization or valid action.
- Responsive health-plan page, dashboard preview, health snapshot, pending/completed groups, quick actions, localized empty states, and idempotent demo seed.
- Sanctum API, Form Requests that prohibit client progress/ownership values, API Resources, owner-only policies with no admin bypass, and per-user querying.
- Optional cached Groq personalization reuses the Phase 4–5 provider abstraction, structured output, aggregate-only context, medical safety checks, and local fallback. AI never controls core tasks.
- The main dashboard now provides an owner-scoped daily snapshot, goal progress, 7/30/90-day BMI chart, health plan preview, weekly comparison, safe insight, and responsive quick actions.
- Phase 6 verification brings the complete suite to 140 passing tests and 682 assertions.

### Phase 7 — Notification & Wellness Reminder (implemented)

- Durable owner-scoped notification center with unread filtering, read/read-all actions, pagination, category filters, action links, and localized empty states.
- Structured types, one preference row per user, master/category switches, reminder time, cross-midnight quiet hours, and delayed broadcast delivery.
- Actual-data reminders for missing journal/meal records, pending daily health-plan tasks, significant goal progress, weekly insights, and general wellness.
- Database-enforced per-user deduplication, retry-safe queue jobs, chunked scheduler dispatch, atomic deferred delivery, and safe local wording without AI dependency.
- Private Reverb channels with minimized payloads, conditional Laravel Echo startup, a real-time badge/dropdown, and non-blocking SweetAlert2 toasts. Durable notifications continue to work when Reverb is offline.
- Sanctum API Resources, Form Request validation, policies with no admin bypass, idempotent demo seed, and service/web/API/queue/command/broadcast/ownership coverage.

### Phase 8 — Administration

- Aggregate dashboard, user lifecycle management, food/menu management, AI operational telemetry, error monitoring, and redacted audit review.

### Phase 9 — Quality and release

- Complete PHPUnit suites, accessibility audit, bilingual copy audit, rate-limit/security review, query profiling, queues/cache, image retention, PDF exports and deployment runbook.

## Model inventory

Implemented domain models: `User`, `HealthProfile`, `HealthJournal`, `HealthJournalEntry`, `BmiRecord`, `NutritionProfile`, `Meal`, `MealItem`, `NutritionDailySummary`, `FoodAnalysis`, `HealthyMenu`, `HealthConversation`, `HealthMessage`, `SymptomAssessment`, `HealthGoal`, `HealthPlan`, `HealthPlanTask`, `UserNotification`, `NotificationPreference`, and `AuditLog`.

Framework notification, token, session, queue, and cache records remain framework-managed.

## Testing strategy

Each phase adds feature tests for happy paths, validation, unauthenticated access, cross-user ownership, empty state, localization, and API shape. Domain calculations receive unit tests. AI adapters are replaced with deterministic fakes in tests, including timeout and malformed-response cases. Upload tests cover MIME, extension, size, private storage, deletion and retention.

