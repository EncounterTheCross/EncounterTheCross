<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SetFromListener implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ?string $fromEmail = null,
        private ?string $fromName = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }

    public function onMessage(MessageEvent $event): void
    {
        $email = $event->getMessage();
        if (!$email instanceof Email) {
            return;
        }

        // To allow this function to work we will want to require both values,
        // These will be autowired into the class. And configured on the .env file
        // In the future we might need to look at having multiple configs for this.
        // That way if we end up sending from more than one email.
        if (null === $this->fromEmail || null === $this->fromName) {
            $this->logger->error(
                'You don\'t have all the Mailer variables configured. You are missing the from address options. Take a look in your environment variables to see if you have them set.',
                ['fromEmail' => $this->fromEmail, 'fromName' => $this->fromName]
            );

            return;
        }

        $email->from(new Address($this->fromEmail, $this->fromName));
    }
}
