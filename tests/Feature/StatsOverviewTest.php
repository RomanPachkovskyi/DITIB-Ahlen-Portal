<?php

namespace Tests\Feature;

use App\Filament\Widgets\StatsOverview;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use ReflectionMethod;
use Tests\TestCase;

class StatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_revenue_counts_only_active_members(): void
    {
        Mail::fake();

        $this->createMember([
            'email' => 'active@example.com',
            'monatsbeitrag' => 35,
            'status' => 'active',
        ]);

        $this->createMember([
            'email' => 'pending@example.com',
            'monatsbeitrag' => 45,
            'status' => 'pending',
        ]);

        $this->createMember([
            'email' => 'inactive@example.com',
            'monatsbeitrag' => 55,
            'status' => 'inactive',
        ]);

        $stats = $this->stats();

        $this->assertSame('€ 35,00', $stats[2]->getValue());
        $this->assertSame('Nur aktive Mitglieder', $stats[2]->getDescription());
    }

    /**
     * @return array<int, \Filament\Widgets\StatsOverviewWidget\Stat>
     */
    private function stats(): array
    {
        $method = new ReflectionMethod(StatsOverview::class, 'getStats');
        $method->setAccessible(true);

        return $method->invoke(new StatsOverview());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'anrede' => 'Herr',
            'full_name' => 'Ali Mustermann',
            'street' => 'Musterstrasse 1',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'postal_code' => '59227',
            'birth_date' => '1990-01-01',
            'email' => 'ali@example.com',
            'phone' => '+492382123456',
            'zahlungsart' => 'barzahlung',
            'monatsbeitrag' => 25,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
            'status' => 'pending',
        ], $overrides));
    }
}
