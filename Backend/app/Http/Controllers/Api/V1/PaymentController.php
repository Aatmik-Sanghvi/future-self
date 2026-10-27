<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\PaymentGatewayInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ResponseController;
use App\Models\Payment;
use App\Models\User;
use App\Services\ValidationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends ResponseController
{
    protected PaymentGatewayInterface $paymentGateway;

    // Supported plans duration
    protected const PLANS = [
        'pro_monthly' => [
            'name' => 'Pro Monthly',
            'duration_days' => 30,
        ],
    ];

    /**
     * Get active plan details dynamically from Razorpay (cached).
     */
    protected function getActivePlan(): array
    {
        $planId = config('services.razorpay.plan_id');

        if (! $planId) {
            return [
                'plan_id' => null,
                'amount' => 0,
                'currency' => 'INR',
                'name' => 'Pro Monthly',
            ];
        }

        try {
            $plan = $this->paymentGateway->getPlan($planId);
            $item = $plan['item'] ?? [];

            return [
                'plan_id' => $planId,
                'amount' => (int) ($item['amount'] ?? 0),
                'currency' => $item['currency'] ?? 'INR',
                'name' => $item['name'] ?? 'Pro Monthly',
                'period' => $plan['period'] ?? 'monthly',
                'interval' => $plan['interval'] ?? 1,
            ];
        } catch (Exception $e) {
            Log::warning('Failed to fetch plan from Razorpay: ' . $e->getMessage());
            return [
                'plan_id' => $planId,
                'amount' => 0,
                'currency' => 'INR',
                'name' => 'Pro Monthly',
            ];
        }
    }

    public function __construct(
        ValidationService $validationService,
        PaymentGatewayInterface $paymentGateway
    ) {
        parent::__construct($validationService);
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Create a new payment order.
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'plan' => 'nullable|string|in:pro_monthly',
        ]);

        $planKey = $request->input('plan', 'pro_monthly');
        $planData = $this->getActivePlan();

        $user = auth()->user();

        try {
            $orderData = $this->paymentGateway->createOrder([
                'amount' => $planData['amount'],
                'currency' => $planData['currency'],
                'receipt' => 'fs_rcpt_' . $user->id . '_' . time(),
                'notes' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'plan' => $planKey,
                ],
            ]);

            // Save order in payments table
            $payment = Payment::create([
                'user_id' => $user->id,
                'razorpay_order_id' => $orderData['id'],
                'amount' => $orderData['amount'],
                'currency' => $orderData['currency'],
                'status' => 'created',
                'plan' => $planKey,
            ]);

            $razorpayKey = config('services.razorpay.key') ?? env('RAZORPAY_KEY');

            return ResponseHelper::send(200, 'Order created successfully.', [
                'order_id' => $orderData['id'],
                'amount' => $orderData['amount'],
                'currency' => $orderData['currency'],
                'key' => $razorpayKey,
                'plan' => [
                    'id' => $planKey,
                    'name' => $plan['name'],
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Razorpay createOrder failed: ' . $e->getMessage());
            return ResponseHelper::send(500, 'Unable to create payment order. Please try again.');
        }
    }

    /**
     * Verify the payment signature and activate Pro subscription.
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = auth()->user();

        $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)
            ->where('user_id', $user->id)
            ->first();

        $isValid = $this->paymentGateway->verifyPayment([
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);

        if (! $isValid) {
            if ($payment) {
                $payment->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature,
                    'status' => 'failed',
                ]);
            }

            return ResponseHelper::send(400, 'Payment signature verification failed.');
        }

        // Determine plan duration
        $planKey = $payment?->plan ?? 'pro_monthly';
        $durationDays = self::PLANS[$planKey]['duration_days'] ?? 30;

        // Update payment status
        if ($payment) {
            $payment->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'status' => 'paid',
            ]);
        }

        // Activate Pro subscription on user
        $currentExpiry = $user->pro_expires_at && $user->pro_expires_at->isFuture()
            ? $user->pro_expires_at
            : now();

        $user->update([
            'is_pro' => true,
            'subscription_status' => 'active',
            'has_used_trial' => true,
            'pro_expires_at' => $currentExpiry->addDays($durationDays),
        ]);

        return ResponseHelper::send(
            200,
            'Payment verified successfully! Welcome to FutureSelf Pro.',
            $this->get_user_data()
        );
    }

    /**
     * Create a Razorpay subscription (with 7-day trial if user is eligible).
     */
    public function createSubscription(Request $request)
    {
        $user = auth()->user();

        if ($user->hasActivePro()) {
            return ResponseHelper::send(400, 'You already have an active Pro plan or trial.');
        }

        $planData = $this->getActivePlan();
        $planId = $planData['plan_id'] ?? config('services.razorpay.plan_id');

        if (! $planId) {
            return ResponseHelper::send(500, 'Subscription plan is not configured. Please contact support.');
        }

        $isTrial = ! $user->has_used_trial;

        try {
            $subPayload = [
                'plan_id' => $planId,
                'total_count' => 12,
                'notes' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'is_trial' => $isTrial ? 'true' : 'false',
                ],
            ];

            // If user has NOT used trial yet, first deduction happens after 7 days
            if ($isTrial) {
                $subPayload['start_at'] = now()->addDays(7)->timestamp;
            }

            $subscription = $this->paymentGateway->createSubscription($subPayload);

            $razorpayKey = config('services.razorpay.key') ?? env('RAZORPAY_KEY');

            return ResponseHelper::send(200, 'Subscription created successfully.', [
                'subscription_id' => $subscription['id'],
                'key' => $razorpayKey,
                'is_trial' => $isTrial,
                'trial_days' => $isTrial ? 7 : 0,
                'amount' => $planData['amount'],
                'currency' => $planData['currency'],
                'plan_id' => $planId,
            ]);
        } catch (Exception $e) {
            Log::error('Razorpay createSubscription failed: ' . $e->getMessage());
            return ResponseHelper::send(500, 'Unable to create subscription. Please try again.');
        }
    }

    /**
     * Verify subscription authorization signature from Razorpay checkout.
     */
    public function verifySubscription(Request $request)
    {
        $request->validate([
            'razorpay_subscription_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = auth()->user();

        $isValid = $this->paymentGateway->verifySubscription([
            'razorpay_subscription_id' => $request->razorpay_subscription_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);

        if (! $isValid) {
            return ResponseHelper::send(400, 'Subscription authorization verification failed.');
        }

        $isTrial = ! $user->has_used_trial;

        if ($isTrial) {
            $trialEndsAt = now()->addDays(7);
            $user->update([
                'is_pro' => true,
                'subscription_status' => 'trial',
                'trial_ends_at' => $trialEndsAt,
                'pro_expires_at' => $trialEndsAt,
                'has_used_trial' => true,
                'razorpay_subscription_id' => $request->razorpay_subscription_id,
            ]);

            $message = '🎉 15-minute free trial activated! Daily Missions and Goal Tracking are now unlocked.';
        } else {
            $currentExpiry = $user->pro_expires_at && $user->pro_expires_at->isFuture()
                ? $user->pro_expires_at
                : now();

            $user->update([
                'is_pro' => true,
                'subscription_status' => 'active',
                'pro_expires_at' => $currentExpiry->addDays(30),
                'has_used_trial' => true,
                'razorpay_subscription_id' => $request->razorpay_subscription_id,
            ]);

            $message = '🎉 Welcome to FutureSelf Pro! Your subscription is now active.';
        }

        $planData = $this->getActivePlan();

        // Save payment record
        Payment::create([
            'user_id' => $user->id,
            'razorpay_subscription_id' => $request->razorpay_subscription_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
            'amount' => $planData['amount'],
            'currency' => $planData['currency'],
            'status' => 'paid',
            'plan' => 'pro_monthly',
        ]);

        return ResponseHelper::send(200, $message, $this->get_user_data());
    }

    /**
     * Start the 7-day free trial directly (used in tests or automated flows).
     */
    public function startTrial(Request $request)
    {
        $user = auth()->user();

        if ($user->hasActivePro()) {
            return ResponseHelper::send(400, 'You already have an active Pro plan or trial.');
        }

        if ($user->has_used_trial) {
            return ResponseHelper::send(400, 'You have already used your 7-day free trial. Please purchase the Pro plan to continue.');
        }

        $trialEndsAt = now()->addDays(7);

        $user->update([
            'is_pro' => true,
            'subscription_status' => 'trial',
            'trial_ends_at' => $trialEndsAt,
            'pro_expires_at' => $trialEndsAt,
            'has_used_trial' => true,
        ]);

        return ResponseHelper::send(
            200,
            '🎉 7-day free trial activated! Daily Missions and Goal Tracking are now unlocked.',
            $this->get_user_data()
        );
    }

    /**
     * Cancel the active Pro plan or 7-day trial.
     * Cancels the subscription in Razorpay immediately so no future charges occur.
     */
    public function cancelSubscription(Request $request)
    {
        $user = auth()->user();

        if (! $user->hasActivePro() && $user->subscription_status !== 'trial' && $user->subscription_status !== 'active') {
            return ResponseHelper::send(400, 'You do not have an active plan or trial to cancel.');
        }

        // Cancel on Razorpay if subscription ID exists
        if (! empty($user->razorpay_subscription_id)) {
            $this->paymentGateway->cancelSubscription($user->razorpay_subscription_id);
        }

        $user->update([
            'is_pro' => false,
            'subscription_status' => 'cancelled',
            'has_used_trial' => true,
        ]);

        return ResponseHelper::send(
            200,
            'Your subscription has been cancelled. Daily Missions and Goal Tracking access is now restricted.',
            $this->get_user_data()
        );
    }

    /**
     * Webhook handler for Razorpay recurring subscription events.
     */
    public function handleWebhook(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');

        if (! $signature || ! $this->paymentGateway->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Razorpay webhook signature verification failed or signature header missing.');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? '';

        Log::info('Razorpay webhook event: ' . $event, ['event' => $event]);

        if ($event === 'subscription.charged') {
            $subEntity = $payload['payload']['subscription']['entity'] ?? null;
            if ($subEntity && ! empty($subEntity['id'])) {
                $user = User::where('razorpay_subscription_id', $subEntity['id'])->first();
                if ($user) {
                    $user->update([
                        'is_pro' => true,
                        'subscription_status' => 'active',
                        'pro_expires_at' => now()->addDays(30),
                    ]);

                    $planData = $this->getActivePlan();
                    $amount = $subEntity['plan']['item']['amount'] ?? $planData['amount'];
                    $currency = $subEntity['plan']['item']['currency'] ?? $planData['currency'];

                    Payment::create([
                        'user_id' => $user->id,
                        'razorpay_subscription_id' => $subEntity['id'],
                        'razorpay_payment_id' => $payload['payload']['payment']['entity']['id'] ?? null,
                        'amount' => $amount,
                        'currency' => $currency,
                        'status' => 'paid',
                        'plan' => 'pro_monthly',
                    ]);
                }
            }
        } elseif ($event === 'subscription.cancelled' || $event === 'subscription.halted') {
            $subEntity = $payload['payload']['subscription']['entity'] ?? null;
            if ($subEntity && ! empty($subEntity['id'])) {
                $user = User::where('razorpay_subscription_id', $subEntity['id'])->first();
                if ($user) {
                    $user->update([
                        'is_pro' => false,
                        'subscription_status' => 'cancelled',
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Get current subscription and trial details for the authenticated user.
     */
    public function getSubscriptionStatus(Request $request)
    {
        $user = auth()->user();

        return ResponseHelper::send(200, 'Subscription status fetched successfully.', [
            'is_pro' => $user->hasActivePro(),
            'subscription_status' => $user->subscription_status ?? ($user->hasActivePro() ? 'active' : 'free'),
            'pro_expires_at' => $user->pro_expires_at,
            'trial_ends_at' => $user->trial_ends_at,
            'has_used_trial' => (bool) $user->has_used_trial,
            'days_remaining' => $user->pro_expires_at && $user->pro_expires_at->isFuture()
                ? now()->diffInDays($user->pro_expires_at, false)
                : 0,
        ]);
    }

    /**
     * Get plan details fetched dynamically from Razorpay.
     */
    public function getPlanDetails(Request $request)
    {
        $planData = $this->getActivePlan();

        return ResponseHelper::send(200, 'Plan details fetched successfully.', [
            'plan_id' => $planData['plan_id'],
            'name' => $planData['name'],
            'amount' => $planData['amount'],
            'currency' => $planData['currency'],
            'amount_in_rupees' => $planData['amount'] > 0 ? ($planData['amount'] / 100) : 0,
            'amount_formatted' => '₹' . number_format($planData['amount'] / 100, 0),
        ]);
    }
}
