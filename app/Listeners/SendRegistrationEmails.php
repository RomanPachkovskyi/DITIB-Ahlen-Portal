<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Mail\MemberRegistrationConfirmation;
use App\Mail\NewMemberNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendRegistrationEmails
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MemberRegistered $event): void
    {
        try {
            // Send email to the client
            Mail::to($event->member->email)->send(new MemberRegistrationConfirmation($event->member));

            // Send email to the admin
            Mail::to('info@ditib-ahlen-projekte.de')->send(new NewMemberNotification($event->member));
        } catch (Throwable $exception) {
            Log::error('Registration email delivery failed.', [
                'member_id' => $event->member->id,
                'member_number' => $event->member->member_number,
                'email' => $event->member->email,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
