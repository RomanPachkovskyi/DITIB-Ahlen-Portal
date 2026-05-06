# AGENTS.md — AI Agent Instructions
## DITIB-Ahlen-Portal

> **Цей файл читається автоматично кожним AI агентом при відкритті проекту.**
> Тут — операційні правила, команди, середовище.

---

## Три документи проекту

| Файл | Призначення | Коли читати |
|------|-------------|-------------|
| **`AGENTS.md`** ← ти тут | Правила для агентів, команди, середовище | Завжди, першим |
| **`PROJECT.md`** | Архітектура, стек, функціональність, деплой | Перед початком роботи над кодом |
| **`CHANGELOG.md`** | Хронологія всіх змін з підписами агентів | Перед змінами і після |

**Обов'язковий порядок для нового агента:**
1. Прочитай `AGENTS.md` (цей файл) — правила і середовище
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
- Це Laravel-портал, тому на сервер деплоїться **весь репозиторій**, не тільки `public/build`
- Document Root домену має бути: `mitglied.ditib-ahlen-projekte.de/public`
- З браузера має бути доступна тільки папка `public/`, не корінь Laravel-проєкту
- `public/build` — це лише Vite assets (CSS/JS), не сам застосунок
- Production DB: MySQL у Plesk; SQLite використовується тільки локально
- `.env` створюється вручну на сервері та ніколи не комітиться
- `APP_KEY` на production згенерувати один раз і не міняти: він потрібен для encrypted IBAN/BIC

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
```

---

## Структура проекту

```
app/
├── Livewire/
│   └── MembershipForm.php          ← публічна форма
├── Models/
│   ├── Member.php                   ← encrypted: iban, bic
│   └── ChangeRequest.php
├── Filament/Resources/Members/     ← адмін ресурс
└── Providers/Filament/
    ├── AdminPanelProvider.php       ← /admin
    └── MemberPanelProvider.php      ← /konto
resources/views/
├── livewire/membership-form.blade.php
└── layouts/public.blade.php
database/migrations/
```

---

## Artifact deploy — стабільний локальний процес

Production-архів збирається тільки командою:

```bash
cd ~/Project/DITIB-Ahlen/portal
scripts/build-artifact.sh
```

Скрипт працює через тимчасову staging-папку в `/tmp`:
- копіює туди проект без `.env`, `*.md`, `==logs/`, `node_modules/`, локальної SQLite та локальних runtime/cache файлів;
- у staging виконує `composer install --no-dev --optimize-autoloader`;
- у staging виконує `npm ci` і `npm run build`;
- нормалізує права доступу перед пакуванням: директорії `755`, файли `644`, `artisan` і shell-скрипти `755`;
- пакує готовий Laravel artifact із production `vendor/` та `public/build/`;
- видаляє staging-папку.

**Важливо для агентів:** не запускати `composer install --no-dev` і `npm ci` у робочій папці проекту для production-збірки. Це ламає локальний dev-цикл, бо прибирає dev-залежності на кшталт PHPUnit. Після `scripts/build-artifact.sh` локальні `vendor/`, `node_modules/` і `public/build/` мають залишатись недоторканими.

**Важливо для Plesk:** artifact не повинен містити root `./` з правами `700`, інакше Apache дає `403 Forbidden` на весь сайт після розпакування. Перед release перевірити:

```bash
tar -tvzf deploy-artifacts/ditib-ahlen-portal-*.tar.gz | head
```

Перший рядок має бути `drwxr-xr-x` для `./`.

Стабільний цикл:

```bash
./vendor/bin/phpunit
scripts/build-artifact.sh
# завантажити deploy-artifacts/ditib-ahlen-portal-*.tar.gz на Plesk
# продовжувати локальні правки без відновлення залежностей
```

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
- AgentName: `Codex`, `Codex`, `Gemini`
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
6. **Commit** — англійська, коротко: `feat:`, `fix:`, `docs:`, `chore:`
7. **Гілка** — завжди `main` для цього репо
8. **Не чіпати** `~/Project/DITIB-Ahlen/main/` — інший проект
9. **Filament v5** — `Section` → `Filament\Schemas\Components\Section` (не Forms)

---

## Безпека (DSGVO)

- IBAN, BIC — `'encrypted'` cast → зашифровані в БД
- `$hidden` у моделі не використовувати для IBAN/BIC — блокує Filament
- Члени бачать тільки свої дані (Filament Panel ізоляція)
- `.env` з `APP_KEY` — ніколи в git

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
