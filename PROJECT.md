# DITIB Ahlen — Mitglieder-Portal

## Про проект

Портал для управління членами ісламської громади DITIB Ahlen.
Пов'язаний з лендінгом [ditib-ahlen-projekte.de](https://ditib-ahlen-projekte.de) — на ньому є кнопка переходу до порталу.

Очікувана кількість членів: до 500 осіб.

---

## Репозиторії

| Проект | GitHub | Локально |
|--------|--------|----------|
| Лендінг | `DITIB-Ahlen` | `~/Project/DITIB-Ahlen/main/` |
| Портал | `DITIB-Ahlen-Portal` | `~/Project/DITIB-Ahlen/portal/` |

Два незалежних репозиторії — два паралельних проекти.

---

## Стек

| Шар | Технологія |
|-----|-----------|
| Backend | Laravel 13 |
| Admin-панель | Filament v5 |
| Реактивність | Livewire v4 |
| Стилі | Tailwind CSS v4 |
| База даних (local) | SQLite |
| База даних (production) | MySQL (Plesk хостинг) |
| Email | Laravel Mail → SMTP хостингу |
| PDF | barryvdh/laravel-dompdf |
| Підпис | Filament Signature Pad плагін (обирається при встановленні) |

---

## Функціональність

### Публічна частина (`/`)
- Форма вступу до громади (Mitgliedsantrag) — DE + TR
- Сторінка підтвердження після відправки

### Кабінет члена (`/konto`)
- Вхід через email (magic link, без пароля)
- Перегляд власних даних
- Подача запиту на зміну даних (Änderungsantrag)
- Перегляд статусу заявки

### Адмін-панель (`/admin`) — Filament
- Список усіх членів з пошуком і фільтрами
- Схвалення / відхилення нових заявок
- Обробка запитів на зміну даних
- Експорт у CSV

---

## Поля форми (Mitgliedsantrag)

| Поле | Тип | Обов'язкове |
|------|-----|-------------|
| Vor- und Nachname | Text | Так |
| Straße und Hausnummer | Text | Так |
| Ort | Text | Так |
| Bundesland | Text | Так |
| Postleitzahl | Text | Так |
| Geburtsdatum | Date | Так |
| E-Mail | Email | Так |
| Telefonnummer | Phone | Так |
| Jahresbeitrag | Currency (мін. €36) | Так |
| Kontoinhaber | Text | Так |
| IBAN | Text (зашифровано в БД) | Так |
| BIC | Text (зашифровано в БД) | Ні |
| Kreditinstitut | Text | Ні |
| Unterschrift | Signature (canvas) | Так |
| SEPA Zustimmung | Checkbox | Так |
| DSGVO Zustimmung | Checkbox | Так |

---

## Локальна розробка

| Параметр | Значення |
|----------|----------|
| Папка | `~/Project/DITIB-Ahlen/portal/` |
| PHP | 8.5 (Homebrew) |
| База даних | SQLite → `database/database.sqlite` |
| Порт | **8000** |
| Адмін-панель | http://localhost:8000/admin |
| Публічна форма | http://localhost:8000 |
| Кабінет члена | http://localhost:8000/konto |

**Запуск сервера:**
```bash
cd ~/Project/DITIB-Ahlen/portal
php artisan serve --port=8000
```

**Перший запуск (після клонування):**
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan make:filament-user
php artisan serve --port=8000
```

**Тест адмін-доступу:**
```
URL:      http://localhost:8000/admin/login
Email:    rpachkovskyi@gmail.com
Password: Admin1234!
```

---

## Хостинг і деплой

| Параметр | Значення |
|----------|----------|
| Хостинг | Plesk (virtual hosting) |
| Домен | `mitglied.ditib-ahlen-projekte.de` |
| PHP на сервері | 8.2+ |
| База даних | MySQL (Plesk) |
| Deploy | git push main → Plesk Git → auto-deploy |

**Deploy actions на Plesk (після кожного push):**
```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Змінні середовища на Plesk (`.env` на сервері, не в git):**
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mitglied.ditib-ahlen-projekte.de
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
MAIL_HOST=...   # SMTP хостингу ditib-ahlen-projekte.de
```

---

## Безпека та DSGVO

- IBAN і BIC зберігаються в БД у зашифрованому вигляді через Laravel `encrypted` cast
- `.env` з ключами шифрування — ніколи не комітити в git
- Кожен член бачить тільки власні дані (Filament Panel ізоляція)
- Згода SEPA і DSGVO фіксується при відправці форми

---

## Workflow для AI агентів

- **Claude Code** — архітектура, команди, файли
- **Codex** — code completion в IDE
- **Gemini (Antigravity)** — додатковий асистент

### Правила
1. Commit повідомлення — коротко, англійською
2. `.env` — ніколи не комітити
3. Міграції — тільки через `php artisan make:migration`
4. Бізнес-логіку тримати чистою — без прив'язки до конкретної організації (на майбутнє)

---

## Статус

- [x] Репозиторій створено окремо від лендінгу
- [x] PROJECT.md написаний
- [x] Laravel 13 ініціалізований
- [x] Filament v5 встановлений
- [ ] Форма вступу розроблена
- [ ] Адмін-панель налаштована
- [ ] Кабінет члена розроблений
- [ ] Деплой на Plesk налаштований
