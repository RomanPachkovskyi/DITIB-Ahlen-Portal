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
| Email | Laravel Mail → SMTP + Queues | — |
| PDF | barryvdh/laravel-dompdf | встановлено |
| Підпис | Alpine.js canvas | вбудований |
| Налаштування адміна | spatie/laravel-settings | Етап 3 |
| Черги (email/PDF) | Laravel Queue (database driver) | Етап 2–3 |

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
- Обробка запитів на зміну даних

---

## База даних

### Таблиця `members`

| Поле | Тип | Примітка |
|------|-----|---------|
| member_number | string(20) | unique, auto DA-YYYY-NNNN |
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
- `$hidden` у моделі НЕ використовувати для IBAN/BIC (блокує Filament форму)
- Члени бачать тільки свої дані (Filament Panel ізоляція)
- Згода SEPA і DSGVO фіксується з timestamp при відправці форми
- `.env` з `APP_KEY` — ніколи не комітити в git

---

## Архітектурні рішення

### Email — не унікальний (v1.0)
Один email може використовуватись для кількох членів. Причина: літні члени громади не мають власної пошти — діти або родичі реєструють їх на свій email.

> **Майбутнє (Етап 4+):** При реалізації кабінету (`/konto`) входу через email-посилання (magic link) потрібно продумати логіку вибору облікового запису, якщо на один email зареєстровано кількох членів (наприклад, показати список і дати вибрати, або використовувати `member_number` як додатковий ідентифікатор).

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

**Запуск:**
```bash
cd ~/Project/DITIB-Ahlen/portal && php artisan serve --port=8000
```

**Build локально:**
```bash
npm run build
```

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

**Обраний робочий процес з 2026-05-06:** artifact deploy. Причина: Plesk Git успішно копіює файли, але additional deployment actions не можуть запускати shell-команди через `execv("/bin/bash") failed system error: Permission denied`. Тому production-артефакт збирається локально або в GitHub Actions і завантажується на сервер уже з `vendor/` та `public/build/`.

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

Оскільки `php artisan migrate --force` на сервері недоступний, зміни БД потрібно виконувати окремо через phpMyAdmin.

Для першого production-деплою імпортувати SQL-схему з міграцій у MySQL. Для наступних деплоїв:

1. Перед деплоєм перевірити, чи додалися нові файли в `database/migrations/`.
2. Якщо міграцій немає, SQL-дії не потрібні.
3. Якщо міграції є, підготувати відповідний SQL вручну або згенерувати на тестовій MySQL-базі.
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
3. Запустити `php artisan migrate --force`.

SQLite (`database/database.sqlite`) використовується тільки локально. Для production не переносити локальний SQLite-файл на сервер.

**Стан на 2026-05-05:** MySQL database/user створені в Plesk, серверний `.env` створений, Plesk Git deploy налаштований. Перший deploy виконувати вручну, щоб перевірити лог і помилки до увімкнення регулярного automatic deploy.

**Перший manual deploy 2026-05-05:** deploy key validation успішний, файли скопійовані в `mitglied.ditib-ahlen-projekte.de`, але Deploy actions не виконались:

```text
execv("/bin/bash") failed system error: Permission denied
```

Це означає, що Git deploy працює, але Plesk/subscription user не має права запускати `/bin/bash`, через який Plesk виконує additional deployment actions. Поки це не виправлено, `composer install`, `npm ci`, `npm run build`, `php artisan migrate --force` і cache-команди не запускаються.

Що перевірити в Plesk:

1. Domains → `mitglied.ditib-ahlen-projekte.de` → Hosting & DNS → Web Hosting Access.
2. Для системного користувача увімкнути shell access (`/bin/bash` або `/bin/sh`, якщо доступно).
3. Якщо shell access недоступний у тарифі, попросити хостинг увімкнути виконання Plesk Git deploy actions або надати Plesk Terminal/SSH.
4. Після зміни доступу повторити manual deploy.

Якщо хостинг не дозволяє shell/Deploy actions взагалі, потрібна fallback-стратегія без серверних команд. Варіанти нижче, від найкращого до найменш бажаного.

### Fallback без SSH / shell

#### Варіант A — попросити хостинг увімкнути тільки Deploy actions

Найкращий варіант, якщо SSH як інтерактивний доступ не дають, але можуть дозволити Plesk Git additional deployment actions або Plesk Terminal для subscription user.

