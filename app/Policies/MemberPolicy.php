<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewProfilePhoto(User $user, Member $member): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $member->email !== null
            && strcasecmp($user->email, $member->email) === 0;
    }
}
