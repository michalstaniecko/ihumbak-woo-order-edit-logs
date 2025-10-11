# iHumBak - WooCommerce Order Edit Logs

Plugin WordPress do automatycznego zbierania i przechowywania szczegółowych logów wszystkich zmian przeprowadzanych w zamówieniach WooCommerce.

## 📋 Opis

Plugin **iHumBak - WooCommerce Order Edit Logs** umożliwia administratorom sklepu WooCommerce śledzenie i monitorowanie wszystkich modyfikacji dokonywanych w zamówieniach. Każda zmiana - od aktualizacji statusu, przez modyfikację adresów, po dodanie czy usunięcie produktów - jest szczegółowo rejestrowana wraz z informacjami o użytkowniku, dacie i szczegółach zmiany.

## ✨ Główne Funkcjonalności

- 📝 **Automatyczne logowanie zmian** - wszystkie modyfikacje zamówień są automatycznie zapisywane
- 👤 **Informacje o użytkowniku** - kto, kiedy i co zmienił
- 📊 **Szczegółowa historia** - wartości przed i po zmianie
- 🔍 **Zaawansowane filtrowanie** - wyszukiwanie po dacie, typie akcji, użytkowniku, ID zamówienia
- 📤 **Eksport danych** - CSV, PDF, JSON
- 🗑️ **Automatyczne czyszczenie** - usuwanie starych logów
- 🔒 **Bezpieczeństwo** - system uprawnień i ochrona danych
- 🌍 **Wielojęzyczność** - pełna obsługa tłumaczeń (polski, angielski)

## 📦 Śledzone Zdarzenia

Plugin rejestruje następujące typy zmian:

### Zamówienia
- ✅ Zmiana statusu zamówienia
- 📧 Zmiana danych kontaktowych klienta
- 💰 Zmiana sum i kwot

### Produkty
- ➕ Dodanie produktu do zamówienia
- ➖ Usunięcie produktu z zamówienia
- 🔢 Zmiana ilości produktu
- 💵 Zmiana ceny produktu

### Adresy
- 🏠 Modyfikacja adresu wysyłki
- 📮 Modyfikacja adresu rozliczeniowego

### Wysyłka i Płatności
- 🚚 Dodanie/usunięcie/zmiana metody wysyłki
- 💳 Zmiana metody płatności
- 💸 Zmiana kosztów przesyłki
- 🎟️ Dodanie/usunięcie kuponów rabatowych

### Notatki
- 📌 Dodanie notatki prywatnej
- 💬 Dodanie notatki dla klienta
- 🗑️ Usunięcie notatki

### Inne
- 🔧 Zmiany metadanych i pól niestandardowych
- 📅 Zmiany dat zamówienia
- 🌐 Zmiana waluty

**[📖 Zobacz jak śledzić własne pola niestandardowe](CUSTOM_META_FIELDS.md)**

## 🎯 Dla Kogo?

Plugin idealnie sprawdzi się w:
- Średnich i dużych sklepach z wieloma administratorami
- Sklepach wymagających audytu zmian
- Sklepach z wrażliwymi danymi lub wysokimi wartościami transakcji
- Sklepach potrzebujących compliance z regulacjami (RODO, audyty)
- Zespołach wymagających kontroli nad procesem obsługi zamówień

## 📋 Wymagania

- **WordPress:** 5.8 lub wyższy
- **WooCommerce:** 6.0 lub wyższy (z pełną obsługą HPOS)
- **PHP:** 7.4 lub wyższy
- **MySQL:** 5.6+ lub MariaDB 10.0+

## ⚡ Kompatybilność z HPOS

Plugin jest w pełni kompatybilny z **WooCommerce High-Performance Order Storage (HPOS)**:
- ✅ Obsługa tradycyjnego trybu CPT (Custom Post Type)
- ✅ Obsługa nowego trybu HPOS
- ✅ Obsługa trybu kompatybilności (synchronizacja CPT ↔ HPOS)
- ✅ Automatyczne wykrywanie aktywnego trybu storage
- ✅ Wszystkie funkcje działają identycznie w obu trybach

## 🚀 Instalacja

