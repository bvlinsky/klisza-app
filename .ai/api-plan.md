# REST API Plan

## 1. Resources
- `events` → `events` table; provides public metadata required by guests to understand upload status and window.
- `guests` → `guests` table; represents authenticated guest identity within an event, managed via Laravel Sanctum tokens.
- `photos` → `photos` table; stores uploaded assets tied to authenticated guest and event, using soft delete for moderation outside scope of this API.

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
  "gallery_published": true
}
```

### 2.2 Guest Authentication (`guests`)

#### POST `/api/events/{event_id}/auth`
- **Description** Authenticate a guest for a specific event using Laravel Sanctum. Guests supply their name to create or retrieve their identity; the API returns a Sanctum access token that must be stored client-side and used in Authorization header for subsequent requests.
- **Request JSON**
```json
{
  "name": "Kasia"
}
```
- **Response JSON**
```json
{
  "access_token": "sanctum-token",
  "quota_remaining": 15
}
```

### 2.3 Photo Upload (`photos`)

#### POST `/api/events/{event_id}/photos`
- **Description** Upload a processed photo using Sanctum authentication. Requires valid Bearer token from guest authentication.
- **Headers** `Authorization: Bearer sanctum-token`, `Content-Type: multipart/form-data`.
- **Form Fields**
  - `file` (required) – JPEG/PNG ≤ 10 MB, already cropped/compressed on client.
  - `taken_at` (required) – ISO 8601 timestamp captured on device.
- **Response JSON**
```json
{
  "quota_remaining": 14
}
```

## 3. Authentication and Authorization
- Guest authentication is handled via Laravel Sanctum for mobile/SPA applications. Guests authenticate once per event and receive a Bearer token.
- Access control relies on valid `event_id` plus authenticated guest identity through Sanctum middleware.
- The Guest model uses `HasApiTokens` trait. Tokens are managed by Sanctum and can be revoked if needed.
- Authentication is required for photo uploads; event metadata is publicly accessible.

## 4. Validation and Business Logic
- **Events**: validate `event_id` as UUID. Responses include computed upload window (`date ± 12h`). An unpublished event (`gallery_published = false`) can still accept uploads while the window is active.
- **Guest Sessions**: `name` required (2–50 chars, allow Polish characters). The client must reuse the issued session token after creation.
- **Photos**: enforce server-side validations matching client processing rules—JPEG only, ≤10 MB, resolution max 2560×1920. Generate storage filename with UUID v6. Set `uploaded_at` on server time; optional `taken_at` must be within event window ±24h. Limit uploads to 15 active photos per session.
- **Security & Performance**: Use eager loading to avoid N+1 queries when deriving quota. Ensure UUID-based filenames keep gallery URLs unguessable.
