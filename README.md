# HEALO

**Heal + Hello — Your everyday health companion.**

HEALO is a standalone, privacy-conscious personal health application. It is built as a modular Laravel monolith with a Blade/Alpine interface and a versioned Sanctum API. It is not coupled to Zavora, a hospital, a clinic, or another healthcare platform.

## Current delivery status

Phase 1 foundation through Phase 7 Notification & Wellness Reminder are implemented:

- Laravel 12, Blade, Tailwind CSS, Alpine.js and Vite.
- Registration, login, logout, email verification, password reset and account deletion.
- User/admin roles with a separate, access-controlled admin dashboard.
- Sanctum `/api/v1` registration, login, logout and profile endpoints.
- Indonesian and English localization with per-user preference.
- Responsive HEALO landing page, dashboard shell, desktop sidebar and mobile bottom navigation.
- Minimal audit logging with hashed IP addresses and no health content.
- PostgreSQL-ready environment configuration and SQLite in-memory tests.
- Reverb, Laravel Echo, queued notifications, DomPDF, Chart.js, DataTables, SweetAlert2 and Lucide are integrated.
- Owner-scoped health profiles with consent-aware storage and encrypted conditions/allergies.
- BMI calculation, interpretation, history, profile synchronization, and a Chart.js trend view.
- Health journal CRUD with mood, complaints, symptoms, notes, search/filtering, and encrypted entry content.
- Laravel policies with no implicit admin bypass for private health records.
- Versioned Sanctum REST endpoints for all Phase 2 modules, protected by rate limiting.
- Unit, feature, API, validation, ownership, encryption, and seeder coverage.
- Nutrition profile and bounded calorie-target calculation using existing Health Profile data.
- Meal and food-item tracking with synchronized totals, daily summaries, and 7-day charts.
- Private food-photo analysis drafts with strict rate limiting, low-confidence warnings, mandatory review, and confirmation before a Meal is created.
- Searchable internal healthy-menu samples with source attribution and dietary filters.
- Owner-scoped health conversations with encrypted titles, messages, and AI metadata.
- A structured symptom checker with deterministic emergency detection, urgency levels, red flags, and optional private history.
- A minimized health-context layer that reads only relevant internal HEALO data for the authenticated user.
- Medical safety boundaries for diagnosis, prescriptions, medication dosage, prompt injection, and unsafe provider output.
- Explicit confirmation before a saved assessment can create a health-journal entry.
- Health overview, 7/30/90-day BMI, weight, nutrition, macro, journal, and mood trends using actual user records.
- Rule-based personalized insights, weekly summaries, and live progress for weight, BMI, calorie, journal, and nutrition-logging goals.
- Optional Groq AI insights using `openai/gpt-oss-20b`, strict JSON Schema validation, medical safety checks, per-user caching, and safe fallback.
- Minimal aggregate context for Groq: raw journals, meal descriptions, email, internal IDs, authentication data, and credentials are excluded.
- A deterministic daily health-plan generator with duplicate prevention and controlled wellness task types.
- Nutrition and journal tasks synchronize from actual source records; only legitimate manual wellness/review tasks can be toggled directly.
- Server-calculated daily progress, responsive `/health-plan` experience, dashboard preview, quick actions, empty states, and owner-scoped Sanctum endpoints.
- A responsive personal-health dashboard with live BMI, calories, journal, nutrition, goals, daily plan, weekly comparisons, localized empty states, and 7/30/90-day Chart.js trends.
- Optional Groq wellness wording and motivation through the existing provider/safety pipeline; core planning remains fully functional offline.
- Owner-scoped notification center, real-time badge/dropdown, category filters, read/read-all actions, preferences, reminder time, and cross-midnight quiet hours.
- Actual-data reminders for journals, meals, pending health-plan tasks, health-goal progress, weekly insights, and general wellness with database-enforced deduplication.
- Retry-safe notification queue jobs, private Reverb channels, delayed quiet-hours delivery, and an idempotent demo notification seeder.
- The complete regression suite contains 162 passing tests with 761 assertions.

Reporting screens remain planned. See [implementation roadmap](docs/implementation-roadmap.md).

## Requirements

- PHP 8.2+
- Composer 2
- Node.js 20+
- PostgreSQL 15+

## Local setup

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Create a PostgreSQL database and update `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`, then run:

```bash
php artisan migrate --seed
npm install
npm run build
composer run dev
```

Run the scheduler, notification worker, and Reverb server in production under a process supervisor:

```bash
php artisan schedule:work
php artisan queue:work --queue=notifications,default --tries=3
php artisan reverb:start
```

Configure `BROADCAST_CONNECTION=reverb`, `QUEUE_CONNECTION=database`, the `REVERB_*` server credentials, and matching `VITE_REVERB_*` public connection values. Restrict `REVERB_ALLOWED_ORIGINS` to the deployed HEALO origin in production. Notifications remain stored and usable if Reverb is temporarily unavailable.

Development seed accounts use `HEALO_DEMO_PASSWORD` (default: `password`):

- `admin@healo.test` — admin
- `demo@healo.test` — user with sample health, BMI, journal, nutrition, meal, menu, AI conversation, symptom-assessment, health-goal, and today's health-plan data

Never run the development seeder with unchanged demo credentials in production.

## Verification

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

Tests use isolated in-memory SQLite, while runtime configuration targets PostgreSQL.

## API foundation

