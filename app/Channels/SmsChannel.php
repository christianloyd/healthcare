<?php

namespace App\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Get the phone number from the notifiable entity
        $phoneNumber = $this->getPhoneNumber($notifiable);

        if (empty($phoneNumber)) {
            Log::warning('Cannot send SMS: No phone number found', [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null
            ]);
            return;
        }

        // Validate phone number format
        if (!$this->smsService->isValidPhoneNumber($phoneNumber)) {
            Log::warning('Cannot send SMS: Invalid phone number format', [
                'phone' => $phoneNumber,
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null
            ]);
            return;
        }

        // Get the SMS message from the notification
        $message = $notification->toSms($notifiable);

        if (empty($message)) {
            Log::warning('Cannot send SMS: Empty message', [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null
            ]);
            return;
        }

        // Send the SMS
        $result = $this->smsService->sendSms($phoneNumber, $message, $notification->smsType ?? 'general');

        // Log the result
        if ($result['success']) {
            Log::info('SMS notification sent successfully', [
                'phone' => $phoneNumber,
                'notification' => get_class($notification)
            ]);
        } else {
            Log::error('SMS notification failed', [
                'phone' => $phoneNumber,
                'notification' => get_class($notification),
                'error' => $result['message']
            ]);
        }
    }

    /**
     * Get the phone number from the notifiable entity
     *
     * @param  mixed  $notifiable
     * @return string|null
     */
    protected function getPhoneNumber($notifiable)
    {
        // Try different possible phone number fields
        if (method_exists($notifiable, 'routeNotificationForSms')) {
            return $notifiable->routeNotificationForSms();
        }

        // Check common phone number attributes
        $phoneFields = ['contact', 'phone', 'phone_number', 'contact_number', 'mobile'];

        foreach ($phoneFields as $field) {
            if (isset($notifiable->$field) && !empty($notifiable->$field)) {
                return $notifiable->$field;
            }
        }

        return null;
    }
}
