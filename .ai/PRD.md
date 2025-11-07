# Product Requirements Document (PRD)
## klisza.app - Aplikacja do zbierania zdjęć z wesel

---

## 1. Przegląd produktu

### 1.1 Cel produktu
klisza.app to aplikacja webowa pozwalająca parom młodym (organizatorom wesel) zbierać zdjęcia od gości w łatwy i nostalgiczny sposób. Aplikacja ma zachęcać do dokumentowania wydarzeń weselnych, tworząc wspólną galerię zdjęć jako pamiątkę z uroczystości.

### 1.2 Problem użytkownika
Pary młode chcą mieć fajne, autentyczne zdjęcia z wesela wykonane przez gości, które uchwycą różnorodne perspektywy i momenty wydarzenia. Tradycyjne metody zbierania zdjęć (social media, WhatsApp, email) są rozproszone i trudne w zarządzaniu.

### 1.3 Rozwiązanie
Aplikacja webowa z retro designem przypominającym jednorazowy aparat, która umożliwia gościom przesyłanie zdjęć przez przeglądarkę mobilną bez instalacji, a organizatorowi zarządzanie wszystkimi zdjęciami w jednym miejscu.

### 1.4 Target grupa
- **Główni użytkownicy (organizatorzy):** Pary młode planujące wesela w Polsce
- **Drugorzędni użytkownicy (goście):** Uczestnicy wesel z urządzeniami mobilnymi

---

## 2. Zakres MVP

### 2.1 Funkcjonalności w zakresie MVP

#### 2.1.1 Panel zarządzania dla organizatora (Laravel + Filament)
- **Autoryzacja:**
  - Rejestracja i logowanie przez email i hasło (domyślna autoryzacja Laravel/Filament)
  - Brak weryfikacji email w MVP
  - Zabezpieczenie silnym hasłem

- **Zarządzanie wydarzeniami:**
  - Tworzenie wydarzenia z wymaganymi polami: nazwa i data
  - Edycja nazwy i daty wydarzenia przed publikacją galerii
  - Generowanie unikalnego linku dla gości
  - Usuwanie wydarzenia

- **Przeglądanie zdjęć:**
  - Wyświetlanie zdjęć w grid (domyślna opcja grid w Filament)
  - Widoczność imienia gościa przy każdym zdjęciu
  - Paginacja (20-50 zdjęć na stronę)
  - Sortowanie chronologiczne (od najnowszych)
  - Podgląd pełnego rozmiaru po kliknięciu
  - Możliwość usuwania zdjęć przed publikacją

- **Publikacja galerii:**
  - Przycisk "Opublikuj galerię" (maksymalnie 3 kliknięcia)
  - Generowanie linku do galerii dla gości
  - Blokada edycji wydarzenia po publikacji


#### 2.1.2 Aplikacja dla gości (tylko mobile web)
- **Design:**
  - Retro interface przypominający jednorazowy aparat fotograficzny
  - Stylizacja polaroid/vintage dla spójności wizualnej
  - Polski język interfejsu
  - Brak dźwięków w MVP

- **Dostęp do wydarzenia:**
  - Dostęp przez unikalny link bez rejestracji
  - Wymagane podanie imienia przed pierwszym uploadem zdjęcia
  - Gość zostaje zapisany w bazie danych i powiązany ze swoimi zdjęciami
  - Tracking sesji przez localStorage

- **Upload zdjęć:**
- Tylko upload gotowych zdjęć (brak bezpośredniego dostępu do kamery przez WebRTC)
- Uprawnienie do dostępu do plików
- Automatyczne przycinanie do aspect ratio 3:4 (centrowane)
- Kompresja do maksymalnej rozdzielczości 1920x2560 (JPEG 85% jakości)
  - Maksymalnie 15 zdjęć na uczestnika (limit per sesja/localStorage)
  - Walidacja po stronie klienta i serwera

