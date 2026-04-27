# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: members.kontoinhaber (Connection: sqlite, Database: /Users/roman/Project/DITIB-Ahlen/portal/database/database.sqlite, SQL: insert into "members" ("full_name", "birth_date", "birth_place", "staatsangehoerigkeit", "familienangehoerige", "cenaze_fonu", "cenaze_fonu_nr", "gemeinderegister", "beruf", "heimatstadt", "street", "postal_code", "city", "state", "email", "phone", "monatsbeitrag", "zahlungsart", "kontoinhaber", "iban", "bic", "kreditinstitut", "unterschrift", "sepa_zustimmung", "dsgvo_zustimmung", "zustimmung_at", "status", "member_number", "updated_at", "created_at") values (Munas Print, 1988-01-01 00:00:00, Ahlen, Ukraine, 5, 0, ?, 0, Print, Ankara, Ost 36, 59227, Ahlen, Nordrhein-Westfalen, info@munas123.de, 01515553344, 35, barzahlung, ?, ?, ?, ?, data:image/png;base64,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, 0, 1, 2026-04-27 14:28:48, pending, DA-2026-0002, 2026-04-27 14:28:48, 2026-04-27 14:28:48))

PHP 8.5.4
Laravel 13.6.0
localhost:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:841
1 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:797
2 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:576
3 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:540
4 - vendor/laravel/framework/src/Illuminate/Database/Query/Processors/Processor.php:32
5 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:4246
6 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:2270
7 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:1660
8 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:1576
9 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:1380
10 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:1224
11 - vendor/laravel/framework/src/Illuminate/Support/helpers.php:393
12 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:1223
13 - vendor/laravel/framework/src/Illuminate/Support/Traits/ForwardsCalls.php:23
14 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:2803
15 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:2819
16 - app/Livewire/MembershipForm.php:135
17 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:36
18 - vendor/laravel/framework/src/Illuminate/Container/Util.php:43
19 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:96
20 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:35
21 - vendor/livewire/livewire/src/Wrapped.php:23
22 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:685
23 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:240
24 - vendor/livewire/livewire/src/LivewireManager.php:131
25 - vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php:190
26 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
27 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:269
28 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:215
29 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
30 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
31 - vendor/livewire/livewire/src/Mechanisms/HandleRequests/RequireLivewireHeaders.php:19
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
33 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:52
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php:104
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
40 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
41 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
42 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
43 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
44 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
45 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
46 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
47 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
48 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
49 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
50 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
51 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
52 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
53 - vendor/livewire/livewire/src/Features/SupportDisablingBackButtonCache/DisableBackButtonCacheMiddleware.php:19
54 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
55 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:27
56 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
57 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:47
58 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
59 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
60 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
61 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
62 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
63 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
64 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
65 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
66 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
67 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
68 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
69 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:28
70 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
71 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
72 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
73 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
74 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
75 - public/index.php:20
76 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Previous exception

### 1. PDOException

SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: members.kontoinhaber

0 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:587
1 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:587
2 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:830
3 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:797
4 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:576
5 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:540
6 - vendor/laravel/framework/src/Illuminate/Database/Query/Processors/Processor.php:32
7 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:4246
8 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:2270
9 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:1660
10 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:1576
11 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:1380
12 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:1224
13 - vendor/laravel/framework/src/Illuminate/Support/helpers.php:393
14 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:1223
15 - vendor/laravel/framework/src/Illuminate/Support/Traits/ForwardsCalls.php:23
16 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:2803
17 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:2819
18 - app/Livewire/MembershipForm.php:135
19 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:36
20 - vendor/laravel/framework/src/Illuminate/Container/Util.php:43
21 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:96
22 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:35
23 - vendor/livewire/livewire/src/Wrapped.php:23
24 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:685
25 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:240
26 - vendor/livewire/livewire/src/LivewireManager.php:131
27 - vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php:190
28 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
29 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:269
30 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:215
31 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
33 - vendor/livewire/livewire/src/Mechanisms/HandleRequests/RequireLivewireHeaders.php:19
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:52
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php:104
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
40 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
41 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
42 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
43 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
44 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
45 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
46 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
47 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
48 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
49 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
50 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
51 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
52 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
53 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
54 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
55 - vendor/livewire/livewire/src/Features/SupportDisablingBackButtonCache/DisableBackButtonCacheMiddleware.php:19
56 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
57 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:27
58 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
59 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:47
60 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
61 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
62 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
63 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
64 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
65 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
66 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
67 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
68 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
69 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
70 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
71 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:28
72 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
73 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
74 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
75 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
76 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
77 - public/index.php:20
78 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

