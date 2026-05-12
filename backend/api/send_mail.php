<?php
// We keep the autoload in case you use SimplePie or other tools elsewhere, 
// but we no longer need the PHPMailer classes here.
require_once __DIR__ . '/../vendor/autoload.php';

function sendTalaAralEmail(string $to, string $subject, string $body) {
    // This is the URL you copied from the Google Apps Script "Web App" deployment
    $apiUrl = getenv('EMAIL_API_URL');

    if (!$apiUrl) {
        error_log("Email Error: EMAIL_API_URL not set in environment.");
        return false;
    }

    $payload = json_encode([
        "to" => $to,
        "subject" => $subject,
        "body" => $body
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Crucial for Google Script redirects
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return isset($result['status']) && $result['status'] === 'success';
    }

    error_log("Email API Error: Received HTTP $httpCode - Response: $response");
    return false;
}