# CHANGELOG.md — Хронологія змін
## DITIB-Ahlen-Portal

> Тут — повна історія змін: хто, коли, що зробив.
> Правила для агентів → `AGENTS.md` | Архітектура → `PROJECT.md`

---

## Три документи проекту

| Файл | Призначення |
|------|-------------|
| **`AGENTS.md`** | Правила для агентів, команди, середовище — читати першим |
| **`PROJECT.md`** | Архітектура, стек, функціональність, деплой |
| **`CHANGELOG.md`** ← ти тут | Хронологія всіх змін — що, коли, хто зробив |
| `CLAUDE.md` | Compatibility-pointer для Claude Code → посилається на `AGENTS.md` |

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

### [2026-05-04 16:25] Дозвіл дублювання email — Gemini
- **Валідація форми**: прибрано правило `unique:members,email`. Тепер один email може використовуватись для кількох членів.
- **Причина**: літні члени громади часто не мають власної електронної пошти — їх реєструють діти або родичі на свій email.
- **Зафіксовано в `PROJECT.md`**: архітектурне рішення задокументовано з нотаткою для майбутньої реалізації кабінету (`/konto`) — при magic link авторизації потрібно передбачити вибір серед кількох членів на один email.


### [2026-05-05 17:22] Документація Plesk-деплою — Codex
- Зафіксовано hosting-схему для `mitglied.ditib-ahlen-projekte.de`: окрема папка subdomain поруч із `httpdocs`, Document Root = `mitglied.ditib-ahlen-projekte.de/public`.
- Уточнено, що Laravel-портал деплоїться як весь репозиторій, а `public/build` — тільки Vite assets, не сам застосунок.
- Додано Plesk Git settings: remote repository, deploy key, server path `/mitglied.ditib-ahlen-projekte.de`, automatic/manual deployment mode і включення deploy actions.
- Додано порядок першого тестового деплою на Plesk: MySQL, серверний `.env`, одноразовий `php artisan key:generate`, `composer install`, `npm ci`, `npm run build`, `php artisan migrate --force`, cache-команди і створення Filament admin.
- Додано покрокову інструкцію створення серверного `.env` через Plesk File Manager і production-шаблон для першого тесту.
- Додано сценарій без SSH/Terminal: регулярні команди через Plesk Deploy actions, а `APP_KEY` генерується локально через `php artisan key:generate --show` і вставляється вручну в `.env`.
- Оновлено production PHP requirement до `8.3+` відповідно до `composer.json`.

### [2026-05-05 17:39] Plesk readiness перед першим деплоєм — Codex
- Зафіксовано поточний hosting-стан: MySQL database/user створені, серверний `.env` створено, Plesk Git deploy налаштовано.
- Оновлено статус Етапу 4: Plesk Git deploy налаштований, перший manual deploy ще потрібно запустити й перевірити за логами.

### [2026-05-05 17:41] Перший manual deploy — помилка Deploy actions — Codex
- Plesk успішно пройшов deploy key validation і скопіював файли в `mitglied.ditib-ahlen-projekte.de`.
- Deploy actions не виконались через `execv("/bin/bash") failed system error: Permission denied`.
- Зафіксовано діагноз: Git deploy працює, але Plesk/subscription user не має права запускати shell для additional deployment actions; потрібно увімкнути shell access (`/bin/bash` або `/bin/sh`) або просити хостинг дозволити Plesk Git deploy actions/Terminal.

### [2026-05-05 17:49] Fallback-стратегії без SSH — Codex
- Додано в `PROJECT.md` варіанти роботи, якщо хостинг не дозволить SSH/shell: дозвіл тільки для Plesk Deploy actions, Plesk Composer/Node/Scheduled Tasks/phpMyAdmin, artifact deploy із готовими `vendor/` і `public/build/`, тимчасовий захищений web-deploy endpoint або перехід на тариф/VPS із SSH.
- Зафіксовано, що artifact/web endpoint варіанти можливі, але менш бажані для Laravel production і потребують контрольованого процесу для міграцій БД.

### [2026-05-06 08:29] Artifact deploy обрано як процес деплою — Codex
- Зафіксовано в `PROJECT.md`, що поточний робочий спосіб деплою — artifact deploy через File Manager/FTP, бо Plesk Git deploy actions не можуть запускати shell-команди.
- Додано `scripts/build-artifact.sh`: локально встановлює production `vendor/`, збирає `public/build/` і створює архів `deploy-artifacts/ditib-ahlen-portal-*.tar.gz`.
- Додано `/deploy-artifacts` у `.gitignore`, щоб готові архіви не потрапляли в репозиторій.
- Описано, що artifact не містить `.env`, локальний SQLite, `node_modules`, логи і локальні cache/session/view файли; production `.env` лишається тільки на сервері.
- Додано порядок роботи з міграціями БД при artifact deploy: SQL/phpMyAdmin окремо від завантаження файлів.

### [2026-05-06 09:15] SQL export для artifact deploy — Codex
- Додано `scripts/export-production-sql.php` для створення MySQL SQL-файлів у `deploy-artifacts/`.
- Скрипт генерує окремо production-схему, PLZ-дані з локальної SQLite-бази та admin-користувача для імпорту через phpMyAdmin.
- Локальні тестові `members` не експортуються автоматично, щоб не переносити encrypted IBAN/BIC із залежністю від локального `APP_KEY`.

### [2026-05-06 09:40] Фікс DSGVO checkbox у формі — Codex
- Змінено binding для DSGVO та SEPA checkbox-ів з `wire:model.blur` на `wire:model.live`, щоб згода одразу потрапляла в Livewire перед submit.
- Додано регресійний тест, який перевіряє успішне відправлення анкети з Barzahlung і прийнятою Datenschutzerklärung.

### [2026-05-06 09:53] Оновлення PHP requirement і очищення git — Codex
- Оновлено актуальну вимогу production PHP до `8.4+` у `AGENTS.md`, `PROJECT.md` і `composer.json`.
- Видалено з git локальні робочі файли `==logs/` та `Правки і зміни на сайті.md`.
- Додано ці локальні файли/папки в `.gitignore`, щоб вони не повертались у репозиторій.

### [2026-05-06 09:55] Фікс 403 після входу в Filament admin — Codex
- Додано `FilamentUser::canAccessPanel()` у модель `User`, бо Filament на production вимагає явний дозвіл доступу до панелей.
- Обмежено доступ до `/admin` тільки користувачем `rpachkovskyi@gmail.com`; `/konto` лишено доступним для authenticated users.
- Додано тест доступу до Filament панелей для admin і member сценаріїв.

### [2026-05-06 10:15] Фікс dashboard chart для MySQL production — Codex
- Прибрано SQLite-only `strftime()` з віджета `MembersChart`, через який admin dashboard міг падати на production MySQL.
- Підрахунок реєстрацій по місяцях перенесено в PHP, щоб графік працював однаково на SQLite і MySQL.
- Додано regression test для графіка реєстрацій по місяцях.

### [2026-05-06 10:21] Artifact exclude для Markdown документації — Codex
- Оновлено `scripts/build-artifact.sh`, щоб production artifact не містив жодних `*.md` файлів.
- З artifact тепер виключаються `AGENTS.md`, `PROJECT.md`, `CHANGELOG.md`, `README.md` та markdown-файли в підпапках.
- Додатково виключено локальну папку `==logs/` з production artifact.

### [2026-05-06 10:28] Staging build без ламання локальних залежностей — Codex
- Перероблено `scripts/build-artifact.sh`: production artifact тепер збирається в тимчасовій staging-папці в `/tmp`.
- `composer install --no-dev`, `npm ci` і `npm run build` виконуються тільки в staging, тому локальні `vendor/`, `node_modules/` і `public/build/` не змінюються.
- Оновлено `PROJECT.md` з новим стабільним циклом локальна робота → artifact build → production deploy → локальні правки.

### [2026-05-06 10:32] Документація artifact-процесу для агентів — Codex
- Додано в `AGENTS.md` окремий розділ про staging artifact build.
- Зафіксовано заборону запускати `composer install --no-dev` і `npm ci` у робочій папці для production-збірки.
- Описано стабільний цикл: локальні тести → `scripts/build-artifact.sh` → deploy archive на Plesk → подальші локальні правки без відновлення залежностей.

### [2026-05-06 10:35] Фікс permissions у staging artifact — Codex
- Виявлено, що staging root з `mktemp` пакувався в artifact як `./` з правами `700`, через що Plesk/Apache міг віддавати `403 Forbidden` після розпакування.
- Додано normalization permissions у `scripts/build-artifact.sh`: директорії `755`, файли `644`, `artisan` і shell-скрипти `755`.
- Оновлено `AGENTS.md` і `PROJECT.md` з правилом перевірки tar permissions перед production deploy.

### [2026-05-06 10:41] Production deploy перевірено після fixes — Codex
- Нова staging-збірка успішно розпакована на production: сторінки порталу знову завантажуються без Apache `403 Forbidden`.
- Помилка Filament admin dashboard "Fehler beim Laden der Seite" більше не з'являється після виправлення `MembersChart` для MySQL.

### [2026-05-06 10:49] Дружній формат IBAN у формі — Codex
- Додано `App\Support\Iban` для normalization, display-formatting і структурної валідації IBAN.
- Публічна анкета тепер приймає IBAN з пробілами та малими літерами, показує його як `DE 42 4005 0150 0068 0009 59`, а в БД зберігає canonical IBAN без пробілів.
- Filament admin form отримав такий самий формат відображення та dehydrate до canonical IBAN.

### [2026-05-06 11:02] Додано другого адміністратора — Codex
- Додано `info@ditib-ahlen-projekte.de` до allow-list для доступу в Filament AdminPanel (`/admin`).
- Створено локального admin-користувача `info@ditib-ahlen-projekte.de` з паролем `AhlenDitib2026!`.
- Підготовлено ignored SQL-файл для phpMyAdmin production import: `deploy-artifacts/add-admin-info-ditib-ahlen-projekte-20260506-1102.sql`.
- Оновлено regression test для доступу до admin panel двома дозволеними email-адресами.

### [2026-05-06 11:10] Фікс production email без queue worker — Codex
- Прибрано `ShouldQueue` з registration email listener-а та mailables, щоб листи не зависали в таблиці `jobs` на Plesk без queue worker-а.
- Явно зареєстровано `MemberRegistered` → `SendRegistrationEmails` у `AppServiceProvider`.
- Додано логування SMTP-помилок у listener, щоб збій пошти не ламав збереження анкети.
- Додано regression test: після submit лист члену та лист адміну відправляються синхронно і нічого не queue-иться.
- Оновлено `PROJECT.md`: реєстраційні листи тепер описані як synchronous SMTP, не Queue.

### [2026-05-06 11:21] Фікс дублювання registration email — Codex
- Прибрано зайву явну реєстрацію `MemberRegistered` listener-а з `AppServiceProvider`, бо Laravel event discovery уже знаходить `SendRegistrationEmails`.
- Оновлено regression test: після submit має відправлятись рівно один лист члену і рівно один лист адміну.

