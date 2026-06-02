<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Mail\MemberRegistrationConfirmation;
use App\Mail\NewMemberNotification;
use App\Services\EmailLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendRegistrationEmails
{
    public function __construct(private readonly EmailLogger $emailLogger) {}

    public function handle(MemberRegistered $event): void
    {
        $member = $event->member;

        try {
            Mail::to($member->email)->send(new MemberRegistrationConfirmation($member));
            $this->emailLogger->sent('registration_confirmation', MemberRegistrationConfirmation::class, 'member', $member->email, $member);
        } catch (Throwable $exception) {
            $this->emailLogger->failed('registration_confirmation', MemberRegistrationConfirmation::class, 'member', $member->email, $exception, $member);
            Log::error('Registration email delivery failed.', [
                'member_id' => $member->id,
                'member_number' => $member->member_number,
                'email' => $member->email,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            Mail::to('info@ditib-ahlen-projekte.de')->send(new NewMemberNotification($member));
            $this->emailLogger->sent('admin_new_member', NewMemberNotification::class, 'admin', 'info@ditib-ahlen-projekte.de', $member);
        } catch (Throwable $exception) {
            $this->emailLogger->failed('admin_new_member', NewMemberNotification::class, 'admin', 'info@ditib-ahlen-projekte.de', $exception, $member);
            Log::error('Admin new member notification failed.', [
                'member_id' => $member->id,
                'member_number' => $member->member_number,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
