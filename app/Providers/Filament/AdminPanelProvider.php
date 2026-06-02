<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\Login;
use App\Support\BrandColors;
use App\Http\Middleware\AdminPanelAuthenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => BrandColors::primary(),
            ])
            ->font('Albert Sans')
            ->brandLogo(asset('images/ditib_ahlen_logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('favicon.svg'))
            ->globalSearch(false)
            ->databaseNotifications()
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => view('filament.membership-form-link'),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn () => view('filament.footer'),
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_END,
                fn () => view('filament.auth-system-info'),
                \Filament\Auth\Pages\Login::class,
            )
            ->renderHook(
                PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER,
                fn () => view('filament.system-info'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.panel-style'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.admin-style'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                AdminPanelAuthenticate::class,
            ]);
    }
}
