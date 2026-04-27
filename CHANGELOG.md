# CHANGELOG.md — Хронологія змін
## DITIB-Ahlen-Portal

> Тут — повна історія змін: хто, коли, що зробив.
> Правила для агентів → `CLAUDE.md` | Архітектура → `PROJECT.md`

---

## Три документи проекту

| Файл | Призначення |
|------|-------------|
| **`CLAUDE.md`** | Правила для агентів, команди, середовище — читати першим |
| **`PROJECT.md`** | Архітектура, стек, функціональність, деплой |
| **`CHANGELOG.md`** ← ти тут | Хронологія всіх змін — що, коли, хто зробив |

---

## Як додавати запис

```
### [YYYY-MM-DD HH:MM] Короткий опис — AgentName
- що зроблено
- що змінено
- що виправлено
```

**Правила:**
- Дата і час — реальні, не приблизні
- AgentName: `Claude Code`, `Codex`, `Gemini`
- Один запис на сесію роботи
- Нові записи — завжди **знизу**
- Не редагувати чужі записи
- Якщо оновлюєш `PROJECT.md` статус — також пиши тут

---

## 2026-04-27

### [2026-04-27 09:00] Ініціалізація проекту — Claude Code
- Створено окремий GitHub репозиторій `DITIB-Ahlen-Portal`
- Створено локальну папку `~/Project/DITIB-Ahlen/portal/`
- Написано `PROJECT.md` з архітектурою, стеком, правилами
- Написано `CLAUDE.md` з правилами для AI агентів
- Зафіксовано порти: Portal=8000, Docker/Landing=8080

### [2026-04-27 10:00] Laravel + Filament встановлені — Claude Code
- `composer create-project laravel/laravel` → Laravel 13.6.0
- `composer require filament/filament:"^5.0"` → Filament v5.6
- `php artisan filament:install --panels` → AdminPanel на `/admin`
- SQLite налаштований для локальної розробки
- Адмін-користувач створений: `rpachkovskyi@gmail.com`

### [2026-04-27 10:30] База даних і моделі — Claude Code
- Міграція `create_members_table` — всі поля анкети Mitgliedsantrag
- Міграція `create_change_requests_table` — запити на зміну даних
- Модель `Member` з `encrypted` cast для IBAN і BIC (DSGVO)
- Модель `ChangeRequest` з relation до `Member`

### [2026-04-27 11:00] Filament AdminPanel — MemberResource — Claude Code
- `MemberResource` з навігацією "Mitglieder"
- `MemberForm` — форма з секціями: Persönliche Daten, Bankverbindung, Status
- `MembersTable` — таблиця з badge-статусом, фільтром, сортуванням
- `ViewMember` сторінка для перегляду запису
- Другий Filament panel `/konto` для кабінету члена (MemberPanelProvider)

### [2026-04-27 11:30] Публічна форма Mitgliedsantrag — Claude Code
- Livewire компонент `MembershipForm` — 3-крокова форма
- Крок 1: Персональні дані
- Крок 2: Банківські дані (SEPA)
- Крок 3: Підпис (canvas Alpine.js) + SEPA/DSGVO згода
- Layout `layouts/public.blade.php` з хедером DITIB Ahlen
- Маршрут `/` → форма, після відправки → сторінка підтвердження

### [2026-04-27 12:00] Виправлення багів — Claude Code
- **Fix:** `Section` namespace → `Filament\Schemas\Components\Section` (не Forms)
- **Fix:** Видалено зайвий `BadgeColumn` import (не існує в Filament v5)
- **Fix:** Signature canvas — Alpine.js з `$nextTick` → правильна ширина canvas
- **Fix:** IBAN/BIC прибрані з `$hidden` у моделі — `encrypted` cast достатньо
- **Cleanup:** Видалено артефактний файл `=log/# Error`

### [2026-04-27 13:00] Документація — перехресні посилання — Claude Code
- `CLAUDE.md` — повністю переписаний: навігація між документами, структура файлів, Filament v5 gotchas
- `PROJECT.md` — оновлено: таблиця БД, статус реалізації, перехресні посилання
- `CHANGELOG.md` — додано навігаційну шапку і таблицю документів

