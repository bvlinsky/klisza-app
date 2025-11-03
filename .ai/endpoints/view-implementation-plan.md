## API Endpoint Implementation Plan: POST /api/events/{event_id}/photos

### 1. Przegląd punktu końcowego
- Umożliwia gościom przesyłanie zdjęć powiązanych z konkretnym wydarzeniem i sesją gościa.
- Obsługuje walidację okna czasowego wydarzenia, limitów ilościowych (15 aktywnych zdjęć na sesję) oraz poprawności technicznej plików.
- Generuje losowe nazwy plików (UUID v6), zapisuje metadane i zwraca pozostały limit zdjęć w odpowiedzi.
- Plan zakłada jednoczesną zgodność ze specyfikacją API i globalnym ograniczeniem kodów statusu; dla scenariuszy 403/415/429 stosujemy `400` z bogatym `code` domenowym, ale zalecamy eskalację decyzji produktowej.

### 2. Szczegóły żądania
- **Metoda HTTP:** POST
- **Struktura URL:** `/api/events/{event_id}/photos`
- **Parametry ścieżki:**
  - `event_id` (wymagany, UUID istniejącego rekordu `events`).
- **Nagłówki:**
  - `X-Guest-Session` (wymagany, UUIDv4 token sesji gościa, haszowany w bazie).
  - `Content-Type: multipart/form-data` (wymagany).
- **Form Data:**
  - `file` (wymagany) – `UploadedFile`, MIME `image/jpeg` lub `image/png`, max 10 MB, rozdzielczość ≤ 2560×1920, proporcje 4:3 ± 2%.
  - `taken_at` (opcjonalny) – ISO 8601, `<= now()+5m`, mieści się w oknie wydarzenia ±24h.
- **Limitowanie:** Laravel rate limiter, 30 req/min na token, 60 req/min na IP (spójnie z warstwą API).

### 3. Wykorzystywane typy
- **Form Request:** `UploadPhotoRequest` (pobiera event_id z trasy, token z nagłówka, dane formularza, deleguje do serwisu).
- **DTO wejściowe:** `UploadPhotoData` (eventId, sessionToken, UploadedFile, ?Carbon takenAt, RequestContext).
- **Serwis domenowy:** `PhotoUploadService` (lub istniejący odpowiednik) z metodą `upload(UploadPhotoData): PhotoUploadResult`.
- **Wartości pomocnicze:**
  - `UploadWindowChecker` (sprawdza okno czasowe).
  - `QuotaInspector` (zwraca pozostały limit, uwzględnia soft delete).
- **DTO wyjściowe:** `PhotoUploadResult` (Photo model + Quota summary) oraz `UploadedPhotoResource` (transformer JSON).

### 4. Szczegóły odpowiedzi
- **Kod powodzenia:** `201 Created` (zawiera nowy zasób).
- **Payload JSON:**
  - `photo.id` (UUID), `photo.filename` (UUID v6), `photo.uploaded_at` (ISO 8601), `photo.taken_at` (ISO 8601 lub null).
  - `quota.remaining` (int), `quota.limit` (int = 15).
- **Nagłówki:** `Content-Type: application/json; charset=utf-8`.
- **Lokalizacja komunikatów:** Klucze `message` / `code` wg retro-stylu, użycie plików tłumaczeń.

### 5. Przepływ danych
- Routing → FormRequest (`UploadPhotoRequest`) → walidacja → resolve `PhotoUploadService`.
- Serwis ładuje `Event` + relację `guests` dla tokenu (z eager load zdjęć aktywnych), porównuje hash tokenu.
- Sprawdza status okna czasowego (helper + `Carbon::now()`), weryfikuje quota.
- Generuje UUIDv6 dla nazwy pliku, zapisuje w storage (configurable disk, np. `public` lub S3) z użyciem `storeAs`.
- Tworzy `Photo` (Eloquent) z `event_id`, `guest_id`, `filename`, `uploaded_at`, `taken_at`.
- Serwis zwraca wynik → Kontroler buduje `UploadedPhotoResource` i zwraca JSON 201.
- Błędy walidacyjne obsługiwane w FormRequest (400); błędy domenowe rzucają wyjątki niestandardowe mapowane przez handler do 400/401/404.

