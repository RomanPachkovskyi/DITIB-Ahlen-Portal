# План: `/konto` Self-Service Edit І Audit Log

Тимчасовий робочий документ для реалізації повного кабінету члена громади: спільна member schema, редагування даних у `/konto`, перехід у `Verarbeitung`, email адміну і єдина система логування змін.

Створено: 2026-05-29  
Статус: чернетка для перевірки Roman  
Автор чернетки: Codex

> Це робочий документ. Після реалізації фінальні рішення потрібно перенести в `PROJECT.md`, щоб не тримати паралельну документацію.

## Мета

Зібрати одну логічну систему для даних члена громади:

- публічна форма створює `Member`;
- admin бачить і керує повною карткою;
- клієнт у `/konto` бачить майже повну картку і може контрольовано редагувати власні дані;
- кожна зміна від admin або member логуються;
- кожна зміна від member переводить запис у `processing` (`Verarbeitung`) і відправляє email адміну.

Головна архітектурна вимога: не створювати ще одну окрему форму. Admin і Konto мають використовувати спільний Filament schema-builder із різними режимами доступу.

## Поточний Стан

Admin зараз використовує повну schema:

- `app/Filament/Resources/Members/MemberResource.php`
- `app/Filament/Resources/Members/Schemas/MemberForm.php`

Konto зараз використовує окрему коротку schema:

- `app/Filament/Member/Resources/MemberAccounts/MemberAccountResource.php`

Через це admin бачить усі блоки, а клієнт бачить тільки короткий read-only блок `Mitgliedschaft`. Magic-link login не створив цю різницю; він просто відкрив уже існуючий `/konto` resource.

У коді також уже є `ChangeRequest`:

- `app/Models/ChangeRequest.php`
- `database/migrations/2026_04_27_095130_create_change_requests_table.php`

Це не `CHANGELOG.md`. Це окрема таблиця застосунку для майбутніх заявок на зміну з `pending/approved/rejected`. Зараз вона не є audit log. Щоб не мати дві системи логування, у цій задачі робимо одну нову/чітку систему audit log для фактичних змін. `ChangeRequest` не використовуємо як паралельний журнал; після реалізації audit log окремо вирішимо, залишити його для майбутнього approval workflow чи прибрати cleanup-міграцією.

## Підтверджені Рішення

- Клієнт має бачити максимум інформації.
- Від клієнта приховуємо тільки `admin_notiz` (`Interne Notiz`).
- `status` клієнт БАЧИТЬ (read-only) і в списку, і в detail — список `/konto` робимо з тими ж колонками, що admin-таблиця; але клієнт ніколи не РЕДАГУЄ `status` (він керується тільки системно/адміном). (Рішення Roman 2026-05-29, оновлює попередню чернетку, де `status` ховався від клієнта.)
- Detail page у `/konto` має бути `Vorschau`, а не `Anzeigen`.
- На `Vorschau` має бути кнопка `Bearbeiten`.
- Якщо клієнт змінив хоч одне поле, запис переходить у `processing` (`Verarbeitung`).
- Адмін отримує email, що клієнт відредагував свої дані.
- Усі зміни мають логуватися і від admin, і від member.
- Для IBAN/BIC у логах і email показуємо тільки факт зміни, без старих/нових значень.
- Member-edit має мати server-side allowlist дозволених полів. Не можна покладатися тільки на hidden/disabled поля у Filament schema.
- Email не редагується клієнтом у v1, бо email є ключем доступу до `/konto`.
- Admin emails не мають отримувати member magic-link доступ через спільний `User`/web guard.
- Нова migration допускається, але production SQL готуємо тільки після локальної реалізації й тестів.

## Що Має Бачити Клієнт

### `Persönliche Daten`

Клієнт бачить повний блок:

- profile photo preview, якщо є;
- `member_number`;
- `full_name`;
- `anrede`;
- `birth_date`;
- `birth_place`;
- `staatsangehoerigkeit`;
- `familienangehoerige`;
- `beruf`;
- `heimatstadt`;
- `street`;
- `postal_code`;
- `city`;
- `state`;
- `phone`;
- `instagram`;
- `email` read-only у v1;
- `cenaze_fonu`;
- `cenaze_fonu_nr`, якщо актуально;
- `gemeinderegister`.

