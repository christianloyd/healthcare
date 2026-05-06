<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class HealthcareNotification extends Notification
{

    public $title;
    public $message;
    public $type;
    public $actionUrl;
    public $data;
    public $sendSms;
    public $smsType;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $type = 'info', $actionUrl = null, $data = [], $sendSms = false, $smsType = 'general')
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type; // info, success, warning, error
        $this->actionUrl = $actionUrl;
        $this->data = $data;
        $this->sendSms = $sendSms; // Enable/disable SMS notification
        $this->smsType = $smsType; // Type for SMS logging
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        // Add SMS channel if enabled
        if ($this->sendSms) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'data' => $this->data,
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'data' => $this->data,
        ];
    }

    /**
     * Get the broadcastable type of the notification.
     */
    public function broadcastType(): string
    {
        return 'healthcare.notification';
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param  object  $notifiable
     * @return string
     */
    public function toSms(object $notifiable): string
    {
        // Create a concise SMS message (SMS has character limits)
        $smsMessage = $this->title . ': ' . $this->message;

        // Truncate if too long (160 chars for single SMS, 306 for concatenated)
        if (strlen($smsMessage) > 306) {
            $smsMessage = substr($smsMessage, 0, 303) . '...';
        }

        return $smsMessage;
    }
}
