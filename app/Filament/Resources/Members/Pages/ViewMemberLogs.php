<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

class ViewMemberLogs extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MemberResource::class;

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

    public function getTitle(): string|Htmlable
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
}
