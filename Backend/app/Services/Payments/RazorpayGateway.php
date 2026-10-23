<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayGateway implements PaymentGatewayInterface
{
    protected Api $api;

    public function __construct()
    {
        $key = config('services.razorpay.key') ?? env('RAZORPAY_KEY');
        $secret = config('services.razorpay.secret') ?? env('RAZORPAY_SECRET');

        $this->api = new Api($key, $secret);
    }

    /**
     * Create a Razorpay order.
     *
     * @param array $data Expected keys: 'amount' (in paise), optional: 'currency', 'receipt', 'notes'
     * @return array
     */
    public function createOrder(array $data): array
    {
        $order = $this->api->order->create([
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'INR',
            'receipt' => $data['receipt'] ?? (string) Str::uuid(),
            'notes' => $data['notes'] ?? [],
        ]);

        return $order->toArray();
    }

    /**
     * Verify the payment signature.
     *
     * @param array $data Expected keys: 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'
     * @return bool
     */
    public function verifyPayment(array $data): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ]);

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay payment signature verification failed: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Razorpay payment verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a Razorpay subscription (supports future start_at for 7-day free trial).
     *
     * @param array $data Expected keys: 'plan_id', optional: 'total_count', 'start_at', 'notes'
     * @return array
     */
    public function createSubscription(array $data): array
    {
        $payload = [
            'plan_id' => $data['plan_id'],
            'total_count' => $data['total_count'] ?? 12,
            'quantity' => 1,
            'customer_notify' => 1,
            'notes' => $data['notes'] ?? [],
        ];

        if (!empty($data['start_at'])) {
            $payload['start_at'] = (int) $data['start_at'];
        }

        $subscription = $this->api->subscription->create($payload);

        return $subscription->toArray();
    }

    /**
     * Verify subscription authorization signature.
     *
     * @param array $data Expected keys: 'razorpay_subscription_id', 'razorpay_payment_id', 'razorpay_signature'
     * @return bool
     */
    public function verifySubscription(array $data): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature([
                'razorpay_subscription_id' => $data['razorpay_subscription_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ]);

            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay subscription signature verification failed: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Razorpay subscription verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel an active subscription on Razorpay immediately so no future charges occur.
     *
     * @param string $subscriptionId
     * @return bool
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $subscription = $this->api->subscription->fetch($subscriptionId);
            $subscription->cancel(['cancel_at_cycle_end' => 0]);
            return true;
        } catch (Exception $e) {
            Log::error('Razorpay cancel subscription error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify the webhook signature against the raw payload and webhook secret.
     *
     * @param string $payload Raw request body string
     * @param string $signature Value from 'X-Razorpay-Signature' header
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.razorpay.webhook_secret') ?? env('RAZORPAY_WEBHOOK_SECRET');

        if (empty($webhookSecret)) {
            Log::error('Razorpay webhook secret is not configured.');
            return false;
        }

        try {
            $this->api->utility->verifyWebhookSignature($payload, $signature, $webhookSecret);
            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay webhook signature verification failed: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Razorpay webhook verification error: ' . $e->getMessage());
            return false;
        }
    }
}
