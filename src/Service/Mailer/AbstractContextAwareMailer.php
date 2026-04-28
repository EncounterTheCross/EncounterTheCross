<?php

namespace App\Service\Mailer;

use App\Exception\Core\LogicException;
use App\Exception\MissingMailerContextRequiredValuesException;
use App\Exception\MultipleSendsMailerException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface as Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

abstract class AbstractContextAwareMailer implements MailerInterface
{
    private const CONTEXT_REQUIREMENTS = [];
    private bool $sendMultiple = false;
    private ?TemplatedEmail $email;

    public function __construct(
        private Mailer $mailer,
        protected LoggerInterface $logger,
    ) {
        $this->email = new TemplatedEmail();
    }

    /**
     * This method is specifically so you can set up the Email Subject, template or body, etc. This is after the submited context from the send method has been added but before we add the toEmails. Those will me merged later if you add some now.
     */
    abstract protected function configureEmail(TemplatedEmail|Email $email): TemplatedEmail|Email;

    /**
     * @internal
     */
    final public function send(string|array|null $toEmails = null, array $context = []): void
    {
        $email = $this->getEmail();
        $this->email = $email->context(
            array_merge_recursive(
                $email->getContext(),
                $context
            )
        );
        $this->email = $this->configureEmail($email);

        $this->sendMail($toEmails);
    }

    /**
     * @return array<Address>|Address[]
     */
    protected function createToAddresses(string|array $toEmails): array
    {

        if (is_string($toEmails)) {
            return [new Address($toEmails)];
        }

        if (is_array($toEmails)) {
            $toEmails = array_map(function (array|Address $toEmail) {
                return $toEmail instanceof Address
                    ? $toEmail
                    : new Address($toEmail['Email'] ?? $toEmail['email'] ?? $toEmail[0], $toEmail['Name'] ?? '');
            }, $toEmails);
        }

        return $toEmails;
    }

    protected function getMailer(): Mailer
    {
        return $this->mailer;
    }

    private function handlePreventEmailSend(?string $message = null, ?array $context = [])
    {
        $exceptionMessage = $message ?? 'Had an issue generating the email, was unable to send.';

        $this->logger->warning(
            sprintf(
                $exceptionMessage.' From class %s',
                get_class($this)
            ), $context ?? []);
    }

    private function validateContext(array $context): bool
    {
        $exception = null;
        //        dd(is_a('test this out','string',true),is_a('test this out','string'),is_a(['test this out',],'array'));
        foreach (self::CONTEXT_REQUIREMENTS as $key => $objectType) {
            if (
                array_key_exists($key, $context)
            ) {
                $exception = new MissingMailerContextRequiredValuesException(
                    key: $key,
                    previous: $exception
                );
                continue;
            }

            if (is_a($context[$key], $objectType, true)) {
                $exception = new MissingMailerContextRequiredValuesException(
                    key: $key,
                    missing: false,
                    previous: $exception
                );
            }
        }

        return null === $exception
            ? true
            : throw $exception;
    }

    final protected function getEmail(): TemplatedEmail
    {
        // The reason for this is we want a new email created each time we try to send on. That way the email ID is different each time. Other wise it creates a nightmare when looking at the emails.
        if ($this->sendMultiple && null === $this->email) {
            $this->email = new TemplatedEmail();
        }

        // IF the email is null at this point we are configure to send the email only one time.
        if (null === $this->email) {
            throw new MultipleSendsMailerException(get_class($this));
        }

        return $this->email;
    }

    private function sendMail(array|string|null $toEmails): void
    {
        if (null === $toEmails && empty($this->getEmail()->getTo())) {
            $this->handlePreventEmailSend(
                message: 'There was no ToEmails configured, skipping sending email.',
                context: $this->email?->getContext(),
            );

            // Validation Fails, but its ok for the Application to keep running
            return;
        }

        // Make sure emails are in correct format
        try {
            $toEmails = array_merge($this->getEmail()->getTo(), $this->createToAddresses($toEmails ?? []));
        } catch (\Exception $exception) {
            throw new LogicException(message: 'While converting all to emails to an Address, one was missing a value.', previous: $exception);
        }

        $this->validateContext($this->getEmail()->getContext());

        $this->getMailer()->send($this->getEmail()->to(...$toEmails));

        $this->email = null;
    }
}