- **Ograniczenia czasowe:**
  - Zdjęcia można przesyłać tylko w całej dobie wydarzenia + 12h przed i 12h po
  - Walidacja po stronie klienta i serwera (czas serwera jako autorytatywny)
  - Jasne komunikaty o statusie: "Wydarzenie jeszcze się nie rozpoczęło" lub "Wydarzenie już się zakończyło"


#### 2.1.3 Galeria dla gości (po publikacji)
- Dostęp przez unikalny link udostępniony przez organizatora
- Wyświetlanie tylko zdjęć zatwierdzonych przez organizatora
- Responsywny grid zdjęć
- Pełnoekranowy podgląd

### 2.2 Funkcjonalności POZA zakresem MVP
- Weryfikacja email przy rejestracji
- Funkcja "zapomniane hasło"
- Monitoring wykorzystania miejsca na serwerze
- Analityki i metryki zaangażowania gości
- Testy obciążeniowe dla wielu użytkowników
- Konfigurowalny limit zdjęć przez organizatora
- Dodatkowe zabezpieczenia (CAPTCHA, 2FA)
- Wsparcie techniczne i moderacja treści
- Wykrywanie duplikatów zdjęć
- Miniaturki zdjęć (optymalizacja)
- Personalizacja designu galerii
- Edycja zdjęć, filtry
- Tagowanie, komentowanie zdjęć
- Integracje z kalendarzami lub platformami ślubny
- Monetyzacja
- Integracja z social media
- Powiadomienia push
- Geolokalizacja

---

## 3. Wymagania techniczne

### 3.1 Stack technologiczny
- **Backend:** Laravel (PHP)
- **Panel administracyjny:** Filament
- **Frontend dla gości:** Vue.js SPA (Single Page Application)
- **Baza danych:** MySQL/PostgreSQL (standardowa dla Laravel)
- **Storage:** Własny serwer (system plików)

### 3.2 Architektura przechowywania

#### Struktura folderów
```
/storage/events/
  /{event_id}/
    /photos/
      /{uuid}.jpg
```

#### Nazewnictwo plików
- Format: UUID (generowany dla każdego zdjęcia)
- Losowe nazwy dla bezpieczeństwa (utrudnienie odgadnięcia URL)
- Rozszerzenie: `.jpg`

#### Zabezpieczenie plików
- Bezpośredni dostęp do plików przez URL
- Brak dodatkowej autoryzacji dla plików

### 3.3 Przetwarzanie zdjęć

#### Pipeline przetwarzania
1. Walidacja formatu i rozmiaru pliku
 2. Przycinanie do aspect ratio 3:4 (centrowane) - po stronie klienta
 3. Kompresja do maksymalnej rozdzielczości 1920x2560 - po stronie klienta
4. Jakość JPEG: 85% - po stronie klienta
5. Zapisanie z UUID jako nazwą pliku

#### Parametry kompresji (po stronie klienta)
- Format wyjściowy: JPEG
- Jakość: 85%
- Maksymalna rozdzielczość: 1920x2560 (3:4)
- Automatyczne skalowanie w dół (bez powiększania małych zdjęć)

### 3.4 Zarządzanie sesją użytkowników

#### Dla gości (localStorage + baza danych)
- Tracking liczby przesłanych zdjęć per sesja (localStorage)
- Zapisanie gościa w bazie z imieniem przy pierwszym uploadzie
- Powiązanie zdjęć z gościem w bazie danych
- Limit 15 zdjęć na uczestnika
- Brak fallback dla trybu prywatnego przeglądarki w MVP

#### Dla organizatorów
- Standardowa sesja Laravel
- Autoryzacja przez Filament

### 3.5 Walidacje

#### Po stronie klienta
- Format pliku (JPEG, PNG)
- Limit 15 zdjęć per sesja
- Okno czasowe wydarzenia (cała doba + 12h przed i po)
- Natychmiastowy feedback dla użytkownika

