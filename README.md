# DITIB-Ahlen-Portal

Laravel 13 + Filament v5 портал для реєстрації та адміністрування членів громади DITIB Ahlen.

Production: <https://mitglied.ditib-ahlen-projekte.de>

## Документація

Документація проекту навмисно зведена до малого набору файлів:

| Файл | Призначення |
|------|---------|
| `AGENTS.md` | Спільні правила, команди й середовище для всіх AI-агентів |
| `PROJECT.md` | Архітектура, поточний стан, функціональність, deploy-процес, плани і backlog |
| `CHANGELOG.md` | Хронологія кожної робочої сесії і зміни |
| `CLAUDE.md` | Compatibility pointer для Claude Code; не має дублювати правила |

Перед змінами в коді читати: `AGENTS.md`, потім `PROJECT.md`, потім `CHANGELOG.md`.

## Локальна Розробка

Фіксований локальний URL порталу:

```bash
http://localhost:8000
```

Перший запуск після клонування:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan make:filament-user
php artisan serve --port=8000
```

Звичайний запуск:

```bash
php artisan serve --port=8000
```

Тести:

```bash
./vendor/bin/phpunit
```

## Production Deploy

Повний production deploy-процес описаний у `PROJECT.md`.

Коротко:

- production hosting — Plesk;
- deploy — artifact upload через Plesk File Manager/FTP;
- зміни БД — SQL-файли через phpMyAdmin;
- серверні shell-команди не входять у поточний hosting-процес;
- на сервер деплоїться весь Laravel-застосунок, не тільки `public/build`;
- production `.env` створюється вручну на сервері й ніколи не комітиться.

Production artifact збирається тільки командою:

```bash
scripts/build-artifact.sh
```

Не запускати production `composer install --no-dev` або `npm ci` напряму в робочій папці. Artifact script виконує ці команди у тимчасовій staging-папці.

## Безпека

- IBAN і BIC шифруються через Laravel encrypted casts.
- Production `APP_KEY` генерується один раз і має лишатися стабільним, бо він розшифровує encrypted member data.
- `.env` файли ніколи не комітяться.
- Member-facing URLs використовують `member_number`, не внутрішній database `id`.
