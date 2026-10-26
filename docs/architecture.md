# HEALO Architecture Plan

## Product boundary

HEALO is a standalone personal-health companion. Version one has no dependency on Zavora, a hospital, a clinic, an insurer, a pharmacy, a wearable, or a payment platform. External AI is accessed only through replaceable contracts.

## Architectural style

The application is a modular Laravel monolith with server-rendered Blade views and a versioned JSON API. Modules share one PostgreSQL database while keeping validation, authorization, orchestration, and domain calculations separate.

```text
HTTP (Blade / API v1)
  -> Form Requests
  -> Controllers
  -> Application Services
  -> Repositories / Eloquent Models
  -> PostgreSQL

Application Services
  -> Policies and consent checks
  -> Events / Notifications / queued jobs
  -> AI contracts -> configured provider
```

## Module boundaries

| Module | Responsibilities |
| --- | --- |
| Identity | Authentication, email verification, roles, sessions, account lifecycle |
| Profile | Personal profile, consent, health profile and privacy settings |
| Health | BMI, journal, timeline and personal health summaries |
| Nutrition | Food catalogue, food log, nutrition totals and calorie targets |
| AI | Provider-neutral symptom analysis and food recognition |
| Menu | Healthy-menu catalogue and constraint-aware recommendations |
| Notification | Durable owner-scoped notifications, preferences, reminders, quiet hours, queue delivery and Reverb updates |
| Reporting | User-owned PDF exports |
| Administration | Aggregate dashboards and catalogue management; no implicit access to private health records |
| Audit | Minimal, redacted records of security-sensitive actions |

## Code organization

```text
app/
  Contracts/AI/
  Data/
  Events/
  Http/Controllers/{Web,Api/V1,Admin}/
  Http/Requests/
  Http/Resources/
  Jobs/
  Models/
  Notifications/
  Policies/
  Repositories/
  Services/{AI,BMI,Health,Journal,Nutrition,Menu,Audit}/
```

Controllers translate HTTP input and output. Form Requests validate and authorize. Services own calculations and workflows. Policies enforce ownership. Eloquent repositories are introduced where querying or persistence behavior is substantial, not as pass-through wrappers.

## AI boundary

`SymptomAnalyzer` and `FoodRecognizer` are application-facing contracts. Provider adapters receive normalized data and return typed result objects. Controllers never call an AI SDK directly. Long-running work is queued, failures are mapped to safe user messages, raw sensitive prompts and responses are not logged by default, and results require user confirmation before becoming health or food records.

## Privacy and security defaults

- Every health-data query is scoped to the authenticated owner.
- Admin capabilities operate on aggregate metrics and managed catalogues unless a separately audited support workflow is introduced later.
- Uploaded food images are private, validated, size-limited, and deletable; retention is configurable.
- Sensitive free text is excluded from application and audit logs.
- CSRF, output escaping, authentication throttling, API token abilities, policies, and rate limits are applied at the framework boundary.
- Medical language remains educational: AI output is not a diagnosis and nutrition values are estimates.

## Web, API, and realtime

Blade + Tailwind + Alpine.js is the primary UI. `/api/v1` mirrors domain capabilities using Sanctum and API Resources. Database notifications are the durable source of truth. Reminder generation runs in retry-safe queue jobs, while owner-private Reverb channels update the Alpine notification badge and toast without polling. Reverb outages do not block persistence or normal application requests because broadcasting is queued.

## Notification flow

```text
Laravel Scheduler
  -> healo:send-reminders --respect-preferences
  -> SendWellnessReminderJob (notifications queue)
  -> ReminderService reads owner-scoped health data
  -> NotificationService applies preferences, quiet hours and deduplication
  -> notifications table
  -> queued NotificationCreated broadcast
  -> private-users.{id}.notifications
  -> Laravel Echo / Reverb / Alpine UI
```

Quiet-hours records remain durable with a future `available_at`. `healo:deliver-notifications` atomically claims due rows using `delivered_at`, so scheduler overlap or queue retry cannot broadcast the same record twice. No raw journal, meal, profile, email, provider detail, or medical assessment is included in broadcast payloads.

## Configuration

PostgreSQL, mail, storage, queue, Reverb, and AI providers are configured through environment variables. Provider credentials never enter source control. Local tests use an isolated database connection and never depend on production services.

