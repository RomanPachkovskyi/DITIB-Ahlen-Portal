<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Member;
use Throwable;

class EmailLogger
{
    public function sent(
        string $event,
        string $mailClass,
        string $recipientType,
        string $recipientEmail,
        ?Member $member = null,
    ): EmailLog {
        return EmailLog::create([
            'member_id'       => $member?->id,
            'event'           => $event,
            'mail_class'      => class_basename($mailClass),
            'recipient_type'  => $recipientType,
            'recipient_email' => $recipientEmail,
            'status'          => 'sent',
        ]);
    }

    public function failed(
        string $event,
        string $mailClass,
        string $recipientType,
        string $recipientEmail,
        Throwable $exception,
        ?Member $member = null,
    ): EmailLog {
        return EmailLog::create([
            'member_id'       => $member?->id,
            'event'           => $event,
            'mail_class'      => class_basename($mailClass),
            'recipient_type'  => $recipientType,
            'recipient_email' => $recipientEmail,
            'status'          => 'failed',
            'error_message'   => $exception->getMessage(),
        ]);
    }
}