### `Beitrag & Bankverbindung`

Клієнт бачить повний блок:

- `monatsbeitrag`;
- `zahlungsart`;
- `kontoinhaber`, якщо `lastschrift`;
- `iban`, якщо `lastschrift`;
- `bic`, якщо `lastschrift`;
- `kreditinstitut`, якщо `lastschrift`.

Для IBAN/BIC:

- у власному кабінеті клієнт може бачити/редагувати власні банківські дані;
- у audit log не зберігаємо raw old/new IBAN/BIC;
- у email адміну не показуємо old/new IBAN/BIC;
- у логах/email пишемо тільки, що поле було змінено.

### `Status & Verwaltung`

Клієнт бачить:

- `status` (read-only, не редагується клієнтом);
- `zustimmung_at`;
- `sepa_zustimmung`;
- `dsgvo_zustimmung`;
- `profile_photo_zustimmung`;
- `profile_photo_zustimmung_at`.

Клієнт не бачить:

- `admin_notiz`.

Consent fields у загальному випадку read-only. Виняток (Рішення Roman 4): якщо клієнт перемикає `zahlungsart` на `lastschrift`, потрібне нове підтвердження SEPA-згоди — клієнт наново бере на себе відповідальність, `sepa_zustimmung` і відповідний timestamp фіксуються заново. Без цього підтвердження перехід на `lastschrift` не зберігається. DSGVO/photo consent клієнт у v1 не змінює.

## Логіка Редагування В `/konto`

1. Клієнт відкриває magic link і заходить у `/konto`.
2. `/konto` показує `Meine Mitgliedschaften`.
3. Клієнт відкриває запис.
4. Detail page називається `Vorschau`.
5. У header є кнопка `Bearbeiten`.
6. Клієнт редагує дозволені поля.
7. Після save:
   - система визначає фактично змінені поля;
   - створює audit log;
   - якщо зміни справді були, статус запису стає `processing`;
   - адмін отримує email;
   - клієнт повертається на `Vorschau`.

Правила доступу:

- клієнт може view/edit тільки записи, де `lower(members.email) = lower(auth user email)`;
- create/delete у `/konto` лишаються заборонені;
- inactive записи показуються у списку (напівпрозорі, зі статусом), але `canView()` і `canEdit()` для них повертають `false` — клієнт не може ні відкрити detail, ні редагувати, навіть через прямий URL; усі питання по inactive — до адміна;
- `getEloquentQuery()` повертає всі записи з email клієнта (включно з inactive, щоб список їх показав); блокування inactive — на рівні `canView()/canEdit()` і route binding, не на рівні query;
- inactive-only email не отримує login link (немає що відкривати), натомість inactive-notice email.
- save у member-edit має використовувати явний server-side allowlist, наприклад `Arr::only($data, MemberFormContext::memberEditableFields())`;
- навіть якщо Livewire request підкине `status`, `admin_notiz`, `member_number`, `email`, consent fields або інше недозволене поле, backend має його проігнорувати;
- після allowlist застосовується системна status transition у `processing`, а не дані з request.

Поля, які не можна редагувати клієнтом у v1:

- `member_number`;
- `email`;
- `status`;
- `admin_notiz`;
- `sepa_zustimmung`;
- `dsgvo_zustimmung`;
- `zustimmung_at`;
- `profile_photo_zustimmung`;
- `profile_photo_zustimmung_at`;
- `unterschrift`;
- system/photo path fields.

## Inactive Member Login Flow

Поточна логіка:

- якщо email невідомий, UI показує нейтральне повідомлення;
- якщо email є в `members`, відправляється magic link.

Нова логіка:

- якщо email є тільки в `inactive` записах, magic link не відправляти;
- натомість надіслати email із поясненням, що запис зараз не активний і треба звернутися до DITIB Ahlen;
- UI в браузері все одно має лишатися нейтральним, щоб не розкривати, чи є така адреса в базі.
- якщо email належить admin-акаунту (`ADMIN_EMAILS` / `User::isAdmin()`), member magic-link не створювати. Це мінімальний захист від ситуації, де magic-link створює web-session для `User`, який має admin-права.

