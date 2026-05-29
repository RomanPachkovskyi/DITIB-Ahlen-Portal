# PROJECT.md — Архітектура і стан
## DITIB-Ahlen-Portal

> Тут — архітектура, стек, функціональність, деплой, актуальний стан, плани і побажання щодо продукту.
> Правила для агентів → `AGENTS.md` | Історія змін → `CHANGELOG.md`

---

## Три документи проекту

| Файл | Призначення |
|------|-------------|
| **`AGENTS.md`** | Правила для агентів, команди, середовище — читати першим |
| **`PROJECT.md`** ← ти тут | Архітектура, стек, функціональність, деплой, актуальні плани й побажання |
| **`CHANGELOG.md`** | Хронологія всіх змін — що, коли, хто зробив |

**Правило з 2026-05-29:** усі плани, ідеї, побажання, відкриті рішення і roadmap-нотатки фіксуються тут, у `PROJECT.md`. Окремі тематичні документи можна створювати тільки для короткого робочого аналізу; після завершення задачі важливі рішення треба перенести сюди, щоб не розсинхронізувати документацію.

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
| Email | Laravel Mail → SMTP, Markdown Mailables + централізований branding layer | — |
| PDF | не встановлено; кандидат `barryvdh/laravel-dompdf` | заплановано |
| Image processing | intervention/image + GD | для private profile photos |
| Browser crop | Cropper.js + Alpine.js | public optional profile photo flow |
| Підпис | Alpine.js canvas | заплановано |
| Налаштування адміна | кандидат `spatie/laravel-settings` | заплановано для підпису/печатки |
| Черги (майбутні jobs/PDF) | Laravel Queue (database driver), без production worker зараз | заплановано тільки якщо з'явиться стабільний production worker |

> **Важливо для Filament v5:** `Section` знаходиться в `Filament\Schemas\Components\Section`, НЕ в `Filament\Forms\Components`.

---

## Функціональні Блоки І Розвиток

Цей розділ є єдиним місцем для функціонального стану порталу: що вже працює, що хочемо доростити, і які рішення важливо не порушити. Історія змін ведеться в `CHANGELOG.md`.

### Документація

**Працює зараз:**
- [x] `AGENTS.md` — єдиний загальний instruction-документ для всіх AI-агентів.
- [x] `PROJECT.md` — основний документ для архітектури, поточного стану, функціонального розвитку, deploy і планів.
- [x] `CHANGELOG.md` — обов'язкова хронологія кожної сесії змін.
- [x] `README.md` — короткий опис репозиторію, запуску і посилань на канонічні документи.
- [x] `CLAUDE.md` — compatibility pointer для Claude Code без дублювання правил.

**Заплановано / хочемо додати:**
- [x] Після підтвердження видалено старі тематичні документи `FOTO_UPLOAD_TZ.md` і `Правки і зміни на сайті.md`; актуальні рішення перенесені в `PROJECT.md`.

**Важливі рішення:**
- Усі майбутні плани, ідеї, побажання й відкриті рішення фіксуються в `PROJECT.md`, не в окремих roadmap-файлах.
- Тематичні документи можна створювати тільки як короткі робочі чернетки; після завершення задачі важливі рішення переносити сюди.

### Публічна Форма Реєстрації (`/`)

**Працює зараз:**
- [x] 4-крокова форма Mitgliedsantrag: `Persönliche Daten`, `Adresse & Kontakt`, `Beitrag & Zahlungsweise`, optional `Foto`.
- [x] Крок 1 збирає персональні дані: ПІБ, народження, громадянство, сім'я, Cenaze Fonu, Gemeinderegister, Beruf, Heimatstadt.
- [x] Крок 2 збирає адресу й контакт; телефон приймає міжнародний формат `+49...`, німецький формат із `0...`, німецький формат без першого `0` для номерів із Vorwahl, а також короткі місцеві Ahlen-номери, які нормалізуються з Vorwahl `02382`.
- [x] Instagram optional: приймає `username`, `@username` або Instagram URL; у БД зберігається нормалізований username без `@`.
- [x] PLZ autocomplete працює через локальну таблицю `postal_codes`; дані імпортуються з OpenPLZ командою `php artisan plz:import-openplz`; якщо PLZ не знайдено, користувач може ввести місто вручну і вибрати Bundesland.
- [x] Крок 3 збирає внесок і оплату: мін. EUR 10, пресети 10/15/20/25 EUR, ручний ввід кроком 1 EUR, стандартна Zahlungsweise `Dauerauftrag`, умовні SEPA-поля тільки для `Lastschrift`.
- [x] SEPA-згода і DSGVO-згода фіксуються з `zustimmung_at`; admin edit показує їх read-only.
- [x] `Weiter` виконує м'яку валідацію на переходах 1 -> 2 і 2 -> 3: помилки поточного кроку показуються, але користувач може перейти далі й дозаповнити форму.
- [x] Перехід 3 -> 4 (`Foto`) дозволений тільки після валідної анкети на кроках 1-3; якщо є помилки, форма повертає користувача до першого проблемного кроку.
- [x] Duplicate guard перед входом на `Foto` і перед final submit блокує дубль за `birth_date + normalized phone`; email і full_name не використовуються як blocking criteria.
- [x] При final submit форма перевіряє всі кроки й повертає користувача до першого проблемного кроку.
- [x] Step indicators не клікабельні; кроки з помилками позначаються білим кружком із червоною рамкою поверх progress-line.
- [x] Сіре summary-повідомлення над формою показується тільки тоді, коли помилки лишились у попередніх кроках.
- [x] PLZ dropdown не очищає validation errors для `postal_code`, `city`, `state`.
- [x] У Blade/Alpine `x-on:input` handlers використовують `$event.target`, не `this`.
- [x] Публічна форма має footer links `Impressum` і `Datenschutz` у нових вкладках.
- [x] Radio/checkbox controls використовують reusable class `ditib-choice-input` і brand CSS variable `--ditib-brand-primary`.
- [x] Після відправки показується сторінка підтвердження з `member_number`; технічний статус нового запису `pending`, в адмінці показується як `Neu`.
- [x] Під формою показується technical system label `vX.XXX - Update: DD.MM.YYYY - by Munas-Print`.

**Заплановано / хочемо додати:**
- [ ] Unterschrift canvas у формі; поточне поле `unterschrift` у БД є legacy/future placeholder.
- [ ] Двомовність DE + TR для public UI.
- [ ] Окремий дизайн-polish форми без зміни основної логіки кроків, duplicate guard і DSGVO/SEPA consent.
- [ ] Майбутній header/contact block: `DİTİB Türkisch-Islamische Gemeinde zu Ahlen e. V. / Ahlen Ulu Camii`; контакти `Rottmannstr. 62, 59229 Ahlen`, `02382 / 61599`, `info@ditib-ahlen-projekte.de`.

