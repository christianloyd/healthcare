# Implementation Plan: Textbee SMS Integration

This plan outlines the steps and code changes required to replace the existing IPROG SMS integration with [Textbee.dev](https://textbee.dev) to reduce API costs. Textbee routes messages through an Android phone's existing cellular plan, serving as a free or low-cost gateway.

## Goal Description

Integrate Textbee API into the [SmsService](file:///c:/xampp/htdocs/capstone/health-care/app/Services/SmsService.php) class, substituting the current IPROG POST request. We will maintain the existing method signatures in `SmsService` so that controllers and other services (like `PrenatalCheckupService`, `NotificationService`, and `SendSmsJob`) do not need to be refactored.

## User Review Required

> [!IMPORTANT]
> **Authentication & Infrastructure Change**:
> 1.  **Physical Device**: This integration requires a dedicated Android phone with a working SIM card and the Textbee app running 24/7.
> 2.  **Sender ID Limitation**: Unlike IPROG, Textbee uses your phone's SIM. The recipient will see your **Phone Number** as the sender, not "HealthCare". We will append " - HealthCare System" to messages to ensure patients recognize the source.
> 3.  **Rate Limiting**: Sending speed depends on your mobile carrier's limits. Avoid blasting hundreds of messages simultaneously to prevent carrier-level blocking.

## Open Questions

- [ ] **API Key & Device ID**: Do you already have these credentials from the Textbee dashboard?
- [ ] **SIM Plan**: Does your SIM card have "Unli-Text" to all networks? If not, sending reminders might consume your prepaid load quickly.

## User Action Required: Initial Setup

Before these code changes can be tested, you must complete the following setup:
1.  **Prepare an Android Phone**: Ensure you have an Android device with an active SIM card capable of sending SMS.
2.  **Install Textbee App**: Download and install the Textbee app on the Android phone from the [Textbee GitHub Releases](https://github.com/vernu/textbee/releases).
3.  **Get Credentials**:
    *   Sign in to the [Textbee Web Dashboard](https://dashboard.textbee.dev).
    *   Generate a new **API Key**.
    *   Connect your Android phone by scanning the QR code in the dashboard.
    *   Note your connected **Device ID** from the dashboard.

## Proposed Changes

### [Component] Configuration & Environment

#### [MODIFY] .env & .env.example
Add the new Textbee environment variables and remove the old IPROG ones.

```diff
-# IPROG SMS API Configuration
-IPROG_API_TOKEN=your_token
-IPROG_API_URL=https://www.iprogsms.com/api/v1/sms_messages
-IPROG_SENDER_NAME="Health Care"
+
+# Textbee SMS API Configuration
+TEXTBEE_API_KEY=your_textbee_api_key_here
+TEXTBEE_DEVICE_ID=your_textbee_device_id_here
+IPROG_SENDER_NAME="HealthCare System"
```

#### [MODIFY] [services.php](file:///c:/xampp/htdocs/capstone/health-care/config/services.php)
Add `textbee` configuration element.

```diff
-    'iprog' => [
-        'api_token' => env('IPROG_API_TOKEN'),
-        'api_url' => env('IPROG_API_URL', 'https://www.iprogsms.com/api/v1/sms_messages'),
-        'sender_name' => env('IPROG_SENDER_NAME', 'HealthCare System'),
-    ],
+    'textbee' => [
+        'api_key' => env('TEXTBEE_API_KEY'),
+        'device_id' => env('TEXTBEE_DEVICE_ID'),
+    ],
+    'iprog' => [
+        'sender_name' => env('IPROG_SENDER_NAME', 'HealthCare System'),
+    ],
```

***

### [Component] SMS Core Logic

#### [MODIFY] [SmsService.php](file:///c:/xampp/htdocs/capstone/health-care/app/Services/SmsService.php)
Refactor the core SMS sending logic to target Textbee's API.

1.  **Constructor**: Update to load `textbee` config. We keep `senderName` for appending to messages.
2.  **`sendSms` Method**:
    *   URL: `https://api.textbee.dev/api/v1/gateway/devices/{$this->deviceId}/sendSMS`
    *   Header: `x-api-key: {$this->apiKey}`
    *   Body: `{ "recipients": ["639..."], "message": "..." }`

```php
// Summary of logic changes in sendSms():
$response = Http::timeout(30)
    ->withoutVerifying()
    ->withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'x-api-key' => $this->apiKey
    ])
    ->post("https://api.textbee.dev/api/v1/gateway/devices/{$this->deviceId}/sendSMS", [
        'recipients' => [$formattedNumber],
        'message' => $message,
    ]);
```

## Verification Plan

### Automated/Tool-based Testing
1.  **Config Check**: Run `php artisan tinker` and verify `config('services.textbee')` returns the values from `.env`.
2.  **API Connectivity**: Use `curl` or a test script to check if the Textbee API responds to the provided API key.

### Manual Verification
1.  **Tinker Test**:
    ```php
    $sms = app(App\Services\SmsService::class);
    $sms->sendSms('09XXXXXXXXX', 'Test: Integration with Textbee successful!');
    ```
2.  **Check Android Device**: Open the "Messages" app on the connected Android phone. The test message should appear in the "Sent" history.
3.  **Database Audit**: Check the `sms_logs` table in the database to ensure the `status` is recorded as `sent` and the Textbee response JSON is stored in the `response` column.