Напрямок тексту листа німецькою:

> Ihr Mitgliedskonto ist derzeit nicht aktiv. Bitte wenden Sie sich an uns, damit wir den Status gemeinsam prüfen können.

Рішення Roman (1, уточнено): inactive записи в `/konto` показуються у списку напівпрозорими (dimmed) разом зі статусом, але клієнт не може ні відкрити їх (detail), ні редагувати — рядок візуально присутній, але неактивний. Magic-link дозволяти, якщо є хоча б один не-inactive запис. Якщо всі записи email inactive → magic-link не слати (нема чого відкривати), надіслати inactive-notice.

## Спільна Schema Архітектура

Потрібно створити context system навколо `MemberForm`.

Можлива структура:

- `app/Filament/Resources/Members/Schemas/MemberForm.php`
- `app/Filament/Resources/Members/Schemas/MemberFormContext.php`

Контексти:

- `adminView`;
- `adminEdit`;
- `memberView`;
- `memberEdit`.

Опис полів має бути один. Контекст вирішує:

- поле visible чи hidden;
- поле editable чи disabled;
- validation rules;
- visibility секцій;
- форматування значень;
- чи показувати admin-only поля/дії.

Admin resource:

- `MemberResource::form()` викликає shared builder з admin context.

Member resource:

- `MemberAccountResource::form()` викликає shared builder з member context.

Важливо:

- не копіювати повну admin form у `MemberAccountResource`;
- labels/options/rules тримати в одному місці настільки, наскільки дозволяє Filament;
- public registration form зараз Livewire Blade, не Filament, тому повністю спільну schema для public/admin/konto одразу не отримаємо;
- нормалізацію й validation rules для телефону, Instagram, IBAN/BIC, PLZ, contribution треба тримати у shared helpers (`app/Support/...`) і застосувати і в Livewire registration, і у Filament admin/member edit. Це не відкладати після self-edit: member-edit без спільної нормалізації може зламати формат даних і майбутні duplicate/security checks.

## Server-Side Allowlist Для Member Edit

Це головний security boundary для `/konto/edit`.

Причина:

- `Member::$fillable` зараз містить майже всі поля;
- Filament/Livewire request можна підробити;
- hidden або disabled поле в UI не є backend-захистом;
- якщо зберігати всі `$data`, клієнт теоретично може підкинути `status = active`, `admin_notiz`, `member_number` або consent fields.

Вимога:

- у `EditMemberAccount` перед save брати тільки явний список дозволених полів;
- недозволені поля повністю ігнорувати;
- status transition у `processing` виконувати тільки серверною логікою після порівняння дозволених полів.

Орієнтовний allowlist v1:

- `full_name`;
- `anrede`;
- `birth_date`;
- `birth_place`;
- `staatsangehoerigkeit`;
- `familienangehoerige`;
- `beruf`;
- `heimatstadt`;
- `street`;
- `postal_code`;
- `city`;
- `state`;
- `phone`;
- `instagram`;
- `cenaze_fonu`;
- `cenaze_fonu_nr`;
- `gemeinderegister`;
- `monatsbeitrag`;
- `zahlungsart`;
- `kontoinhaber`;
- `iban`;
- `bic`;
- `kreditinstitut`.

Заборонені для member-edit v1:

- `id`;
- `member_number`;
- `email`;
- `status`;
- `admin_notiz`;
- `sepa_zustimmung`;
- `dsgvo_zustimmung`;
- `zustimmung_at`;
- `profile_photo_path`;
- `profile_photo_uploaded_at`;
- `profile_photo_zustimmung`;
- `profile_photo_zustimmung_at`;
- `unterschrift`;
- `deleted_at`;
- timestamps.

Обов’язковий негативний тест:

- member надсилає `status`, `admin_notiz`, `member_number`, `email`, consent fields у Livewire/Filament payload;
- після save ці поля не змінюються;
- статус, якщо були легальні зміни, переходить тільки в `processing`, не в підкинуте значення.

