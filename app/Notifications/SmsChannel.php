<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toSms($notifiable);

        // TODO: replace with a real SMS gateway call, e.g. Http::post(...)
        Log::info("[SMS to {$notifiable->name}] {$message}");
    }
}