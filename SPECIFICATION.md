# Specyfikacja Pluginu: WooCommerce Order Edit Logs

## 1. Przegląd

### 1.1. Nazwa Pluginu
**iHumBak - WooCommerce Order Edit Logs**

### 1.2. Cel
Plugin ma za zadanie automatyczne zbieranie, przechowywanie i wyświetlanie szczegółowych logów wszystkich zmian przeprowadzanych w zamówieniach WooCommerce przez administratorów i użytkowników z odpowiednimi uprawnieniami.

### 1.3. Główne Funkcjonalności
- Automatyczne logowanie wszystkich modyfikacji zamówień
- Wyświetlanie historii zmian w interfejsie administratora
- Przechowywanie informacji o użytkowniku, dacie i szczegółach zmiany
- Możliwość filtrowania i przeszukiwania logów
- Eksport logów do plików CSV/PDF
- Czyszczenie starych logów

## 2. Wymagania Funkcjonalne

### 2.1. Śledzone Zdarzenia

Plugin powinien rejestrować następujące typy zmian w zamówieniach:

#### 2.1.1. Zmiany Statusu Zamówienia
- Zmiana statusu (np. z "oczekuje na płatność" na "przetwarzanie")
- Zapisywanie poprzedniego i nowego statusu
- Data i czas zmiany
- Użytkownik dokonujący zmiany

#### 2.1.2. Zmiany Danych Adresowych
- Modyfikacja adresu wysyłki
- Modyfikacja adresu rozliczeniowego
- Szczegółowe informacje o zmienionych polach (ulica, miasto, kod pocztowy, kraj, itp.)
- Wartości przed i po zmianie

#### 2.1.3. Zmiany Produktów
- Dodanie nowego produktu do zamówienia
- Usunięcie produktu z zamówienia
- Zmiana ilości produktu
- Zmiana ceny produktu
- Zmiana podatku produktu
- Zapisywanie pełnych szczegółów produktu (nazwa, SKU, ilość, cena jednostkowa, cena całkowita)

#### 2.1.4. Zmiany Kosztów Przesyłki
- Dodanie metody wysyłki
- Usunięcie metody wysyłki
- Zmiana kosztu wysyłki
- Zmiana metody wysyłki
- Zapisywanie nazwy metody i kosztu przed/po zmianie

#### 2.1.5. Zmiany Płatności
- Zmiana metody płatności
- Modyfikacja kwot (suma częściowa, rabaty, podatki, suma całkowita)
- Dodanie/usunięcie opłat (fees)
- Zmiany kuponów rabatowych

#### 2.1.6. Notatki i Komentarze
- Dodanie notatki prywatnej
- Dodanie notatki dla klienta
- Usunięcie notatki
- Treść notatki i typ (prywatna/dla klienta)

#### 2.1.7. Zmiany Danych Klienta
- Zmiana adresu e-mail
- Zmiana numeru telefonu
- Zmiana danych kontaktowych

#### 2.1.8. Inne Zmiany
- Modyfikacja pól niestandardowych (custom fields)
- Zmiany metadanych zamówienia
- Zmiany dat (data zakupu, data opłacenia, data ukończenia)
- Zmiany waluty
- Ręczne zmiany numeracji zamówienia

### 2.2. Struktura Logu

Każdy wpis w logu powinien zawierać:

```
{
  "log_id": "unikalny identyfikator logu",
  "order_id": "ID zamówienia",
  "user_id": "ID użytkownika dokonującego zmiany",
  "user_display_name": "Nazwa wyświetlana użytkownika",
  "user_role": "Rola użytkownika",
  "timestamp": "Data i czas zmiany (Y-m-d H:i:s)",
  "action_type": "Typ akcji (status_change, product_add, address_change, itp.)",
  "field_name": "Nazwa zmienionego pola",
  "old_value": "Wartość przed zmianą (JSON dla obiektów)",
  "new_value": "Wartość po zmianie (JSON dla obiektów)",
  "ip_address": "Adres IP użytkownika",
  "user_agent": "User Agent przeglądarki",
  "additional_data": "Dodatkowe dane kontekstowe (JSON)"
}
```