## Email Як Ключ Доступу

У v1 клієнт не редагує `email`.

Причина:

- email є ключем доступу до `/konto`;
- один email може мати кілька записів родини/фірми;
- зміна email може заблокувати самого клієнта або тихо перенести доступ до запису на іншу пошту.

Якщо пізніше дозволяти зміну email, потрібен окремий flow:

- new email double opt-in;
- підтвердження посиланням на нову адресу;
- зміна `members.email` тільки після підтвердження;
- audit log;
- admin notification.

## Admin І Member Identity

Поточний magic-link flow створює/логінить `User` через спільний web guard. Якщо admin email одночасно є в `members`, magic-link може створити admin-capable session.

Мінімальний v1 фікс:

- `MemberMagicLoginService::createForEmail()` не створює token, якщо email належить admin-акаунту;
- для такого email UI лишається нейтральним;
- можна логувати security event без розкриття в UI.

Кращий майбутній варіант:

- окремий guard/model для members;
- admin і member identity повністю розділені.

Для v1 достатньо мінімального фікса + regression test.

## Єдина Система Логування

Це не `CHANGELOG.md`. `CHANGELOG.md` лишається історією роботи агентів/розробки.

Це також не поточний `ChangeRequest`, бо він описує approval-заявки, а не фактичний журнал змін.

Рекомендована нова таблиця: `member_audit_logs`.

Поля:

- `id`;
- `member_id`;
- `actor_type`: `admin`, `member`, `system`;
- `actor_user_id`, nullable;
- `source`: `admin_panel`, `member_panel`, `public_form`, `system`;
- `event`: `created`, `updated`, `status_changed`, `photo_uploaded`, `photo_deleted`;
- `field_name`, nullable для не-field events;
- `old_value`, nullable;
- `new_value`, nullable;
- `old_value_masked`, nullable;
- `new_value_masked`, nullable;
- `sensitive`: boolean;
- `ip_address`, nullable;
- `user_agent`, nullable;
- `meta` JSON, nullable;
- `created_at`.

Sensitive fields:

- `iban`;
- `bic`;
- `unterschrift`;
- можливо `profile_photo_path`.

Для sensitive fields:

- не зберігати raw old/new;
- `sensitive = true`;
- `old_value` / `new_value` лишати null;
- у masked/summary писати `geändert`;
- email адміну теж показує тільки факт зміни.

Що логувати:

- admin edit save;
- member edit save;
- admin table status actions;
- admin bulk status actions;
- profile photo upload/replace/delete;
- public registration create можна додати пізніше як `created`, але це не обов’язково для першого етапу.

DSGVO/retention:

- audit log містить персональні дані навіть для “несенситивних” полів: ім’я, адреса, телефон;
- при soft-delete/erasure потрібно мати політику очищення або анонімізації `member_audit_logs`;
- мінімум для v1: передбачити service/command для anonymize/delete audit logs по `member_id`, навіть якщо UI для цього буде пізніше;
- не робити audit log “вічним відкритим архівом” без retention decision.

Рекомендований service:

- `app/Services/MemberAuditLogger.php`

Відповідальність service:

- порівняти старі й нові значення;
- ігнорувати незмінені поля;
- маскувати sensitive values;
- записати один log row на кожне змінене поле;
- підтримувати event-only logs для photo/status actions;
- фіксувати actor/source/ip/user-agent.

## Email Адміну Після Зміни Клієнтом

Створити:

- `app/Mail/MemberUpdatedByMemberNotification.php`;
- `resources/views/emails/member-updated-by-member-notification.blade.php`.

Кому:

- `info@ditib-ahlen-projekte.de`.

Що в листі:

- ім’я клієнта;
- `member_number`;
- email клієнта;
- список змінених полів;
- для звичайних полів: старе/нове значення, якщо це безпечно;
- для IBAN/BIC: тільки `geändert`;
- пряме посилання на admin record.

Відправка синхронна, як і поточні project emails. SMTP-помилка не має ламати save; її потрібно логувати.

