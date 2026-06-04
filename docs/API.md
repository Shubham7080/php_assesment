# BriBooks API Reference

Base URL: `http://127.0.0.1:8000/api`

All responses are JSON. Protected endpoints require `Authorization: Bearer <access_token>`.

Common status codes:

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 401 | Unauthenticated (missing/invalid token) |
| 403 | Forbidden (role or ownership rule) |
| 404 | Not found |
| 422 | Validation error or invalid workflow transition |

---

## Authentication

### POST /register
Public. Creates a user and returns a token. `role` is optional (`author` default; `reviewer`, `admin`).

Request:
```json
{ "name": "Jane", "email": "jane@example.com", "password": "secret123", "password_confirmation": "secret123", "role": "author" }
```
Response `201`:
```json
{ "access_token": "<jwt>", "token_type": "bearer", "expires_in": 3600, "user": { "id": 1, "name": "Jane", "email": "jane@example.com", "role": "author" } }
```

### POST /login
Public.
```json
{ "email": "jane@example.com", "password": "secret123" }
```
Returns the same token payload as register (`200`), or `401` on bad credentials.

### GET /profile
Auth. Returns the current user.

### POST /logout
Auth. Invalidates the current token.

---

## Books

| Method | Endpoint | Role | Notes |
|--------|----------|------|-------|
| GET | /books | any | Authors see own books; reviewers/admins see all. Paginated. |
| POST | /books | author | Creates a book (status `draft`) and an initial version. |
| GET | /books/{id} | owner / reviewer / admin | Includes author + chapters + pages. |
| PUT | /books/{id} | owner | Blocked (403) once published. Creates a new version. |
| DELETE | /books/{id} | owner | Blocked (403) once published. |

Create request:
```json
{ "title": "My Book", "description": "...", "genre": "Fiction" }
```

---

## Versions

A version is an immutable JSON snapshot of the whole book.

| Method | Endpoint | Notes |
|--------|----------|-------|
| GET | /books/{id}/versions | List versions (ascending `version_number`). |
| POST | /books/{id}/versions | Force a manual snapshot. |
| GET | /books/{id}/versions/{versionId} | Single version with full snapshot. |

Snapshot shape:
```json
{ "id": 5, "book_id": 1, "version_number": 2, "created_by": 1,
  "snapshot": { "title": "...", "description": "...", "genre": "...", "status": "draft",
    "chapters": [ { "title": "Ch1", "position": 1, "pages": [ { "position": 1, "content": "<p>...</p>" } ] } ] } }
```

---

## Chapters

| Method | Endpoint | Notes |
|--------|----------|-------|
| GET | /books/{id}/chapters | List chapters (with pages). |
| POST | /books/{id}/chapters | `{ "title": "...", "position": 1 }` (position optional, auto-appended). |
| PUT | /chapters/{id} | Update title/position. |
| DELETE | /chapters/{id} | Delete chapter (cascades pages). |

All require the parent book to be owned by the author and not published.

---

## Pages

| Method | Endpoint | Notes |
|--------|----------|-------|
| GET | /chapters/{id}/pages | List pages. |
| POST | /chapters/{id}/pages | `{ "content": "<p>...</p>", "position": 1 }` (position optional). |
| PUT | /pages/{id} | Update content/position. |
| DELETE | /pages/{id} | Delete page. |

---

## Document Upload

### POST /books/{id}/upload
Auth (owner). `multipart/form-data` with field `document` (`.doc` / `.docx`, max 10 MB).
Converts the manuscript into a new chapter of HTML pages.

```bash
curl -X POST .../api/books/1/upload -H "Authorization: Bearer $TOKEN" -F "document=@manuscript.docx"
```
Response `201`:
```json
{ "message": "Document converted into pages.", "data": { "id": 3, "title": "manuscript", "pages": [ { "position": 1, "content": "<div>...</div>" } ] } }
```

---

## Workflow

State machine: `draft → submitted → under_review → approved → published`, plus `rejected`.

| Method | Endpoint | Role | Effect |
|--------|----------|------|--------|
| POST | /books/{id}/submit | author (owner) | `draft`/`rejected` → `submitted`; moderation runs automatically. Clean → `under_review`; flagged → back to `draft` with a moderation report. |
| POST | /books/{id}/approve | reviewer | `under_review` → `approved`. |
| POST | /books/{id}/reject | reviewer | `submitted`/`under_review` → `rejected`. Optional `{ "reason": "..." }`. |
| POST | /books/{id}/publish | admin | `approved` → `published` (sets `published_at`; book becomes read-only). |

Invalid transitions return `422` with a message, e.g. `Cannot publish a book in 'draft' status.`

The `moderation_report` field on a book after submit:
```json
{ "passed": false, "profanity": ["damn"], "restricted": [], "checked_at": "2026-06-03T16:00:00+00:00" }
```

---

## Dashboard

### GET /dashboard
Auth. Returns the current author's stats and recent books.
```json
{ "data": { "stats": { "total_books": 4, "drafts": 1, "submitted": 0, "under_review": 1, "approved": 0, "rejected": 0, "published": 2 },
  "recent_books": [ { "id": 4, "title": "...", "status": "published" } ] } }
```
