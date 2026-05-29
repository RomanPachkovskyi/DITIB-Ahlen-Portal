<?php

namespace Tests\Feature;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Models\Member;
use App\Models\User;
use App\Services\ProfilePhotoService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberPanelAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_panel_query_shows_all_members_with_authenticated_email_only(): void
    {
        $user = $this->actingAsMemberUser('family@example.com');

        $first = $this->makeMember(['email' => 'family@example.com', 'full_name' => 'First Family']);
        $second = $this->makeMember(['email' => 'FAMILY@example.com', 'full_name' => 'Second Family']);
        $other = $this->makeMember(['email' => 'other@example.com', 'full_name' => 'Other Person']);

        $ids = MemberAccountResource::getEloquentQuery()
            ->pluck('id')
            ->all();

        $this->assertContains($first->id, $ids);
        $this->assertContains($second->id, $ids);
        $this->assertNotContains($other->id, $ids);
        $this->assertSame('family@example.com', $user->email);
    }

    public function test_member_panel_list_renders_all_members_with_same_email(): void
    {
        $this->actingAsMemberUser('family@example.com');

        $first = $this->makeMember(['email' => 'family@example.com', 'full_name' => 'First Family']);
        $second = $this->makeMember(['email' => 'family@example.com', 'full_name' => 'Second Family']);
        $other = $this->makeMember(['email' => 'other@example.com', 'full_name' => 'Other Person']);

        $this
            ->get(MemberAccountResource::getUrl(panel: 'member'))
            ->assertOk()
            ->assertSee($first->member_number)
            ->assertSee($second->member_number)
            ->assertSee('First Family')
            ->assertSee('Second Family')
            ->assertDontSee($other->member_number)
            ->assertDontSee('Other Person')
            // Default columns mirror the admin list.
            ->assertSee('E-Mail')
            ->assertSee('Beitrag/Mo.')
            ->assertSee('Eingegangen am');
    }

    public function test_member_panel_home_redirects_to_memberships_list(): void
    {
        $this->actingAsMemberUser('family@example.com');

        $this->makeMember(['email' => 'family@example.com', 'full_name' => 'First Family']);

        $this
            ->get('/konto')
            ->assertRedirect(MemberAccountResource::getUrl(panel: 'member'));
    }

    public function test_member_panel_cannot_open_member_with_different_email(): void
    {
        $this->actingAsMemberUser('family@example.com');

        $other = $this->makeMember(['email' => 'other@example.com', 'full_name' => 'Other Person']);

        $this
            ->get(MemberAccountResource::getUrl('view', ['record' => $other], panel: 'member'))
            ->assertNotFound();
    }

    public function test_member_panel_view_renders_profile_photo_when_available(): void
    {
        Storage::fake('member_photos');
        $this->actingAsMemberUser('family@example.com');

        $member = $this->makeMember(['email' => 'family@example.com', 'full_name' => 'First Family']);

        app(ProfilePhotoService::class)->store(
            $member,
            UploadedFile::fake()->image('profile.jpg', 900, 900)->size(600),
        );

        $member->refresh();

        $photoUrl = route('members.profile-photo', [
            'member' => $member,
            'v' => $member->profile_photo_uploaded_at?->getTimestamp(),
        ]);

        $this
            ->get(MemberAccountResource::getUrl('view', ['record' => $member], panel: 'member'))
            ->assertOk()
            ->assertSee($member->member_number)
            ->assertSee($photoUrl, false);
    }

    public function test_member_panel_view_without_photo_has_no_photo_slot(): void
    {
        $this->actingAsMemberUser('family@example.com');

        $member = $this->makeMember(['email' => 'family@example.com', 'full_name' => 'First Family']);

        $this
            ->get(MemberAccountResource::getUrl('view', ['record' => $member], panel: 'member'))
            ->assertOk()
            ->assertDontSee('ditib-profile-photo-preview')
            ->assertDontSee('Kein Foto');
    }

    private function actingAsMemberUser(string $email): User
    {
        Filament::setCurrentPanel(Filament::getPanel('member'));

        $user = User::create([
            'name' => 'Member User',
            'email' => $email,
            'password' => 'secret',
        ]);

        $this->actingAs($user);

        return $user;
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