## Правила Статусу

Якщо клієнт змінив хоча б одне editable поле:

- статус стає `processing`;
- audit log містить змінені поля;
- audit log також фіксує зміну статусу, якщо статус реально змінився;
- admin отримує один email за одну операцію save.

Якщо клієнт натиснув save без реальних змін:

- статус не змінюється;
- email адміну не відправляється;
- audit log не створюється або створюється тільки технічний no-op event, якщо ми окремо вирішимо його логувати. Рекомендація: no-op не логувати.

Якщо поточний статус `inactive`:

- magic link не відправляється;
- клієнт не доходить до edit.

Якщо поточний статус `pending` або `active`:

- member edit переводить у `processing`.

Якщо поточний статус уже `processing`:

- лишається `processing`;
- changed fields логуються;
- admin email відправляється.

Admin changes:

- admin і далі може переводити записи в `active` або `inactive`;
- status actions мають логуватися;
- поточний email клієнту при переході в `active` має залишитись працювати.

## Фази Реалізації

### Phase 1: Підтвердження Плану І Тестові Цілі

- Перевірити цей документ.
- Вирішити mixed-email inactive behavior: якщо один email має активні й inactive записи.
- Вирішити, чи приховувати `status` у списку `/konto`, чи тільки в detail/edit.
- Підтвердити member-edit allowlist і те, що `email` не редагується клієнтом у v1.
- Зафіксувати тестові сценарії для full member view, hidden admin fields, edit access, inactive-login behavior.

### Phase 2: Shared Member Schema

- Додати `MemberFormContext`.
- Переробити `MemberForm` у shared builder.
- Замінити коротку schema в `MemberAccountResource` на shared builder.
- Переконатися, що member view показує всі дозволені секції.
- Переконатися, що admin view/edit візуально й функціонально не зламались.

### Phase 3: Member Edit Page

- Додати `EditMemberAccount`.
- Увімкнути `canView()` і `canEdit()` тільки для власних НЕ-inactive записів; inactive — заблоковані навіть через прямий URL.
- У списку `/konto` inactive рядки показувати напівпрозорими (dimmed) зі статусом, але без `recordUrl`/відкриття і без row actions.
- Додати `Bearbeiten` на member view.
- Змінити breadcrumb/member view wording на `Vorschau`.
- Додати server-side allowlist у save flow.
- Додати shared validation/normalization для phone, Instagram, IBAN/BIC і contribution.
- SEPA re-consent при переході `zahlungsart` → `lastschrift` (нова згода + timestamp).
- Після save повертати на view.

### Phase 4: Inactive Login Handling

- Оновити `MemberMagicLoginService`.
- Додати inactive-account email mailable/template.
- Залишити browser UI нейтральним.
- Не створювати login token для inactive-only email.
- Не створювати member login token для admin emails.
- Додати cleanup для expired/used `member_login_tokens`, бо таблиця містить `ip_address` і `user_agent`.
- Додати tests.

### Phase 5: Audit Logging

- Після стабілізації schema/edit flow створити migration через `php artisan make:migration`.
- Додати `MemberAuditLog` model.
- Додати `MemberAuditLogger` service.
- Логувати member edit.
- Логувати admin edit.
- Логувати admin status actions і bulk actions.
- Логувати profile photo changes.
- Додати sensitive masking для IBAN/BIC.
- Передбачити retention/anonymization для audit logs по `member_id`.

### Phase 6: Admin Notification For Member Edits

- Додати mailable/template.
- Відправляти після успішного member edit, якщо є реальні зміни.
- Додати direct admin record URL.
- Не показувати raw IBAN/BIC.
- Логувати SMTP-помилки без зламу save.

### Phase 7: Verification

Feature tests:

