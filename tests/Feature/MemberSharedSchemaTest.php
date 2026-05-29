<?php

namespace Tests\Feature;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberSharedSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_view_shows_full_card_with_status_but_hides_internal_note(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('member'));
        $this->actingAs(User::create([
            'name' => 'Member',
            'email' => 'family@example.com',
            'password' => 'secret',
        ]));

        $member = $this->makeMember(['email' => 'family@example.com']);

        $this->get(MemberAccountResource::getUrl('view', ['record' => $member], panel: 'member'))
            ->assertOk()
            ->assertSee('Beitrag & Bankverbindung')
            ->assertSee('Status')
            ->assertDontSee('Interne Notiz')
            // No photo → no photo-consent fields.
            ->assertDontSee('Foto-Einwilligung');
    }

    public function test_photo_consent_fields_appear_only_when_a_photo_exists(): void
    {
        \Illuminate\Support\Facades\Storage::fake('member_photos');
        Filament::setCurrentPanel(Filament::getPanel('member'));
        $this->actingAs(User::create([
            'name' => 'Member',
            'email' => 'family@example.com',
            'password' => 'secret',
        ]));

        $member = $this->makeMember(['email' => 'family@example.com']);

        app(\App\Services\ProfilePhotoService::class)->store(
            $member,
            \Illuminate\Http\UploadedFile::fake()->image('p.jpg', 900, 900)->size(400),
        );
        $member->refresh();

        $this->get(MemberAccountResource::getUrl('view', ['record' => $member], panel: 'member'))
            ->assertOk()
            ->assertSee('Foto-Einwilligung');
    }

    public function test_admin_edit_still_shows_internal_note_and_status(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::create([
            'name' => 'Admin',
            'email' => 'rpachkovskyi@gmail.com',
            'password' => 'secret',
        ]));

        $member = $this->makeMember(['email' => 'someone@example.com']);

        $this->get(MemberResource::getUrl('edit', ['record' => $member], panel: 'admin'))
            ->assertOk()
            ->assertSee('Interne Notiz')
            ->assertSee('Status')
            // No photo → admin also does not see Foto-Einwilligung.
            ->assertDontSee('Foto-Einwilligung');
    }

    private function makeMember(array $attributes = []): Member
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
        ], $attributes));
    }
}
