# iHumBak - WooCommerce Order Edit Logs
## Plan Pracy - Etapy Realizacji Projektu

**Wersja dokumentu:** 1.0  
**Data utworzenia:** 2025-10-10  
**Autor:** Michał Staniećko

---

## 📋 Spis Treści

1. [Etap 0: Przygotowanie i Planowanie](#etap-0-przygotowanie-i-planowanie)
2. [Etap 1: Podstawowa Infrastruktura](#etap-1-podstawowa-infrastruktura)
3. [Etap 2: System Logowania - Fundament](#etap-2-system-logowania---fundament)
4. [Etap 3: Hooki i Śledzenie Zmian](#etap-3-hooki-i-śledzenie-zmian)
5. [Etap 4: Interfejs Administratora](#etap-4-interfejs-administratora)
6. [Etap 5: Eksport Danych](#etap-5-eksport-danych)
7. [Etap 6: Bezpieczeństwo i Uprawnienia](#etap-6-bezpieczeństwo-i-uprawnienia)
8. [Etap 7: Optymalizacja i Wydajność](#etap-7-optymalizacja-i-wydajność)
9. [Etap 8: Testowanie i QA](#etap-8-testowanie-i-qa)
10. [Etap 9: Dokumentacja i Release](#etap-9-dokumentacja-i-release)

---

## Etap 0: Przygotowanie i Planowanie

**Cel:** Przygotowanie środowiska pracy i infrastruktury projektu.  
**Szacowany czas:** 1-2 dni  
**Status:** ✅ Ukończony

### Zadania:

- [x] Analiza wymagań z README.md i SPECIFICATION.md
- [x] Utworzenie planu pracy (WORKING_PLAN.md)
- [x] Konfiguracja środowiska deweloperskiego
  - [x] Konfiguracja PHP 7.4/8.0+ (wymagane w composer.json)
  - [x] Instalacja narzędzi deweloperskich (Composer)
  - ℹ️ Lokalna instalacja WordPress i WooCommerce - do wykonania przez dewelopera w środowisku lokalnym
- [x] Inicjalizacja struktury katalogów projektu
- [x] Konfiguracja Git i GitHub
  - [x] .gitignore
  - [x] README.md (już istnieje)
  - [x] CHANGELOG.md
  - [x] .editorconfig
- [x] Konfiguracja narzędzi do testowania
  - [x] PHPUnit (phpunit.xml.dist)
  - [x] PHP_CodeSniffer (phpcs.xml + WordPress Coding Standards)
  - [x] PHPStan (phpstan.neon + analiza statyczna)
  - [x] Composer setup (composer.json)

### Deliverables:
- ✅ WORKING_PLAN.md
- ✅ Podstawowa struktura katalogów (includes/, assets/, languages/, tests/)
- ✅ CHANGELOG.md
- ✅ Konfiguracja narzędzi deweloperskich (composer.json, phpunit.xml.dist, phpcs.xml, phpstan.neon)
- ✅ Pliki konfiguracyjne Git (.gitignore, .editorconfig)

---

## Etap 1: Podstawowa Infrastruktura

**Cel:** Utworzenie podstawowej struktury pluginu, głównego pliku i systemu ładowania klas.  
**Szacowany czas:** 2-3 dni  
**Status:** ✅ Ukończony

### Zadania:

#### 1.1. Główny Plik Pluginu
- [x] Utworzenie `ihumbak-woo-order-edit-logs.php`
  - [x] Nagłówek pluginu z metadanymi
  - [x] Sprawdzenie wymagań (WordPress, WooCommerce, PHP)
  - [x] Inicjalizacja pluginu
  - [x] Hooki aktywacji/deaktywacji
- [x] Utworzenie `uninstall.php`
  - [x] Czyszczenie opcji
  - [x] Opcjonalne usuwanie tabel (z pytaniem)

#### 1.2. Autoloader i Struktura Klas
- [x] Implementacja PSR-4 autoloadera
- [x] Utworzenie struktury katalogów zgodnie ze SPECIFICATION.md
  ```
  includes/
  ├── class-order-logger.php
  ├── class-log-database.php
  ├── class-log-tracker.php
  ├── class-log-formatter.php
  ├── class-log-exporter.php
  ├── class-hpos-compatibility.php
  ├── admin/
  │   ├── class-admin-interface.php
  │   ├── class-log-viewer.php
  │   ├── class-settings.php
  │   └── views/
  └── hooks/
      ├── order-hooks.php
      ├── product-hooks.php
      ├── address-hooks.php
      └── payment-hooks.php
  ```

#### 1.3. Baza Danych
- [x] Utworzenie klasy `class-log-database.php`
- [x] Implementacja metody tworzenia tabeli `wp_ihumbak_order_logs`
- [x] SQL zgodny ze SPECIFICATION.md (indeksy, typy kolumn)
- [x] System wersjonowania schematu bazy
- [x] Hook aktywacji - tworzenie tabel

#### 1.4. HPOS Compatibility Layer
- [x] Utworzenie klasy `class-hpos-compatibility.php`
- [x] Implementacja wykrywania trybu storage (CPT vs HPOS)
- [x] Uniwersalne metody dostępu do zamówień
- [x] Abstrakcja różnic między trybami
- [x] Pomocnicze metody do porównywania stanów zamówień

### Deliverables:
- [x] Funkcjonalny szkielet pluginu
- [x] Tabela w bazie danych
- [x] Warstwa kompatybilności HPOS
- [x] System autoloadingu
- [x] Podstawowe testy jednostkowe dla struktury

### Testy:
- [x] Aktywacja/deaktywacja pluginu bez błędów
- [x] Tworzenie tabeli w bazie danych
- [x] Wykrywanie trybu HPOS
- [x] Deinstalacja z usuwaniem/zachowaniem danych

---

## Etap 2: System Logowania - Fundament

**Cel:** Implementacja podstawowego systemu logowania zmian.  
**Szacowany czas:** 4-5 dni  
**Status:** ✅ Ukończony

### Zadania:

#### 2.1. Klasa Order Logger
- [x] Implementacja `class-order-logger.php`
- [x] Metoda główna: `log_change($order_id, $action_type, $field_name, $old_value, $new_value, $additional_data)`
- [x] Pobieranie informacji o użytkowniku (ID, nazwa, rola)
- [x] Pobieranie IP i User Agent
- [x] Walidacja danych wejściowych
- [x] Zapisywanie do bazy danych

#### 2.2. Klasa Log Tracker
- [x] Implementacja `class-log-tracker.php`
- [x] System snapshottingu (przechowywanie stanu przed zmianą)
- [x] Metody porównywania wartości:
  - [x] `compare_scalar()` - dla prostych wartości
  - [x] `compare_array()` - dla tablic/obiektów
  - [x] `compare_addresses()` - specjalna dla adresów
- [x] Wykrywanie różnic między starym a nowym stanem

#### 2.3. Klasa Log Formatter
- [x] Implementacja `class-log-formatter.php`
- [x] Formatowanie wartości do zapisu:
  - [x] Konwersja obiektów do JSON
  - [x] Formatowanie cen
  - [x] Formatowanie dat
- [x] Formatowanie do wyświetlania:
  - [x] Human-readable labels dla action_type
  - [x] Tłumaczenia nazw pól
  - [x] Formatowanie JSON do czytelnej formy

#### 2.4. Typy Akcji
- [x] Definicja wszystkich typów akcji (zgodnie z SPECIFICATION.md sekcja 2.3)
- [x] Utworzenie systemu rejestracji typów akcji
- [x] Możliwość filtrowania typów akcji przez deweloperów

### Deliverables:
- [x] Działający system logowania
- [x] Zapis do bazy danych
- [x] System porównywania zmian
- [x] Formatowanie danych
- [x] Testy jednostkowe dla każdej klasy

### Testy:
- [x] Zapis pojedynczego logu
- [x] Poprawność formatowania wartości
- [x] Wykrywanie różnic między wartościami
- [x] Obsługa NULL i pustych wartości
- [x] Obsługa dużych obiektów JSON

---

## Etap 3: Hooki i Śledzenie Zmian

**Cel:** Implementacja hooków WooCommerce do automatycznego przechwytywania zmian.  
**Szacowany czas:** 6-8 dni  
**Status:** ✅ Ukończony

### Zadania:

#### 3.1. Hooki Zamówień (order-hooks.php)
- [x] `woocommerce_new_order` - tworzenie zamówienia
- [x] `woocommerce_update_order` - aktualizacja zamówienia
- [x] `woocommerce_order_status_changed` - zmiana statusu
- [x] System snapshottingu przed zapisem:
  - [x] Hook: `woocommerce_before_order_object_save`
  - [x] Przechowanie w transient: `ihumbak_order_snapshot_{$order_id}`
- [x] Porównanie po zapisie i logowanie różnic
- [x] Obsługa `$order->get_changes()` (HPOS-compatible)

#### 3.2. Hooki Produktów (product-hooks.php)
- [x] `woocommerce_new_order_item` - dodanie produktu
- [x] `woocommerce_update_order_item` - aktualizacja produktu
- [x] `woocommerce_before_delete_order_item` - snapshot przed usunięciem
- [x] `woocommerce_delete_order_item` - usunięcie produktu
- [x] Logowanie zmian:
  - [x] Ilość produktu
  - [x] Cena produktu
  - [x] Podatek
  - [x] Metadane produktu (poprzez changes)

#### 3.3. Hooki Adresów (address-hooks.php)
- [x] Śledzenie zmian przez `woocommerce_before_order_object_save`
- [x] Porównanie adresów billing i shipping (przez order-hooks.php)
- [x] Logowanie pól adresowych:
  - [x] First name, Last name
  - [x] Company
  - [x] Address 1, Address 2
  - [x] City, Postcode
  - [x] Country, State
  - [x] Email, Phone

#### 3.4. Hooki Płatności i Wysyłki (payment-hooks.php)
- [x] Zmiana metody płatności (poprzez snapshot comparison)
- [x] Zmiana metody wysyłki
- [x] Zmiana kosztów przesyłki
- [x] Kupony:
  - [x] `woocommerce_applied_coupon` - dodanie kuponu
  - [x] `woocommerce_removed_coupon` - usunięcie kuponu
- [x] Fees (opłaty dodatkowe)
- [x] Zwroty:
  - [x] `woocommerce_order_refunded`

#### 3.5. Hooki Notatek
- [x] `woocommerce_new_order_note` - nowa notatka
- [x] `woocommerce_delete_order_note` - usunięcie notatki
- [x] Rozróżnienie: prywatna vs dla klienta

#### 3.6. Hooki Metadanych (HPOS-compatible)
- [x] Obsługa przez snapshot comparison w order-hooks.php
- [x] Kompatybilność z HPOS poprzez HPOS_Compatibility layer

#### 3.7. Integracja z HPOS Compatibility Layer
- [x] Wszystkie hooki używają warstwy HPOS
- [x] Wykorzystanie woocommerce_before/after_order_object_save (HPOS-compatible)

### Deliverables:
- [x] Komplet plików hooków
- [x] Automatyczne logowanie wszystkich typów zmian
- [x] Pełna kompatybilność HPOS
- [x] Testy jednostkowe dla struktury hooków

### Testy:
- [x] Test struktury plików hooków
- [x] Test funkcji inicjalizacyjnych
- [x] Test integracji z Order_Logger
- [x] Test użycia Log_Tracker
- [x] Test użycia HPOS_Compatibility
- [x] Test rejestracji WooCommerce hooks

---

## Etap 4: Interfejs Administratora

**Cel:** Stworzenie interfejsu użytkownika do przeglądania logów.  
**Szacowany czas:** 5-7 dni  
**Status:** ⚪ Oczekuje

### Zadania:

#### 4.1. Admin Interface (class-admin-interface.php)
- [ ] Rejestracja menu w WooCommerce
- [ ] Pozycja menu: "WooCommerce > Logi Zamówień"
- [ ] Enqueue CSS i JS
- [ ] Inicjalizacja pozostałych klas admin

#### 4.2. Log Viewer - Lista Logów (class-log-viewer.php)
- [ ] Wykorzystanie WP_List_Table
- [ ] Kolumny:
  - [ ] ID Logu
  - [ ] ID Zamówienia (z linkiem)
  - [ ] Data/Czas
  - [ ] Użytkownik
  - [ ] Typ Akcji
  - [ ] Pole
  - [ ] Wartość Poprzednia
  - [ ] Wartość Nowa
  - [ ] IP
  - [ ] Akcje (Szczegóły/Usuń)
- [ ] Sortowanie po kolumnach
- [ ] Paginacja
- [ ] Bulk actions (masowe usuwanie)

#### 4.3. Filtrowanie i Wyszukiwanie
- [ ] Filtry:
  - [ ] Zakres dat (datepicker)
  - [ ] Typ akcji (dropdown)
  - [ ] Użytkownik (dropdown)
  - [ ] ID zamówienia (pole tekstowe)
  - [ ] Status zamówienia
- [ ] Wyszukiwanie pełnotekstowe
- [ ] AJAX dla dynamicznych filtrów

#### 4.4. Szczegóły Logu (log-details.php)
- [ ] Modal lub osobna strona
- [ ] Wszystkie dane logu
- [ ] Sformatowany JSON
- [ ] Link do zamówienia
- [ ] Link do profilu użytkownika

#### 4.5. Meta Box w Edycji Zamówienia
- [ ] Nowa sekcja "Historia Zmian"
- [ ] Lista zmian tylko dla danego zamówienia
- [ ] Sortowanie od najnowszych
- [ ] Paginacja (AJAX)
- [ ] Mini wersja szczegółów inline

#### 4.6. Ustawienia (class-settings.php)
- [ ] Integracja z WooCommerce Settings API
- [ ] Zakładka: "WooCommerce > Ustawienia > Logi Zamówień"
- [ ] Sekcje ustawień:
  
  **Ogólne:**
  - [ ] Włącz/Wyłącz logowanie (master switch)
  - [ ] Checkboxy dla typów akcji
  - [ ] Zapisywanie IP (tak/nie)
  - [ ] Zapisywanie User Agent (tak/nie)
  
  **Przechowywanie:**
  - [ ] Automatyczne czyszczenie (włącz/wyłącz)
  - [ ] Wiek logów do usunięcia (dni)
  - [ ] Częstotliwość czyszczenia (dropdown)
  
  **Uprawnienia:**
  - [ ] Role - przeglądanie logów (checkboxy)
  - [ ] Role - eksport logów
  - [ ] Role - usuwanie logów
  
  **Wydajność:**
  - [ ] Limit logów na stronę
  - [ ] Włącz cache (tak/nie)
  - [ ] Czas życia cache (sekundy)
  
  **Eksport:**
  - [ ] Format daty
  - [ ] Separator CSV
  - [ ] Kodowanie pliku

#### 4.7. Views (pliki PHP w includes/admin/views/)
- [ ] `log-list.php` - główna lista
- [ ] `log-details.php` - modal ze szczegółami
- [ ] `settings-page.php` - strona ustawień
- [ ] `order-meta-box.php` - meta box w zamówieniu

#### 4.8. Assets (CSS i JS)
- [ ] `admin-styles.css`:
  - [ ] Style dla listy logów
  - [ ] Style dla filtrów
  - [ ] Style dla modala
  - [ ] Style dla meta boxu
  - [ ] Responsive design
- [ ] `admin-scripts.js`:
  - [ ] AJAX dla filtrów
  - [ ] AJAX dla paginacji w meta boxu
  - [ ] Modal szczegółów logu
  - [ ] Datepicker dla filtrów dat
  - [ ] Potwierdzenia usuwania

### Deliverables:
- [ ] Pełny interfejs administratora
- [ ] Lista wszystkich logów z filtrami
- [ ] Meta box w zamówieniu
- [ ] Strona ustawień
- [ ] Responsywny design
- [ ] Testy UI

### Testy:
- [ ] Wyświetlanie listy logów
- [ ] Sortowanie po kolumnach
- [ ] Filtrowanie po różnych kryteriach
- [ ] Wyszukiwanie
- [ ] Paginacja
- [ ] Wyświetlanie szczegółów
- [ ] Zapisywanie ustawień
- [ ] Meta box w zamówieniu
- [ ] Responsywność na różnych urządzeniach

---

## Etap 5: Eksport Danych

**Cel:** Implementacja funkcjonalności eksportu logów do różnych formatów.  
**Szacowany czas:** 3-4 dni  
**Status:** ⚪ Oczekuje

### Zadania:

#### 5.1. Klasa Log Exporter (class-log-exporter.php)
- [ ] Architektura klasy
- [ ] Metoda główna: `export($format, $filters)`
- [ ] Walidacja uprawnień
- [ ] Rate limiting (max 5 eksportów/godzinę)
- [ ] Limit rozmiaru (100 MB)

#### 5.2. Eksport CSV
- [ ] Generowanie nagłówków kolumn
- [ ] Formatowanie wartości:
  - [ ] Escapowanie przecinków i cudzysłowów
  - [ ] Formatowanie dat zgodnie z ustawieniami
  - [ ] Obsługa separatora (przecinek/średnik/tab)
- [ ] Kodowanie (UTF-8/ISO-8859-2)
- [ ] Nagłówki HTTP dla pobierania pliku
- [ ] Nazwa pliku: `order-logs-{date}.csv`

#### 5.3. Eksport PDF
- [ ] Wybór biblioteki (TCPDF, FPDF, lub mPDF)
- [ ] Profesjonalny layout:
  - [ ] Nagłówek z logo (opcjonalnie)
  - [ ] Tytuł raportu
  - [ ] Informacje o filtrach
  - [ ] Tabela z danymi
  - [ ] Stopka z numeracją stron i datą
- [ ] Obsługa długich wartości (word wrap)
- [ ] Landscape dla szerszych tabel
- [ ] Nazwa pliku: `order-logs-{date}.pdf`

#### 5.4. Eksport JSON
- [ ] Pełna struktura danych
- [ ] Pretty print (czytelne formatowanie)
- [ ] Obsługa znaków specjalnych
- [ ] Nazwa pliku: `order-logs-{date}.json`

#### 5.5. UI dla Eksportu
- [ ] Przycisk "Eksportuj" w interfejsie logów
- [ ] Modal/dropdown z wyborem formatu
- [ ] Możliwość eksportu z aktywnymi filtrami
- [ ] Progress indicator dla dużych eksportów
- [ ] Komunikaty sukcesu/błędu

#### 5.6. Hooki dla Deweloperów
- [ ] Filter: `ihumbak_order_logs_export_data` - modyfikacja danych przed eksportem
- [ ] Filter: `ihumbak_order_logs_export_filename` - nazwa pliku
- [ ] Action: `ihumbak_order_logs_before_export` - przed eksportem
- [ ] Action: `ihumbak_order_logs_after_export` - po eksporcie

### Deliverables:
- [ ] Funkcjonalny eksport do CSV
- [ ] Funkcjonalny eksport do PDF
- [ ] Funkcjonalny eksport do JSON
- [ ] UI dla eksportu
- [ ] Rate limiting
- [ ] Testy eksportu

### Testy:
- [ ] Eksport do CSV (różne separatory i kodowania)
- [ ] Eksport do PDF (różne rozmiary danych)
- [ ] Eksport do JSON
- [ ] Eksport z filtrami
- [ ] Eksport dużych zestawów danych
- [ ] Rate limiting
- [ ] Uprawnienia do eksportu

---

## Etap 6: Bezpieczeństwo i Uprawnienia

**Cel:** Implementacja systemu bezpieczeństwa i uprawnień.  
**Szacowany czas:** 3-4 dni  
**Status:** ⚪ Oczekuje

### Zadania:

#### 6.1. Capabilities (Uprawnienia)
- [ ] Definicja custom capabilities:
  - [ ] `view_order_logs`
  - [ ] `export_order_logs`
  - [ ] `delete_order_logs`
  - [ ] `manage_order_log_settings`
- [ ] Przypisanie do ról podczas aktywacji:
  - [ ] Administrator: wszystkie
  - [ ] Shop Manager: view, export
- [ ] Usuwanie capabilities przy deinstalacji
- [ ] Możliwość modyfikacji przez ustawienia

#### 6.2. Nonce Verification
- [ ] Wszystkie formularze z nonce
- [ ] Weryfikacja przed zapisem ustawień
- [ ] Weryfikacja przed eksportem
- [ ] Weryfikacja przed usuwaniem logów
- [ ] AJAX requests z nonce

#### 6.3. Sanitizacja i Walidacja
- [ ] Input sanitization:
  - [ ] `sanitize_text_field()` dla pól tekstowych
  - [ ] `absint()` dla ID
  - [ ] `sanitize_email()` dla emaili
  - [ ] `wp_kses_post()` dla treści
- [ ] Walidacja:
  - [ ] Sprawdzanie typów danych
  - [ ] Sprawdzanie zakresów wartości
  - [ ] Walidacja dat

#### 6.4. Output Escaping
- [ ] `esc_html()` dla zwykłego tekstu
- [ ] `esc_attr()` dla atrybutów HTML
- [ ] `esc_url()` dla URLi
- [ ] `wp_kses_post()` dla HTML
- [ ] Escapowanie JSON przed wyświetleniem

#### 6.5. SQL Injection Prevention
- [ ] Używanie `$wpdb->prepare()` dla wszystkich zapytań
- [ ] Walidacja nazw kolumn i tabel
- [ ] Brak dynamicznych nazw tabel
- [ ] Prepared statements

#### 6.6. CSRF Protection
- [ ] Nonce dla wszystkich formularzy
- [ ] Weryfikacja referer
- [ ] Token dla AJAX requests

#### 6.7. XSS Prevention
- [ ] Escapowanie wszystkich outputów
- [ ] Sanitizacja przed zapisem
- [ ] Content Security Policy headers (opcjonalnie)

#### 6.8. RODO Compliance
- [ ] Informacja o zbieraniu IP i User Agent
- [ ] Możliwość wyłączenia zbierania danych osobowych
- [ ] Funkcja anonimizacji logów:
  - [ ] Usuwanie IP
  - [ ] Usuwanie User Agent
  - [ ] Hashowanie user_id (opcjonalnie)
- [ ] Export danych użytkownika (Data Portability)
- [ ] Usuwanie danych użytkownika (Right to Erasure)
- [ ] Hooki dla WP Privacy Tools:
  - [ ] `wp_privacy_personal_data_exporters`
  - [ ] `wp_privacy_personal_data_erasers`

### Deliverables:
- [ ] Kompletny system uprawnień
- [ ] Wszystkie zabezpieczenia wdrożone
- [ ] RODO compliance
- [ ] Testy bezpieczeństwa

### Testy:
- [ ] Testy uprawnień (różne role)
- [ ] Próby CSRF
- [ ] Próby SQL injection
- [ ] Próby XSS
- [ ] Export danych użytkownika (RODO)
- [ ] Usuwanie danych użytkownika (RODO)
- [ ] Anonimizacja logów

---

## Etap 7: Optymalizacja i Wydajność

**Cel:** Optymalizacja wydajności pluginu.  
**Szacowany czas:** 3-4 dni  
**Status:** ⚪ Oczekuje

### Zadania:

#### 7.1. Optymalizacja Zapytań
- [ ] Indeksy bazy danych (już w schemacie)
- [ ] Optymalizacja złożonych zapytań
- [ ] EXPLAIN dla slow queries
- [ ] Limit wyników zapytań

#### 7.2. Caching
- [ ] Wykorzystanie Transients API:
  - [ ] Cache dla list użytkowników (dropdown w filtrach)
  - [ ] Cache dla statystyk
  - [ ] Snapshot zamówienia przed zmianą
- [ ] Object Cache (jeśli dostępny)
- [ ] Możliwość wyłączenia cache w ustawieniach
- [ ] Ustawialny czas życia cache

#### 7.3. Asynchroniczne Logowanie
- [ ] Opcjonalne: Action Scheduler lub WP Cron
- [ ] Kolejkowanie logów
- [ ] Background processing dla dużych zmian
- [ ] Ustawienie: synchroniczne/asynchroniczne

#### 7.4. Lazy Loading
- [ ] Lazy loading dla dużych wartości JSON
- [ ] On-demand ładowanie szczegółów logu
- [ ] Paginacja z AJAX

#### 7.5. Czyszczenie Starych Logów
- [ ] WP Cron job dla czyszczenia
- [ ] Usuwanie logów starszych niż X dni (z ustawień)
- [ ] Opcjonalna archiwizacja przed usunięciem
- [ ] Logowanie operacji czyszczenia

#### 7.6. Monitoring Wydajności
- [ ] Statystyki:
  - [ ] Liczba logów w bazie
  - [ ] Rozmiar tabeli
  - [ ] Najaktywniejsze zamówienia
  - [ ] Średni czas zapisu logu
- [ ] Ostrzeżenia:
  - [ ] Duża liczba logów (> 100k)
  - [ ] Duży rozmiar tabeli
- [ ] Dashboard widget z podstawowymi statystykami

#### 7.7. Optymalizacja Frontendu
- [ ] Minifikacja CSS i JS (opcjonalnie)
- [ ] Ładowanie skryptów tylko gdzie potrzebne
- [ ] Usuwanie nieużywanych zależności

### Deliverables:
- [ ] Zoptymalizowane zapytania
- [ ] System cachingu
- [ ] Czyszczenie starych logów
- [ ] Dashboard widget ze statystykami
- [ ] Dokumentacja wydajności

### Testy:
- [ ] Testy wydajnościowe z 10k, 50k, 100k logów
- [ ] Pomiar czasu zapytań
- [ ] Pomiar czasu zapisu logu
- [ ] Testy cache
- [ ] Testy czyszczenia starych logów

---

## Etap 8: Testowanie i QA

**Cel:** Kompleksowe testowanie pluginu.  
**Szacowany czas:** 5-7 dni  
**Status:** ⚪ Oczekuje

### Zadania:

#### 8.1. Testy Jednostkowe (PHPUnit)
- [ ] Testy dla class-order-logger.php
- [ ] Testy dla class-log-database.php
- [ ] Testy dla class-log-tracker.php
- [ ] Testy dla class-log-formatter.php
- [ ] Testy dla class-log-exporter.php
- [ ] Testy dla class-hpos-compatibility.php
- [ ] Coverage > 70%

#### 8.2. Testy Integracyjne
- [ ] Symulacja zmian zamówień
- [ ] Weryfikacja poprawności logowania
- [ ] Testy w różnych konfiguracjach WordPress/WooCommerce

#### 8.3. Testy HPOS
- [ ] Wszystkie funkcjonalności w trybie CPT
- [ ] Wszystkie funkcjonalności w trybie HPOS
- [ ] Wszystkie funkcjonalności w trybie kompatybilności
- [ ] Migracja z CPT do HPOS (i odwrotnie)

#### 8.4. Testy Manualne
Zgodnie z checklistą z SPECIFICATION.md (sekcja 13.3):

**Podstawowe Funkcjonalności:**
- [ ] Zmiana statusu zamówienia
- [ ] Zmiana adresu wysyłki
- [ ] Zmiana adresu rozliczeniowego
- [ ] Dodanie produktu
- [ ] Usunięcie produktu
- [ ] Zmiana ilości produktu
- [ ] Zmiana ceny produktu
- [ ] Dodanie kosztów przesyłki
- [ ] Zmiana metody wysyłki
- [ ] Dodanie notatki prywatnej
- [ ] Dodanie notatki dla klienta
- [ ] Zmiana metody płatności
- [ ] Dodanie kuponu
- [ ] Usunięcie kuponu
- [ ] Eksport do CSV
- [ ] Eksport do PDF
- [ ] Eksport do JSON
- [ ] Filtrowanie logów
- [ ] Wyszukiwanie logów
- [ ] Czyszczenie starych logów
- [ ] Deinstalacja pluginu

**Testy Kompatybilności:**
- [ ] WordPress 5.8, 6.0, 6.2, 6.4
- [ ] WooCommerce 6.0, 7.0, 8.0, 8.2
- [ ] PHP 7.4, 8.0, 8.1, 8.2
- [ ] Popularne motywy (Storefront, Astra, OceanWP)
- [ ] WPML
- [ ] Polylang

#### 8.5. Testy Bezpieczeństwa
- [ ] Próby SQL injection
- [ ] Próby XSS
- [ ] Próby CSRF
- [ ] Testy uprawnień
- [ ] RODO compliance

#### 8.6. Testy Wydajnościowe
- [ ] Duża liczba logów (10k, 50k, 100k)
- [ ] Duża liczba zamówień
- [ ] Pomiary czasów odpowiedzi
- [ ] Testowanie na słabszym hostingu

#### 8.7. Code Quality
- [ ] PHP_CodeSniffer (WordPress Coding Standards)
- [ ] PHPStan/Psalm (analiza statyczna)
- [ ] Code review
- [ ] Refactoring jeśli potrzebny

### Deliverables:
- [ ] Suite testów jednostkowych
- [ ] Raport z testów integracyjnych
- [ ] Checklist testów manualnych (wypełniony)
- [ ] Raport z testów wydajnościowych
- [ ] Lista znalezionych i naprawionych bugów

### Testy:
To jest etap testów - wszystkie wymienione powyżej.

---

## Etap 9: Dokumentacja i Release

**Cel:** Przygotowanie dokumentacji i wydanie wersji 1.0.  
**Szacowany czas:** 3-4 dni  
**Status:** ⚪ Oczekuje

### Zadania:

#### 9.1. Dokumentacja Użytkownika
- [ ] README.md (już istnieje - do aktualizacji)
- [ ] Instrukcja instalacji (screenshots)
- [ ] Przewodnik użytkownika:
  - [ ] Konfiguracja pluginu
  - [ ] Przeglądanie logów
  - [ ] Filtrowanie i wyszukiwanie
  - [ ] Eksport danych
  - [ ] Zarządzanie ustawieniami
- [ ] FAQ (najczęściej zadawane pytania)
- [ ] Troubleshooting (rozwiązywanie problemów)

#### 9.2. Dokumentacja Deweloperska
- [ ] PHPDoc dla wszystkich klas i metod
- [ ] Dokumentacja API:
  - [ ] Lista wszystkich filtrów
  - [ ] Lista wszystkich akcji
  - [ ] Przykłady użycia
- [ ] Przykłady customizacji:
  - [ ] Wyłączenie logowania dla konkretnych zamówień
  - [ ] Dodanie custom action type
  - [ ] Modyfikacja danych przed zapisem
  - [ ] Custom export format
- [ ] Architektura pluginu (diagram)

#### 9.3. CHANGELOG.md
- [ ] Utworzenie pliku CHANGELOG.md
- [ ] Format: Keep a Changelog
- [ ] Sekcje: Added, Changed, Fixed, Removed
- [ ] Wersja 1.0.0 - lista wszystkich funkcjonalności

#### 9.4. Tłumaczenia
- [ ] Przegląd wszystkich stringów i18n
- [ ] Generowanie .pot file
- [ ] Tłumaczenie polskie (.po, .mo)
- [ ] Weryfikacja tłumaczeń w interfejsie

#### 9.5. Przygotowanie do Release
- [ ] Weryfikacja wersji we wszystkich plikach
- [ ] Sprawdzenie poprawności nagłówka pluginu
- [ ] Usunięcie plików deweloperskich z release'a
- [ ] Utworzenie .zip do dystrybucji
- [ ] Testy instalacji z .zip

#### 9.6. Release 1.0.0
- [ ] Git tag v1.0.0
- [ ] GitHub Release z opisem
- [ ] Załączenie .zip do release
- [ ] Announcement (jeśli aplikowalne)

#### 9.7. Post-Release
- [ ] Monitoring issues na GitHubie
- [ ] Zbieranie feedbacku
- [ ] Planowanie wersji 1.1 (roadmap)

### Deliverables:
- [ ] Kompletna dokumentacja użytkownika
- [ ] Kompletna dokumentacja deweloperska
- [ ] CHANGELOG.md
- [ ] Tłumaczenia (PL, EN)
- [ ] Release 1.0.0 na GitHubie
- [ ] Plik .zip do instalacji

### Testy:
- [ ] Sprawdzenie wszystkich linków w dokumentacji
- [ ] Weryfikacja przykładów kodu
- [ ] Instalacja z .zip i testy podstawowych funkcji

---

## 📊 Podsumowanie Etapów

| Etap | Nazwa | Czas (dni) | Priorytet | Zależności |
|------|-------|------------|-----------|------------|
| 0 | Przygotowanie i Planowanie | 1-2 | Wysoki | - |
| 1 | Podstawowa Infrastruktura | 2-3 | Krytyczny | 0 |
| 2 | System Logowania - Fundament | 4-5 | Krytyczny | 1 |
| 3 | Hooki i Śledzenie Zmian | 6-8 | Krytyczny | 2 |
| 4 | Interfejs Administratora | 5-7 | Wysoki | 2, 3 |
| 5 | Eksport Danych | 3-4 | Średni | 2, 4 |
| 6 | Bezpieczeństwo i Uprawnienia | 3-4 | Wysoki | 4, 5 |
| 7 | Optymalizacja i Wydajność | 3-4 | Średni | 3, 4 |
| 8 | Testowanie i QA | 5-7 | Krytyczny | 1-7 |
| 9 | Dokumentacja i Release | 3-4 | Wysoki | 8 |

**Szacowany całkowity czas:** 35-48 dni roboczych (7-10 tygodni)

---

## 🎯 Kamienie Milowe (Milestones)

### Milestone 1: Podstawowy System Logowania (Etapy 0-2)
**Deadline:** Tydzień 3  
**Deliverable:** Plugin z podstawową funkcjonalnością logowania zapisujący zmiany do bazy danych

### Milestone 2: Kompletne Śledzenie Zmian (Etap 3)
**Deadline:** Tydzień 5  
**Deliverable:** Automatyczne przechwytywanie wszystkich typów zmian w zamówieniach

### Milestone 3: Funkcjonalny Interfejs (Etapy 4-5)
**Deadline:** Tydzień 7  
**Deliverable:** Pełny interfejs administratora z możliwością przeglądania i eksportowania logów

### Milestone 4: Gotowość do Release (Etapy 6-9)
**Deadline:** Tydzień 10  
**Deliverable:** Plugin gotowy do publikacji (wersja 1.0.0)

---

## 📝 Roadmap Przyszłych Wersji

### Wersja 1.1 (Po 1.0)
**Planowane funkcjonalności:**
- Eksport PDF (zaawansowany)
- Zaawansowane filtrowanie (więcej opcji)
- Dashboard widget z statystykami i wykresami
- Dodatkowe tłumaczenia (DE, FR, ES)

**Szacowany czas:** 2-3 tygodnie

### Wersja 1.2
**Planowane funkcjonalności:**
- REST API dla logów
- Powiadomienia e-mail o ważnych zmianach
- Integracja z WooCommerce Admin (Analytics)
- Webhooks dla zewnętrznych systemów

**Szacowany czas:** 3-4 tygodnie

### Wersja 2.0
**Planowane funkcjonalności:**
- Diff view (wizualne porównanie wersji zamówienia)
- Przywracanie poprzednich wersji (rollback)
- Zaawansowana analityka i raporty
- Integracja z narzędziami audytowymi
- Machine learning dla wykrywania anomalii

**Szacowany czas:** 2-3 miesiące

---

## ⚠️ Ryzyka i Mitygacja

### Ryzyko 1: Problemy z HPOS
**Prawdopodobieństwo:** Średnie  
**Wpływ:** Wysoki  
**Mitygacja:** 
- Wczesne testowanie w różnych trybach
- Dokładna analiza dokumentacji WooCommerce
- Kontakt z społecznością WooCommerce

### Ryzyko 2: Wydajność przy dużej liczbie logów
**Prawdopodobieństwo:** Średnie  
**Wpływ:** Średni  
**Mitygacja:**
- Wczesne testy wydajnościowe
- Optymalizacja zapytań SQL
- Implementacja cachingu
- Archiwizacja starych logów

### Ryzyko 3: Konflikty z innymi pluginami
**Prawdopodobieństwo:** Niskie  
**Wpływ:** Średni  
**Mitygacja:**
- Unikalne nazwy funkcji i klas
- Proper namespacing
- Testy z popularnymi pluginami
- Dokumentacja znanych konfliktów

### Ryzyko 4: Zmiany w WooCommerce API
**Prawdopodobieństwo:** Niskie  
**Wpływ:** Wysoki  
**Mitygacja:**
- Używanie stabilnych API
- Monitorowanie WooCommerce changelog
- Backward compatibility
- Regularne aktualizacje

---

## 📞 Kontakt i Wsparcie

**Autor:** Michał Staniećko  
**Repozytorium:** https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs  
**Issues:** https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs/issues

---

**Ostatnia aktualizacja:** 2025-10-10  
**Wersja dokumentu:** 1.0  
**Status projektu:** 🟢 W planowaniu (Etap 0)
