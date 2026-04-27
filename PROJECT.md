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
| Backend | Laravel 11 |
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

## Хостинг і деплой

- **Хостинг:** Plesk (virtual hosting)
- **Домен:** `mitglied.ditib-ahlen-projekte.de`
- **Deploy:** git push main → Plesk Git інтеграція → auto-deploy
- **Deploy actions на Plesk (після кожного push):**
```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Локальна розробка

```bash
php artisan serve   # → localhost:8000
```

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
- [ ] Laravel ініціалізований
- [ ] Filament встановлений
- [ ] Форма вступу розроблена
- [ ] Адмін-панель налаштована
- [ ] Кабінет члена розроблений
- [ ] Деплой на Plesk налаштований
