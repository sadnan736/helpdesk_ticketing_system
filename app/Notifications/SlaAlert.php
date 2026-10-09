<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaAlert extends Notification
{
    // $type is 'warning' or 'breach'
    public function __construct(public Ticket $ticket, public string $type)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', SmsChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $prefix = $this->type === 'breach' ? 'SLA breached' : 'SLA warning';

        return (new MailMessage)
            ->subject($prefix . ': ' . $this->ticket->code)
            ->line($this->message())
            ->action('Open ticket', route('tickets.show', $this->ticket));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'type' => $this->type,
            'message' => $this->message(),
        ];
    }

    public function toSms(object $notifiable): string
    {
        return $this->message();
    }

    private function message(): string
    {
        $label = $this->ticket->code . ' (' . $this->ticket->subject . ')';

        return $this->type === 'breach'
            ? "SLA BREACHED: {$label} is past its deadline."
            : "SLA warning: {$label} is close to its deadline.";
    }
}