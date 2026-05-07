<?php

namespace Tests\Feature;

use App\Events\MemberRegistered;
use App\Livewire\MembershipForm;
use App\Mail\MemberRegistrationConfirmation;
use App\Mail\NewMemberNotification;
use App\Support\Iban;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
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

    public function test_it_formats_and_normalizes_phone_input(): void
    {
        $cases = [
            '0176 12345678' => ['+49 176 1234 5678', '+4917612345678'],
            '176 12345678' => ['+49 176 1234 5678', '+4917612345678'],
            '0049 (2382) 123-456' => ['+49 2382 123456', '+492382123456'],
            '02382/123456' => ['+49 2382 123456', '+492382123456'],
            '2382 123456' => ['+49 2382 123456', '+492382123456'],
            '+90 532 123 45 67' => ['+90 532 123 4567', '+905321234567'],
        ];

        foreach ($cases as $input => [$formatted, $normalized]) {
            $this->assertSame($formatted, PhoneNumber::format($input));
            $this->assertSame($normalized, PhoneNumber::normalize($input));
            $this->assertTrue(PhoneNumber::isValid($input));
        }
    }

    public function test_it_rejects_invalid_phone_input(): void
    {
        foreach (['abc', '123', '+49', '492382123456'] as $input) {
            $this->assertFalse(PhoneNumber::isValid($input));
        }
    }

    public function test_it_accepts_phone_number_with_area_code_without_leading_zero(): void
    {
        Livewire::test(MembershipForm::class)
            ->set('step', 2)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Max Mustermann')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 1')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'max@example.com')
            ->set('phone', '2382 123456')
            ->call('nextStep')
            ->assertHasNoErrors(['phone'])
            ->assertSet('phone', '+49 2382 123456');
    }

    public function test_it_keeps_incomplete_phone_input_visible_for_validation(): void
    {
        $this->assertSame('123456', PhoneNumber::format('123456'));
        $this->assertSame('', PhoneNumber::normalize('123456'));
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
            ->set('phone', '02382/123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('members', [
            'full_name' => 'Max Mustermann',
            'phone' => '+492382123456',
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

    public function test_it_sends_registration_emails_without_queue_worker(): void
    {
        Mail::fake();

        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Ali Mustermann')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 3')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'ali@example.com')
            ->set('phone', '+49 2382 123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        Mail::assertSent(MemberRegistrationConfirmation::class, 1);
        Mail::assertSent(NewMemberNotification::class, 1);
        Mail::assertSent(MemberRegistrationConfirmation::class, fn (MemberRegistrationConfirmation $mail) => $mail->hasTo('ali@example.com'));
        Mail::assertSent(NewMemberNotification::class, fn (NewMemberNotification $mail) => $mail->hasTo('info@ditib-ahlen-projekte.de'));
        Mail::assertNothingQueued();
    }
}
