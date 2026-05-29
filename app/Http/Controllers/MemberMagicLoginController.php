<?php

namespace App\Http\Controllers;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Services\MemberMagicLoginService;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MemberMagicLoginController extends Controller
{
    public function __invoke(string $token, MemberMagicLoginService $magicLogin): RedirectResponse
    {
        $user = $magicLogin->consume($token);

        if ($user === null) {
            return redirect()
                ->to(Filament::getPanel('member')->getLoginUrl())
                ->with('member_login_error', 'Dieser Zugangslink ist ungültig oder abgelaufen. Bitte fordern Sie einen neuen Link an.');
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->to(MemberAccountResource::getUrl(panel: 'member'));
    }
}
