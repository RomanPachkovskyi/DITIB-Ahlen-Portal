<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberNumberSequence extends Model
{
    protected $fillable = [
        'name',
        'next_number',
    ];

    public static function issueForMembers(): string
    {
        return DB::transaction(function (): string {
            $sequence = static::query()
                ->where('name', 'members')
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = static::query()->create([
                    'name' => 'members',
                    'next_number' => static::initialNextNumber(),
                ]);
            }

            do {
                $number = $sequence->next_number;
                $memberNumber = static::formatMemberNumber($number);

                $sequence->next_number = $number + 1;
            } while (Member::withTrashed()->where('member_number', $memberNumber)->exists());

            $sequence->save();

            return $memberNumber;
        }, 5);
    }

    private static function formatMemberNumber(int $number): string
    {
        return sprintf('DA-%s-%04d', now()->format('Y'), $number);
    }

    private static function initialNextNumber(): int
    {
        $lastUsedNumber = DB::table('members')
            ->whereNotNull('member_number')
            ->pluck('member_number')
            ->map(function (string $memberNumber): int {
                preg_match('/(\d+)$/', $memberNumber, $matches);

                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->max() ?? 0;

        return $lastUsedNumber + 1;
    }
}