### [2026-05-06 11:29] Email при підтвердженні та видаленні — Codex
- Додано email члену при зміні статусу заявки на `active`.
- Додано email адміну при видаленні запису члена як мінімальну фіксацію дії до повного audit-log.
- Додано Markdown-шаблони та Mailable-класи для approval/deletion повідомлень.
- Додано regression tests для approval email, deletion email і рендерингу нових email-шаблонів.
- Зафіксовано майбутню задачу глобального audit-log у `Правки і зміни на сайті.md`.

### [2026-05-06 13:18] Email клієнту при видаленні та фікс логотипу листів — Codex
- Додано `MemberDeletedNotification`: при видаленні запису клієнт теж отримує email-повідомлення.
- Оновлено delete lifecycle: адміну і клієнту листи відправляються окремо, з окремим логуванням SMTP-помилок.
- Додано `MAIL_LOGO_URL` у `.env.example` і `config/mail.php`; email header тепер використовує стабільний production HTTPS URL логотипу.
- Додано regression tests для клієнтського delete email і присутності logo URL у rendered email HTML.
- Оновлено `PROJECT.md` і `Правки і зміни на сайті.md`.

### [2026-05-06 13:45] Inline logo для email header — Codex
- Перевірено, що production PNG логотипу доступний через HTTPS, але поштовий клієнт показував alt-текст замість картинки.
- Оновлено mail header: логотип тепер вбудовується як inline/CID image через `$message->embed(...)`, з fallback на `MAIL_LOGO_URL`.
- Додано email-safe атрибути `width`, `height`, `display:block`, `border:0` для стабільнішого рендерингу в поштових клієнтах.

### [2026-05-06 14:08] План інтеграції старих Excel-даних — Codex
- Додано `LEGACY_IMPORT.md` з рішенням не змішувати стару історичну нумерацію з поточним `member_number`.
- Зафіксовано рекомендовану схему імпорту: окреме поле `legacy_member_number`, мобільний номер у `phone`, звичайний номер у майбутнє поле `phone_landline`.
- Оновлено `PROJECT.md` коротким архітектурним рішенням і посиланням на документ інтеграції legacy-даних.

### [2026-05-06 14:33] Централізація брендування email — Codex
- Зведено брендування Laravel Markdown emails в один стандартний шар: `config/mail.php` → `mail.brand.*`, спільні шаблони `resources/views/vendor/mail/`, окремі `resources/views/emails/` лишаються тільки контентом.
- Прибрано inline/CID logo як основний механізм; HTML header використовує стабільний `MAIL_LOGO_URL`, `MAIL_BRAND_NAME`, `MAIL_BRAND_URL` і спільний footer.
- Оновлено `.env.example`, `PROJECT.md` і `Правки і зміни на сайті.md` з поточною email-архітектурою та майбутнім варіантом переходу на власний HTML-шаблон.
- Додано regression assertion, що email header/footer рендериться через централізований branding layer без `cid:`.

### [2026-05-06 14:47] Email branding лишено відкритим питанням — Codex
- Зафіксовано, що останні зміни email branding не дали очікуваного результату в production/реальному поштовому клієнті.
- Додано примітку, що перші листи історично приходили з логотипом, але поточна причина regression поки не встановлена.
- Оновлено `PROJECT.md` і `Правки і зміни на сайті.md`: централізований Laravel Markdown mail-layer лишається структурним поточним підходом, але logo rendering не вважати production-fixed.
- Питання залишено відкритим через пріоритет часу; до повного HTML email template повернутись пізніше за потреби.

### [2026-05-06 14:54] Неповторювані member numbers і soft delete — Codex
- Додано `member_number_sequences` як окремий транзакційний лічильник номерів членів із `lockForUpdate()`.
- Перенесено генерацію `member_number` з пошуку останнього запису на `MemberNumberSequence`, який також перевіряє soft-deleted записи й переступає вже зайняті номери.
- Увімкнено soft delete для `members`, щоб видалені записи зберігали свій номер і могли бути відновлені.
- Додано Filament-фільтр видалених записів, колонку `Gelöscht am` і restore actions.
- Додано regression tests для послідовної видачі номерів, невикористання номера після delete і захисту від sequence drift.
- Оновлено `AGENTS.md` і `PROJECT.md` з новим правилом: `member_number` ніколи не перевикористовується.

### [2026-05-06 15:03] Production stack зафіксовано як artifact + SQL/phpMyAdmin — Codex
- Зафіксовано в `AGENTS.md` і `PROJECT.md`, що стандартний production deploy stack: artifact upload через Plesk File Manager/FTP і SQL import через phpMyAdmin.
- Прибрано двозначність навколо SSH/Terminal/Deploy actions: агентам більше не потрібно щоразу питати про SSH, якщо користувач явно не змінить це рішення.
- Підготовлено SQL для production-міграції member number sequence + soft deletes: `deploy-artifacts/production-member-number-sequence-soft-deletes-20260506-150252.sql`.
- Оновлено `scripts/export-production-sql.php`, щоб повний SQL schema export містив `members.deleted_at` і `member_number_sequences`.

### [2026-05-06 15:05] Фікс collation у production SQL міграції — Codex
- Оновлено `deploy-artifacts/production-member-number-sequence-soft-deletes-20260506-150252.sql`, щоб порівняння `migrations.migration` з `@migration_name` явно використовувало `utf8mb4_unicode_ci`.
- Додано явний collation також для перевірки `member_number_sequences.name`, щоб повторний імпорт через phpMyAdmin був стабільним на БД з іншим default collation.

### [2026-05-06 15:26] Admin lifecycle через статус Inaktiv — Codex
- Прибрано delete actions з адмінського UI членів: row menu, bulk actions і edit page header більше не показують `Löschen`.
- Прибрано `Anzeigen` з row action меню; лишено `Bearbeiten` і окрему статусну групу швидких дій.
- Додано row actions для переведення запису в `pending`, `active` або `inactive`, приховуючи поточний статус.
- Додано bulk status actions у `Mehrfachaktionen`; масове редагування не додається, масове видалення не показується.
- Оновлено `Monatliche Einnahmen`: тепер враховує тільки членів зі статусом `active`.
- Додано regression test для revenue-віджета, щоб `pending` і `inactive` не потрапляли в суму.
- Оновлено `AGENTS.md` і `PROJECT.md`: нормальний admin workflow — переводити членів у `inactive`, не видаляти через UI.

### [2026-05-06 15:49] Полірування admin member UX — Codex
- Явно налаштовано row click у таблиці членів на сторінку перегляду запису, без повернення в edit через приховану дію.
- У статусних row/bulk actions узгоджено підписи й іконки з існуючими статусами `Ausstehend`, `Aktiv`, `Inaktiv`.
- На edit page додано верхні кнопки `Änderungen speichern` і `Abbrechen`, щоб зберігати без прокрутки вниз.
- Усі `Änderungen speichern` кнопки зроблено в зеленому `success` стилі.
- Поля `zustimmung_at`, `SEPA-Lastschriftmandat` і `Datenschutzerklärung` зроблено read-only у edit UI; save backend додатково не приймає зміни цих полів.
- Перевірено локальний admin UI в браузері: row click веде на view, actions/bulk menus не містять `Löschen`/`Anzeigen`, edit page має top/bottom save-cancel.

### [2026-05-06 16:06] Admin status action colors — Codex
- Для статусних actions у row menu і bulk menu додано точні `oklch` кольори з badge-тегів: `Aktiv` зелений `oklch(0.527 0.154 150.069)`, `Ausstehend` оранжевий `oklch(0.555 0.163 48.998)`.
- Додано вузький admin CSS hook, щоб Filament не перебивав колір тексту та іконок у dropdown actions.
- Усі видимі `Bearbeiten` actions залишено в сірому стилі як `Abbrechen`.

### [2026-05-06 19:35] Root cause production email logo — Codex
- Встановлено реальну причину відсутності логотипу в production листах: `scripts/build-artifact.sh` через `tar --exclude='./vendor'` виключав не тільки root `vendor/`, а й вкладену папку `resources/views/vendor/mail/` з Laravel Markdown mail overrides.
- Пояснено різницю з локальним тестом: локально `resources/views/vendor/mail/` існує, тому листи рендеряться брендовано; production artifact приходив без цієї папки, тому Laravel використовував стандартні mail templates.
- Оновлено `scripts/build-artifact.sh`: після staging-copy mail override templates явно копіюються назад у `resources/views/vendor/mail/`.
- Dry-run перевірка підтвердила, що `resources/views/vendor/mail/html/header.blade.php`, `themes/default.css` і `text/message.blade.php` потрапляють у staging-копію.
- Оновлено `PROJECT.md` і `Правки і зміни на сайті.md` з правильним діагнозом і перевіркою після наступного artifact deploy.

### [2026-05-07 13:28] Форматування і нормалізація телефонів — Codex
- Додано `App\Support\PhoneNumber` для нормалізації телефонів у стабільний формат із `+` та цифрами.
- Публічна форма тепер форматує телефон на blur, приймає типові варіанти `0176...`, `0049...`, `02382/123456` і міжнародні номери з `+`.
- Filament member form і таблиця членів показують телефон читабельно, але зберігають нормалізоване значення в БД.
- Додано regression tests для німецьких мобільних/стаціонарних форматів, міжнародного `+90` і submit форми з messy phone input.

### [2026-05-07 13:37] Обов'язкова Vorwahl для телефонів — Codex
- Посилено phone validation: короткі номери без Vorwahl більше не нормалізуються автоматично.
- Введення на кшталт `2382 123456` або `176 12345678` тепер дозволяється як німецький номер без першого `0` і нормалізується в `+49...`.
- Введення на кшталт `492382123456` лишається недійсним: для country code потрібен явний `+49` або `0049`.
- Оновлено повідомлення помилки і placeholder у публічній формі: користувач має ввести номер з Vorwahl, наприклад `02382 123456`, `2382 123456` або `+49 2382 123456`.
- Додано regression tests для номерів без першого `0`, коротких неповних номерів і country-code-without-plus input.

### [2026-05-07 13:41] Телефон без першого нуля дозволено — Codex
- Уточнено phone formatter після аналізу UX: німецькі номери з Vorwahl без початкового `0` приймаються й нормалізуються.
- `2382 123456` форматується як `+49 2382 123456`, а `176 12345678` як `+49 176 1234 5678`.
- Повний тест-набір пройшов після зміни правила.

### [2026-05-09 16:10] Мінімальний членський внесок 10 євро — Codex
- Змінено мінімальний `monatsbeitrag` з 25 € на 10 € у публічній Livewire-формі та Filament admin form.
- У публічній формі додано вибір внеску пресетами 10/15/20/25 € і ручний ввід із помилкою для сум менше 10 €.
- Кнопки пресетів оформлено як компактні pill-кнопки у 2x2 сітці для телефону й desktop.
- Додано більше повітря між pill-кнопками, вирівняно висоту ручного поля суми, крок ручного вводу змінено на 1 €, а стандартною Zahlungsweise зроблено `Dauerauftrag`.
- Додано regression tests для внеску 10 € і відхилення суми нижче мінімуму.