### 2.3. Typy Akcji (action_type)

- `order_created` - Utworzenie zamówienia
- `status_changed` - Zmiana statusu
- `billing_address_changed` - Zmiana adresu rozliczeniowego
- `shipping_address_changed` - Zmiana adresu wysyłki
- `product_added` - Dodanie produktu
- `product_removed` - Usunięcie produktu
- `product_quantity_changed` - Zmiana ilości produktu
- `product_price_changed` - Zmiana ceny produktu
- `shipping_added` - Dodanie wysyłki
- `shipping_removed` - Usunięcie wysyłki
- `shipping_cost_changed` - Zmiana kosztu wysyłki
- `shipping_method_changed` - Zmiana metody wysyłki
- `payment_method_changed` - Zmiana metody płatności
- `fee_added` - Dodanie opłaty
- `fee_removed` - Usunięcie opłaty
- `fee_changed` - Zmiana opłaty
- `coupon_added` - Dodanie kuponu
- `coupon_removed` - Usunięcie kuponu
- `note_added` - Dodanie notatki
- `note_deleted` - Usunięcie notatki
- `email_changed` - Zmiana e-maila
- `phone_changed` - Zmiana telefonu
- `customer_data_changed` - Zmiana danych klienta
- `total_changed` - Zmiana sumy całkowitej
- `tax_changed` - Zmiana podatku
- `meta_updated` - Aktualizacja metadanych
- `custom_field_changed` - Zmiana pola niestandardowego
- `date_changed` - Zmiana daty
- `currency_changed` - Zmiana waluty

## 3. Architektura Techniczna

### 3.1. Struktura Pluginu

```
ihumbak-woo-order-edit-logs/
├── includes/
│   ├── class-order-logger.php           # Główna klasa logowania
│   ├── class-log-database.php           # Obsługa bazy danych
│   ├── class-log-tracker.php            # Śledzenie zmian
│   ├── class-log-formatter.php          # Formatowanie danych
│   ├── class-log-exporter.php           # Eksport danych
│   ├── class-hpos-compatibility.php     # Obsługa kompatybilności HPOS
│   ├── admin/
│   │   ├── class-admin-interface.php    # Interface administratora
│   │   ├── class-log-viewer.php         # Przeglądarka logów
│   │   ├── class-settings.php           # Ustawienia pluginu
│   │   └── views/
│   │       ├── log-list.php             # Lista logów
│   │       ├── log-details.php          # Szczegóły logu
│   │       └── settings-page.php        # Strona ustawień
│   └── hooks/
│       ├── order-hooks.php              # Hooki zamówień (HPOS-compatible)
│       ├── product-hooks.php            # Hooki produktów
│       ├── address-hooks.php            # Hooki adresów
│       └── payment-hooks.php            # Hooki płatności
├── assets/
│   ├── css/
│   │   └── admin-styles.css             # Style administratora
│   └── js/
│       └── admin-scripts.js             # Skrypty administratora
├── languages/
│   ├── ihumbak-order-logs-pl_PL.po      # Tłumaczenie polskie
│   └── ihumbak-order-logs.pot           # Plik szablonu tłumaczeń
├── ihumbak-woo-order-edit-logs.php      # Główny plik pluginu
└── uninstall.php                         # Skrypt deinstalacji
```

**Nowa klasa: class-hpos-compatibility.php**
- Wykrywanie aktywnego trybu storage (CPT vs HPOS)
- Uniwersalne metody dostępu do zamówień
- Abstrakcja różnic między trybami
- Pomocnicze metody do porównywania stanów zamówień

### 3.2. Baza Danych

#### 3.2.1. Tabela: wp_ihumbak_order_logs

