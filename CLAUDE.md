# AI Agent Instructions — DITIB-Ahlen-Portal

## Проект
Laravel 13 + Filament v5 портал для членів ісламської громади DITIB Ahlen.
Детальна документація: `PROJECT.md`

## Порти (фіксовано — не змінювати)

| Порт | Проект | Як запускається |
|------|--------|-----------------|
| **8080** | Лендінг (`main/`) | Docker (`docker-compose up`) |
| **8000** | Портал (`portal/`) | `php artisan serve --port=8000` |

Проекти працюють паралельно без конфліктів.

## Середовище

| | Local | Production |
|--|-------|------------|
| URL | http://localhost:8000 | https://mitglied.ditib-ahlen-projekte.de |
| Admin | http://localhost:8000/admin | https://mitglied.ditib-ahlen-projekte.de/admin |
| DB | SQLite (`database/database.sqlite`) | MySQL (Plesk) |
| PHP | 8.5 (Homebrew, не Docker) | 8.2+ |

## Запуск локально
```bash
cd ~/Project/DITIB-Ahlen/portal
php artisan serve --port=8000
```

## Ключові команди
```bash
php artisan migrate                    # застосувати міграції
php artisan migrate:rollback           # відкотити останню міграцію
php artisan make:migration <name>      # нова міграція (ТІЛЬКИ ТАК)
php artisan make:model <Name> -m       # модель + міграція
php artisan make:filament-resource <Name>  # Filament ресурс
php artisan config:clear && php artisan cache:clear  # скинути кеш
```

## Структура проекту
```
app/
├── Models/          ← Eloquent моделі
├── Providers/
│   └── Filament/
│       ├── AdminPanelProvider.php   ← /admin
│       └── MemberPanelProvider.php  ← /konto (ще не створений)
resources/views/     ← Blade шаблони (публічна форма)
database/migrations/ ← всі міграції
```

## Обов'язкові правила

1. **Ніколи не комітити `.env`** — тільки `.env.example`
2. **Міграції** — тільки через `php artisan make:migration`
3. **IBAN і BIC** — обов'язково `'encrypted'` cast у моделі
4. **Commit повідомлення** — англійська, коротко (`feat:`, `fix:`, `docs:`)
5. **Гілка** — завжди `main` для цього репо
6. **Не чіпати** `~/Project/DITIB-Ahlen/main/` — це інший проект (лендінг)

## Безпека (DSGVO)
- IBAN, BIC — зашифровані в БД (`encrypted` cast)
- Члени бачать тільки свої дані (Filament Panel ізоляція)
- `.env` з `APP_KEY` — ніколи в git

## Git
```bash
# Репозиторій
git@github.com:RomanPachkovskyi/DITIB-Ahlen-Portal.git

# Стандартний цикл
git add <files>
git commit -m "feat: short description"
git push origin main
```