### [2026-05-18 14:49] Очищення зайвої Claude git-гілки — Codex
- Видалено локальний Claude worktree `claude/epic-ellis-6d2d80`, який дублював поточний коміт `main`.
- Видалено локальну гілку `claude/epic-ellis-6d2d80`; у порталі лишилась основна гілка `main` з tracking на `origin/main`.

### [2026-05-18 14:59] Legal links і згоди у Mitgliedsantrag — Codex
- Додано у футер публічної форми посилання `Impressum` і `Datenschutz` на основний сайт із відкриттям у новій вкладці.
- Додано посилання на Datenschutzerklärung у DSGVO checkbox і в SEPA-текст для Lastschrift.
- Виправлено логіку `Dauerauftrag`: SEPA-мандат, IBAN і банківські поля тепер стосуються тільки `Lastschrift`.
- Перевірено фактичну Datenschutzerklärung на сайті: поточний документ описує проектний сайт, але не покриває окремо Mitgliedsantrag, SEPA/IBAN і членські дані.

### [2026-05-18 15:21] Фіксація локальних портів DITIB Ahlen — Codex
- Зафіксовано в `AGENTS.md`, `CLAUDE.md` і `PROJECT.md` остаточне правило: лендінг `main` працює на `localhost:8082`, портал `portal` працює на `localhost:8000`.
- Явно заборонено переносити портал у Docker Desktop або повертати старі порти `8083`, `5173` чи `8383`.
- Зупинено й видалено старий Docker-контейнер `ditib-ahlen-portal`, який помилково слухав `8083` і `5173`.

### [2026-05-18 15:39] Полірування публічної форми — Codex
- Додано надійний відступ і розділювач між footer links `Impressum` та `Datenschutz`.
- Оновлено телефонний placeholder і validation message: спочатку міжнародний формат, потім німецький формат із `0`.
- Дозволено короткі місцеві Ahlen-номери без показу їх у прикладі; вони нормалізуються з Vorwahl `02382`.
- Перероблено кнопки місячного внеску: desktop у 4 колонки на ширину блоку, mobile у 2x2 з центруванням.

### [2026-05-18 15:49] Soft validation для кроків анкети — Codex
- `Weiter` більше не блокує перехід між кроками: поточний крок перевіряється, помилки показуються, але користувач може продовжити заповнення.
- Додано summary-повідомлення над формою і червоні маркери на кроках, де вже знайдено помилки.
- Кружечки кроків зроблено клікабельними для швидкого повернення до потрібного розділу.
- При `Antrag absenden` виконується повна перевірка всіх кроків; якщо є помилки, форма повертає користувача до першого проблемного кроку.

### [2026-05-18 15:55] Стилізація soft validation станів — Codex
- Error-кружечки кроків зроблено білими з червоною рамкою і піднято поверх progress-line.
- Текстові labels кроків із помилками зроблено нейтрально темними замість червоних.
- Summary-повідомлення про помилки переведено з червоного оформлення в сірий, спокійніший alert.

### [2026-05-18 16:04] Уточнення показу validation summary — Codex
- Summary-повідомлення над анкетою тепер показується тільки тоді, коли помилки залишились у попередніх кроках.
- Якщо користувач повернувся, виправив попередній крок і натиснув `Weiter`, summary автоматично зникає.
- Поточний крок більше не дублює власні inline-помилки загальним повідомленням.

### [2026-05-18 16:18] Документація останніх правок форми — Codex
- Оновлено `PROJECT.md` з актуальною поведінкою публічної форми: м'яка валідація `Weiter`, фінальне повернення до першої помилки, неклікабельні step indicators і сіре summary тільки для помилок у попередніх кроках.
- Задокументовано актуальний phone UX: у прикладі показуються `+49...` і `0...`, а короткі місцеві Ahlen-номери приймаються без показу в placeholder.
- Задокументовано legal links у футері і посилання на Datenschutzerklärung у текстах SEPA/DSGVO-згод.
- Зафіксовано поточний UX для `Monatlicher Mitgliedsbeitrag`: desktop/tablet в один ряд, mobile у 2x2.
- Зафіксовано ризик по legal content: поточна Datenschutzerklärung на лендінгу ще потребує доповнення під Mitgliedsantrag, SEPA/IBAN, членські дані й портал.

### [2026-05-18 16:23] Фікс inline-помилок адреси — Codex
- Прибрано очищення validation errors з `closePlzDropdown()`, яке спрацьовувало через `click.outside` при натисканні `Weiter` і ховало помилки `Postleitzahl`, `Ort`, `Bundesland`.
- Повернуто попередній вигляд summary без списку `Offene Felder`; помилки мають показуватись inline під відповідними полями на кроці адреси.
- Додано regression test: закриття PLZ dropdown більше не очищає помилки адресних полів.

### [2026-05-18 16:32] Відступи validation summary — Codex
- Додано додаткові 5px внутрішнього відступу зверху і знизу в сірому validation summary, щоб текст не притискався до рамки.

### [2026-05-18 16:33] Уточнення padding validation summary — Codex
- Встановлено точні inline-значення `padding-top: calc(0.75rem)` і `padding-bottom: calc(0.75rem)` для сірого validation summary.

### [2026-05-18 16:36] Фікс Alpine input handlers — Codex
- Виправлено `x-on:input` handlers у публічній формі: замість Alpine `this.value` використовується `$event.target.value`, щоб прибрати console errors `Cannot read properties of undefined`.
- Оновлено custom validity handler для ручного внеску на `$event.target.setCustomValidity(...)`.
- Перевірено, що в Blade більше немає Alpine `x-on:input` з `this.*`; нативні `oninput` handlers для PLZ і Telefon лишаються без змін.

### [2026-05-18 16:41] Підсумкова документація етапу правок форми — Codex
- Оновлено `PROJECT.md` як джерело поточного стану публічної форми після великого етапу UX/validation/legal правок.
- Зафіксовано стабільний сценарій soft validation: `Weiter` показує inline-помилки, не блокує рух далі, а `Antrag absenden` повертає до першої помилки.
- Зафіксовано правила для step indicators, validation summary, inline-помилок адреси й поведінки PLZ dropdown.
- Зафіксовано frontend-рішення для Alpine input handlers: у `x-on:input` використовувати `$event.target`, щоб уникати runtime errors у консолі.
- Підсумовано, що цей етап охопив legal links/DSGVO-посилання, phone UX, contribution presets, адресні помилки, summary spacing і JS console cleanup.

### [2026-05-18 16:50] Production redeploy checklist для існуючої БД — Codex
- Додано в `PROJECT.md` короткий Plesk redeploy-чекліст для ситуації, коли production БД уже містить реєстрації.
- Зафіксовано: перед deploy зробити backup MySQL через phpMyAdmin, на сервері не видаляти `.env`, не міняти `APP_KEY`, не чіпати БД без нових міграцій.
- Зафіксовано сценарій file-only deploy: видалити старі файли Laravel-порталу, залишити `.env`, завантажити й розпакувати artifact у `mitglied.ditib-ahlen-projekte.de/`, потім перевірити форму, `/admin` і старі реєстрації.

### [2026-05-19 08:22] Admin Mitglieder header actions — Codex
- Вимкнено глобальний пошук у header адмін-панелі та замінено його кнопкою переходу до публічної форми реєстрації в новій вкладці.
- На сторінці Mitglieder прибрано header-кнопку `Erstellen`.
- Поле пошуку над таблицею Mitglieder розширено до 24rem, приблизно на 50% від стандартної ширини.

### [2026-05-19 08:36] Admin footer links і pagination default — Codex
- Додано `Impressum` і `Datenschutz` у footer адмін-панелі з відкриттям у новій вкладці.
- Для таблиці Mitglieder встановлено стандартний показ 25 записів на сторінку.

### [2026-05-19 08:47] Єдине джерело brand color для Filament — Codex
- Додано `App\Support\BrandColors` як PHP-джерело основного brand color `#009689`.
- Admin panel і Member panel переведено на спільний `BrandColors::primary()` замість окремих `Color::Amber` / `Color::Teal`.
- Admin primary controls тепер використовують бірюзову Filament palette у стилі публічної форми.

### [2026-05-19 08:55] Brand style для primary кнопок адмінки — Codex
- Додано CSS variables для brand primary button у Filament style hook на базі `App\Support\BrandColors`.
- Кнопки `Neue Registrierung` і `Änderungen speichern` отримали однаковий brand style: фон `#009689`, hover `teal-700`, білий текст та іконки.
- Save actions на edit page переведено з semantic `success` на brand `primary`, щоб не розходились зі стилем публічної форми.

### [2026-05-19 08:59] Глобальний brand стиль для primary Filament buttons — Codex
- Розширено admin CSS: усі Filament-кнопки з `fi-color-primary` автоматично отримують brand style `#009689`, hover `teal-700`, білий текст та іконки.
- Майбутні кнопки з `color('primary')` або `color="primary"` тепер не потребують окремого класу для базового brand вигляду.
- Це покриває системні Filament-кнопки на кшталт `Filter anwenden` і `Spalten anwenden`.

### [2026-05-19 09:07] Brand style для radio і checkbox публічної форми — Codex
- Публічний layout тепер прокидає CSS variables `--ditib-brand-*` з `App\Support\BrandColors`.
- Додано reusable CSS-клас `ditib-choice-input` для radio/checkbox з явним `accent-color: #009689`.
- Radio-поля `Cenaze Fonu`, `Gemeinderegister` і checkbox-згоди SEPA/DSGVO переведено на спільний brand-клас.

### [2026-05-19 09:10] Документація brand styling і admin UX — Codex
- Оновлено `PROJECT.md` з актуальним описом brand source of truth: `App\Support\BrandColors`, Filament primary palette і CSS variables.
- Задокументовано глобальне правило для Filament primary buttons: `#009689`, hover `teal-700`, білий текст/іконки.
- Задокументовано reusable `ditib-choice-input` для radio/checkbox у публічній формі та актуальні admin UX зміни: header button, без `Erstellen`, пошук 24rem, default pagination 25, footer legal links.

### [2026-05-19 09:36] Оновлення логіки статусів членів — Codex
- Додано централізований `MemberStatus` helper: `pending` тепер показується як `Neu`, новий `processing` як `Verarbeitung`, обидва з warning-кольором.
- Оновлено Filament таблицю, фільтр, форму редагування, status chart і dashboard: `Offene Anträge` рахує `Neu + Verarbeitung`.
- Швидкі й масові admin actions для статусів обмежено переходами в `Aktiv` або `Inaktiv`; системні стани лишаються видимими в edit form.
- Додано MySQL-міграцію для enum `members.status` без зміни існуючих `pending` записів і підготовлено SQL-файл для phpMyAdmin у `deploy-artifacts/`.
- Перевірено повним PHPUnit-набором, включно з lifecycle email tests.

