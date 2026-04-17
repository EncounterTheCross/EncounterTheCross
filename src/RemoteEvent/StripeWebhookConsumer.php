<?php

namespace App\RemoteEvent;

use App\Repository\EventParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

/**
 * Consumes Stripe webhook events after they've been verified by StripeWebhookParser.
 *
 * The `name: 'stripe'` attribute matches the routing key in config/packages/webhook.yaml.
 */
#[AsRemoteEventConsumer(name: 'stripe')]
final class StripeWebhookConsumer implements ConsumerInterface
{
    public function __construct(
        private EventParticipantRepository $eventParticipantRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    public function consume(RemoteEvent $event): void
    {
        $payload = $event->getPayload();
        $eventType = $event->getName();

        match ($eventType) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($payload),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($payload),
            default => $this->logger->info('Stripe webhook: ignoring event type', [
                'type' => $eventType,
                'event_id' => $event->getId(),
            ]),
        };
    }

    private function handlePaymentSucceeded(array $payload): void
    {
        $paymentIntent = $payload['data']['object'] ?? null;

        if (!$paymentIntent) {
            $this->logger->error('Stripe webhook: payment_intent.succeeded missing data.object');
            return;
        }

        $registrationId = $paymentIntent['metadata']['registration_id'] ?? null;

        if (!$registrationId) {
            $this->logger->error('Stripe webhook: payment_intent.succeeded missing registration_id', [
                'payment_intent_id' => $paymentIntent['id'] ?? null,
            ]);
            return;
        }

        $registration = $this->eventParticipantRepository->find($registrationId);

        if (!$registration) {
            $this->logger->error('Stripe webhook: registration not found', [
                'registration_id' => $registrationId,
                'payment_intent_id' => $paymentIntent['id'] ?? null,
            ]);
            return;
        }

        // Idempotency — Stripe can deliver the same event multiple times.
        // If we've already marked this paid, skip doing it again.
        if ($registration->isPaid()) {
            $this->logger->info('Stripe webhook: registration already marked paid, skipping', [
                'registration_id' => $registrationId,
            ]);
            return;
        }

        $registration
            ->setPaid(true)
            ->setStripePaymentIntentId($paymentIntent['id'])
            ->setAmountPaidCents($paymentIntent['amount_received'])
            ->setPaidAt(new \DateTimeImmutable());

        $this->em->persist($registration);
        $this->em->flush();

        $this->logger->info('Stripe webhook: registration marked paid', [
            'registration_id' => $registrationId,
            'amount_cents' => $paymentIntent['amount_received'],
            'payment_intent_id' => $paymentIntent['id'],
        ]);
    }

    private function handlePaymentFailed(array $payload): void
    {
        $paymentIntent = $payload['data']['object'] ?? null;

        $this->logger->warning('Stripe webhook: payment failed', [
            'registration_id' => $paymentIntent['metadata']['registration_id'] ?? null,
            'payment_intent_id' => $paymentIntent['id'] ?? null,
            'last_payment_error' => $paymentIntent['last_payment_error']['message'] ?? null,
        ]);

        // For now, just logging. Later you might want to email yourself or flag
        // the registration so you can follow up with the registrant.
    }
}