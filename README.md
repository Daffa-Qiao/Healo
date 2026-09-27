# HEALO

**Heal + Hello — Your everyday health companion.**

HEALO is a standalone, privacy-conscious personal health application. It is built as a modular Laravel monolith with a Blade/Alpine interface and a versioned Sanctum API. It is not coupled to Zavora, a hospital, a clinic, or another healthcare platform.

## Current delivery status

Phase 1 foundation is implemented:

- Laravel 12, Blade, Tailwind CSS, Alpine.js and Vite.
- Registration, login, logout, email verification, password reset and account deletion.
- User/admin roles with a separate, access-controlled admin dashboard.
- Sanctum `/api/v1` registration, login, logout and profile endpoints.
- Indonesian and English localization with per-user preference.
- Responsive HEALO landing page, dashboard shell, desktop sidebar and mobile bottom navigation.
- Minimal audit logging with hashed IP addresses and no health content.
- PostgreSQL-ready environment configuration and SQLite in-memory tests.
- Reverb, DomPDF, Chart.js, DataTables, SweetAlert2 and Lucide dependencies ready for their planned modules.

Health, nutrition, AI, menu, realtime and reporting screens are clearly marked as planned rather than presented as working functionality. See [implementation roadmap](docs/implementation-roadmap.md).

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

Development seed accounts use the password `password`:

- `admin@healo.test` — admin
- `demo@healo.test` — user

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

## Design and architecture documents

- [Architecture plan](docs/architecture.md)
- [Database ERD and migration inventory](docs/database-erd.md)
- [Routes, pages, components and implementation roadmap](docs/implementation-roadmap.md)

## Safety boundary

HEALO provides indicators, estimates, and educational information. AI features must never claim a medical diagnosis, prescribe medication, or replace professional care. Private health records are owner-scoped, and the admin role does not implicitly grant access to their contents.
