# AGENTS.md — AI Agent Instructions
## DITIB-Ahlen-Portal

> **Цей файл — єдиний загальний instruction-документ для всіх AI агентів у цьому репозиторії.**
> Не створювати окремі паралельні правила на кшталт `CLAUDE.md`, `GEMINI.md` тощо. Якщо конкретному інструменту потрібен compatibility-файл, він має тільки посилатися на `AGENTS.md`, не дублювати правила.
> Тут — операційні правила, команди й середовище. Актуальний стан, архітектура, плани, побажання і повний deploy-процес ведуться в `PROJECT.md`.

---

## Три документи проекту

| Файл | Призначення | Коли читати |
|------|-------------|-------------|
| **`AGENTS.md`** ← ти тут | Єдині правила для всіх агентів, команди, середовище | Завжди, першим |
| **`PROJECT.md`** | Архітектура, стек, функціональність, деплой, плани і побажання | Перед початком роботи над кодом |
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
| **8082** | Лендінг `main/` | Docker (`docker compose up` у `../main`) |
| **8000** | Портал `portal/` | Homebrew PHP: `php artisan serve --port=8000` |

> ⛔ Не переналаштовувати ці порти. Портал не запускати через Docker Desktop на 8083/5173/8383. Для порталу істина — тільки `http://localhost:8000`.

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
- Production deploy stack: **artifact upload через Plesk File Manager/FTP + SQL import через phpMyAdmin**. Серверні shell-команди на хостингу недоступні за умовами хостера; не планувати production deploy навколо серверних `composer`, `npm`, `php artisan migrate` або cache-команд.
- Повний production deploy-процес описаний у `PROJECT.md`; не дублювати його тут.
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

## Production deploy — коротке правило для агентів

Повний процес ведеться в `PROJECT.md`. Тут лишається тільки operational guardrail.

Production-архів збирається тільки командою з робочої папки:

```bash
cd ~/Project/DITIB-Ahlen/portal
scripts/build-artifact.sh
```

Не запускати `composer install --no-dev` або `npm ci` у робочій папці для production-збірки. Скрипт робить це у staging-папці в `/tmp`, щоб локальні `vendor/`, `node_modules/` і `public/build/` не ламали dev-цикл.

Production DB зміни виконуються через SQL-файли для phpMyAdmin, не через серверний `php artisan migrate`. Якщо додається міграція, потрібно підготувати відповідний SQL у `deploy-artifacts/` і зафіксувати це в `PROJECT.md`/`CHANGELOG.md`.

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
- AgentName: `Codex`, `Claude Code`, `Gemini`
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
6. **Member numbers** — `member_number` ніколи не перевикористовувати; видавати тільки через `MemberNumberSequence`, видалення `Member` має бути soft delete
7. **Admin member lifecycle** — в UI адмін не видаляє членів; для завершення членства переводить `status` у `inactive`
8. **Admin consent fields** — SEPA/DSGVO consent і `zustimmung_at` у edit UI мають лишатися read-only; адмін не редагує згоду клієнта
9. **Commit** — англійська, коротко: `feat:`, `fix:`, `docs:`, `chore:`
10. **Гілка** — завжди `main` для цього репо
11. **Не чіпати** `~/Project/DITIB-Ahlen/main/` — інший проект
12. **Filament v5** — `Section` → `Filament\Schemas\Components\Section` (не Forms)

---

## Безпека (DSGVO)

- IBAN, BIC — `'encrypted'` cast → зашифровані в БД
- `$hidden` у моделі не використовувати для IBAN/BIC — блокує Filament
- Члени в `/konto` бачать тільки записи з email authenticated user; один email може мати кілька записів родини/фірми
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
