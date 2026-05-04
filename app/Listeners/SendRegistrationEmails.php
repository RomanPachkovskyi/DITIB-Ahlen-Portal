<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Mail\MemberRegistrationConfirmation;
use App\Mail\NewMemberNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendRegistrationEmails implements ShouldQueue
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
        // Send email to the client
        Mail::to($event->member->email)->send(new MemberRegistrationConfirmation($event->member));

        // Send email to the admin
        Mail::to('info@ditib-ahlen-projekte.de')->send(new NewMemberNotification($event->member));
    }
}
