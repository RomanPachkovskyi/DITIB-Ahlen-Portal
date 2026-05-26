# ТЗ: Фото члена під час реєстрації

Робочий документ для поетапної реалізації optional фото профілю в DITIB-Ahlen-Portal.

## Головне рішення

Фото під час реєстрації є необов'язковим. Функція має бути mobile-first, бо основний трафік очікується з телефонів.

Базова архітектура v1:

- Public form: custom Livewire/Blade flow через Cropper.js + Alpine.js + Livewire `WithFileUploads`.
- Admin panel: Filament `FileUpload` / `ImageEntry` / за потреби `ImageColumn`.
- Storage: private Laravel disk `member_photos`, без прямого public URL.
- Output: optimized JPEG 800x800 px, square 1:1.
- Server: не довіряє браузеру повністю, повторно валідує і нормалізує image.
- Datenschutz: фото optional, не публікується, не використовується в email або Excel, і потребує окремої Foto-Einwilligung, якщо користувач додає фото.

Client-side crop/compression приймаємо як основний UX і performance pattern, але не як абсолютну гарантію для HEIC/HEIF. Якщо браузер не може прочитати або обробити файл, користувач бачить зрозумілу помилку і може продовжити реєстрацію без фото.

## Що прийнято з рекомендацій

- Дві окремі великі кнопки в public UI:
  - `Mit Kamera aufnehmen`
  - `Foto auswählen`
- Для camera input використовувати `accept="image/*"` і `capture="user"`, але не покладатися на однакову поведінку в усіх браузерах.
- Для gallery input приймати JPEG, PNG, WebP і, за потреби, `image/*` як fallback для мобільних браузерів.
- Cropper.js використовувати для manual 1:1 crop, touch pan/zoom і mobile-friendly crop area.
- Перед Livewire upload генерувати через canvas фінальний cropped JPEG 800x800.
- На сервер відправляти не оригінальне 5-10MB фото, а оптимізований cropped JPEG.
- Server-side validation і normalization все одно обов'язкові.
- Admin upload/display робити через Filament, але public form не переводити на Filament Form schema у v1.
- Доступ до фото тільки через авторизований Laravel route/controller.
- Фото не додавати в email і не експортувати в Excel.
- Якщо фото некоректне або браузер не може його обробити, форма не блокує всю реєстрацію, бо фото optional.

## Що змінено в плані реалізації

Реалізацію розбиваємо не за UI-екранами, а за ризиком:

1. Backend foundation.
2. Public mobile proof-of-concept.
3. Full public form integration.
4. Admin Filament integration.
5. Member panel display.
6. Datenschutz/deploy/backup documentation.
7. Tests and release verification.

Причина: найбільший технічний ризик знаходиться не в БД і не в Filament, а в mobile browser crop/upload flow: iPhone/Android, HEIC/HEIF, великі фото, слабкий інтернет, EXIF orientation.

## Цільовий UX public form

Фото додається як новий optional Step 4: `Foto`.

Поведінка:

- Крок фото можна пропустити.
- На кроці є дві дії: зробити фото камерою або вибрати з галереї.
- Після вибору відкривається square crop UI.
- Crop area займає всю доступну ширину mobile screen, без маленького вікна.
- Користувач може рухати фото, масштабувати, замінити або видалити.
- Кнопки: `Übernehmen`, `Anderes Foto`, `Foto entfernen`.
- Після `Übernehmen` показується preview фінального квадратного фото.
- Якщо фото не додано, користувач може продовжити.
- Якщо файл не читається або не обробляється браузером, показати м'яку помилку і дозволити продовжити без фото.

Текст біля upload:

> Bitte laden Sie ein aktuelles Porträtfoto hoch, keine Ausweise oder Dokumente.

## Backend-рішення

Фото не зберігати в базі як base64.

Поля в `members`:

- `profile_photo_path` nullable string
- `profile_photo_uploaded_at` nullable timestamp
- `profile_photo_zustimmung` boolean default false
- `profile_photo_zustimmung_at` nullable timestamp

Опційні поля для майбутнього аудиту:

- `profile_photo_mime` nullable string
- `profile_photo_size` nullable unsigned integer

Storage:

