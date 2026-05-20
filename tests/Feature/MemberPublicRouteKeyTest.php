<?php

namespace Tests\Feature;

use App\Filament\Member\Resources\MemberAccounts\MemberAccountResource;
use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Models\User;
use App\Services\ProfilePhotoService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberPublicRouteKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_route_key_is_member_number(): void
    {
        $member = $this->makeMember();

        $this->assertSame('member_number', $member->getRouteKeyName());
        $this->assertSame($member->member_number, $member->getRouteKey());
        $this->assertNotSame((string) $member->id, $member->getRouteKey());
    }

    public function test_admin_member_view_uses_member_number_in_url(): void
    {
        $this->actingAsAdmin();
        $member = $this->makeMember();

        $url = MemberResource::getUrl('view', ['record' => $member]);

        $this->assertStringContainsString('/admin/members/'.$member->member_number, $url);
        $this->assertStringNotContainsString('/admin/members/'.$member->id, $url);

        $this->get($url)->assertOk();
        $this->get('/admin/members/'.$member->id)->assertNotFound();
    }

    public function test_member_panel_view_uses_member_number_in_url(): void
    {
        $member = $this->makeMember(['email' => 'member@example.com']);
        $this->actingAsMemberUser('member@example.com');

        $url = MemberAccountResource::getUrl('view', ['record' => $member], panel: 'member');

        $this->assertStringContainsString('/konto/mitgliedschaften/'.$member->member_number, $url);
        $this->assertStringNotContainsString('/konto/mitgliedschaften/'.$member->id, $url);

        $this->get($url)->assertOk();
        $this->get('/konto/mitgliedschaften/'.$member->id)->assertNotFound();
    }

    public function test_profile_photo_route_uses_member_number_binding(): void
    {
        Storage::fake('member_photos');

        $member = $this->makeMember(['email' => 'member@example.com']);
        app(ProfilePhotoService::class)->store(
            $member,
            UploadedFile::fake()->image('profile.jpg', 900, 900)->size(600),
        );
        $member->refresh();

        $this->actingAsMemberUser('member@example.com');

        $url = route('members.profile-photo', $member);

        $this->assertStringContainsString('/members/'.$member->member_number.'/profile-photo', $url);
        $this->assertStringNotContainsString('/members/'.$member->id.'/profile-photo', $url);

        $this->get($url)->assertOk();
        $this->get('/members/'.$member->id.'/profile-photo')->assertNotFound();
    }

    private function actingAsAdmin(): User
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'info@ditib-ahlen-projekte.de',
            'password' => 'secret',
        ]);

        $this->actingAs($admin);

        return $admin;
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
