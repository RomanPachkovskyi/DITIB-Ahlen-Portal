<?php

namespace App\Services;

use App\Models\Member;
use App\Support\PhoneNumber;

class MemberDuplicateChecker
{
    public function findByBirthDateAndPhone(?string $birthDate, ?string $phone): ?Member
    {
        $normalizedPhone = PhoneNumber::normalize($phone);

        if (blank($birthDate) || blank($normalizedPhone)) {
            return null;
        }

        return Member::withTrashed()
            ->whereDate('birth_date', $birthDate)
            ->where('phone', $normalizedPhone)
            ->first();
    }
}