- disk: `member_photos`;
- local root fallback: `storage/app/private`;
- production root: `Home directory/ditib-portal-data`, задається через `MEMBER_PHOTOS_ROOT`;
- folder: `member-photos/{member_number}/` всередині root disk;
- filename: `{member_number}-profile.jpg`, наприклад `DA-2026-0001-profile.jpg`;
- у filename не додавати `full_name`;
- у БД зберігати відносний шлях, наприклад `member-photos/DA-2026-0001/DA-2026-0001-profile.jpg`;
- production artifact deploy не має видаляти `Home directory/ditib-portal-data/member-photos`;
- production backup має включати MySQL і папку з фото.

Access:

- фото віддається через авторизований Laravel route/controller;
- admin бачить фото будь-якого члена;
- член у `/konto` бачить тільки своє фото;
- прямий public URL до файлу не використовується;
- фото не кладеться в `public/build` і не віддається через `public/storage`.

Processing:

- browser робить crop/compression до JPEG 800x800;
- сервер перевіряє, що upload справді є image;
- сервер повторно відкриває image, приводить до 800x800 і перезберігає optimized JPEG;
- metadata бажано прибирати при перезбереженні;
- EXIF orientation має бути врахований настільки, наскільки це підтримує обрана backend library;
- якщо браузер уже віддав нормальний canvas JPEG, серверний крок лишається validation/normalization safety net.

Backend library:

- додати `intervention/image` окремою залежністю;
- перед реалізацією перевірити production-сумісність PHP extensions на Plesk;
- WebP не використовувати як baseline v1, щоб уникнути ризиків із PDF і сумісністю.

## Етап 1: Backend foundation

**Статус: ✅ виконано 2026-05-19 16:49 — Codex**

Звіт:

- Міграцію створено через `php artisan make:migration`: `2026_05_19_144555_add_profile_photo_fields_to_members_table`.
- У `members` додано `profile_photo_path` і `profile_photo_uploaded_at`; `Member` model оновлено.
- Додано `intervention/image` 4.1.0 і backend service `ProfilePhotoService`.
- Фото нормалізується через GD/Intervention у JPEG 800x800, filename базується тільки на `member_number`, шлях лишається relative.
- Збереження й читання працюють через private disk `local` → `storage/app/private`.
- Додано protected route `members.profile-photo` і policy: admin читає будь-яке фото, member-user читає фото за email-match.
- Підготовлено production SQL: `deploy-artifacts/production-add-profile-photo-fields-20260519-144555.sql`.
- `scripts/build-artifact.sh` оновлено, щоб runtime folder `storage/app/private/member-photos` не потрапляв у production artifact.
- Додано feature tests для збереження JPEG 800x800 і доступу guest/admin/member/other-member.

Зауваження, що впливає на наступні етапи:

- ✅ Уточнено перед Етапом 5: email-match є прийнятою v1 access model для `/konto`. Якщо один email використано для кількох членів родини/фірми, користувач із цим email бачить усі ці записи і їхні фото. Це не вважається bug; точніший `User -> Member` binding потрібен лише для майбутнього індивідуального self-service edit/approval.
- ⚠️ Локально PHP має `gd`, `exif`, `fileinfo`; на Plesk це треба перевірити до release, інакше Intervention GD не зможе стабільно нормалізувати фото.

Мета: підготувати безпечну основу для зберігання і доступу до фото, без public UI.

Зробити:

- Створити міграцію через `php artisan make:migration`.
- Додати `profile_photo_path` і `profile_photo_uploaded_at` у `members`.
- Оновити `Member` model fillable/casts за потреби.
- Додати `intervention/image`.
- Створити `ProfilePhotoService` або аналогічний service class для:
  - validation input file;
  - normalization to 800x800 JPEG;
  - deterministic path на базі `member_number`;
  - replace old photo;
  - delete photo;
  - безпечне читання private file.
- Створити protected route/controller для показу фото.
- Реалізувати authorization:
  - admin може читати будь-яке фото;
  - member panel user може читати тільки своє;
  - guest не може читати фото.
- Підготувати production SQL у `deploy-artifacts/`, бо production DB зміни виконуються через phpMyAdmin.

Acceptance criteria:

- Фото зберігається тільки на private disk `member_photos`, у production це `Home directory/ditib-portal-data/member-photos/...`.
- У БД зберігається тільки relative path.
- Direct public URL до файлу не існує.
- Route повертає фото тільки авторизованим користувачам.
- Старе фото замінюється контрольовано, без видалення `Member`.

## Етап 2: Public mobile proof-of-concept

**Статус: ✅ виконано 2026-05-19 17:16 — Codex**