---

## 2026-04-27 (продовження)

### [2026-04-27 14:00] Етап 1 — нові поля, 4-крокова форма, валідація — Claude Code
- **Міграція** `update_members_table_stage1`: нові поля `member_number` (унікальний, auto), `birth_place`, `staatsangehoerigkeit`, `familienangehoerige`, `cenaze_fonu`, `cenaze_fonu_nr`, `gemeinderegister`, `beruf`, `heimatstadt`, `zahlungsart` (enum); перейменування `jahresbeitrag` → `monatsbeitrag`, дефолт €25
- **Модель `Member`**: оновлено `$fillable`, `$casts`, auto-генерація `member_number` у форматі `DA-YYYY-NNNN` через `booted()`
- **`MemberForm` (Filament admin)**: нові секції з усіма полями, умовна видимість SEPA-полів через `->visible()` та `->live()`
- **`MembersTable`**: `member_number` — перша колонка, `monatsbeitrag` замість `jahresbeitrag`, `zahlungsart`
- **Публічна форма** перебудована на 4 кроки: 1-Kişisel Bilgiler, 2-Adres, 3-Aidat/Banka, 4-İmza
- Валідація мінімального віку 16 років (кастомне правило), мін. €25/міс
- SEPA-поля (Kontoinhaber/IBAN/BIC) показуються тільки при zahlungsart=lastschrift/dauerauftrag
- Двомовні мітки DE+TR прибрано з усіх полів — залишено тільки німецьку. Турецька — Етап 4.

### [2026-04-27 15:00] Документація — аудит і зафіксовані архітектурні рішення — Claude Code
- **`Правки і зміни на сайті.md`** повністю переструктуровано по 4 етапах з позначками ✅/⬜/🔲
- Додано технічні рішення (зафіксовано) до кожного етапу:
  - Етап 2: PLZ → локальна БД (не API), FilePond + Image Crop, Laravel Queues для email
  - Етап 3: spatie/laravel-settings для підпису/печатки, Base64 в DomPDF (критично), Event+Job для підтвердження
  - Етап 4: Middleware SetLocale, lang/de.json + lang/tr.json, Magic Link (temporarySignedRoute)
- Дизайн адмінки виділено в окреме завдання: Dashboard stats, іконки навігації, логотип
- **`PROJECT.md`** оновлено: таблиця БД відповідає поточній схемі, стек доповнено (Queues, spatie/laravel-settings), статус реалізації розбито по 4 етапах
- Проаналізовано файл `==logs/Analytics.md` (Gemini): більшість порад прийнято, хибне зауваження про версії (Laravel 13/Filament v5/PHP 8.5) відхилено — версії актуальні станом на 2026

### [2026-04-27 16:30] Етап 2 — Автозаповнення міст за PLZ (Поштовим індексом) — Gemini
- Створено міграцію та модель `PostalCode` (`plz`, `ort`, `bundesland`)
- Написано Artisan команду `ImportPostalCodes` (`php artisan app:import-postal-codes`) для завантаження бази поштових індексів Німеччини (із GeoNames, `DE.zip`) та імпорту в локальну базу даних (завантажено 23,297 індексів)
- `MembershipForm` (Livewire) оновлено: додано хук `updatedPostalCode()`
- `membership-form.blade.php` оновлено: інпут `postal_code` використовує `wire:model.live.debounce.300ms` для миттєвого підтягування `city` та `state` без перезавантаження

- **Fix:** Створено міграцію `make_sepa_fields_nullable_in_members_table`, щоб `kontoinhaber` та `iban` могли бути `null` (для виправлення помилки при `barzahlung` "NOT NULL constraint failed")

### [2026-04-27 16:35] Адмін-панель: редирект після збереження — Gemini
- Оновлено `EditMember.php`: додано `getRedirectUrl()`, щоб після успішного збереження редагувань користувача (Save changes) система автоматично повертала адміна до списку (`index`), замість того щоб залишатися на сторінці редагування.
- В `Правки і зміни на сайті.md` відмічено відповідні пункти як виконані (✅).

---