| Method | Endpoint | Authentication |
| --- | --- | --- |
| POST | `/api/v1/auth/register` | Public, rate limited |
| POST | `/api/v1/auth/login` | Public, rate limited |
| DELETE | `/api/v1/auth/logout` | Sanctum token |
| GET | `/api/v1/profile` | Sanctum token |
| GET, PUT | `/api/v1/health-profile` | Sanctum token |
| GET, POST | `/api/v1/bmi-records` | Sanctum token |
| GET, DELETE | `/api/v1/bmi-records/{id}` | Sanctum token, owner only |
| GET, POST | `/api/v1/health-journals` | Sanctum token |
| GET, PUT, DELETE | `/api/v1/health-journals/{id}` | Sanctum token, owner only |
| POST | `/api/v1/health-journals/{id}/entries` | Sanctum token, owner only |
| PUT, DELETE | `/api/v1/health-journal-entries/{id}` | Sanctum token, owner only |
| GET, PUT | `/api/v1/nutrition/profile` | Sanctum token |
| GET | `/api/v1/nutrition/today`, `/api/v1/nutrition/summary` | Sanctum token |
| GET, POST | `/api/v1/meals` | Sanctum token |
| GET, PUT, DELETE | `/api/v1/meals/{id}` | Sanctum token, owner only |
| POST | `/api/v1/meals/{id}/items` | Sanctum token, owner only |
| PUT, DELETE | `/api/v1/meal-items/{id}` | Sanctum token, owner only |
| POST | `/api/v1/nutrition/food-analysis` | Sanctum token, 5 requests/minute |
| GET, POST, DELETE | `/api/v1/nutrition/food-analysis/{id}/*` | Sanctum token, owner only |
| GET | `/api/v1/nutrition/menus` | Sanctum token |
| GET, POST | `/api/v1/health-assistant/conversations` | Sanctum token |
| GET, DELETE | `/api/v1/health-assistant/conversations/{id}` | Sanctum token, owner only |
| POST | `/api/v1/health-assistant/conversations/{id}/messages` | Sanctum token, owner only, 10 requests/minute |
| POST | `/api/v1/symptom-checker/assess` | Sanctum token, 10 requests/minute |
| GET | `/api/v1/symptom-checker/history`, `/api/v1/symptom-checker/{id}` | Sanctum token, owner only |
| POST | `/api/v1/symptom-checker/{id}/journal` | Sanctum token, owner only, explicit confirmation |
| DELETE | `/api/v1/symptom-checker/{id}` | Sanctum token, owner only |
| GET | `/api/v1/health-insights` | Sanctum token, personalized and rate limited |
| GET | `/api/v1/health-insights/trends`, `/api/v1/health-insights/weekly` | Sanctum token |
| GET, POST | `/api/v1/health-goals` | Sanctum token |
| GET, PUT, DELETE | `/api/v1/health-goals/{id}` | Sanctum token, owner only |
| GET | `/api/v1/health-plan`, `/api/v1/health-plan/today` | Sanctum token, owner only |
| POST | `/api/v1/health-plan/generate` | Sanctum token, server-generated tasks and progress |
| POST | `/api/v1/health-plan/tasks/{id}/complete`, `/uncomplete` | Sanctum token, owner only, manual tasks only |
| GET | `/api/v1/notifications`, `/api/v1/notifications/unread-count` | Sanctum token, owner only |
| POST | `/api/v1/notifications/{id}/read`, `/api/v1/notifications/read-all` | Sanctum token, owner only |
| GET, PUT | `/api/v1/notification-preferences` | Sanctum token, validated owner preference |

The default `local_demo` food-analysis provider never sends images outside HEALO. It produces a deliberately low-confidence editable draft for the complete review/confirmation workflow. Replace it only through the provider abstraction and an explicit privacy/consent decision.

The health AI provider is selected with `HEALO_AI_PROVIDER`. Set it to `groq` with `GROQ_API_KEY` to use Groq, or `local_demo` for deterministic offline development. The configurable model defaults to `openai/gpt-oss-20b`. If Groq is selected without an API key, HEALO safely falls back to the local provider.

```env
HEALO_AI_PROVIDER=groq
GROQ_API_KEY=
GROQ_MODEL=openai/gpt-oss-20b
GROQ_TIMEOUT=20
GROQ_MAX_OUTPUT_TOKENS=1200
```

Groq receives only context needed for the requested AI feature. Phase 5–6 send aggregates rather than raw journal or meal text. Phase 6 uses AI only for wording, a general suggestion, and motivation; deterministic server rules remain the source of truth for tasks and progress. Provider responses are validated again by HEALO even when Groq Structured Outputs are enabled. See the [Groq API reference](https://console.groq.com/docs/api-reference), [Structured Outputs documentation](https://console.groq.com/docs/structured-outputs), and [GPT-OSS 20B model page](https://console.groq.com/docs/model/openai/gpt-oss-20b).

Conversation content and stored assessments remain encrypted at rest. Configure `HEALO_EMERGENCY_PHONE` or `HEALO_EMERGENCY_FACILITY_URL` only with verified local values; HEALO does not invent emergency numbers or request precise location.

## Design and architecture documents

- [Architecture plan](docs/architecture.md)
- [Database ERD and migration inventory](docs/database-erd.md)
- [Routes, pages, components and implementation roadmap](docs/implementation-roadmap.md)
- [Product backlog, use cases, activity diagrams, use case diagrams, and class diagrams](docs/system-analysis/README.md)

## Safety boundary

HEALO provides indicators, estimates, and educational information. AI features must never claim a medical diagnosis, prescribe medication, or replace professional care. When Groq is enabled, a clearly disclosed minimum health context is processed by that external provider. Private records remain owner-scoped, and the admin role does not implicitly grant access to their contents.