**Важливі рішення:**
- Email не є унікальним: один email дозволений для кількох членів родини/фірми.
- Duplicate guard не використовує ім'я через ризик різних турецьких/німецьких написань і помилок введення.
- Додаткові зміни duplicate guard мають зберігати правило: blocking criterion зараз `birth_date + normalized phone`.

### Фото Профілю

**Працює зараз:**
- [x] Optional Step 4 `Foto` у public form: camera/gallery input -> Cropper.js v2 -> client-side JPEG 800x800 -> Livewire `croppedPhoto`.
- [x] Submit без фото працює як раніше.
- [x] Якщо фото додано, потрібна окрема Foto-Einwilligung checkbox; без фото ця згода не потрібна.
- [x] Після створення `Member` фото зберігається через `ProfilePhotoService` у private storage на базі `member_number`.
- [x] Фото не публікується, не потрапляє в email, не експортується в Excel і потрібне тільки для внутрішньої Mitgliederverwaltung.
- [x] Фото зберігається на private disk `member_photos`; production root задається через `MEMBER_PHOTOS_ROOT` поза Laravel-проєктом.
- [x] Admin бачить фото у View/Edit, може завантажити/замінити або видалити фото; таблиця Mitglieder фото не показує.
- [x] `/konto` view показує фото доступних записів через protected route `members.profile-photo`.
- [x] Production deploy фото-функції виконано; admin upload/replace/delete і public crop/preview перевірені; `/konto` production QA відкладено до access-flow задачі.

**Заплановано / хочемо додати:**
- [ ] Audit log для фото: хто і коли завантажив, замінив або видалив фото.
- [ ] Процес відкликання photo consent із `/konto`, якщо пізніше буде self-service edit/change-request.
- [ ] Optional workflow `Foto geprüft`, якщо фото пізніше використовуватимуться для друку, PDF-пакетів або карток і потрібна ручна перевірка якості.
- [ ] Якщо знадобиться audit/metadata для фото, майбутня міграція може додати `profile_photo_mime` і `profile_photo_size`; зараз цих полів у `members` немає.

**Важливі рішення:**
- Public photo flow не повертати на старий план FilePond; фактична реалізація — Cropper.js + Alpine + Livewire.
- Базовий photo output — JPEG 800x800; WebP не використовувати як baseline v1 через ризики сумісності з PDF/браузерами/серверним оточенням.
- HEIC/HEIF не гарантується як baseline support; якщо браузер не може прочитати, повернути або стиснути фото, користувач має бачити зрозумілу помилку і мати можливість продовжити реєстрацію без фото.
- Foto UX labels: `Mit Kamera aufnehmen`, `Foto auswählen`, `Übernehmen`, `Anderes Foto`, `Foto entfernen`; helper text: `Bitte laden Sie ein aktuelles Porträtfoto hoch, keine Ausweise oder Dokumente.`
- Для admin upload не покладатися на Filament `imageCropAspectRatio('1:1')` як hard validation; фінальний квадрат, нормалізацію і replace/delete гарантує `ProfilePhotoService`.
- Manual QA для майбутніх фото-змін: iPhone Safari camera/gallery, Android Chrome camera/gallery, велике фото, rotated portrait/EXIF orientation, повільна мережа, submit без фото, submit після crop і submit після crop error з видаленням фото.

### Кабінет Члена (`/konto`)

**Працює зараз:**
- [x] Filament MemberPanel доступний на `/konto`.
- [x] `/konto` перенаправляє на список `Meine Mitgliedschaften`.
- [x] Access model v1: `User.email` є ключем доступу; якщо на одну пошту зареєстровано кілька членів, користувач бачить усі `Member` записи з цією поштою.
- [x] Detail URLs використовують `member_number`, наприклад `/konto/mitgliedschaften/DA-2026-0001`; внутрішній DB `id` у URL не використовується.
- [x] Resource query і route binding scoped за email поточного користувача; записи з іншими email не відкриваються через URL.
- [x] `/konto` використовує спільну з admin схему (`MemberForm::build(..., MemberFormContext::MemberView)`): клієнт бачить майже повну картку (`Persönliche Daten` / `Status & Verwaltung` / `Beitrag & Bankverbindung`), мінус admin-only поля. `status` показується read-only, `admin_notiz` прихований, `email` read-only.
- [x] Self-service edit (Phase 3 core): сторінка `EditMemberAccount`, кнопка `Bearbeiten` на `Vorschau`, redirect на view після save. Доступ — лише власні **не-inactive** записи (`canView`/`canEdit`). Save проходить через server-side allowlist `MemberFormContext::onlyMemberEditable()`; будь-яка реальна зміна переводить `pending`/`active` у `processing`; no-op save статус не змінює.
- [x] Inactive записи у списку `/konto` показуються dimmed (`opacity-50`) і не клікабельні; `canView`/`canEdit` для них `false` (не відкрити навіть прямим URL).
- [x] Під списком `/konto` показується technical system label (`vX.XXX - Update: DD.MM.YYYY - by Munas-Print`), так само як під таблицею Mitglieder в адмінці (render-hook `RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER` → `filament.system-info`).
- [x] Перехід `zahlungsart` → `lastschrift` без наявної SEPA-згоди у member-edit поки **блокується** (повний re-consent UX — окремий під-зріз 3c). Photo replace через `/konto` у v1 не ввімкнено.
- [x] Список `/konto` має ті самі дефолтні колонки, що admin: `Nr. / Name / Status / E-Mail / Ort / Beitrag/Mo. / Eingegangen am`.
- [x] Фото-consent поля (`Foto-Einwilligung` тумблер і `Foto-Einwilligung am`) показуються лише коли є збережене фото (`profile_photo_path !== null`) — для обох панелей.
- [x] Фото профілю показується у view кожного доступного запису через protected route `members.profile-photo`.
- [x] `/konto/login` використовує email-only magic-link flow: користувач вводить email, отримує одноразове посилання на пошту, link дійсний 60 хвилин і використовується один раз.
- [x] Magic-link токени зберігаються в `member_login_tokens` тільки як SHA-256 hash; `used_at` блокує повторне використання.
- [x] Якщо email не знайдений у `members`, UI показує нейтральне повідомлення без розкриття, чи є така адреса в базі.
- [x] Member magic-link не видається для admin email (`User::isAdminEmail()`): admin і member ділять один `User`/web guard, тому magic-link для admin email створив би admin-capable сесію. `createForEmail()` і `consume()` відмовляють такому email, UI лишається нейтральним, спроба логується як security warning. Admin входить тільки через `/admin`.
- [x] Spent (used/expired) magic-link токени видаляються автоматично при кожній видачі нового лінка через `MemberLoginToken::pruneSpent()`, тому `ip_address`/`user_agent` не накопичуються; cron не потрібен. Опційна команда `php artisan member:prune-login-tokens` (`--keep-hours=N`) лишається тільки для разового ops-purge.

