<?php
declare(strict_types=1);

/**
 * Stripe REST API helper — uses PHP cURL (bundled with XAMPP).
 * No SDK required.
 */

/**
 * Make a signed request to the Stripe REST API.
 *
 * @param string $method  'GET' or 'POST'
 * @param string $path    e.g. 'checkout/sessions' or 'checkout/sessions/cs_xxx'
 * @param array  $params  Payload for POST or query params for GET
 * @return array Decoded JSON response from Stripe
 */
function stripe_request(string $method, string $path, array $params = []): array {
    $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
    $ch  = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
        CURLOPT_HTTPHEADER     => ['Stripe-Version: 2023-10-16'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, stripe_encode($params));
    } else {
        $qs = $params ? '?' . stripe_encode($params) : '';
        curl_setopt($ch, CURLOPT_URL, $url . $qs);
    }

    $body  = curl_exec($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    if ($errno || $body === false) {
        return ['error' => ['message' => 'Network error contacting Stripe (cURL errno ' . $errno . ').']];
    }

    $decoded = json_decode((string)$body, true);
    return is_array($decoded) ? $decoded : ['error' => ['message' => 'Invalid JSON from Stripe.']];
}

/**
 * Recursively encode a nested array into Stripe's expected form-encoded format.
 * e.g. line_items[0][price_data][unit_amount]=999
 */
function stripe_encode(array $data, string $prefix = ''): string {
    $parts = [];
    foreach ($data as $key => $value) {
        $fullKey = $prefix !== '' ? $prefix . '[' . $key . ']' : (string)$key;
        if (is_array($value)) {
            $nested = stripe_encode($value, $fullKey);
            if ($nested !== '') {
                $parts[] = $nested;
            }
        } else {
            $parts[] = urlencode($fullKey) . '=' . urlencode((string)$value);
        }
    }
    return implode('&', $parts);
}

/**
 * Verify that an incoming webhook payload was signed by Stripe.
 * Protects against replay attacks (rejects events older than 5 minutes).
 *
 * @param string $payload    Raw request body (file_get_contents('php://input'))
 * @param string $sig_header Value of HTTP_STRIPE_SIGNATURE
 * @param string $secret     STRIPE_WEBHOOK_SECRET
 */
function stripe_verify_webhook(string $payload, string $sig_header, string $secret): bool {
    if ($sig_header === '') {
        return false;
    }

    $parts = [];
    foreach (explode(',', $sig_header) as $part) {
        $kv = explode('=', $part, 2);
        if (count($kv) === 2) {
            $parts[$kv[0]][] = $kv[1];
        }
    }

    $timestamp = (int)($parts['t'][0] ?? 0);
    if ($timestamp === 0) {
        return false;
    }

    // Reject events older than 5 minutes (replay attack protection)
    if (abs(time() - $timestamp) > 300) {
        return false;
    }

    $signed   = $timestamp . '.' . $payload;
    $expected = hash_hmac('sha256', $signed, $secret);

    foreach ($parts['v1'] ?? [] as $v) {
        if (hash_equals($expected, $v)) {
            return true;
        }
    }
    return false;
}
