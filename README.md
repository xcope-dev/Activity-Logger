# Activity Logger for Store

**Plugin Name:** Activity Logger for Store  
**Version:** 1.2  
**Author:** Xcope

---

## Opis

Activity Logger for Store to wtyczka do WordPress, która rejestruje aktywność administratorów oraz kierowników sklepu podczas tworzenia i edycji wpisów oraz produktów. Wtyczka umożliwia:
- Logowanie zdarzeń (tworzenie i aktualizacja wpisów) z informacjami o użytkowniku, jego roli, typie wpisu, tytule, adresie IP oraz dacie i godzinie zdarzenia.
- Automatyczne ignorowanie autosave’ów oraz rewizji, co gwarantuje czyste i precyzyjne logi.
- Czyszczenie starszych logów poprzez określenie liczby dni, co pozwala na utrzymanie bazy danych w optymalnym stanie.
- Usuwanie pozostałości – przy dezinstalacji wtyczki tabela logów zostaje automatycznie usunięta.

---

## Funkcjonalności

- **Rejestracja aktywności:**  
  Logowanie zdarzeń przy tworzeniu i edycji wpisów oraz produktów. Wtyczka zapisuje m.in. ID użytkownika, rolę, typ zdarzenia (utworzenie lub edycja), ID oraz tytuł wpisu, adres IP oraz datę i godzinę zdarzenia.

- **Bezpieczeństwo:**  
  - Wtyczka jest zabezpieczona przed bezpośrednim dostępem do pliku dzięki sprawdzeniu stałej `ABSPATH`.
  - Logowanie odbywa się tylko dla użytkowników posiadających role `administrator` lub `shop_manager`, co gwarantuje, że tylko uprawnione osoby mogą wywołać zapis aktywności.
  - Adresy IP oraz inne dane są odpowiednio sanitizowane, co zmniejsza ryzyko wstrzyknięcia niebezpiecznego kodu.
  - Przy dezinstalacji wtyczki, tabela z logami zostaje usunięta, dzięki czemu nie pozostaje żadnych niepotrzebnych danych w bazie.

- **Czyszczenie logów:**  
  Możliwość usunięcia logów starszych niż określona liczba dni za pomocą dedykowanego formularza w panelu administracyjnym.

- **Integracja z panelem administracyjnym:**  
  Wtyczka dodaje własną stronę w menu administracyjnym, gdzie można przeglądać zarejestrowane logi oraz korzystać z opcji czyszczenia starych danych.

---

## Instalacja

1. **Pobierz wtyczkę:**
   - Upewnij się, że masz najnowszą wersję wtyczki (`activity-logger/activity-logger.php`).

2. **Prześlij wtyczkę:**
   - Skopiuj folder `activity-logger` do katalogu `wp-content/plugins/` w Twojej instalacji WordPress.

3. **Aktywacja:**
   - Zaloguj się do panelu administracyjnego WordPress.
   - Przejdź do sekcji **Wtyczki** i znajdź **Activity Logger for Store**.
   - Kliknij **Aktywuj**.

4. **Korzystanie:**
   - Po aktywacji, w menu administracyjnym pojawi się nowa pozycja **Log aktywności**.
   - Kliknij w nią, aby przeglądać logi oraz skorzystać z funkcji czyszczenia starych logów.

5. **Dezinstalacja:**
   - W przypadku dezinstalacji, wtyczka automatycznie usuwa utworzoną tabelę logów, co pozwala na czyste usunięcie danych z bazy.

---

## Uwagi końcowe

Activity Logger for Store jest zgodny z obecnymi standardami WordPress, napisany z dbałością o bezpieczeństwo i optymalizację działania. Wtyczka została przetestowana pod kątem kompatybilności z różnymi wersjami WordPress i jest polecana dla sklepów internetowych, gdzie rejestrowanie aktywności administracyjnych jest kluczowe dla bezpieczeństwa i analizy działań.

Dziękuje za używanie wtyczki i życzę owocnej pracy z WordPressem!

---