**Заплановано / хочемо додати:**
- [ ] Production QA `/konto`, включно з magic-link email delivery, одноразовим входом і profile photo display, після deploy access flow.
- [x] Shared member schema для `/konto` — зроблено (Phase 2): спільний `MemberForm` із контекстами admin/member.
- [x] Self-service edit для `/konto` (Phase 3 core) — зроблено: edit власних не-inactive записів, allowlist save, статус→`processing`, dimmed/заблоковані inactive.
- [ ] Залишок навколо self-service edit: SEPA re-consent UX при переході на `lastschrift` (3c); audit log змін (Phase 5); email адміну після member-edit (Phase 6); inactive-login suppression + notice (Phase 4).
- [ ] Детальний робочий план цієї задачі ведеться в тимчасовому документі `docs/member-account-editing-audit-plan.md`; після реалізації фінальні рішення перенести назад у `PROJECT.md`.
- [ ] `Änderungsantrag` / approval workflow лишається можливим майбутнім напрямком, але поточний план self-service edit базується на прямому редагуванні з audit log і статусом `processing`.

**Важливі рішення:**
- Email визначає доступ до списку записів, але не є ідентифікатором заявки на зміну.
- `/konto` не використовує password login для членів; ручні паролі лишаються тільки для admin flow.
- Для magic-link не показувати явну помилку “email не знайдено”, щоб не розкривати membership presence.
- (Ще не реалізовано — Phase 4 плану) Якщо email існує тільки в `inactive` записах, magic-link не надсилати; натомість надіслати email із поясненням, що запис зараз не активний і треба звернутися до DITIB Ahlen. Поточний код (`MemberMagicLoginService::memberEmailExists()`) перевіряє лише наявність email у `members`, без фільтра за `status`, тому inactive-only email зараз усе ще отримує magic-link.
- Member magic-link не створює login token для admin email (реалізовано через `User::isAdminEmail()`); поточний shared `User`/web guard є свідомим тимчасовим компромісом, кращий майбутній варіант — окремий guard/model для членів.
- Кожна create/view/update дія для майбутнього `Änderungsantrag` повинна повторно перевіряти на backend, що обраний `member_id` належить до `Member` запису з email authenticated user.
- Від клієнта приховуємо тільки `admin_notiz`; `status` клієнт БАЧИТЬ read-only (і в списку, і в картці), але ніколи не редагує. IBAN/BIC зміни в audit/email фіксувати тільки як факт зміни без старого/нового значення. (Рішення Roman 2026-05-29, оновлює попередню чернетку, де `status` ховався.)
- Server-side allowlist дозволених member-полів уже реалізований як єдине джерело правди: `App\Filament\Resources\Members\Schemas\MemberFormContext` (`memberEditableFields()` / `onlyMemberEditable()`) з принципом deny-by-default — будь-яке нове `fillable`/системне поле автоматично заборонене для member-edit, доки явно не додане в allowlist. `email`, `member_number`, `status`, `admin_notiz` і consent/timestamp fields не редагуються клієнтом у v1. Майбутня сторінка self-service edit зобовʼязана пропускати дані через `MemberFormContext::onlyMemberEditable()`, а не покладатися на hidden/disabled поля Filament.

### Адмін-Панель (`/admin`)

**Працює зараз:**
- [x] Filament AdminPanel доступний на `/admin`.
- [x] Список членів має пошук, фільтр по статусу, badge-кольори і default pagination 25 записів.
- [x] Member View/Edit URLs використовують `member_number`, наприклад `/admin/members/DA-2026-0001`; внутрішній DB `id` лишається тільки PK.
- [x] Перегляд і редагування запису розділені; клік по рядку відкриває View, редагування доступне через `Bearbeiten`.
- [x] Системні відкриті стани: `pending` (`Neu`) і `processing` (`Verarbeitung`); адмін переводить запис у `active` або `inactive`.
- [x] У таблиці швидкі дії: `Bearbeiten` і зміна статусу тільки на `Aktiv` або `Inaktiv`; `Anzeigen` і `Löschen` не показуються в row actions.
- [x] Масові дії використовуються тільки для зміни статусу на `Aktiv` або `Inaktiv`; mass delete не показується.
- [x] На edit page кнопки `Änderungen speichern` і `Abbrechen` доступні зверху і знизу форми; save-кнопки мають brand primary style.
- [x] Global search у header вимкнено; замість нього є `Neue Registrierung`, яка відкриває production-form у новій вкладці.
- [x] Header-кнопка `Erstellen` на сторінці Mitglieder не показується; нові члени мають реєструватись через публічну форму.
- [x] Footer адмінки має `Impressum` і `Datenschutz`.
- [x] При зміні статусу на `active` член отримує email про прийняття.
- [x] При видаленні запису адміністратор і член отримують email-фіксацію видалення.
- [x] Dashboard адмінки має статистичні widgets.
- [x] Навігація й адмінка мають DITIB branding, logo і primary color `#009689`.
- [x] `/admin` і `/konto` використовують спільний Filament DITIB style layer `resources/views/filament/panel-style.blade.php` для brand variables, primary buttons, compact spacing і mobile login відступів; admin-only tweaks лишаються в `resources/views/filament/admin-style.blade.php`.
- [x] `/admin/login` лишається password-based login із heading `Admin-Anmeldung`; `/konto/login` має окремий member magic-link текст і не показує password field.
- [x] Під таблицею Mitglieder справа показується technical system label `vX.XXX - Update: DD.MM.YYYY - by Munas-Print`.
- [x] Login-форми `/admin` і `/konto` показують technical system label під формою справа, щоб адмін і користувач бачили актуальну версію до входу.

**Заплановано / хочемо додати:**
- [ ] Фінальний language polish адмін-панелі німецькою.
- [ ] Сторінка налаштувань для підпису відповідальної особи DITIB і печатки організації; кандидат — `spatie/laravel-settings`, пакет ще не встановлено.
- [ ] UI/workflow для обробки `Änderungsantrag`; базова таблиця `change_requests` уже існує.

**Важливі рішення:**
- Звичайний процес завершення членства: перевести запис у `inactive`, не видаляти.
- Soft delete лишається технічно в системі, але не є адмінською UI-дією; `member_number` не звільняється навіть для inactive або soft-deleted записів.
- Admin consent fields — SEPA/DSGVO consent і `zustimmung_at` — лишаються read-only.
- Filament-specific brand/UI правила тримати у спільному `filament.panel-style` для всіх panels; panel-specific override файли використовувати тільки для поведінки конкретної панелі.

### Email