1. Pobierz plugin z repozytorium GitHub
2. Prześlij folder pluginu do `/wp-content/plugins/`
3. Aktywuj plugin przez panel administracyjny WordPress
4. Przejdź do **WooCommerce > Ustawienia > Logi Zamówień** aby skonfigurować plugin

## 📖 Dokumentacja

Szczegółowa specyfikacja techniczna dostępna w pliku [SPECIFICATION.md](SPECIFICATION.md)

### Główne Sekcje Dokumentacji:
- Pełna specyfikacja funkcjonalności
- Architektura techniczna i struktura plików
- Schemat bazy danych
- Lista hooków WordPress/WooCommerce
- Interfejs użytkownika
- Bezpieczeństwo i uprawnienia
- Optymalizacje wydajności
- Instrukcje dla deweloperów

## 🔧 Konfiguracja

Po instalacji możesz skonfigurować plugin w **WooCommerce > Ustawienia > Logi Zamówień**:

- **Ogólne** - włącz/wyłącz konkretne typy logowania
- **Przechowywanie** - automatyczne czyszczenie starych logów
- **Uprawnienia** - kontrola dostępu do logów
- **Wydajność** - optymalizacja dla dużych sklepów
- **Eksport** - ustawienia formatowania eksportowanych danych

## 📊 Przeglądanie Logów

### W Zamówieniu
W panelu edycji zamówienia znajdziesz nową sekcję **"Historia Zmian"** z listą wszystkich modyfikacji tego zamówienia.

### Wszystkie Logi
Pełna lista logów dostępna w **WooCommerce > Logi Zamówień** z możliwością:
- Filtrowania po dacie, typie akcji, użytkowniku
- Wyszukiwania pełnotekstowego
- Sortowania po kolumnach
- Eksportowania do CSV/PDF/JSON

## 🔒 Bezpieczeństwo i Prywatność

Plugin został zaprojektowany z myślą o bezpieczeństwie:
- ✅ Nonce verification
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (escapowanie danych)
- ✅ CSRF protection
- ✅ System uprawnień oparty na capabilities
- ✅ Zgodność z RODO (anonimizacja, export, usuwanie danych)

## 🛠️ Dla Deweloperów

Plugin oferuje liczne hooki i filtry do customizacji:

### Przykładowe Filtry:
```php
// Wyłącz logowanie dla konkretnego zamówienia
add_filter('ihumbak_order_logs_enabled', function($enabled, $order_id) {
    if ($order_id === 123) {
        return false;
    }
    return $enabled;
}, 10, 2);

// Modyfikuj dane przed zapisem
add_filter('ihumbak_order_logs_data_before_save', function($data) {
    // Twoja logika
    return $data;
});
```

### Przykładowe Akcje:
```php
// Wykonaj akcję po zapisaniu logu
add_action('ihumbak_order_logs_after_log', function($log_id, $data) {
    // Np. wyślij powiadomienie
}, 10, 2);
```

Pełna lista dostępnych hooków w [SPECIFICATION.md](SPECIFICATION.md#123-hooki-i-filtry-dla-deweloperów).

## 📈 Roadmap

### Wersja 1.0 (Aktualna)
- ✅ Podstawowe logowanie wszystkich zmian
- ✅ Interface administratora
- ✅ Eksport CSV

### Wersja 1.1 (Planowana)
- 📋 Eksport PDF
- 🔍 Zaawansowane filtrowanie
- 📊 Dashboard widget z statystykami

### Wersja 1.2 (Planowana)
- 🔌 REST API dla logów
- 📧 Powiadomienia e-mail o ważnych zmianach
- 🎨 Integracja z WooCommerce Admin

### Wersja 2.0 (Przyszłość)
- 🔄 Diff view (porównanie wersji)
- ⏮️ Przywracanie poprzednich wersji
- 📈 Zaawansowana analityka

## 🤝 Wsparcie

- **Zgłoszenia błędów:** [GitHub Issues](https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs/issues)
- **Dokumentacja:** [SPECIFICATION.md](SPECIFICATION.md)
- **Pytania:** Otwórz issue na GitHubie

## 📝 Licencja

GPL v2 lub późniejsza - zgodna z licencją WordPress i WooCommerce.

## 👏 Credits

Opracowane przez Michał Staniećko

---

**Uwaga:** Plugin jest obecnie w fazie specyfikacji. Kod źródłowy będzie dostępny wkrótce.