Звіт:

- Додано `cropperjs` 2.1.1 через npm.
- Додано local-only PoC route `/photo-upload-poc`; route реєструється тільки для `app()->isLocal()`, не для production.
- Додано Livewire компонент `PhotoUploadPoc` з `WithFileUploads`.
- Реалізовано два file input-и:
  - camera: `accept="image/*"` + `capture="user"`;
  - gallery: `image/jpeg,image/png,image/webp,image/*`.
- Реалізовано Alpine/Cropper flow:
  - file select;
  - browser preview;
  - square 1:1 crop;
  - mobile-width crop area;
  - `canvas.toBlob('image/jpeg', 0.85)` у 800x800;
  - Livewire upload саме cropped JPEG, не original file;
  - replace/remove flow;
  - fallback-помилки для non-image, too-large local input, browser render failure, canvas export failure, Livewire upload failure.
- Livewire PoC валідовує post-crop JPEG як `jpg/jpeg`, max 1 MB, і показує server-side metadata `image/jpeg`, file size, `800 x 800`.
- Додано tests для accepted JPEG і rejected non-JPEG cropped upload.
- Зроблено desktop/headless Chrome verification: cropper canvas створюється, `Übernehmen` проходить, Livewire отримує `image/jpeg` 800x800.
- Screenshot proof збережено локально: `/private/tmp/ditib-photo-poc-success.png`.
- Manual mobile QA виконано 2026-05-20 на телефоні через LAN URL `http://192.168.2.118:8000/photo-upload-poc`: фото успішно обробилось, Livewire показав `image/jpeg`, `167,2 KB`, `800 x 800`.

Зауваження, що впливає на наступні етапи:

- ⚠️ Cropper.js v2 має інший API, ніж старі приклади v1 (`getCroppedCanvas()` більше не baseline). У подальшій інтеграції використовувати поточний v2 flow через `cropper.getCropperSelection().$toCanvas({ width: 800, height: 800 })`.
- ✅ Manual mobile QA через LAN IP Mac mini підтверджено. Для наступних mobile-перевірок запускати `php artisan serve --host=0.0.0.0 --port=8000` і відкривати `http://192.168.2.118:8000/...` з телефона.

Мета: перевірити найризикованішу частину до повної інтеграції в форму.

Зробити:

- Додати тестовий/ізольований Blade/Livewire fragment для photo step або локальний PoC у межах форми.
- Підключити Cropper.js через Vite/npm.
- Реалізувати два input-и:
  - camera: `accept="image/*"` + `capture="user"`;
  - gallery: JPEG/PNG/WebP + mobile fallback.
- Реалізувати Alpine controller:
  - file select;
  - preview;
  - crop init/destroy;
  - zoom/pan/reset/replace/remove;
  - `canvas.toBlob('image/jpeg', quality)` до 800x800.
- Передати cropped blob у Livewire, не оригінал.
- Додати fallback-помилки:
  - file cannot be read;
  - browser cannot render image;
  - canvas export failed;
  - file too large for local processing;
  - unsupported format.

Acceptance criteria:

- На desktop працює gallery upload і crop.
- На mobile crop area не тісна і не ламає layout.
- Після crop Livewire отримує JPEG, а не оригінальний файл.
- Якщо обробка фото падає, реєстрацію можна продовжити без фото.
- HEIC/HEIF не обіцяється як guaranteed support; для нього є fallback behavior.

## Етап 3: Full public form integration

**Статус: ✅ виконано 2026-05-20 11:41 — Codex**

Звіт:

- `MembershipForm` переведено на 4-кроковий flow: Step 4 `Foto`.
- Додано `WithFileUploads` у реальну public form; властивість названо `croppedPhoto`, без конфліктного `upload`.
- Додано `rulesStep4()` з optional JPEG validation: nullable, image, `jpg/jpeg`, max 1 MB.
- Оновлено `rulesForStep()`, `nextStep()`, `submit()` і step indicators на 4 кроки.
- Перенесено робочий Cropper.js v2 flow з PoC у реальну форму:
  - camera/gallery input;
  - square crop;
  - `canvas.toBlob('image/jpeg', 0.85)`;
  - Livewire отримує cropped JPEG 800x800, не original file;
  - replace/remove;
  - fallback-помилки.
