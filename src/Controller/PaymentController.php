<?php

namespace App\Controller;

use App\Entity\Event;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/register/{event}/payment', name: 'app_registration_payment')]
    public function payment(Event $event, Request $request): Response
    {
        $registrationData = $request->getSession()->get('registration_data');

        if (!$registrationData || $registrationData['event_id'] !== $event->getId()) {
            // TODO: this will change when we have payments at checkin.
            $this->addFlash('error', 'Registration session expired. Please start over.');

            return $this->redirectToRoute('app_registration_attendee_formentry', ['event' => $event->getId()]);
        }

        $eventAmount = (int) ($event->getPrice() * 100);
        $platformFee = $this->getPlatformFees($eventAmount);

        // No PaymentIntent here anymore — just render the page
        return $this->render('frontend/events/payment.html.twig', [
            'event' => $event,
            'registration' => $registrationData['registration'],
            'stripe_public_key' => $this->getStripeSettings()->getPublicKey(),
            'event_amount' => $eventAmount,
            'platform_fee' => $platformFee,
        ]);
    }

    #[Route('/register/{event}/payment-intent', name: 'app_registration_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Event $event, Request $request): JsonResponse
    {
        $registrationData = $request->getSession()->get('registration_data');

        // Validate session still belongs to this event
        if (!$registrationData || $registrationData['event_id'] !== $event->getId()) {
            return $this->json(['error' => 'Session expired.'], 403);
        }

        Stripe::setApiKey($this->getStripeSettings()->getSecretKey());

        try {
            $eventAmount = (int) ($event->getPrice() * 100);
            $platformFee = $this->getPlatformFees($eventAmount);
            $registration = $registrationData['registration'];

            $paymentIntent = PaymentIntent::create([
                'amount' => $eventAmount + $platformFee,
                'currency' => 'usd',
                'description' => 'Mens Encounter '.$event->getName().' - '
                    .$registration->getPerson()->getFirstName().' '
                    .$registration->getPerson()->getLastName(),
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'site_url' => $request->getSchemeAndHttpHost(),
                    'payment_type' => 'single',
                    'event_id' => $event->getId(),
                    'event_name' => 'Mens Encounter '.$event->getName(),
                    'registration_id' => $registration->getId(),
                    'customer_name' => $registration->getPerson()->getFirstName().' '.$registration->getPerson()->getLastName(),
                    'customer_email' => $registration->getPerson()->getEmail(),
                ],
            ]);

            // Store the PaymentIntent ID in session so the completion route can verify it
            $registrationData['payment_intent_id'] = $paymentIntent->id;
            $request->getSession()->set('registration_data', $registrationData);

            return $this->json(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to initialize payment: '.$e->getMessage()], 500);
        }
    }

    /**
     * Calculates the platform fee (in cents) to add to the event cost so that after
     * Stripe deducts their processing fee, the net amount received equals the original cost.
     *
     * Problem: If you simply add Stripe's fee to the cost, Stripe will then charge
     * their percentage on the NEW total (fee-on-fee), leaving you short.
     *
     * Solution: Use the gross-up formula to solve for the total charge T:
     *     T - (r * T + f) = P
     *     T = (P + f) / (1 - r)
     *
     * Where:
     *     P = desired net (the original event cost, in cents)
     *     r = Stripe's percentage rate (e.g., 0.029 for 2.9%)
     *     f = Stripe's flat fee (in cents, e.g., 30 for 30 cents)
     *     T = total amount to charge the customer (in cents)
     *
     * The platform fee returned is simply T - P.
     *
     * Example: 15000 cents ($150) cost with Stripe's 2.9% + 30 cent fee
     *     T = (15000 + 30) / (1 - 0.029) = 15479.92... -> rounded up to 15480
     *     Platform fee = 15480 - 15000 = 480 cents ($4.80)
     *     Stripe takes floor(15480 * 0.029) + 30 = 448 + 30 = 478 cents
     *     Net received = 15480 - 478 = 15002 cents (>= 15000, target met) ✓
     *
     * @param int $totalCost the desired net amount (event cost) in cents
     *
     * @return int the platform fee to add, in cents, rounded up to the nearest cent
     */
    private function getPlatformFees(int $totalCost): int
    {
        $settings = $this->getStripeSettings();
        $rate = $settings->getFeeRate();  // e.g., 0.029
        $baseCents = $settings->getFeeBase();  // e.g., 30 (cents)

        // Gross-up: solve for the total charge (in cents) that nets out to $totalCost after fees
        $totalCharge = ($totalCost + $baseCents) / (1 - $rate);

        // Round UP to the nearest whole cent so rounding never leaves us short of the net target
        $totalChargeCents = (int) ceil($totalCharge);

        // The platform fee is the difference between what we charge and what we want to net
        return $totalChargeCents - $totalCost;
    }
}
