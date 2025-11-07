# Architektura UI dla klisza.app

## Konfiguracja środowiska

### API URL
URL API można skonfigurować przez zmienną środowiskową:
```bash
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

Domyślnie ustawiony na `http://localhost:8000`. Dla produkcji użyj swojego rzeczywistego domainu API.

## 1. Przegląd struktury UI

Architektura UI opiera się na dwóch głównych aplikacjach: aplikacji dla gości (wygląda jak aplikacja do robienia zdjęć na telefony, ale w retro stylu - jeden widok SPA w Nuxt/Vue z modalami powitalnymi i obszarem kamery) oraz panelu administracyjnego (Filament dla organizatorów z galerią). Struktura jest mobile-first, z naciskiem na płynność UX, bezpieczeństwo (Sanctum tokens w localStorage) i integrację z API. Używa Nuxt UI, Tailwind, Pinia dla stanu oraz open-api-fetch dla wywołań API, z toastami dla błędów.

## 2. Lista widoków

### Główny widok aplikacji gości
- **Ścieżka widoku**: /event/{event_id}
- **Główny cel**: Wygląda jak aplikacja do robienia zdjęć na telefony, ale w retro stylu - umożliwia gościom robienie zdjęć, które automatycznie się przesyłają.
- **Kluczowe informacje do wyświetlenia**: Przy pierwszym wejściu - modal z informacjami o weselu i działaniu aplikacji; nazwa i data wydarzenia; po podaniu imienia - interfejs kamery z przyciskiem migawki; komunikaty błędów (w toastach).
- **Kluczowe komponenty widoku**: Modal powitalny z info o aplikacji, modal imienia do utworzenia tokenu, retro interfejs kamery (jak w aparacie telefonu), przycisk migawki, ramka polaroid, toast dla błędów.
- **UX, dostępność i względy bezpieczeństwa**: Pierwszy raz na linku: modal powitalny → modal imię → interfejs kamery; dostępność poprzez ARIA w komponentach; bezpieczeństwo poprzez autoryzację Sanctum z tokenem w localStorage i obsługą błędów 401 (reset stanu).

### Panel administracyjny (Filament)
- **Ścieżka widoku**: /admin (z podstronami dla wydarzeń)
- **Główny cel**: Zarządzanie wydarzeniami i moderacja zdjęć przez organizatorów.
- **Kluczowe informacje do wyświetlenia**: Lista wydarzeń z nazwą i datą, grid zdjęć z imieniem gościa, status publikacji galerii.
- **Kluczowe komponenty widoku**: Tabela wydarzeń, grid zdjęć z paginacją, przyciski usuwania/publikacji.
- **UX, dostępność i względy bezpieczeństwa**: Funkcjonalny design Filament z paginacją i filtrowaniem; dostępność standardowa Filament; bezpieczeństwo poprzez autoryzację Laravel z walidacją hasła.

## 3. Mapa podróży użytkownika

**Dla gości**: Otwórz link z event_id po raz pierwszy → Wyświetl modal powitalny z informacjami o weselu i działaniu aplikacji → Podaj imię w modalu (utwórz token przez POST/auth) → Modale znikają i przejdź do głównego widoku kamery (wygląda jak aplikacja do zdjęć na telefon, ale retro) → Zrób zdjęcie przyciskiem migawki → Kompresja klienta-side → Automatyczny upload w tle (POST/photos) → Toast potwierdzenia lub błędu → Kontynuuj robienie zdjęć do limitu (15 zdjęć).

**Dla organizatorów**: Zaloguj do panelu Filament → Wybierz wydarzenie z listy → Przeglądaj grid zdjęć (sortowane chronologicznie) → Usuń nieodpowiednie zdjęcia → Opublikuj galerię (maksymalnie 3 kliknięcia).

Kluczowe interakcje: Płynne przełączanie modali przez stan Pinia; upload w tle; błędy obsługiwane toastami bez przerywania przepływu.

## 4. Układ i struktura nawigacji

W aplikacji gości: Brak tradycyjnego routingu – jeden widok z przełączaniem stanów (pierwsze wejście: modal powitalny z info o aplikacji → modal imię do utworzenia tokenu → główny widok kamery jak w aplikacji do zdjęć na telefon, ale retro). Modale znikają po autoryzacji i zostaje czysty interfejs kamery.

W panelu admina: Standardowa nawigacja Filament (menu boczne, breadcrumbs) między dashboardem a szczegółami wydarzeń. Bez dodatkowych routów poza standardowymi Filament.

## 5. Kluczowe komponenty

- **Modal (Nuxt UI)**: Modal powitalny z informacjami o weselu i aplikacji (pierwsze wejście), modal imię do utworzenia tokenu; responsywny, z przyciskami pełnej szerokości na mobile.
- **Toast (Nuxt UI)**: Globalny dla błędów API w obu aplikacjach; krótkie komunikaty polskie na dole ekranu, z mapowaniem kodów błędów.
- **Retro interfejs kamery (custom)**: Wygląda jak aplikacja do robienia zdjęć na telefon, ale w retro stylu - przycisk migawki, ramka polaroid, ciepłe kolory dla nostalgii.
- **Grid/table (Filament)**: Dla galerii w adminie i potencjalnego podglądu w gościach; paginacja 20-50 elementów.
