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

*Цей файл ведеться вручну всіма агентами. Не видаляти, не перейменовувати.*
