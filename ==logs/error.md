# ParseError - Internal Server Error

syntax error, unexpected token ";", expecting "]"

PHP 8.5.4
Laravel 13.6.0
localhost:8000

## Stack Trace

0 - app/Filament/Widgets/MemberStatusChart.php:39
1 - vendor/composer/ClassLoader.php:427
2 - vendor/filament/filament/src/Panel/Concerns/HasComponents.php:518
3 - vendor/filament/filament/src/Panel/Concerns/HasComponents.php:382
4 - app/Providers/Filament/AdminPanelProvider.php:49
5 - vendor/filament/filament/src/PanelProvider.php:15
6 - vendor/laravel/framework/src/Illuminate/Collections/helpers.php:266
7 - vendor/filament/filament/src/Facades/Filament.php:192
8 - vendor/laravel/framework/src/Illuminate/Container/Container.php:1623
9 - vendor/laravel/framework/src/Illuminate/Container/Container.php:1543
10 - vendor/laravel/framework/src/Illuminate/Container/Container.php:954
11 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1078
12 - vendor/laravel/framework/src/Illuminate/Container/Container.php:864
13 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1058
14 - vendor/laravel/framework/src/Illuminate/Foundation/helpers.php:138
15 - vendor/filament/filament/src/FilamentManager.php:382
16 - vendor/laravel/framework/src/Illuminate/Support/Facades/Facade.php:363
17 - vendor/filament/filament/routes/web.php:17
18 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:524
19 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:480
20 - vendor/laravel/framework/src/Illuminate/Routing/RouteRegistrar.php:212
21 - vendor/filament/filament/routes/web.php:16
22 - vendor/laravel/framework/src/Illuminate/Support/ServiceProvider.php:201
23 - vendor/spatie/laravel-package-tools/src/Concerns/PackageServiceProvider/ProcessRoutes.php:14
24 - vendor/spatie/laravel-package-tools/src/PackageServiceProvider.php:85
25 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:36
26 - vendor/laravel/framework/src/Illuminate/Container/Util.php:43
27 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:96
28 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:35
29 - vendor/laravel/framework/src/Illuminate/Container/Container.php:799
30 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1151
31 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1132
32 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1131
33 - vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/BootProviders.php:17
34 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:342
35 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:186
36 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:170
37 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
38 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
39 - public/index.php:20
40 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23


## Request

GET /admin

## Headers

* **host**: localhost:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
* **sec-ch-ua**: "Google Chrome";v="147", "Not.A/Brand";v="8", "Chromium";v="147"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "macOS"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://localhost:8000/admin/members
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: uk,de;q=0.9,en;q=0.8
* **cookie**: wordpress_test_cookie=WP%20Cookie%20check; wordpress_logged_in_37d007a56d816107ce5b52c10342db37=Munas-Admin%7C1778069275%7CkJWyvWPYgU9ssE110H37XCinboFtBupOEPuCujSEjcg%7C7e31b45430f290e41fc86838f33e6c44ae4fbf8405dae5f5c9779087714b02d7; wp-settings-time-1=1776955492; wp-settings-1=libraryContent%3Dbrowse%26posts_list_mode%3Dlist%26editor%3Dtinymce; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IjY1eElFY3I5Q3hjb1VoWEYvSE1LSUE9PSIsInZhbHVlIjoibnMwOXdldEFpK1pHejZCRnJVeXF3bFF6aHV5WUEvT1VBNFZBWFBiSk0xcE1LcGZJRjhrekYrRzJMVC8yRGlHcW9ad3h2dkN5QU45dTlLWEtGSTRJSkhPdkdDUHFCTU53Vm1nTDJFbEVLbno2cWplNFlrUDNDNlZ5TTVpVEM4azBOWUMrQWxEM05mRU1ONUsxUDBYaUdTUmpJVitMRnFocTBaMFdOOVhLUzJhbWVNTDJMdnVWT1lJUmlsNjV1Z1ZvVFR2bGF4d1hzbTYzbWhUVjQ3bnJ3SXA3WFZLTnVuVzRndk50UFhGNCs3RT0iLCJtYWMiOiJkM2Y3N2IyYWEzYmJiODNiYTc5MGE1MzkyMzUwODg1MzIyYmVkZWQ5YmFkMDkyOGQxOWQ1OTliNTBhZjZmNjkxIiwidGFnIjoiIn0%3D; XSRF-TOKEN=eyJpdiI6Ii9aTTRzWnRGZ09qdE1SeFNyMEUvQkE9PSIsInZhbHVlIjoiVDNRNmdLZVFRakV2OG5tMjJsQUFRK2xidmZGZ2NIenZjQzlVWUtRRWxlUmVLa3dSZWFtQ2ZCNUx2Q015UFNWb2haaFA4aE00V1U4bW52MnJRdFhwUDFMdktnbFkzbVlWSVcybm1VdEpiaHdMYS92MkVUS21yaEIvUld3YVNySkIiLCJtYWMiOiI4OGU5NGE0OGMzYjNlMzMwZDg1YmM4MWJmNDEzZWNlZGY2NjI0ODNmN2FhNGY2ZDAwMThmZDVmMzMyMmE0NjgwIiwidGFnIjoiIn0%3D; ditib-ahlen-session=eyJpdiI6IkcwTXBCMGUrK1hHSWwvU3RUQXpBV2c9PSIsInZhbHVlIjoia3hYVFgrRWgxcWlLRGxVM0pmQ29sdS9PbmRYQlFuODZHRjEremIwcGdtQ0M0L3kzdERmODR4L1V1NGJocEY0Q1JEaWRGcGdERXNJUnQvNm1hK0lST3pxbmF3b0Y5V3NtZE9BUkJFWXU2ZVpocjZrTDJ5VDhuQzlqdjdwSjFQNjQiLCJtYWMiOiIxMmI5YjhhNDJkNTg2NDczNWZkYzY5MmJjOGUyNDFjNTQ1MjFlMTNjYTI3ZmExY2EwNjU1ZTcyYjcwNzBjMDA2IiwidGFnIjoiIn0%3D

## Route Context

No routing data available.

## Route Parameters

No route parameter data available.

## Database Queries

No database queries detected.
