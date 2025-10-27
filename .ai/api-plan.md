# REST API Plan

## 1. Resources
- `events` → `events` table; provides public metadata required by guests to understand upload status and window.
- `guest_sessions` (virtual) → backed by `guests` table; represents immutable guest identity within an event and associated session token stored in localStorage.
- `photos` → `photos` table; stores uploaded assets tied to guest session and event, using soft delete for moderation outside scope of this API.

## 2. Endpoints

### 2.1 Event Metadata (`events`)

#### GET `/api/events/{event_id}`
- **Description** Retrieve public event information and upload window state so a guest can decide whether uploads are allowed.
- **Response JSON**
```json
{
  "id": "uuid",
  "name": "Anna & Piotr",
  "date": "2025-06-14T14:00:00Z",
  "time_window": {
    "starts_at": "2025-06-13T14:00:00Z",
    "ends_at": "2025-06-15T14:00:00Z",
    "status": "active",
    "message": "Wydarzenie trwa!"
  },
  "upload_rules": {
    "max_photos_per_session": 15,
    "allowed_formats": ["image/jpeg", "image/png"],
    "max_dimensions": "2560x1920",
    "quality": 85
  },
  "gallery_published": true
}
```
- **Success Codes** `200 OK`.
- **Error Codes** `404 Not Found` invalid `event_id`; `410 Gone` event archived.

### 2.2 Guest Session (`guest_sessions`)

#### POST `/api/events/{event_id}/sessions`
- **Description** Create an immutable guest session for a specific event. Guests supply only their name; the API returns a session token that must be stored client-side (localStorage) and reused for all subsequent uploads.
- **Request JSON**
```json
{
  "name": "Kasia"
}
```
- **Response JSON**
```json
{
  "guest": {
    "id": "uuid",
    "name": "Kasia"
  },
  "session": {
    "token": "session-id",
    "photo_quota_remaining": 15
  }
}
```
- **Success Codes** `201 Created`.
- **Error Codes** `404 Not Found` event missing; `422 Unprocessable Entity` invalid name or name already bound to a different session hint (if enforced); `403 Forbidden` event outside upload window.

### 2.3 Photo Upload (`photos`)

#### POST `/api/events/{event_id}/photos`
- **Description** Upload a processed photo using an existing session token. No other guest actions are exposed.
- **Headers** `X-Guest-Session: session-id`, `Content-Type: multipart/form-data`.
- **Form Fields**
  - `file` (required) – JPEG/PNG ≤ 10 MB, already cropped/compressed on client.
  - `taken_at` (optional) – ISO 8601 timestamp captured on device.
- **Response JSON**
```json
{
  "photo": {
    "id": "uuid",
    "filename": "uuidv6.jpg",
    "uploaded_at": "2025-06-14T16:12:00Z",
    "taken_at": "2025-06-14T15:55:00Z"
  },
  "quota": {
    "remaining": 14,
    "limit": 15
  }
}
```
- **Success Codes** `201 Created`.
- **Error Codes** `400 Bad Request` malformed multipart data; `401 Unauthorized` missing/invalid session token; `403 Forbidden` upload window closed or quota exhausted; `404 Not Found` event or guest session missing; `415 Unsupported Media Type` wrong format; `429 Too Many Requests` rate-limit exceeded.

## 3. Authentication and Authorization
- There is no traditional user authentication. Access control relies on valid `event_id` plus a guest session token (`X-Guest-Session`) issued by `POST /api/events/{event_id}/sessions`.
- Session tokens are UUIDv4 values stored hashed in `guests.session_id` to prevent credential leakage. Tokens are immutable: no logout, reset, or regeneration flows exposed to guests.
- CORS restricts origins to official guest web app domains. All traffic must use HTTPS; reject insecure requests. Rate limiting throttles abuse: `30 req/min` per session token for uploads, `60 req/min` per IP for event metadata.

## 4. Validation and Business Logic
- **Events**: validate `event_id` as UUID. Responses include computed upload window (`date ± 12h`). If current time is outside window, `status` becomes `upcoming` or `closed`, and `POST /photos` returns `403` with friendly message. Event may be unpublished (`gallery_published = false`) yet still accept uploads if within window.
- **Guest Sessions**: `name` required (2–50 chars, allow Polish characters, trim whitespace). Creating a session outside the upload window returns `403`. Once created, the session cannot be deleted or renamed; the client must reuse the original token indefinitely.
- **Photos**: enforce server-side validations matching client processing rules—JPEG/PNG only, ≤10 MB, aspect ratio 4:3 ± 2%, resolution max 2560×1920. Generate storage filename with UUID v6. Set `uploaded_at` on server time; optional `taken_at` must be ≤ current time +5 minutes and within event window ±24h. Limit uploads to 15 active photos per session (soft-deleted photos still count until moderation pipeline outside this API handles them). Return localized retro-style error messages alongside machine-readable `code` (e.g., `"code": "quota_exceeded"`).
- **Security & Performance**: apply Laravel rate limiting to prevent burst uploads, log attempts with IP + session token hash for audit. Use eager loading to avoid N+1 queries when deriving quota. Asset delivery (CDN) remains out of scope; this API issues metadata only. Ensure UUID-based filenames keep gallery URLs unguessable.