### [2026-05-19 10:34] Технічний system label і автооновлення версії — Codex
- Додано `v1.035 - Update: 19.05.2026 - by Munas-Print` як єдиний system label на базі `config/system-version.json` і `App\Support\SystemInfo`; `Munas-Print` відкриває `https://munas.online/` у новій вкладці.
- Публічна форма показує label одразу під формою по центру; адмінка показує label після таблиці списку Mitglieder справа, у стилі реквізитів футера.
- Для адмінки зменшено верхній відступ system label до `0px` і compact gap Filament stack до `calc(var(--spacing) * 3)`.
- Додано `scripts/update-system-version.php`; `scripts/build-artifact.sh` і `scripts/export-production-sql.php` автоматично піднімають patch-номер і дату перед build/DB export.
- Оновлено `PROJECT.md` і перевірено повним PHPUnit-набором.

### [2026-05-19 11:23] Актуалізація roadmap-документації — Codex
- Оновлено `Правки і зміни на сайті.md`: позначено явно виконані пункти форми, email branding, admin UX, footer/header, статуси й branding адмінки.
- Прибрано активний план legacy Excel-імпорту: видалено `LEGACY_IMPORT.md` і секцію legacy import з `PROJECT.md`.
- Додано в Етап 4 майбутню функцію експорту таблиці членів громади в Excel-файл (`.xlsx`) із завантаженням на ПК.

### [2026-05-19 11:48] Instagram поле в Mitgliedsantrag — Codex
- Додано необов'язкове поле `instagram` у `members` із міграцією та production SQL для phpMyAdmin.
- Публічна форма приймає Instagram username, `@username` або Instagram URL і зберігає нормалізований username без `@`.
- Додано поле Instagram у Filament edit/view form і приховану за замовчуванням колонку таблиці з клікабельним посиланням.
- Додано regression tests для нормалізації, валідації та збереження Instagram input.

### [2026-05-19 12:15] ТЗ для фото профілю в реєстрації — Codex
- Створено робочий документ `FOTO_UPLOAD_TZ.md` для планування optional фото профілю в Mitgliedsantrag.
- Зафіксовано рішення: фото 1:1, optional, mobile-first upload з камери/галереї, crop перед збереженням, private storage, 800x800 оптимізація, доступ тільки для адміна і самого члена.
- Описано backend-ризики: HEIC/HEIF, великі файли, private file access, production backup/storage, DSGVO і admin correction workflow.
- Додано список відкритих питань перед реалізацією.

### [2026-05-19 12:20] Аналіз Filament FileUpload для фото профілю — Codex
- Доповнено `FOTO_UPLOAD_TZ.md` розділом про використання Filament/FilePond для фото.
- Зафіксовано, що Filament суттєво спрощує admin upload/display: `FileUpload`, image editor/crop, private visibility, `ImageEntry` і `ImageColumn`.
- Зафіксовано обмеження: публічна форма зараз custom Livewire/Blade, тому mobile upload/crop може потребувати окремої реалізації або proof-of-concept з Filament Form component.

### [2026-05-19 12:28] Технічне рішення для public photo upload — Codex
- Оновлено `FOTO_UPLOAD_TZ.md` під рекомендовану архітектуру: admin через Filament, public form через Cropper.js + Alpine + Livewire `WithFileUploads`, backend normalization через Intervention Image.
- Зафіксовано private storage pattern `storage/app/private/member-photos/{member_number}/{member_number}-profile.jpg`.
- Вирішено не додавати ім'я людини у filename, щоб не дублювати персональні дані в шляху файлу й уникнути проблем зі спецсимволами.
- Зафіксовано baseline output для v1: optimized JPEG 800x800.

### [2026-05-19 13:30] Локальний Laravel storage symlink — Codex
- Перевірено symlink-и в `portal` після проблеми з індексацією workspace.
- Перебудовано локальний `public/storage`: замість Docker-шляху `/var/www/storage/app/public` він тепер веде на локальний `storage/app/public`.
- Підтверджено, що `public/storage` не є частиною git і лишається runtime-лінком, ігнорованим через `.gitignore`.

### [2026-05-19 13:37] Antigravity ignore для portal workspace — Codex
- Додано `.antigravityignore`, щоб Antigravity не індексував Laravel runtime/dependency папки: `vendor`, `node_modules`, `storage/framework`, `storage/logs`, `public/build`, `deploy-artifacts` і локальну SQLite DB.
- Мета: полегшити старт індексації при відкритті саме папки `portal` як окремого workspace.

### [2026-05-19 13:41] Git worktreeConfig fix для Antigravity — Codex
- Перевірено Antigravity logs: language server падав на `core.repositoryformatversion does not support extension: worktreeconfig` і `workspace infos is nil`.
- Прибрано локальний git config `extensions.worktreeConfig=true` з `.git/config`, бо репозиторій `portal` не використовує додаткові worktree.
- Це локальна зміна git metadata, не зміна коду проекту і не частина commit.

### [2026-05-19 13:43] Прибрано зайвий Antigravity ignore — Codex
- Видалено `.antigravityignore`, бо після підтвердження фіксу він не потрібен: причина збою була в локальному git metadata `extensions.worktreeConfig=true`.

### [2026-05-19 14:03] Версія в назві production artifact — Codex
- Оновлено `scripts/build-artifact.sh`: production-архів тепер отримує назву у форматі `ditib-ahlen-portal-vX.XXX-YYYYMMDD-HHMMSS.tar.gz`.
- Назва бере версію з `config/system-version.json` після автоматичного підняття patch-номера перед build.
- Оновлено `PROJECT.md` з новим форматом імені архіву.

### [2026-05-19 16:26] Перебудова ТЗ для фото профілю — Codex
- Переписано `FOTO_UPLOAD_TZ.md` як поетапне технічне завдання з чіткими етапами реалізації, acceptance criteria і release verification.
- Зафіксовано прийняті рекомендації: mobile-first public upload через Cropper.js + Alpine + Livewire, client-side JPEG 800x800, server-side validation/normalization і admin integration через Filament.
- Уточнено ризики HEIC/HEIF: browser-side crop зменшує ризик, але не гарантує підтримку; для v1 потрібен fallback із можливістю продовжити без optional фото.

### [2026-05-19 16:49] Фото профілю — Етап 1 backend foundation — Codex
- Додано `intervention/image` і backend service `ProfilePhotoService` для private JPEG 800x800 у `storage/app/private/member-photos/{member_number}/`.
- Додано міграцію `add_profile_photo_fields_to_members_table`, поля `profile_photo_path` і `profile_photo_uploaded_at`, оновлено `Member` model і production SQL для phpMyAdmin.
- Додано protected route/controller/policy для читання фото: admin має доступ до будь-якого фото, member-user до фото за email-match.
- Оновлено `scripts/build-artifact.sh`, щоб runtime profile photos не пакувалися в artifact, і `PROJECT.md`/`FOTO_UPLOAD_TZ.md` з поточним статусом та caveat щодо duplicate email ownership.
- Додано feature tests для збереження приватного JPEG і контролю доступу guest/admin/member.

### [2026-05-19 17:16] Фото профілю — Етап 2 public mobile PoC — Codex
- Додано `cropperjs` і local-only маршрут `/photo-upload-poc` для перевірки browser crop/upload flow без зміни основного Mitgliedsantrag.
- Реалізовано Alpine/Cropper.js PoC із camera/gallery input, square crop UI, JPEG export 800x800 через canvas і Livewire upload cropped blob замість original file.
- Додано fallback-помилки для non-image, too-large local input, browser render/canvas export failure і Livewire upload failure.
- Додано Livewire tests для accepted cropped JPEG і rejected non-JPEG upload.
- Перевірено headless Chrome scenario: file input → cropper canvas → `Übernehmen` → Livewire metadata `image/jpeg`, `800 x 800`; screenshot proof збережено в `/private/tmp/ditib-photo-poc-success.png`.

### [2026-05-20 11:12] Фото профілю — mobile QA PoC — Codex
- Підтверджено ручну перевірку `/photo-upload-poc` з телефона через Mac mini LAN IP `192.168.2.118`.
- Mobile flow успішно повернув Livewire metadata: `image/jpeg`, `167,2 KB`, `800 x 800`.
- Оновлено `FOTO_UPLOAD_TZ.md`: mobile QA для Етапу 2 позначено як підтверджений, LAN URL для наступних перевірок зафіксовано.

### [2026-05-20 11:41] Фото профілю — Етап 3 public form integration — Codex
- Інтегровано optional Step 4 `Foto` у реальний `MembershipForm`: progress indicators, `rulesStep4()`, `WithFileUploads`, `nextStep()`, `submit()` і nullable photo validation.
- Перенесено підтверджений Cropper.js v2 flow з PoC у public form: camera/gallery input, square crop, JPEG 800x800 через canvas, Livewire cropped upload, replace/remove і fallback-помилки.
- На final submit фото зберігається після створення `Member`, коли вже є `member_number`; backend storage і normalization проходять через `ProfilePhotoService`.
- Submit без фото працює без змін; submit з фото заповнює `profile_photo_path` і `profile_photo_uploaded_at` та створює private JPEG 800x800.
- Додано regression test для реєстрації з optional фото; перевірено browser scenario реальної форми до Step 4 з `image/jpeg`, `800 x 800`.
- Зафіксовано, що новий SQL для Етапу 3 не потрібен; фінальний combined SQL готується тільки перед production release.

### [2026-05-20 11:54] Фото профілю — Етап 4 admin Filament integration — Codex
- Додано profile photo preview у shared `MemberForm`: фото показується у View/Edit у секції `Persönliche Daten` над `Mitgliedsnummer`, без `ImageColumn` у таблиці.
- Preview відкриває фото через protected route `members.profile-photo`; direct public storage або Filament persistent private URL не використовуються.
- Додано edit header actions `Foto hochladen` / `Foto ersetzen` і `Foto entfernen`; фактичне збереження та видалення виконуються через `ProfilePhotoService`.
- Admin replace/delete не змінює SEPA/DSGVO consent facts або `zustimmung_at`, і delete photo не видаляє member record.
- Додано tests для Filament admin upload/delete actions і service regressions для replace, delete та soft delete behavior.
- Browser QA підтвердив розташування фото перед `Mitgliedsnummer` і відсутність upload/delete кнопок на view page.

### [2026-05-20 11:59] Admin profile photo preview sizing — Codex
- Обмежено ширину фото в `Persönliche Daten`: desktop до 30% ширини блоку, mobile до 50%, із лівим вирівнюванням.
- Preview лишається квадратним через `aspect-square` і не розтягується на всю ширину секції.

### [2026-05-20 12:02] Admin profile photo preview sizing fix — Codex
- Виправлено sizing preview через scoped CSS у Blade partial, бо Tailwind utility-класи в Filament view не обмежили ширину стабільно.
- Browser QA на `/admin/members/12` підтвердив: preview 128x128 px, приблизно 29% ширини блоку `Persönliche Daten`, вирівняний зліва.

