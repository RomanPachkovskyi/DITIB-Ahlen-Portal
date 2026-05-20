<?php

namespace App\Filament\Member\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        $this->redirect(MemberAccountResource::getUrl(panel: 'member'));
    }
}