### 6. Względy bezpieczeństwa
- Token sesyjny porównywany w czasie stałym (np. `hash_equals` na hashach przechowywanych w DB).
- CORS ograniczony do domen aplikacji gości, wymuszone HTTPS.
- Limit rozmiaru uploadu (php.ini `upload_max_filesize`, `post_max_size` ustawione ≥10 MB, ale spójne).
- Weryfikacja MIME przy użyciu `mimes` + `image` + dodatkowy check biblioteki (exif-imagetype) dla pewności.
- Generowanie nazw plików losowych oraz przechowywanie w katalogu niedostępnym publicznie bezpośrednio (dostarczanie via CDN poza scope).
- Logowanie IP + hash tokenu przy powodzeniu i błędach (monolog channel `security`).

### 7. Obsługa błędów
- **400 Bad Request:**
  - Walidacja wejścia (brak pliku, zły MIME, za duży rozmiar, niewłaściwe `taken_at`).
  - Naruszenia reguł domenowych (quota_exceeded, window_closed, format_invalid) – zwracane z {`code`, `message`}.
- **401 Unauthorized:**
  - Brak nagłówka lub token niepoprawny/niedopasowany do eventu.
- **404 Not Found:**
  - `event_id` nieistniejący, brak przypisanego gościa (opcjonalnie maskowane jako 401 jeśli wolisz).
- **500 Internal Server Error:**
  - Nieoczekiwane wyjątki (np. problemy ze storage). Handler loguje i zwraca generyczną odpowiedź.
- Uwaga: oryginalne przypadki 403/415/429 w specyfikacji są mapowane do 400 z zachowaniem `code` domenowego; decyzja wymaga potwierdzenia właściciela produktu.
- Błędy logowane w centralnym logu (Monolog) + opcjonalna tabela audytowa, jeśli istnieje (`upload_failures`).

### 8. Rozważania dotyczące wydajności
- Używać eager loadingu i agregacji (COUNT aktywnych zdjęć) jednym zapytaniem (`withCount` z warunkiem `whereNull('deleted_at')`).
- Utrzymywać przetwarzanie obrazów na minimum (zakładamy preprocessing po stronie klienta); walidować w pamięci bez dodatkowej obróbki.
- Dodać indeksy na `guests.session_id` (hash) oraz `photos.guest_id / deleted_at`.
- Wykorzystać `Storage::putFileAs` z streamem, aby ograniczyć zużycie pamięci; rozważyć kolejkę do dalszego przetwarzania poza zakresem.
- Rate limiting oraz ograniczenie wielkości pliku redukują ryzyko DoS.

### 9. Etapy wdrożenia
1. Potwierdź z właścicielem produktu strategię kodów statusu (czy można przywrócić 403/415/429 lub zaakceptować mapowanie na 400) i zaktualizuj dokumentację.
2. Dodaj trasę w `routes/api.php` z odpowiednim middleware (throttle, cors, https enforcement).
3. Utwórz `UploadPhotoRequest` z pełnymi regułami walidacji i autoryzacją eventu + sesji.
4. Zaimplementuj serwis `PhotoUploadService` (lub rozbuduj istniejący) z logiką: weryfikacja eventu, sesji, okna, limitu, generowanie UUIDv6, zapis pliku, utworzenie `Photo`.
5. Zaimplementuj klasę zasobu `UploadedPhotoResource` / transformer do odpowiedzi 201.
6. Skonfiguruj logowanie i audyt (Monolog channel, ewentualna tabela) dla prób udanych/nieudanych.
7. Dodaj testy feature (Pest/PHPUnit): sukces uploadu, błędny token (401), quota exceeded (400), window closed (400), invalid mime (400), event nie istnieje (404).
8. Ustal limity w `Route::middleware('throttle:30,1')` lub dedykowane `RateLimiter::for` i zaktualizuj konfigurację.
9. Zweryfikuj konfigurację storage (env, dysk, prawa) oraz php.ini dla uploadów.
10. Przeprowadź manualne testy integracyjne z klientem Vue/Nuxt i monitoruj logi bezpieczeństwa.



