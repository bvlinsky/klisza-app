# klisza.app

klisza.app to aplikacja webowa pozwalająca parom młodym zbierać zdjęcia od gości weselnych w łatwy i nostalgiczny sposób. Aplikacja wykorzystuje retro design inspirowany jednorazowymi aparatami fotograficznymi, tworząc wspólną galerię zdjęć jako pamiątkę z uroczystości.

## Architektura

Projekt składa się z dwóch głównych komponentów:

### Panel zarządzania (admin/)
- **Technologia:** Laravel + Filament
- **Użytkownicy:** Organizatorzy wesel (pary młode)
- **Funkcjonalności:**
  - Tworzenie i zarządzanie wydarzeniami weselnymi
  - Moderacja przesłanych zdjęć
  - Publikacja galerii dla gości
  - API dla aplikacji gości (endpointy do uploadu zdjęć i zarządzania sesjami)

### Aplikacja dla gości (guest/)
- **Technologia:** Vue.js SPA (Single Page Application)
- **Użytkownicy:** Uczestnicy wesel
- **Funkcjonalności:**
  - Przesyłanie zdjęć przez przeglądarkę mobilną (bez instalacji aplikacji)
  - Retro interface przypominający jednorazowy aparat
  - Automatyczne przetwarzanie zdjęć (kadrowanie 3:4, kompresja)
  - Limit 15 zdjęć na uczestnika w oknie czasowym wydarzenia ±12h

## Kluczowe cechy

- **Retro design:** Nostalgiczny interfejs inspirowany klasycznymi aparatami
- **Mobile-first:** Optymalizacja dla urządzeń mobilnych
- **Bezpieczeństwo:** Walidacja po stronie klienta i serwera
- **Wydajność:** Kompresja zdjęć przed przesłaniem
- **Prostota:** Maksymalnie 3 kliknięcia dla kluczowych akcji

## Docelowi użytkownicy

- **Główni:** Pary młode planujące wesela w Polsce
- **Drugorzędni:** Uczestnicy wesel z urządzeniami mobilnymi