#### Po stronie serwera
- Wszystkie walidacje z klienta (dla bezpieczeństwa)
- Weryfikacja UUID wydarzenia
- Czas serwera jako autorytatywny

### 3.6 Wymagania wydajnościowe
- Kompresja zdjęć przed wysłaniem (po stronie klienta)
- Paginacja dla dużych galerii
- Protected routes z cache

---

## 4. User Stories i ścieżki użytkownika

### 4.1 Organizator (para młoda)

#### US1: Tworzenie wydarzenia
**Jako** para młoda  
**Chcę** stworzyć wydarzenie weselne  
**Aby** móc zbierać zdjęcia od gości

**Kroki:**
1. Rejestracja/logowanie do panelu Filament
2. Kliknięcie "Utwórz wydarzenie"
3. Wypełnienie nazwy i daty wesela
4. Zapisanie wydarzenia
5. Skopiowanie unikalnego linku dla gości

**Kryteria akceptacji:**
- Nazwa i data są wymagane
- Unikalny link jest generowany automatycznie
- Link można skopiować jednym kliknięciem

#### US2: Moderacja zdjęć
**Jako** para młoda  
**Chcę** przeglądać i usuwać zdjęcia  
**Aby** galeria zawierała tylko odpowiednie treści

**Kroki:**
1. Zalogowanie do panelu
2. Wybranie wydarzenia
3. Przeglądanie zdjęć w grid
4. Kliknięcie zdjęcia do podglądu
5. Usunięcie nieodpowiednich zdjęć

**Kryteria akceptacji:**
- Zdjęcia są sortowane od najnowszych
- Imię gościa jest widoczne przy każdym zdjęciu
- Grid z paginacją (20-50 zdjęć/strona)
- Podgląd pełnego rozmiaru działa
- Usunięcie jest natychmiastowe

#### US3: Publikacja galerii
**Jako** para młoda  
**Chcę** opublikować galerię po weselu  
**Aby** goście mogli zobaczyć wszystkie zdjęcia

**Kroki:**
1. Zalogowanie do panelu
2. Wybranie wydarzenia
3. Sprawdzenie wszystkich zdjęć
4. Kliknięcie "Opublikuj galerię"
5. Udostępnienie linku gościom

**Kryteria akceptacji:**
- Publikacja w maksymalnie 3 kliknięciach
- Link do galerii jest unikalny
- Po publikacji nie można edytować nazwy/daty wydarzenia

### 4.2 Gość weselny

#### US4: Przesłanie zdjęcia
**Jako** gość weselny  
**Chcę** przesłać zdjęcia z wesela  
**Aby** podzielić się wspomnieniami z parą młodą

**Kroki:**
1. Otrzymanie linku od pary młodej
2. Otwarcie linku w przeglądarce mobilnej (Vue SPA)
3. Kliknięcie przycisku upload/wybór zdjęcia
4. Przy pierwszym zdjęciu: podanie swojego imienia w formularzu
5. Automatyczne przetworzenie po stronie klienta (crop 3:4, kompresja)
5. Automatyczne przetworzenie po stronie klienta (crop 3:4, kompresja)
6. Przesłanie przetworzonego zdjęcia do serwera wraz z danymi gościa
7. Otrzymanie potwierdzenia

**Kryteria akceptacji:**
- Interface w stylu retro aparatu (Vue SPA)
- Komunikaty w języku polskim
- Formularz z imieniem pojawia się przed pierwszym zdjęciem
- Imię gościa jest zapisywane w bazie danych
- Zdjęcia są powiązane z gościem w bazie
- Przesłanie działa tylko w całej dobie wydarzenia + 12h przed i po
- Limit 15 zdjęć per sesja
- Feedback o powodzeniu

#### US5: Próba przesłania poza oknem czasowym
**Jako** gość weselny  
**Chcę** zobaczyć jasny komunikat  
**Gdy** próbuję przesłać zdjęcie przed/po wydarzeniu

