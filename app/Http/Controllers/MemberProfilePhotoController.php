<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\ProfilePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberProfilePhotoController extends Controller
{
    public function __invoke(Request $request, Member $member, ProfilePhotoService $photos): StreamedResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        Gate::forUser($user)->authorize('viewProfilePhoto', $member);

        return $photos->response($member);
    }
}