**Працює зараз:**
- [x] Registration emails відправляються синхронно через SMTP при submit форми.
- [x] Email клієнту після відправки форми.
- [x] Email адміну про нову заявку.
- [x] Email клієнту при підтвердженні реєстрації (`active`).
- [x] Email адміну і клієнту при видаленні запису члена.
- [x] Email із одноразовим `/konto` Zugangslink для magic-link входу.
- [x] Логіка відправки відділена від Livewire через event/listener layer.
- [x] Централізований branding layer для Laravel Markdown emails: `config/mail.php` -> `mail.brand.*`, `resources/views/vendor/mail/`, `resources/views/emails/`.
- [x] Artifact build виправлений, щоб mail override templates не випадали через exclude `vendor`.

**Заплановано / хочемо додати:**
- [ ] Email адміну, коли клієнт змінив власні дані в `/konto`; лист містить ім'я, member number, список змінених полів і пряме посилання на admin record. IBAN/BIC не розкривати в листі, тільки позначати як змінені.
- [ ] Якщо Gmail/Outlook/Apple Mail покажуть недостатній контроль верстки, перейти на власний HTML email template (`view:`) окремим етапом.

**Важливі рішення:**
- На поточному Plesk artifact-deploy немає стабільного queue worker; `ShouldQueue` не використовувати для production email flow.
- При SMTP-помилці збереження анкети не має ламатися; помилка має логуватись.
- Окремі email views не повинні містити логотип, footer або глобальні стилі — тільки зміст конкретного повідомлення.
- Magic-link email використовує той самий Markdown mail branding layer; окремий view містить тільки зміст конкретного листа.

### PDF І Документи

**Працює зараз:**
- [x] Approval email працює синхронно без PDF.

**Заплановано / хочемо додати:**
- [ ] PDF підтвердження членства; кандидат — `barryvdh/laravel-dompdf`, пакет ще не встановлено.
- [ ] PDF має містити заповнені дані анкети, а пізніше — підпис клієнта, підпис відповідальної особи DITIB і печатку організації.
- [ ] Сторінка налаштувань для підпису/печатки пов'язана з PDF-функцією.

**Важливі рішення:**
- Для PDF усі зображення (підпис клієнта, підпис відповідальної особи, печатка, optional photo) передавати як Base64 або іншим стабільним способом, сумісним із Plesk.
- Якщо фото відсутнє, PDF має пропускати photo block без placeholder.
- Майбутній PDF не має вводити queue worker як production requirement, доки Plesk worker не стане стабільно доступним.

### Локалізація

**Працює зараз:**
- [x] Поточна UI-мова — німецька.

**Заплановано / хочемо додати:**
- [ ] Турецька локалізація: middleware/cookie або інший locale flow.
- [ ] `lang/de.json` і `lang/tr.json`.
- [ ] Перемикач мови.
- [ ] Автовизначення мови браузера і запам'ятовування вибору в cookie.

**Важливі рішення:**
- Німецька лишається default language, якщо вибір користувача або browser locale не визначені.

### Експорт І Audit Log

**Працює зараз:**
- [x] Немає production Excel export і глобального audit log; це свідомо ще не реалізовано.
- [x] Існує legacy/future таблиця `change_requests`, але вона не є audit log і зараз не використовується як єдина система логування.
- [x] Cleanup для `member_login_tokens` реалізовано: spent токени видаляються автоматично при видачі нового лінка (`MemberLoginToken::pruneSpent()`) + опційна команда `member:prune-login-tokens`. Це закриває retention тільки для login-токенів; глобальний audit log і його retention ще не реалізовані.