- member бачить повні дозволені секції;
- member бачить `Status` (read-only), але не бачить `Interne Notiz` (`admin_notiz`);
- member може редагувати власний не-inactive запис;
- member не може редагувати чужий запис;
- member НЕ може відкрити (`canView`) або редагувати (`canEdit`) власний inactive запис, навіть через прямий URL;
- inactive запис показується у списку `/konto` (dimmed), але без відкриття/редагування;
- member edit ставить статус `processing`;
- no-op save не змінює status/email/audit;
- member не може підкинути `status`, `admin_notiz`, `member_number`, `email` або consent fields через request;
- member не може редагувати `email` у v1;
- `monatsbeitrag` нижче EUR 10 відхиляється у member edit;
- перехід `zahlungsart` → `lastschrift` без SEPA re-consent не зберігається;
- inactive-only email отримує inactive notice, не magic link;
- admin email не отримує member magic-link token;
- admin edit створює audit logs;
- admin status actions створюють audit logs;
- IBAN/BIC changes masked в audit/email.
- expired/used `member_login_tokens` очищуються cleanup-командою.

Manual QA:

- `/admin/members/{member_number}` view/edit;
- `/konto/login` magic link;
- `/konto/mitgliedschaften`;
- `/konto/mitgliedschaften/{member_number}`;
- `/konto/mitgliedschaften/{member_number}/edit`;
- mobile layout для view/edit.

### Phase 8: Production Preparation

Тільки після локальних tests і manual QA:

- підготувати production SQL у `deploy-artifacts/` для phpMyAdmin;
- оновити `scripts/export-production-sql.php`, якщо full export має включати нові таблиці;
- оновити `PROJECT.md` фінальною архітектурою;
- оновити `CHANGELOG.md`;
- збирати artifact тільки коли буде готовність до deploy.

## Залежності І Ризики

- Filament v5 schema APIs: `Section` має бути `Filament\Schemas\Components\Section`, не old Forms namespace.
- Admin consent read-only behavior має залишитися.
- Email клієнту при переході в `active` має залишитися.
- Emails при видаленні member мають залишитися.
- Protected route для profile photo має зберегти admin/member authorization.
- `/konto` query зараз scoped тільки за email; inactive filtering змінить access і tests.
- Поточний `ChangeRequest` може плутати майбутню роботу; треба документально зафіксувати, що audit log є єдиною системою фактичного логування.
- Public registration form не Filament; повне reuse з admin/konto потребує окремого майбутнього refactor.
- Member edit без server-side allowlist є неприйнятним ризиком.
- Shared web guard для admin/member є тимчасовим компромісом; admin emails треба заблокувати в member magic-link flow.
- `member_login_tokens` містить PII (`ip_address`, `user_agent`), тому потрібен cleanup expired/used токенів.
- Production не має server-side artisan migrations; для deployment потрібен SQL файл для phpMyAdmin.

## Рішення Roman (зафіксовано 2026-05-29)

1. **Mixed-email → inactive показуємо dimmed, але заблоковані.** Inactive записи зʼявляються у списку `/konto` напівпрозорими, зі статусом, але клієнт не може їх ні відкрити, ні редагувати (блокування у `canView()/canEdit()` + route binding, не у query). Magic-link дозволяється, якщо є хоча б один не-inactive запис; якщо всі inactive → magic-link не слати + inactive-notice email.
2. **Список `/konto` = як у admin.** Ті самі колонки (`member_number`, name, `status` badge, city). Це безпечно, бо admin-таблиця не виводить нічого справді прихованого: `admin_notiz` не є колонкою. Наслідок: `status` видимий клієнту (read-only) і в списку, і в detail; справді прихований тільки `admin_notiz`. `status` клієнт ніколи не редагує.
3. **`email` — не редагується клієнтом у v1** (ключ доступу). Пізніше тільки через double opt-in. (Залишається без змін.)
4. **`monatsbeitrag` редагований, мінімум EUR 10** — центральне правило для public/admin/member. Member може і збільшувати, і зменшувати, але не нижче EUR 10.
5. **`zahlungsart` → `lastschrift` вимагає нової SEPA-згоди.** Клієнт при кожній такій зміні наново бере відповідальність: показати SEPA-consent checkbox, зберегти `sepa_zustimmung` + новий timestamp; без підтвердження перехід на `lastschrift` не зберігається. `sepa_zustimmung` лишається system-controlled (не вільне поле allowlist) — встановлюється тільки через цей явний consent-крок.
