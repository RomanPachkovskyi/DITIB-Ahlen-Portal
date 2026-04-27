# CHANGELOG — DITIB-Ahlen-Portal

Хронологія змін. Кожен агент **зобов'язаний** додавати запис при будь-яких змінах у проекті.

## Формат запису

```
### [YYYY-MM-DD HH:MM] Короткий опис — AgentName
- що зроблено
- що змінено
- що виправлено
```

---

## 2026-04-27

### [2026-04-27 09:00] Ініціалізація проекту — Claude Code
- Створено окремий GitHub репозиторій `DITIB-Ahlen-Portal`
- Створено локальну папку `~/Project/DITIB-Ahlen/portal/`
- Написано `PROJECT.md` з архітектурою, стеком, правилами

### [2026-04-27 10:00] Laravel + Filament встановлені — Claude Code
- `composer create-project laravel/laravel` → Laravel 13.6.0
- `composer require filament/filament:"^5.0"` → Filament v5.6
- `php artisan filament:install --panels` → AdminPanel на `/admin`
- Налаштовано SQLite для локальної розробки
- Створено адмін-користувача `rpachkovskyi@gmail.com`
- Зафіксовано порти: Portal=8000, Docker/Landing=8080

### [2026-04-27 10:30] База даних і моделі — Claude Code
- Міграція `create_members_table` — всі поля анкети Mitgliedsantrag
- Міграція `create_change_requests_table` — запити на зміну даних
- Модель `Member` з `encrypted` cast для IBAN і BIC (DSGVO)
- Модель `ChangeRequest` з relaton до `Member`

### [2026-04-27 11:00] Filament AdminPanel — MemberResource — Claude Code
- `MemberResource` з навігацією "Mitglieder"
- `MemberForm` — форма з секціями: Persönliche Daten, Bankverbindung, Status
- `MembersTable` — таблиця з badge-статусом, фільтром, сортуванням
- `ViewMember` сторінка для перегляду запису
- Другий Filament panel `/konto` для кабінету члена

### [2026-04-27 11:30] Публічна форма Mitgliedsantrag — Claude Code
- Livewire компонент `MembershipForm` — 3-крокова форма
- Крок 1: Персональні дані
- Крок 2: Банківські дані (SEPA)
- Крок 3: Підпис (canvas) + SEPA/DSGVO згода
- Layout `layouts/public.blade.php` з хедером DITIB Ahlen
- Маршрут `/` → форма, після відправки — сторінка підтвердження

### [2026-04-27 12:00] Виправлення багів — Claude Code
- **Fix:** `Section` namespace в Filament v5 → `Filament\Schemas\Components\Section`
- **Fix:** Видалено зайвий `BadgeColumn` import (не існує в v5)
- **Fix:** Signature canvas — перепис на Alpine.js з `$nextTick` для правильної ширини
- **Fix:** IBAN/BIC прибрані з `$hidden` у моделі — encrypted cast достатньо для захисту, `$hidden` блокував відображення в Filament
- **Cleanup:** Видалено артефактний файл `=log/# Error`

---

*Цей файл ведеться вручну. Не видаляти, не перейменовувати.*
