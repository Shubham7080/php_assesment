# BriBooks — Mini Book Writing Platform

A backend REST API for a simplified book authoring and publishing platform. Authors write books composed of chapters and pages, snapshot versions of their work, upload manuscripts, submit books through an automated moderation + human review workflow, and admins publish approved books.

Built with **Laravel 13 / PHP 8.3**, **MySQL 8 (MariaDB compatible)**, **JWT authentication**, and an **event-driven** moderation/notification pipeline.

---

## Features

| Area | Summary |
|------|---------|
| Authentication | JWT login/register/profile/logout, three roles (author, reviewer, admin), bcrypt hashing |
| Book management | CRUD, authors manage only their own books, published books are read-only |
| Versioning | Every create/update snapshots the full book (metadata + chapters + pages) into a JSON version, with rollback support |
| Chapters & Pages | Nested CRUD under books / chapters with ordering |
| Document upload | `.doc` / `.docx` manuscripts converted into HTML pages (PHPWord) |
| Moderation | Profanity + restricted-word detection on submit, driven by config |
| Workflow | `draft → submitted → under_review → approved → published` (+ `rejected`), role-gated transitions |
| Dashboard | Per-author book statistics and recent books |
| Tests | 27 PHPUnit feature tests covering auth, books, versioning, workflow, moderation, authorization |

---

## Requirements

- PHP 8.3+
- Composer
- MySQL 8+ (or MariaDB 10.6+)
- ext-pdo_mysql, ext-mbstring, ext-zip, ext-xml, ext-gd

---

## Installation

```bash
# 1. Install dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure the database in .env
#    DB_DATABASE=assesment_db, DB_USERNAME, DB_PASSWORD
mysql -u root -p -e "CREATE DATABASE assesment_db"

# 4. Generate the JWT signing secret
php artisan jwt:secret

# 5. Run migrations
php artisan migrate

# 6. Serve
php artisan serve
```

The API is served under `http://127.0.0.1:8000/api`.

---

## Running Tests

Tests run against a dedicated MySQL database (the SQLite driver is not assumed to be installed).

```bash
# One-time: create the test database
mysql -u root -p -e "CREATE DATABASE assesment_db_test"

# Run the suite
php artisan test
```

Database credentials for tests are set in `phpunit.xml` (`DB_DATABASE=assesment_db_test`). Adjust the `DB_USERNAME` / `DB_PASSWORD` env entries there if your MySQL credentials differ. `RefreshDatabase` migrates a clean schema for every test.

Run a single test:

```bash
php artisan test --filter=WorkflowTest
php artisan test --filter=test_only_a_reviewer_can_approve
```

---

## API Usage

All endpoints are JSON. Authenticated routes require an `Authorization: Bearer <token>` header. Full endpoint reference: [docs/API.md](docs/API.md).

```bash
# Register (role defaults to "author"; pass reviewer/admin explicitly)
curl -X POST http://127.0.0.1:8000/api/register \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"name":"Jane","email":"jane@example.com","password":"secret123","password_confirmation":"secret123"}'

# Login -> returns access_token
curl -X POST http://127.0.0.1:8000/api/login \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"email":"jane@example.com","password":"secret123"}'

# Create a book
curl -X POST http://127.0.0.1:8000/api/books \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"title":"My Book","description":"...","genre":"Fiction"}'

# Submit -> moderation runs automatically; clean books advance to under_review
curl -X POST http://127.0.0.1:8000/api/books/1/submit -H "Authorization: Bearer $TOKEN"
```

Workflow in brief: an **author** submits, a **reviewer** approves/rejects, an **admin** publishes.

---

## Architecture Decisions

- **Layered structure.** Controllers stay thin: validation lives in `FormRequest` classes, business logic in `app/Services`, output shaping in `app/Http/Resources` (API Resources), and authorization in a `BookPolicy`. This keeps each concern testable in isolation.

