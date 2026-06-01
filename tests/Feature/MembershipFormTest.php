<?php

namespace Tests\Feature;

use App\Events\MemberRegistered;
use App\Livewire\MembershipForm;
use App\Mail\MemberRegistrationConfirmation;
use App\Mail\NewMemberNotification;
use App\Models\Member;
use App\Support\Iban;
use App\Support\Instagram;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
            '61599' => ['+49 2382 61599', '+49238261599'],
            '2382' => ['+49 2382 2382', '+4923822382'],
            '+90 532 123 45 67' => ['+90 532 123 4567', '+905321234567'],
        ];

        foreach ($cases as $input => [$formatted, $normalized]) {
            $this->assertSame($formatted, PhoneNumber::format($input));
            $this->assertSame($normalized, PhoneNumber::normalize($input));
            $this->assertTrue(PhoneNumber::isValid($input));
        }
    }

    public function test_it_normalizes_instagram_input(): void
    {
        $cases = [
            'ditibahlen' => 'ditibahlen',
            '@ditibahlen' => 'ditibahlen',
            'https://www.instagram.com/ditibahlen/' => 'ditibahlen',
            'instagram.com/ditibahlen?igsh=test' => 'ditibahlen',
        ];

        foreach ($cases as $input => $normalized) {
            $this->assertSame($normalized, Instagram::normalize($input));
            $this->assertSame('@'.$normalized, Instagram::display($input));
            $this->assertTrue(Instagram::isValid($input));
        }

        $this->assertNull(Instagram::normalize('https://example.com/ditibahlen'));
        $this->assertFalse(Instagram::isValid('https://example.com/ditibahlen'));
        $this->assertNull(Instagram::normalize('https://fakeinstagram.com/ditibahlen'));
        $this->assertFalse(Instagram::isValid('https://fakeinstagram.com/ditibahlen'));
    }

    public function test_it_rejects_invalid_phone_input(): void
    {
        foreach (['abc', '123', '+49', '492382123456'] as $input) {
            $this->assertFalse(PhoneNumber::isValid($input));
        }
    }

    public function test_bank_transfer_payment_is_selected_by_default(): void
    {
        Livewire::test(MembershipForm::class)
            ->assertSet('zahlungsart', 'dauerauftrag');
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

    public function test_it_rejects_invalid_instagram_input(): void
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
            ->set('instagram', 'https://example.com/ditibahlen')
            ->call('nextStep')
            ->assertHasErrors(['instagram'])
            ->assertSet('instagram', 'https://example.com/ditibahlen');
    }

    public function test_it_formats_valid_instagram_input_on_step_validation(): void
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
            ->set('instagram', '@ditibahlen')
            ->call('nextStep')
            ->assertHasNoErrors(['instagram'])
            ->assertSet('instagram', 'ditibahlen');
    }

    public function test_it_allows_moving_forward_while_showing_step_errors(): void
    {
        Livewire::test(MembershipForm::class)
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertSet('showValidationSummary', true)
            ->assertSet('stepsWithErrors.1', true)
            ->assertSee('Bitte prüfen Sie die markierten Angaben.')
            ->assertHasErrors(['anrede', 'full_name', 'birth_date']);
    }

    public function test_validation_summary_disappears_after_previous_step_is_fixed(): void
    {
        Livewire::test(MembershipForm::class)
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertSee('Bitte prüfen Sie die markierten Angaben.')
            ->call('prevStep')
            ->set('anrede', 'Herr')
            ->set('full_name', 'Max Mustermann')
            ->set('birth_date', '1990-01-01')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertSet('stepsWithErrors', [])
            ->assertSet('showValidationSummary', false)
            ->assertDontSee('Bitte prüfen Sie die markierten Angaben.');
    }

    public function test_closing_plz_dropdown_does_not_clear_address_validation_errors(): void
    {
        Livewire::test(MembershipForm::class)
            ->set('step', 2)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Max Mustermann')
            ->set('birth_date', '1990-01-01')
            ->call('submit')
            ->assertSet('step', 2)
            ->assertHasErrors(['postal_code', 'city', 'state'])
            ->call('closePlzDropdown')
            ->assertHasErrors(['postal_code', 'city', 'state'])
            ->assertSee('Bitte geben Sie Ihre Postleitzahl ein.')
            ->assertSee('Bitte geben Sie Ihren Ort ein.')
            ->assertSee('Bitte geben Sie das Bundesland ein.');
    }

    public function test_submit_returns_to_first_step_with_errors(): void
    {
        Livewire::test(MembershipForm::class)
            ->set('step', 3)
            ->set('monatsbeitrag', 10)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertSet('step', 1)
            ->assertSet('showValidationSummary', true)
            ->assertSet('submitted', false)
            ->assertSet('stepsWithErrors.1', true)
            ->assertDontSee('Bitte prüfen Sie die markierten Angaben.')
            ->assertHasErrors(['anrede', 'full_name', 'birth_date']);
    }

    public function test_it_does_not_enter_photo_step_when_registration_is_incomplete(): void
    {
        Livewire::test(MembershipForm::class)
            ->set('step', 3)
            ->set('monatsbeitrag', 10)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('nextStep')
            ->assertSet('step', 1)
            ->assertSet('showValidationSummary', true)
            ->assertSet('stepsWithErrors.1', true)
            ->assertHasErrors(['anrede', 'full_name', 'birth_date']);
    }

    public function test_it_blocks_duplicate_registration_before_photo_step(): void
    {
        $this->createMember([
            'birth_date' => '1990-01-01',
            'phone' => '+492382123456',
        ]);

        Livewire::test(MembershipForm::class)
            ->set('step', 3)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Andere Schreibweise')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 1')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'andere@example.com')
            ->set('phone', '02382/123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertSet('showValidationSummary', true)
            ->assertSet('stepsWithErrors.2', true)
            ->assertHasErrors(['phone']);
    }

    public function test_it_blocks_duplicate_registration_on_submit(): void
    {
        Event::fake([MemberRegistered::class]);

        $this->createMember([
            'birth_date' => '1990-01-01',
            'phone' => '+492382123456',
        ]);

        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Andere Schreibweise')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 1')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'andere@example.com')
            ->set('phone', '02382/123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertSet('step', 2)
            ->assertSet('submitted', false)
            ->assertHasErrors(['phone']);

        $this->assertDatabaseCount('members', 1);
        Event::assertNotDispatched(MemberRegistered::class);
    }

    public function test_it_keeps_too_short_phone_input_visible_for_validation(): void
    {
        $this->assertSame('123', PhoneNumber::format('123'));
        $this->assertSame('', PhoneNumber::normalize('123'));
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
            ->set('instagram', 'https://www.instagram.com/ditibahlen/')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('members', [
            'full_name' => 'Max Mustermann',
            'phone' => '+492382123456',
            'instagram' => 'ditibahlen',
            'profile_photo_path' => null,
            'profile_photo_zustimmung' => false,
            'profile_photo_zustimmung_at' => null,
            'zahlungsart' => 'barzahlung',
            'dsgvo_zustimmung' => true,
        ]);

        Event::assertDispatched(MemberRegistered::class);
    }

    public function test_it_submits_registration_with_optional_profile_photo(): void
    {
        Event::fake([MemberRegistered::class]);
        Storage::fake('member_photos');

        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Foto Nutzer')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 1')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'foto@example.com')
            ->set('phone', '02382/123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->set('croppedPhoto', UploadedFile::fake()->image('profile-photo.jpg', 800, 800)->size(180))
            ->call('acceptCroppedPhoto')
            ->assertHasNoErrors(['croppedPhoto'])
            ->assertSet('photoResult.width', 800)
            ->assertSet('photoResult.height', 800)
            ->set('profile_photo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $member = Member::where('email', 'foto@example.com')->firstOrFail();

        $this->assertNotNull($member->profile_photo_path);
        $this->assertNotNull($member->profile_photo_uploaded_at);
        $this->assertTrue($member->profile_photo_zustimmung);
        $this->assertNotNull($member->profile_photo_zustimmung_at);
        $this->assertSame("member-photos/{$member->member_number}/{$member->member_number}-profile.jpg", $member->profile_photo_path);
        Storage::disk('member_photos')->assertExists($member->profile_photo_path);

        $imageSize = getimagesize(Storage::disk('member_photos')->path($member->profile_photo_path));

        $this->assertSame(800, $imageSize[0]);
        $this->assertSame(800, $imageSize[1]);
        $this->assertSame('image/jpeg', $imageSize['mime']);

        Event::assertDispatched(MemberRegistered::class);
    }

    public function test_it_requires_separate_consent_when_profile_photo_is_uploaded(): void
    {
        Event::fake([MemberRegistered::class]);
        Storage::fake('member_photos');

        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Foto Ohne Zustimmung')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 1')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'foto-ohne-zustimmung@example.com')
            ->set('phone', '02382/123456')
            ->set('monatsbeitrag', 25)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->set('croppedPhoto', UploadedFile::fake()->image('profile-photo.jpg', 800, 800)->size(180))
            ->call('acceptCroppedPhoto')
            ->call('submit')
            ->assertSet('step', 4)
            ->assertHasErrors(['profile_photo_zustimmung']);

        $this->assertDatabaseMissing('members', [
            'email' => 'foto-ohne-zustimmung@example.com',
        ]);

        Event::assertNotDispatched(MemberRegistered::class);
    }

    public function test_it_submits_standing_order_without_sepa_mandate_or_bank_details(): void
    {
        Event::fake([MemberRegistered::class]);

        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Dauerauftrag Nutzer')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 4')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'dauerauftrag@example.com')
            ->set('phone', '02382/123456')
            ->set('monatsbeitrag', 10)
            ->set('zahlungsart', 'dauerauftrag')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('members', [
            'email' => 'dauerauftrag@example.com',
            'zahlungsart' => 'dauerauftrag',
            'kontoinhaber' => null,
            'iban' => null,
            'sepa_zustimmung' => false,
            'dsgvo_zustimmung' => true,
        ]);

        Event::assertDispatched(MemberRegistered::class);
    }

    public function test_it_accepts_ten_euro_as_minimum_monthly_contribution(): void
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
            ->set('email', 'min@example.com')
            ->set('phone', '02382/123456')
            ->call('selectMonatsbeitrag', 10)
            ->assertSet('monatsbeitrag', 10.0)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('members', [
            'email' => 'min@example.com',
            'monatsbeitrag' => 10,
        ]);

        Event::assertDispatched(MemberRegistered::class);
    }

    public function test_it_rejects_monthly_contribution_below_ten_euro(): void
    {
        Livewire::test(MembershipForm::class)
            ->set('anrede', 'Herr')
            ->set('full_name', 'Max Mustermann')
            ->set('birth_date', '1990-01-01')
            ->set('familienangehoerige', 1)
            ->set('street', 'Musterstrasse 1')
            ->set('postal_code', '59227')
            ->set('city', 'Ahlen')
            ->set('state', 'Nordrhein-Westfalen')
            ->set('email', 'below-min@example.com')
            ->set('phone', '02382/123456')
            ->set('monatsbeitrag', 9.99)
            ->set('zahlungsart', 'barzahlung')
            ->set('dsgvo_zustimmung', true)
            ->call('submit')
            ->assertHasErrors(['monatsbeitrag']);
    }

    public function test_registration_page_links_to_member_account(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Zum Mitgliedskonto')
            ->assertSee(url('/konto'), false);
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

        $this->assertSame('DE42400501500068000959', Member::where('email', 'erika@example.com')->value('iban'));

        // SEPA mandate at registration must carry its own consent timestamp.
        $erika = Member::where('email', 'erika@example.com')->first();
        $this->assertTrue($erika->sepa_zustimmung);
        $this->assertNotNull($erika->sepa_zustimmung_at);

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

    private function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'anrede' => 'Herr',
            'full_name' => 'Max Mustermann',
            'street' => 'Musterstrasse 1',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'postal_code' => '59227',
            'birth_date' => '1990-01-01',
            'email' => 'max@example.com',
            'phone' => '+492382123456',
            'zahlungsart' => 'barzahlung',
            'monatsbeitrag' => 25,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
            'status' => 'pending',
        ], $overrides));
    }
}
