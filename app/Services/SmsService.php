<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\SmsLog;
use Exception;

class SmsService
{
    protected $apiKey;
    protected $deviceId;
    protected $senderName;

    public function __construct()
    {
        $this->apiKey = config('services.textbee.api_key');
        $this->deviceId = config('services.textbee.device_id');
        $this->senderName = config('services.iprog.sender_name', 'HealthCare System');
    }

    /**
     * Send SMS to a single recipient
     *
     * @param string $phoneNumber Philippine phone number (09xxxxxxxxx or 639xxxxxxxxx)
     * @param string $message SMS message content
     * @param string $type SMS type (appointment_reminder, vaccination_reminder, etc.)
     * @param string $recipientName Name of recipient
     * @param string $relatedType Related model type
     * @param int $relatedId Related model ID
     * @return array Response with success status and message
     */
    public function sendSms(
        string $phoneNumber,
        string $message,
        string $type = 'general',
        string $recipientName = null,
        string $relatedType = null,
        int $relatedId = null
    ): array {
        $formattedNumber = null;
        $status = 'failed';
        $responseData = null;

        try {
            // Format phone number to 639xxxxxxxxx format
            $formattedNumber = $this->formatPhoneNumber($phoneNumber);

            // Log the SMS attempt
            Log::info('Attempting to send SMS', [
                'phone' => $formattedNumber,
                'message' => $message,
                'type' => $type
            ]);

            // Make API request to Textbee
            $response = Http::timeout(30)
                ->withoutVerifying() // Bypass SSL verification (for development/XAMPP)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'x-api-key' => $this->apiKey,
                ])
                ->post("https://api.textbee.dev/api/v1/gateway/devices/{$this->deviceId}/sendSMS", [
                    'recipients' => [$formattedNumber],
                    'message' => $message,
                ]);