- На final submit створюється `Member`, система отримує `member_number`, потім фото зберігається через `ProfilePhotoService` у private path на базі `member_number`.
- Якщо фото не додано, submit працює як раніше, `profile_photo_path` лишається `NULL`.
- Якщо фото додано, `profile_photo_path` і `profile_photo_uploaded_at` заповнюються; фото зберігається як private JPEG 800x800.
- Додано regression test для submit з optional profile photo і збереження private JPEG.
- Browser verification реальної форми виконано: Step 1-3 → Step 4 → crop → Livewire `image/jpeg`, `800 x 800`.
- Screenshot proof: `/private/tmp/ditib-membership-step4-photo.png`.

Оновлення 2026-05-26:

- Step 4 `Foto` більше не відкривається для неповної анкети: перед входом на фото крок перевіряються Step 1-3, а користувач повертається до першого проблемного кроку.
- Перед входом на Step 4 виконується duplicate guard за `birth_date + normalized phone`; це захищає від повторної реєстрації до того, як користувач витратить час на optional фото.
- Та сама duplicate-перевірка повторюється на final submit перед створенням `Member`.

Зауваження, що впливає на наступні етапи:

- ✅ Новий SQL для Етапу 3 не потрібен: використані поля `profile_photo_path` і `profile_photo_uploaded_at` вже додані в Етапі 1.
- ⚠️ Перед production release усе ще потрібен фінальний combined SQL, бо імпорт буде тільки в самому кінці.
- ⚠️ Для Етапу 4 не покладатися на те, що Filament `FileUpload` сам правильно працює з private preview; потрібно перевірити й за потреби використовувати protected photo route.

Мета: інтегрувати фото як Step 4 в існуючу registration form.

Зробити:

- Додати `step = 4` у `MembershipForm`.
- Додати `rulesStep4()` з nullable photo validation.
- Оновити `rulesForStep()`, `nextStep()`, `submit()` і step indicators.
- Додати `WithFileUploads`, але не називати властивість або метод `upload`.
- На final submit:
  - створити `Member`;
  - отримати `member_number`;
  - зберегти фото через backend service за path з `member_number`;
  - оновити `profile_photo_path` і `profile_photo_uploaded_at`.
- Якщо фото optional і не додано, submit працює як зараз.
- Якщо фото було вибрано, але не вдалося обробити, показати помилку на Step 4 і дозволити видалити фото або продовжити без нього.

Acceptance criteria:

- Без фото реєстрація працює без змін для користувача.
- З фото реєстрація створює member і private JPEG 800x800.
- Step 4 не ламає soft validation існуючих Step 1-3.
- Фото не впливає на SEPA/DSGVO consent fields і `zustimmung_at`.
- Email notification не містить фото.

## Етап 4: Admin Filament integration

**Статус: ✅ виконано 2026-05-20 11:54 — Codex**

Звіт:

- Фото додано в shared `MemberForm`, тому воно показується в admin View і Edit.
- Розташування узгоджене: у секції `Persönliche Daten` фото стоїть одразу над `Mitgliedsnummer`.
- Після уточнення layout фото більше не є полем у двоколонковій сітці даних: у `Persönliche Daten` є окремий верхній `View` layout для фото, окремий `Placeholder` для `Mitgliedsnummer`, а звичайні поля винесено у вкладений `Grid`.
- Preview не використовує public storage або Filament temporary URL; `<img>` відкриває фото через protected route `members.profile-photo`.
- Якщо фото відсутнє, photo block не рендериться взагалі: без placeholder, без `Kein Foto`, без порожнього місця.
- Для admin preview додано cache busting `?v=profile_photo_uploaded_at`, а photo response змінено на `Cache-Control: no-store`, щоб після replace/delete браузер не показував старе фото.
- На mobile фото займає 50% ширини секції, на desktop 30%; це рахується від content width секції, не від лівої колонки полів.
- У таблицю Mitglieder фото не додано, щоб не перевантажувати список.
- У `EditMember` додано header actions:
  - `Foto hochladen` / `Foto ersetzen`;
  - `Foto entfernen`.
- Admin upload використовує Filament `FileUpload` тільки як temporary input; реальне збереження, normalization і private path ідуть через `ProfilePhotoService`.
- Admin delete викликає `ProfilePhotoService::delete()` і не видаляє `Member`.
- Заміна/видалення фото не змінює `zustimmung_at`, `sepa_zustimmung` або `dsgvo_zustimmung`.
- Додано tests для Filament edit actions: admin upload і admin delete.
- Додано service tests для replace, delete без видалення member і soft delete без автоматичного видалення фото.
- Browser QA виконано локально: `/admin/members/12/edit` показує фото перед `Mitgliedsnummer`, `/admin/members/12` показує фото без edit/delete кнопок.
- Screenshot proof: `/private/tmp/ditib-admin-profile-photo-edit.png`.

