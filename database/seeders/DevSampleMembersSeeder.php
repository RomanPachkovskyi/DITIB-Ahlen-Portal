<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Services\MemberAuditLogger;
use App\Services\ProfilePhotoService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Local-only sample members for testing /konto and /admin.
 * Run with: php artisan db:seed --class=DevSampleMembersSeeder
 */
class DevSampleMembersSeeder extends Seeder
{
    public function run(ProfilePhotoService $photos): void
    {
        $shared = 'roman.2271670@gmail.com';

        // 3 records on the shared email, one of them inactive.
        $m1 = $this->makeMember([
            'full_name' => 'Roman Aktiv',
            'email' => $shared,
            'status' => 'active',
            'zahlungsart' => 'lastschrift',
            'kontoinhaber' => 'Roman Pachkovskyi',
            'iban' => 'DE89370400440532013000',
            'bic' => 'COBADEFFXXX',
            'kreditinstitut' => 'Commerzbank',
            'sepa_zustimmung' => true,
            'sepa_zustimmung_at' => now(),
            'zustimmung_at' => now(),
        ]);
        $this->attachPhoto($photos, $m1, '009689');

        $m2 = $this->makeMember([
            'full_name' => 'Roman Verarbeitung',
            'email' => $shared,
            'status' => 'processing',
            'zahlungsart' => 'barzahlung',
            'zustimmung_at' => now(),
        ]); // no photo

        $m3 = $this->makeMember([
            'full_name' => 'Roman Inaktiv',
            'email' => $shared,
            'status' => 'inactive',
            'zahlungsart' => 'dauerauftrag',
            'zustimmung_at' => now(),
        ]); // no photo

        // 2 more on separate emails.
        $m4 = $this->makeMember([
            'full_name' => 'Ayse Yilmaz',
            'email' => 'ayse.test@example.com',
            'anrede' => 'Frau',
            'status' => 'active',
            'zahlungsart' => 'lastschrift',
            'kontoinhaber' => 'Ayse Yilmaz',
            'iban' => 'DE02120300000000202051',
            'bic' => 'BYLADEM1001',
            'kreditinstitut' => 'Deutsche Kreditbank',
            'sepa_zustimmung' => true,
            'sepa_zustimmung_at' => now(),
            'zustimmung_at' => now(),
        ]);
        $this->attachPhoto($photos, $m4, 'b91c1c');

        $m5 = $this->makeMember([
            'full_name' => 'Mehmet Demir',
            'email' => 'mehmet.test@example.com',
            'status' => 'pending',
            'zahlungsart' => 'barzahlung',
            'zustimmung_at' => now(),
        ]); // no photo

        $this->command?->info('Seeded 5 sample members (3 on '.$shared.', 1 inactive; 2 with photo).');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeMember(array $attributes): Member
    {
        $member = Member::create(array_merge([
            'anrede' => 'Herr',
            'birth_date' => '1985-06-15',
            'staatsangehoerigkeit' => 'deutsch',
            'familienangehoerige' => 1,
            'street' => 'Rottmannstr. 62',
            'postal_code' => '59229',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'phone' => '+492382123456',
            'monatsbeitrag' => 15,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
        ], $attributes));

        // Mirror the public registration: first timeline entry "Account erstellt".
        app(MemberAuditLogger::class)->created($member, 'client');

        return $member;
    }

    private function attachPhoto(ProfilePhotoService $photos, Member $member, string $hex): void
    {
        $photos->store(
            $member,
            UploadedFile::fake()->image('photo.jpg', 800, 800),
        );
        $member->refresh();
    }
}
