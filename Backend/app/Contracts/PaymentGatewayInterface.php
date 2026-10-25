<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function createOrder(array $data): array;

    public function verifyPayment(array $data): bool;

    public function createSubscription(array $data): array;

    public function verifySubscription(array $data): bool;

    public function cancelSubscription(string $subscriptionId): bool;

    public function verifyWebhookSignature(string $payload, string $signature): bool;
}