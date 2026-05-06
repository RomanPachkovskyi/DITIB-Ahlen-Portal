# CLAUDE.md — AI Agent Instructions
## DITIB-Ahlen-Portal

> **Цей файл читається автоматично кожним AI агентом при відкритті проекту.**
> Тут — операційні правила, команди, середовище.

---

## Три документи проекту

| Файл | Призначення | Коли читати |
|------|-------------|-------------|
| **`CLAUDE.md`** ← ти тут | Правила для агентів, команди, середовище | Завжди, першим |
| **`PROJECT.md`** | Архітектура, стек, функціональність, деплой | Перед початком роботи над кодом |
| **`CHANGELOG.md`** | Хронологія всіх змін з підписами агентів | Перед змінами і після |

**Обов'язковий порядок для нового агента:**
1. Прочитай `CLAUDE.md` (цей файл) — правила і середовище
2. Прочитай `PROJECT.md` — що вже збудовано і як
3. Прочитай `CHANGELOG.md` — що змінювалось останнім часом
4. Тільки потім починай роботу

---

## Проект

Laravel 13 + Filament v5 портал членів громади DITIB Ahlen.
Пов'язаний з лендінгом [ditib-ahlen-projekte.de](https://ditib-ahlen-projekte.de).
Детальна архітектура → `PROJECT.md`

---

## Порти (фіксовано — не змінювати)

| Порт | Проект | Запуск |
|------|--------|--------|
| **8080** | Лендінг `main/` | Docker |
| **8000** | Портал `portal/` | `php artisan serve --port=8000` |

---

## Середовище

| | Local | Production |
|--|-------|------------|
| Форма | http://localhost:8000 | https://mitglied.ditib-ahlen-projekte.de |
| Admin | http://localhost:8000/admin | https://mitglied.ditib-ahlen-projekte.de/admin |
| Konto | http://localhost:8000/konto | https://mitglied.ditib-ahlen-projekte.de/konto |
| DB | SQLite (`database/database.sqlite`) | MySQL (Plesk) |
| PHP | 8.5 (Homebrew, не Docker) | 8.4+ |

---

## Хостинг Plesk — важливо

- Subdomain `mitglied.ditib-ahlen-projekte.de` має окрему папку поруч із `httpdocs`: `mitglied.ditib-ahlen-projekte.de/`
- Document Root домену: `mitglied.ditib-ahlen-projekte.de/public`
- З браузера має бути доступна тільки папка `public/`, не корінь Laravel-проєкту
- Production DB: MySQL у Plesk; SQLite використовується тільки локально
- `.env` створюється вручну на сервері та ніколи не комітиться
- `APP_KEY` на production згенерувати один раз і не міняти: шифрує IBAN/BIC у БД

---

## Запуск локально

```bash
cd ~/Project/DITIB-Ahlen/portal
php artisan serve --port=8000
```

**Після клонування (перший раз):**
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan make:filament-user
php artisan serve --port=8000
```

---

## Ключові команди

```bash
php artisan migrate                        # застосувати міграції
php artisan migrate:rollback               # відкотити останню міграцію
php artisan make:migration <name>          # нова міграція (ТІЛЬКИ ТАК)
php artisan make:model <Name> -m           # модель + міграція
php artisan make:filament-resource <Name>  # Filament ресурс
php artisan config:clear                   # скинути кеш конфігу
php artisan cache:clear                    # скинути кеш
./vendor/bin/phpunit                       # запустити тести
```

---

## Структура проекту

```
app/
├── Livewire/
│   └── MembershipForm.php          ← публічна форма (4 кроки)
├── Models/
│   ├── Member.php                   ← encrypted: iban, bic; auto member_number
│   └── ChangeRequest.php
├── Support/
│   └── Iban.php                     ← normalize, display-format, validate IBAN
├── Filament/Resources/Members/     ← адмін ресурс
└── Providers/Filament/
    ├── AdminPanelProvider.php       ← /admin
    └── MemberPanelProvider.php      ← /konto