Зауваження, що впливає на наступні етапи:

- ✅ Новий SQL для Етапу 4 не потрібен: нових колонок або таблиць немає.
- ⚠️ Filament `imageCropAspectRatio('1:1')` додає server-side dimension validation і відхиляє ще не обрізані фото в action-тесті. Для v1 використано `imageEditorAspectRatioOptions(['1:1'])`, а фінальний квадрат 800x800 гарантує `ProfilePhotoService`.

Мета: дати адміну перегляд, заміну і видалення фото.

Зробити:

- Додати photo section у `MemberForm`.
- Використати Filament `FileUpload` для admin replace/upload.
- Увімкнути image editor/crop 1:1, якщо method names сумісні з Filament v5.
- Перевірити private storage behavior у Filament.
- Якщо Filament private preview не підходить, показувати preview через наш protected photo route.
- Додати `ImageEntry` у view/infolist.
- `ImageColumn` у таблиці додати тільки якщо це не перевантажує список членів; інакше не показувати фото в таблиці v1.
- Дозволити admin видалити фото без видалення member.
- Заміна/видалення фото не змінює `zustimmung_at`, SEPA або DSGVO facts.

Acceptance criteria:

- Admin бачить фото в member view/edit.
- Admin може замінити фото.
- Admin може видалити фото.
- Після видалення member record лишається.
- Після soft delete фото не видаляється автоматично.

## Етап 5: Member panel display

**Статус: ✅ виконано 2026-05-20 15:42 — Codex**

Звіт:

- Зафіксовано access model v1: `/konto` працює за email authenticated user.
- Якщо на одну пошту зареєстровано кілька `Member`, користувач бачить усі ці записи в `Meine Mitgliedschaften`.
- Додано read-only Filament resource `MemberAccountResource` у member panel.
- `/konto` перенаправляє на список `Meine Mitgliedschaften`.
- Resource query і route binding scoped за `members.email = auth()->user()->email` case-insensitive.
- Користувач не може відкрити запис із іншою поштою через URL.
- View кожного доступного запису показує profile photo через protected route `members.profile-photo`, якщо фото є.
- Якщо фото відсутнє, photo block не рендериться: без placeholder і без порожнього місця.
- Self-service replace/edit фото у v1 не додано.
- Додано feature tests для multi-member same-email access, заборони іншого email, `/konto` redirect, profile photo display і відсутності photo-slot без фото.

Зауваження, що впливає на наступні етапи:

- ✅ Новий SQL для Етапу 5 не потрібен: нових таблиць або колонок немає.
- ✅ Уточнено для майбутнього `Änderungsantrag`: користувач спочатку обирає конкретний запис зі списку `Meine Mitgliedschaften`; кожен запит на зміну створюється для конкретного `member_id` / `member_number`, не для email загалом.
- ⚠️ Backend security rule для майбутнього `Änderungsantrag`: UI показує тільки записи з email authenticated user, але create/view/update запити все одно мають повторно перевіряти на backend, що обраний `member_id` належить до цього email. Це захист від ручної підстановки чужого `member_id` у URL або request payload.

Мета: показати члену його власне фото в `/konto`.

Зробити:

- Додати display фото в member panel.
- Якщо фото відсутнє, не показувати photo block і не залишати порожній slot.
- Не дозволяти self-service replace у v1.
- Якщо пізніше буде self-service replace, це має йти через окремий етап і, ймовірно, `ChangeRequest`.

Acceptance criteria:

- Член бачить фото всіх записів із тим самим email.
- Член не може відкрити фото або запис member з іншим email через URL.
- Відсутнє фото не виглядає як помилка і не створює порожній layout slot.

## Етап 6: Datenschutz, deploy і backup

**Статус: ✅ виконано 2026-05-20 16:45 — Codex**

Звіт:

