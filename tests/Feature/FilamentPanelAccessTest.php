<?php

namespace Tests\Feature;

use App\Filament\Widgets\MembersChart;
use App\Models\Member;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_allows_only_configured_admin_email(): void
    {
        $panel = (new Panel())->id('admin');

        $this->assertTrue((new User(['email' => 'rpachkovskyi@gmail.com']))->canAccessPanel($panel));
        $this->assertTrue((new User(['email' => 'info@ditib-ahlen-projekte.de']))->canAccessPanel($panel));
        $this->assertFalse((new User(['email' => 'member@example.com']))->canAccessPanel($panel));
    }

    public function test_member_panel_allows_authenticated_users(): void
    {
        $panel = (new Panel())->id('member');

        $this->assertTrue((new User(['email' => 'member@example.com']))->canAccessPanel($panel));
    }

    public function test_members_chart_counts_registrations_without_database_specific_sql(): void
    {
        $januaryMember = Member::create([
            'anrede' => 'Herr',
            'full_name' => 'Januar Mitglied',
            'street' => 'Musterstrasse 1',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'postal_code' => '59227',
            'birth_date' => '1990-01-01',
            'email' => 'januar@example.com',
            'phone' => '+492382123456',
            'zahlungsart' => 'barzahlung',
            'monatsbeitrag' => 25,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
        ]);
        $januaryMember->forceFill(['created_at' => '2026-01-10 12:00:00'])->saveQuietly();

        $mayMember = Member::create([
            'anrede' => 'Frau',
            'full_name' => 'Mai Mitglied',
            'street' => 'Musterstrasse 2',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'postal_code' => '59227',
            'birth_date' => '1991-01-01',
            'email' => 'mai@example.com',
            'phone' => '+492382654321',
            'zahlungsart' => 'barzahlung',
            'monatsbeitrag' => 25,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
        ]);
        $mayMember->forceFill(['created_at' => '2026-05-10 12:00:00'])->saveQuietly();

        $widget = new MembersChart();
        $data = (fn () => $this->getData())->call($widget);

        $this->assertSame(1, $data['datasets'][0]['data'][0]);
        $this->assertSame(1, $data['datasets'][0]['data'][4]);
        $this->assertSame(0, $data['datasets'][0]['data'][1]);
    }
}