POST /livewire-f89e5246/update

## Headers

* **host**: localhost:8000
* **connection**: keep-alive
* **content-length**: 8101
* **sec-ch-ua-platform**: "macOS"
* **user-agent**: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36
* **sec-ch-ua**: "Google Chrome";v="147", "Not.A/Brand";v="8", "Chromium";v="147"
* **content-type**: application/json
* **x-livewire**: 1
* **sec-ch-ua-mobile**: ?0
* **accept**: */*
* **origin**: http://localhost:8000
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: cors
* **sec-fetch-dest**: empty
* **referer**: http://localhost:8000/
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: uk,de;q=0.9,en;q=0.8
* **cookie**: wordpress_test_cookie=WP%20Cookie%20check; wordpress_logged_in_37d007a56d816107ce5b52c10342db37=Munas-Admin%7C1778069275%7CkJWyvWPYgU9ssE110H37XCinboFtBupOEPuCujSEjcg%7C7e31b45430f290e41fc86838f33e6c44ae4fbf8405dae5f5c9779087714b02d7; wp-settings-time-1=1776955492; wp-settings-1=libraryContent%3Dbrowse%26posts_list_mode%3Dlist%26editor%3Dtinymce; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6ImZleDUzU1lwUnRRdC9FdmhuV2JiYlE9PSIsInZhbHVlIjoiK0hEdzNJNTV0QzYxc2RNWUZqeG8wQURyNUtVV2c1SnJ2TWhUL1Jvc2p5bzlrdU5WSFpmNENac1gzTEVoNW9CM1RIL1lnOGl0MW5zUEhGR2FPSm9MS2UrbEJ6bUd4aDFZYTNrN2tVV2NJcCt0WjVnOWdiN1JRQUhNeGs4TkxGTFB2MHkzdkROVlBUOFlpQUpFSDdZbXFscUNMa1plc2xZYXIvQXJyRzg3aWpwRmpYQzY3cnRJaHY0eERST05oa0NvdWQ3RDhpdEVidGtWNVdWWGpvYmpiNjFQZFlZMmQreHcwS1g5YnZ5LzV4Yz0iLCJtYWMiOiI0M2EyZDMzNGExYTI1OTNmMDg2MWY2NTI0MDEyYzExMDdjMjc5ZDk0YTJkZTU3MzE5YzViMDVjYTdhYzczYjk2IiwidGFnIjoiIn0%3D; XSRF-TOKEN=eyJpdiI6ImxReDhyUkpxbXFJY0JDMTFpNkNpWnc9PSIsInZhbHVlIjoiUTlSTFAwQUZxRi8wU21sOTl1cnRXTS9SaE1VLzBaMVg4ZDRLUWRwREF0d3YxaHNjSUZHNCs4VVp2VjZURUJRQlhIT2F5eXhwY0UzOWo4eUNXdTZsaFExNHFYSE1SaVVBSS9PeXdpa09rc1dXeEIyRHBjLy9HbjA3Zy85eDdadGMiLCJtYWMiOiI5MTFlN2RhZmU0OTUwNTJjYmNhNTIwMGVlMGQ0NzcxOWI1NTEwYjYwYTA0YjIzM2QzZDA5OGNiYTYyZjgyN2JkIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6Ik16eE9ENXlBRGw3RzFXNUcxNFg1aXc9PSIsInZhbHVlIjoiaVVFelo0bWQ2NUlzTjBGWUw1ZkR4MTRZOVp3WEQvblVYUjdnRkF0YTdNcE93WGlPV241S2tXYkp5a3cramhDUTVnellxcUE4aXpyWjJXeU9hSjNIRm1HamdValZVSG5MNE1RY1lwMmVpNDJKZC8vYWs5Wmt3YytwcUMrNVlEaSsiLCJtYWMiOiI2NDhkODI4YTRiZDM2NDI1OWQzNDM0NjI2ZTgzMWRiYWIzN2FlNzBiMWJlNGMyYTA4NDVmNjE3YjYzMDc2OWIwIiwidGFnIjoiIn0%3D

## Route Context

controller: Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate
route name: default-livewire.update
middleware: web, Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders

## Route Parameters

No route parameter data available.

## Database Queries

* sqlite - select * from "sessions" where "id" = 'XSsf9xndyHDu8rmAasNcZ4kxsD6fYnCMYIgoGxld' limit 1 (0.4 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-livewire-checksum-failures:127.0.0.1') (0.03 ms)
* sqlite - select "member_number" from "members" where strftime('%Y', "created_at") = cast('2026' as text) and "member_number" is not null order by "id" desc limit 1 (0.04 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.03 ms)
