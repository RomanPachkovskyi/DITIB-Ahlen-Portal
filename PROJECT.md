# PROJECT.md — Архітектура і стан
## DITIB-Ahlen-Portal

> Тут — архітектура, стек, функціональність, деплой. Актуальний стан проекту.
> Правила для агентів → `AGENTS.md` | Історія змін → `CHANGELOG.md`

---

## Три документи проекту

| Файл | Призначення |
|------|-------------|
| **`AGENTS.md`** | Правила для агентів, команди, середовище — читати першим |
| **`PROJECT.md`** ← ти тут | Архітектура, стек, функціональність, деплой |
| **`CHANGELOG.md`** | Хронологія всіх змін — що, коли, хто зробив |

---

## Про проект

Портал для управління членами ісламської громади DITIB Ahlen.
Пов'язаний з лендінгом [ditib-ahlen-projekte.de](https://ditib-ahlen-projekte.de) — там є кнопка переходу до порталу.
Очікувана кількість членів: до 500 осіб.

---

## Репозиторії

| Проект | GitHub | Локально |
|--------|--------|----------|
| Лендінг (React) | `DITIB-Ahlen` | `~/Project/DITIB-Ahlen/main/` |
| Портал (Laravel) | `DITIB-Ahlen-Portal` | `~/Project/DITIB-Ahlen/portal/` |

Два незалежних репозиторії — два паралельних проекти, спільний домен.

---

## Стек

| Шар | Технологія | Версія |
|-----|-----------|--------|
| Backend | Laravel | 13.6 |
| Admin-панель | Filament | v5.6 |
| Реактивність | Livewire | v4 |
| Стилі | Tailwind CSS | v4 |
| База даних (local) | SQLite | — |
| База даних (production) | MySQL | Plesk |
| Email | Laravel Mail → SMTP, Markdown Mailables + централізований branding layer | — |
| PDF | barryvdh/laravel-dompdf | встановлено |
| Підпис | Alpine.js canvas | вбудований |
| Налаштування адміна | spatie/laravel-settings | Етап 3 |
| Черги (майбутні jobs/PDF) | Laravel Queue (database driver), без production worker зараз | Етап 3+ |

> **Важливо для Filament v5:** `Section` знаходиться в `Filament\Schemas\Components\Section`, НЕ в `Filament\Forms\Components`.

---

## Функціональність