**Заплановано / хочемо додати:**
- [ ] Експорт таблиці членів громади в `.xlsx` із адмінки.
- [ ] Єдиний audit log для фактичних змін `Member`: admin edit, member edit, status actions, bulk status actions, photo upload/delete; детальний план у `docs/member-account-editing-audit-plan.md`.
- [ ] Мінімальний audit scope: нова реєстрація, зміна статусу, редагування полів, soft delete/delete, admin login, email-помилки, admin replace/delete profile photo.
- [ ] Окрема адмін-сторінка audit log із фільтрами по користувачу, типу дії, сутності та даті.
- [ ] Retention/anonymization для майбутнього audit log: він міститиме персональні дані (ім'я, адреса, телефон) навіть для несенситивних полів, тому при soft-delete/erasure члена потрібен service/command для очищення або анонімізації записів по `member_id`. (Cleanup для `member_login_tokens` уже реалізовано окремо, див. вище.)

**Важливі рішення:**
- Фото в Excel export не додавати.
- Для audit log не показувати IBAN/BIC відкрито; або маскувати, або не логувати значення цих полів.

### Deploy І Production Operations

**Працює зараз:**
- [x] Artifact deploy через Plesk File Manager/FTP.
- [x] SQL changes через phpMyAdmin.
- [x] `scripts/build-artifact.sh` збирає production artifact у staging-папці в `/tmp`.
- [x] `scripts/build-artifact.sh` автоматично піднімає technical version у `config/system-version.json`.
- [x] Production photo data живе поза Laravel-проєктом у `Home directory/ditib-portal-data/member-photos`.
- [x] Для magic-link access migration підготовлено phpMyAdmin SQL `deploy-artifacts/production-member-login-tokens-release-20260529.sql`.

**Заплановано / хочемо додати:**
- [ ] При кожній новій migration готувати SQL-файл у `deploy-artifacts/` для phpMyAdmin.

**Важливі рішення:**
- Серверні shell-команди на хостингу недоступні за умовами хостера; не планувати production deploy навколо серверних `composer`, `npm`, `php artisan migrate`, `config:cache`, `route:cache`, `view:cache`.
- На сервер деплоїться весь Laravel-застосунок, не тільки `public/build`.
- `.env` створюється вручну на сервері й ніколи не комітиться.
- Production `APP_KEY` генерується один раз і не змінюється, бо потрібен для encrypted IBAN/BIC.

---

## База даних

### Таблиця `members`

| Поле | Тип | Примітка |
|------|-----|---------|
| member_number | string(20) | unique, auto DA-YYYY-NNNN, не перевикористовується; єдиний public route key для member URLs |
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
| email | string | **не unique** — один email дозволений для кількох членів (сім'я) |
| phone | string | |
| instagram | string | nullable, зберігається нормалізований username без `@`; форма приймає username, `@username` або Instagram URL |
| profile_photo_path | string | nullable, relative path у private disk `member_photos`, без public URL |
| profile_photo_uploaded_at | timestamp | nullable |
| profile_photo_zustimmung | boolean | default false, окрема згода на optional profile photo |
| profile_photo_zustimmung_at | timestamp | nullable |
| zahlungsart | enum | barzahlung / lastschrift / dauerauftrag |
| monatsbeitrag | decimal | мін. €10 |
| kontoinhaber | string | nullable (тільки при SEPA) |
| iban | text | **encrypted**, nullable |
| bic | text | **encrypted**, nullable |
| kreditinstitut | string | nullable |
| unterschrift | text | legacy/future placeholder для base64 PNG підпису, hidden; canvas-підпис ще не реалізований |
| sepa_zustimmung | boolean | |
| dsgvo_zustimmung | boolean | |
| zustimmung_at | timestamp | |
| status | enum | pending / processing / active / inactive; `pending` показується як `Neu`, `processing` як `Verarbeitung` |
| admin_notiz | text | nullable |
| deleted_at | timestamp | nullable, soft delete для історії номерів |

### Таблиця `member_number_sequences`

| Поле | Тип | Примітка |
|------|-----|---------|
| name | string(50) | unique, зараз `members` |
| next_number | unsigned big integer | наступний кандидат для `member_number` |

Номери членів видаються тільки через `MemberNumberSequence` у DB transaction з `lockForUpdate()`. Allocator додатково перевіряє `members` разом із soft-deleted записами й переступає вже зайняті номери, якщо sequence колись відстане після ручної правки.

### Таблиця `postal_codes`

| Поле | Тип | Примітка |
|------|-----|---------|
| plz | string | індекс, indexed |
| ort | string | місто/населений пункт |
| bundesland | string | федеральна земля |

Дані для PLZ autocomplete імпортуються з OpenPLZ через artisan-команду `php artisan plz:import-openplz`. Команда очищає `postal_codes` і заново завантажує реальні німецькі locality records з `openplzapi.org`; за попереднім імпортом очікуваний порядок величини — близько 50 тис. записів.

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
- `member_number` — постійний історичний ідентифікатор; soft-deleted записи зберігають номер і не дають системі видати його повторно
- Public URLs для `Member` використовують `member_number` через Laravel route model binding (`Member::getRouteKeyName()`), не auto-increment `id`
- `inactive` — нормальний адміністративний статус для колишніх/неактивних членів; не видаляти записи через UI
- SEPA/DSGVO consent fields і `zustimmung_at` у admin edit є read-only фактами; адмін не редагує згоду клієнта
- `$hidden` у моделі НЕ використовувати для IBAN/BIC (блокує Filament форму)
- Члени в `/konto` бачать тільки записи з `members.email`, що збігається з email authenticated user; якщо один email використано для кількох членів родини/фірми, усі ці записи вважаються доступними цьому користувачу
- Admin і member ділять одну таблицю `users` і один web guard; щоб magic-link не створив admin-capable сесію, member magic-link не видається для admin email (`User::isAdminEmail()` перевіряється і в `createForEmail()`, і в `consume()`). Розділення на окремий member guard — майбутнє покращення
- `member_login_tokens` зберігає тільки SHA-256 hash токена + `ip_address`/`user_agent` (PII); spent (used/expired) токени автоматично видаляються при видачі нового лінка (`MemberLoginToken::pruneSpent()`), щоб ця таблиця не ставала відкритим архівом PII
- Майбутній member self-service edit зобовʼязаний використовувати server-side allowlist `MemberFormContext::onlyMemberEditable()` (deny-by-default); hidden/disabled поля у Filament НЕ вважаються захистом, бо Livewire-запит можна підробити. Заборонені для member-edit: `email`, `member_number`, `status`, `admin_notiz`, усі consent/timestamp і photo-path поля
- Згода SEPA і DSGVO фіксується з timestamp при відправці форми; у публічній формі тексти згод мають посилання на `https://ditib-ahlen-projekte.de/datenschutz`
- Авторитетний legal text для порталу і лендінгу ведеться в `../main/docs/legal-texts.md`; сторінка Datenschutz на лендінгу має залишатися синхронізованою з цим текстом, особливо для Mitgliedsantrag, SEPA/IBAN, членських даних і optional profile photos
- Optional profile photos зберігаються тільки на private disk `member_photos`; локально root за замовчуванням `storage/app/private`, на production root задається через `MEMBER_PHOTOS_ROOT` поза папкою Laravel-проєкту (`Home directory/ditib-portal-data`); у БД лежить тільки relative path `member-photos/...`; direct public URL, `public/storage` і email-usage не використовуються
- Optional profile photos мають окрему згоду: якщо користувач додає фото на Step 4, `profile_photo_zustimmung` є обов'язковим і фіксується з `profile_photo_zustimmung_at`; без фото ця згода не потрібна
- Admin preview для profile photos використовує protected route `members.profile-photo` із cache busting `?v=profile_photo_uploaded_at`; Filament temporary/private URLs не використовуються для постійного перегляду фото
- Admin replace/delete фото не змінює `zustimmung_at`, SEPA або DSGVO facts; видалення фото очищає photo consent fields, soft delete member не видаляє runtime photo file автоматично
- Protected photo route дозволяє admin доступ до будь-якого фото, а member-user доступ до всіх фото записів із тим самим email; це свідомий family/company access model для v1
- `.env` з `APP_KEY` — ніколи не комітити в git

---

## Архітектурні рішення

### UI branding і стилі

Основний brand color проекту: `#009689`. Він відповідає бірюзовому стилю публічної форми (`teal-600`) і має бути єдиною основою для primary controls у порталі.

Джерело правди для brand tokens у PHP:

- `app/Support/BrandColors.php`
- `BrandColors::PRIMARY_HEX` → `#009689`
- `BrandColors::ON_PRIMARY_HEX` → `#ffffff`
- `BrandColors::PRIMARY_HOVER_CSS_VAR` → `var(--color-teal-700, var(--primary-700))`
- `BrandColors::primary()` → Filament teal palette для `->colors(['primary' => ...])`

Filament panels:

- `AdminPanelProvider` і `MemberPanelProvider` використовують `BrandColors::primary()` для `primary`
- Не повертати `Color::Amber` в admin panel; жовтий `#ffb900` більше не є primary стилем
- `resources/views/filament/admin-style.blade.php` прокидає CSS variables `--ditib-brand-*`
- Усі Filament-кнопки `.fi-btn.fi-color-primary` глобально отримують brand style: фон `#009689`, hover `teal-700`, білий текст та білі іконки
- Майбутні Filament actions/buttons, які мають бути основними CTA, треба робити через `->color('primary')` або `color="primary"`; окремий custom class для базового brand button не потрібен
- Semantic status colors не змішувати з brand: `active` лишається `success`, `pending`/`processing` лишаються `warning`, `inactive` лишається `danger`, бо це статуси, а не CTA

Публічна форма:

- `resources/views/layouts/public.blade.php` прокидає ті самі CSS variables `--ditib-brand-*` з `BrandColors`
- `resources/css/app.css` містить reusable class `ditib-choice-input` для native radio/checkbox controls
- Radio/checkbox у формі мають використовувати `ditib-choice-input`, а не випадкові `text-blue-*` або ручні inline colors
- Існуючі Tailwind-класи `teal-*` у формі історично відповідають brand color; при подальшому впорядкуванні їх можна поступово переводити на reusable brand classes/CSS variables

Важливо: публічна форма і адмінка не є однією UI-системою. Форма — Blade/Tailwind/Vite, адмінка — Filament. Спільність стилю забезпечується через `BrandColors` і CSS variables, а не через спільні компоненти.

### Email — не унікальний (v1.0)
Один email може використовуватись для кількох членів. Причина: літні члени громади не мають власної пошти — діти або родичі реєструють їх на свій email.

У `/konto` це є прийнятою бізнес-логікою: користувач, який увійшов під певним email, бачить усі записи членів із цим email. Це покриває родину або фірму, де одна людина керує кількома записами.

Duplicate protection для публічної реєстрації не використовує email і не покладається на ім'я, бо імена можуть мати різні турецькі/німецькі написання або помилки введення. Дубль блокується за практичним критерієм `birth_date + normalized phone`. Перевірка виконується перед optional кроком `Foto` і повторно перед створенням `Member`; пошук включає soft-deleted записи через `Member::withTrashed()`.

Важливо для майбутнього `Änderungsantrag`: хоча доступ до списку записів визначається email, самі запити на зміну мають створюватися і вестися для конкретної зареєстрованої особи через `member_id` / `member_number`. Email не є ідентифікатором запису зміни.

Backend security rule для майбутнього `Änderungsantrag`: UI вже показує користувачу тільки доступні записи з його email, але backend не має довіряти тільки UI. Кожна create/view/update дія для заявки на зміну повинна повторно перевіряти, що обраний `member_id` належить до `Member` запису з email authenticated user.

Майбутнє уточнення: якщо потрібне індивідуальне self-service редагування або юридично точніша авторизація для окремого члена, можна додати додатковий binding через `member_number`, invitation token або окрему таблицю зв'язків `user_member`.

### Public route key для Member

`Member` використовує `member_number` як route model binding key:

```php
public function getRouteKeyName(): string
{
    return 'member_number';
}
```

Результат:

- `/admin/members/DA-2026-0001` замість `/admin/members/1`
- `/admin/members/DA-2026-0001/edit` замість `/admin/members/1/edit`
- `/konto/mitgliedschaften/DA-2026-0001` замість `/konto/mitgliedschaften/1`
- `/members/DA-2026-0001/profile-photo` замість `/members/1/profile-photo`

Внутрішній `id` лишається primary key і не має штучно збігатися з `member_number`. Це важливо для FK-зв'язків, performance і нормальної роботи Eloquent. Публічний ідентифікатор для адміна/члена — тільки `member_number`.

Release-check для цієї зміни:

```sql
SELECT COUNT(*) AS members_without_member_number
FROM members
WHERE member_number IS NULL OR member_number = '';
```

Очікувано: `0`. Якщо результат не 0, спочатку потрібен backfill для старих записів, інакше вони не відкриються через новий URL. Для phpMyAdmin був підготовлений перевірочний файл `deploy-artifacts/production-check-member-number-route-key-readiness.sql`; production readiness уже підтверджено 2026-05-20 без записів із порожнім `member_number`.

### Email — архітектура і брендування

Поточна production-схема: Laravel Mail → SMTP, синхронна відправка без queue worker. Це свідоме рішення для Plesk artifact-deploy, де немає стабільного production worker-а для черг.

Поточний шаблонний шар:

- окремі листи реалізовані як `Mailable` + `markdown:` views у `resources/views/emails/`
- спільний HTML/text layout Laravel Markdown Mail перевизначений у `resources/views/vendor/mail/`
- бренд налаштовується централізовано через `config/mail.php` → `mail.brand.*`
- `.env` ключі бренду: `MAIL_BRAND_NAME`, `MAIL_BRAND_URL`, `MAIL_LOGO_URL`, `MAIL_BRAND_FOOTER`
- окремі email views не повинні містити логотип, footer або глобальні стилі — тільки зміст конкретного повідомлення

Логотип у листах зараз використовується як стабільний HTTPS URL з `MAIL_LOGO_URL`. Inline/CID images не використовуються як основний механізм, щоб branding layer залишався простим, стандартним і передбачуваним для Laravel Markdown Mail.

**Діагноз 2026-05-06:** локальні листи рендеряться з логотипом, але production artifact не містив `resources/views/vendor/mail/`. Причина: `scripts/build-artifact.sh` копіював проект через `tar --exclude='./vendor'`, а на локальному tar цей exclude також прибирав вкладену папку `resources/views/vendor/mail/`. Через це production використовував стандартні Laravel Markdown mail templates без нашого header/logo. Скрипт виправлено: після staging-copy mail override templates явно повертаються в `resources/views/vendor/mail/`. Після наступного artifact deploy потрібно перевірити, що ця папка є на сервері.

Майбутнє оновлення: якщо Gmail/Outlook/Apple Mail покажуть, що Markdown theme недостатньо контролює вигляд, можна перейти з `markdown:` на власний `view:` HTML email template. Це буде окремий етап, не змішаний із поточним SMTP/Mailable layer.

---

## Локальна розробка

| Параметр | Значення |
|----------|----------|
| Папка | `~/Project/DITIB-Ahlen/portal/` |
| PHP | 8.5 (Homebrew, не Docker) |
| DB | SQLite → `database/database.sqlite` |
| Порт | **8000** (портал запускається через Homebrew PHP, не через Docker) |
| Форма | http://localhost:8000 |
| Admin | http://localhost:8000/admin |
| Konto | http://localhost:8000/konto |
| Admin login | rpachkovskyi@gmail.com / Admin1234! |
| Admin login | info@ditib-ahlen-projekte.de / AhlenDitib2026! |

**Запуск:**
```bash
cd ~/Project/DITIB-Ahlen/portal && php artisan serve --port=8000
```

**Фіксовані локальні порти DITIB Ahlen:** лендінг `../main` працює через Docker на `http://localhost:8082`, портал `../portal` працює через Homebrew PHP на `http://localhost:8000`. Портал не переносити в Docker Desktop і не запускати на `8083`, `5173` або `8383`.

**Frontend assets build:**

`npm run build` створює тільки Vite assets у `public/build/` (CSS/JS). Це не окремий статичний сайт, не повний build Laravel-застосунку і не production deploy package.

Окремо запускати `npm run build` потрібно тільки для локальної перевірки frontend assets після змін у CSS/JS/Vite. Для production artifact це не є окремим ручним кроком: `scripts/build-artifact.sh` сам виконує `npm ci` і `npm run build` у staging-папці.

---

## Хостинг і деплой

| Параметр | Значення |
|----------|----------|
| Хостинг | Plesk (virtual hosting) |
| Домен | `mitglied.ditib-ahlen-projekte.de` |
| Server folder | `mitglied.ditib-ahlen-projekte.de/` (окремо від `httpdocs`) |
| Document Root | `mitglied.ditib-ahlen-projekte.de/public` |
| PHP | 8.4+ (`composer.json` вимагає `^8.4`) |
| DB | MySQL (Plesk) |
| Deploy | Artifact deploy через File Manager/FTP |
| DB changes | SQL-файли через phpMyAdmin |

**Обраний робочий процес з 2026-05-06:** artifact deploy + SQL/phpMyAdmin. За умовами хостингу серверні shell-команди для цього проекту недоступні, тому production-артефакт збирається локально і завантажується на сервер уже з `vendor/` та `public/build/`; зміни БД виконуються окремими SQL-файлами через phpMyAdmin.

Для агентів: стандартний production stack цього проекту — **Plesk File Manager/FTP для файлів і phpMyAdmin для БД**. Не планувати deploy навколо серверних shell-команд, якщо користувач явно не змінить це рішення.

### Artifact deploy — поточний процес

Локально зібрати архів:

```bash
cd ~/Project/DITIB-Ahlen/portal
scripts/build-artifact.sh
```

Скрипт працює через тимчасову staging-папку в `/tmp`, тому не змінює локальні `vendor/`, `node_modules/` і `public/build/` у робочому проекті. Всередині staging він виконує:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Перед пакуванням staging нормалізує права доступу: директорії `755`, файли `644`, `artisan` і shell-скрипти `755`. Це важливо для Plesk/Apache: root `./` всередині tar не може мати права `700`, інакше після розпакування сайт дає `403 Forbidden`.

Перед staging-copy скрипт автоматично піднімає технічну версію в `config/system-version.json` через `scripts/update-system-version.php`. Після цього створюється архів у форматі `deploy-artifacts/ditib-ahlen-portal-vX.XXX-YYYYMMDD-HHMMSS.tar.gz`. Звичайні code/doc changes без запуску `scripts/build-artifact.sh` або `scripts/export-production-sql.php` не піднімають версію вручну.

Архів містить:

- весь Laravel-код (`app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `public/`, `storage/` тощо);
- готовий `vendor/`;
- готовий `public/build/`;
- `.env.example` для довідки.

Архів НЕ містить:

- `.env` та локальні `.env.*`;
- усі `*.md` файли документації;
- `node_modules/`;
- `database/database.sqlite`;
- локальну папку `==logs/`;
- runtime profile photos з production data folder `Home directory/ditib-portal-data/member-photos/` не потрапляють в artifact, бо ця папка знаходиться поза Laravel-проєктом;
- локальні `storage/logs/*.log`, sessions, compiled views і cache data;
- `.git/`.

Завантаження на Plesk:

1. Відкрити Plesk → `mitglied.ditib-ahlen-projekte.de` → File Manager або FTP.
2. Перейти в корінь Laravel-проєкту: `mitglied.ditib-ahlen-projekte.de/`.
3. Завантажити архів.
4. Розпакувати архів у цю папку з перезаписом файлів.
5. Перевірити, що серверний `.env` залишився на місці і не був замінений.
6. Перевірити, що Document Root досі `mitglied.ditib-ahlen-projekte.de/public`.

Короткий production redeploy, коли БД уже містить реєстрації і міграцій немає:

1. Зробити Plesk backup із `User files` + `Databases`; він має включати `Home directory/ditib-portal-data/member-photos`, якщо на production вже є фото.
2. У Plesk File Manager зайти в `mitglied.ditib-ahlen-projekte.de/`.
3. Видалити старі файли Laravel-порталу, але **не видаляти `.env`**.
4. Якщо на production є користувацькі uploads у `storage/app/public` або profile photos у `Home directory/ditib-portal-data/member-photos`, їх також не видаляти.
5. Завантажити новий `deploy-artifacts/ditib-ahlen-portal-*.tar.gz`.
6. Розпакувати архів у `mitglied.ditib-ahlen-projekte.de/`.
7. Перевірити, що `.env` залишився серверним, `APP_KEY` не змінився, Document Root досі `.../public`.
8. Якщо нових файлів у `database/migrations/` немає, **БД не чіпати**: не імпортувати SQL і не запускати migrate.
9. Перевірити форму, `/admin`, старі реєстрації в адмінці і одну тестову валідаційну помилку у формі.

Якщо після перенесення/зміни Plesk-середовища потрібно повторно перевірити PHP extensions для фото без серверних shell-команд:

1. Завантажити `deploy-artifacts/check-photo-extensions.php` у `mitglied.ditib-ahlen-projekte.de/public/check-photo-extensions.php`.
2. Відкрити `https://mitglied.ditib-ahlen-projekte.de/check-photo-extensions.php`.
3. Має бути `OK` для `gd_extension`, `fileinfo_extension`, `gd_imagecreatetruecolor`, `gd_imagejpeg`; `exif_extension` бажаний для коректнішої orientation-обробки.
4. Файл також покаже `member_photos_root_candidate`; його можна використати як `MEMBER_PHOTOS_ROOT`, якщо `member_photos_root_exists` і `member_photos_root_writable` показують `OK`.
5. Після перевірки одразу видалити цей файл із production.

Цей check уже використовувався перед фото-release 2026-05-20; повторювати його потрібно тільки після зміни hosting/PHP configuration або при проблемах із обробкою фото.

Для Plesk backup: внутрішній backup має включати і файли, і базу даних. Критична папка для фото: `Home directory/ditib-portal-data/member-photos`. Перевірка 2026-05-20 підтвердила, що Plesk backup subscription включає створену `ditib-portal-data/member-photos` поруч із `httpdocs` і `mitglied.ditib-ahlen-projekte.de`. Цю папку не можна очищати при redeploy і треба відновлювати разом із MySQL, бо в БД зберігаються тільки relative paths.

Production `.env` для фото має вказувати абсолютний шлях до data folder поза Laravel-проєктом:

```env
MEMBER_PHOTOS_ROOT=/var/www/vhosts/ditib-ahlen-projekte.de/ditib-portal-data
```

Якщо фактичний absolute path у Plesk інший, використати саме його. Локально `MEMBER_PHOTOS_ROOT` можна лишити порожнім: тоді disk `member_photos` пише у `storage/app/private/member-photos`.

Після artifact deploy без shell-команд Laravel працює без `config:cache`, `route:cache` і `view:cache`. Це повільніше за optimized deploy, але прийнятно для поточного масштабу порталу.

### Міграції БД при artifact deploy

Оскільки `php artisan migrate --force` на сервері недоступний і не є частиною поточного production stack, зміни БД потрібно виконувати окремо через phpMyAdmin.

Для першого production-деплою імпортувати SQL-схему з міграцій у MySQL. Для наступних деплоїв:

1. Перед деплоєм перевірити, чи додалися нові файли в `database/migrations/`.
2. Якщо міграцій немає, SQL-дії не потрібні.
3. Якщо міграції є, підготувати відповідний SQL-файл у `deploy-artifacts/`.
4. Імпортувати SQL через phpMyAdmin.
5. Перевірити таблицю `migrations`, щоб production не вважав міграцію невиконаною, якщо Artisan колись стане доступним.

Повний SQL export через `scripts/export-production-sql.php` автоматично піднімає технічну версію в `config/system-version.json`.

Важливо: локальний SQLite (`database/database.sqlite`) ніколи не переносити на production.

### Як влаштований сервер

Plesk для subdomain створює окрему папку поруч із `httpdocs`:

```text
home directory/
├── httpdocs/                              # основний домен / landing
├── mitglied.ditib-ahlen-projekte.de/      # Laravel portal repo
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/                            # Document Root дивиться сюди
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                               # тільки на сервері, не в git
│   ├── artisan
│   ├── composer.json
│   └── package.json
└── logs/
```

Це не React/Vite-only сайт. На сервер деплоїться **весь Laravel-репозиторій**, а не тільки `public/build`. Відвідувачі сайту при цьому мають бачити тільки `public/`, тому Document Root обов'язково:

```text
mitglied.ditib-ahlen-projekte.de/public
```

Якщо Document Root буде `mitglied.ditib-ahlen-projekte.de` без `/public`, це небезпечно: браузеру можуть стати видимими Laravel-файли, `.env`, `storage`, `vendor` тощо.

### База даних на production

Production потребує MySQL у Plesk:

1. Створити MySQL database і database user у Plesk.
2. Прописати доступи в серверному `.env`.
3. Імпортувати schema/data SQL через phpMyAdmin.
4. Для наступних змін БД імпортувати окремі migration SQL-файли з `deploy-artifacts/`.

SQLite (`database/database.sqlite`) використовується тільки локально. Для production не переносити локальний SQLite-файл на сервер.

**Стан:** MySQL database/user створені в Plesk, серверний `.env` створений. Поточний deploy-стек зафіксований як artifact upload через File Manager/FTP + SQL import через phpMyAdmin.

Серверні shell-команди на хостингу недоступні за умовами хостера. Тому production-процес не використовує серверні `composer`, `npm`, `php artisan migrate --force`, `config:cache`, `route:cache`, `view:cache` або інші shell-команди.

Для production:

- файли: `scripts/build-artifact.sh` → завантажити архів через Plesk File Manager/FTP;
- база: SQL-файли з `deploy-artifacts/` → імпорт через phpMyAdmin;
- `.env`: створюється і редагується вручну в Plesk File Manager;
- `APP_KEY`: генерується локально один раз і вставляється вручну в production `.env`.

`APP_KEY` можна згенерувати локально:

```bash
php artisan key:generate --show
```

Після першого production-деплою `APP_KEY` не міняти, бо IBAN/BIC шифруються Laravel encrypted cast через цей ключ.

Admin-користувачі на production створюються через підготовлений SQL для phpMyAdmin або через Filament/адмін-інтерфейс, якщо він уже доступний. `php artisan make:filament-user` не є стандартним production-кроком для цього хостингу.

### `.env` на сервері

`.env` створюється вручну в `mitglied.ditib-ahlen-projekte.de/.env` і не комітиться в git:

**Як створити через Plesk File Manager:**

1. Відкрити Plesk → Domains → `mitglied.ditib-ahlen-projekte.de` → File Manager.
2. Зайти в папку `mitglied.ditib-ahlen-projekte.de` (корінь Laravel-проєкту).
3. Натиснути `+` / `New File`.
4. Назва файлу: `.env` (саме з крапкою на початку, без `.txt`).
5. Вставити вміст нижче і зберегти.

**Приклад для першого production-тесту:**

```env
APP_NAME="DITIB Ahlen"
APP_ENV=production
APP_KEY=base64:PASTE_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://mitglied.ditib-ahlen-projekte.de

APP_LOCALE=de
APP_FALLBACK_LOCALE=de
APP_FAKER_LOCALE=de_DE

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ditib_ahlen_portal
DB_USERNAME=ditib_portal_user
DB_PASSWORD=DITIB-Ahlen-Portal_2026!m5

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
MEMBER_PHOTOS_ROOT=/var/www/vhosts/ditib-ahlen-projekte.de/ditib-portal-data

MAIL_MAILER=smtp
MAIL_HOST=mail.ditib-ahlen-projekte.de
MAIL_PORT=587
MAIL_USERNAME=info@ditib-ahlen-projekte.de
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@ditib-ahlen-projekte.de"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_BRAND_NAME="${APP_NAME}"
MAIL_BRAND_URL="${APP_URL}"
MAIL_LOGO_URL="https://mitglied.ditib-ahlen-projekte.de/images/ditib_ahlen_logo.png"
MAIL_BRAND_FOOTER="DITIB Türkisch-Islamische Gemeinde zu Ahlen e.V."

VITE_APP_NAME="${APP_NAME}"
```

`APP_KEY` згенерувати локально і вставити замість `base64:PASTE_GENERATED_KEY_HERE`:

```bash
php artisan key:generate --show
```

Це замінює серверний `php artisan key:generate` у поточному hosting-процесі без shell-команд. Після цього `APP_KEY` не міняти.

Якщо SMTP ще не готовий, `MAIL_PASSWORD` можна тимчасово залишити порожнім, але email-повідомлення не будуть працювати повноцінно.

**Мінімальна структура, яку завжди перевіряти:**

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mitglied.ditib-ahlen-projekte.de
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
MAIL_HOST=...
MAIL_FROM_ADDRESS=...
MAIL_LOGO_URL=...
```

Для першого тесту можна тимчасово залишити mail налаштування мінімальними, але перед перевіркою реєстраційних листів потрібно прописати реальний SMTP. Реєстраційні листи відправляються синхронно під час submit, бо на поточному Plesk artifact-deploy немає стабільного queue worker-а.

Брендування листів централізоване в `config/mail.php` → `mail.brand.*`. Для production мінімально перевірити `MAIL_BRAND_URL="${APP_URL}"`, публічний HTTPS `MAIL_LOGO_URL` і коректний footer. Якщо в майбутньому знадобиться повний pixel-control для Outlook/Gmail, перейти на окремий HTML-шаблон (`view:`), але поточний стандартний шар Laravel Markdown не змішувати з ручними HTML-вставками в окремих листах.