```sql
CREATE TABLE `wp_ihumbak_order_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_display_name` varchar(250) NOT NULL,
  `user_role` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action_type` varchar(100) NOT NULL,
  `field_name` varchar(255) DEFAULT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `additional_data` longtext DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `action_type` (`action_type`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3.2.2. Indeksy
- `log_id` - Klucz główny
- `order_id` - Szybkie wyszukiwanie logów dla konkretnego zamówienia
- `user_id` - Filtrowanie po użytkowniku
- `action_type` - Filtrowanie po typie akcji
- `timestamp` - Sortowanie chronologiczne

### 3.3. Hooki WordPress/WooCommerce

Plugin wykorzystuje następujące hooki do przechwytywania zmian. Wszystkie hooki są kompatybilne z HPOS (High-Performance Order Storage):

#### 3.3.1. Hooki Zamówień (HPOS-Compatible)
- `woocommerce_new_order` - Nowe zamówienie (args: $order_id, $order)
- `woocommerce_update_order` - Aktualizacja zamówienia (args: $order_id, $order)
- `woocommerce_order_status_changed` - Zmiana statusu (args: $order_id, $from_status, $to_status, $order)

**Strategia śledzenia zmian:**
- Przed zapisem: przechowywanie aktualnego stanu zamówienia w transient/cache
- Po zapisie: porównanie nowego stanu z zapisanym wcześniej
- Wykorzystanie metody `$order->get_changes()` do wykrywania zmienionych pól
- Obsługa zarówno trybu CPT (Custom Post Type) jak i HPOS

#### 3.3.2. Hooki Produktów (Order Items)
- `woocommerce_before_save_order_items` - Przed zapisem produktów (args: $order_id, $items)
- `woocommerce_saved_order_items` - Po zapisie produktów (args: $order_id, $items)
- `woocommerce_new_order_item` - Nowy produkt (args: $item_id, $item, $order_id)
- `woocommerce_update_order_item` - Aktualizacja produktu (args: $item_id, $item, $order_id)
- `woocommerce_delete_order_item` - Usunięcie produktu (args: $item_id)
- `woocommerce_before_delete_order_item` - Przed usunięciem (args: $item_id)

#### 3.3.3. Hooki Zmian w Zamówieniu
- `woocommerce_before_save_order_items` - Przechowywanie stanu przed zmianą
- Porównanie obiektów zamówienia przed i po zapisie
- Monitoring getterów: `get_billing_*()`, `get_shipping_*()`, `get_total()`, etc.

#### 3.3.4. Hooki Notatek
- `woocommerce_new_order_note` - Nowa notatka (args: $note_id, $order_note_data)
- `woocommerce_delete_order_note` - Usunięcie notatki (args: $note_id, $order)

#### 3.3.5. Hooki Metadanych (HPOS-Compatible)
**Dla trybu CPT (tradycyjnego):**
- `updated_post_meta` - Aktualizacja metadanych (args: $meta_id, $object_id, $meta_key, $meta_value)
- `added_post_meta` - Dodanie metadanych (args: $meta_id, $object_id, $meta_key, $meta_value)
- `deleted_post_meta` - Usunięcie metadanych (args: $meta_ids, $object_id, $meta_key, $meta_value)

**Dla trybu HPOS:**
- `added_wc_order_meta` - Dodanie metadanych zamówienia HPOS (args: $meta_id, $object_id, $meta_key, $meta_value)
- `updated_wc_order_meta` - Aktualizacja metadanych zamówienia HPOS (args: $meta_id, $object_id, $meta_key, $meta_value)
- `deleted_wc_order_meta` - Usunięcie metadanych zamówienia HPOS (args: $meta_ids, $object_id, $meta_key, $meta_value)
- Alternatywnie: hook w `woocommerce_update_order` i sprawdzanie zmian przez `$order->get_meta_data()`

#### 3.3.6. Dodatkowe Hooki dla Szczególnych Przypadków
- `woocommerce_order_refunded` - Zwrot zamówienia
- `woocommerce_order_fully_refunded` - Pełny zwrot
- `woocommerce_refund_created` - Utworzenie zwrotu
- `woocommerce_payment_complete` - Płatność zakończona
- `woocommerce_order_action_*` - Akcje masowe na zamówieniach

## 4. Interfejs Użytkownika

### 4.1. Lista Logów w Zamówieniu

W panelu edycji zamówienia (WooCommerce > Zamówienia > Edycja) powinna być dodana nowa sekcja/meta box:

**"Historia Zmian"**
- Tabela z listą wszystkich zmian dla danego zamówienia
- Kolumny: Data/Czas, Użytkownik, Akcja, Szczegóły, Wartość Poprzednia, Wartość Nowa
- Możliwość rozwinięcia szczegółów dla złożonych zmian
- Sortowanie według daty (najnowsze na górze)
- Paginacja dla dużej liczby wpisów

### 4.2. Strona Wszystkich Logów

Nowa pozycja w menu: **WooCommerce > Logi Zamówień**

Funkcjonalności:
- Tabela ze wszystkimi logami ze wszystkich zamówień
- Filtry:
  - Zakres dat (od-do)
  - Typ akcji (dropdown z wszystkimi typami)
  - Użytkownik (dropdown z użytkownikami)
  - ID zamówienia (pole tekstowe)
  - Status zamówienia
- Wyszukiwanie pełnotekstowe
- Sortowanie po kolumnach
- Paginacja (50/100/200 wpisów na stronę)
- Przycisk eksportu (CSV/PDF)
- Kolumny:
  - ID Logu
  - ID Zamówienia (link do edycji zamówienia)
  - Data/Czas
  - Użytkownik
  - Typ Akcji
  - Pole
  - Wartość Poprzednia
  - Wartość Nowa
  - IP
  - Akcje (Szczegóły/Usuń)

### 4.3. Szczegóły Logu

Modal lub osobna strona z pełnymi informacjami o logu:
- Wszystkie pola z bazy danych
- Sformatowane wyświetlanie JSON
- Link do zamówienia
- Link do profilu użytkownika
- Przycisk zamknięcia

### 4.4. Ustawienia Pluginu

Strona ustawień: **WooCommerce > Ustawienia > Logi Zamówień**

Zakładki:
1. **Ogólne**
   - Włącz/Wyłącz logowanie
   - Włącz/Wyłącz konkretne typy akcji (checkboxy)
   - Zapisywanie adresu IP (tak/nie)
   - Zapisywanie User Agent (tak/nie)

2. **Przechowywanie**
   - Automatyczne czyszczenie starych logów (włącz/wyłącz)
   - Wiek logów do usunięcia (dni): [pole numeryczne, domyślnie 90]
   - Częstotliwość czyszczenia (codziennie/co tydzień/co miesiąc)

3. **Uprawnienia**
   - Role, które mogą przeglądać logi (checkboxy dla ról WordPress)
   - Role, które mogą eksportować logi
   - Role, które mogą usuwać logi

4. **Wydajność**
   - Limit logów na stronę (domyślnie 50)
   - Włącz cache dla zapytań (tak/nie)
   - Czas życia cache (sekundy)

5. **Eksport**
   - Format daty w eksportach
   - Separator CSV (przecinek/średnik/tab)
   - Kodowanie pliku (UTF-8/ISO-8859-2)

## 5. Bezpieczeństwo i Uprawnienia

### 5.1. Capability (Uprawnienia WordPress)

Plugin tworzy następujące custom capabilities:
- `view_order_logs` - Przeglądanie logów
- `export_order_logs` - Eksportowanie logów
- `delete_order_logs` - Usuwanie logów
- `manage_order_log_settings` - Zarządzanie ustawieniami

Domyślnie przypisane do:
- Administrator: wszystkie uprawnienia
- Shop Manager: `view_order_logs`, `export_order_logs`

### 5.2. Zabezpieczenia

- Nonce verification dla wszystkich operacji
- Escapowanie danych wyjściowych (esc_html, esc_attr, esc_url)
- Prepared statements dla zapytań SQL
- Walidacja i sanitizacja danych wejściowych
- Rate limiting dla eksportu danych (max 5 eksportów na godzinę)
- CSRF protection
- XSS prevention

### 5.3. Prywatność Danych (RODO)

- Informacja o przechowywaniu IP i User Agent
- Możliwość wyłączenia zbierania danych osobowych
- Funkcja anonimizacji starych logów (usuwanie IP i User Agent)
- Export logów dla konkretnego użytkownika (Data Portability)
- Usuwanie logów użytkownika na żądanie (Right to Erasure)

## 6. Wydajność

### 6.1. Optymalizacje

- Asynchroniczne logowanie (WordPress Cron lub Action Scheduler)
- Buforowanie częstych zapytań (Transient API)
- Lazy loading dla dużych zestawów danych
- Indeksowanie bazy danych
- Paginacja dla wszystkich list
- AJAX dla operacji nie wymagających przeładowania strony

### 6.2. Limity

- Maksymalnie 1000 logów wyświetlanych jednocześnie
- Automatyczne archiwizowanie logów starszych niż 1 rok
- Limit 100 MB dla pojedynczego eksportu

### 6.3. Monitoring

- Logowanie błędów do debug.log (jeśli WP_DEBUG włączone)
- Statystyki: liczba logów, rozmiar tabeli, najaktywniejsze zamówienia
- Ostrzeżenia przy dużym rozmiarze tabeli (> 100k wpisów)

## 7. Eksport Danych

### 7.1. Format CSV

Kolumny w pliku CSV:
```
ID Logu, ID Zamówienia, Data/Czas, Użytkownik, Rola, Typ Akcji, Pole, Wartość Poprzednia, Wartość Nowa, Adres IP
```

### 7.2. Format PDF

- Profesjonalny układ z logo i nagłówkiem
- Tabela z wszystkimi danymi
- Możliwość filtrowania przed eksportem
- Stopka z datą wygenerowania i liczbą stron

### 7.3. Format JSON

- Pełny eksport danych w formacie JSON
- Przydatne dla integracji z innymi systemami
- Struktura odpowiadająca strukturze w bazie danych

## 8. Kompatybilność

### 8.1. Wymagania Minimalne

- WordPress: 5.8 lub wyższy
- WooCommerce: 6.0 lub wyższy (z obsługą HPOS)
- PHP: 7.4 lub wyższy
- MySQL: 5.6 lub wyższy / MariaDB: 10.0 lub wyższy

### 8.2. Testowana Kompatybilność

- WordPress: do wersji 6.4
- WooCommerce: do wersji 8.2
- PHP: 7.4, 8.0, 8.1, 8.2
- Popularne motywy: Storefront, Astra, OceanWP
- WPML (wielojęzyczność)
- Polylang (wielojęzyczność)
- **WooCommerce HPOS** (High-Performance Order Storage) - pełna kompatybilność

### 8.3. Kompatybilność z HPOS (High-Performance Order Storage)

Plugin jest w pełni kompatybilny z WooCommerce HPOS i obsługuje oba tryby przechowywania zamówień:

#### 8.3.1. Tryby Działania
- **Tryb CPT (Custom Post Type)** - tradycyjne przechowywanie zamówień jako posty
- **Tryb HPOS** - nowy system przechowywania w dedykowanych tabelach
- **Tryb kompatybilności** - synchronizacja między CPT a HPOS

#### 8.3.2. Implementacja HPOS

**Wykrywanie aktywnego trybu:**
```php
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
    // HPOS jest aktywny
} else {
    // Używany jest tradycyjny tryb CPT
}
```

**Uniwersalne pobieranie zamówienia:**
```php
// Zamiast get_post()
$order = wc_get_order( $order_id );

// Użycie getterów zamiast bezpośredniego dostępu
$status = $order->get_status();
$total = $order->get_total();
$billing_email = $order->get_billing_email();
```

**Obsługa metadanych:**
```php
// HPOS-compatible
$order->update_meta_data( 'key', 'value' );
$order->save();

// Lub bezpośrednio
update_post_meta( $order_id, 'key', 'value' ); // CPT
$order->update_meta_data( 'key', 'value' ); // HPOS
```

#### 8.3.3. Hooki Specyficzne dla HPOS

Plugin używa hooków kompatybilnych z HPOS:
- ✅ `woocommerce_new_order` - działa w obu trybach
- ✅ `woocommerce_update_order` - działa w obu trybach
- ✅ `woocommerce_order_status_changed` - działa w obu trybach
- ⚠️ `updated_post_meta` - tylko CPT (wymagana alternatywa dla HPOS)
- ✅ Monitoring przez `$order->get_changes()` - HPOS-compatible

#### 8.3.4. Strategia Porównywania Zmian

Ponieważ nie ma hooków `before_save` dla zamówień, plugin używa następującej strategii:

1. **Przed zapisem zamówienia:**
   - Hook: `woocommerce_before_save_order_items`
   - Akcja: Przechowanie aktualnego stanu zamówienia w transient
   - Cache key: `ihumbak_order_snapshot_{$order_id}`

2. **Po zapisie zamówienia:**
   - Hook: `woocommerce_update_order`
   - Akcja: Pobranie snapshotu i porównanie z aktualnym stanem
   - Logowanie różnic

3. **Użycie metody get_changes():**
   ```php
   $order = wc_get_order( $order_id );
   $changes = $order->get_changes(); // Tablica zmienionych właściwości
   ```

#### 8.3.5. Testowanie HPOS

Plugin musi być testowany w trzech scenariuszach:
- [ ] Tylko CPT (HPOS wyłączony)
- [ ] Tylko HPOS (CPT wyłączony)
- [ ] Tryb kompatybilności (synchronizacja CPT ↔ HPOS)

### 8.4. Znane Konflikty

Lista pluginów, które mogą powodować konflikty (do uzupełnienia podczas testów)

## 9. Wielojęzyczność

### 9.1. Języki

Plugin w pełni przygotowany do tłumaczenia (i18n):
- Wszystkie stringi owinięte w funkcje translacyjne
- Pliki .pot, .po, .mo
- Domyślny język: angielski
- Dostępne tłumaczenia: polski

### 9.2. Text Domain

`ihumbak-order-logs`

## 10. Instalacja i Aktywacja

### 10.1. Proces Instalacji

1. Upload pluginu przez panel WordPress lub FTP
2. Aktywacja pluginu
3. Automatyczne utworzenie tabel w bazie danych
4. Automatyczne utworzenie domyślnych ustawień
5. Przekierowanie do strony powitalnej z instrukcją

### 10.2. Deinstalacja

Przy usunięciu pluginu:
- Opcjonalne: Usunięcie tabel z bazy danych
- Opcjonalne: Usunięcie wszystkich ustawień
- Checkbox podczas deinstalacji: "Usuń wszystkie dane"

## 11. Aktualizacje

### 11.1. Migracje Bazy Danych

- System wersjonowania schematu bazy
- Automatyczne migracje podczas aktualizacji
- Backup przed migracją (opcjonalnie)
- Rollback w przypadku błędu

### 11.2. Changelog

Plik CHANGELOG.md z listą zmian w każdej wersji

## 12. Dokumentacja

### 12.1. Dokumentacja Użytkownika

- README.md - podstawowe informacje
- Instrukcja instalacji
- Przewodnik użytkownika (screenshots)
- FAQ (najczęściej zadawane pytania)

### 12.2. Dokumentacja Deweloperska

- PHPDoc dla wszystkich klas i metod
- Hooki i filtry dostępne dla rozszerzeń
- Przykłady customizacji
- API documentation

### 12.3. Hooki i Filtry dla Deweloperów

#### Filtry:
- `ihumbak_order_logs_enabled` - Włącz/wyłącz logowanie
- `ihumbak_order_logs_action_types` - Modyfikacja typów akcji
- `ihumbak_order_logs_data_before_save` - Modyfikacja danych przed zapisem
- `ihumbak_order_logs_capabilities` - Modyfikacja uprawnień
- `ihumbak_order_logs_retention_days` - Czas przechowywania
- `ihumbak_order_logs_display_columns` - Kolumny w tabeli
- `ihumbak_order_logs_export_data` - Dane do eksportu

#### Akcje:
- `ihumbak_order_logs_before_log` - Przed zapisem logu
- `ihumbak_order_logs_after_log` - Po zapisie logu
- `ihumbak_order_logs_before_delete` - Przed usunięciem logu
- `ihumbak_order_logs_after_delete` - Po usunięciu logu
- `ihumbak_order_logs_cleanup` - Czyszczenie starych logów

## 13. Testowanie

### 13.1. Testy Jednostkowe (Unit Tests)

- PHPUnit dla głównych klas
- Coverage co najmniej 70%
- Testy dla wszystkich metod logowania

### 13.2. Testy Integracyjne

- Symulacja zmian zamówień
- Weryfikacja poprawności logowania
- Testy wydajnościowe z dużą liczbą logów
- **Testy kompatybilności HPOS** (wszystkie scenariusze w 3 trybach: CPT, HPOS, kompatybilności)

### 13.3. Testy Manualne

Checklist testów przed release:

**Podstawowe Testy Funkcjonalności:**
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
- [ ] Filtrowanie logów
- [ ] Wyszukiwanie logów
- [ ] Czyszczenie starych logów
- [ ] Deinstalacja pluginu

**Testy Kompatybilności HPOS:**
- [ ] Wszystkie powyższe testy z HPOS wyłączonym (tylko CPT)
- [ ] Wszystkie powyższe testy z HPOS włączonym (tylko HPOS)
- [ ] Wszystkie powyższe testy w trybie kompatybilności (CPT + HPOS)
- [ ] Migracja z CPT do HPOS (sprawdzenie czy logi są prawidłowo zapisywane przed i po)
- [ ] Migracja z HPOS do CPT (sprawdzenie czy logi są prawidłowo zapisywane przed i po)
- [ ] Weryfikacja że order_id odnosi się do prawidłowego zamówienia w obu trybach

## 14. Roadmap (Przyszłe Funkcjonalności)

### Wersja 1.0
- Podstawowe logowanie wszystkich zmian
- Interface administratora
- Eksport CSV

### Wersja 1.1
- Eksport PDF
- Zaawansowane filtrowanie
- Dashboard widget z statystykami

### Wersja 1.2
- REST API dla logów
- Powiadomienia e-mail o ważnych zmianach
- Integracja z WooCommerce Admin

### Wersja 2.0
- Diff view (porównanie wersji zamówienia)
- Przywracanie poprzednich wersji (rollback)
- Zaawansowana analityka i raporty
- Integracja z narzędziami audytowymi

## 15. Support i Wsparcie

### 15.1. Kanały Wsparcia

- GitHub Issues dla zgłoszeń błędów
- Dokumentacja online
- E-mail support dla klientów premium

### 15.2. SLA (Service Level Agreement)

- Odpowiedź na critical bugs: 24h
- Odpowiedź na standardowe pytania: 48h
- Nowe features: według roadmap

## 16. Licencja

GPL v2 lub późniejsza - kompatybilna z WordPress i WooCommerce

## 17. Credits

- Autor: [Nazwa/Firma]
- Contributors: [Lista współtwórców]
- Wsparcie: [Informacje kontaktowe]

---

**Wersja dokumentu:** 1.0  
**Data ostatniej aktualizacji:** 2025-10-10  
**Status:** Draft - Do zatwierdzenia