**Kroki:**
1. Otwarcie linku przed wydarzeniem
2. Próba przesłania zdjęcia
3. Zobaczenie komunikatu: "Wydarzenie jeszcze się nie rozpoczęło"

**Kryteria akceptacji:**
- Komunikat w stylu retro
- Jasne wyjaśnienie przyczyny
- Brak możliwości obejścia ograniczenia

#### US6: Osiągnięcie limitu zdjęć
**Jako** gość weselny  
**Chcę** zobaczyć komunikat o limicie  
**Gdy** przesłałem już 15 zdjęć

**Kroki:**
1. Przesłanie 15 zdjęcia
2. Próba przesłania 16 zdjęcia
3. Zobaczenie komunikatu o osiągnięciu limitu

**Kryteria akceptacji:**
- Komunikat jest przyjazny i wyjaśnia limit
- Licznik pokazuje ile zdjęć zostało przesłanych
- Brak możliwości obejścia limitu

#### US7: Przeglądanie galerii
**Jako** gość weselny  
**Chcę** zobaczyć wszystkie zdjęcia z wesela  
**Gdy** para młoda opublikuje galerię

**Kroki:**
1. Otrzymanie linku do galerii od pary młodej
2. Otwarcie linku w przeglądarce
3. Przeglądanie zdjęć w grid
4. Kliknięcie na zdjęcie dla pełnego widoku

**Kryteria akceptacji:**
- Responsywny grid na mobile
- Pełnoekranowy podgląd działa
- Tylko zatwierdzone zdjęcia są widoczne

---

## 5. Design i UX

### 5.1 Design dla gości (retro aparat)

#### Inspiracje wizualne
- Jednorazowe aparaty fotograficzne (Fujifilm, Kodak)
- Ramki Polaroid
- Vintage estetyka lat 90.
- Ciepłe, nostalgiczne kolory

#### Elementy interfejsu
- Stylizowany przycisk "migawki" do uploadowania
- Ramka w stylu polaroid dla podglądu
- Czcionka maszynowa/retro dla komunikatów
- Minimalistyczny layout (jeden główny przycisk)

#### Animacje
- Migotanie "flesza" po przesłaniu zdjęcia
- Płynne przejścia między stanami
- Loading animation w stylu retro

### 5.2 Design panelu zarządzania

#### Styl
- Domyślny design Filament (nowoczesny, funkcjonalny)
- Focus na efektywność i przejrzystość
- Standardowa paleta Filament


## 7. Wymagania niefunkcjonalne

### 7.1 Bezpieczeństwo
- Silne hasła dla organizatorów (walidacja Laravel)
- Losowe nazwy plików (UUID)
- HTTPS dla całej aplikacji
- Walidacja po stronie serwera (defense in depth)

### 7.2 Wydajność
- Kompresja zdjęć po stronie klienta
- Paginacja dla dużych zestawów danych
- Brak miniaturek w MVP (trade-off)

### 7.3 Kompatybilność
- Mobile-first dla aplikacji gości
- Responsywny design dla panelu zarządzania
- Wsparcie dla nowoczesnych przeglądarek (Chrome 90+, Safari 14+)
- Brak wsparcia dla starszych przeglądarek w MVP

### 7.4 Użyteczność
- Intuicyjny interfejs (maksymalnie 3 kliknięcia dla kluczowych akcji)
- Spójność wizualna (retro dla gości, funkcjonalny dla organizatorów)

---

## Appendix: Endpointy API

### Dla gości (public)
```
GET  /event/{event_id}              → Strona uploadu dla gości (Vue SPA)
POST /event/{event_id}/guest        → Utworzenie gościa po imieniu i pobranie session_id
POST /event/{event_id}/upload       → Upload zdjęcia (walidacja: limit, czas, guest_id)
GET  /gallery/{event_id}            → Galeria opublikowanych zdjęć
```