### Публічна форма (`/`)
- 4-крокова форма Mitgliedsantrag (тільки DE, TR — Етап 4)
- Крок 1: Persönliche Daten (ПІБ, народження, громадянство, сім'я, Cenaze Fonu, Gemeinderegister, берuf, Heimatstadt)
- Крок 2: Adresse & Kontakt
- Крок 3: Beitrag & Zahlungsweise (мін. €25, умовні SEPA-поля, SEPA-згода, DSGVO-згода)
- ~~Крок 4: Unterschrift~~ → перенесено в Етап 4 разом із фото профілю
- Після відправки → сторінка підтвердження з member_number, статус `pending`

### Кабінет члена (`/konto`) — Filament MemberPanel
- Вхід через email (Filament auth)
- Перегляд власних даних
- Подача запиту на зміну (Änderungsantrag)
- Перегляд статусу заявки

### Адмін-панель (`/admin`) — Filament AdminPanel
- Список членів з пошуком, фільтром по статусу, badge-кольорами
- Перегляд і редагування кожного запису (секції: Дані, Банк, Статус)
- Схвалення / відхилення заявок (статуси: `pending` → `active` / `inactive`)
- Клік по рядку таблиці відкриває перегляд запису; редагування доступне тільки через `Bearbeiten`
- У таблиці швидкі дії: `Bearbeiten` і окремо зміна статусу; `Anzeigen` і `Löschen` не показуються в row actions
- Масові дії в таблиці використовуються тільки для зміни статусу вибраних записів; масове видалення не показується
- На сторінці редагування кнопки `Änderungen speichern` і `Abbrechen` доступні зверху і знизу форми; save-кнопки зелені
- При зміні статусу на `active` член отримує email про прийняття
- Звичайний адмін-процес для завершення членства: перевести запис у `inactive`, не видаляти
- Soft delete лишається технічно в системі, але не є адмінською UI-дією; номер не звільняється навіть для inactive або soft-deleted записів
- При видаленні запису адміністратор і член отримують email-фіксацію видалення
- Обробка запитів на зміну даних

---

## База даних

### Таблиця `members`

| Поле | Тип | Примітка |
|------|-----|---------|
| member_number | string(20) | unique, auto DA-YYYY-NNNN, не перевикористовується |
| full_name | string | |
| birth_date | date | |
| birth_place | string | nullable |
| staatsangehoerigkeit | string | nullable |
| familienangehoerige | tinyint | default 1 |
| cenaze_fonu | boolean | default false |
| cenaze_fonu_nr | string(50) | nullable |
| gemeinderegister | boolean | default false |
| beruf | string | nullable |
| heimatstadt | string | nullable |
| street | string | |
| city, state, postal_code | string | |
| email | string | **не unique** — один email дозволений для кількох членів (сім'я) |
| phone | string | |
| zahlungsart | enum | barzahlung / lastschrift / dauerauftrag |
| monatsbeitrag | decimal | мін. €25 |
| kontoinhaber | string | nullable (тільки при SEPA) |
| iban | text | **encrypted**, nullable |
| bic | text | **encrypted**, nullable |
| kreditinstitut | string | nullable |
| unterschrift | text | base64 PNG, hidden |
| sepa_zustimmung | boolean | |
| dsgvo_zustimmung | boolean | |
| zustimmung_at | timestamp | |
| status | enum | pending / active / inactive |
| admin_notiz | text | nullable |
| deleted_at | timestamp | nullable, soft delete для історії номерів |

### Таблиця `member_number_sequences`

| Поле | Тип | Примітка |
|------|-----|---------|
| name | string(50) | unique, зараз `members` |
| next_number | unsigned big integer | наступний кандидат для `member_number` |

Номери членів видаються тільки через `MemberNumberSequence` у DB transaction з `lockForUpdate()`. Allocator додатково перевіряє `members` разом із soft-deleted записами й переступає вже зайняті номери, якщо sequence колись відстане після імпорту або ручної правки.

### Таблиця `change_requests`

| Поле | Тип | Примітка |
|------|-----|---------|
| member_id | FK | cascade delete |
| field_name | string | яке поле змінюється |
| old_value | text | |
| new_value | text | |
| reason | text | nullable |
| status | enum | pending / approved / rejected |
| admin_notiz | text | nullable |

---

## Безпека та DSGVO

- IBAN і BIC — `'encrypted'` cast → зашифровані в БД
- `member_number` — постійний історичний ідентифікатор; soft-deleted записи зберігають номер і не дають системі видати його повторно
- `inactive` — нормальний адміністративний статус для колишніх/неактивних членів; не видаляти записи через UI
- SEPA/DSGVO consent fields і `zustimmung_at` у admin edit є read-only фактами; адмін не редагує згоду клієнта
- `$hidden` у моделі НЕ використовувати для IBAN/BIC (блокує Filament форму)
- Члени бачать тільки свої дані (Filament Panel ізоляція)
- Згода SEPA і DSGVO фіксується з timestamp при відправці форми
- `.env` з `APP_KEY` — ніколи не комітити в git

---

## Архітектурні рішення

### Email — не унікальний (v1.0)
Один email може використовуватись для кількох членів. Причина: літні члени громади не мають власної пошти — діти або родичі реєструють їх на свій email.

> **Майбутнє (Етап 4+):** При реалізації кабінету (`/konto`) входу через email-посилання (magic link) потрібно продумати логіку вибору облікового запису, якщо на один email зареєстровано кількох членів (наприклад, показати список і дати вибрати, або використовувати `member_number` як додатковий ідентифікатор).

### Email — архітектура і брендування

Поточна production-схема: Laravel Mail → SMTP, синхронна відправка без queue worker. Це свідоме рішення для Plesk artifact-deploy, де немає стабільного production worker-а для черг.

Поточний шаблонний шар:

- окремі листи реалізовані як `Mailable` + `markdown:` views у `resources/views/emails/`
- спільний HTML/text layout Laravel Markdown Mail перевизначений у `resources/views/vendor/mail/`
- бренд налаштовується централізовано через `config/mail.php` → `mail.brand.*`
- `.env` ключі бренду: `MAIL_BRAND_NAME`, `MAIL_BRAND_URL`, `MAIL_LOGO_URL`, `MAIL_BRAND_FOOTER`
- окремі email views не повинні містити логотип, footer або глобальні стилі — тільки зміст конкретного повідомлення

Логотип у листах зараз використовується як стабільний HTTPS URL з `MAIL_LOGO_URL`. Inline/CID images не використовуються як основний механізм, щоб branding layer залишався простим, стандартним і передбачуваним для Laravel Markdown Mail.

**Діагноз 2026-05-06:** локальні листи рендеряться з логотипом, але production artifact не містив `resources/views/vendor/mail/`. Причина: `scripts/build-artifact.sh` копіював проект через `tar --exclude='./vendor'`, а на локальному tar цей exclude також прибирав вкладену папку `resources/views/vendor/mail/`. Через це production використовував стандартні Laravel Markdown mail templates без нашого header/logo. Скрипт виправлено: після staging-copy mail override templates явно повертаються в `resources/views/vendor/mail/`. Після наступного artifact deploy потрібно перевірити, що ця папка є на сервері.

Майбутнє оновлення: якщо Gmail/Outlook/Apple Mail покажуть, що Markdown theme недостатньо контролює вигляд, можна перейти з `markdown:` на власний `view:` HTML email template. Це буде окремий етап, не змішаний із поточним SMTP/Mailable layer.

### Legacy import зі старого Excel (план)
Стара база членів існує як Excel-файл на 743 записи, а не як SQL-база. Для неї прийнято рішення **не змішувати історичний номер зі системним `member_number`**.

Рекомендований підхід:

- поточний `member_number` лишається новим системним номером у форматі `DA-YYYY-NNNN`
- старий номер з Excel зберігається окремо як `legacy_member_number`
- мобільний номер мапиться в `phone`
- звичайний номер рекомендується зберігати окремо як `phone_landline`

Детальний план міграції та підготовки імпорту описаний у `LEGACY_IMPORT.md`.

---

## Локальна розробка

| Параметр | Значення |
|----------|----------|
| Папка | `~/Project/DITIB-Ahlen/portal/` |
| PHP | 8.5 (Homebrew, не Docker) |
| DB | SQLite → `database/database.sqlite` |
| Порт | **8000** (Docker на 8080 — не конфліктує) |
| Форма | http://localhost:8000 |
| Admin | http://localhost:8000/admin |
| Konto | http://localhost:8000/konto |
| Admin login | rpachkovskyi@gmail.com / Admin1234! |
| Admin login | info@ditib-ahlen-projekte.de / AhlenDitib2026! |

**Запуск:**
```bash
cd ~/Project/DITIB-Ahlen/portal && php artisan serve --port=8000
```

**Build локально:**
```bash
npm run build
```

Перед пакуванням staging нормалізує права доступу: директорії `755`, файли `644`, `artisan` і shell-скрипти `755`. Це важливо для Plesk/Apache: root `./` всередині tar не може мати права `700`, інакше після розпакування сайт дає `403 Forbidden`.

`npm run build` створює тільки Vite assets у `public/build/` (CSS/JS). Це не окремий статичний сайт і не повний build застосунку. Laravel-порталу для роботи потрібні PHP-код, `vendor/`, `routes/`, `resources/`, `storage/`, `.env` і база даних.

---

## Хостинг і деплой

| Параметр | Значення |
|----------|----------|
| Хостинг | Plesk (virtual hosting) |
| Домен | `mitglied.ditib-ahlen-projekte.de` |
| Server folder | `mitglied.ditib-ahlen-projekte.de/` (окремо від `httpdocs`) |
| Document Root | `mitglied.ditib-ahlen-projekte.de/public` |
| PHP | 8.4+ (`composer.json` вимагає `^8.4`) |
| DB | MySQL (Plesk) |
| Deploy | Artifact deploy через File Manager/FTP |
| DB changes | SQL-файли через phpMyAdmin |

**Обраний робочий процес з 2026-05-06:** artifact deploy + SQL/phpMyAdmin. Причина: Plesk Git успішно копіює файли, але additional deployment actions не можуть запускати shell-команди через `execv("/bin/bash") failed system error: Permission denied`. Тому production-артефакт збирається локально або в GitHub Actions і завантажується на сервер уже з `vendor/` та `public/build/`; зміни БД виконуються окремими SQL-файлами через phpMyAdmin.

Для агентів: не питати щоразу про SSH/Terminal. Стандартний production stack цього проекту — **Plesk File Manager/FTP для файлів і phpMyAdmin для БД**. SSH/Terminal вважати недоступним, якщо користувач явно не змінить це рішення.

### Artifact deploy — поточний процес

Локально зібрати архів:

```bash
cd ~/Project/DITIB-Ahlen/portal
scripts/build-artifact.sh
```

Скрипт працює через тимчасову staging-папку в `/tmp`, тому не змінює локальні `vendor/`, `node_modules/` і `public/build/` у робочому проекті. Всередині staging він виконує:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Після цього створюється архів у `deploy-artifacts/ditib-ahlen-portal-YYYYMMDD-HHMMSS.tar.gz`.

Архів містить:

- весь Laravel-код (`app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `public/`, `storage/` тощо);
- готовий `vendor/`;
- готовий `public/build/`;
- `.env.example` для довідки.

Архів НЕ містить:

- `.env` та локальні `.env.*`;
- усі `*.md` файли документації;
- `node_modules/`;
- `database/database.sqlite`;
- локальну папку `==logs/`;
- локальні `storage/logs/*.log`, sessions, compiled views і cache data;
- `.git/`.

Завантаження на Plesk:

1. Відкрити Plesk → `mitglied.ditib-ahlen-projekte.de` → File Manager або FTP.
2. Перейти в корінь Laravel-проєкту: `mitglied.ditib-ahlen-projekte.de/`.
3. Завантажити архів.
4. Розпакувати архів у цю папку з перезаписом файлів.
5. Перевірити, що серверний `.env` залишився на місці і не був замінений.
6. Перевірити, що Document Root досі `mitglied.ditib-ahlen-projekte.de/public`.

Після artifact deploy без shell-команд Laravel працює без `config:cache`, `route:cache` і `view:cache`. Це повільніше за optimized deploy, але прийнятно для поточного масштабу порталу.

### Міграції БД при artifact deploy

Оскільки `php artisan migrate --force` на сервері недоступний і не є частиною поточного production stack, зміни БД потрібно виконувати окремо через phpMyAdmin.

Для першого production-деплою імпортувати SQL-схему з міграцій у MySQL. Для наступних деплоїв:

1. Перед деплоєм перевірити, чи додалися нові файли в `database/migrations/`.
2. Якщо міграцій немає, SQL-дії не потрібні.
3. Якщо міграції є, підготувати відповідний SQL-файл у `deploy-artifacts/`.
4. Імпортувати SQL через phpMyAdmin.
5. Перевірити таблицю `migrations`, щоб production не вважав міграцію невиконаною, якщо Artisan колись стане доступним.

Важливо: локальний SQLite (`database/database.sqlite`) ніколи не переносити на production.

### Plesk Git settings

Цей варіант поки не є основним через помилку shell execution. Залишено як довідку, якщо хостинг пізніше увімкне Deploy actions/Terminal.

У Plesk обирати **Install from remote repository**, не `Install Skeleton`.

| Поле | Значення |
|------|----------|
| Repository URL | `git@github.com:RomanPachkovskyi/DITIB-Ahlen-Portal.git` |
| SSH public key | додати цей ключ у GitHub repo → Settings → Deploy keys |
| Deploy key permissions | Read-only достатньо |
| Repository name | `DITIB-Ahlen-Portal` або `DITIB-Ahlen-Portal.git` |
| Deployment mode | `Automatic` після першої перевірки; `Manual` можна для першого тесту |
| Server path | `/mitglied.ditib-ahlen-projekte.de` |
| Additional deployment actions | увімкнути |

Server path має бути коренем Laravel-проєкту (`/mitglied.ditib-ahlen-projekte.de`), **не** `/mitglied.ditib-ahlen-projekte.de/public`. `public` задається окремо в Hosting Settings як Document Root.

### Як влаштований сервер

Plesk для subdomain створює окрему папку поруч із `httpdocs`:

```text
home directory/
├── httpdocs/                              # основний домен / landing
├── mitglied.ditib-ahlen-projekte.de/      # Laravel portal repo
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/                            # Document Root дивиться сюди
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                               # тільки на сервері, не в git
│   ├── artisan
│   ├── composer.json
│   └── package.json
└── logs/
```

Це не React/Vite-only сайт. На сервер деплоїться **весь Laravel-репозиторій**, а не тільки `public/build`. Відвідувачі сайту при цьому мають бачити тільки `public/`, тому Document Root обов'язково:

```text
mitglied.ditib-ahlen-projekte.de/public
```

Якщо Document Root буде `mitglied.ditib-ahlen-projekte.de` без `/public`, це небезпечно: браузеру можуть стати видимими Laravel-файли, `.env`, `storage`, `vendor` тощо.

### База даних на production

Production потребує MySQL у Plesk:

1. Створити MySQL database і database user у Plesk.
2. Прописати доступи в серверному `.env`.
3. Імпортувати schema/data SQL через phpMyAdmin.
4. Для наступних змін БД імпортувати окремі migration SQL-файли з `deploy-artifacts/`.

SQLite (`database/database.sqlite`) використовується тільки локально. Для production не переносити локальний SQLite-файл на сервер.

**Стан на 2026-05-06:** MySQL database/user створені в Plesk, серверний `.env` створений. Поточний deploy-стек зафіксований як artifact upload через File Manager/FTP + SQL import через phpMyAdmin.

**Перший manual deploy 2026-05-05:** deploy key validation успішний, файли скопійовані в `mitglied.ditib-ahlen-projekte.de`, але Deploy actions не виконались:

```text
execv("/bin/bash") failed system error: Permission denied
```

Це означає, що Git deploy працює, але Plesk/subscription user не має права запускати `/bin/bash`, через який Plesk виконує additional deployment actions. Поки це не виправлено, `composer install`, `npm ci`, `npm run build`, `php artisan migrate --force` і cache-команди не запускаються.

Через це Git deploy/Deploy actions лишаються довідковим, не основним шляхом. Не планувати production deploy навколо SSH, Terminal або `php artisan migrate --force`, якщо це рішення не буде явно переглянуте.

### Plesk Deploy actions — не активний процес

Plesk Git/Deploy actions залишені тільки як історична довідка. Поточний робочий процес не використовує серверні shell-команди, `composer`, `npm`, `php artisan migrate --force` або cache-команди на production.

Для production:

- файли: `scripts/build-artifact.sh` → завантажити архів через Plesk File Manager/FTP;
- база: SQL-файли з `deploy-artifacts/` → імпорт через phpMyAdmin;
- `.env`: створюється і редагується вручну в Plesk File Manager;
- `APP_KEY`: генерується локально один раз і вставляється вручну в production `.env`.

`APP_KEY` можна згенерувати локально:

```bash
php artisan key:generate --show
```

Після першого production-деплою `APP_KEY` не міняти, бо IBAN/BIC шифруються Laravel encrypted cast через цей ключ.

Admin-користувачі на production створюються через підготовлений SQL для phpMyAdmin або через Filament/адмін-інтерфейс, якщо він уже доступний. `php artisan make:filament-user` не є стандартним production-кроком для цього хостингу.

### `.env` на сервері

`.env` створюється вручну в `mitglied.ditib-ahlen-projekte.de/.env` і не комітиться в git:

**Як створити через Plesk File Manager:**

1. Відкрити Plesk → Domains → `mitglied.ditib-ahlen-projekte.de` → File Manager.
2. Зайти в папку `mitglied.ditib-ahlen-projekte.de` (корінь Laravel-проєкту).
3. Натиснути `+` / `New File`.
4. Назва файлу: `.env` (саме з крапкою на початку, без `.txt`).
5. Вставити вміст нижче і зберегти.

**Приклад для першого production-тесту:**

```env
APP_NAME="DITIB Ahlen"
APP_ENV=production
APP_KEY=base64:PASTE_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://mitglied.ditib-ahlen-projekte.de

APP_LOCALE=de
APP_FALLBACK_LOCALE=de
APP_FAKER_LOCALE=de_DE

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ditib_ahlen_portal
DB_USERNAME=ditib_portal_user
DB_PASSWORD=DITIB-Ahlen-Portal_2026!m5

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=mail.ditib-ahlen-projekte.de
MAIL_PORT=587
MAIL_USERNAME=info@ditib-ahlen-projekte.de
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@ditib-ahlen-projekte.de"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_BRAND_NAME="${APP_NAME}"
MAIL_BRAND_URL="${APP_URL}"
MAIL_LOGO_URL="https://mitglied.ditib-ahlen-projekte.de/images/ditib_ahlen_logo.png"
MAIL_BRAND_FOOTER="DITIB Türkisch-Islamische Gemeinde zu Ahlen e.V."

VITE_APP_NAME="${APP_NAME}"
```

`APP_KEY` згенерувати локально і вставити замість `base64:PASTE_GENERATED_KEY_HERE`:

```bash
php artisan key:generate --show
```

Якщо SSH/Terminal на сервері недоступні, це повністю замінює серверний `php artisan key:generate`. Після цього `APP_KEY` не міняти.

Якщо SMTP ще не готовий, `MAIL_PASSWORD` можна тимчасово залишити порожнім, але email-повідомлення не будуть працювати повноцінно.

**Мінімальна структура, яку завжди перевіряти:**

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mitglied.ditib-ahlen-projekte.de
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
MAIL_HOST=...
MAIL_FROM_ADDRESS=...
MAIL_LOGO_URL=...
```

Для першого тесту можна тимчасово залишити mail налаштування мінімальними, але перед перевіркою реєстраційних листів потрібно прописати реальний SMTP. Реєстраційні листи відправляються синхронно під час submit, бо на поточному Plesk artifact-deploy немає стабільного queue worker-а.

Брендування листів централізоване в `config/mail.php` → `mail.brand.*`. Для production мінімально перевірити `MAIL_BRAND_URL="${APP_URL}"`, публічний HTTPS `MAIL_LOGO_URL` і коректний footer. Якщо в майбутньому знадобиться повний pixel-control для Outlook/Gmail, перейти на окремий HTML-шаблон (`view:`), але поточний стандартний шар Laravel Markdown не змішувати з ручними HTML-вставками в окремих листах.

---

## Статус реалізації

### Етап 1 ✅ ВИКОНАНО
- [x] Репозиторій створено, структура папок
- [x] Laravel 13 + Filament v5 встановлені
- [x] Міграції: `members` (всі поля), `change_requests`
- [x] Модель `Member`: encrypted IBAN/BIC, auto member_number, всі fillable/casts
- [x] AdminPanel (`/admin`) — MemberResource з View/Edit/List, member_number першим
- [x] MemberPanel (`/konto`) — базова панель створена
- [x] Публічна форма (`/`) — 4-крокова, всі поля анкети, умовний SEPA, валідація 16+

### Етап 2 ✅ ВИКОНАНО
- [x] Автовизначення PLZ → місто і федеральна земля (OpenPLZ API)
- [x] Email клієнту після відправки форми (синхронно через SMTP, без queue worker)
- [x] Email адміну про нову заявку
- [x] Централізований branding layer для Laravel Markdown emails

### Етап 3 ✅ ВИКОНАНО
- [x] Dashboard адмінки зі статистикою (widgets)
- [x] Іконки навігації + логотип DITIB в адмінці
- [x] Email клієнту при підтвердженні реєстрації (Event + synchronous listener)
- [x] Email адміну і клієнту при видаленні запису члена
- [x] DSGVO-згода та SEPA-згода перенесені на Крок 3 форми
- [ ] spatie/laravel-settings — сторінка налаштувань (підпис, печатка)
- [ ] PDF підтвердження членства (Base64 для зображень)

### Етап 4 🔲
- [ ] Фото профілю (FilePond + Image Crop 1:1)
- [ ] Unterschrift canvas у формі реєстрації (Крок 4)
- [ ] Кабінет члена — перегляд даних, Änderungsantrag
- [ ] Двомовність (DE + TR): Middleware SetLocale, lang/de.json, lang/tr.json
- [x] Artifact deploy обрано як поточний процес: staging-збірка `vendor/` + `public/build/` + Laravel-код без зміни локальних dev-залежностей, завантаження через File Manager/FTP; міграції БД виконуються окремо через SQL/phpMyAdmin

> Зміни в статусі — оновлювати тут і писати в `CHANGELOG.md`
