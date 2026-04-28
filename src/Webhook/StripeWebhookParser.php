<?php

namespace App\Webhook;

use App\Settings\Global\StripeSettings;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;
use Tzunghaor\SettingsBundle\Service\SettingsService;

final class StripeWebhookParser extends AbstractRequestParser
{
    public function __construct(
        private SettingsService $settingsService,
    ) {
    }

    /**
     * Defines which requests this parser accepts.
     * Stripe posts JSON to our webhook URL, so we match on POST + JSON.
     */
    protected function getRequestMatcher(): RequestMatcherInterface
    {
        return new ChainRequestMatcher([
            new MethodRequestMatcher('POST'),
            new IsJsonRequestMatcher(),
        ]);
    }

    /**
     * Verifies the Stripe signature using the Stripe SDK, then wraps the
     * verified event data in a RemoteEvent so the consumer can process it.
     *
     * @param string $secret The webhook signing secret from config
     */
    protected function doParse(Request $request, string $secret): ?RemoteEvent
    {
        /** @var StripeSettings $stripeSettings */
        $stripeSettings = $this->settingsService->getSection(sectionClass: StripeSettings::class);

        $webhookSecret = $stripeSettings->getWebhookSecret();

        if (empty($webhookSecret)) {
            throw new RejectWebhookException(500, 'Stripe webhook secret not configured.');
        }

        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        if (!$sigHeader) {
            throw new RejectWebhookException(400, 'Missing Stripe-Signature header.');
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            throw new RejectWebhookException(400, 'Invalid Stripe payload: '.$e->getMessage());
        } catch (SignatureVerificationException $e) {
            throw new RejectWebhookException(400, 'Invalid Stripe signature: '.$e->getMessage());
        }

        // The RemoteEvent carries:
        //   - name: the Stripe event type (e.g. "payment_intent.succeeded")
        //   - id: Stripe's unique event id, useful for deduplication
        //   - payload: full event data as an associative array
        return new RemoteEvent(
            name: $event->type,
            id: $event->id,
            payload: $event->toArray(),
        );
    }
}