### [2026-05-20 12:18] Admin profile photo mobile fixes — Codex
- Прибрано порожній photo-slot для записів без фото: у View/Edit не показується ні placeholder, ні `Kein Foto`.
- Зроблено `Mitgliedsnummer` nowrap, щоб на mobile вона лишалась в одному рядку.
- Для admin photo preview додано cache busting через `?v=profile_photo_uploaded_at` і змінено response header на `no-store`, щоб після replace/delete не показувалось старе фото.
- Mobile CSS для photo preview тепер дає 50% ширини без max-width, desktop лишається 30%.
- Browser QA підтвердив відсутність `Profilfoto`/`Hochgeladen am`, один рядок `Mitgliedsnummer`, versioned image URL і відсутність photo-slot у записі без фото.

### [2026-05-20 13:06] Admin profile photo grid span fix — Codex
- Явно задано `columnSpan(['default' => 'full', 'sm' => 'full'])` для profile photo preview і `Mitgliedsnummer`, щоб вони не потрапляли тільки в ліву колонку Filament section grid.
- Browser DOM QA підтвердив, що grid column тепер має `--col-span-default: 1 / -1` і `--col-span-sm: 1 / -1`.

### [2026-05-20 13:20] Admin profile photo separate layout — Codex
- Перебудовано `Persönliche Daten`: фото винесено в окремий `View` layout, `Mitgliedsnummer` лишається окремим placeholder, а звичайні поля перенесено у вкладений `Grid`.
- Фото більше не належить до двоколонкової сітки даних; DOM QA підтвердив, що nested fields grid не містить photo preview.
- Оновлено `FOTO_UPLOAD_TZ.md` і `PROJECT.md` із поточним статусом Етапу 4 та рішенням щодо layout.

### [2026-05-20 15:42] Фото профілю — Етап 5 member panel display — Codex
- Зафіксовано `/konto` access model v1: authenticated user бачить усі `Member` записи з тим самим email, що покриває родину або фірму на одній пошті.
- Додано read-only member-panel resource `MemberAccountResource` зі списком `Meine Mitgliedschaften` і scoped query/route binding за email поточного користувача.
- `/konto` перенаправляє на список `Meine Mitgliedschaften`; записи з іншою поштою не рендеряться і не відкриваються через URL.
- У view доступного запису показується profile photo через protected route; якщо фото немає, photo block не рендериться.
- Self-service edit/replace фото не додано у v1; майбутній `Änderungsantrag` має працювати від конкретного `member_id`.
- Додано feature tests для multi-member same-email access, заборони іншого email, redirect `/konto`, photo display і відсутності photo-slot без фото.

### [2026-05-20 16:15] Änderungsantrag ownership rule — Codex
- Зафіксовано в `PROJECT.md` і `FOTO_UPLOAD_TZ.md`: доступ у `/konto` може йти через email, але кожен майбутній `Änderungsantrag` має бути прив'язаний до конкретного `member_id` / `member_number`.
- Додано backlog-правило: перед створенням або переглядом запиту на зміну треба перевіряти, що обраний member record належить до email authenticated user.

### [2026-05-20 16:20] Änderungsantrag backend security clarification — Codex
- Уточнено формулювання в `PROJECT.md` і `FOTO_UPLOAD_TZ.md`: перевірка `member_id` для майбутнього `Änderungsantrag` є backend security rule проти ручної підстановки URL/request payload, а не обмеженням бізнес-логіки доступу.
- Бізнес-логіка лишається такою: користувач у `/konto` має доступ до всіх записів із email authenticated user.

### [2026-05-20 16:45] Фото профілю — Етап 6 Datenschutz і deploy safety — Codex
- Звірено `../main/docs/legal-texts.md`: optional profile photo потребує окремої Einwilligung checkbox, а не тільки загальної DSGVO-згоди.
- Додано окрему photo consent у public Step 4: без фото submit працює без неї, з фото вона required і фіксується в `profile_photo_zustimmung` / `profile_photo_zustimmung_at`.
- Додано міграцію `2026_05_20_144219_add_profile_photo_consent_fields_to_members_table`, production SQL для phpMyAdmin і оновлено full SQL export schema.
- Видалення фото через `ProfilePhotoService` очищає photo consent fields; admin edit показує ці поля read-only і попереджає про необхідність Einwilligung при admin upload.
- Додано `deploy-artifacts/check-photo-extensions.php` для ручної Plesk-перевірки `gd`, `fileinfo`, GD JPEG функцій і рекомендованого `exif`; після перевірки файл треба видалити з production.
- Посилено `scripts/build-artifact.sh` guard: runtime `storage/app/private/member-photos` не може потрапити в production artifact.
- Оновлено `PROJECT.md` і `FOTO_UPLOAD_TZ.md`: backup має включати MySQL і private photo folder, фінальний SQL краще об'єднати перед import, audit log для фото перенесено в майбутні покращення.

### [2026-05-20 17:26] Profile photo external data disk — Codex
- Після ручної Plesk backup-перевірки зафіксовано, що subscription backup включає `Home directory/ditib-portal-data/member-photos` поруч із `httpdocs` і папкою порталу.
- Додано Laravel filesystem disk `member_photos`: локальний fallback пише в `storage/app/private`, production root задається через `MEMBER_PHOTOS_ROOT`.
- `ProfilePhotoService` переведено з disk `local` на `member_photos`; DB relative paths `member-photos/...` не змінюються.
- Оновлено `.env.example`, `PROJECT.md`, `FOTO_UPLOAD_TZ.md` і tests під новий photo disk.
- Production `.env` має містити `MEMBER_PHOTOS_ROOT=/var/www/vhosts/ditib-ahlen-projekte.de/ditib-portal-data` або фактичний absolute path Plesk Home directory.

### [2026-05-20 17:36] Member public route key — Codex
- Додано `Member::getRouteKeyName()` з `member_number`, щоб public URLs більше не використовували auto-increment `id`.
- `/admin/members/{record}`, `/konto/mitgliedschaften/{record}` і protected photo route тепер генерують/резолвлять записи через `DA-YYYY-NNNN`.
- Додано regression tests для admin view URL, member-panel view URL і profile-photo route: `member_number` відкривається, numeric `id` повертає 404.
- Зафіксовано в `PROJECT.md`, що `id` лишається внутрішнім PK і не має штучно збігатися з `member_number`.
- Підготовлено phpMyAdmin readiness check `deploy-artifacts/production-check-member-number-route-key-readiness.sql`; production уже підтверджено без записів із порожнім `member_number`.

### [2026-05-20 17:52] Фото профілю — закриття відкритих рішень — Codex
- Закрито відкриті рішення в `FOTO_UPLOAD_TZ.md`: фото не додаємо в admin table як `ImageColumn`, а лишаємо тільки у View/Edit.
- `Foto geprüft` не входить у v1; перенесено в майбутні покращення, якщо фото пізніше використовуватимуться для друку/PDF/карток.
- Для майбутнього PDF v1 зафіксовано: якщо фото відсутнє, placeholder не показувати, photo block пропускати.

### [2026-05-20 18:16] Фото профілю — deploy artifacts cleanup — Codex
- У `deploy-artifacts/` залишено тільки потрібні файли для майбутнього production-підготування: `check-photo-extensions.php` і `production-photo-upload-release-20260520.sql`.
- Об'єднано проміжні SQL для photo fields і photo consent у один phpMyAdmin import-файл із записами в `migrations`.
- Позначено в `FOTO_UPLOAD_TZ.md`, що Datenschutz-сторінка лендінгу вже оновлена, а фінальний SQL підготовлено; актуальним лишається тільки ручний Plesk extension check.

### [2026-05-20 18:26] Фото профілю — локальна release verification — Codex
- Виконано фінальну локальну перевірку перед production deploy: `./vendor/bin/phpunit`, `npm run build`, `scripts/build-artifact.sh`.
- Production artifact зібрано як `deploy-artifacts/ditib-ahlen-portal-v1.039-20260520-182520.tar.gz`.
- Перевірено tar permissions: root `./` має `drwxr-xr-x`; artifact містить `vendor/autoload.php`, `public/build/manifest.json` і mail override templates.
- Перевірено, що artifact не містить `.env`, `.phpunit.result.cache`, локальну SQLite DB або runtime photo folder.
- Після успішної Plesk extension check і backup у `deploy-artifacts/` залишено тільки актуальний artifact і `production-photo-upload-release-20260520.sql`.
- Оновлено `scripts/build-artifact.sh`, щоб `.phpunit.result.cache` не потрапляв у production artifact.

### [2026-05-20 18:42] Фото профілю — production QA зафіксовано — Codex
- Зафіксовано результат production QA після deploy: `/admin` працює, member URLs через `member_number` коректні.
- Admin photo upload/replace/delete працює; файл створюється в `ditib-portal-data/member-photos/...` і видаляється із сервера після видалення фото.
- Публічна форма працює; вибір фото, crop і preview працюють.
- `/konto` production QA відкладено, бо клієнтський доступ/посилання на пошту для кабінету ще не реалізовані; це окрема майбутня задача.
- Оновлено `PROJECT.md` і `FOTO_UPLOAD_TZ.md`; подальші помилки або корекції після release фіксуються окремими fix-задачами.

### [2026-05-26 15:11] Захист від дубля реєстрації перед фото — Codex
- Додано `MemberDuplicateChecker`: duplicate guard шукає існуючий запис за `birth_date + normalized phone` і включає soft-deleted записи через `Member::withTrashed()`.
- Перехід 3 → 4 (`Foto`) тепер дозволений тільки після валідних кроків 1-3; якщо анкета неповна, користувач повертається до першого проблемного кроку і не витрачає час на фото.
- Перед входом на `Foto` і повторно перед `Member::create()` блокується дубль; форма повертається на крок 2 і показує повідомлення біля телефону.
- Додано regression tests для блокування Step 4 при неповній анкеті, duplicate guard перед фото і duplicate guard на фінальному submit.
- Оновлено `PROJECT.md` з новою логікою та уточненням: технічна версія піднімається автоматично тільки при `scripts/build-artifact.sh` або `scripts/export-production-sql.php`; artifact build у цій сесії не запускався.

### [2026-05-29 08:54] Актуалізація PROJECT.md як джерела планів — Codex
- Зафіксовано правило: усі плани, ідеї, побажання, відкриті рішення і roadmap-нотатки ведуться в `PROJECT.md`.
- Виправлено неточності в стеку: PDF/dompdf і `spatie/laravel-settings` позначені як заплановані кандидати, а не встановлені залежності; canvas-підпис позначено як майбутній.
- Додано консолідовану секцію backlog: документація, публічна форма, `/konto`, Änderungsantrag, PDF/settings, локалізація, Excel export, audit log, майбутні покращення фото й email.
- Оновлено статуси `/konto`, duplicate guard, profile photo disk, release checks і production photo extension check як уже виконані/актуальні записи.

### [2026-05-29 09:03] Спрощення production deploy документації — Codex
- Прибрано з `PROJECT.md` шумні історичні згадки про Plesk Git/Deploy actions, deploy keys і помилки shell execution.
- Залишено коротке актуальне пояснення: серверні shell-команди недоступні за умовами хостингу, тому production deploy виконується через artifact upload у Plesk File Manager/FTP і SQL import через phpMyAdmin.