- **Event-driven workflow.** State transitions live in `BookWorkflowService` and emit domain events (`BookSubmitted`, `BookApproved`, `BookPublished`, …). Side effects are decoupled into listeners:
  - `BookSubmitted → ModerationListener` runs content moderation. On pass it fires `ModerationPassed → AdvanceToUnderReviewListener`; on fail it returns the book to draft and fires `ModerationFailed`.
  - `BookPublished / BookApproved / BookRejected / ModerationFailed → NotificationListener`.
  - `BookCreated / BookVersionCreated / BookPublished → AnalyticsListener`.
  This mirrors the chain in the brief and makes the moderation step swappable without touching the controller.

- **Versioning via JSON snapshots.** Each `book_versions` row stores a complete JSON snapshot (title, description, genre, chapters, pages) with a monotonically increasing `version_number` per book. Rollback rebuilds the book's chapters/pages from a chosen snapshot and records a new version. JSON snapshots were chosen over a fully normalized version history because they make point-in-time reads and rollbacks trivial (one row = one complete book state) and keep writes cheap — a good fit for an assessment while remaining horizontally scalable.

- **Role-based authorization.** Roles are a typed `UserRole` enum stored on `users.role`, embedded as a JWT custom claim. `BookPolicy` enforces both ownership (authors touch only their own books) and role gates (reviewers review, admins publish). Published books are read-only via the policy's `update`/`delete` checks.

- **Consistent JSON.** A `ForceJsonResponse` middleware on the `api` group guarantees JSON responses (including `401`/`422`/`404`) regardless of the client's `Accept` header. `WorkflowException` renders as `422` with a clear message.

- **Document conversion.** `DocumentConversionService` loads `.doc`/`.docx` via PHPWord, extracts the HTML body, and splits it into pages on page breaks, creating a chapter of HTML pages.

---

## Assumptions & Trade-offs

- **Database for tests.** The environment lacks the SQLite PHP driver, so the test suite targets a MySQL `assesment_db_test` database instead of in-memory SQLite. Migrations are portable across both.
- **"Significant update" = every update.** Updating a book always creates a new version. A production system might debounce or threshold this; here every change is versioned for clarity.
- **Moderation is config-driven, not ML.** Profanity / restricted words come from `config/moderation.php` (overridable via env). This keeps moderation deterministic and unit-testable. Swapping in a third-party service means changing only `ModerationService`.
- **Synchronous events.** Listeners run in-process (no queue). The events are queue-ready (`SerializesModels`); moving moderation/notifications/conversion to background jobs is the documented next step but is part of the optional bonus scope and was intentionally left out.
- **Reviewers/admins can read all books; authors only their own** — applied in both the book index query and `BookPolicy::view`.
- **Optional bonus features were intentionally skipped**: Redis caching, queue workers, OpenAI integration. The focus was a clean, correct, well-tested implementation of the mandatory requirements.

---

## Project Map

```
app/
  Enums/            UserRole, BookStatus
  Events/           BookCreated, BookSubmitted, ModerationPassed, BookApproved, BookPublished, ...
  Listeners/        ModerationListener, AdvanceToUnderReviewListener, NotificationListener, AnalyticsListener
  Services/         BookWorkflowService, ModerationService, BookVersionService, DocumentConversionService
  Policies/         BookPolicy
  Http/
    Controllers/Api/  Auth, Book, Chapter, Page, BookVersion, BookWorkflow, DocumentUpload, Dashboard
    Requests/         FormRequest validation classes
    Resources/        BookResource, ChapterResource, PageResource, BookVersionResource, UserResource
    Middleware/       ForceJsonResponse
  Models/           User, Book, Chapter, Page, BookVersion
database/migrations/  users(+role), books, chapters, pages, book_versions
routes/api.php
tests/Feature/       Auth, BookManagement, BookVersion, Workflow, Moderation, Authorization
docs/API.md
```
