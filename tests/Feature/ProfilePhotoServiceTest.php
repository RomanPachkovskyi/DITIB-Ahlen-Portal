<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use App\Services\ProfilePhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_normalized_private_jpeg_photo(): void
    {
        Storage::fake('member_photos');

        $member = $this->makeMember();
        $file = UploadedFile::fake()->image('portrait.png', 1200, 900)->size(600);

        $path = app(ProfilePhotoService::class)->store($member, $file);

        $this->assertSame("member-photos/{$member->member_number}/{$member->member_number}-profile.jpg", $path);
        Storage::disk('member_photos')->assertExists($path);

        $member->refresh();
        $this->assertSame($path, $member->profile_photo_path);
        $this->assertNotNull($member->profile_photo_uploaded_at);

        $imageSize = getimagesize(Storage::disk('member_photos')->path($path));

        $this->assertSame(800, $imageSize[0]);
        $this->assertSame(800, $imageSize[1]);
        $this->assertSame('image/jpeg', $imageSize['mime']);
    }

    public function test_it_replaces_existing_photo_without_changing_path(): void
    {
        Storage::fake('member_photos');

        $member = $this->makeMember();
        $service = app(ProfilePhotoService::class);

        $firstPath = $service->store($member, UploadedFile::fake()->image('first.png', 1200, 900)->size(600));
        $secondPath = $service->store($member->refresh(), UploadedFile::fake()->image('second.png', 900, 1200)->size(600));

        $this->assertSame($firstPath, $secondPath);
        Storage::disk('member_photos')->assertExists($secondPath);

        $member->refresh();
        $this->assertSame($secondPath, $member->profile_photo_path);
        $this->assertNotNull($member->profile_photo_uploaded_at);

        $imageSize = getimagesize(Storage::disk('member_photos')->path($secondPath));

        $this->assertSame(800, $imageSize[0]);
        $this->assertSame(800, $imageSize[1]);
        $this->assertSame('image/jpeg', $imageSize['mime']);
    }

    public function test_it_deletes_photo_without_deleting_member(): void
    {
        Storage::fake('member_photos');

        $member = $this->memberWithPhoto();
        $path = $member->profile_photo_path;

        app(ProfilePhotoService::class)->delete($member);

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

    public function test_soft_delete_does_not_delete_profile_photo(): void
    {
        Storage::fake('member_photos');

        $member = $this->memberWithPhoto();
        $path = $member->profile_photo_path;

        $member->delete();

        Storage::disk('member_photos')->assertExists($path);
        $this->assertSoftDeleted('members', [
            'id' => $member->id,
        ]);
    }

    public function test_guest_cannot_access_profile_photo_route(): void
    {
        Storage::fake('member_photos');

        $member = $this->memberWithPhoto();

        $this->get(route('members.profile-photo', $member))->assertForbidden();
    }

    public function test_admin_can_access_any_profile_photo(): void
    {
        Storage::fake('member_photos');

        $member = $this->memberWithPhoto(['email' => 'member@example.com']);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'info@ditib-ahlen-projekte.de',
            'password' => 'secret',
        ]);

        $this
            ->actingAs($admin)
            ->get(route('members.profile-photo', $member))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg')
            ->assertHeader('cache-control', 'max-age=0, no-store, private');
    }

    public function test_member_can_access_own_profile_photo_by_email(): void
    {
        Storage::fake('member_photos');

        $member = $this->memberWithPhoto(['email' => 'member@example.com']);
        $user = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => 'secret',
        ]);

        $this
            ->actingAs($user)
            ->get(route('members.profile-photo', $member))
            ->assertOk();
    }

    public function test_member_cannot_access_another_profile_photo(): void
    {
        Storage::fake('member_photos');

        $member = $this->memberWithPhoto(['email' => 'member@example.com']);
        $user = User::create([
            'name' => 'Other Member',
            'email' => 'other@example.com',
            'password' => 'secret',
        ]);

        $this
            ->actingAs($user)
            ->get(route('members.profile-photo', $member))
            ->assertForbidden();
    }

    private function memberWithPhoto(array $attributes = []): Member
    {
        $member = $this->makeMember($attributes);
        $path = "member-photos/{$member->member_number}/{$member->member_number}-profile.jpg";

        Storage::disk('member_photos')->put($path, 'jpeg-content');
        $member->forceFill([
            'profile_photo_path' => $path,
            'profile_photo_uploaded_at' => now(),
            'profile_photo_zustimmung' => true,
            'profile_photo_zustimmung_at' => now(),
        ])->save();

        return $member;
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