### [2026-05-29 09:06] Очищення AGENTS.md від дублювання deploy-процесу — Codex
- Зафіксовано `AGENTS.md` як єдиний загальний instruction-документ для всіх AI-агентів; окремі agent-файли не мають дублювати правила.
- Скорочено production deploy у `AGENTS.md` до guardrail-рівня, а повний процес лишено в `PROJECT.md`.
- Оновлено правила changelog і формулювання `/konto` access model для shared-email сценарію.

### [2026-05-29 09:09] Очищення README.md і CLAUDE.md — Codex
- Замінено стандартний Laravel `README.md` на короткий опис порталу, локального запуску, production guardrails і посилань на канонічні документи.
- Скорочено `CLAUDE.md` до compatibility pointer: Claude Code має читати `AGENTS.md`, `PROJECT.md` і `CHANGELOG.md`, але не тримати окремі дубльовані правила.
- Перевірено узгодженість із новою схемою документації: правила агентів у `AGENTS.md`, архітектура/плани/deploy у `PROJECT.md`, історія змін у `CHANGELOG.md`.

### [2026-05-29 09:15] Перенесення корисних нотаток зі старих тематичних документів — Codex
- Перенесено в `PROJECT.md` актуальні деталі з `Правки і зміни на сайті.md`: PLZ/OpenPLZ table/import, робочий варіант magic-link входу, контактний блок для майбутнього дизайну і audit-log UI filters.
- Перенесено з `FOTO_UPLOAD_TZ.md` актуальні photo notes: HEIC/HEIF fallback, photo UX labels/helper text, Filament upload caveat, майбутні photo metadata fields і manual QA checklist.
- Свідомо не переносились застарілі або помилкові записи: FilePond як photo flow, “dompdf/spatie вже встановлено” і PDF через production queue worker.

### [2026-05-29 09:37] Об'єднання функціонального статусу і планів у PROJECT.md — Codex
- Замінено окремі блоки `Плани, Побажання І Backlog` і `Статус реалізації` на єдиний розділ `Функціональні Блоки І Розвиток`.
- Для кожного функціонального модуля зафіксовано в одному місці: що працює зараз, що заплановано/хочемо додати, і які рішення важливо не порушити.
- Прибрано дублювання етапів реалізації; історична хронологія лишається в `CHANGELOG.md`, а `PROJECT.md` тепер показує актуальний функціональний стан.

### [2026-05-29 09:41] Видалення старих тематичних документів — Codex
- Видалено `FOTO_UPLOAD_TZ.md` і `Правки і зміни на сайті.md`, бо актуальні рішення, плани і статуси вже перенесені в `PROJECT.md`.
- Документація скорочена до канонічного набору: `AGENTS.md`, `PROJECT.md`, `CHANGELOG.md`, `README.md` і compatibility `CLAUDE.md`.

### [2026-05-29 09:49] Уточнення різниці між frontend build і production artifact — Codex
- У `PROJECT.md` уточнено, що `npm run build` є тільки локальною перевіркою Vite assets і не є стандартним production/deploy кроком.
- Зафіксовано, що для production artifact агент має запускати `scripts/build-artifact.sh`, який сам виконує `npm ci` і `npm run build` у staging-папці.

### [2026-05-29 10:19] Систематизація Filament стилів для login — Codex
- Додано technical system label під login-card `/admin` і `/konto`, вирівняний справа і прив'язаний до централізованого `SystemInfo`.
- Винесено спільні DITIB Filament styles у `resources/views/filament/panel-style.blade.php`: brand variables, primary button override, compact schema spacing і mobile login inset.
- Підключено shared style layer до `AdminPanelProvider` і `MemberPanelProvider`; admin-only статусні/table tweaks лишено в `resources/views/filament/admin-style.blade.php`.
- Додано regression test, який перевіряє system label на `/admin/login` і `/konto/login`; browser QA підтвердив однакові button color, spacing і mobile layout на обох login-сторінках.

### [2026-05-29 10:51] Magic-link вхід для `/konto` — Codex
- Розділено auth UX: `/admin/login` лишився password login із heading `Admin-Anmeldung`, а `/konto/login` став email-only flow із кнопкою `Zugangslink senden`.
- Додано `member_login_tokens`, `MemberMagicLoginService`, `MemberMagicLoginController`, custom Filament member auth page і одноразові magic links на 60 хвилин із token hash у БД та `used_at`.
- Додано `MemberLoginLinkMail` і Markdown email view у наявному mail branding layer; SMTP-помилки логуються без розкриття email presence.
- Підготовлено production SQL для phpMyAdmin: `deploy-artifacts/production-member-login-tokens-release-20260529.sql`; `scripts/export-production-sql.php` оновлено для повних SQL export.
- Додано regression tests для admin/member login UI, відправки magic-link листа, невідомого email без enumeration, valid/expired/used token behavior, shared-email memberships і заборони admin-доступу для member user.

### [2026-05-29 10:55] Link zur Registrierung на `/konto/login` — Codex
- Додано під кнопкою `/konto/login` текст `Noch kein Mitgliedskonto? Jetzt registrieren` із посиланням на `https://mitglied.ditib-ahlen-projekte.de/`.
- Посилання додано тільки в member magic-link login flow; `/admin/login` не змінено.
- Додано regression assertion для тексту й URL реєстрації на `/konto/login`.

### [2026-05-29 12:56] План self-service edit і audit log для `/konto` — Codex
- Створено тимчасовий робочий документ `docs/member-account-editing-audit-plan.md` для shared member schema, member edit flow, inactive login behavior, admin email і єдиного audit log.
- У `PROJECT.md` коротко зафіксовано напрямок: `/konto` має перейти на майже повну картку члена з редагуванням, статусом `processing` після змін і audit logging.
- Уточнено, що існуючий `ChangeRequest` не є `CHANGELOG.md` і не має ставати паралельною системою логування без окремого рішення.
- Додано security guardrails до плану: server-side allowlist для member-edit, email read-only у v1, блокування member magic-link для admin emails, DSGVO retention і cleanup для token/audit PII.

### [2026-05-29 14:55] Admin/member identity guard у magic-link flow — Claude Code
- Реалізовано пункт C плану `member-account-editing-audit-plan.md`: admin emails більше не отримують member magic-link доступ через спільний `User`/web guard.
- `User::isAdminEmail()` — новий case-insensitive helper; `User::isAdmin()` тепер використовує його.
- `MemberMagicLoginService::createForEmail()` не створює token для admin email (UI лишається нейтральним, подія логується як security warning).
- `MemberMagicLoginService::consume()` додатково відмовляє admin email навіть при вже наявному token (defense in depth).
- Додано тести: admin email не отримує token/лист попри наявний member-запис; pre-existing admin-email token не автентифікує. Весь набір — 71 тест зелений.

### [2026-05-29 15:10] Server-side allowlist для member-edit (foundation) — Claude Code
- Реалізовано foundation пункту A плану: `app/Filament/Resources/Members/Schemas/MemberFormContext.php` — єдине джерело правди для server-side allowlist member-полів (не сама shared schema; admin/konto форми поки лишаються окремими).
- Enum-кейси `AdminView/AdminEdit/MemberView/MemberEdit` додані як заготовка під майбутню shared schema (Phase 2), але schema-building логіки ще не містять.
- `MemberFormContext::memberEditableFields()` — явний allowlist (23 поля); `memberProtectedFields()` derived за принципом deny-by-default: будь-яке нове `fillable`/системне поле автоматично заборонене, доки явно не додане в allowlist.
- `MemberFormContext::onlyMemberEditable($data)` — security boundary для майбутнього `EditMemberAccount` save: зрізає підкинуті `status`/`admin_notiz`/`member_number`/`email`/consent.
- Додано unit-тести (`tests/Unit/MemberFormContextTest.php`): editable∩protected=∅, sensitive/admin поля захищені, повне покриття `fillable`, зрізання forbidden keys. Весь набір — 75 тестів зелений.

### [2026-05-29 15:25] Auto-cleanup member_login_tokens (DSGVO) — Claude Code
- Реалізовано пункт D плану без потреби в cron/ручному запуску.
- `MemberLoginToken::pruneSpent()` — єдине джерело логіки: видаляє used/expired токени (таблиця зберігає `ip_address`/`user_agent` PII), активні не чіпає.
- `MemberMagicLoginService::createForEmail()` викликає `pruneSpent()` при кожній видачі нового токена — рутинна чистка відбувається автоматично під час звичайної активності.
- `php artisan member:prune-login-tokens` лишено як опційний ops-fallback (one-off purge, `--keep-hours=N`); для нормальної роботи не потрібен.
- Тести: auto-prune при видачі токена + поведінка команди. Весь набір — 78 тестів зелений.

### [2026-05-29 15:45] Doc-sync після контрольного рев'ю — Claude Code
- Виправлено header CHANGELOG: канон правил для агентів — `AGENTS.md`, а не `CLAUDE.md`; `CLAUDE.md` позначено як compatibility-pointer (header-legend і верхня таблиця «Три документи»).
- Уточнено формулювання про `MemberFormContext`: це foundation + server-side allowlist, а не сама shared schema (admin/konto форми поки окремі).
- У `PROJECT.md` явно позначено, що inactive-only magic-link suppression ще не реалізовано (Phase 4): `memberEmailExists()` поки не фільтрує за `status`, тому inactive-only email усе ще отримує лінк.
- Без змін у коді; `php artisan test` — 78 тестів зелений.

### [2026-05-29 16:20] Shared member field validation rules (пункт E) — Claude Code
- `app/Support/MemberFieldRules.php` — єдине джерело правди для валідаційних правил полів `Member`, включно з closure-правилами (мін. вік 16, phone, IBAN structure, Instagram). Нормалізація лишається в наявних helpers `PhoneNumber`/`Iban`/`Instagram`.
- `MembershipForm` (`rulesStep1/2/3`) переведено на `MemberFieldRules` без зміни поведінки реєстрації; прибрано невикористаний `Carbon` import.
- Мета: майбутня Filament admin/member edit форма валідуватиме ідентично до публічної реєстрації, тож member-edit не зможе обійти перевірки (duplicate-guard, формат phone/IBAN, мін. €10).
- Додано `tests/Feature/MemberFieldRulesTest.php` (6 кейсів); наявний `MembershipFormTest` як характеризаційна сітка не змінювався. Весь набір — 84 тести зелений.
- Зафіксовано рішення Roman у `docs/member-account-editing-audit-plan.md` (mixed-email: inactive показувати dimmed без відкриття/редагування; `status` видимий клієнту read-only; `monatsbeitrag` ≥ €10; `zahlungsart`→`lastschrift` потребує нової SEPA-згоди; `email` read-only у v1).

