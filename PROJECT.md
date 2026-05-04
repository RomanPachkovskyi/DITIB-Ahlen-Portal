# PROJECT.md — Архітектура і стан
## DITIB-Ahlen-Portal

> Тут — архітектура, стек, функціональність, деплой. Актуальний стан проекту.
> Правила для агентів → `CLAUDE.md` | Історія змін → `CHANGELOG.md`

---

## Три документи проекту

| Файл | Призначення |
|------|-------------|
| **`CLAUDE.md`** | Правила для агентів, команди, середовище — читати першим |
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
| email | string | unique |
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

---

## Хостинг і деплой

| Параметр | Значення |
|----------|----------|
| Хостинг | Plesk (virtual hosting) |
| Домен | `mitglied.ditib-ahlen-projekte.de` |
| PHP | 8.2+ |
| DB | MySQL (Plesk) |
| Deploy | git push main → Plesk Git → auto-deploy |

**Deploy actions на Plesk:**
```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**`.env` на сервері (не в git):**
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mitglied.ditib-ahlen-projekte.de
DB_CONNECTION=mysql
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
MAIL_HOST=...
```

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
- [ ] Деплой на Plesk налаштований

> Зміни в статусі — оновлювати тут і писати в `CHANGELOG.md`
