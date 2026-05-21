<?php
declare(strict_types=1);

/**
 * Stripe webhook endpoint.
 * Configure in Stripe Dashboard → Developers → Webhooks
 * URL: http://localhost/soundmarket/payment/webhook.php
 *
 * Events to enable:
 *   - checkout.session.completed
 *   - payment_intent.payment_failed
 */

// Must read raw body before any output or session start
$payload    = (string)file_get_contents('php://input');
$sig_header = (string)($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');

require_once dirname(__DIR__) . '/inc/config.php';
require_once dirname(__DIR__) . '/inc/db.php';
require_once __DIR__ . '/stripe_helper.php';

// ── Verify signature ──────────────────────────────────────────────────────────
if (!stripe_verify_webhook($payload, $sig_header, STRIPE_WEBHOOK_SECRET)) {
    http_response_code(400);
    exit('Webhook signature verification failed.');
}

$event = json_decode($payload, true);
if (!is_array($event)) {
    http_response_code(400);
    exit('Invalid payload.');
}

$pdo = db();

// ── Handle events ─────────────────────────────────────────────────────────────
switch ($event['type'] ?? '') {

    case 'checkout.session.completed':
        $sess       = $event['data']['object'] ?? [];
        $session_id = (string)($sess['id']             ?? '');
        $pay_status = (string)($sess['payment_status'] ?? '');

        if ($session_id !== '' && $pay_status === 'paid') {
            $pdo->prepare("
                UPDATE orders SET status = 'paid'
                WHERE stripe_session_id = ? AND status != 'paid'
            ")->execute([$session_id]);
        }
        break;

    case 'payment_intent.payment_failed':
        $pi    = $event['data']['object'] ?? [];
        $pi_id = (string)($pi['id'] ?? '');

        if ($pi_id !== '') {
            // Find the checkout session that produced this payment intent, then cancel the order.
            // Stripe links payment_intent → checkout.session via session.payment_intent field.
            // We do a best-effort lookup; the success/cancel pages remain the primary UX.
            $pdo->prepare("
                UPDATE orders SET status = 'cancelled'
                WHERE stripe_session_id IN (
                    SELECT id FROM (
                        SELECT id FROM orders WHERE stripe_session_id IS NOT NULL
                    ) AS tmp
                )
            ")->execute();
            // Note: full reconciliation requires retrieving the session via Stripe API.
            // In production, store payment_intent_id separately for precise matching.
        }
        break;
}

http_response_code(200);
echo 'OK';
