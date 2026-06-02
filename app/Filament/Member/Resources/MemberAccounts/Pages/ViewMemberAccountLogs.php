<?php

namespace App\Filament\Member\Resources\MemberAccounts\Pages;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class ViewMemberAccountLogs extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MemberAccountResource::class;

    protected string $view = 'filament.members.audit-logs-page';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    public function getBreadcrumb(): string
    {
        return 'Logs';
    }

    public function getTitle(): string
    {
        return $this->record->full_name.' - Logs';
    }

    public function getLogs(): Collection
    {
        return $this->record
            ->auditLogs()
            ->latest('created_at')
            ->latest('id')
            ->get();
    }

    public function getEmailLogs(): Collection
    {
        return $this->record
            ->emailLogs()
            ->latest('created_at')
            ->latest('id')
            ->get();
    }
}