### [2026-05-29 16:55] Shared member schema (Phase 2) — Claude Code
- `MemberForm` зроблено context-aware: `configure()` (admin) делегує новому `build($schema, MemberFormContext)`; admin-поведінка не змінилась.
- Member-контекст дає точкові відмінності: `admin_notiz` приховано, `status` і `email` read-only; consents лишаються `disabledOn('edit')`.
- `/konto` (`MemberAccountResource`) переведено з окремої короткої схеми на спільний `MemberForm::build(..., MemberFormContext::MemberView)` — клієнт тепер бачить майже повну картку (як admin), мінус admin-only поля. `canEdit` поки `false` (edit вмикається у Phase 3).
- Виправлено наявний баг: німецьке validation-повідомлення для `full_name` містило українську вставку — замінено на коректний німецький текст.
- Додано `tests/Feature/MemberSharedSchemaTest.php` (member бачить `Status`/повну картку, не бачить `Interne Notiz`; admin edit досі бачить обидва). Весь набір — 86 тестів зелений.

### [2026-05-29 17:20] /konto display-фікси (колонки + фото-consent) — Claude Code
- `/konto` таблиця тепер має ті самі дефолтні колонки, що admin: `Nr. / Name / Status / E-Mail / Ort / Beitrag/Mo. / Eingegangen am` (раніше тільки 4).
- Фото-consent поля (`Foto-Einwilligung` тумблер і `Foto-Einwilligung am` timestamp) у `MemberForm` тепер видимі лише коли є збережене фото (`profile_photo_path !== null`) — для обох панелей. Це прибирає зайвий «Foto-Einwilligung am» без фото на `/konto` (наслідок Phase 2) і той самий наявний баг в адмінці.
- Додано тести: дефолтні колонки `/konto`; фото-consent ховається без фото (member + admin) і показується з фото. Весь набір — 87 тестів зелений.

### [2026-05-29 18:05] Member self-service edit (Phase 3 core) — Claude Code
- Додано сторінку `EditMemberAccount`; на `/konto` view тепер breadcrumb `Vorschau` + кнопка `Bearbeiten`, redirect на view після save.
- `MemberAccountResource`: `canView`/`canEdit` тільки для власних **не-inactive** записів; inactive у списку показуються dimmed (`opacity-50`) і не клікабельні (`recordUrl` null, ViewAction приховано).
- Save проходить через server-side allowlist `MemberFormContext::onlyMemberEditable()` — підкинуті `status`/`admin_notiz`/`member_number`/`email` ігноруються. Будь-яка реальна зміна editable-поля переводить `pending`/`active` у `processing` (`afterSave`, `saveQuietly`); no-op save статус не змінює.
- SEPA: перехід `zahlungsart`→`lastschrift` без наявної згоди блокується (повний re-consent UX — окремий під-зріз 3c).
- Під списком `/konto` додано technical system label (render-hook `RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER` → `filament.system-info`), як під таблицею Mitglieder в адмінці; версія/дата підтягуються з `config/system-version.json`.
- Додано `tests/Feature/MemberSelfEditTest.php` (7 кейсів: edit→processing, no-op, forge protected, lastschrift-guard, inactive view/edit заборонено, dimmed list, чужий email) + тест label під списком `/konto`. Весь набір — 95 тестів зелений.
- Оновлено `PROJECT.md` (факти Phase 3 + label + залишок 3c/Phase 4/5/6).

### [2026-05-29 18:45] SEPA re-consent для member-edit (3c) — Claude Code
- Рішення Roman: DSGVO і SEPA розділені. Додано окрему колонку `members.sepa_zustimmung_at` (міграція `2026_05_29_151717_add_sepa_zustimmung_at_to_members_table`), `zustimmung_at` лишається часом заявки/DSGVO.
- Member-edit: будь-яка зміна банк-даних (`zahlungsart`→`lastschrift` або зміна `iban`/`bic`/`kontoinhaber`/`kreditinstitut` при lastschrift) вимагає підтвердження SEPA-мандата через checkbox (`sepa_reconsent`, видимий тільки в member edit при lastschrift). На save: `sepa_zustimmung=true` + `sepa_zustimmung_at=now()`; без підтвердження зміна не зберігається. `sepa_zustimmung*` лишаються system-controlled (поза allowlist), `zustimmung_at` не чіпається.
- `MemberForm` показує `SEPA-Zustimmung am` (read-only) коли є SEPA-згода.
- Checkbox re-consent з'являється лише коли банк-дані фактично відрізняються від збереженого запису (`MemberForm::bankDataChanged()` через `$get` vs `$record`), а не одразу при lastschrift. Видимість дзеркалить серверну умову «банк змінився», тож UI і backend узгоджені; `dehydrated(true)` тримає server-логіку незалежно від видимості.
- Банк-поля (`zahlungsart`/`kontoinhaber`/`iban`/`bic`/`kreditinstitut`) переведено на `->live()` (замість `onBlur`): зміна синхронізується одразу, тож checkbox з'являється без потреби «клікнути деінде», і нове значення гарантовано доходить до сервера при save (закрито щілину «зберіг без згоди»).
- Замінено проміжний блок-guard Phase 3 на повний re-consent flow.
- Виправлено: публічна реєстрація Lastschrift тепер ставить `sepa_zustimmung_at=now()` (раніше мандат був true, а дата порожня). Бекфіл існуючих SEPA-членів `sepa_zustimmung_at = zustimmung_at` додано в deploy-SQL і застосовано локально.
- Підготовлено phpMyAdmin SQL `deploy-artifacts/production-add-sepa-zustimmung-at-release-20260529.sql` (ALTER + backfill).
- Тести: 4 нові 3c-кейси (switch з/без re-consent, зміна IBAN з/без, non-bank без re-consent, `zustimmung_at` недоторканий). Весь набір — 98 тестів зелений.

### [2026-05-29 19:05] Інваріант: без Lastschrift — без SEPA-мандата і банк-даних — Claude Code
- Виявлено: при `Lastschrift → Barzahlung` старий SEPA-мандат лишався (`sepa_zustimmung=true`, timestamp, `iban` у БД).
- Рішення Roman: очищати і згоду, і банк-дані. Реалізовано як `Member` saving-hook (доменний інваріант для admin/member/public): якщо `zahlungsart` ≠ `lastschrift` → `sepa_zustimmung=false`, `sepa_zustimmung_at=null`, `iban`/`bic`/`kontoinhaber`/`kreditinstitut`=null.
- Менше PII (DSGVO-мінімізація), нема «застряглого» мандата у Status & Verwaltung.
- Додано тест `switching_away_from_lastschrift_clears_mandate_and_bank_data`. Весь набір — 99 тестів зелений.

### [2026-06-01 08:46] Блок Beitrag & Bankverbindung read-only для admin — Claude Code
- За рішенням Roman: внесок і банк-дані веде член через `/konto`, тож для admin весь блок `Beitrag & Bankverbindung` тепер read-only (`disabled` на рівні секції в admin-контексті `MemberForm`; member-контекст лишається редагованим). Безпека + єдине джерело правди.
- Disabled-поля admin не дегідруються → admin save їх не перезаписує; інваріант моделі (без lastschrift — без банк-даних) лишається на `$member->zahlungsart`.
- Тести: admin — `monatsbeitrag`/`zahlungsart` disabled; member — enabled. Весь набір — 102 тести зелений.

### [2026-06-01 09:28] Phase 4: Info-модалка для inactive у /konto — Claude Code
- Рішення Roman: magic-link шлеться незалежно від статусу, окремий inactive-notice email НЕ робимо.
- Біля dimmed inactive-запису в списку `/konto` додано дію `Info` (іконка, лише для inactive) → мобільно-зручна модалка `resources/views/filament/members/inactive-info.blade.php`: запис неактивний, клієнт сам статус не змінює, для реактивації звернутися до DITIB Ahlen + контакти (тел./email/адреса з tap-лінками).
- Phase 4 закрито без нових типів листів; admin-email guard і token cleanup уже були раніше.
- Тест: `inactiveInfo` дія visible для inactive, hidden для active. Весь набір — 103 тести зелений.
- Клік по всьому inactive-рядку відкриває Info-модалку (`recordAction` → `inactiveInfo`); активні рядки далі ведуть на перегляд. Іконки в модалці зафіксовано 20×20 (SVG-атрибути + inline-стилі, незалежно від Tailwind-build). Картку контактів переведено на рідний `<x-filament::section>` (коректна темна тема).

### [2026-06-01 09:49] Лінк на /konto зі сторінки Anketa — Claude Code
- Під version-label публічної форми додано ненав'язливий лінк `Bereits Mitglied? Zum Mitgliedskonto →` на `/konto` (для тих, хто вже має акаунт). Хедер/лого і кроки форми не чіпались.
- Тест: `/` рендерить лінк на `/konto`. Весь набір — 104 тести зелений.
- Під кнопкою `Anmelden` на `/admin/login` додано лінк `Sind Sie Mitglied? Zum Mitgliedskonto →` на `/konto` (для тих, хто випадково потрапив на admin-логін) — через override `content()` у `App\Filament\Admin\Pages\Auth\Login`, той самий стиль, що member-лінк. Збільшено відступ лінка на сторінці Anketa до 2rem (`mt-8`).
- Уточнено підзаголовок `/konto/login`: «Geben Sie die in Ihrer Mitgliedschaft hinterlegte E-Mail-Adresse ein. Ist diese bei uns registriert, senden wir einen einmaligen Zugangslink an genau diese Adresse.» — щоб не складалось враження, що лінк може отримати будь-хто (нейтральність/enumeration-захист збережено).

### [2026-06-01 10:33] Security: відкликання попередніх активних magic-link токенів — Claude Code
- Виявлено: `createForEmail()` викликав тільки `pruneSpent()` (used/expired), тобто повторний запит лінка лишав попередній активний токен дійсним — для одного email могло існувати кілька одночасно валідних лінків.
- Виправлення: перед створенням нового токена `createForEmail()` видаляє всі активні (не used, не expired) токени того ж email. Для одного email тепер завжди тільки один активний лінк.
- Додано тест `issuing a new token revokes previous active token`. Весь набір — 105 тестів зелений.
- Оновлено PROJECT.md: рядки 162, 280, 400 тепер точно відображають двоетапний cleanup (revoke active + prune spent).

### [2026-06-01 09:57] Email-покращення: кнопка в approved-листі + посилання на лендінг у футері — Claude Code
- `member-approved-notification.blade.php`: додано кнопку «Zum Mitgliedskonto» з посиланням на `https://mitglied.ditib-ahlen-projekte.de/konto` — щоб новий член міг одразу зайти в акаунт.
- `vendor/mail/html/message.blade.php` та `text/message.blade.php`: у футері під copyright-рядком додано посилання на лендінг `https://ditib-ahlen-projekte.de/` — присутнє в усіх листах системи.

### [2026-06-01 11:04] Member audit logs і per-record Logs сторінки — Codex
- Додано `member_audit_logs`, `MemberAuditLog` і `MemberAuditLogger` для історії змін конкретного запису члена.
- У admin і `/konto` в картці запису додано посилання `Historie dieses Eintrags anzeigen` на сторінку `Logs` з timeline, нові записи зверху.
- Логуються створення акаунта, member/admin edit, status actions/bulk status actions, admin profile photo upload/replace/delete і soft delete/delete; IBAN/BIC у логах маскуються.
- Підготовлено production SQL для phpMyAdmin: `deploy-artifacts/production-create-member-audit-logs-release-20260601.sql`; локальна міграція застосована.