            // Check if request was successful
            if ($response->successful()) {
                $status = 'sent';
                $responseData = json_encode($response->json());

                Log::info('SMS sent successfully', [
                    'phone' => $formattedNumber,
                    'response' => $response->json()
                ]);

                // Log to database
                $this->logSms($formattedNumber, $message, $type, $status, $responseData, $recipientName, $relatedType, $relatedId);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $response->json()
                ];
            }

            // Handle API errors
            $responseData = $response->body();
            Log::error('Failed to send SMS', [
                'phone' => $formattedNumber,
                'status' => $response->status(),
                'response' => $responseData
            ]);

            // Log to database
            $this->logSms($formattedNumber, $message, $type, $status, $responseData, $recipientName, $relatedType, $relatedId);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $responseData,
                'status' => $response->status()
            ];

        } catch (Exception $e) {
            $responseData = 'Exception: ' . $e->getMessage();
            Log::error('SMS sending exception', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            // Log to database
            $this->logSms($formattedNumber ?? $phoneNumber, $message, $type, $status, $responseData, $recipientName, $relatedType, $relatedId);

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log SMS to database
     */
    protected function logSms(
        string $recipientNumber,
        string $message,
        string $type,
        string $status,
        string $response = null,
        string $recipientName = null,
        string $relatedType = null,
        int $relatedId = null
    ): void {
        try {
            SmsLog::create([
                'recipient_number' => $recipientNumber,
                'recipient_name' => $recipientName,
                'message' => $message,
                'type' => $type,
                'status' => $status,
                'response' => $response,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'sent_by' => Auth::id()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log SMS to database', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS to multiple recipients
     *
     * @param array $recipients Array of phone numbers
     * @param string $message SMS message content
     * @return array Results for each recipient
     */
    public function sendBulkSms(array $recipients, string $message): array
    {
        $results = [];

        foreach ($recipients as $phoneNumber) {
            $results[$phoneNumber] = $this->sendSms($phoneNumber, $message);
        }

        return $results;
    }

    /**
     * Format phone number to IPROG format (639xxxxxxxxx)
     *
     * @param string $phoneNumber
     * @return string Formatted phone number
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Convert different formats to 639xxxxxxxxx
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 2) === '09') {
            // 09xxxxxxxxx -> 639xxxxxxxxx
            return '63' . substr($cleaned, 1);
        } elseif (strlen($cleaned) === 10 && substr($cleaned, 0, 1) === '9') {
            // 9xxxxxxxxx -> 639xxxxxxxxx
            return '63' . $cleaned;
        } elseif (strlen($cleaned) === 12 && substr($cleaned, 0, 2) === '63') {
            // Already in 639xxxxxxxxx format
            return $cleaned;
        }

        // Return as-is if format is unclear
        return $cleaned;
    }

    /**
     * Validate Philippine phone number format
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function isValidPhoneNumber(string $phoneNumber): bool
    {
        $formatted = $this->formatPhoneNumber($phoneNumber);

        // Check if it matches 639xxxxxxxxx format (12 digits total)
        return preg_match('/^639\d{9}$/', $formatted) === 1;
    }

    /**
     * Send appointment reminder SMS
     *
     * @param string $phoneNumber
     * @param string $patientName
     * @param string $appointmentDate
     * @param string $appointmentType
     * @param int $relatedId
     * @return array
     */
    public function sendAppointmentReminder(
        string $phoneNumber,
        string $patientName,
        string $appointmentDate,
        string $appointmentType = 'checkup',
        int $relatedId = null
    ): array {
        $message = "Hi {$patientName}! This is a reminder for your {$appointmentType} on {$appointmentDate}. "
                 . "Please arrive on time. For concerns, contact your barangay health worker. - {$this->senderName}";

        return $this->sendSms(
            $phoneNumber,
            $message,
            'appointment_reminder',
            $patientName,
            'PrenatalCheckup',
            $relatedId
        );
    }

    /**
     * Send vaccination reminder SMS
     *
     * @param string $phoneNumber
     * @param string $childName
     * @param string $vaccineName
     * @param string $dueDate
     * @param string $motherName (optional)
     * @param int $relatedId
     * @return array
     */
    public function sendVaccinationReminder(
        string $phoneNumber,
        string $childName,
        string $vaccineName,
        string $dueDate,
        string $motherName = null,
        int $relatedId = null
    ): array {
        // If mother name is provided, personalize the message
        if ($motherName) {
            $message = "Hi {$motherName}! This is a reminder that your child {$childName}'s {$vaccineName} vaccination is due on {$dueDate}. "
                     . "Please visit the health center. - {$this->senderName}";
        } else {
            $message = "Reminder: {$childName}'s {$vaccineName} vaccination is due on {$dueDate}. "
                     . "Please visit the health center. - {$this->senderName}";
        }

        return $this->sendSms(
            $phoneNumber,
            $message,
            'vaccination_reminder',
            $motherName ?? $childName,
            'Immunization',
            $relatedId
        );
    }

    /**
     * Send missed appointment notification
     *
     * @param string $phoneNumber
     * @param string $patientName
     * @param string $missedDate
     * @param string $motherName (optional, for child appointments)
     * @param int $relatedId
     * @return array
     */
    public function sendMissedAppointmentNotification(
        string $phoneNumber,
        string $patientName,
        string $missedDate,
        string $motherName = null,
        int $relatedId = null
    ): array {
        // If mother name is provided (for child immunization), personalize the message
        if ($motherName) {
            $message = "Hi {$motherName}! Your child {$patientName} missed the appointment on {$missedDate}. "
                     . "Please contact us to reschedule. Your child's health is important! - {$this->senderName}";
        } else {
            $message = "Hi {$patientName}, you missed your appointment on {$missedDate}. "
                     . "Please contact us to reschedule. Your health is important! - {$this->senderName}";
        }

        return $this->sendSms(
            $phoneNumber,
            $message,
            'missed_appointment',
            $motherName ?? $patientName,
            null,
            $relatedId
        );
    }

    /**
     * Get current SMS balance/credits (if API supports it)
     *
     * @return array
     */
    public function getBalance(): array
    {
        // IPROG may not have a balance endpoint, but this is a placeholder
        // You can check your balance on the IPROG dashboard
        return [
            'success' => true,
            'message' => 'Check status at Textbee dashboard',
            'url' => 'https://dashboard.textbee.dev'
        ];
    }
}