### [2026-04-27 17:00] Аудит статусів у документі вимог — Claude Code
- Виявлено і виправлено помилково проставлені ✅ у `Правки і зміни на сайті.md`:
  - "Підтвердження реєстрації" → повернуто в ⬜ (кнопки підтвердження одним кліком нема, є лише ручна зміна статусу через Edit-форму)
  - "Видалення користувача" → повернуто в ⬜ (bulk-delete і Delete на Edit-сторінці є, але кнопки в рядку/View нема)
- Підтверджено реально виконані: авто-редирект після збереження (`getRedirectUrl()`), PLZ→місто/земля (`updatedPostalCode()`)
- Три нові пункти від Gemini/користувача впорядковано: прибирання Filament-блоку, German locale, Dashboard зі статистикою — усі поставлені в ⬜ (Етап 3)
- Dashboard і branding-задачі переміщено з Етапу 4 до Етапу 3 (вони належать до адмінки)

### [2026-04-27 17:30] Адмін-таблиця: колонки, статуси, хлібні крихти — Claude Code
- **Таблиця**: гумові колонки (`->wrap()`), Column Toggle (`->toggleable()`) — за замовчуванням видно Nr./Name/Status/E-Mail/Ort/Beitrag/Eingegangen, решта приховані
- **Статус у таблиці**: badge з іконками — Ausstehend ✦ (warning), Aktiv ✓ (success), Inaktiv ✕ (danger)
- **Додатковий фільтр** по Zahlungsart
- **Статус у формі Edit**: ToggleButtons з іконками та кольорами (`->inline()`) замість Select-дропдауну
- **Хлібні крихти**: `getRecordTitle()` в MemberResource → показує `full_name` члена замість "View"
- **Fix**: підпис методу `getRecordTitle()` виправлено до сумісного з батьківським класом (nullable record, Htmlable return type)

---

### [2026-04-27 19:00] Адмін-панель: layout форми, хлібні крихти, меню таблиці — Claude Code

- **Хлібні крихти**: перевизначено `getBreadcrumb()` у `ViewMember` і `EditMember` → тепер показує ім'я члена (`full_name`), а не "View"
- **Заголовок сторінок**: `getTitle()` у ViewMember → ім'я члена; у EditMember → "ім'я bearbeiten"
- **Редирект після збереження**: `getRedirectUrl()` в EditMember → переходить на View-сторінку запису (не на список)
- **Меню в рядках таблиці**: `ActionGroup::make([View/Bearbeiten/Löschen])` у `->recordActions()` — три дії під кнопкою "⋮"
- **Layout форми**: виявлено, що Filament v5 Schema за замовчуванням вже є 2-колонковим гридом — прибрано зайвий `Grid::make()` та `Group::make()`, секції передаються прямо в `$schema->components([])`
- **Результат layout**: ліво — Persönliche Daten + Beitrag & Bankverbindung; право — Status & Verwaltung (`grid-row: span 2`)
- **Persönliche Daten**: внутрішня 2-колонкова сітка `->columns(['default' => 1, 'sm' => 2])` — парні поля поряд (Geburtsdatum/Geburtsort, Staatsangehörigkeit/Familienangehörige, Beruf/Heimatstadt, Postleitzahl/Ort, Bundesland/Telefon); довгі поля (`street`, `email`, toggles) → `->columnSpanFull()`
- **Fix**: `Toggle::make('cenaze_fonu')` отримав `->live()` — поле `cenaze_fonu_nr` з'являється без перезавантаження сторінки
- **Fix**: Radio не підтримує `->icons()` у Filament v5 → замінено на `ToggleButtons`

### [2026-04-27 19:15] Адмін-панель: правки в Persönliche Daten — Gemini
- `Mitgliedsnummer`: замінено з `TextInput` (disabled) на `Placeholder`, вирівняно по правому краю в стилі технічного ідентифікатора, показується тільки якщо номер вже згенеровано (не показується при створенні).
- `full_name`: встановлено `->columnSpanFull()`, щоб поле "Vor- und Nachname" займало всю ширину блоку, аналогічно до "Straße und Hausnummer".

---

*Цей файл ведеться вручну всіма агентами. Не видаляти, не перейменовувати.*