resources/views/
├── livewire/membership-form.blade.php
└── layouts/public.blade.php
database/migrations/
scripts/
└── build-artifact.sh               ← staging artifact build (не запускати вручну в prod)
```

---

## Artifact deploy — стабільний процес

Production-архів збирається тільки командою:

```bash
cd ~/Project/DITIB-Ahlen/portal
scripts/build-artifact.sh
```

Скрипт працює через тимчасову staging-папку в `/tmp`:
- `composer install --no-dev`, `npm ci`, `npm run build` виконуються тільки в staging;
- нормалізує права: директорії `755`, файли `644`, `artisan` + shell-скрипти `755`;
- виключає `*.md`, `==logs/`, локальну SQLite, node_modules, runtime/cache;
- результат: `deploy-artifacts/ditib-ahlen-portal-*.tar.gz`.

**Заборона:** не запускати `composer install --no-dev` або `npm ci` у робочій папці — це прибирає PHPUnit та інші dev-залежності.

**Перевірка artifact перед deploy:**
```bash
tar -tvzf deploy-artifacts/ditib-ahlen-portal-*.tar.gz | head
# Перший рядок має бути drwxr-xr-x ./  (права 755, не 700!)
```

Стабільний цикл: `./vendor/bin/phpunit` → `scripts/build-artifact.sh` → завантажити на Plesk → локальні правки без відновлення залежностей.

---

## Email (синхронно, без queue worker)

- Registration emails відправляються **синхронно** через SMTP при submit форми.
- `ShouldQueue` НЕ використовується — на Plesk Plesk немає стабільного queue worker.
- При збої SMTP помилка логується, але збереження анкети не ламається.
- Events: `MemberRegistered` → `SendRegistrationEmails` (виявляється Laravel auto-discovery).
- Листи: клієнту при реєстрації, при підтвердженні (`active`), при видаленні запису; адміну при новій заявці і при видаленні.

---

## IBAN

- `App\Support\Iban` — normalize (canonical без пробілів), display-format `DE 42 4005 0150...`, структурна валідація.
- Форма приймає IBAN з пробілами та малими літерами, показує в форматованому вигляді, в БД — canonical.
- Filament admin form: такий самий format display + dehydrate до canonical.

---

## Адміністратори Filament

Доступ до `/admin` обмежений у `FilamentUser::canAccessPanel()`:
- `rpachkovskyi@gmail.com`
- `info@ditib-ahlen-projekte.de`

`/konto` — доступно для будь-якого authenticated user.

---

## CHANGELOG — обов'язково після кожної сесії

**Файл:** `CHANGELOG.md`

Після будь-яких змін — додати запис у кінець файлу:
```
### [YYYY-MM-DD HH:MM] Короткий опис — AgentName
- що зроблено / змінено / виправлено
```

**Правила changelog:**
- Дата і час — реальні
- AgentName: `Claude Code`, `Codex`, `Gemini`
- Один запис на сесію роботи
- Нові записи — завжди знизу
- Не редагувати чужі записи

---

## Обов'язкові правила

1. **`CHANGELOG.md`** — оновлювати після кожної сесії
2. **`PROJECT.md`** — оновлювати статус і архітектуру при великих змінах
3. **`.env`** — ніколи не комітити, тільки `.env.example`
4. **Міграції** — тільки через `php artisan make:migration`
5. **IBAN і BIC** — обов'язково `'encrypted'` cast у моделі (DSGVO)
6. **Email** — синхронно через SMTP, без `ShouldQueue`
7. **Commit** — англійська, коротко: `feat:`, `fix:`, `docs:`, `chore:`
8. **Гілка** — завжди `main` для цього репо
9. **Не чіпати** `~/Project/DITIB-Ahlen/main/` — інший проект (лендінг)
10. **Filament v5** — `Section` → `Filament\Schemas\Components\Section` (не Forms)
11. **Artifact** — `scripts/build-artifact.sh` тільки для production, не запускати залежності вручну у робочій папці

---

## Безпека (DSGVO)

- IBAN, BIC — `'encrypted'` cast → зашифровані в БД через `APP_KEY`
- `$hidden` у моделі не використовувати для IBAN/BIC — блокує Filament
- Члени бачать тільки свої дані (Filament Panel ізоляція)
- `.env` з `APP_KEY` — ніколи в git
- `APP_KEY` не міняти після першого production deploy — IBAN/BIC стануть нечитабельними

---

## Git

```bash
# Репозиторій
git@github.com:RomanPachkovskyi/DITIB-Ahlen-Portal.git

# Стандартний цикл
git add <files>
git commit -m "feat: short description"
git push origin main
```
