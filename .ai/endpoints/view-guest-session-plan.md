## API Endpoint Implementation Plan: POST /api/events/{event_id}/sessions

### 1. Przegląd punktu końcowego
- Tworzy nową, niezmienną sesję gościa dla określonego wydarzenia na potrzeby procesu uploadu.
- Zwraca identyfikator gościa i token sesji (do przechowywania w localStorage), kontroluje limity nazwy i okno czasowe wydarzenia.
- Zapewnia odporność na duplikaty oraz bezpieczeństwo tokenów (UUIDv4 przechowywany jako hash w DB).

### 2. Szczegóły żądania
- **Metoda HTTP:** POST
- **Struktura URL:** `/api/events/{event_id}/sessions`
- **Parametry ścieżki:**
  - `event_id` (wymagany) – UUID wydarzenia.
- **Nagłówki:**
  - `Content-Type: application/json`.
  - `Accept: application/json`.
- **Body JSON:**
  - `name` (wymagany) – string 2–50 znaków, dopuszczalne polskie litery, brak wiodących/trailing whitespace (trim przed walidacją).
- **Rate limiting:** dedykowany limiter (np. 10 req/min na IP i 5/min na event) w celu ograniczenia floodu.

### 3. Wykorzystywane typy
- **Form Request:** `CreateGuestSessionRequest` (autoryzuje event, waliduje `name`, weryfikuje okno czasowe).
- **DTO wejściowe:** `CreateGuestSessionData` (eventId, normalizedName, requestIp, userAgent).
- **Serwis domenowy:** `GuestSessionService::create(CreateGuestSessionData $data): GuestSessionResult`.
- **Wartości pomocnicze:**
  - `SessionTokenGenerator` (UUIDv4 + hashing, np. SHA256 z solą).
  - `UploadWindowChecker` (współdzielony z innymi endpointami).
- **DTO wyjściowe:** `GuestSessionResult` (guest model, session token plaintext, quota info).
- **Resource:** `GuestSessionResource` (JSON: `guest`, `session`).

### 4. Szczegóły odpowiedzi
- **Kod powodzenia:** `201 Created`.
- **JSON:**
  - `guest.id` (UUID), `guest.name` (string).
  - `session.token` (plaintext UUIDv4), `session.photo_quota_remaining` (int = 15 na start).
- **Nagłówki:** `Content-Type: application/json; charset=utf-8`.
- **Uwagi:** Token zwracany tylko raz – klient musi bezpiecznie przechowywać (localStorage); brak endpointu odświeżenia.

### 5. Przepływ danych
- Trasa → middleware (`throttle`, `EnsureFrontendRequestsHttps`, `cors`).
- `CreateGuestSessionRequest` waliduje `event_id`, `name`, weryfikuje okno czasowe (można użyć reguły `after_or_equal/before_or_equal` dla daty eventu).
- Kontroler tworzy `CreateGuestSessionData` i przekazuje do `GuestSessionService`.
- Serwis:
  1. Pobiera `Event` (z eager loadem istniejących gości, jeśli potrzebny check unikalności).
  2. Sprawdza status upload window (helper) → przy `closed/upcoming` zwraca wyjątek domenowy.
  3. Normalizuje nazwę (trim, collapse whitespace, ewentualna transliteracja do logów).
  4. Opcjonalnie sprawdza kolizje (np. czy nazwa już istnieje i czy polityka na to pozwala).
  5. Generuje token (UUIDv4), hashuje (`hash('sha256', $token . config('app.key'))`).
  6. Tworzy rekord w `guests` (event_id, name, session_id hash).
- Serwis zwraca `GuestSessionResult`; kontroler transformuje do JSON i odpowiada 201.

### 6. Względy bezpieczeństwa
- Haszowanie tokenów przed zapisem, brak przechowywania plaintextu w DB.
- Zabezpieczenie przed brute-force: rate limiting oraz ujednolicone odpowiedzi na błędne tokeny/kolizje.
- Walidacja i sanitacja nazwy (escape w odpowiedziach, unikanie XSS).
- Wymuszenie HTTPS i CORS do aplikacji gościa.
- Logowanie IP i user agent w audycie tworzonych sesji (możliwe osobne tabeli `guest_session_logs`).

### 7. Obsługa błędów
- **400 Bad Request:** niepoprawny JSON, brak `name` po parsowaniu.
- **404 Not Found:** event nie istnieje.
- **403 Forbidden:** okno uploadu zamknięte (`window_closed`), event unpublished (jeśli polityka).
- **422 Unprocessable Entity:** naruszenia walidacji (zły format imienia, za krótka/długa nazwa, duplikat nazwy jeżeli wymagana unikalność).
- **409 Conflict (opcjonalnie):** próba utworzenia podwójnej sesji dla tego samego tokenu lub nazwy, jeśli polityka na to wskazuje.
- **500 Internal Server Error:** nieprzewidziane wyjątki – logowanie + maskowanie.
- Każdy błąd powinien zawierać `code` i `message` (retro-styl), ewentualnie `hints` dla UI.

### 8. Rozważania dotyczące wydajności
- Wymaga jednego zapytania select + insert; utrzymywać indeksy na `guests.session_id` (unique) oraz `guests.event_id`.
- Walidacja unikalności nazwy może korzystać z `where event_id` aby uniknąć pełnego skanu.
- Możliwe dołączenie pamięci podręcznej (cache) statusu okna wydarzenia z endpointu GET.
- Asynchroniczne logowanie (queue) jeśli zapis do tabeli audytowej wpływa na czas odpowiedzi.

### 9. Etapy wdrożenia
1. Dodać trasę `Route::post('/events/{event}/sessions', ...)` z limiterem `sessions-create`.
2. Utworzyć `CreateGuestSessionRequest` z regułami walidacji (trim w `prepareForValidation`), autoryzacją eventu.
3. Zaimplementować `GuestSessionService`, `SessionTokenGenerator`, `UploadWindowChecker` (reuse).
4. Dodać DTO `CreateGuestSessionData`, `GuestSessionResult` oraz `GuestSessionResource` dla odpowiedzi.
5. Dodać wyjątki domenowe (`UploadWindowClosedException`, `DuplicateGuestNameException`) i mapować je na 403/422/409 w handlerze.
6. Skonfigurować rate limiting (`RateLimiter::for('sessions-create')`).
7. Napisać testy feature: sukces (201, poprawny JSON), brak eventu (404), zamknięte okno (403), walidacja nazwy (422), duplikat (422/409).
8. Zaktualizować dokumentację API + definicje tłumaczeń komunikatów.
9. Dodać logowanie zdarzeń (Monolog channel lub event `GuestSessionCreated`) oraz monitorować po wdrożeniu.

