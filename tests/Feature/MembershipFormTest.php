<?php

namespace Tests\Feature;

use App\Events\MemberRegistered;
use App\Livewire\MembershipForm;
use App\Support\Iban;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class MembershipFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_formats_and_normalizes_iban_input(): void
    {
        foreach ([
            'de42 4005 0150 0068 0009 59',
            'de42400501500068000959',
            'de42 4005015000680009 59',
        ] as $input) {
            $this->assertSame('DE 42 4005 0150 0068 0009 59', Iban::format($input));
            $this->assertSame('DE42400501500068000959', Iban::normalize($input));
            $this->assertTrue(Iban::isValidStructure($input));
        }
    }

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

    public function test_it_submits_direct_debit_with_messy_iban_input(): void
    {
        Event::fake([MemberRegistered::class]);

        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Frau')
            ->set('full_name', 'Erika Mustermann')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 2')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'erika@example.com')
            ->set('phone', '+49 2382 123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'lastschrift')
            ->set('kontoinhaber', 'Erika Mustermann')
            ->set('iban', 'de42 4005015000680009 59')
            ->set('sepa_zustimmung', true)
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('iban', 'DE 42 4005 0150 0068 0009 59')
            ->assertSet('submitted', true);

        $this->assertSame('DE42400501500068000959', \App\Models\Member::where('email', 'erika@example.com')->value('iban'));

        Event::assertDispatched(MemberRegistered::class);
    }
}
