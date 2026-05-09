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

---

*Цей файл ведеться вручну всіма агентами. Не видаляти, не перейменовувати.*
