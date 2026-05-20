<?php

namespace Tests\Feature;

use App\Filament\Resources\Members\Pages\EditMember;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfilePhotoActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_profile_photo_from_edit_page_action(): void
    {
        Storage::fake('member_photos');
        $this->actingAsAdmin();

        $member = $this->makeMember();

        Livewire::test(EditMember::class, [
            'record' => $member->getRouteKey(),
        ])
            ->callAction('uploadProfilePhoto', [
                'profile_photo' => UploadedFile::fake()->image('admin-photo.png', 1200, 900)->size(600),
            ])
            ->assertHasNoActionErrors();

        $member->refresh();

        $this->assertSame("member-photos/{$member->member_number}/{$member->member_number}-profile.jpg", $member->profile_photo_path);
        $this->assertNotNull($member->profile_photo_uploaded_at);
        Storage::disk('member_photos')->assertExists($member->profile_photo_path);
    }

    public function test_admin_can_delete_profile_photo_from_edit_page_action(): void
    {
        Storage::fake('member_photos');
        $this->actingAsAdmin();

        $member = $this->makeMember();
        $path = "member-photos/{$member->member_number}/{$member->member_number}-profile.jpg";

        Storage::disk('member_photos')->put($path, 'jpeg-content');
        $member->forceFill([
            'profile_photo_path' => $path,
            'profile_photo_uploaded_at' => now(),
            'profile_photo_zustimmung' => true,
            'profile_photo_zustimmung_at' => now(),
        ])->save();

        Livewire::test(EditMember::class, [
            'record' => $member->getRouteKey(),
        ])
            ->callAction('deleteProfilePhoto')
            ->assertHasNoActionErrors();

        Storage::disk('member_photos')->assertMissing($path);

        $member->refresh();
        $this->assertNull($member->profile_photo_path);
        $this->assertNull($member->profile_photo_uploaded_at);
        $this->assertFalse($member->profile_photo_zustimmung);
        $this->assertNull($member->profile_photo_zustimmung_at);
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
        ]);
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