- Перевірено авторитетний legal text у `../main/docs/legal-texts.md`: для optional profile photo потрібна окрема Einwilligung checkbox, а не тільки загальна DSGVO-згода.
- На Step 4 public form додано пояснення німецькою: фото не публікується і служить тільки внутрішній Mitgliederverwaltung.
- Додано окрему checkbox-згоду на фото; вона обов'язкова тільки якщо користувач додав фото. Submit без фото не вимагає цієї згоди.
- Додано міграцію `2026_05_20_144219_add_profile_photo_consent_fields_to_members_table` з полями `profile_photo_zustimmung` і `profile_photo_zustimmung_at`.
- Public submit із фото фіксує photo consent у БД; видалення фото через `ProfilePhotoService` очищає photo consent fields.
- Admin edit показує photo consent fields read-only і попереджає в upload helper text, що admin upload треба використовувати тільки якщо Einwilligung vorliegt.
- Підготовлено один узгоджений SQL для phpMyAdmin: `deploy-artifacts/production-photo-upload-release-20260520.sql` із profile photo fields, photo consent fields і записами в `migrations`.
- Додано `deploy-artifacts/check-photo-extensions.php` для ручної Plesk-перевірки PHP extensions без SSH: тимчасово завантажити в `public/`, відкрити в браузері, після перевірки видалити.
- `scripts/build-artifact.sh` отримав guard: якщо runtime `storage/app/private/member-photos` потрапить у staging, збірка зупиниться з помилкою.
- `PROJECT.md` оновлено: backup має включати MySQL і private photo folder; redeploy не має очищати `.env`, `APP_KEY` або private photo folder.
- Audit log для фото зафіксовано як майбутнє покращення, не частина v1.

Зауваження, що впливає на наступні етапи:

- ⚠️ Перед production release потрібно вручну перевірити Plesk через `check-photo-extensions.php`: `gd`, `fileinfo`, `imagecreatetruecolor`, `imagejpeg` мають бути `OK`; `exif` бажаний.
- ✅ Фінальний SQL для production import підготовлено: `deploy-artifacts/production-photo-upload-release-20260520.sql`.
- ✅ Datenschutz-сторінка на лендінгу оновлена і відповідає `../main/docs/legal-texts.md`.

Додаткове production-рішення 2026-05-20:

- ✅ Plesk backup перевірено вручну: backup subscription включає папку `Home directory/ditib-portal-data/member-photos`, створену поруч із `httpdocs` і `mitglied.ditib-ahlen-projekte.de`.
- ✅ Фото переводяться на окремий Laravel disk `member_photos`, щоб production runtime-файли жили поза папкою Laravel-проєкту.
- Production `.env`: `MEMBER_PHOTOS_ROOT=/var/www/vhosts/ditib-ahlen-projekte.de/ditib-portal-data` або фактичний absolute path цього Home directory.
- У БД шлях не змінюється: лишається relative `member-photos/{member_number}/{member_number}-profile.jpg`.

Мета: закрити юридичні й production-операційні наслідки.

Зробити:

- Оновити Datenschutz/DSGVO текст:
  - фото optional;
  - мета збору фото;
  - хто має доступ;
  - де використовується;
  - фото не публікується;
  - фото не використовується в email;
  - фото не експортується в Excel;
  - як попросити заміну або видалення.
- Вирішити юридично, чи потрібна окрема optional photo consent checkbox.
- Оновити deploy/backup notes:
  - production backup = MySQL + `Home directory/ditib-portal-data/member-photos`;
  - artifact deploy не має чистити private photo folder;
  - `.env` і `APP_KEY` не міняти.
- Перевірити, що `scripts/build-artifact.sh` не пакує runtime photo files.

Acceptance criteria:

- Документація явно каже, що backup тепер включає files.
- Deploy process не загрожує вже завантаженим фото.
- Текст Datenschutz готовий до юридичного погодження.

## Етап 7: Tests і release verification

**Статус: ✅ локальна release verification виконана 2026-05-20 18:26 — Codex**

Звіт:

- `./vendor/bin/phpunit` пройшов: 59 tests, 285 assertions.
- `npm run build` пройшов.
- `scripts/build-artifact.sh` пройшов і створив production artifact `deploy-artifacts/ditib-ahlen-portal-v1.039-20260520-182520.tar.gz`.
- Перевірено права root у tar: перший рядок `drwxr-xr-x` для `./`, тобто Plesk/Apache 403 через root permissions не очікується.
- Перевірено вміст artifact: є `vendor/autoload.php`, `public/build/manifest.json`, `resources/views/vendor/mail/html/layout.blade.php`.
- Перевірено, що artifact не містить `.env`, `.phpunit.result.cache`, локальну SQLite DB або runtime `storage/app/private/member-photos`.
- У `deploy-artifacts/` залишено тільки актуальні production файли: artifact `.tar.gz` і `production-photo-upload-release-20260520.sql`.

