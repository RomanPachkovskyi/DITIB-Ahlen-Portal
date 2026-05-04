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

- `kontoinhaber` та `iban`: додано обов'язкову валідацію (`->required()`) при виборі "Lastschrift" або "Dauerauftrag", щоб уникнути збереження неповної інформації адміном.

- `phone`: оновлено валідацію: `regex:/^[+\(\)\-\s0-9]+$/` — тепер поле дозволяє зручний формат (з плюсом, дужками, дефісами та пробілами, але без літер), а при збереженні система автоматично очищає телефон, залишаючи лише плюс та цифри (E.164 формат) `preg_replace('/[^\+0-9]/', '', $this->phone)`.
- `iban`: додано строгу валідацію міжнародного формату IBAN (2 літери, 2 цифри, від 11 до 30 символів) `regex:/^[A-Za-z]{2}[0-9]{2}[A-Za-z0-9]{11,30}$/` у публічній формі та адмін-панелі.
- `postal_code`: додано валідацію `regex:/^[0-9]{5}$/` (тільки 5 цифр для Німеччини).
- **Live Validation**: у публічній формі реєстрації (`MembershipForm.php` + `blade`) додано метод `updated($propertyName)` та замінено всі `wire:model` на `wire:model.blur`. Тепер людина бачить помилки валідації **одразу**, щойно переходить на наступне поле (on blur), а не після натискання кнопки далі/зберегти.
- **Fix**: поле `familienangehoerige` (Кількість членів сім'ї) тепер має статус `->required()` в адмін-панелі (щоб уникнути збереження порожнього значення `null` і помилки "NOT NULL constraint failed").

---

### [2026-04-27–28] Валідація форм + PLZ-автодоповнення з dropdown — Claude Sonnet

#### Виправлення валідації (публічна форма реєстрації)
- **`MembershipForm.php`** — метод `updated()` виправлено: тепер перевіряє лише правила **поточного кроку** (`match($this->step)` замість злиття всіх 4 кроків), що усуває хибні помилки
- **Regex-валідація** додана до всіх текстових полів у `rulesStep1()`: `full_name`, `birth_place`, `staatsangehoerigkeit`, `beruf`, `heimatstadt` — regex `/^[\pL\s\-]+$/u` (тільки літери, пробіли, дефіс; цифри заборонені)
- **`nextStep()`** тепер викликає `$this->resetValidation()` після переходу кроку — щоб старі помилки не показувалися на новому кроці
- **`@error()` блоки** додані до раніше "сліпих" необов'язкових полів: `birth_place`, `staatsangehoerigkeit`, `beruf`, `heimatstadt` — тепер помилки фактично відображаються у браузері

#### Фізичне блокування символів (браузерний рівень)
- **Проблема**: `x-on:input` (Alpine.js) + `wire:model.blur` конфліктували — Livewire перезаписував DOM після blur-sync, скасовуючи Alpine-фільтрацію
- **Рішення**: текстові поля з блокуванням перемкнуто на `wire:model.live.debounce.500ms`; `x-on:input` синхронізується до Livewire-стану відразу, тому фільтрація зберігається
- **Адмін-форма (Filament)**: замінено `x-on:input` на нативний `oninput` у `->extraInputAttributes()` — незалежний від Alpine.js, завжди спрацьовує
- Тепер фізично неможливо ввести:
  - Цифри у `Postleitzahl` → `oninput` видаляє нецифрові символи, `maxlength=5`
  - Літери у `Telefon` → `oninput` видаляє кирилицю, латиницю, умляути
  - Цифри у текстових полях (ім'я, місто, берег тощо) → `x-on:input` + `wire:model.live`

#### PLZ Autocomplete — повний dropdown-пошук
- **`MembershipForm.php`**: новий стан `$plzSuggestions[]`, `$showPlzDropdown`; переписано `updatedPostalCode()`, додано методи `selectPlz()` і `closePlzDropdown()`
- **Алгоритм**: групування по PLZ + вибір найкоротшого `ort` → усуває корпоративні назви ("Sparkasse Münsterland Ost Hauptstelle Ahlen" → "Ahlen")
- **Dropdown-поведінка**: з'являється від 2 введених цифр, оновлюється з кожною новою, при виборі → заповнює PLZ + Ort + Bundesland, клік поза полем → закриває
- **Після вибору**: `selectPlz()` викликає `$this->resetValidation(['postal_code', 'city', 'state'])` — зникають помилки що з'явились під час набору часткового PLZ
- **Стиль**: dropdown на inline CSS (не Tailwind) щоб обійти можливий `overflow:hidden` батьківського контейнера; `overflow-y:scroll` з фіксованою висотою `220px`

#### Заміна бази даних PLZ
- **Стара база**: Deutsche Post PLZ-дані (23,297 записів) — містила Großkunde-PLZ (PLZ компаній: LR Health & Beauty Systems GmbH, IKK classic тощо)
- **Нова база**: [openplzapi.org](https://openplzapi.org) — відкрите API на основі офіційних даних Destatis (Федеральне статистичне відомство)
- **Нова команда**: `php artisan plz:import-openplz` → `app/Console/Commands/ImportPlzFromOpenPlz.php`
- **Результат**: **50,451 записів**, 0 помилок, тільки реальні міста та громади (Gemeinden) — жодних компаній

#### Адмін-форма (Filament MemberForm)
- `->live(onBlur: true)` додано до всіх полів секцій `monatsbeitrag`, `kontoinhaber`, `iban`, `bic`, `kreditinstitut`
- `->regex('/^[\pL\s\-]+$/u')` додано до `kontoinhaber`, `kreditinstitut`
- `->minValue(25)` + `oninput` feedback для `monatsbeitrag`

---

### [2026-05-04 13:25] Етап 2 — Сповіщення Email — Gemini
- Створено систему сповіщень: Event `MemberRegistered` та Listener `SendRegistrationEmails`.
- Налаштовано черги (Queues) для фонової відправки листів (`ShouldQueue`), щоб реєстрація відбувалась миттєво.
- Створено `MemberRegistrationConfirmation` (Mailable) — лист-підтвердження для клієнта з його `member_number`.
- Створено `NewMemberNotification` (Mailable) — сповіщення адміністратору про нову заявку (з лінком на адмінку).
- Оновлено UI/дизайн листів: замінено стандартні стилі Laravel на фірмовий зелений колір (`#059669`) та логотип/назву DITIB Ahlen.
- В `.env.example` додано плейсхолдери для налаштувань поштового сервера (`mail.ditib-ahlen-projekte.de`).

### [2026-05-04 14:09] Етап 2 — Логотип і Favicon — Gemini
- Додано оригінальний логотип (`ditib_ahlen_logo.png`) та favicon (`.png`, `.svg`).
- Логотип іконкою інтегровано в публічну сторінку (`public.blade.php`).
- Логотип та іконка додані в Filament адмінку та кабінет користувача (`AdminPanelProvider`, `MemberPanelProvider`).
- Логотип інтегровано в шапку email-повідомлень замість текстової заглушки.

### [2026-05-04 15:52] Етап 3 — Dashboard та Локалізація — Gemini
- **Локалізація**: Систему повністю переведено на німецьку мову (`APP_LOCALE=de`). Оновлено всі переклади в адмін-панелі.
- **Хлібні крихти**: Налаштовано коректне відображення статусів у навігації ресурсу Members — замість імені тепер відображаються "Vorschau" (перегляд) та "Bearbeiten" (редагування).
- **Widgets**:
  - `StatsOverview`: додано картки для відкритих заявок (жовтий), загальної кількості членів (сірий) та доходу (зелений).
  - `MembersChart`: лінійний графік реєстрацій по місяцях (тільки цілі числа на осі Y).
  - `MemberStatusChart`: кругова діаграма розподілу статусів. Додано інтерактивну легенду з цифрами та ефектом pointer-hover.
- **Dashboard UI**: Налаштовано порядок та адаптивну сітку віджетів. Видалено стандартні блоки Filament.
- **Виправлення**: Усунено конфлікти типів властивостей віджетів та виправлено помилки синтаксису в логіці діаграм.

### [2026-05-04 16:13] Реструктуризація форми — Gemini
- **Форма реєстрації**: прибрано Крок 4 (Unterschrift canvas). Форма тепер має 3 кроки.
- **DSGVO & SEPA zgoda**: перенесено з колишнього Кроку 4 на Крок 3 (Beitrag & Zahlung). Тепер згоди підтверджуються перед відправкою форми на тому ж кроці, де і платіжні дані.
- **`MembershipForm.php`**: прибрано `rulesStep4()` та валідацію `unterschrift`. Метод `submit()` тепер валідує `rulesStep3()` (включаючи `dsgvo_zustimmung` і `sepa_zustimmung`). `nextStep()` обмежено до 3 кроків.
- **Blade**: Прогрес-бар скорочено до 3 кроків. Canvas Unterschrift закоментовано в Blade-коментарі (Blade comment) для майбутнього використання.
- **PROJECT.md**: Оновлено опис форми та статус етапів. Фото профілю та Unterschrift перенесено в Етап 4.

---

*Цей файл ведеться вручну всіма агентами. Не видаляти, не перейменовувати.*
