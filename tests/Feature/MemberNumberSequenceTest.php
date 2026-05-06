<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberNumberSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MemberNumberSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_numbers_are_issued_sequentially(): void
    {
        Mail::fake();

        $first = $this->createMember(['email' => 'first@example.com']);
        $second = $this->createMember(['email' => 'second@example.com']);

        $this->assertSame('DA-' . now()->format('Y') . '-0001', $first->member_number);
        $this->assertSame('DA-' . now()->format('Y') . '-0002', $second->member_number);
    }

    public function test_soft_deleted_member_numbers_are_not_reused(): void
    {
        Mail::fake();

        $first = $this->createMember(['email' => 'deleted-number@example.com']);
        $first->delete();

        $next = $this->createMember(['email' => 'next-number@example.com']);

        $this->assertSoftDeleted($first);
        $this->assertSame('DA-' . now()->format('Y') . '-0001', $first->member_number);
        $this->assertSame('DA-' . now()->format('Y') . '-0002', $next->member_number);
    }

    public function test_allocator_skips_numbers_that_are_already_used_if_sequence_is_behind(): void
    {
        Mail::fake();

        $this->createMember([
            'email' => 'manual-number@example.com',
            'member_number' => 'DA-' . now()->format('Y') . '-0003',
        ]);

        MemberNumberSequence::query()
            ->where('name', 'members')
            ->update(['next_number' => 3]);

        $member = $this->createMember(['email' => 'skipped-number@example.com']);

        $this->assertSame('DA-' . now()->format('Y') . '-0004', $member->member_number);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'anrede' => 'Herr',
            'full_name' => 'Ali Mustermann',
            'street' => 'Musterstrasse 1',
            'city' => 'Ahlen',
            'state' => 'Nordrhein-Westfalen',
            'postal_code' => '59227',
            'birth_date' => '1990-01-01',
            'email' => 'ali@example.com',
            'phone' => '+492382123456',
            'zahlungsart' => 'barzahlung',
            'monatsbeitrag' => 25,
            'unterschrift' => '',
            'dsgvo_zustimmung' => true,
            'status' => 'pending',
        ], $overrides));
    }
}