### [2026-06-01 11:14] Audit log link placement polish — Codex
- Посилання `Historie dieses Eintrags anzeigen` винесено з блоку `Status & Verwaltung` в окремий centered рядок нижче цього блоку для admin і `/konto`.

### [2026-06-01 11:23] Audit log link center alignment — Codex
- Для `Historie dieses Eintrags anzeigen` додано full-width span і wrapper-level `text-center`, щоб посилання реально вирівнювалось по центру в desktop і mobile layout.

### [2026-06-01 11:28] Scoped audit log link alignment CSS — Codex
- Вирівнювання audit-link перенесено в scoped CSS `.ditib-audit-log-link .fi-in-text...`, щоб центрувати тільки `Historie dieses Eintrags anzeigen` і не зачіпати `Mitgliedsnummer`.

### [2026-06-01 11:31] Inline centering for audit log link — Codex
- Для audit-link додано inline `display:block; width:100%; text-align:center;`, щоб вирівнювання працювало в admin і `/konto` незалежно від внутрішнього Filament wrapper.

### [2026-06-01 11:35] Mobile order for member card blocks — Codex
- У shared `MemberForm` змінено порядок блоків для admin і `/konto`: `Persönliche Daten` → `Beitrag & Bankverbindung` → `Status & Verwaltung` → `Historie dieses Eintrags anzeigen`.

### [2026-06-01 11:46] Two-column member card layout groups — Codex
- Перебудовано shared `MemberForm` на дві top-level колонки: ліва містить `Persönliche Daten` + `Beitrag & Bankverbindung`, права містить `Status & Verwaltung` + `Historie dieses Eintrags anzeigen`.
- Це зберігає правильний mobile порядок і повертає логічний desktop layout без окремого порожнього нижнього правого блока.

### [2026-06-01 11:50] Backfill member-created audit logs — Codex
- Додано idempotent migration `backfill_member_created_audit_logs`, щоб існуючі члени отримали перший timeline-запис `Account erstellt`, якщо `member_audit_logs` уже була створена до backfill-логіки.
- Локально застосовано міграцію; перевірка SQLite показала `16` members і `16` `member_created` audit logs.

### [2026-06-01 11:57] Compact audit log timeline formatting — Codex
- Сторінку `Logs` зроблено компактнішою: менший шрифт, менші відступи, технічний card-style.
- Для `member_updated` зміни виводяться списком із дефісом (`- Name geändert`, `- Adresse geändert`); create/status/photo події лишаються людським описом.

### [2026-06-01 12:01] Visual timeline style for Logs page — Codex
- Сторінку `Logs` перероблено з card-stack у timeline layout: лівий маркер із вертикальною лінією, дата й actor badge праворуч, нижче список змін.

### [2026-06-01 12:06] Shared CSS for audit log timeline — Codex
- Вигляд `Logs` перенесено з Tailwind utility-класів у явні scoped CSS-класи в shared `filament.panel-style`, щоб зміни реально застосовувались в admin і `/konto` незалежно від Filament/Tailwind build.
- Очищено compiled Blade views локально (`php artisan view:clear`).

### [2026-06-01 12:11] Light theme colors for audit log timeline — Codex
- Виправлено світлу тему `Logs`: dark-стилі тепер прив'язані до `.dark` класу Filament, а не до системного `prefers-color-scheme`, тому дата і текст видимі на світлому фоні.
- Прибрано вертикальну лінію timeline; лишились окремі бірюзові маркери, як у референсі.

### [2026-06-01 14:00] Audit log: прибрано ip/user_agent, зафіксовано маску IBAN — Claude Code
- Рішення Roman: у `member_audit_logs` ip_address/user_agent НЕ зберігаємо — достатньо actor_type (admin/member/system), бо редагувати можуть лише ці двоє. Менше PII, частково знімає DSGVO-retention борг по логах.
- Прибрано колонки `ip_address`/`user_agent` з міграції, моделі, logger, inline+окремого backfill і production SQL. Локальну БД переприкладено (db:wipe + migrate).
- Рішення Roman: маска IBAN/BIC `****1234` лишається як є — узгоджено формулювання в `PROJECT.md` і плані (раніше було «без старих/нових значень», що суперечило реалізації).
- Тести: 110 passed (MemberAuditLogTest не залежав від ip/ua).

### [2026-06-01 14:30] Guardrails проти деструктивних команд + dev-seeders — Claude Code
- ПРИЧИНА: `db:wipe` стер локальну БД (резервної копії не було). Корінь — `.claude/settings.local.json` мав `allow: Bash(php artisan *)`, тож небезпечні artisan-команди виконувались без питання.
- `.claude/settings.json` (комітиться): `permissions.deny` для `db:wipe`/`migrate:fresh`/`migrate:reset`/`migrate:refresh`/`git reset --hard`/`git push --force|-f`/`git clean -f`; `ask` для `migrate:rollback`/`migrate`/raw `DROP|TRUNCATE`. Deny має пріоритет над allow.
- `.claude/settings.local.json` додано в `.gitignore` (персональний файл не комітимо).
- `AGENTS.md` правило 13: деструктивні команди — ніколи без явного дозволу.
- `DatabaseSeeder` тепер ідемпотентно сідить 2 admin-користувачів (відновлення після reset через `migrate --seed`).
- Додано `DevSampleMembersSeeder` (local-only): `php artisan db:seed --class=DevSampleMembersSeeder` створює 5 тестових членів (3 на roman.2271670@gmail.com, 1 inactive; 2 з фото).
- Тести: 110 passed.

### [2026-06-01 14:50] Перевірка: лог "Account erstellt" при реєстрації — Claude Code
- Підтверджено: публічна реєстрація (`MembershipForm`) викликає `MemberAuditLogger::created()` → кожен новий член отримує перший лог `Account erstellt`. Додано feature-тест на цей шлях (раніше був непокритий).
- `DevSampleMembersSeeder` тепер теж пише лог створення для кожного seeded-члена (раніше через `Member::create` логи не з'являлись).
- Бекфілнуто лог `member_created` для 5 уже створених тестових членів (ідемпотентно).
- Тести: 111 passed.

### [2026-06-01 15:10] Inactive-рядки /konto приглушені по кольору — Claude Code
- Виправлено: `opacity-50` (Tailwind-утиліта) не діяв у списку `/konto`, бо клас не входить у зібраний CSS Filament — inactive-рядок виглядав як звичайний.
- Додано власний клас `.ditib-inactive-row { opacity: 0.5 }` у `panel-style` (гарантовано вантажиться); `recordClasses` у `MemberAccountResource` тепер віддає його. Тільки member-панель; admin-список не зачеплено; hover і Info-модалка не змінені.
- Тести: 111 passed.

### [2026-06-01 16:10] Phase 6: email адміну після member-edit — Claude Code
- `MemberUpdatedByMemberNotification` (mailable + markdown-шаблон): при реальній зміні даних членом у `/konto` admin (`info@ditib-ahlen-projekte.de`) отримує лист — ім'я, member_number, email, список змінених полів (старе→нове), пряме посилання на admin-запис. IBAN/BIC лише маска `****1234`.
- Вшито в `EditMemberAccount::afterSave()`: один лист на save, тільки при реальних змінах; синхронно; SMTP-помилка логується і НЕ ламає save.
- Рефактор обчислення змін: порівнюємо розшифровані old/new (через `getAttribute`), а не `getChanges()`. Це виправляє дві проблеми — маскування IBAN бралось із зашифрованого blob, і рандомний IV шифрування хибно позначав незмінений IBAN як «змінено». Тепер і audit log, і лист коректні.
- `MemberAuditLogger::describeChanges()` — публічний форматер (label + маска), єдине джерело для листа.
- Тести: `MemberEditAdminNotificationTest` (notify, no-op, IBAN-маска, render, SMTP-стійкість). Весь набір — 116 passed.

### [2026-06-01 16:40] Консолідація документації після Phases 1–6 — Claude Code
- Phases 1–6 self-service edit завершені; робочий документ `docs/member-account-editing-audit-plan.md` консолідовано в `PROJECT.md` і видалено (як і передбачав сам план — тимчасовий чернетковий документ).
- У `PROJECT.md`: прибрано посилання на план; done-`[x]` Phase-пункти з «Заплановано» прибрано (функціонал уже в «Працює зараз»); `Änderungsantrag` переформульовано (доля `change_requests` відкрита). Додано архітектурні замітки: `MemberFieldRules` як єдине джерело валідації; обчислення member-edit змін через розшифровані old/new (не `getChanges()`).
- CHANGELOG історичні записи з назвою плану лишено як хронологію.

### [2026-06-01 17:30] Зафіксовано фікс: 403 на лінк з листа member-edit — Claude Code
- На проді реліз self-service edit працює (листи зі змінами ходять, логи пишуться).
- Виявлено: кнопка «Datensatz im Admin öffnen» у листі `MemberUpdatedByMemberNotification` веде прямо на `/admin/members/{member_number}` → 403 Forbidden, якщо браузер не залогінений як admin (нюанс: спільний web guard admin/member — member-сесія або guest дає 403, не редірект на логін).
- Зафіксовано в `PROJECT.md` (Email → Заплановано) як фікс: guest/не-admin → `/admin/login` з redirect назад на запис; залогінений admin → одразу запис. Код не змінювався — лише документація.

### [2026-06-02] Фікс 403 → redirect-to-login для admin-панелі — Claude Code
- Створено `app/Http/Middleware/AdminPanelAuthenticate.php`: overrides `handle()` (не `authenticate()`), бо `authenticate()` повертає `void` і return-value ігнорується батьківським `handle()`. Логіка: guest → стандартний Filament `unauthenticated()` → redirect to login; authenticated-but-not-admin → logout + session invalidate + `url.intended` set + пряме redirect to `/admin/login`; admin → `$next($request)`.
- Logout перед редіректом є обов'язковим: Filament `Login::mount()` рядок 62–63 одразу викликає `redirect()->intended()` якщо `auth()->check() = true` — без logout це спричиняло нескінченну петлю (підтверджено на проді: «Safari Can't Open the Page — Too many redirects»).
- `AdminPanelProvider`: `authMiddleware` → `AdminPanelAuthenticate`.
- `MemberMagicLoginTest`: `assertForbidden()` → `assertRedirectToRoute(...)` + `assertGuest()`.
- Всі 116 тестів пройшли.

### [2026-06-01 17:45] Очищення deploy-artifacts + актуалізація doc — Claude Code
- Roman очистив `deploy-artifacts/` (SQL уже застосовані на проді 2026-06-01); папка gitignored, тож git цього не торкається. Лишається для майбутніх релізних артефактів.
- PROJECT.md: пункти про підготовлені SQL замінено на «застосовано на проді 2026-06-01» (щоб ніхто повторно не запускав non-idempotent SQL).