Зауваження:

- `tar: Failed to set default locale` з'являється локально під час tar-команд на macOS, але artifact створюється й читається коректно.
- Production deploy виконано після backup, Plesk extension check і локальної release verification.

Production QA 2026-05-20:

- `/admin` працює після deploy; admin list/view/edit відкриваються, member URLs використовують `member_number`.
- Admin photo upload/replace/delete працює; файл фізично створюється в `ditib-portal-data/member-photos/...` і видаляється з сервера після видалення фото в UI.
- Публічна форма працює; вибір фото, crop і preview працюють.
- `/konto` повністю не перевірявся на production, бо клієнтський доступ/посилання на пошту для кабінету ще не реалізовані. Це окрема майбутня задача і не блокує фото-реліз.
- Подальші помилки або корекції після release фіксуються окремими fix-задачами/чатами.

Мета: довести, що функція безпечна і не ламає реєстрацію.

Тести:

- Registration without photo still works.
- Registration with photo stores private JPEG and DB path.
- Invalid file is rejected or gracefully ignored according to optional flow.
- Oversized post-crop file is rejected.
- Guest cannot access photo route.
- Admin can access any member photo.
- Member can access own photo.
- Member cannot access another member photo.
- Admin can replace photo.
- Admin can delete photo.
- Soft delete member does not delete photo.
- Photo upload requires separate consent; submit without photo does not.
- Deleting photo clears photo consent fields.

Manual QA:

- Desktop Chrome/Safari/Firefox gallery upload.
- iPhone Safari camera and gallery.
- Android Chrome camera and gallery.
- Large photo from phone.
- Rotated portrait photo.
- Slow network behavior.
- Submit without photo.
- Submit after crop.
- Submit after crop error and remove photo.

Release checklist:

- `./vendor/bin/phpunit`
- `npm run build`
- `scripts/build-artifact.sh`
- Check artifact permissions with `tar -tvzf deploy-artifacts/ditib-ahlen-portal-*.tar.gz | head`
- Prepare/import SQL through phpMyAdmin if migration exists
- Verify production form, `/admin`, `/konto`, and existing registrations

## Не робити у v1

- Не робити AI/face detection.
- Не забороняти документи автоматично як hard rule.
- Не зберігати фото в base64 у БД.
- Не зберігати фото в `public/`, `public/build` або `public/storage`.
- Не додавати full name у filename.
- Не додавати фото в email.
- Не додавати фото в Excel export.
- Не давати member self-service photo replace без окремого approval/change-request рішення.
- Не переносити всю public form на Filament Form schema тільки заради upload.

## Відкриті рішення перед стартом реалізації

1. ✅ Вирішено в Етапі 6: потрібна окрема optional checkbox-згода на фото; вона required тільки якщо фото додано.
2. ✅ Вирішено в Етапі 1: server-side max size для фінального normalized JPEG — 1 MB; input validation у backend service тимчасово дозволяє до 8 MB для admin/manual сценаріїв.
3. ✅ Вирішено в Етапі 4: фото показуємо тільки у View/Edit; `ImageColumn` у admin table не додаємо у v1, щоб не перевантажувати список і не тягнути приватні зображення в таблицю.
4. ✅ Вирішено для v1: admin-позначку `Foto geprüft` не додаємо. Якщо в майбутньому з'явиться процес перевірки фото перед друком/PDF/картками, це окреме покращення.
5. ✅ Вирішено для майбутнього PDF v1: якщо фото відсутнє, placeholder не показувати; photo block просто пропускається, щоб optional фото не виглядало як помилка або незаповнений обов'язковий елемент.
6. ✅ Вирішено для v1: audit log для admin replace/delete photo не робити зараз; перенесено в список майбутніх покращень.

## Майбутні покращення

- Audit log для фото: хто і коли завантажив, замінив або видалив фото.
- Процес відкликання photo consent із `/konto`, якщо пізніше буде self-service edit/change-request.
- Optional workflow `Foto geprüft`, якщо пізніше фото використовуватимуться для друку, PDF-пакетів або карток і потрібна ручна перевірка якості.