Текст для хостингу:

```text
Plesk Git deployment copies files successfully, but additional deployment actions fail with:
execv("/bin/bash") failed system error: Permission denied

Please enable shell execution for the subscription user only for Plesk Git additional deployment actions, or provide Plesk Terminal for this domain.
Interactive SSH access is not required if deployment actions can run.
Domain: mitglied.ditib-ahlen-projekte.de
```

#### Варіант B — використати Plesk UI-інструменти замість shell

Перевірити, чи доступні в Plesk:

- **PHP Composer**: може виконати `composer install` через UI.
- **Node.js**: може виконати `npm ci` / `npm run build` через UI.
- **Scheduled Tasks**: іноді дозволяє запускати PHP-команди навіть без SSH.
- **phpMyAdmin**: можна імпортувати SQL для структури БД, якщо `php artisan migrate` неможливий.

Цей варіант залежить від конкретного тарифу/розширень Plesk.

#### Варіант C — artifact deploy через Git/File Manager/FTP

Якщо на сервері не можна запускати жодні команди, збирати все локально або в GitHub Actions:

1. Локально/CI виконати `composer install --no-dev`, `npm ci`, `npm run build`.
2. Завантажити/доставити на сервер уже готові `vendor/` і `public/build/`.
3. Для БД не запускати міграції на сервері, а імпортувати SQL через phpMyAdmin.

Мінуси:

- `vendor/` великий і зазвичай не комітиться.
- Deploy стає повільнішим і менш чистим.
- Кожна зміна міграцій потребує SQL-експорту/імпорту.

#### Варіант D — тимчасовий захищений web-deploy endpoint

Можна зробити тимчасовий route/controller з секретним токеном, який запускає потрібні Artisan-команди через HTTP. Це технічно можливо, але ризиковано.

Використовувати тільки як тимчасовий аварійний інструмент:

- обмежити секретним токеном;
- бажано обмежити IP;
- не логувати секрети;
- видалити route одразу після першого деплою.

Для production це не рекомендований постійний процес.

#### Варіант E — інший тариф/хостинг/VPS

Найчистіший довгостроковий варіант для Laravel: тариф або VPS із SSH/Terminal, Composer, Node і cron/queue support. Для Filament/Livewire/Laravel це значно зменшить ризики при майбутніх оновленнях.

### Deploy actions на Plesk

Якщо SSH/Terminal на хостингу недоступні, усі регулярні команди виконуються через **Plesk Git → Deploy actions**.

Перед першим автодеплоєм створити `.env` на сервері та налаштувати MySQL. `APP_KEY` можна:

- згенерувати локально командою `php artisan key:generate --show` і вставити значення вручну в серверний `.env`;
- або тимчасово додати `php artisan key:generate --force` у Deploy actions тільки для першого запуску, а після успіху одразу прибрати.

Переважний варіант без SSH: **згенерувати APP_KEY локально і вставити вручну**. Так немає ризику випадково змінити ключ на наступному деплої.

```bash
php artisan key:generate --show
```

Після першого production-деплою `APP_KEY` не міняти, бо IBAN/BIC шифруються Laravel encrypted cast через цей ключ.

У поле **Deploy actions** у Plesk вставити команди, кожну з нового рядка:

```bash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`npm ci` використовується, бо в репозиторії є `package-lock.json`; це стабільніше для production, ніж `npm install`.

Якщо Plesk не знаходить `composer`, `npm` або потрібну версію `php`, треба вказати повні шляхи з Plesk/PHP settings або виконувати ці команди через Plesk Terminal у правильному PHP/Node середовищі.

Повторні ручні деплої виконують той самий набір команд:
```bash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Після першого деплою створити адміна:
```bash
php artisan make:filament-user
```

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
```

Для першого тесту можна тимчасово залишити mail налаштування мінімальними, але перед перевіркою реєстраційних листів потрібно прописати реальний SMTP і перевірити черги (`QUEUE_CONNECTION=database`).

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
- [x] Email клієнту після відправки форми (через Queue)
- [x] Email адміну про нову заявку

### Етап 3 ✅ ВИКОНАНО
- [x] Dashboard адмінки зі статистикою (widgets)
- [x] Іконки навігації + логотип DITIB в адмінці
- [x] Email клієнту при підтвердженні реєстрації (Event + Job)
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
