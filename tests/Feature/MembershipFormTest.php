<?php

namespace Tests\Feature;

use App\Events\MemberRegistered;
use App\Livewire\MembershipForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class MembershipFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_submits_cash_payment_when_dsgvo_is_accepted(): void
    {
        Event::fake([MemberRegistered::class]);

        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Max Mustermann')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 1')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'max@example.com')
            ->set('phone', '+49 2382 123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('members', [
            'full_name' => 'Max Mustermann',
            'zahlungsart' => 'barzahlung',
            'dsgvo_zustimmung' => true,
        ]);

        Event::assertDispatched(MemberRegistered::class);
    }
}
