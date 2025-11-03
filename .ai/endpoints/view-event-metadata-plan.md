## API Endpoint Implementation Plan: GET /api/events/{event_id}

### 1. Przegląd punktu końcowego
- Dostarcza publiczne metadane wydarzenia, status okna uploadu i zasady przesyłania zdjęć dla aplikacji gości.
- Służy do określenia, czy gość może rozpoczynać lub kontynuować proces przesyłania plików.
- Uwzględnia stan publikacji galerii oraz komunikaty lokalizowane (retro-styl) zależne od stanu wydarzenia.

### 2. Szczegóły żądania
- **Metoda HTTP:** GET
- **Struktura URL:** `/api/events/{event_id}`
- **Parametry ścieżki:**
  - `event_id` (wymagany) – identyfikator UUID wydarzenia.
- **Nagłówki:**
  - `Accept: application/json` (wymagany implicit, potwierdzić w dokumentacji).
- **Parametry zapytania:** brak.
- **Rate limiting:** 60 req/min per IP (Route throttle lub `RateLimiter::for('event-metadata')`).

### 3. Wykorzystywane typy
- **Form Request / Route Binding:** `ShowEventRequest` (waliduje UUID, autoryzuje dostęp; alternatywnie route-model binding z obsługą `ModelNotFoundException`).
- **Serwis domenowy:** `EventMetadataService::fetch(EventMetadataQuery $query): EventMetadataResult` – agreguje dane i status okna.
- **DTO wejściowe:** `EventMetadataQuery` (eventId, requestContext).
- **DTO wyjściowe:** `EventMetadataResult` (event, timeWindow, uploadRules, galleryPublished, statusMessage).
- **Resource/Transformer:** `EventMetadataResource` (Mapuje wynik na JSON zgodny ze specyfikacją).
- **Helpery:** `UploadWindowCalculator`, `TimeWindowStatusMapper` (współdzielone z innymi endpointami).

### 4. Szczegóły odpowiedzi
- **Kod powodzenia:** `200 OK`.
- **JSON (skrótowo):** `id`, `name`, `date`, `time_window.{starts_at, ends_at, status, message}`, `upload_rules.{max_photos_per_session, allowed_formats, max_dimensions, quality}`, `gallery_published`.
- **Nagłówki odpowiedzi:** `Cache-Control` (np. `max-age=30, public` aby wspierać CDN), `Content-Type: application/json; charset=utf-8`.
- **Lokalizacja:** `time_window.message` pochodzi z plików tłumaczeń (`lang/pl.json`) na podstawie statusu.

### 5. Przepływ danych
- Routing (`routes/api.php`) → middleware (`throttle`, `EnsureFrontendRequestsHttps`, `cors`), ewentualnie `bindings`.
- FormRequest (lub route binding) waliduje `event_id`; w razie sukcesu przekazuje do kontrolera (`EventController@show`).
- Kontroler buduje `EventMetadataQuery`, deleguje do `EventMetadataService`.
- Serwis pobiera `Event` (`events` tabela) z repozytorium, sprawdza `gallery_published`, określa status okna na podstawie `date` (±12h) i `now()`.
- Serwis konstruuje reguły uploadu (stałe/konfig) i zwraca DTO.
- Resource transformuje DTO do JSON i zwraca odpowiedź.

### 6. Względy bezpieczeństwa
- Tokeny nie są wymagane, ale endpoint ograniczać do HTTPS i zaufanych originów (CORS whitelist).
- Zapobieganie enumeracji: brak bocznych kanałów w odpowiedziach (użyj tych samych opóźnień/logów przy 404/410).
- Ukrywanie szczegółów dla zdarzeń zarchiwizowanych (410) – bez ujawniania metadanych.
- Monitorowanie logów dostępu (IP, user agent) dla detekcji scrapingów.

### 7. Obsługa błędów
- **400 Bad Request:** `event_id` niepoprawny format (walidacja UUID).
- **404 Not Found:** Event nie istnieje lub nie został wydany gościom (np. `gallery_published` = false i polityka ukrywania).
- **410 Gone:** Event zarchiwizowany (`archived_at` jeśli dostępne lub status eventu); odpowiedź bez ciała lub z komunikatem.
- **500 Internal Server Error:** problemy z bazą danych, nieobsłużone wyjątki – logowane i maskowane.
- Logowanie błędów w kanale `api` z kontekstem `event_id`, IP, user agent.

### 8. Rozważania dotyczące wydajności
- Włączyć caching wyników (np. `Cache::remember` na 30–60 sekund lub dłużej) dla popularnych wydarzeń.
- Zapewnić indeksy na `events.id` oraz ewentualnie na `events.archived_at`/` gallery_published`.
- Rozważyć preload danych statycznych (reguł uploadu) z configu zamiast DB.
- Minimalizować zapytania – pojedyncze zapytanie do `events` bez relacji (szczegóły gości niepotrzebne).

### 9. Etapy wdrożenia
1. Zarejestrować trasę `Route::get('/events/{event}', ...)` z aliasem `events.show` i middleware `throttle:event-metadata`.
2. Utworzyć `ShowEventRequest` (lub dostosować binding) z walidacją UUID i autoryzacją publikacji.
3. Zaimplementować `EventMetadataService` wraz z helperami liczącymi okno czasowe i status.
4. Utworzyć DTO `EventMetadataQuery/Result` oraz `EventMetadataResource` zgodny ze specyfikacją.
5. Dodać mapowanie wyjątków (`EventArchivedException`, `EventNotFoundException`) w handlerze do 410/404.
6. Skonfigurować caching i rate limiting (w `RouteServiceProvider` / `RateLimiter`).
7. Zaimplementować testy feature: sukces dla aktywnego eventu (200), brak eventu (404), event zarchiwizowany (410), niepoprawny UUID (400).
8. Zaktualizować dokumentację API oraz pliki tłumaczeń dla komunikatów statusu okna.
9. Monitorować logi po wdrożeniu i dostroić caching/rate limit jeżeli potrzebne.

